<?php
// Asegurarnos de que no haya salida antes del header
ob_start();

// Configurar el manejo de errores para capturar todo
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '../logs/php_errors.log');

// Establecer el header de Content-Type antes de cualquier salida
header('Content-Type: application/json');

session_start();
require_once '../config/database.php';

// Crear un archivo de log específico para debugging
$logFile = '../logs/delete_debug.log';
file_put_contents($logFile, "=== Nueva solicitud de eliminación ===\n", FILE_APPEND);
file_put_contents($logFile, "Fecha: " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);

// Función para enviar respuesta JSON y terminar
function sendJsonResponse($data) {
    ob_clean();
    echo json_encode($data);
    exit();
}

// Verificar si el usuario está autenticado y es administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'Administrador') {
    file_put_contents($logFile, "Error: Usuario no autorizado\n", FILE_APPEND);
    sendJsonResponse(['error' => 'No tiene permisos para realizar esta acción']);
}

// Log de datos recibidos
file_put_contents($logFile, "POST data: " . print_r($_POST, true) . "\n", FILE_APPEND);

// Verificar si se recibió un ID
if (!isset($_POST['id'])) {
    file_put_contents($logFile, "Error: No se proporcionó un ID de horario\n", FILE_APPEND);
    sendJsonResponse(['error' => 'No se proporcionó un ID de horario']);
}

$id = trim($_POST['id']);

// Validar que el ID no esté vacío y sea numérico
if (empty($id) || !is_numeric($id)) {
    file_put_contents($logFile, "Error: ID de horario no válido\n", FILE_APPEND);
    sendJsonResponse(['error' => 'ID de horario no válido']);
}

// Convertir a entero
$id = intval($id);
file_put_contents($logFile, "ID a eliminar: " . $id . "\n", FILE_APPEND);

try {
    // Log de conexión
    file_put_contents($logFile, "Iniciando transacción\n", FILE_APPEND);
    
    // Iniciar transacción
    $pdo->beginTransaction();

    // Verificar si el horario existe antes de eliminarlo
    $stmtCheck = $pdo->prepare("SELECT id FROM horarios_trabajo WHERE id = ?");
    $stmtCheck->execute([$id]);
    
    $horario = $stmtCheck->fetch();
    file_put_contents($logFile, "Resultado de búsqueda: " . ($horario ? "Horario encontrado" : "Horario no encontrado") . "\n", FILE_APPEND);
    
    if (!$horario) {
        $pdo->rollBack();
        file_put_contents($logFile, "Error: Horario no encontrado en la base de datos\n", FILE_APPEND);
        sendJsonResponse(['error' => 'Horario no encontrado']);
    }

    // Proceder con la eliminación
    $stmtDelete = $pdo->prepare("DELETE FROM horarios_trabajo WHERE id = ?");
    $resultado = $stmtDelete->execute([$id]);
    
    file_put_contents($logFile, "Resultado de eliminación: " . ($resultado ? "Éxito" : "Fallo") . "\n", FILE_APPEND);
    file_put_contents($logFile, "Filas afectadas: " . $stmtDelete->rowCount() . "\n", FILE_APPEND);

    if ($resultado && $stmtDelete->rowCount() > 0) {
        $pdo->commit();
        file_put_contents($logFile, "Transacción completada con éxito\n", FILE_APPEND);
        sendJsonResponse([
            'success' => true,
            'message' => 'Horario eliminado correctamente',
            'id' => $id
        ]);
    } else {
        $pdo->rollBack();
        file_put_contents($logFile, "Error: No se pudo eliminar el horario\n", FILE_APPEND);
        sendJsonResponse(['error' => 'No se pudo eliminar el horario']);
    }
} catch (PDOException $e) {
    $pdo->rollBack();
    $errorMsg = "Error PDO: " . $e->getMessage() . "\n";
    file_put_contents($logFile, $errorMsg, FILE_APPEND);
    error_log($errorMsg);
    sendJsonResponse(['error' => 'Error en el servidor al procesar la solicitud']);
}

file_put_contents($logFile, "=== Fin de la solicitud ===\n\n", FILE_APPEND);
?>
