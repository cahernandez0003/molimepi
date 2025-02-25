<?php
require_once '../config/database.php';
require_once 'includes/auth.php';

header('Content-Type: application/json');
verificarAcceso();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

try {
    $usuario_id = $_GET['usuario_id'];
    $mes = $_GET['mes'] ?? date('m');
    $anio = $_GET['anio'] ?? date('Y');

    // 1. Información básica del usuario (de usuarios)
    $stmt = $pdo->prepare("SELECT nombre, cargo, imagen FROM usuarios WHERE ID = ?");
    $stmt->execute([$usuario_id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        throw new Exception('Usuario no encontrado');
    }

    // Corregir ruta de imagen
    if (!empty($usuario['imagen'])) {
        // Si la imagen ya tiene el prefijo 'imgs/', no lo añadimos de nuevo
        if (strpos($usuario['imagen'], 'imgs/') === 0) {
            $imagen = $usuario['imagen'];
        } else if (strpos($usuario['imagen'], 'public/imgs/') === 0) {
            // Si tiene el prefijo 'public/imgs/', lo reemplazamos por 'imgs/'
            $imagen = str_replace('public/imgs/', 'imgs/', $usuario['imagen']);
        } else {
            // En cualquier otro caso, asumimos que necesita el prefijo 'imgs/'
            $imagen = 'imgs/' . basename($usuario['imagen']);
        }
    } else {
        $imagen = 'imgs/nofoto.png';
    }

    // 2. Horario del mes (de horarios_trabajo)
    $stmt = $pdo->prepare("
        SELECT 
            DAYOFWEEK(fecha) as dia, 
            fecha,
            hora_entrada, 
            hora_salida, 
            tipo 
        FROM horarios_trabajo 
        WHERE usuario_id = ? 
        AND YEAR(fecha) = ? 
        AND MONTH(fecha) = ?
        ORDER BY fecha
    ");
    $stmt->execute([$usuario_id, $anio, $mes]);
    $horarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $dias_semana = [
        1 => 'DOMINGO',
        2 => 'LUNES', 
        3 => 'MARTES', 
        4 => 'MIÉRCOLES', 
        5 => 'JUEVES', 
        6 => 'VIERNES', 
        7 => 'SÁBADO'
    ];

    $horario_formateado = [];
    foreach ($horarios as $h) {
        $dia_semana = $dias_semana[date('N', strtotime($h['fecha']))];
        $dia_numero = date('j', strtotime($h['fecha']));
        
        if ($h['tipo'] === 'descanso') {
            $horario_formateado[] = [
                'dia_semana' => $dia_semana,
                'dia_numero' => $dia_numero,
                'horario' => 'DESCANSO'
            ];
        } else {
            $horario_formateado[] = [
                'dia_semana' => $dia_semana,
                'dia_numero' => $dia_numero,
                'horario' => 
                    ($h['hora_entrada'] ? substr($h['hora_entrada'], 0, 5) : '') . 
                    ' - ' . 
                    ($h['hora_salida'] ? substr($h['hora_salida'], 0, 5) : '')
            ];
        }
    }

    // 3. Vacaciones del año (solo aprobadas)
    $stmt = $pdo->prepare("
        SELECT 
            fecha_inicio, 
            fecha_fin
        FROM vacaciones 
        WHERE usuario_id = ? 
        AND YEAR(fecha_inicio) = ?
        AND estado_solicitud = 'Aprobado'
        ORDER BY fecha_inicio DESC
    ");
    $stmt->execute([$usuario_id, $anio]);
    $vacaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 4. Horas extra del mes (sumar y mostrar)
    $stmt = $pdo->prepare("
        SELECT 
            SUM(horas_extra) as total_horas,
            GROUP_CONCAT(
                CONCAT(
                    DATE_FORMAT(fecha, '%d/%m/%Y'), 
                    ': ', 
                    horas_extra, 
                    ' hrs'
                ) 
                SEPARATOR '; '
            ) as detalle_horas
        FROM hrex_empleado 
        WHERE usuario_id = ? 
        AND YEAR(fecha) = ? 
        AND MONTH(fecha) = ?
    ");
    $stmt->execute([$usuario_id, $anio, $mes]);
    $horas_extra = $stmt->fetch(PDO::FETCH_ASSOC);

    // Formatear fechas de vacaciones
    foreach ($vacaciones as &$v) {
        $v['fecha_inicio'] = date('d/m/Y', strtotime($v['fecha_inicio']));
        $v['fecha_fin'] = date('d/m/Y', strtotime($v['fecha_fin']));
    }

    echo json_encode([
        'success' => true,
        'data' => [
            'nombre' => $usuario['nombre'],
            'cargo' => $usuario['cargo'],
            'imagen' => $imagen,
            'horario' => $horario_formateado,
            'vacaciones' => $vacaciones,
            'horas_extra' => [
                'total' => $horas_extra['total_horas'] ?? 0,
                'detalle' => $horas_extra['detalle_horas'] ?? 'Sin horas extra'
            ]
        ]
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} 