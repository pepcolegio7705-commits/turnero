<?php
require_once '../../../core/config.php';

$desde = $_GET['desde'] ?? date('Y-m-d');
$hasta = $_GET['hasta'] ?? date('Y-m-d');
// Definimos el porcentaje de comisión (puedes pasarlo por $_GET si quieres que sea dinámico)
$porcentaje_comision = 0.20; // Ejemplo: 20%

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=auditoria_sintek_salud_'.$desde.'.csv');

$output = fopen('php://output', 'w');
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // Soporte para acentos

// Encabezados con las nuevas columnas de cálculo
fputcsv($output, [
    'Fecha Cobro', 
    'Médico', 
    'Paciente', 
    'Obra Social', 
    'Método Pago', 
    'Monto Total ($)', 
    'Comisión Clínica ($)', 
    'Neto Médico ($)', 
    'Nro Operación'
]);

$sql = "SELECT t.created_at, m.apellido as medico, p.apellido as paciente, 
               os.nombre as obra_social, t.tipo_pago, t.monto_cobrado, t.nro_operacion
        FROM turnos t
        INNER JOIN medicos m ON t.medico_id = m.id
        INNER JOIN pacientes p ON t.paciente_id = p.id
        LEFT JOIN obras_sociales os ON t.obra_social_id = os.id
        WHERE t.created_at BETWEEN ? AND ? AND t.estado = 'Atendido'
        ORDER BY t.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$desde . ' 00:00:00', $hasta . ' 23:59:59']);

$total_recaudado = 0;
$total_comisiones = 0;

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $monto = (float)$row['monto_cobrado'];
    $comision = $monto * $porcentaje_comision;
    $neto_medico = $monto - $comision;
    
    $total_recaudado += $monto;
    $total_comisiones += $comision;

    fputcsv($output, [
        $row['created_at'],
        $row['medico'],
        $row['paciente'],
        $row['obra_social'] ?? 'Particular',
        $row['tipo_pago'],
        number_format($monto, 2, '.', ''),
        number_format($comision, 2, '.', ''),
        number_format($neto_medico, 2, '.', ''),
        $row['nro_operacion']
    ]);
}

// Agregar fila de totales al final
fputcsv($output, []); // Fila vacía separadora
fputcsv($output, ['', '', '', '', 'TOTALES:', number_format($total_recaudado, 2, '.', ''), number_format($total_comisiones, 2, '.', ''), number_format($total_recaudado - $total_comisiones, 2, '.', ''), '']);

fclose($output);
exit;