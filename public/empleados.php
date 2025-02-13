<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'Administrador') {
    header('Location: login.php');
    exit();
}

// Aquí puedes agregar lógica para gestionar empleados
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Empleados</title>
</head>
<body>
    <h1>Gestión de Empleados</h1>
    <!-- Formulario y lista de empleados -->
</body>
</html> 