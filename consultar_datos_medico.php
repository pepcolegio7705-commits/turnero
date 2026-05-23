<?php
include "core/config.php"; // Asegúrate que aquí esté tu conexión $pdo

header('Content-Type: application/json');

if(isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $res = ['especialidad' => 'No definida', 'horarios' => []];

    try {
        // 1. Obtener la especialidad asociada al médico
        $sql_esp = "SELECT e.nombre FROM especialidades e 
                    INNER JOIN medicos m ON m.especialidad_id = e.id 
                    WHERE m.id = ?";
        $stmt_esp = $pdo->prepare($sql_esp);
        $stmt_esp->execute([$id]);
        $nombre_esp = $stmt_esp->fetchColumn();
        if($nombre_esp) $res['especialidad'] = $nombre_esp;

        // 2. Obtener los horarios (ajustado a tu tabla horarios_medicos)
        $sql_h = "SELECT id, dia_semana, hora_inicio, hora_fin FROM horarios_medicos WHERE medico_id = ?";
        $stmt_h = $pdo->prepare($sql_h);
        $stmt_h->execute([$id]);
        $res['horarios'] = $stmt_h->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($res);
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}