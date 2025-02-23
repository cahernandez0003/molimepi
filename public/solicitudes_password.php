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

// Procesar acciones (aprobar/rechazar)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $solicitud_id = $_POST['solicitud_id'];
    $accion = $_POST['accion'];
    $usuario_id = $_POST['usuario_id'];
    $comentario = $_POST['comentario'] ?? '';

    try {
        $pdo->beginTransaction();

        if ($accion === 'aprobar') {
            // Restablecer la contraseña a "123456" y marcar para cambio
            $nueva_password = password_hash("123456", PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("UPDATE usuarios SET password = :password, cambio_password = 0 
                                  WHERE ID = :id");
            $stmt->execute([
                'password' => $nueva_password,
                'id' => $usuario_id
            ]);
        }

        // Actualizar estado de la solicitud
        $stmt = $pdo->prepare("UPDATE solicitudes_password SET 
                              estado = :estado, 
                              fecha_actualizacion = NOW()
                              WHERE id = :solicitud_id");
        $stmt->execute([
            'estado' => ($accion === 'aprobar') ? 'Aprobada' : 'Rechazada',
            'solicitud_id' => $solicitud_id
        ]);

        // Obtener información del usuario
        $stmt = $pdo->prepare("SELECT u.* FROM usuarios u 
                              INNER JOIN solicitudes_password sp ON u.ID = sp.usuario_id 
                              WHERE sp.id = :solicitud_id");
        $stmt->execute(['solicitud_id' => $solicitud_id]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        // Crear notificación para el usuario
        $mensaje = ($accion === 'aprobar') 
            ? "Tu solicitud de restablecimiento de contraseña ha sido aprobada. Tu nueva contraseña temporal es: 123456" 
            : "Tu solicitud de restablecimiento de contraseña ha sido rechazada.";

        $stmt = $pdo->prepare("INSERT INTO notificaciones (usuario_id, tipo, mensaje, comentario, leida) 
                              VALUES (:usuario_id, 'solicitud_password', :mensaje, :comentario, 0)");
        $stmt->execute([
            'usuario_id' => $usuario_id,
            'mensaje' => $mensaje,
            'comentario' => $comentario
        ]);

        // Enviar correo al usuario
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
            $mail->addAddress($usuario['correo'], $usuario['nombre']);
            $mail->Subject = ($accion === 'aprobar') 
                ? 'Solicitud de Contraseña Aprobada' 
                : 'Solicitud de Contraseña Rechazada';

            $mensaje_correo = ($accion === 'aprobar')
                ? "<h2>¡Hola {$usuario['nombre']}!</h2>
                   <p>Tu solicitud de restablecimiento de contraseña ha sido aprobada.</p>
                   <p>Tu nueva contraseña temporal es: <strong>123456</strong></p>
                   <p>Por favor, inicia sesión y cambia tu contraseña inmediatamente.</p>"
                : "<h2>¡Hola {$usuario['nombre']}!</h2>
                   <p>Tu solicitud de restablecimiento de contraseña ha sido rechazada.</p>
                   <p>Si crees que esto es un error, por favor contacta con el administrador.</p>";

            $mail->isHTML(true);
            $mail->Body = $mensaje_correo;
            $mail->send();
        } catch (Exception $e) {
            error_log("Error al enviar correo: " . $mail->ErrorInfo);
        }

        $pdo->commit();
        $mensaje = "La solicitud ha sido " . ($accion === 'aprobar' ? 'aprobada' : 'rechazada') . " correctamente.";
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Error en la base de datos: " . $e->getMessage());
        $error = "Error al procesar la solicitud.";
    }
}

// Obtener todas las solicitudes pendientes
$stmt = $pdo->prepare("
    SELECT sp.*, u.nombre, u.nickname, u.correo,
           CASE 
               WHEN sp.estado = 'Pendiente' THEN 'warning'
               WHEN sp.estado = 'Aprobada' THEN 'success'
               ELSE 'danger'
           END as estado_clase
    FROM solicitudes_password sp
    INNER JOIN usuarios u ON sp.usuario_id = u.ID
    ORDER BY sp.fecha_solicitud DESC
");
$stmt->execute();
$solicitudes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<?php include 'head.php'; ?>
<body>
    <?php include 'navbar.php'; ?>
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h3 class="mb-0">
                            <i class="fas fa-key"></i> Solicitudes de Restablecimiento de Contraseña
                        </h3>
                    </div>
                    <div class="card-body">
                        <?php if (isset($mensaje)): ?>
                            <div class="alert alert-success"><?php echo $mensaje; ?></div>
                        <?php endif; ?>

                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <?php if (empty($solicitudes)): ?>
                            <div class="alert alert-info">No hay solicitudes de restablecimiento de contraseña.</div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Usuario</th>
                                            <th>Nickname</th>
                                            <th>Correo</th>
                                            <th>Estado</th>
                                            <th>Fecha Solicitud</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($solicitudes as $solicitud): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($solicitud['nombre']); ?></td>
                                                <td><?php echo htmlspecialchars($solicitud['nickname']); ?></td>
                                                <td><?php echo htmlspecialchars($solicitud['correo']); ?></td>
                                                <td>
                                                    <span class="badge badge-<?php echo $solicitud['estado_clase']; ?>">
                                                        <?php echo $solicitud['estado']; ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('d/m/Y H:i', strtotime($solicitud['fecha_solicitud'])); ?></td>
                                                <td>
                                                    <?php if ($solicitud['estado'] === 'Pendiente'): ?>
                                                        <form method="post" class="d-inline" onsubmit="return confirmarAccion(event, '<?php echo $accion; ?>');">
                                                            <input type="hidden" name="solicitud_id" value="<?php echo $solicitud['id']; ?>">
                                                            <input type="hidden" name="usuario_id" value="<?php echo $solicitud['usuario_id']; ?>">
                                                            <input type="hidden" name="accion" value="<?php echo $accion; ?>">
                                                            <input type="hidden" name="comentario" id="comentario_<?php echo $solicitud['id']; ?>">
                                                            <button type="submit" class="btn btn-<?php echo $accion === 'aprobar' ? 'success' : 'danger'; ?> btn-sm">
                                                                <i class="fas fa-<?php echo $accion === 'aprobar' ? 'check' : 'times'; ?>"></i> 
                                                                <?php echo ucfirst($accion); ?>
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    function confirmarAccion(event, accion) {
        event.preventDefault();
        const form = event.target;
        const solicitudId = form.querySelector('input[name="solicitud_id"]').value;

        Swal.fire({
            title: `¿${accion === 'aprobar' ? 'Aprobar' : 'Rechazar'} esta solicitud?`,
            text: "Por favor, agregue un comentario:",
            input: 'textarea',
            inputPlaceholder: 'Escriba un comentario...',
            showCancelButton: true,
            confirmButtonText: 'Confirmar',
            cancelButtonText: 'Cancelar',
            inputValidator: (value) => {
                if (!value) {
                    return 'Debe escribir un comentario';
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById(`comentario_${solicitudId}`).value = result.value;
                form.submit();
            }
        });

        return false;
    }
    </script>
</body>
</html> 