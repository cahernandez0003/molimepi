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

    // Eliminar el registro
    $stmt = $pdo->prepare("DELETE FROM vacaciones WHERE id = :id");
    $stmt->execute(['id' => $id]);

    if ($stmt->rowCount() === 0) {
        throw new Exception('No se encontró el registro de vacaciones');
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'mensaje' => 'Registro eliminado correctamente']);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} 