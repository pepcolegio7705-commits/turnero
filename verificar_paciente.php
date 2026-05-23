<?php
include "core/config.php";
header('Content-Type: application/json');

$dni = $_GET['dni'] ?? '';
$res = ['existe' => false, 'nombre' => '', 'apellido' => ''];

if(!empty($dni)) {
    $stmt = $pdo->prepare("SELECT nombre, apellido FROM pacientes WHERE dni = ? LIMIT 1");
    $stmt->execute([$dni]);
    $paciente = $stmt->fetch(PDO::FETCH_ASSOC);

    if($paciente) {
        $res = [
            'existe' => true,
            'nombre' => $paciente['nombre'],
            'apellido' => $paciente['apellido']
        ];
    }
}
echo json_encode($res);