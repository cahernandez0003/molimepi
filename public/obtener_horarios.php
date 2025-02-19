<?php
require_once '../config/database.php';

header('Content-Type: application/json');

try {
    // Si es una petición para el calendario completo, mostrar todos los horarios
    // Si es una petición específica (click en un día), mostrar solo ese día
    $fecha = isset($_GET['fecha']) ? $_GET['fecha'] : null;
    
    if ($fecha) {
        $stmt = $pdo->prepare("SELECT h.id, h.usuario_id, h.fecha, h.hora_entrada, h.hora_salida, h.tipo, h.horas_dia, u.nombre, u.ID as usuario_id 
                             FROM horarios_trabajo h
                             JOIN usuarios u ON h.usuario_id = u.ID
                             WHERE h.fecha = :fecha");
        $stmt->execute(['fecha' => $fecha]);
    } else {
        // Para el calendario, mostrar todos los horarios
        $stmt = $pdo->query("SELECT h.id, h.usuario_id, h.fecha, h.hora_entrada, h.hora_salida, h.tipo, h.horas_dia, u.nombre, u.ID as usuario_id 
                           FROM horarios_trabajo h
                           JOIN usuarios u ON h.usuario_id = u.ID");
    }
    $horarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $eventos = [];
    $colores = [
        'normal' => '#007bff',
        'descanso' => '#6c757d',
        'baja' => '#ffffff',
        'otros' => '#6c757d'
    ];

    $iconos = [
        'normal' => '',
        'descanso' => '🏠',
        'baja' => '🏥',
        'otros' => '⚠️'
    ];

    foreach ($horarios as $horario) {
        $tipo = $horario['tipo'] ?? 'normal';
        $color = $colores[$tipo] ?? '#6c757d';
        $icono = $iconos[$tipo] ?? '';
        
        $textColor = ($tipo === 'baja') ? '#dc3545' : null;

        $eventos[] = [
            'id' => $horario['id'],
            'title' => $icono . ' ' . $horario['nombre'],
            'start' => $horario['fecha'],
            'hora_entrada' => $horario['hora_entrada'],
            'hora_salida' => $horario['hora_salida'],
            'usuario_id' => $horario['usuario_id'],
            'tipo' => $tipo,
            'horas_dia' => $horario['horas_dia'],
            'allDay' => true,
            'color' => $color,
            'textColor' => $textColor,
            'extendedProps' => [
                'descripcion' => $tipo === 'normal' ? 
                    "Horario: " . $horario['hora_entrada'] . " - " . $horario['hora_salida'] :
                    "Tipo: " . ucfirst($tipo)
            ]
        ];
    }

    echo json_encode($eventos);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
