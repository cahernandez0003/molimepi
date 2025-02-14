<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

// Aquí puedes agregar lógica para mostrar información relevante en el dashboard
?>

<!DOCTYPE html>
<html lang="es">
<?php include 'head.php'; ?>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container mt-4">
        <div class="jumbotron">
            <h1 class="display-4">Bienvenido al Sistema</h1>
            <p class="lead">Panel de control para la gestión de empleados</p>
        </div>
    </div>
</body>
</html>