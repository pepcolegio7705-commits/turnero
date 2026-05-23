<?php
require_once 'core/config.php';
header('Content-Type: application/json');

// --- PASO 0: IMPORTANTE - Configurar zona horaria ---
// Cámbiala por tu ciudad para que la comparación de la hora actual sea real
date_default_timezone_set('America/Argentina/Buenos_Aires'); 

$medico_id = $_POST['medico_id'] ?? null;
$fecha = $_POST['fecha'] ?? null;

if (!$medico_id || !$fecha) {
    echo json_encode(['status' => false, 'slots' => []]);
    exit;
}

try {
    $dia_semana = date('N', strtotime($fecha));

    // 1. Buscamos el rango horario del médico
    $stmt = $pdo->prepare("SELECT hora_inicio, hora_fin, duracion_turno 
                           FROM horarios_medicos 
                           WHERE medico_id = ? AND dia_semana = ? AND activo = 1");
    $stmt->execute([$medico_id, $dia_semana]);
    $config = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$config) {
        echo json_encode(['status' => true, 'slots' => []]);
        exit;
    }

    // 2. Generamos los turnos base
    $slots_generados = [];
    $inicio = strtotime($config['hora_inicio']);
    $fin    = strtotime($config['hora_fin']);
    $intervalo = $config['duracion_turno'] * 60; 

    for ($i = $inicio; $i < $fin; $i += $intervalo) {
        $slots_generados[] = date('H:i', $i);
    }

    // 3. Obtenemos los turnos ya ocupados en la BD
    $stmt = $pdo->prepare("SELECT TIME_FORMAT(hora, '%H:%i') as hora 
                           FROM turnos 
                           WHERE medico_id = ? AND fecha = ? 
                           AND estado IN ('Pendiente','Confirmado','Atendido','Ausente','Espera','Llamando','Atendiendo')");
    $stmt->execute([$medico_id, $fecha]);
    $ocupados = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // --- 4. FILTRADO FINAL (Lógica "en caliente") ---
    
    $hoy = date('Y-m-d');
    $hora_actual = date('H:i');
    $disponibles = [];

    foreach ($slots_generados as $slot) {
        // Primero: Verificamos si NO está ocupado en la base de datos
        if (!in_array($slot, $ocupados)) {
            
            // Segundo: Si la fecha es HOY, verificamos que el slot sea mayor a la hora actual
            if ($fecha === $hoy) {
                if ($slot > $hora_actual) {
                    $disponibles[] = $slot;
                }
            } else {
                // Si es una fecha futura, agregamos todos los disponibles
                $disponibles[] = $slot;
            }
        }
    }

    echo json_encode([
        'status' => true, 
        'slots' => array_values($disponibles)
    ]);

} catch (Exception $e) {
    echo json_encode(['status' => false, 'message' => $e->getMessage()]);
}