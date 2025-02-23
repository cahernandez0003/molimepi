<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

if (!estaAutenticado()) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

$id = $_POST['id'] ?? null;
if (!$id) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de notificación no proporcionado']);
    exit;
}

try {
    // Verificar que la notificación pertenezca al usuario
    $stmt = $pdo->prepare("SELECT usuario_id FROM notificaciones WHERE ID = :id");
    $stmt->execute(['id' => $id]);
    $notificacion = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$notificacion || $notificacion['usuario_id'] != $_SESSION['usuario_id']) {
        http_response_code(403);
        echo json_encode(['error' => 'No autorizado para esta notificación']);
        exit;
    }

    // Marcar como leída
    $stmt = $pdo->prepare("UPDATE notificaciones SET leida = 1 WHERE ID = :id");
    $stmt->execute(['id' => $id]);

    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error al marcar la notificación como leída'
    ]);
} 