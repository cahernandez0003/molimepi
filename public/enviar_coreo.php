<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../config/phpmailer/src/Exception.php';
require '../config/phpmailer/src/PHPMailer.php';
require '../config/phpmailer/src/SMTP.php';

function enviarCorreoCambioPassword($correo, $nombre, $nickname) {
    $mail = new PHPMailer(true);

    try {
        // Configuración del servidor SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.tudominio.com'; // Cambia esto con tu servidor SMTP
        $mail->SMTPAuth = true;
        $mail->Username = 'tuemail@tudominio.com'; // Tu correo
        $mail->Password = 'tucontraseña'; // Tu contraseña
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587; // Puerto SMTP

        // Configuración del correo
        $mail->setFrom('tuemail@tudominio.com', 'Molimepi - Gestión de Empleados');
        $mail->addAddress($correo, $nombre);
        $mail->Subject = 'Cambio de Contraseña - MOLIMEPI';

        // Cuerpo del correo
        $mensaje = "
        <h2>¡Hola, $nombre!</h2>
        <p>Has iniciado sesión en MOLIMEPI y necesitas cambiar tu contraseña.</p>
        <p>Tu usuario (nickname) es: <strong>$nickname</strong></p>
        <p>Haz clic en el siguiente enlace para cambiar tu contraseña:</p>
        <p><a href='http://tuservidor/cambiar_password.php'>Cambiar mi contraseña</a></p>
        <br>
        <p>Si no reconoces este acceso, por favor contacta con el administrador.</p>
        <br>
        <p>Saludos,<br><strong>Molimepi - Gestión de Empleados</strong></p>
        ";
        $mail->isHTML(true);
        $mail->Body = $mensaje;

        // Enviar correo
        $mail->send();
        return true;
    } catch (Exception $e) {
        return "Error al enviar el correo: {$mail->ErrorInfo}";
    }
}
?>
