<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'Administrador') {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

if (!isset($_POST['id']) || !isset($_POST['comentario'])) {
    echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE notificaciones SET comentario = :comentario WHERE ID = :id");
    $stmt->execute([
        'comentario' => $_POST['comentario'],
        'id' => $_POST['id']
    ]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    error_log("Error en guardar_comentario_notificacion.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Error al guardar el comentario']);
} 