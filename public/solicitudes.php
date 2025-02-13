<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

// Aquí puedes agregar lógica para enviar y gestionar solicitudes
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Solicitudes</title>
</head>
<body>
    <h1>Gestión de Solicitudes</h1>
    <!-- Formulario para enviar solicitudes -->
</body>
</html> 