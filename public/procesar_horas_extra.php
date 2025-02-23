<?php
require_once '../config/database.php';
require_once 'includes/auth.php';

header('Content-Type: application/json');

verificarAcceso();
if (!esAdmin()) {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit();
}

try {
    $pdo->beginTransaction();

    // Obtener datos del POST
    $registro_id = $_POST['registro_id'];
    $empleado_id = $_POST['empleado_id'];
    $fecha = $_POST['fecha'];
    $horas_solicitadas = floatval($_POST['horas_solicitadas']);
    $horas_aprobadas = floatval($_POST['horas_aprobadas']);
    $estado = $_POST['estado'];
    $comentarios = $_POST['comentarios'];

    // Validaciones
    if ($estado === 'Aprobado' && ($horas_aprobadas <= 0 || $horas_aprobadas > $horas_solicitadas)) {
        throw new Exception('Las horas aprobadas deben ser mayores a 0 y no pueden exceder las horas solicitadas');
    }

    // Insertar o actualizar en solicitudes_horas_extra
    $stmt = $pdo->prepare("INSERT INTO solicitudes_horas_extra 
                          (usuario_id, fecha, horas_solicitadas, horas_aprobadas, estado, comentarios, aprobado_por, aprobado_en) 
                          VALUES (:usuario_id, :fecha, :horas_solicitadas, :horas_aprobadas, :estado, :comentarios, :aprobado_por, NOW())
                          ON DUPLICATE KEY UPDATE 
                          horas_aprobadas = :horas_aprobadas,
                          estado = :estado,
                          comentarios = :comentarios,
                          aprobado_por = :aprobado_por,
                          aprobado_en = NOW()");

    $stmt->execute([
        'usuario_id' => $empleado_id,
        'fecha' => $fecha,
        'horas_solicitadas' => $horas_solicitadas,
        'horas_aprobadas' => $estado === 'Aprobado' ? $horas_aprobadas : 0,
        'estado' => $estado,
        'comentarios' => $comentarios,
        'aprobado_por' => $_SESSION['usuario_id']
    ]);

    // Si se aprueba, registrar en hrex_empleado
    if ($estado === 'Aprobado') {
        $stmt = $pdo->prepare("INSERT INTO hrex_empleado 
                              (usuario_id, fecha, horas_extra) 
                              VALUES (:usuario_id, :fecha, :horas_extra)
                              ON DUPLICATE KEY UPDATE 
                              horas_extra = :horas_extra");
        
        $stmt->execute([
            'usuario_id' => $empleado_id,
            'fecha' => $fecha,
            'horas_extra' => $horas_aprobadas
        ]);
    }

    // Crear notificación para el empleado
    $stmt = $pdo->prepare("INSERT INTO notificaciones 
                          (usuario_id, tipo, mensaje, comentario, leida) 
                          VALUES (:usuario_id, 'horas_extra', :mensaje, :comentario, 0)");
    
    $mensaje = $estado === 'Aprobado' 
        ? "Se han aprobado {$horas_aprobadas} horas extra para el día " . date('d/m/Y', strtotime($fecha))
        : "Se han rechazado las horas extra solicitadas para el día " . date('d/m/Y', strtotime($fecha));

    $stmt->execute([
        'usuario_id' => $empleado_id,
        'mensaje' => $mensaje,
        'comentario' => $comentarios
    ]);

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'mensaje' => $estado === 'Aprobado' 
            ? 'Horas extra aprobadas correctamente' 
            : 'Horas extra rechazadas correctamente'
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Error en procesar_horas_extra.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} 