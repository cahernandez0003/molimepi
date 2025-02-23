<?php
session_start();
require_once '../config/database.php';

// Verificar que el usuario está autenticado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$es_admin = $_SESSION['rol'] === 'Administrador';

// Obtener notificaciones según el rol
if ($es_admin) {
    $sql = "SELECT n.*, u.nickname as solicitante, u.nombre as nombre_solicitante,
            CASE 
                WHEN n.tipo = 'solicitud_password' THEN 'Solicitud de Contraseña'
                ELSE n.tipo 
            END as tipo_formato
            FROM notificaciones n 
            LEFT JOIN usuarios u ON n.usuario_id = u.ID 
            ORDER BY n.fecha_creacion DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
} else {
    $sql = "SELECT n.*, 
            CASE 
                WHEN n.tipo = 'solicitud_password' THEN 'Solicitud de Contraseña'
                ELSE n.tipo 
            END as tipo_formato
            FROM notificaciones n 
            WHERE usuario_id = :usuario_id 
            ORDER BY n.fecha_creacion DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['usuario_id' => $usuario_id]);
}

$notificaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Marcar todas las notificaciones como leídas
$stmt = $pdo->prepare("UPDATE notificaciones SET leida = 1 
                       WHERE usuario_id = :usuario_id AND leida = 0");
$stmt->execute(['usuario_id' => $usuario_id]);

include 'navbar.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificaciones - MoliMepi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .notification-card {
            margin-bottom: 1rem;
            border-left: 4px solid #007bff;
        }
        .notification-card.read {
            border-left-color: #6c757d;
            background-color: #f8f9fa;
        }
        .notification-meta {
            font-size: 0.85rem;
            color: #6c757d;
        }
        .notification-comment {
            margin-top: 0.5rem;
            padding: 0.5rem;
            background-color: #f8f9fa;
            border-radius: 0.25rem;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="card">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h3 class="mb-0">
                    <i class="fas fa-bell"></i> Mis Notificaciones
                </h3>
            </div>
            <div class="card-body">
                <?php if (empty($notificaciones)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> No hay notificaciones para mostrar.
                    </div>
                <?php else: ?>
                    <?php foreach ($notificaciones as $notif): ?>
                        <div class="card notification-card <?php echo $notif['leida'] ? 'read' : ''; ?>">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <?php if ($es_admin && isset($notif['nombre_solicitante'])): ?>
                                            <h5 class="card-title">
                                                <i class="fas fa-user"></i> 
                                                <?php echo htmlspecialchars($notif['nombre_solicitante']); ?>
                                            </h5>
                                        <?php endif; ?>
                                        <p class="card-text"><?php echo htmlspecialchars($notif['mensaje']); ?></p>
                                        <?php if (!empty($notif['comentario'])): ?>
                                            <div class="notification-comment">
                                                <i class="fas fa-comment"></i> 
                                                <?php echo htmlspecialchars($notif['comentario']); ?>
                                            </div>
                                        <?php endif; ?>
                                        <div class="notification-meta mt-2">
                                            <span><i class="fas fa-tag"></i> <?php echo htmlspecialchars($notif['tipo_formato']); ?></span>
                                            <span class="ml-3"><i class="fas fa-clock"></i> <?php echo date('d/m/Y H:i', strtotime($notif['fecha_creacion'])); ?></span>
                                            <span class="ml-3">
                                                <?php if ($notif['leida']): ?>
                                                    <i class="fas fa-check-double text-secondary"></i> Leída
                                                <?php else: ?>
                                                    <i class="fas fa-circle text-primary"></i> Nueva
                                                <?php endif; ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html> 