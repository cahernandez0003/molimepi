<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

try {
    // Obtener notificaciones del usuario
    $stmt = $pdo->prepare("
        SELECT n.*, 
               CASE 
                   WHEN n.tipo = 'solicitud_password' THEN 'solicitudes_password.php'
                   ELSE '#'
               END as url
        FROM notificaciones n
        WHERE n.usuario_id = :usuario_id
        ORDER BY n.fecha_creacion DESC, n.leida ASC
        LIMIT 10
    ");
    $stmt->execute(['usuario_id' => $_SESSION['usuario_id']]);
    $notificaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Contar notificaciones no leídas
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM notificaciones 
        WHERE usuario_id = :usuario_id AND leida = 0
    ");
    $stmt->execute(['usuario_id' => $_SESSION['usuario_id']]);
    $no_leidas = $stmt->fetchColumn();

    echo json_encode([
        'success' => true,
        'notificaciones' => $notificaciones,
        'no_leidas' => $no_leidas
    ]);
} catch (PDOException $e) {
    error_log("Error en obtener_notificaciones.php: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'error' => 'Error al obtener notificaciones'
    ]);
} 