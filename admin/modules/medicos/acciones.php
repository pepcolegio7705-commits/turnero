<?php
require_once '../../../core/config.php';
require_once '../../../core/funciones.php';

$accion = $_GET['accion'] ?? '';

switch($accion) {
    case 'listar':
        $start  = isset($_POST['start'])  ? (int)$_POST['start']  : 0;
        $length = isset($_POST['length']) ? (int)$_POST['length'] : 10;
        $search = (isset($_POST['search']['value'])) ? $_POST['search']['value'] : '';

        try {
            $sql = "SELECT m.*, e.nombre as especialidad_nombre 
                    FROM medicos m 
                    INNER JOIN especialidades e ON m.especialidad_id = e.id 
                    WHERE (m.nombre LIKE :s1 
                       OR m.apellido LIKE :s2 
                       OR m.matricula LIKE :s3
                       OR m.consultorio LIKE :s4)
                    ORDER BY m.apellido ASC 
                    LIMIT $start, $length";
            
            $stmt = $pdo->prepare($sql);
            $searchTerm = "%$search%";
            $stmt->bindValue(':s1', $searchTerm, PDO::PARAM_STR);
            $stmt->bindValue(':s2', $searchTerm, PDO::PARAM_STR);
            $stmt->bindValue(':s3', $searchTerm, PDO::PARAM_STR);
            $stmt->bindValue(':s4', $searchTerm, PDO::PARAM_STR);
            $stmt->execute();
            $datos = $stmt->fetchAll();

            $total = $pdo->query("SELECT COUNT(*) FROM medicos")->fetchColumn();

            $res = [];
            foreach($datos as $d) {
                $id_encriptado = encriptar($d['id']);
                $esta_activo = (int)$d['estado'] === 1;

                if ($esta_activo) {
                    $btnEditar = '<button class="btn btn-light btn-sm rounded-circle me-1 text-warning shadow-sm" onclick="editar(\''.$id_encriptado.'\')" title="Editar Profesional"><i data-lucide="edit-3" style="width:16px; height:16px;"></i></button>';
                    $nombre_display = $d['apellido'] . ", " . $d['nombre'];
                } else {
                    $btnEditar = '<button class="btn btn-light btn-sm rounded-circle me-1 text-muted shadow-sm" style="opacity: 0.5; cursor: not-allowed;" title="Debe activar al profesional para editar" disabled><i data-lucide="edit-3" style="width:16px; height:16px;"></i></button>';
                    $nombre_display = '<span class="text-muted">' . $d['apellido'] . ", " . $d['nombre'] . '</span>';
                }

                $btnEstado = '<button class="btn btn-light btn-sm rounded-circle '.($esta_activo ? 'text-danger' : 'text-success').' shadow-sm" onclick="eliminar(\''.$id_encriptado.'\')" title="'.($esta_activo ? 'Desactivar Profesional' : 'Activar Profesional').'"><i data-lucide="'.($esta_activo ? 'user-x' : 'user-check').'" style="width:16px; height:16px;"></i></button>';

                $res[] = [
                    "matricula"       => '<span class="fw-bold ' . ($esta_activo ? '' : 'text-muted') . '">' . $d['matricula'] . '</span>',
                    "nombre_completo" => $nombre_display,
                    "especialidad"    => $esta_activo ? $d['especialidad_nombre'] : '<span class="text-muted">' . $d['especialidad_nombre'] . '</span>',
                    "consultorio"     => '<span>' . ($d['consultorio'] ?? 'N/A') . '</span>',
                    "estado"          => $esta_activo 
                                         ? '<span class="badge bg-success-subtle text-success border border-success-subtle px-3">Activo</span>' 
                                         : '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3">Inactivo</span>',
                    "acciones" => '<div class="text-end"><button class="btn btn-light btn-sm rounded-circle me-1 text-info shadow-sm" onclick="verFichaMedico(\''.$id_encriptado.'\')" title="Ver Ficha"><i data-lucide="eye" style="width:16px; height:16px;"></i></button>'.$btnEditar.$btnEstado.'</div>'
                ];
            }

            echo json_encode(["draw" => isset($_POST['draw']) ? intval($_POST['draw']) : 1, "recordsTotal" => intval($total), "recordsFiltered" => intval($total), "data" => $res]);

        } catch (PDOException $e) {
            header('Content-Type: application/json');
            echo json_encode(["error" => $e->getMessage()]);
        }
        exit;
        break;

    case 'get_especialidades':
        $stmt = $pdo->query("SELECT id, nombre FROM especialidades WHERE estado = 1 ORDER BY nombre ASC");
        responderJSON(true, "", $stmt->fetchAll());
        break;

    case 'guardar':
        $id_encriptado    = $_POST['id'] ?? '';
        $nombre           = limpiarCadena($_POST['nombre']);
        $apellido         = limpiarCadena($_POST['apellido']);
        $dni              = limpiarCadena($_POST['dni']);
        $matricula        = limpiarCadena($_POST['matricula']);
        $telefono         = limpiarCadena($_POST['telefono']);
        $email            = limpiarCadena($_POST['email']);
        $esp_id           = $_POST['especialidad_id'];
        $valor_consulta   = floatval($_POST['valor_consulta']);
        $consultorio      = limpiarCadena($_POST['consultorio']);
        $pass_raw         = $_POST['password'] ?? ''; 
        $os_seleccionadas = $_POST['os_asignadas'] ?? [];

        try {
            $pdo->beginTransaction();
            if(empty($id_encriptado)) {
                $check = $pdo->prepare("SELECT id FROM medicos WHERE dni = ?");
                $check->execute([$dni]);
                if($check->rowCount() > 0) { $pdo->rollBack(); responderJSON(false, "El DNI ya está registrado."); exit; }
                $password = password_hash($pass_raw, PASSWORD_DEFAULT);
                $sql = "INSERT INTO medicos (nombre, apellido, dni, matricula, telefono, email, password, especialidad_id, valor_consulta, consultorio) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$nombre, $apellido, $dni, $matricula, $telefono, $email, $password, $esp_id, $valor_consulta, $consultorio]);
                $id_real = $pdo->lastInsertId();
                $mensaje = "Profesional registrado correctamente.";
            } else {
                $id_real = desencriptar($id_encriptado);
                if (!empty($pass_raw)) {
                    $password = password_hash($pass_raw, PASSWORD_DEFAULT);
                    $sql = "UPDATE medicos SET nombre=?, apellido=?, dni=?, matricula=?, telefono=?, email=?, especialidad_id=?, valor_consulta=?, consultorio=?, password=? WHERE id=?";
                    $params = [$nombre, $apellido, $dni, $matricula, $telefono, $email, $esp_id, $valor_consulta, $consultorio, $password, $id_real];
                } else {
                    $sql = "UPDATE medicos SET nombre=?, apellido=?, dni=?, matricula=?, telefono=?, email=?, especialidad_id=?, valor_consulta=?, consultorio=? WHERE id=?";
                    $params = [$nombre, $apellido, $dni, $matricula, $telefono, $email, $esp_id, $valor_consulta, $consultorio, $id_real];
                }
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $mensaje = "Datos actualizados correctamente.";
            }
            $del = $pdo->prepare("DELETE FROM medico_obras_sociales WHERE medico_id = ?");
            $del->execute([$id_real]);
            if (!empty($os_seleccionadas)) {
                $ins = $pdo->prepare("INSERT INTO medico_obras_sociales (medico_id, obra_social_id) VALUES (?, ?)");
                foreach ($os_seleccionadas as $os_id) { $ins->execute([$id_real, $os_id]); }
            }
            $pdo->commit();
            responderJSON(true, $mensaje);
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            responderJSON(false, "Error de base de datos: " . $e->getMessage());
        }
    break;

    case 'leer_uno':
        try {
            $id = desencriptar($_POST['id']);
            $sql = "SELECT m.id, m.nombre, m.apellido, m.dni, m.matricula, m.telefono, 
                    m.email, m.especialidad_id, m.valor_consulta, m.consultorio, 
                    e.nombre AS nombre_especialidad,
                    (SELECT GROUP_CONCAT(os.nombre SEPARATOR '|') FROM medico_obras_sociales mos JOIN obras_sociales os ON mos.obra_social_id = os.id WHERE mos.medico_id = m.id AND os.estado = 1) as obras_nombres
                    FROM medicos m LEFT JOIN especialidades e ON m.especialidad_id = e.id WHERE m.id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            if($data) { responderJSON(true, "", $data); } else { responderJSON(false, "No encontrado."); }
        } catch (Exception $e) { responderJSON(false, $e->getMessage()); }
    break;

    case 'eliminar':
        try {
            $id_real = desencriptar($_POST['id']);
            $consulta = $pdo->prepare("SELECT estado FROM medicos WHERE id = ?");
            $consulta->execute([$id_real]);
            $estado_actual = $consulta->fetchColumn();
            $nuevo_estado = ($estado_actual == 1) ? 0 : 1;
            $stmt = $pdo->prepare("UPDATE medicos SET estado = ? WHERE id = ?");
            $stmt->execute([$nuevo_estado, $id_real]);
            responderJSON(true, $nuevo_estado == 1 ? "Activado" : "Desactivado");
        } catch (Exception $e) { responderJSON(false, $e->getMessage()); }
    break;

    case 'listar_os_checks':
        $medico_id = isset($_POST['medico_id']) ? desencriptar($_POST['medico_id']) : null;
        $todas = $pdo->query("SELECT id, nombre FROM obras_sociales WHERE estado = 1 ORDER BY nombre ASC")->fetchAll();
        $asignadas = [];
        if($medico_id) {
            $stmt = $pdo->prepare("SELECT obra_social_id FROM medico_obras_sociales WHERE medico_id = ?");
            $stmt->execute([$medico_id]);
            $asignadas = $stmt->fetchAll(PDO::FETCH_COLUMN);
        }
        $data = [];
        foreach($todas as $os) { $data[] = ["id" => $os['id'], "nombre" => $os['nombre'], "asignada" => in_array($os['id'], $asignadas)]; }
        echo json_encode(["status" => true, "data" => $data]);
    break;

    case 'get_horarios':
        $id = $_GET['id'] ?? 0;
        $stmt = $pdo->prepare("SELECT * FROM horarios_medicos WHERE medico_id = ?");
        $stmt->execute([$id]);
        echo json_encode(["status" => true, "data" => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    break;

    case 'guardar_horarios':
        $medico_id = intval($_POST['medico_id'] ?? 0);
        $activos = $_POST['activo'] ?? [];
        $inicios = $_POST['inicio'] ?? [];
        $fines = $_POST['fin'] ?? [];
        $duraciones = $_POST['duracion'] ?? [];
        try {
            $pdo->beginTransaction();
            $pdo->prepare("DELETE FROM horarios_medicos WHERE medico_id = ?")->execute([$medico_id]);
            $stmt = $pdo->prepare("INSERT INTO horarios_medicos (medico_id, dia_semana, hora_inicio, hora_fin, duracion_turno, activo) VALUES (?, ?, ?, ?, ?, 1)");
            foreach ($activos as $dia_idx => $v) {
                $stmt->execute([$medico_id, $dia_idx, $inicios[$dia_idx] ?: '08:00', $fines[$dia_idx] ?: '12:00', intval($duraciones[$dia_idx] ?: 20)]);
            }
            $pdo->commit();
            echo json_encode(["status" => true, "message" => "Actualizado"]);
        } catch (Exception $e) { $pdo->rollBack(); echo json_encode(["status" => false, "message" => $e->getMessage()]); }
    break;
} // Cierre de Switch
?>