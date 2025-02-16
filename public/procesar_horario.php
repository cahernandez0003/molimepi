<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'Administrador') {
    header('Location: login.php');
    exit();
}

require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validar que todos los campos requeridos han sido enviados
    if (!isset($_POST['usuario_id'], $_POST['dia'], $_POST['hora_entrada'], $_POST['hora_salida'])) {
        die("Error: Faltan datos obligatorios.");
    }

    $usuario_id = $_POST['usuario_id'];
    $dia = $_POST['dia']; // Día de la semana
    $hora_entrada = $_POST['hora_entrada'];
    $hora_salida = $_POST['hora_salida'];

    try {
        // Insertar horario en la base de datos
        $stmt = $pdo->prepare("INSERT INTO horarios_trabajo (usuario_id, dia_semana, hora_entrada, hora_salida) 
                               VALUES (:usuario_id, :dia, :hora_entrada, :hora_salida)");
        $stmt->execute([
            'usuario_id'   => $usuario_id,
            'dia'          => $dia,
            'hora_entrada' => $hora_entrada,
            'hora_salida'  => $hora_salida
        ]);

        // Redirigir con mensaje de éxito
        $_SESSION['mensaje'] = "Horario asignado correctamente.";
        header('Location: horarios.php');
        exit();

    } catch (PDOException $e) {
        die("Error al guardar el horario: " . $e->getMessage());
    }
} else {
    die("Acceso no autorizado.");
}
