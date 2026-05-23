<?php
error_reporting(E_ERROR | E_PARSE); 
header('Content-Type: application/json');

require_once '../../../core/Database.php';
require_once '../../../core/funciones.php';
require_once '../../../core/config.php';

$accion = $_GET['accion'] ?? '';

switch ($accion) {
    case 'validar_paciente':
        $dni = $_POST['dni'];
        // IMPORTANTE: Incluir obra_social_id en el SELECT
        $stmt = $pdo->prepare("SELECT id, nombre, apellido, obra_social_id FROM pacientes WHERE dni = ?");
        $stmt->execute([$dni]);
        $paciente = $stmt->fetch();

        if ($paciente) {
            echo json_encode([
                'existe' => true, 
                'data' => $paciente // Aquí va el ID, nombre, apellido y OBRA_SOCIAL_ID
            ]);
        } else {
            echo json_encode(['existe' => false]);
        }
        exit;
    break;

    case 'get_medicos_filtro':
        try {
            $stmt = $pdo->query("SELECT id, nombre, apellido FROM medicos WHERE estado = 1 ORDER BY apellido ASC");
            $medicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(["status" => true, "data" => $medicos]);
        } catch (PDOException $e) {
            echo json_encode(["status" => false, "message" => $e->getMessage()]);
        }
        exit;
    break;

    case 'get_medicos_especialidad':
        $esp_id = intval($_GET['especialidad_id']);
        $stmt = $pdo->prepare("SELECT id, nombre, apellido FROM medicos WHERE especialidad_id = ? AND estado = 1");
        $stmt->execute([$esp_id]);
        $medicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(["status" => true, "data" => $medicos]);
        exit;
    break;

    case 'get_disponibilidad':
        $medico_id = isset($_POST['medico_id']) ? intval($_POST['medico_id']) : 0;
        $fecha = isset($_POST['fecha']) ? $_POST['fecha'] : null;

        if (!$medico_id || !$fecha) {
            echo json_encode(["status" => false, "message" => "Faltan parámetros"]);
            exit;
        }

        $dia_semana = date('w', strtotime($fecha));

        // 1. CONFIGURACIÓN
        $stmt = $pdo->prepare("SELECT * FROM horarios_medicos WHERE medico_id = ? AND dia_semana = ? AND activo = 1");
        $stmt->execute([$medico_id, $dia_semana]);
        $horario = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$horario) {
            echo json_encode(["status" => true, "slots" => []]); 
            exit;
        }

        // 2. TURNOS OCUPADOS
        $ocupados = [];
        try {
            $stmt = $pdo->prepare("SELECT DATE_FORMAT(hora, '%H:%i') as hora FROM turnos WHERE medico_id = ? AND fecha = ? AND estado != 'cancelado'");
            $stmt->execute([$medico_id, $fecha]);
            $ocupados = $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            echo json_encode(["status" => false, "message" => "Error SQL: " . $e->getMessage()]);
            exit;
        }

        // 3. GENERAR SLOTS
        $slots = [];
        $inicio = strtotime($horario['hora_inicio']); 
        $fin = strtotime($horario['hora_fin']);    
        $duracion = intval($horario['duracion_turno']) * 60; 

        $actual = $inicio;
        while ($actual < $fin) {
            $hora_slot = date('H:i', $actual);
            if (!in_array($hora_slot, $ocupados)) {
                $slots[] = $hora_slot;
            }
            $actual += $duracion;
        }

        echo json_encode(["status" => true, "slots" => $slots]);
        exit;
    break;

    case 'listar_turnos_gestion':
        // Recibimos los filtros (si no existen, usamos valores por defecto)
        $fecha = !empty($_POST['fecha']) ? $_POST['fecha'] : date('Y-m-d');
        $medico_id = !empty($_POST['medico_id']) ? intval($_POST['medico_id']) : '';
        $estado = !empty($_POST['estado']) ? $_POST['estado'] : '';

        $params = [$fecha];
        $where = "WHERE t.fecha = ?";

        if ($medico_id) {
            $where .= " AND t.medico_id = ?";
            $params[] = $medico_id;
        }
        if ($estado) {
            $where .= " AND t.estado = ?";
            $params[] = $estado;
        }

        try {
            // Consulta SQL Robusta: Une Turnos, Pacientes, Medicos, Especialidades y Obras Sociales
            $sql = "SELECT 
                        t.id, 
                        DATE_FORMAT(t.hora, '%H:%i') as hora_formateada, 
                        t.estado, 
                        t.obra_social_id,
                        p.nombre as pac_nom, p.apellido as pac_ape, p.dni as pac_dni,
                        m.nombre as med_nom, m.apellido as med_ape, m.valor_consulta,
                        e.nombre as especialidad_nombre,
                        os.nombre as os_nombre, os.coseguro_estandar
                    FROM turnos t
                    INNER JOIN pacientes p ON t.paciente_id = p.id
                    INNER JOIN medicos m ON t.medico_id = m.id
                    INNER JOIN especialidades e ON m.especialidad_id = e.id
                    LEFT JOIN obras_sociales os ON t.obra_social_id = os.id
                    $where
                    ORDER BY t.hora ASC";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $turnos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $data = [];
            foreach ($turnos as $t) {
                // LÓGICA DE COBRO AUTOMÁTICO
                $valor_medico = floatval($t['valor_consulta']);
                $coseguro = floatval($t['coseguro_estandar'] ?? 0);
                
                // Si el turno tiene obra_social_id, el monto sugerido es el coseguro.
                // Si es NULL, el monto sugerido es el valor particular del médico.
                $monto_sugerido = ($t['obra_social_id']) ? $coseguro : $valor_medico;

                $data[] = [
                    "id" => $t['id'],
                    "hora" => $t['hora_formateada'],
                    "paciente_nombre" => $t['pac_nom'] . " " . $t['pac_ape'],
                    "paciente_dni" => $t['pac_dni'],
                    "medico_nombre" => $t['med_nom'] . " " . $t['med_ape'],
                    "especialidad" => $t['especialidad_nombre'],
                    "obra_social" => $t['os_nombre'] ?? 'PARTICULAR',
                    "monto_sugerido" => $monto_sugerido,
                    "estado" => $t['estado']
                ];
            }

            echo json_encode(["status" => true, "data" => $data]);

        } catch (PDOException $e) {
            echo json_encode(["status" => false, "message" => "Error SQL: " . $e->getMessage()]);
        }
        exit;
    break;

    case 'validar_cobertura_medico':
        $m_id = intval($_POST['medico_id']);
        $os_id = intval($_POST['os_id']);

        // Buscamos en tu estructura: medico_id | obra_social_id
        $sql = "SELECT 1 FROM medico_obras_sociales 
                WHERE medico_id = ? AND obra_social_id = ? 
                LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$m_id, $os_id]);
        
        // Si existe fila, atiende es true
        echo json_encode(['atiende' => (bool)$stmt->fetch()]);
        exit;
    break;

    case 'confirmar_turno':
        try {
            $pdo->beginTransaction();

            $paciente_id = !empty($_POST['paciente_id']) ? $_POST['paciente_id'] : null;
            $es_nuevo = ($_POST['es_nuevo'] === 'true');
            $os_id = !empty($_POST['obra_social_id']) ? $_POST['obra_social_id'] : null;

            // 1. Manejo de Paciente Nuevo
            if ($es_nuevo) {
                $stmtPac = $pdo->prepare("INSERT INTO pacientes (dni, nombre, apellido, telefono, obra_social_id) VALUES (?, ?, ?, ?, ?)");
                $stmtPac->execute([$_POST['dni'], $_POST['nombre'], $_POST['apellido'], $_POST['tel'], $os_id]);
                $paciente_id = $pdo->lastInsertId();
            }

            // 2. Determinar tipo de pago
            $tipo_pago = ($os_id === null) ? 'Particular' : 'Obra Social';

            // 3. INSERT EN TURNOS (Corregido con obra_social_id)
            $sqlTurno = "INSERT INTO turnos (
                            medico_id, 
                            paciente_id, 
                            fecha, 
                            hora, 
                            obra_social_id, 
                            estado, 
                            tipo_pago, 
                            monto_cobrado
                        ) VALUES (?, ?, ?, ?, ?, 'Pendiente', ?, 0.00)";
            
            $stmtTurno = $pdo->prepare($sqlTurno);
            $stmtTurno->execute([
                $_POST['medico_id'],
                $paciente_id,
                $_POST['fecha'],
                $_POST['hora'],
                $os_id, // <--- Ahora sí se guarda el ID de la obra social
                $tipo_pago
            ]);

            $pdo->commit();
            echo json_encode(['status' => true, 'message' => 'Turno agendado correctamente']);

        } catch (Exception $e) {
            $pdo->rollBack();
            echo json_encode(['status' => false, 'message' => $e->getMessage()]);
        }
        exit;
    break;

    case 'obtener_detalle_cobro':
        $id = $_POST['id'];
        $sql = "SELECT t.id, t.obra_social_id, p.nombre as paciente_nombre, p.apellido as paciente_apellido,
                       m.nombre as medico_nombre, m.apellido as medico_apellido, m.valor_consulta,
                       os.coseguro_estandar
                FROM turnos t
                JOIN pacientes p ON t.paciente_id = p.id
                JOIN medicos m ON t.medico_id = m.id
                LEFT JOIN obras_sociales os ON t.obra_social_id = os.id
                WHERE t.id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $t = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($t) {
            $t['paciente_nombre'] = $t['paciente_nombre'] . " " . $t['paciente_apellido'];
            $t['medico_nombre'] = "Dr. " . $t['medico_apellido'];
            // Si no hay obra social, el coseguro es 0 por defecto
            $t['coseguro_estandar'] = $t['coseguro_estandar'] ?? 0;
            echo json_encode(['status' => true, 'data' => $t]);
        } else {
            echo json_encode(['status' => false]);
        }
        exit;
    break;

    case 'cancelar_turno':
        $id = $_POST['id'];
        $stmt = $pdo->prepare("UPDATE turnos SET estado = 'Cancelado' WHERE id = ?");
        $res = $stmt->execute([$id]);
        echo json_encode(['status' => $res]);
        exit;
    break;

   case 'confirmar_recepcion':
        $turno_id = $_POST['turno_id'] ?? null;
        $metodo_atencion = $_POST['metodo_atencion'] ?? ''; // 'obra_social' o 'particular'
        $medio_pago = $_POST['medio_pago'] ?? '';      // 'Efectivo', 'Transferencia', etc.
        $monto = $_POST['monto_cobrado'] ?? 0;
        $nro_operacion = $_POST['nro_operacion'] ?? null; // <--- AQUÍ CAPTURAMOS EL NÚMERO
        $observaciones = $_POST['observaciones'] ?? '';

        if ($turno_id) {
            try {
                // Concatenamos el método y el medio para el campo tipo_pago si lo deseas
                // O puedes guardarlos en campos separados si modificaste la tabla.
                $tipo_pago_detalle = strtoupper($metodo_atencion) . " - " . $medio_pago;

                $sql = "UPDATE turnos SET 
                            estado = 'Espera', 
                            tipo_pago = ?, 
                            monto_cobrado = ?, 
                            nro_operacion = ?, 
                            observaciones = ?,
                            created_at = NOW() 
                        WHERE id = ?";
                
                $stmt = $pdo->prepare($sql);
                $res = $stmt->execute([
                    $tipo_pago_detalle, 
                    $monto, 
                    $nro_operacion, 
                    $observaciones, 
                    $turno_id
                ]);

                echo json_encode(['status' => true, 'message' => 'Paciente recibido correctamente']);
            } catch (PDOException $e) {
                echo json_encode(['status' => false, 'message' => 'Error en BD: ' . $e->getMessage()]);
            }
        } else {
            echo json_encode(['status' => false, 'message' => 'ID de turno no válido']);
        }
        exit;
    break;

    case 'reporte_diario_caja':
        try {
            // Soporte para rango o un solo día
            $desde = $_GET['fecha_desde'] ?? ($_GET['fecha'] ?? date('Y-m-d'));
            $hasta = $_GET['fecha_hasta'] ?? $desde;

            $sql = "SELECT 
                        CASE 
                            WHEN tipo_pago IS NULL OR tipo_pago = '' THEN 'Particular (No especificado)'
                            ELSE tipo_pago 
                        END as categoria,
                        COUNT(*) as cantidad_turnos,
                        SUM(monto_cobrado) as total_recaudado
                    FROM turnos 
                    WHERE (fecha BETWEEN ? AND ?) AND (estado = 'Atendido' OR estado = 'Espera')
                    GROUP BY categoria";
                    
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$desde, $hasta]);
            $resumen = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(['status' => true, 'data' => $resumen]);
        } catch (PDOException $e) {
            echo json_encode(['status' => false, 'message' => $e->getMessage()]);
        }
        exit;
    break;
}