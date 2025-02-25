<?php
require_once '../config/database.php';
require_once 'includes/auth.php';

verificarAcceso();

// Obtener el mes y año seleccionados
$mes = isset($_GET['mes']) ? $_GET['mes'] : date('m');
$anio = isset($_GET['anio']) ? $_GET['anio'] : date('Y');

// Nombre del mes para el título
$nombres_meses = [
    '01' => 'Enero', '02' => 'Febrero', '03' => 'Marzo',
    '04' => 'Abril', '05' => 'Mayo', '06' => 'Junio',
    '07' => 'Julio', '08' => 'Agosto', '09' => 'Septiembre',
    '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre'
];
$nombre_mes = $nombres_meses[$mes];

// Obtener lista de usuarios
$stmt = $pdo->query("SELECT u.ID, u.nombre, u.cargo FROM usuarios u ORDER BY u.nombre");
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Configurar cabeceras para descarga de Excel
header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
header('Content-Disposition: attachment; filename="Reporte_Personal_' . $nombre_mes . '_' . $anio . '.xls"');
header('Pragma: no-cache');
header('Expires: 0');

// Crear el archivo Excel (formato HTML simple que Excel puede abrir)
?>
<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <!--[if gte mso 9]>
    <xml>
        <x:ExcelWorkbook>
            <x:ExcelWorksheets>
                <x:ExcelWorksheet>
                    <x:Name>Listado de Personal</x:Name>
                    <x:WorksheetOptions>
                        <x:DisplayGridlines/>
                    </x:WorksheetOptions>
                </x:ExcelWorksheet>
            </x:ExcelWorksheets>
        </x:ExcelWorkbook>
    </xml>
    <![endif]-->
    <style>
        table {border-collapse: collapse; width: 100%;}
        th, td {border: 1px solid #000000; padding: 5px; text-align: center;}
        th {background-color: #CCCCCC; font-weight: bold;}
        .title {font-size: 16pt; font-weight: bold; text-align: center; background-color: #EEEEEE; padding: 10px;}
        .subtitle {font-size: 14pt; font-weight: bold; text-align: center; background-color: #DDDDDD; padding: 5px;}
        .total {font-weight: bold; background-color: #EEEEEE;}
    </style>
</head>
<body>
    <div class="title">Reporte de Personal - <?php echo $nombre_mes . ' ' . $anio; ?></div>
    
    <table>
        <tr>
            <th>Nombre</th>
            <th>Cargo</th>
            <th>Total Horas Trabajadas</th>
            <th>Total Horas Extra</th>
            <th>Días de Vacaciones</th>
        </tr>
        <?php foreach ($usuarios as $usuario): 
            // Obtener total de horas trabajadas
            $stmt = $pdo->prepare("
                SELECT 
                    SUM(
                        TIMESTAMPDIFF(HOUR, hora_entrada, hora_salida)
                    ) as total_horas
                FROM horarios_trabajo 
                WHERE usuario_id = ? 
                AND YEAR(fecha) = ? 
                AND MONTH(fecha) = ?
                AND tipo != 'descanso'
            ");
            $stmt->execute([$usuario['ID'], $anio, $mes]);
            $horas_trabajadas = $stmt->fetch(PDO::FETCH_ASSOC);
            $total_horas = $horas_trabajadas['total_horas'] ?? 0;
            
            // Obtener total de horas extra
            $stmt = $pdo->prepare("
                SELECT 
                    SUM(horas_extra) as total_horas
                FROM hrex_empleado 
                WHERE usuario_id = ? 
                AND YEAR(fecha) = ? 
                AND MONTH(fecha) = ?
            ");
            $stmt->execute([$usuario['ID'], $anio, $mes]);
            $horas_extra = $stmt->fetch(PDO::FETCH_ASSOC);
            $total_horas_extra = $horas_extra['total_horas'] ?? 0;
            
            // Obtener días de vacaciones
            $stmt = $pdo->prepare("
                SELECT 
                    SUM(
                        DATEDIFF(fecha_fin, fecha_inicio) + 1
                    ) as total_dias
                FROM vacaciones 
                WHERE usuario_id = ? 
                AND YEAR(fecha_inicio) = ?
                AND estado_solicitud = 'Aprobado'
            ");
            $stmt->execute([$usuario['ID'], $anio]);
            $vacaciones = $stmt->fetch(PDO::FETCH_ASSOC);
            $total_dias_vacaciones = $vacaciones['total_dias'] ?? 0;
        ?>
        <tr>
            <td><?php echo htmlspecialchars($usuario['nombre']); ?></td>
            <td><?php echo htmlspecialchars($usuario['cargo']); ?></td>
            <td><?php echo number_format($total_horas, 2); ?></td>
            <td><?php echo number_format($total_horas_extra, 2); ?></td>
            <td><?php echo number_format($total_dias_vacaciones, 0); ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    
    <?php foreach ($usuarios as $usuario): ?>
    <br><br>
    <div class="subtitle">Detalles de <?php echo htmlspecialchars($usuario['nombre']); ?></div>
    
    <!-- Horario -->
    <div style="margin-top: 20px; margin-bottom: 10px; font-weight: bold;">HORARIO DE TRABAJO</div>
    <table>
        <tr>
            <th>Día</th>
            <th>Fecha</th>
            <th>Horario</th>
            <th>Total Horas</th>
        </tr>
        <?php
        // Obtener horario del mes
        $stmt = $pdo->prepare("
            SELECT 
                fecha,
                DAYOFWEEK(fecha) as dia_semana,
                hora_entrada, 
                hora_salida, 
                tipo 
            FROM horarios_trabajo 
            WHERE usuario_id = ? 
            AND YEAR(fecha) = ? 
            AND MONTH(fecha) = ?
            ORDER BY fecha
        ");
        $stmt->execute([$usuario['ID'], $anio, $mes]);
        $horarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Días de la semana
        $dias_semana = [
            1 => 'Domingo', 2 => 'Lunes', 3 => 'Martes', 
            4 => 'Miércoles', 5 => 'Jueves', 6 => 'Viernes', 7 => 'Sábado'
        ];
        
        // Crear un mapa de horarios para búsqueda rápida
        $horarioMap = [];
        foreach ($horarios as $h) {
            $horarioMap[date('j', strtotime($h['fecha']))] = $h;
        }
        
        // Generar filas para cada día del mes
        $diasEnMes = cal_days_in_month(CAL_GREGORIAN, $mes, $anio);
        $total_horas_mes = 0;
        
        for ($dia = 1; $dia <= $diasEnMes; $dia++) {
            $fecha = new DateTime("$anio-$mes-$dia");
            $diaSemana = $dias_semana[$fecha->format('N')];
            $horarioDia = isset($horarioMap[$dia]) ? $horarioMap[$dia] : null;
            
            // Calcular horas del día
            $horasDia = 0;
            $horarioTexto = '-';
            if ($horarioDia && $horarioDia['tipo'] !== 'descanso') {
                if ($horarioDia['hora_entrada'] && $horarioDia['hora_salida']) {
                    $entrada = new DateTime($horarioDia['hora_entrada']);
                    $salida = new DateTime($horarioDia['hora_salida']);
                    $horasDia = ($salida->getTimestamp() - $entrada->getTimestamp()) / 3600;
                    $horarioTexto = substr($horarioDia['hora_entrada'], 0, 5) . ' - ' . substr($horarioDia['hora_salida'], 0, 5);
                    $total_horas_mes += $horasDia;
                }
            }
        ?>
        <tr>
            <td><?php echo $diaSemana; ?></td>
            <td><?php echo sprintf('%02d/%02d/%04d', $dia, $mes, $anio); ?></td>
            <td><?php echo $horarioDia ? ($horarioDia['tipo'] === 'descanso' ? 'DESCANSO' : $horarioTexto) : '-'; ?></td>
            <td><?php echo $horarioDia && $horarioDia['tipo'] !== 'descanso' ? number_format($horasDia, 2) : '-'; ?></td>
        </tr>
        <?php } ?>
        <tr class="total">
            <td colspan="3">Total Horas Trabajadas</td>
            <td><?php echo number_format($total_horas_mes, 2); ?></td>
        </tr>
    </table>
    
    <!-- Vacaciones -->
    <div style="margin-top: 20px; margin-bottom: 10px; font-weight: bold;">VACACIONES APROBADAS</div>
    <table>
        <tr>
            <th>Período</th>
            <th>Días</th>
        </tr>
        <?php
        // Obtener vacaciones
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
        $stmt->execute([$usuario['ID'], $anio]);
        $vacaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $total_dias_vac = 0;
        if (count($vacaciones) > 0) {
            foreach ($vacaciones as $v) {
                $fecha_inicio = new DateTime($v['fecha_inicio']);
                $fecha_fin = new DateTime($v['fecha_fin']);
                $dias_vac = $fecha_fin->diff($fecha_inicio)->days + 1;
                $total_dias_vac += $dias_vac;
        ?>
        <tr>
            <td><?php echo $fecha_inicio->format('d/m/Y') . ' - ' . $fecha_fin->format('d/m/Y'); ?></td>
            <td><?php echo $dias_vac; ?></td>
        </tr>
        <?php } ?>
        <tr class="total">
            <td>Total</td>
            <td><?php echo $total_dias_vac; ?></td>
        </tr>
        <?php } else { ?>
        <tr>
            <td colspan="2">Sin vacaciones aprobadas</td>
        </tr>
        <?php } ?>
    </table>
    
    <!-- Horas Extra -->
    <div style="margin-top: 20px; margin-bottom: 10px; font-weight: bold;">HORAS EXTRA</div>
    <table>
        <tr>
            <th>Día</th>
            <th>Fecha</th>
            <th>Horas Extra</th>
        </tr>
        <?php
        // Obtener horas extra
        $stmt = $pdo->prepare("
            SELECT 
                fecha,
                horas_extra
            FROM hrex_empleado 
            WHERE usuario_id = ? 
            AND YEAR(fecha) = ? 
            AND MONTH(fecha) = ?
            ORDER BY fecha
        ");
        $stmt->execute([$usuario['ID'], $anio, $mes]);
        $horas_extra = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $total_horas_extra = 0;
        if (count($horas_extra) > 0) {
            foreach ($horas_extra as $he) {
                $fecha = new DateTime($he['fecha']);
                $diaSemana = $dias_semana[$fecha->format('N')];
                $total_horas_extra += $he['horas_extra'];
        ?>
        <tr>
            <td><?php echo $diaSemana; ?></td>
            <td><?php echo $fecha->format('d/m/Y'); ?></td>
            <td><?php echo number_format($he['horas_extra'], 2); ?></td>
        </tr>
        <?php } ?>
        <tr class="total">
            <td colspan="2">Total</td>
            <td><?php echo number_format($total_horas_extra, 2); ?></td>
        </tr>
        <?php } else { ?>
        <tr>
            <td colspan="3">Sin horas extra</td>
        </tr>
        <?php } ?>
    </table>
    <?php endforeach; ?>
</body>
</html> 