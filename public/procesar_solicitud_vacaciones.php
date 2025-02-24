<?php
require_once '../config/database.php';
require_once 'includes/auth.php';

header('Content-Type: application/json');

verificarAccesoAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

try {
    $pdo->beginTransaction();

    $id = $_POST['id'];
    $usuario_id = $_POST['usuario_id'];
    $estado = $_POST['estado'];
    $comentarios = $_POST['comentarios'];

    // Si el estado es Aprobado, verificar el límite de días
    if ($estado === 'Aprobado') {
        // Obtener la solicitud actual
        $stmt = $pdo->prepare("SELECT fecha_inicio, fecha_fin FROM vacaciones WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $solicitud_actual = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$solicitud_actual) {
            throw new Exception('No se encontró la solicitud');
        }

        $fecha_inicio_actual = new DateTime($solicitud_actual['fecha_inicio']);
        $fecha_fin_actual = new DateTime($solicitud_actual['fecha_fin']);
        $dias_solicitud_actual = $fecha_inicio_actual->diff($fecha_fin_actual)->days + 1;

        // Obtener el año de la solicitud
        $anio_solicitud = $fecha_inicio_actual->format('Y');

        // Contar días ya aprobados en el mismo año (excluyendo esta solicitud)
        $stmt = $pdo->prepare("
            SELECT SUM(
                DATEDIFF(fecha_fin, fecha_inicio) + 1
            ) as dias_aprobados
            FROM vacaciones 
            WHERE usuario_id = :usuario_id 
            AND YEAR(fecha_inicio) = :anio
            AND estado_solicitud = 'Aprobado'
            AND id != :id"
        );
        
        $stmt->execute([
            'usuario_id' => $usuario_id,
            'anio' => $anio_solicitud,
            'id' => $id
        ]);
        
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        $dias_ya_aprobados = (int)($resultado['dias_aprobados'] ?? 0);
        $total_dias = $dias_ya_aprobados + $dias_solicitud_actual;

        if ($total_dias > 31) {
            throw new Exception(
                "No se puede aprobar esta solicitud. El empleado ya tiene {$dias_ya_aprobados} días aprobados este año. " .
                "Con esta solicitud de {$dias_solicitud_actual} días, excedería el límite de 31 días anuales."
            );
        }
    }

    // Actualizar la solicitud
    $stmt = $pdo->prepare("UPDATE vacaciones 
                          SET estado_solicitud = :estado,
                              comentarios = :comentarios,
                              fecha_aprobacion = NOW(),
                              fecha_actualizacion = NOW(),
                              aprobado_por = :aprobado_por
                          WHERE id = :id");
    
    $stmt->execute([
        'estado' => $estado,
        'comentarios' => $comentarios,
        'aprobado_por' => obtenerIdUsuario(),
        'id' => $id
    ]);

    $pdo->commit();
    echo json_encode([
        'success' => true, 
        'mensaje' => 'Solicitud ' . strtolower($estado) . 'a correctamente'
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} 