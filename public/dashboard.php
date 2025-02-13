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
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
</head>
<body>
    <h1>Bienvenido al Dashboard</h1>
    <p>Usuario: <?php echo $_SESSION['usuario_id']; ?></p>
    <a href="logout.php">Cerrar sesión</a>
</body>
</html>