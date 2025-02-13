<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $correo = $_POST['correo'];
    $contraseña = md5($_POST['contraseña']); // Encriptar la contraseña ingresada

    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE correo = :correo");
    $stmt->execute(['correo' => $correo]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario && $contraseña === $usuario['contraseña']) { // Comparar el hash
        $_SESSION['usuario_id'] = $usuario['ID'];
        $_SESSION['rol'] = $usuario['rol'];
        header('Location: dashboard.php');
        exit();
    } else {
        $error = "Correo o contraseña incorrectos.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <!-- Incluir Bootstrap CSS desde CDN -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Incluir jQuery desde CDN -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <!-- Incluir Bootstrap JS desde CDN -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>
<body>
    <div class="container">
        <h2 class="mt-5">Iniciar Sesión</h2>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="post" action="">
            <div class="form-group">
                <label for="correo">Correo:</label>
                <input type="email" id="correo" name="correo" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="contraseña">Contraseña:</label>
                <input type="password" id="contraseña" name="contraseña" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Iniciar Sesión</button>
        </form>
    </div>
</body>
</html>