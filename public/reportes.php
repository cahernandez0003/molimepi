<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

// Aquí puedes agregar lógica para generar reportes
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reportes</title>
</head>
<body>
    <h1>Generación de Reportes</h1>
    <!-- Opciones para generar reportes -->
</body>
</html> 