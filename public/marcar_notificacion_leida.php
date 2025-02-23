<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

$id = $_POST['id'] ?? null;
if (!$id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'ID de notificación no proporcionado']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Obtener información de la notificación
    $stmt = $pdo->prepare("SELECT * FROM notificaciones WHERE ID = :id");
    $stmt->execute(['id' => $id]);
    $notificacion = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$notificacion) {
        throw new Exception('Notificación no encontrada');
    }

    // Verificar permisos
    $es_admin = $_SESSION['rol'] === 'Administrador';
    if (!$es_admin && $notificacion['usuario_id'] != $_SESSION['usuario_id']) {
        throw new Exception('No autorizado para esta notificación');
    }

    // Marcar como leída
    $stmt = $pdo->prepare("UPDATE notificaciones SET leida = 1 WHERE ID = :id");
    $stmt->execute(['id' => $id]);

    // Obtener contador actualizado
    if ($es_admin) {
        $sql = "SELECT COUNT(*) FROM notificaciones WHERE tipo = 'solicitud_password' AND leida = 0";
        $params = [];
    } else {
        $sql = "SELECT COUNT(*) FROM notificaciones WHERE usuario_id = :usuario_id AND leida = 0";
        $params = ['usuario_id' => $_SESSION['usuario_id']];
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $no_leidas = $stmt->fetchColumn();

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'no_leidas' => $no_leidas,
        'mensaje' => 'Notificación marcada como leída'
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} 