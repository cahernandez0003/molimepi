<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

try {
    $params = [];
    $sql = "SELECT h.*, u.nombre as title 
            FROM horarios_trabajo h 
            INNER JOIN usuarios u ON h.usuario_id = u.ID 
            WHERE 1=1";

    // Si se proporciona una fecha específica
    if (isset($_GET['fecha'])) {
        $sql .= " AND h.fecha = :fecha";
        $params['fecha'] = $_GET['fecha'];
    }

    // Si es empleado, solo mostrar sus horarios
    if ($_SESSION['rol'] !== 'Administrador') {
        $sql .= " AND h.usuario_id = :usuario_id";
        $params['usuario_id'] = $_SESSION['usuario_id'];
    }

    $sql .= " ORDER BY h.fecha ASC, h.hora_entrada ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $horarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Formatear los horarios para FullCalendar
    $eventos = array_map(function($horario) {
        $color = '';
        $textColor = '#000';
        
        switch($horario['tipo']) {
            case 'normal':
                $color = '#28a745'; // Verde
                $textColor = '#fff';
                break;
            case 'descanso':
                $color = '#ffc107'; // Amarillo
                break;
            case 'baja':
                $color = '#dc3545'; // Rojo
                $textColor = '#fff';
                break;
            case 'otros':
                $color = '#17a2b8'; // Azul claro
                $textColor = '#fff';
                break;
        }

        return [
            'id' => $horario['id'],
            'title' => $horario['title'],
            'start' => $horario['fecha'],
            'hora_entrada' => $horario['hora_entrada'],
            'hora_salida' => $horario['hora_salida'],
            'tipo' => $horario['tipo'],
            'horas_dia' => $horario['horas_dia'],
            'usuario_id' => $horario['usuario_id'],
            'backgroundColor' => $color,
            'textColor' => $textColor,
            'allDay' => true
        ];
    }, $horarios);

    echo json_encode($eventos);

} catch (PDOException $e) {
    error_log("Error en obtener_horarios.php: " . $e->getMessage());
    echo json_encode(['error' => 'Error al obtener los horarios']);
}
?>
