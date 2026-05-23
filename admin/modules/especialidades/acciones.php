<?php
require_once '../../../core/config.php';
require_once '../../../core/funciones.php';

$accion = $_GET['accion'] ?? '';

switch($accion) {
    case 'listar':
        // Lógica simplificada de Server-Side para DataTables
        $start = $_POST['start'];
        $length = $_POST['length'];
        $search = $_POST['search']['value'];

        $sql = "SELECT * FROM especialidades WHERE nombre LIKE :search LIMIT $start, $length";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['search' => "%$search%"]);
        $datos = $stmt->fetchAll();

        // Contar total
        $total = $pdo->query("SELECT COUNT(*) FROM especialidades")->fetchColumn();

        $res = [];
        foreach($datos as $d) {
            $res[] = [
                "id" => $d['id'],
                "nombre" => $d['nombre'],
                "estado" => $d['estado'] ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>',
                "acciones" => '
                    <button class="btn btn-sm btn-warning" onclick="editar(\''.encriptar($d['id']).'\')">Editar</button>
                    <button class="btn btn-sm btn-danger" onclick="eliminar(\''.encriptar($d['id']).'\')">Eliminar</button>
                '
            ];
        }

        echo json_encode([
            "draw" => intval($_POST['draw']),
            "recordsTotal" => $total,
            "recordsFiltered" => $total, // Para este ejemplo usamos el mismo
            "data" => $res
        ]);
        break;

        case 'guardar':
            $id_encriptado = $_POST['id'] ?? '';
            $nombre = limpiarCadena($_POST['nombre']);

            if(empty($id_encriptado)) {
                // INSERTAR NUEVO
                $stmt = $pdo->prepare("INSERT INTO especialidades (nombre) VALUES (?)");
                $stmt->execute([$nombre]);
                responderJSON(true, "Especialidad creada correctamente");
            } else {
                // EDITAR EXISTENTE
                $id_real = desencriptar($id_encriptado);
                if(!$id_real) responderJSON(false, "ID no válido");

                $stmt = $pdo->prepare("UPDATE especialidades SET nombre = ? WHERE id = ?");
                $stmt->execute([$nombre, $id_real]);
                responderJSON(true, "Especialidad actualizada correctamente");
            }
        break;

    case 'leer_uno':
        $id_real = desencriptar($_POST['id']);
        if(!$id_real) responderJSON(false, "Error de seguridad");

        $stmt = $pdo->prepare("SELECT nombre FROM especialidades WHERE id = ?");
        $stmt->execute([$id_real]);
        $data = $stmt->fetch();

        if($data) {
            responderJSON(true, "", $data);
        } else {
            responderJSON(false, "No se encontró el registro");
        }
        break;

    case 'eliminar':
        $id_real = desencriptar($_POST['id']);
        if(!$id_real) responderJSON(false, "Error de seguridad");

        try {
            $stmt = $pdo->prepare("DELETE FROM especialidades WHERE id = ?");
            $stmt->execute([$id_real]);
            responderJSON(true, "Registro eliminado con éxito");
        } catch (PDOException $e) {
            // Si hay médicos asignados, saltará el error de restricción de SQL
            responderJSON(false, "No se puede eliminar: hay médicos asociados a esta especialidad.");
        }
        break;
}