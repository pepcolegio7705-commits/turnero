<?php
require_once '../../../core/config.php';
require_once '../../fpdf/fpdf.php';

// Parámetros
$desde = $_GET['desde'] ?? date('Y-m-d');
$hasta = $_GET['hasta'] ?? date('Y-m-d');
$comision_pct = isset($_GET['comision']) ? (float)$_GET['comision'] : 10;

// Función auxiliar para reemplazar utf8_decode (Compatible con PHP 8.2+)
function txt($texto) {
    if ($texto === null) return '';
    // Convertimos de UTF-8 a ISO-8859-1 para que FPDF lo entienda
    return mb_convert_encoding($texto, 'ISO-8859-1', 'UTF-8');
}

class PDF extends FPDF {
    function Header() {
        global $desde, $hasta;
        $this->SetFont('Arial', 'B', 15);
        $this->SetTextColor(33, 37, 41);
        // Usamos la función txt() para tildes y eñes
        $this->Cell(0, 10, txt('SINTEK SALUD - REPORTE DE AUDITORÍA'), 0, 1, 'C');
        $this->SetFont('Arial', '', 10);
        $this->Cell(0, 10, 'Periodo: ' . $desde . ' al ' . $hasta, 0, 1, 'C');
        $this->Ln(5);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, txt('Página ') . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
}

// Consulta de datos
$sql = "SELECT t.created_at, m.apellido as medico, p.apellido as paciente, 
               os.nombre as obra_social, t.tipo_pago, t.monto_cobrado
        FROM turnos t
        INNER JOIN medicos m ON t.medico_id = m.id
        INNER JOIN pacientes p ON t.paciente_id = p.id
        LEFT JOIN obras_sociales os ON t.obra_social_id = os.id
        WHERE t.created_at BETWEEN ? AND ? AND t.estado = 'Atendido'
        ORDER BY t.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$desde . ' 00:00:00', $hasta . ' 23:59:59']);
$datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 9);

// Encabezados de tabla con colores de Sintek
$pdf->SetFillColor(240, 240, 240);
$pdf->Cell(35, 8, 'Fecha', 1, 0, 'C', true);
$pdf->Cell(45, 8, txt('Médico'), 1, 0, 'C', true);
$pdf->Cell(45, 8, 'Paciente', 1, 0, 'C', true);
$pdf->Cell(30, 8, txt('Método'), 1, 0, 'C', true);
$pdf->Cell(35, 8, 'Monto', 1, 1, 'C', true);

$pdf->SetFont('Arial', '', 8);
$total_bruto = 0;

foreach ($datos as $row) {
    $monto = (float)$row['monto_cobrado'];
    $total_bruto += $monto;

    $pdf->Cell(35, 7, $row['created_at'], 1);
    $pdf->Cell(45, 7, txt($row['medico']), 1);
    $pdf->Cell(45, 7, txt($row['paciente']), 1);
    $pdf->Cell(30, 7, txt($row['tipo_pago']), 1);
    $pdf->Cell(35, 7, '$' . number_format($monto, 2, ',', '.'), 1, 1, 'R');
}

// Resumen Final
$pdf->Ln(10);
$comision_monto = $total_bruto * ($comision_pct / 100);
$neto_liquidar = $total_bruto - $comision_monto;

$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(125, 8, '', 0);
$pdf->Cell(35, 8, 'Total Bruto:', 0, 0, 'R');
$pdf->Cell(30, 8, '$' . number_format($total_bruto, 2, ',', '.'), 0, 1, 'R');

$pdf->SetTextColor(200, 0, 0);
$pdf->Cell(125, 8, '', 0);
$pdf->Cell(35, 8, txt('Comisión (') . $comision_pct . '%):', 0, 0, 'R');
$pdf->Cell(30, 8, '-$' . number_format($comision_monto, 2, ',', '.'), 0, 1, 'R');

$pdf->SetTextColor(0, 128, 0);
$pdf->SetFillColor(230, 255, 230);
$pdf->Cell(125, 10, '', 0);
$pdf->Cell(35, 10, 'Neto Sintek:', 1, 0, 'R', true);
$pdf->Cell(30, 10, '$' . number_format($neto_liquidar, 2, ',', '.'), 1, 1, 'R', true);

$pdf->Output('I', 'Reporte_Auditoria_Sintek.pdf');