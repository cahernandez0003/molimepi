<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

// Si se envía el formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nueva_password = $_POST['nueva_password'];
    $confirm_password = $_POST['confirm_password'];

    // Validar el formato de la contraseña en el servidor
    $tiene_mayuscula = preg_match('/[A-Z]/', $nueva_password);
    $tiene_minuscula = preg_match('/[a-z]/', $nueva_password);
    $tiene_numero = preg_match('/[0-9]/', $nueva_password);
    $tiene_simbolos = preg_match('/[^a-zA-Z0-9]/', $nueva_password);
    $longitud_valida = strlen($nueva_password) >= 6;

    $errores = [];
    if (!$longitud_valida) {
        $errores[] = "La contraseña debe tener al menos 6 caracteres";
    }
    if (!$tiene_mayuscula) {
        $errores[] = "La contraseña debe contener al menos una letra mayúscula";
    }
    if (!$tiene_minuscula) {
        $errores[] = "La contraseña debe contener al menos una letra minúscula";
    }
    if (!$tiene_numero) {
        $errores[] = "La contraseña debe contener al menos un número";
    }
    if ($tiene_simbolos) {
        $errores[] = "La contraseña no debe contener símbolos especiales";
    }
    if ($nueva_password !== $confirm_password) {
        $errores[] = "Las contraseñas no coinciden";
    }

    if (empty($errores)) {
        $hashed_password = password_hash($nueva_password, PASSWORD_BCRYPT);

        // Actualizar la contraseña y marcar cambio_password como 1
        $stmt = $pdo->prepare("UPDATE usuarios SET password = :password, cambio_password = 1 WHERE ID = :id");
        $stmt->execute([
            'password' => $hashed_password,
            'id' => $_SESSION['usuario_id']
        ]);

        header('Location: dashboard.php');
        exit();
    }
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
                        <h3 class="mb-0">Cambiar Contraseña</h3>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($errores)): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($errores as $error): ?>
                                        <li><?php echo $error; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <form method="post" id="formCambioPassword">
                            <div class="form-group">
                                <label>Nueva Contraseña</label>
                                <input type="password" name="nueva_password" id="nueva_password" class="form-control" required>
                                <small class="form-text text-muted">
                                    La contraseña debe tener:
                                    <ul>
                                        <li>Al menos 6 caracteres</li>
                                        <li>Al menos una letra mayúscula</li>
                                        <li>Al menos una letra minúscula</li>
                                        <li>Al menos un número</li>
                                        <li>No debe contener símbolos especiales</li>
                                    </ul>
                                </small>
                            </div>
                            <div class="form-group">
                                <label>Confirmar Contraseña</label>
                                <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block">Actualizar Contraseña</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="js/validaciones.js"></script>
    <script>
        document.getElementById('formCambioPassword').addEventListener('submit', function(e) {
            const password = document.getElementById('nueva_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            const validacion = validarPassword(password);
            
            if (!validacion.valido) {
                e.preventDefault();
                Swal.fire('Error', validacion.mensaje, 'error');
                return;
            }
            
            if (password !== confirmPassword) {
                e.preventDefault();
                Swal.fire('Error', 'Las contraseñas no coinciden', 'error');
                return;
            }
        });
    </script>
</body>
</html>
