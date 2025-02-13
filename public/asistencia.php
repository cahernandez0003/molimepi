<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

// Aquí puedes agregar lógica para registrar la entrada y salida
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Asistencia</title>
</head>
<body>
    <h1>Registro de Asistencia</h1>
    <!-- Formulario para marcar entrada y salida -->
</body>
</html> 