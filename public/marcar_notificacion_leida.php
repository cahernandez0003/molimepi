<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    echo json_encode(['error' => 'ID de notificación no válido']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        UPDATE notificaciones 
        SET leida = 1, 
            fecha_lectura = NOW() 
        WHERE id = :id AND usuario_id = :usuario_id
    ");
    
    $stmt->execute([
        'id' => $_POST['id'],
        'usuario_id' => $_SESSION['usuario_id']
    ]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'No se pudo marcar la notificación como leída']);
    }
} catch (PDOException $e) {
    error_log("Error en marcar_notificacion_leida.php: " . $e->getMessage());
    echo json_encode(['error' => 'Error al marcar la notificación']);
} 