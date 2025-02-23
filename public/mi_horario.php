<?php
require_once '../config/database.php';
require_once 'includes/auth.php';

verificarAcceso();
$usuario_id = obtenerIdUsuario();

// Obtener el horario asignado del empleado
$sql = "SELECT h.*, u.nombre as nombre_usuario 
        FROM horarios h 
        JOIN usuarios u ON h.usuario_id = u.ID 
        WHERE h.usuario_id = :usuario_id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['usuario_id' => $usuario_id]);
$horario = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Horario - MoliMepi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container mt-4">
        <h2>Mi Horario</h2>

        <?php if ($horario): ?>
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Horario Asignado</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Día</th>
                                    <th>Entrada</th>
                                    <th>Salida</th>
                                    <th>Total Horas</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
                                foreach ($dias as $dia):
                                    $dia_lower = strtolower($dia);
                                    if (isset($horario[$dia_lower . '_entrada']) && $horario[$dia_lower . '_entrada']):
                                ?>
                                <tr>
                                    <td><?php echo $dia; ?></td>
                                    <td><?php echo date('H:i', strtotime($horario[$dia_lower . '_entrada'])); ?></td>
                                    <td><?php echo date('H:i', strtotime($horario[$dia_lower . '_salida'])); ?></td>
                                    <td>
                                        <?php
                                        $entrada = strtotime($horario[$dia_lower . '_entrada']);
                                        $salida = strtotime($horario[$dia_lower . '_salida']);
                                        $diferencia = $salida - $entrada;
                                        echo number_format($diferencia / 3600, 2);
                                        ?> horas
                                    </td>
                                </tr>
                                <?php endif; endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> No tienes un horario asignado aún. Por favor, contacta con tu administrador.
            </div>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 