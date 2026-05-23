<?php
require_once '../core/config.php';

header('Content-Type: application/json');

try {
    // 1. Agregamos las columnas faltantes y el ORDER BY
    $sql = "SELECT 
                t.id as id_turno,
                p.nombre as paciente_nombre,
                p.apellido as paciente_apellido,
                CONCAT(m.nombre, ' ', m.apellido) as medico,
                m.consultorio as consultorio,
                t.hora as hora_turno,
                t.estado,
                t.updated_at
            FROM turnos t
            JOIN medicos m ON t.medico_id = m.id
            JOIN pacientes p ON t.paciente_id = p.id
            WHERE t.estado IN ('Llamando', 'Atendiendo') 
            ORDER BY t.updated_at DESC 
            LIMIT 5";

    $stmt = $pdo->query($sql);
    $llamados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $resultado = [];
    foreach ($llamados as $fila) {
        $resultado[] = [
            'id_turno'    => $fila['id_turno'],
            'paciente'    => $fila['paciente_nombre'] . ' ' . $fila['paciente_apellido'],
            'medico'      => $fila['medico'],
            'consultorio' => $fila['consultorio'] ?? '--',
            'hora_turno'  => $fila['hora_turno'],
            'estado'      => $fila['estado'],
            'updated_at'  => $fila['updated_at']
        ];
    }
    
    echo json_encode($resultado);

} catch (PDOException $e) {
    // Si hay error, devolvemos array vacío para no romper el JS
    echo json_encode([]);
}