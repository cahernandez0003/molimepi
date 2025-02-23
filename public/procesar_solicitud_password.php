<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'Administrador') {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

if (!isset($_POST['id']) || !isset($_POST['aprobar']) || !isset($_POST['comentario'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Faltan datos requeridos']);
    exit;
}

$id = $_POST['id'];
$aprobar = $_POST['aprobar'] === 'true' || $_POST['aprobar'] === true;
$comentario = trim($_POST['comentario']);

try {
    $pdo->beginTransaction();

    // Obtener información de la notificación y el usuario
    $stmt = $pdo->prepare("
        SELECT n.*, u.correo, u.nombre, u.ID as usuario_id 
        FROM notificaciones n 
        INNER JOIN usuarios u ON n.usuario_id = u.ID 
        WHERE n.id = :id
    ");
    $stmt->execute(['id' => $id]);
    $notificacion = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$notificacion) {
        throw new Exception('Notificación no encontrada');
    }

    if ($aprobar) {
        // Restablecer la contraseña a "123456" y marcar para cambio
        $password = password_hash("123456", PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("UPDATE usuarios SET password = :password, cambio_password = 0 WHERE ID = :id");
        $stmt->execute([
            'password' => $password,
            'id' => $notificacion['usuario_id']
        ]);
    }

    // Actualizar la notificación
    $stmt = $pdo->prepare("
        UPDATE notificaciones 
        SET leida = 1, 
            comentario = :comentario,
            estado = :estado
        WHERE id = :id
    ");
    $stmt->execute([
        'comentario' => $comentario,
        'estado' => $aprobar ? 'Aprobada' : 'Rechazada',
        'id' => $id
    ]);

    // Crear notificación de respuesta para el usuario
    $mensaje = $aprobar 
        ? "Tu solicitud de restablecimiento de contraseña ha sido aprobada. Tu nueva contraseña temporal es: 123456" 
        : "Tu solicitud de restablecimiento de contraseña ha sido rechazada.";

    $stmt = $pdo->prepare("
        INSERT INTO notificaciones (usuario_id, tipo, mensaje, comentario, leida) 
        VALUES (:usuario_id, 'respuesta_password', :mensaje, :comentario, 0)
    ");
    $stmt->execute([
        'usuario_id' => $notificacion['usuario_id'],
        'mensaje' => $mensaje,
        'comentario' => $comentario
    ]);

    // Enviar correo al usuario
    require_once '../config/phpmailer/src/Exception.php';
    require_once '../config/phpmailer/src/PHPMailer.php';
    require_once '../config/phpmailer/src/SMTP.php';

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.office365.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'poli-70811@hotmail.com';
        $mail->Password = 'Agus091123';
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('poli-70811@hotmail.com', 'Molimepi - Sistema');
        $mail->addAddress($notificacion['correo'], $notificacion['nombre']);
        $mail->Subject = $aprobar ? 'Solicitud de Contraseña Aprobada' : 'Solicitud de Contraseña Rechazada';

        $mensaje_correo = $aprobar
            ? "<h2>¡Hola {$notificacion['nombre']}!</h2>
               <p>Tu solicitud de restablecimiento de contraseña ha sido aprobada.</p>
               <p>Tu nueva contraseña temporal es: <strong>123456</strong></p>
               <p>Por favor, inicia sesión y cambia tu contraseña inmediatamente.</p>"
            : "<h2>¡Hola {$notificacion['nombre']}!</h2>
               <p>Tu solicitud de restablecimiento de contraseña ha sido rechazada.</p>
               <p>Comentario del administrador: {$comentario}</p>";

        $mail->isHTML(true);
        $mail->Body = $mensaje_correo;
        $mail->send();
    } catch (Exception $e) {
        error_log("Error al enviar correo: " . $mail->ErrorInfo);
    }

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'mensaje' => $aprobar 
            ? 'Solicitud aprobada y contraseña restablecida' 
            : 'Solicitud rechazada'
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Error en procesar_solicitud_password.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} 