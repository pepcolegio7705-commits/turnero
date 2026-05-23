<?php
// 1. Configuración de errores para PHP 8
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 2. Importación de dependencias
require('admin/fpdf/fpdf.php'); 
require('core/config.php'); 

$id_turno = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id_turno === 0) die("ID de turno no válido.");

/**
 * Función auxiliar para manejar la codificación en PHP 8
 * Reemplaza al antiguo utf8_decode()
 */
function toISO($text) {
    return mb_convert_encoding($text, 'ISO-8859-1', 'UTF-8');
}

try {
    // 3. Consulta con PDO
    $sql = "SELECT t.id, t.fecha, t.hora, 
                   p.nombre as pac_nom, p.apellido as pac_ape, p.dni,
                   m.nombre as med_nom, m.apellido as med_ape
            FROM turnos t
            INNER JOIN pacientes p ON t.paciente_id = p.id
            INNER JOIN medicos m ON t.medico_id = m.id
            WHERE t.id = :id LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id_turno, PDO::PARAM_INT);
    $stmt->execute();
    
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$data) die("El turno no existe.");

} catch (PDOException $e) {
    die("Error crítico de base de datos: " . $e->getMessage());
}

// 4. Generación del PDF
$pdf = new FPDF('P', 'mm', array(80, 160)); 
$pdf->AddPage();
$pdf->SetMargins(5, 5, 5);

// Logo
$ruta_logo = 'assets/img/staff/logo.png';
if (file_exists($ruta_logo)) {
    $pdf->Image($ruta_logo, 25, 10, 30); 
    $pdf->Ln(25);
} else {
    $pdf->Ln(5);
}

// Título
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(70, 8, toISO('COMPROBANTE DE TURNO'), 0, 1, 'C');
$pdf->Ln(2);

// Datos Paciente
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(70, 5, 'PACIENTE:', 0, 1);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(70, 5, toISO($data['pac_ape'] . ', ' . $data['pac_nom']), 0, 1);
$pdf->Cell(70, 5, 'DNI: ' . $data['dni'], 0, 1);
$pdf->Ln(3);

// Datos Médico
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(70, 5, toISO('MÉDICO:'), 0, 1);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(70, 5, toISO($data['med_ape'] . ' ' . $data['med_nom']), 0, 1);
$pdf->Ln(5);

// Bloque de Tiempo (Resaltado)
$pdf->SetFillColor(240, 240, 240);
$pdf->SetFont('Arial', 'B', 11);

// Formateo de fecha y hora seguro para PHP 8
$fecha_formateada = date('d/m/Y', strtotime($data['fecha']));
$hora_formateada = date('H:i', strtotime($data['hora']));

$pdf->Cell(70, 10, 'FECHA: ' . $fecha_formateada, 1, 1, 'C', true);
$pdf->Cell(70, 10, 'HORA: ' . $hora_formateada . ' hs', 1, 1, 'C', true);

$pdf->Ln(10);
$pdf->SetFont('Arial', '', 8);
$pdf->MultiCell(70, 4, toISO("Por favor, presente este ticket en recepción.\nConserve este comprobante."), 0, 'C');

// 5. Limpieza de salida (Crucial en PHP 8 para evitar errores de headers)
if (ob_get_length()) ob_end_clean();

$pdf->Output('I', 'Ticket_Turno_'.$id_turno.'.pdf');