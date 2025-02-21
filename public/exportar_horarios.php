<?php
session_start();
require_once '../config/database.php';
require '../vendor/autoload.php'; // Composer autoload

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Dompdf\Dompdf;
use Dompdf\Options;

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

// Función para obtener los horarios
function obtenerHorarios($pdo, $mes = null) {
    $sql = "SELECT ht.*, u.nombre, u.identificacion, u.cargo 
            FROM horarios_trabajo ht 
            INNER JOIN usuarios u ON ht.usuario_id = u.ID 
            WHERE 1=1";
    
    $params = [];
    if ($mes) {
        $sql .= " AND DATE_FORMAT(ht.fecha, '%Y-%m') = :mes";
        $params['mes'] = $mes;
    }
    
    $sql .= " ORDER BY ht.fecha ASC, u.nombre ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Exportar a Excel
if (isset($_GET['formato']) && $_GET['formato'] === 'excel') {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Establecer encabezados
    $sheet->setCellValue('A1', 'Fecha');
    $sheet->setCellValue('B1', 'Empleado');
    $sheet->setCellValue('C1', 'Identificación');
    $sheet->setCellValue('D1', 'Cargo');
    $sheet->setCellValue('E1', 'Hora Entrada');
    $sheet->setCellValue('F1', 'Hora Salida');
    $sheet->setCellValue('G1', 'Tipo');
    $sheet->setCellValue('H1', 'Horas');

    // Estilo para encabezados
    $sheet->getStyle('A1:H1')->getFont()->setBold(true);
    $sheet->getStyle('A1:H1')->getFill()
          ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
          ->getStartColor()->setARGB('CCCCCC');

    // Obtener y escribir datos
    $horarios = obtenerHorarios($pdo, $_GET['mes'] ?? null);
    $row = 2;
    foreach ($horarios as $horario) {
        $sheet->setCellValue('A' . $row, date('d/m/Y', strtotime($horario['fecha'])));
        $sheet->setCellValue('B' . $row, $horario['nombre']);
        $sheet->setCellValue('C' . $row, $horario['identificacion']);
        $sheet->setCellValue('D' . $row, $horario['cargo']);
        $sheet->setCellValue('E' . $row, $horario['hora_entrada']);
        $sheet->setCellValue('F' . $row, $horario['hora_salida']);
        $sheet->setCellValue('G' . $row, ucfirst($horario['tipo']));
        $sheet->setCellValue('H' . $row, $horario['horas_dia']);
        $row++;
    }

    // Autoajustar columnas
    foreach (range('A', 'H') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    // Generar archivo Excel
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="Horarios_MOLIMEPI.xlsx"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}

// Exportar a PDF
if (isset($_GET['formato']) && $_GET['formato'] === 'pdf') {
    $horarios = obtenerHorarios($pdo, $_GET['mes'] ?? null);
    
    $html = '
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; }
            table { width: 100%; border-collapse: collapse; margin-top: 10px; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; font-size: 12px; }
            th { background-color: #f2f2f2; }
            .header { text-align: center; margin-bottom: 20px; }
            .header img { max-width: 100px; }
            h1 { color: #333; font-size: 24px; }
        </style>
    </head>
    <body>
        <div class="header">
            <img src="imgs/molimepi.png" alt="MOLIMEPI">
            <h1>Reporte de Horarios</h1>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Empleado</th>
                    <th>Identificación</th>
                    <th>Cargo</th>
                    <th>Entrada</th>
                    <th>Salida</th>
                    <th>Tipo</th>
                    <th>Horas</th>
                </tr>
            </thead>
            <tbody>';
    
    foreach ($horarios as $horario) {
        $html .= '<tr>
            <td>' . date('d/m/Y', strtotime($horario['fecha'])) . '</td>
            <td>' . htmlspecialchars($horario['nombre']) . '</td>
            <td>' . htmlspecialchars($horario['identificacion']) . '</td>
            <td>' . htmlspecialchars($horario['cargo']) . '</td>
            <td>' . $horario['hora_entrada'] . '</td>
            <td>' . $horario['hora_salida'] . '</td>
            <td>' . ucfirst($horario['tipo']) . '</td>
            <td>' . $horario['horas_dia'] . '</td>
        </tr>';
    }
    
    $html .= '</tbody></table></body></html>';

    // Configurar DOMPDF
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isPhpEnabled', true);

    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'landscape');
    $dompdf->render();

    // Generar archivo PDF
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="Horarios_MOLIMEPI.pdf"');
    echo $dompdf->output();
    exit;
} 