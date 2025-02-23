<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'Administrador') {
    echo json_encode(['success' => false, 'error' => 'No tiene permisos para realizar esta acción']);
    exit;
}

// Validar datos requeridos
if (!isset($_POST['usuario_id']) || !isset($_POST['fecha']) || !isset($_POST['tipo'])) {
    echo json_encode(['success' => false, 'error' => 'Faltan datos obligatorios']);
    exit;
}

$horario_id = $_POST['horario_id'] ?? null;
$usuario_id = $_POST['usuario_id'];
$fecha = $_POST['fecha'];
$tipo = $_POST['tipo'];
$hora_entrada = ($tipo === 'normal' && isset($_POST['hora_entrada'])) ? $_POST['hora_entrada'] : null;
$hora_salida = ($tipo === 'normal' && isset($_POST['hora_salida'])) ? $_POST['hora_salida'] : null;

try {
    // Verificar si ya existe un horario para ese usuario y fecha
    $stmt = $pdo->prepare("SELECT id FROM horarios_trabajo 
                          WHERE usuario_id = :usuario_id 
                          AND fecha = :fecha
                          AND id != :horario_id");
    $stmt->execute([
        'usuario_id' => $usuario_id,
        'fecha' => $fecha,
        'horario_id' => $horario_id ?? 0
    ]);
    
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'error' => 'Ya existe un horario para este empleado en la fecha seleccionada']);
        exit;
    }

    // Calcular horas trabajadas si es horario normal
    $horas_dia = 0;
    if ($tipo === 'normal' && $hora_entrada && $hora_salida) {
        $entrada = new DateTime($hora_entrada);
        $salida = new DateTime($hora_salida);
        $diferencia = $entrada->diff($salida);
        $horas_dia = $diferencia->h + ($diferencia->i / 60);
    }

    if ($horario_id) {
        // Actualizar horario existente
        $stmt = $pdo->prepare("UPDATE horarios_trabajo 
                              SET usuario_id = :usuario_id,
                                  fecha = :fecha,
                                  hora_entrada = :hora_entrada,
                                  hora_salida = :hora_salida,
                                  tipo = :tipo,
                                  horas_dia = :horas_dia
                              WHERE id = :id");
        $params = [
            'usuario_id' => $usuario_id,
            'fecha' => $fecha,
            'hora_entrada' => $hora_entrada,
            'hora_salida' => $hora_salida,
            'tipo' => $tipo,
            'horas_dia' => $horas_dia,
            'id' => $horario_id
        ];
    } else {
        // Insertar nuevo horario
        $stmt = $pdo->prepare("INSERT INTO horarios_trabajo 
                              (usuario_id, fecha, hora_entrada, hora_salida, tipo, horas_dia)
                              VALUES 
                              (:usuario_id, :fecha, :hora_entrada, :hora_salida, :tipo, :horas_dia)");
        $params = [
            'usuario_id' => $usuario_id,
            'fecha' => $fecha,
            'hora_entrada' => $hora_entrada,
            'hora_salida' => $hora_salida,
            'tipo' => $tipo,
            'horas_dia' => $horas_dia
        ];
    }

    $stmt->execute($params);

    echo json_encode([
        'success' => true,
        'message' => $horario_id ? 'Horario actualizado correctamente' : 'Horario guardado correctamente'
    ]);

} catch (PDOException $e) {
    error_log("Error en guardar_horario.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Error al guardar el horario. Por favor, intente nuevamente'
    ]);
} 