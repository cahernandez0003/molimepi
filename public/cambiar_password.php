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

    if ($nueva_password === $confirm_password) {
        $hashed_password = password_hash($nueva_password, PASSWORD_BCRYPT);

        // Actualizar la contraseña y marcar cambio_password como 1
        $stmt = $pdo->prepare("UPDATE usuarios SET password = :password, cambio_password = 1 WHERE ID = :id");
        $stmt->execute([
            'password' => $hashed_password,
            'id' => $_SESSION['usuario_id']
        ]);

        header('Location: dashboard.php'); // Redirigir después del cambio de contraseña
        exit();
    } else {
        $error = "Las contraseñas no coinciden.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<?php include 'head.php'; ?>
<body>
    <div class="container mt-5">
        <h2>Cambiar Contraseña</h2>
        <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
        <form method="post">
            <div class="form-group">
                <label>Nueva Contraseña</label>
                <input type="password" name="nueva_password" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Confirmar Contraseña</label>
                <input type="password" name="confirm_password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Actualizar Contraseña</button>
        </form>
    </div>
</body>
</html>
