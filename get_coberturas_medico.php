<?php
include "core/config.php";
$medico_id = $_GET['medico_id'] ?? 0;
$sql = "SELECT os.id, os.nombre FROM obras_sociales os 
        INNER JOIN medico_obras_sociales mos ON os.id = mos.obra_social_id 
        WHERE mos.medico_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$medico_id]);
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));