<?php
session_start();
require_once '../config/database.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../config/phpmailer/src/Exception.php';
require '../config/phpmailer/src/PHPMailer.php';
require '../config/phpmailer/src/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nickname = trim($_POST['nickname']);
    
    // Buscar al usuario por nickname
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE nickname = :nickname");
    $stmt->execute(['nickname' => $nickname]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
        // Verificar si ya existe una solicitud pendiente
        $stmt = $pdo->prepare("SELECT * FROM solicitudes_password 
                              WHERE usuario_id = :usuario_id AND estado = 'Pendiente'");
        $stmt->execute(['usuario_id' => $usuario['ID']]);
        $solicitud_existente = $stmt->fetch();

        if ($solicitud_existente) {
            $error = "Ya tienes una solicitud pendiente. Por favor, espera la respuesta del administrador.";
        } else {
            // Generar token único
            $token = md5(uniqid() . time());

            // Crear nueva solicitud
            $stmt = $pdo->prepare("INSERT INTO solicitudes_password (usuario_id, token) VALUES (:usuario_id, :token)");
            $stmt->execute([
                'usuario_id' => $usuario['ID'],
                'token' => $token
            ]);

            $solicitud_id = $pdo->lastInsertId();

            // Buscar administradores para notificar
            $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE rol = 'Administrador'");
            $stmt->execute();
            $administradores = $stmt->fetchAll(PDO::FETCH_ASSOC);

            error_log("Administradores encontrados: " . print_r($administradores, true));

            if (empty($administradores)) {
                error_log("No se encontraron administradores en el sistema");
                $mensaje = "Se ha registrado su solicitud. Por favor, contacte al administrador del sistema.";
            } else {
                // Crear notificación para cada administrador
                foreach ($administradores as $admin) {
                    error_log("Creando notificación para administrador ID: " . $admin['ID']);
                    
                    try {
                        $stmt = $pdo->prepare("INSERT INTO notificaciones (usuario_id, tipo, mensaje, referencia_id, leida) 
                                             VALUES (:usuario_id, 'solicitud_password', :mensaje, :referencia_id, 0)");
                        $stmt->execute([
                            'usuario_id' => $admin['ID'],
                            'mensaje' => "El usuario {$usuario['nombre']} ha solicitado restablecer su contraseña",
                            'referencia_id' => $solicitud_id
                        ]);
                        error_log("Notificación creada exitosamente para administrador ID: " . $admin['ID']);
                    } catch (PDOException $e) {
                        error_log("Error al crear notificación: " . $e->getMessage());
                    }

                    // Enviar correo al administrador
                    $mail = new PHPMailer(true);

                    try {
                        $mail->isSMTP();
                        $mail->Host = 'smtp.office365.com';
                        $mail->SMTPAuth = true;
                        $mail->Username = 'poli-70811@hotmail.com';
                        $mail->Password = 'Agus091123';
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                        $mail->Port = 587;

                        $mail->setFrom('poli-70811@hotmail.com', 'Molimepi - Sistema');
                        $mail->addAddress($admin['correo'], $admin['nombre']);
                        $mail->Subject = 'Solicitud de Restablecimiento de Contraseña';

                        $mensaje = "
                        <h2>Solicitud de Restablecimiento de Contraseña</h2>
                        <p>El usuario {$usuario['nombre']} ({$usuario['nickname']}) ha solicitado restablecer su contraseña.</p>
                        <p>Para gestionar esta solicitud, accede al panel de administración:</p>
                        <p><a href='http://localhost/molimepi/public/solicitudes_password.php'>Gestionar Solicitudes</a></p>
                        <br>
                        <p>Si no desea aprobar esta solicitud, puede rechazarla desde el panel.</p>
                        ";

                        $mail->isHTML(true);
                        $mail->Body = $mensaje;
                        $mail->send();
                    } catch (Exception $e) {
                        error_log("Error al enviar correo: " . $mail->ErrorInfo);
                    }
                }

                $mensaje = "Se ha enviado una notificación al administrador. Por favor, espere su aprobación.";
            }
        }
    } else {
        $error = "No se encontró ningún usuario con ese nickname.";
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
                    <h4 style="font-size: 1.2rem; color: #007bff;">Recuperar Contraseña</h4>
                </div>

                <?php if (isset($mensaje)): ?>
                    <div class="alert alert-success"><?php echo $mensaje; ?></div>
                <?php endif; ?>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <form method="post">
                    <div class="form-group">
                        <label for="nickname" style="color: #007bff;">Ingrese su Nickname:</label>
                        <input type="text" id="nickname" name="nickname" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block mt-3">Solicitar Recuperación</button>
                    <a href="login.php" class="btn btn-secondary btn-block mt-2">Volver al Login</a>
                </form>
            </div>
        </div>
    </div>
</body>
</html> 