<?php
include "core/config.php";
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recibimos los datos del formulario
    $dni = $_POST['dni'] ?? null;
    $medico_id = $_POST['medico_id'] ?? null;
    $os_id = $_POST['obra_social_id'] ?? null;

    // Si el usuario seleccionó la opción particular, lo convertimos a NULL real
    if ($os_id === "NULL" || $os_id === "") {
        $os_id = null;
    }

    $fecha_seleccionada = $_POST['fecha'] ?? null;
    // IMPORTANTE: Ahora recibimos 'hora', que es el valor del input oculto 'hora_final'
    $hora_turno = $_POST['hora'] ?? null; 

    try {
        // Validamos los datos (Cambiamos $horario_id por $hora_turno)
        if (!$dni || !$hora_turno || !$fecha_seleccionada || !$medico_id) {
            throw new Exception("Faltan datos obligatorios (DNI, Fecha, Profesional u Horario).");
        }

        $pdo->beginTransaction();

        // 1. LÓGICA DE PACIENTE (Empadronamiento automático)
        $stmt = $pdo->prepare("SELECT id FROM pacientes WHERE dni = ? LIMIT 1");
        $stmt->execute([$dni]);
        $paciente_id = $stmt->fetchColumn();

        if (!$paciente_id) {
            $nombre = htmlspecialchars($_POST['nombre'] ?? '');
            $apellido = htmlspecialchars($_POST['apellido'] ?? '');
            $telefono = htmlspecialchars($_POST['telefono'] ?? '');
            
            if (empty($nombre) || empty($apellido)) {
                throw new Exception("Para pacientes nuevos, el nombre y apellido son obligatorios.");
            }

            $sqlP = "INSERT INTO pacientes (dni, nombre, apellido, telefono, obra_social_id, estado, created_at) 
                     VALUES (?, ?, ?, ?, ?, 1, NOW())";
            $stmtP = $pdo->prepare($sqlP);
            $stmtP->execute([$dni, $nombre, $apellido, $telefono, $os_id]);
            $paciente_id = $pdo->lastInsertId();
        }

        // 2. VERIFICACIÓN ANTI-DUPLICADO (Seguridad extra)
        // Verificamos si ese médico no recibió un turno en ese mismo momento justo antes de insertar
        $stmtCheck = $pdo->prepare("SELECT id FROM turnos WHERE medico_id = ? AND fecha = ? AND hora = ? AND estado != 'Cancelado'");
        $stmtCheck->execute([$medico_id, $fecha_seleccionada, $hora_turno]);
        if ($stmtCheck->fetch()) {
            throw new Exception("Lo sentimos, este horario acaba de ser reservado. Por favor elija otro.");
        }

        // 3. INSERTAR EN LA TABLA turnos
        $sqlT = "INSERT INTO turnos (
                    medico_id, 
                    paciente_id, 
                    fecha, 
                    hora, 
                    obra_social_id, 
                    estado, 
                    monto_cobrado,
                    created_at,
                    updated_at
                ) VALUES (?, ?, ?, ?, ?, 'Pendiente', 0, NOW(), NOW())";
        
        $stmtT = $pdo->prepare($sqlT);
        $stmtT->execute([
            $medico_id, 
            $paciente_id, 
            $fecha_seleccionada, 
            $hora_turno, 
            $os_id
        ]);

        $ultimo_id = $pdo->lastInsertId(); // Capturamos el ID antes del commit
        $pdo->commit();

        $fecha_formateada = date('d/m/Y', strtotime($fecha_seleccionada));
        
        ob_clean(); 
        echo json_encode([
            'status' => 'success', 
            'message' => "¡Turno agendado con éxito! Te esperamos el día $fecha_formateada a las $hora_turno hs.",
            'turno_id' => $ultimo_id
        ], JSON_UNESCAPED_UNICODE);
        exit;

    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}