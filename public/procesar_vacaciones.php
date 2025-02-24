<?php
require_once '../config/database.php';
require_once 'includes/auth.php';

header('Content-Type: application/json');

verificarAcceso();
$es_admin = esAdmin();
$usuario_actual = obtenerIdUsuario();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

try {
    $pdo->beginTransaction();

    $id = $_POST['id'] ?? null;
    $usuario_id = $_POST['usuario_id'];
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin = $_POST['fecha_fin'];
    $comentarios = $_POST['comentarios'] ?? null;

    // Validar fechas
    $inicio = new DateTime($fecha_inicio);
    $fin = new DateTime($fecha_fin);
    $dias_solicitud = $inicio->diff($fin)->days + 1;
    $anio_solicitud = $inicio->format('Y');

    if ($inicio > $fin) {
        throw new Exception('La fecha de fin debe ser posterior a la fecha de inicio');
    }

    if ($dias_solicitud > 31) {
        throw new Exception('El período de vacaciones no puede exceder los 31 días');
    }

    // Verificar permisos
    if (!$es_admin && $usuario_id != $usuario_actual) {
        throw new Exception('No tiene permisos para realizar esta acción');
    }

    // Verificar el límite anual de días de vacaciones
    $stmt = $pdo->prepare("
        SELECT SUM(DATEDIFF(fecha_fin, fecha_inicio) + 1) as dias_aprobados
        FROM vacaciones 
        WHERE usuario_id = :usuario_id 
        AND YEAR(fecha_inicio) = :anio
        AND estado_solicitud = 'Aprobado'
        AND id != :id
    ");
    
    $stmt->execute([
        'usuario_id' => $usuario_id,
        'anio' => $anio_solicitud,
        'id' => $id ?? 0
    ]);
    
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    $dias_ya_aprobados = (int)($resultado['dias_aprobados'] ?? 0);
    $total_dias = $dias_ya_aprobados + $dias_solicitud;

    if ($es_admin && $total_dias > 31) {
        throw new Exception(
            "No se pueden asignar/modificar estas vacaciones. El empleado ya tiene {$dias_ya_aprobados} días aprobados en {$anio_solicitud}. " .
            "Con esta solicitud de {$dias_solicitud} días, excedería el límite de 31 días anuales."
        );
    }

    // Verificar si hay solapamiento de fechas
    $stmt = $pdo->prepare("SELECT id FROM vacaciones 
                          WHERE usuario_id = :usuario_id 
                          AND ((fecha_inicio BETWEEN :fecha_inicio AND :fecha_fin) 
                          OR (fecha_fin BETWEEN :fecha_inicio AND :fecha_fin)
                          OR (:fecha_inicio BETWEEN fecha_inicio AND fecha_fin))
                          AND estado_solicitud != 'Rechazado'
                          AND id != :id");
    
    $stmt->execute([
        'usuario_id' => $usuario_id,
        'fecha_inicio' => $fecha_inicio,
        'fecha_fin' => $fecha_fin,
        'id' => $id ?? 0
    ]);

    if ($stmt->fetch()) {
        throw new Exception('Ya existe un período de vacaciones que se solapa con las fechas seleccionadas');
    }

    if ($id) {
        // Actualizar vacaciones existentes
        if (!$es_admin) {
            throw new Exception('Solo los administradores pueden editar vacaciones');
        }

        $stmt = $pdo->prepare("UPDATE vacaciones 
                              SET fecha_inicio = :fecha_inicio,
                                  fecha_fin = :fecha_fin,
                                  fecha_actualizacion = NOW()
                              WHERE id = :id");
        $stmt->execute([
            'fecha_inicio' => $fecha_inicio,
            'fecha_fin' => $fecha_fin,
            'id' => $id
        ]);

        $mensaje = 'Vacaciones actualizadas correctamente';
    } else {
        // Insertar nuevas vacaciones
        $stmt = $pdo->prepare("INSERT INTO vacaciones 
                              (usuario_id, fecha_inicio, fecha_fin, comentarios, estado_solicitud) 
                              VALUES 
                              (:usuario_id, :fecha_inicio, :fecha_fin, :comentarios, :estado)");
        
        $stmt->execute([
            'usuario_id' => $usuario_id,
            'fecha_inicio' => $fecha_inicio,
            'fecha_fin' => $fecha_fin,
            'comentarios' => $comentarios,
            'estado' => $es_admin ? 'Aprobado' : 'Pendiente'
        ]);

        $mensaje = $es_admin ? 'Vacaciones asignadas correctamente' : 'Solicitud de vacaciones enviada correctamente';
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'mensaje' => $mensaje]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} 