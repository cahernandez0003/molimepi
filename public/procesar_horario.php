<?php
session_start();
require_once '../config/database.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

// Verificar si el usuario está autenticado y es administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'Administrador') {
    echo json_encode(['error' => 'No tiene permisos para realizar esta acción']);
    exit;
}

// Verificar si los datos están presentes
if (!isset($_POST['fecha'], $_POST['usuario_id'], $_POST['hora_entrada'], $_POST['hora_salida'])) {
    echo json_encode(['error' => 'Faltan datos obligatorios']);
    exit;
}

// Capturar y validar datos del formulario
$fecha = trim($_POST['fecha']);
$usuario_id = trim($_POST['usuario_id']);
$hora_entrada = trim($_POST['hora_entrada']);
$hora_salida = trim($_POST['hora_salida']);

// Validaciones básicas
if (empty($fecha) || empty($usuario_id) || empty($hora_entrada) || empty($hora_salida)) {
    echo json_encode(['error' => 'Todos los campos son obligatorios']);
    exit;
}

try {
    // Verificar si ya existe un horario para ese empleado en esa fecha
    $stmt = $pdo->prepare("SELECT id FROM horarios_trabajo WHERE usuario_id = :usuario_id AND fecha = :fecha AND id != :horario_id");
    $stmt->execute([
        'usuario_id' => $usuario_id,
        'fecha' => $fecha,
        'horario_id' => isset($_POST['horario_id']) ? $_POST['horario_id'] : 0
    ]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['error' => 'Ya existe un horario para este empleado en esta fecha']);
        exit;
    }

    // Insertar o actualizar horario
    if (!empty($_POST['horario_id'])) {
        // Actualizar horario existente
        $stmt = $pdo->prepare("UPDATE horarios_trabajo SET 
                              usuario_id = :usuario_id,
                              fecha = :fecha,
                              hora_entrada = :hora_entrada,
                              hora_salida = :hora_salida
                              WHERE id = :id");
        $params = [
            'usuario_id' => $usuario_id,
            'fecha' => $fecha,
            'hora_entrada' => $hora_entrada,
            'hora_salida' => $hora_salida,
            'id' => $_POST['horario_id']
        ];
    } else {
        // Insertar nuevo horario
        $stmt = $pdo->prepare("INSERT INTO horarios_trabajo (usuario_id, fecha, hora_entrada, hora_salida) 
                              VALUES (:usuario_id, :fecha, :hora_entrada, :hora_salida)");
        $params = [
            'usuario_id' => $usuario_id,
            'fecha' => $fecha,
            'hora_entrada' => $hora_entrada,
            'hora_salida' => $hora_salida
        ];
    }
    
    $stmt->execute($params);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true, 
            'message' => !empty($_POST['horario_id']) ? 'Horario actualizado correctamente' : 'Horario creado correctamente'
        ]);
    } else {
        echo json_encode(['error' => 'No se realizaron cambios en el horario']);
    }
} catch (PDOException $e) {
    error_log("Error en procesar_horario.php: " . $e->getMessage());
    echo json_encode(['error' => 'Error al procesar el horario. Por favor, intente nuevamente']);
}
?>
