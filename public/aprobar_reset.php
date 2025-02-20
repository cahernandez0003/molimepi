<?php
session_start();
require_once '../config/database.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../config/phpmailer/src/Exception.php';
require '../config/phpmailer/src/PHPMailer.php';
require '../config/phpmailer/src/SMTP.php';

// Verificar si el usuario está autenticado y es administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'Administrador') {
    header('Location: login.php');
    exit();
}

if (!isset($_GET['id']) || !isset($_GET['token'])) {
    die("Solicitud inválida");
}

$usuario_id = $_GET['id'];
$token = $_GET['token'];

try {
    // Obtener información del usuario
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE ID = :id");
    $stmt->execute(['id' => $usuario_id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        die("Usuario no encontrado");
    }

    // Restablecer la contraseña a "123456" y marcar para cambio
    $nueva_password = password_hash("123456", PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("UPDATE usuarios SET password = :password, cambio_password = 0 WHERE ID = :id");
    $stmt->execute([
        'password' => $nueva_password,
        'id' => $usuario_id
    ]);

    // Enviar correo al usuario notificando el restablecimiento
    $mail = new PHPMailer(true);

    try {
        // Configuración del servidor SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.office365.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'poli-70811@hotmail.com';
        $mail->Password = 'Agus091123';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Configuración del correo
        $mail->setFrom('poli-70811@hotmail.com', 'Molimepi - Sistema');
        $mail->addAddress($usuario['correo'], $usuario['nombre']);
        $mail->Subject = 'Contraseña Restablecida';

        // Cuerpo del correo
        $mensaje = "
        <h2>¡Hola {$usuario['nombre']}!</h2>
        <p>Tu contraseña ha sido restablecida por un administrador.</p>
        <p>Tu nueva contraseña temporal es: <strong>123456</strong></p>
        <p>Por favor, inicia sesión y cambia tu contraseña inmediatamente.</p>
        <p><a href='http://localhost/molimepi/public/login.php'>Iniciar Sesión</a></p>
        <br>
        <p>Saludos,<br><strong>Molimepi - Sistema</strong></p>
        ";

        $mail->isHTML(true);
        $mail->Body = $mensaje;
        $mail->send();

        $mensaje = "La contraseña ha sido restablecida y se ha notificado al usuario.";
    } catch (Exception $e) {
        error_log("Error al enviar correo: " . $mail->ErrorInfo);
        $error = "La contraseña fue restablecida pero hubo un error al enviar el correo.";
    }
} catch (PDOException $e) {
    error_log("Error en la base de datos: " . $e->getMessage());
    $error = "Error al restablecer la contraseña.";
}
?>

<!DOCTYPE html>
<html lang="es">
<?php include 'head.php'; ?>
<body>
    <?php include 'navbar.php'; ?>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h3 class="mb-0">Restablecer Contraseña</h3>
                    </div>
                    <div class="card-body">
                        <?php if (isset($mensaje)): ?>
                            <div class="alert alert-success">
                                <?php echo $mensaje; ?>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger">
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>

                        <div class="text-center mt-3">
                            <a href="empleados.php" class="btn btn-primary">Volver a Empleados</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 