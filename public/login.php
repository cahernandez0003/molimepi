<?php
session_start();
require_once '../config/database.php';

// Incluir PHPMailer desde la carpeta config/phpmailer/
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../config/phpmailer/src/Exception.php';
require '../config/phpmailer/src/PHPMailer.php';
require '../config/phpmailer/src/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nickname = $_POST['nickname']; // Ahora se usa nickname en lugar de correo
    $password = $_POST['password']; // Se recibe la contraseña en texto plano

    // Buscar al usuario por nickname
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE nickname = :nickname");
    $stmt->execute(['nickname' => $nickname]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario && password_verify($password, $usuario['password'])) { // Comparar con password_verify
        $_SESSION['usuario_id'] = $usuario['ID'];
        $_SESSION['rol'] = $usuario['rol'];

        // Verificar si el usuario debe cambiar su contraseña
        if ($usuario['cambio_password'] == 0) {
            enviarCorreoCambioPassword($usuario['correo'], $usuario['nombre'], $usuario['nickname']);
            header('Location: cambiar_password.php');
            exit();
        }

        header('Location: perfil.php'); // Redirigir al perfil si ya cambió la contraseña
        exit();
    } else {
        $error = "Nickname o contraseña incorrectos.";
    }
}

function enviarCorreoCambioPassword($correo, $nombre, $nickname) {
    $mail = new PHPMailer(true);

    try {
        // Configuración del servidor SMTP de Outlook/Hotmail
        $mail->isSMTP();
        $mail->Host = 'smtp.office365.com'; // Servidor SMTP de Hotmail/Outlook
        $mail->SMTPAuth = true;
        $mail->Username = 'poli-70811@hotmail.com'; // Reemplaza con tu correo de Hotmail
        $mail->Password = 'Agus091123'; // Usa tu contraseña de aplicación (no la normal)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587; // Puerto SMTP para Hotmail/Outlook

        // Configuración del correo
        $mail->setFrom('poli-70811@hotmail.com', 'Molimepi - Gestión de Empleados');
        $mail->addAddress($correo, $nombre);
        $mail->Subject = 'Cambio de Contraseña - MOLIMEPI';

        // Cuerpo del correo
        $mensaje = "
        <h2>¡Hola, $nombre!</h2>
        <p>Has iniciado sesión en MOLIMEPI y necesitas cambiar tu contraseña.</p>
        <p>Tu usuario (nickname) es: <strong>$nickname</strong></p>
        <p>Haz clic en el siguiente enlace para cambiar tu contraseña:</p>
        <p><a href='http://localhost/molimepi/public/cambiar_password.php
'>Cambiar mi contraseña</a></p>
        <br>
        <p>Si no reconoces este acceso, por favor contacta con el administrador.</p>
        <br>
        <p>Saludos,<br><strong>Molimepi - Gestión de Empleados</strong></p>
        ";
        $mail->isHTML(true);
        $mail->Body = $mensaje;

        // Enviar correo
        $mail->send();
    } catch (Exception $e) {
        error_log("Error al enviar el correo: " . $mail->ErrorInfo);
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<?php include 'head.php'; ?>
<body class="bg-dark text-white">
    <div class="container d-flex justify-content-center align-items-center vh-100">
        <div class="card p-4 shadow-lg" style="width: 22rem;">
            <div class="card-body">
                <div class="text-center mb-4">
                    <img src="imgs/molimepi.png" alt="MOLIMEPI" class="img-fluid" style="max-width: 100px;">
                    <h3 style="font-size: 2rem; color: #007bff;">MOLIMEPI</h3>
                    <h4 style="font-size: 1.2rem; color: #007bff;">Sistema de Gestión de Empleados</h4>
                </div>
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                <form method="post" action="">
                    <div class="form-group">
                        <label for="nickname" style="color: #007bff;">Nickname:</label>
                        <input type="text" id="nickname" name="nickname" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="password" style="color: #007bff;">Contraseña:</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block mt-3">Iniciar Sesión</button>
                    <a href="olvide_password.php" class="btn btn-link btn-block">¿Olvidaste tu contraseña?</a>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
