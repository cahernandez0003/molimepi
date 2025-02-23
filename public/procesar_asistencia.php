<?php
require_once '../config/database.php';
require_once 'includes/auth.php';

header('Content-Type: application/json');

verificarAcceso();
$usuario_id = obtenerIdUsuario();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit();
}

try {
    $fecha = $_POST['fecha'] ?? date('Y-m-d');
    $hora_entrada = $_POST['hora_entrada'] ?? null;
    $hora_salida = $_POST['hora_salida'] ?? null;
    $registro_id = $_POST['id'] ?? null; // ID para edición
    
    if (!$hora_entrada) {
        throw new Exception('Debe ingresar la hora de entrada');
    }

    // Verificar si ya existe un registro para la fecha seleccionada (excepto el mismo registro en caso de edición)
    $sql = "SELECT * FROM registro_asistencia 
            WHERE usuario_id = :usuario_id 
            AND fecha = :fecha";
    $params = ['usuario_id' => $usuario_id, 'fecha' => $fecha];
    
    if ($registro_id) {
        $sql .= " AND id != :registro_id";
        $params['registro_id'] = $registro_id;
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $registro_existente = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($registro_existente && !$registro_id) {
        throw new Exception('Ya existe un registro para la fecha seleccionada');
    }

    // Calcular total de horas si se proporcionó hora de salida
    $total_horas = null;
    if ($hora_entrada && $hora_salida) {
        $entrada = strtotime($hora_entrada);
        $salida = strtotime($hora_salida);
        $diferencia = $salida - $entrada;
        if ($diferencia < 0) {
            $diferencia += 24 * 3600; // Si la salida es al día siguiente
        }
        $total_horas = round($diferencia / 3600, 2);
    }
    
    if ($registro_id) {
        // Actualizar registro existente
        $sql = "UPDATE registro_asistencia 
                SET fecha = :fecha,
                    hora_entrada = :hora_entrada,
                    hora_salida = :hora_salida,
                    total_horas = :total_horas 
                WHERE id = :id AND usuario_id = :usuario_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'fecha' => $fecha,
            'hora_entrada' => $hora_entrada,
            'hora_salida' => $hora_salida,
            'total_horas' => $total_horas,
            'id' => $registro_id,
            'usuario_id' => $usuario_id
        ]);
        
        if ($stmt->rowCount() === 0) {
            throw new Exception('No se encontró el registro o no tienes permiso para editarlo');
        }
        
        echo json_encode([
            'success' => true,
            'mensaje' => 'Registro actualizado correctamente'
        ]);
    } else {
        // Crear nuevo registro
        $sql = "INSERT INTO registro_asistencia 
                (usuario_id, fecha, hora_entrada, hora_salida, total_horas, creado_en) 
                VALUES 
                (:usuario_id, :fecha, :hora_entrada, :hora_salida, :total_horas, CURRENT_TIMESTAMP)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'usuario_id' => $usuario_id,
            'fecha' => $fecha,
            'hora_entrada' => $hora_entrada,
            'hora_salida' => $hora_salida,
            'total_horas' => $total_horas
        ]);
        
        echo json_encode([
            'success' => true,
            'mensaje' => 'Registro creado correctamente'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?> 