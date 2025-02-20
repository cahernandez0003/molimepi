<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'Administrador') {
    echo json_encode(['error' => 'No tiene permisos para realizar esta acción']);
    exit;
}

if (!isset($_POST['mesOrigen']) || !isset($_POST['mesDestino'])) {
    echo json_encode(['error' => 'Faltan datos obligatorios']);
    exit;
}

$mesOrigen = $_POST['mesOrigen'];
$mesDestino = $_POST['mesDestino'];

try {
    // Verificar si existen registros en el mes destino
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM horarios_trabajo 
                          WHERE DATE_FORMAT(fecha, '%Y-%m') = :mes_destino");
    $stmt->execute(['mes_destino' => $mesDestino]);
    $registrosDestino = $stmt->fetchColumn();

    if ($registrosDestino > 0) {
        echo json_encode(['error' => 'El mes destino ya tiene horarios registrados. Por favor, seleccione otro mes o elimine los horarios existentes.']);
        exit;
    }

    // Obtener los horarios del mes origen
    $stmt = $pdo->prepare("SELECT * FROM horarios_trabajo 
                          WHERE DATE_FORMAT(fecha, '%Y-%m') = :mes_origen");
    $stmt->execute(['mes_origen' => $mesOrigen]);
    $horarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($horarios)) {
        echo json_encode(['error' => 'No hay horarios para copiar en el mes seleccionado']);
        exit;
    }

    // Iniciar transacción
    $pdo->beginTransaction();

    // Obtener el primer día del mes destino
    $primerDiaDestino = new DateTime($mesDestino . '-01');
    
    foreach ($horarios as $horario) {
        // Obtener el día del mes del horario original
        $diaOriginal = (new DateTime($horario['fecha']))->format('d');
        
        // Crear la nueva fecha manteniendo el mismo día del mes
        $nuevaFecha = $primerDiaDestino->format('Y-m-') . $diaOriginal;
        
        // Verificar si la fecha es válida (por ejemplo, 31 de febrero)
        if (checkdate(
            $primerDiaDestino->format('m'),
            $diaOriginal,
            $primerDiaDestino->format('Y')
        )) {
            // Insertar el nuevo horario
            $stmt = $pdo->prepare("INSERT INTO horarios_trabajo 
                                  (usuario_id, fecha, hora_entrada, hora_salida, tipo, horas_dia) 
                                  VALUES (:usuario_id, :fecha, :hora_entrada, :hora_salida, :tipo, :horas_dia)");
            $stmt->execute([
                'usuario_id' => $horario['usuario_id'],
                'fecha' => $nuevaFecha,
                'hora_entrada' => $horario['hora_entrada'],
                'hora_salida' => $horario['hora_salida'],
                'tipo' => $horario['tipo'],
                'horas_dia' => $horario['horas_dia']
            ]);
        }
    }

    // Confirmar transacción
    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Horarios copiados correctamente'
    ]);

} catch (PDOException $e) {
    // Revertir transacción en caso de error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Error en copiar_horarios.php: " . $e->getMessage());
    echo json_encode(['error' => 'Error al copiar los horarios. Por favor, intente nuevamente']);
}
?> 