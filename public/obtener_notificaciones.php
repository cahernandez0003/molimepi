<?php
// Habilitar el reporte de errores solo para depuración
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Establecer el header de Content-Type desde el inicio
header('Content-Type: application/json; charset=utf-8');

try {
    require_once '../config/database.php';
    
    // Iniciar sesión si no está iniciada
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Verificar autenticación
    if (!isset($_SESSION['usuario_id'])) {
        throw new Exception('No autorizado');
    }

    $usuario_id = $_SESSION['usuario_id'];
    $es_admin = $_SESSION['rol'] === 'Administrador';

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
    if ($es_admin) {
        $sql = "SELECT COUNT(*) FROM notificaciones 
                WHERE tipo = 'solicitud_password' AND leida = 0";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
    } else {
        $sql = "SELECT COUNT(*) FROM notificaciones 
                WHERE usuario_id = :usuario_id AND leida = 0";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['usuario_id' => $usuario_id]);
    }
    $no_leidas = $stmt->fetchColumn();
    
    // Asegurar que los datos son UTF-8
    array_walk_recursive($notificaciones, function(&$item) {
        if (is_string($item)) {
            $item = mb_convert_encoding($item, 'UTF-8', 'UTF-8');
        }
    });
    
    echo json_encode([
        'success' => true,
        'notificaciones' => $notificaciones,
        'no_leidas' => $no_leidas
    ], JSON_UNESCAPED_UNICODE);
    
} catch (PDOException $e) {
    http_response_code(500);
    error_log("Error de base de datos en obtener_notificaciones.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Error al obtener notificaciones'
    ]);
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} 