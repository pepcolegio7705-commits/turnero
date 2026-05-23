<?php
require_once '../../../core/Database.php';
require_once '../../../core/funciones.php';
require_once '../../../core/config.php';

$accion = $_GET['accion'] ?? '';

// Aseguramos que la respuesta siempre sea JSON
header('Content-Type: application/json');

switch ($accion) {
    case 'listar':
        try {
            // Parámetros de DataTables con valores por defecto
            $limit = isset($_POST['length']) ? intval($_POST['length']) : 10;
            $start = isset($_POST['start']) ? intval($_POST['start']) : 0;
            $draw  = isset($_POST['draw']) ? intval($_POST['draw']) : 1;

            // Conteo total de registros
            $totalData = $pdo->query("SELECT COUNT(*) FROM pacientes")->fetchColumn();
            
            // Lógica de búsqueda
            $search = isset($_POST['search']['value']) ? limpiarCadena($_POST['search']['value']) : '';
            $where = "";
            $params = [];

            if(!empty($search)){
                $where = " WHERE nombre LIKE ? OR apellido LIKE ? OR dni LIKE ? OR email LIKE ?";
                $params = ["%$search%", "%$search%", "%$search%", "%$search%"];
            }

            // Consulta paginada
            $sql = "SELECT * FROM pacientes $where ORDER BY id DESC LIMIT $limit OFFSET $start";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $pacientes = $stmt->fetchAll();

            // Formateo de datos para la tabla
            $data = [];
            foreach ($pacientes as $p) {
                $data[] = [
                    "dni" => $p['dni'],
                    "nombre_completo" => "<b>" . strtoupper($p['apellido']) . "</b>, " . $p['nombre'],
                    "telefono" => $p['telefono'] ?: '<span class="text-muted">---</span>',
                    "email" => $p['email'] ?: '<span class="text-muted">---</span>',
                    "acciones" => '
                        <div class="text-end">
                            <button class="btn btn-light btn-sm rounded-circle me-1 text-info shadow-sm" onclick="verFicha(\''.encriptar($p['id']).'\')" title="Ver Ficha">
                                <i data-lucide="eye" style="width:16px; height:16px;"></i>
                            </button>
                            <button class="btn btn-light btn-sm rounded-circle me-1 text-warning shadow-sm" onclick="editar(\''.encriptar($p['id']).'\')" title="Editar">
                                <i data-lucide="edit-3" style="width:16px; height:16px;"></i>
                            </button>
                            <button class="btn btn-light btn-sm rounded-circle text-danger shadow-sm" onclick="eliminar(\''.encriptar($p['id']).'\')" title="Eliminar">
                                <i data-lucide="trash-2" style="width:16px; height:16px;"></i>
                            </button>
                        </div>'
                ];
            }

            ob_clean(); // Limpia cualquier warning previo
            echo json_encode([
                "draw" => $draw,
                "recordsTotal" => intval($totalData),
                "recordsFiltered" => !empty($search) ? count($data) : intval($totalData),
                "data" => $data
            ]);
        } catch (PDOException $e) {
            echo json_encode(["error" => $e->getMessage()]);
        }
        exit;
        break;

    case 'guardar':
        $id_enc = $_POST['id'] ?? '';
        $dni = limpiarCadena($_POST['dni']);
        $nombre = limpiarCadena($_POST['nombre']);
        $apellido = limpiarCadena($_POST['apellido']);
        
        // Validamos que la fecha no venga vacía para evitar errores de tipo date en SQL
        $fecha_nac = (!empty($_POST['fecha_nacimiento'])) ? $_POST['fecha_nacimiento'] : null;
        
        $tel = limpiarCadena($_POST['telefono']);
        $email = limpiarCadena($_POST['email']);
        
        // Lógica para Obra Social (0 o vacío se guarda como NULL)
        $os_id_post = $_POST['obra_social_id'] ?? 0; 
        $obra_social_id = ($os_id_post > 0) ? (int)$os_id_post : null;

        try {
            if (empty($id_enc)) {
                // --- INSERTAR NUEVO ---
                $check = $pdo->prepare("SELECT id FROM pacientes WHERE dni = ?");
                $check->execute([$dni]);
                if ($check->rowCount() > 0) {
                    echo json_encode(["status" => false, "message" => "El DNI <b>$dni</b> ya está registrado."]); 
                    exit;
                }

                $sql = "INSERT INTO pacientes (nombre, apellido, dni, fecha_nacimiento, telefono, email, obra_social_id) VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                // Corregido: $obra_social_id (con 'b')
                $stmt->execute([$nombre, $apellido, $dni, $fecha_nac, $tel, $email, $obra_social_id]);
                
                echo json_encode(["status" => true, "message" => "Paciente registrado correctamente."]);
            } else {
                // --- ACTUALIZAR EXISTENTE ---
                $id = desencriptar($id_enc);
                
                // Validar que el DNI no lo tenga otro paciente
                $check = $pdo->prepare("SELECT id FROM pacientes WHERE dni = ? AND id != ?");
                $check->execute([$dni, $id]);
                if ($check->rowCount() > 0) {
                    echo json_encode(["status" => false, "message" => "No se puede actualizar: El DNI <b>$dni</b> ya pertenece a otro paciente."]); 
                    exit;
                }

                // Corregido: nombre de columna 'obra_social_id' y variable '$obra_social_id'
                $sql = "UPDATE pacientes SET dni=?, nombre=?, apellido=?, fecha_nacimiento=?, telefono=?, email=?, obra_social_id=? WHERE id=?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$dni, $nombre, $apellido, $fecha_nac, $tel, $email, $obra_social_id, $id]);
                
                echo json_encode(["status" => true, "message" => "Datos actualizados con éxito."]);
            }
        } catch (PDOException $e) {
            echo json_encode(["status" => false, "message" => "Error de BD: " . $e->getMessage()]);
        }
        exit;
    break;

    case 'leer_uno':
        try {
            $id = desencriptar($_POST['id']);
            
            // Usamos LEFT JOIN para traer el nombre de la obra social si existe
            $sql = "SELECT p.*, os.nombre AS obra_social_nombre 
                    FROM pacientes p 
                    LEFT JOIN obras_sociales os ON p.obra_social_id = os.id 
                    WHERE p.id = ?";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id]);
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if($res) {
                // Limpieza de datos para el JSON (opcional pero recomendado)
                echo json_encode(["status" => true, "data" => $res]);
            } else {
                echo json_encode(["status" => false, "message" => "Paciente no encontrado."]);
            }
        } catch (Exception $e) {
            echo json_encode(["status" => false, "message" => $e->getMessage()]);
        }
        exit;
    break;

    case 'get_os_activas':
        try {
            // Traemos solo ID y Nombre de las activas
            $sql = "SELECT id, nombre FROM obras_sociales WHERE estado = 1 ORDER BY nombre ASC";
            $stmt = $pdo->query($sql);
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Respondemos con los datos
            echo json_encode([
                "status" => true,
                "data" => $datos
            ]);
        } catch (PDOException $e) {
            echo json_encode([
                "status" => false, 
                "message" => "Error al cargar coberturas: " . $e->getMessage()
            ]);
        }
        exit;
    break;

    case 'eliminar':
        try {
            $id = desencriptar($_POST['id']);
            if(!$id) throw new Exception("ID no válido");

            $stmt = $pdo->prepare("DELETE FROM pacientes WHERE id = ?");
            $stmt->execute([$id]);
            
            echo json_encode(["status" => true, "message" => "Paciente eliminado del sistema."]);
        } catch (Exception $e) {
            echo json_encode(["status" => false, "message" => "Error al eliminar: " . $e->getMessage()]);
        }
        exit;
        break;
}