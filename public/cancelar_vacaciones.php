<?php
require_once '../config/database.php';
require_once 'includes/auth.php';

header('Content-Type: application/json');

verificarAcceso();
$usuario_id = obtenerIdUsuario();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

try {
    $pdo->beginTransaction();

    $id = $_POST['id'];

    // Verificar que la solicitud existe y pertenece al usuario
    $stmt = $pdo->prepare("SELECT id FROM vacaciones 
                          WHERE id = :id 
                          AND usuario_id = :usuario_id 
                          AND estado_solicitud = 'Pendiente'");
    
    $stmt->execute([
        'id' => $id,
        'usuario_id' => $usuario_id
    ]);

    if (!$stmt->fetch()) {
        throw new Exception('No se encontró la solicitud o no tiene permisos para cancelarla');
    }

    // Eliminar la solicitud
    $stmt = $pdo->prepare("DELETE FROM vacaciones WHERE id = :id");
    $stmt->execute(['id' => $id]);

    $pdo->commit();
    echo json_encode(['success' => true, 'mensaje' => 'Solicitud cancelada correctamente']);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} 