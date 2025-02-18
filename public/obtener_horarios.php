<?php
require_once '../config/database.php';

header('Content-Type: application/json');

try {
    // Si es una petición para el calendario completo, mostrar todos los horarios
    // Si es una petición específica (click en un día), mostrar solo ese día
    $fecha = isset($_GET['fecha']) ? $_GET['fecha'] : null;
    
    if ($fecha) {
        $stmt = $pdo->prepare("SELECT h.id, h.usuario_id, h.fecha, h.hora_entrada, h.hora_salida, u.nombre 
                             FROM horarios_trabajo h
                             JOIN usuarios u ON h.usuario_id = u.ID
                             WHERE h.fecha = :fecha");
        $stmt->execute(['fecha' => $fecha]);
    } else {
        // Para el calendario, mostrar todos los horarios
        $stmt = $pdo->query("SELECT h.id, h.usuario_id, h.fecha, h.hora_entrada, h.hora_salida, u.nombre 
                           FROM horarios_trabajo h
                           JOIN usuarios u ON h.usuario_id = u.ID");
    }
    $horarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $eventos = [];
    $colores = ['#007bff', '#28a745', '#dc3545', '#ffc107', '#17a2b8', '#6610f2']; 
    $contador = 0;

    foreach ($horarios as $horario) {
        $color = $colores[$contador % count($colores)];

        $eventos[] = [
            'id' => $horario['id'],
            'title' => $horario['nombre'],
            'start' => $horario['fecha'],
            'hora_entrada' => $horario['hora_entrada'],
            'hora_salida' => $horario['hora_salida'],
            'allDay' => true,
            'color' => $color,
            'extendedProps' => [
                'descripcion' => "Horario: " . $horario['hora_entrada'] . " - " . $horario['hora_salida']
            ]
        ];
        $contador++;
    }

    echo json_encode($eventos);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
