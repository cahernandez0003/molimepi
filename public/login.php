<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $correo = $_POST['correo'];
    $password = md5($_POST['password']); // Encriptar la password ingresada

    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE correo = :correo");
    $stmt->execute(['correo' => $correo]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario && $password === $usuario['password']) { // Comparar el hash
        $_SESSION['usuario_id'] = $usuario['ID'];
        $_SESSION['rol'] = $usuario['rol'];
        header('Location: perfil.php'); // Redirigir al perfil del usuario
        exit();
    } else {
        $error = "Correo o password incorrectos.";
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
                        <label for="correo" style="color: #007bff;">Correo:</label>
                        <input type="email" id="correo" name="correo" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="password" style="color: #007bff;">password:</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block mt-3">Iniciar Sesión</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>