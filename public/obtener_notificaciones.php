<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

if (!estaAutenticado()) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$es_admin = $_SESSION['rol'] === 'Administrador';

try {
    // Obtener notificaciones según el rol
    if ($es_admin) {
        $sql = "SELECT n.*, u.nickname as solicitante 
                FROM notificaciones n 
                LEFT JOIN usuarios u ON n.usuario_id = u.ID 
                WHERE n.tipo = 'solicitud_password'
                ORDER BY n.fecha_creacion DESC 
                LIMIT 5";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
    } else {
        $sql = "SELECT * FROM notificaciones 
                WHERE usuario_id = :usuario_id 
                ORDER BY fecha_creacion DESC 
                LIMIT 5";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['usuario_id' => $usuario_id]);
    }
    
    $notificaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Contar notificaciones no leídas
    $sql = "SELECT COUNT(*) FROM notificaciones 
            WHERE usuario_id = :usuario_id AND leida = 0";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['usuario_id' => $usuario_id]);
    $no_leidas = $stmt->fetchColumn();
    
    echo json_encode([
        'success' => true,
        'notificaciones' => $notificaciones,
        'no_leidas' => $no_leidas
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error al obtener notificaciones'
    ]);
} 