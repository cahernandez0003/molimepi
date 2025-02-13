<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

// Aquí puedes agregar lógica para exportar datos a PDF y Excel
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Exportación de Datos</title>
</head>
<body>
    <h1>Exportación de Datos</h1>
    <!-- Opciones para exportar datos -->
</body>
</html>