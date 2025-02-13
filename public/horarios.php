<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'Administrador') {
    header('Location: login.php');
    exit();
}

// Aquí puedes agregar lógica para crear y gestionar horarios
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Planificación de Horarios</title>
</head>
<body>
    <h1>Planificación de Horarios</h1>
    <!-- Formulario para crear horarios semanales -->
</body>
</html> 