<?php
require_once '../config/database.php';
require_once 'includes/auth.php';

header('Content-Type: application/json');

verificarAccesoAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

try {
    $usuario_id = $_GET['usuario_id'];
    $anio = $_GET['anio'];

    $stmt = $pdo->prepare("
        SELECT SUM(DATEDIFF(fecha_fin, fecha_inicio) + 1) as dias_aprobados
        FROM vacaciones 
        WHERE usuario_id = :usuario_id 
        AND YEAR(fecha_inicio) = :anio
        AND estado_solicitud = 'Aprobado'
    ");
    
    $stmt->execute([
        'usuario_id' => $usuario_id,
        'anio' => $anio
    ]);
    
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    $dias_aprobados = (int)($resultado['dias_aprobados'] ?? 0);

    echo json_encode([
        'success' => true,
        'dias_aprobados' => $dias_aprobados
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} 