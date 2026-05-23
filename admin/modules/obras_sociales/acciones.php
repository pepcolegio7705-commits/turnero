<?php
require_once '../../../core/Database.php';
require_once '../../../core/funciones.php';
require_once '../../../core/config.php';

$accion = $_GET['accion'] ?? '';
header('Content-Type: application/json');

switch ($accion) {
    case 'listar':
        // Parámetros de paginación y dibujo de DataTables
        $limit = isset($_POST['length']) ? intval($_POST['length']) : 10;
        $start = isset($_POST['start']) ? intval($_POST['start']) : 0;
        $draw  = isset($_POST['draw']) ? intval($_POST['draw']) : 1;

        // Conteo total de registros
        $totalData = $pdo->query("SELECT COUNT(*) FROM obras_sociales")->fetchColumn();
        
        // Buscador
        $search = isset($_POST['search']['value']) ? limpiarCadena($_POST['search']['value']) : '';
        $where = "";
        $params = [];

        if(!empty($search)){
            $where = " WHERE nombre LIKE ?";
            $params = ["%$search%"];
        }

        // Consulta SQL
        $sql = "SELECT * FROM obras_sociales $where ORDER BY nombre ASC LIMIT $limit OFFSET $start";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $obras = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $data = [];
        foreach ($obras as $d) {
            $id_encriptado = encriptar($d['id']);
            $esta_activa = ($d['estado'] == 1);

            // --- Lógica del Botón Editar ---
            if ($esta_activa) {
                // Botón habilitado
                $btnEditar = '<button class="btn btn-light btn-sm rounded-circle me-1 text-warning shadow-sm" 
                                      onclick="editar(\''.$id_encriptado.'\')" 
                                      title="Editar">
                                <i data-lucide="edit-3" style="width:16px; height:16px;"></i>
                              </button>';
                $nombre_display = "<b>" . $d['nombre'] . "</b>";
            } else {
                // Botón deshabilitado visualmente y sin función onclick
                $btnEditar = '<button class="btn btn-light btn-sm rounded-circle me-1 text-muted shadow-sm" 
                                      style="opacity: 0.5; cursor: not-allowed;" 
                                      title="Reactive para poder editar" 
                                      disabled>
                                <i data-lucide="edit-3" style="width:16px; height:16px;"></i>
                              </button>';
                $nombre_display = "<span class='text-muted'>" . $d['nombre'] . "</span>";
            }

            // --- Lógica del Botón de Estado (Toggle) ---
            $btnEstado = '<button class="btn btn-light btn-sm rounded-circle '.($esta_activa ? 'text-danger' : 'text-success').' shadow-sm" 
                                  onclick="cambiarEstado(\''.$id_encriptado.'\', '.$d['estado'].')" 
                                  title="'.($esta_activa ? 'Suspender' : 'Activar').'">
                            <i data-lucide="'.($esta_activa ? 'slash' : 'check').'" style="width:16px; height:16px;"></i>
                          </button>';

            $data[] = [
                "nombre" => $nombre_display,
                "coseguro" => "$ " . number_format($d['coseguro_estandar'], 2, ',', '.'),
                "estado" => $esta_activa 
                            ? '<span class="badge bg-success">Activa</span>' 
                            : '<span class="badge bg-danger">Suspendida</span>',
                "acciones" => '
                    <div class="text-end">
                        ' . $btnEditar . '
                        ' . $btnEstado . '
                    </div>'
            ];
        }

        // Limpieza de buffer y respuesta JSON
        ob_clean();
        echo json_encode([
            "draw" => $draw,
            "recordsTotal" => intval($totalData),
            "recordsFiltered" => !empty($search) ? count($data) : intval($totalData),
            "data" => $data
        ]);
        exit;
        break;

    case 'guardar':
        $id_enc = $_POST['id'] ?? '';
        $nombre = limpiarCadena($_POST['nombre']);
        $coseguro = str_replace(',', '.', $_POST['coseguro_estandar']); // Normalizar decimal

        try {
            if (empty($id_enc)) {
                $sql = "INSERT INTO obras_sociales (nombre, coseguro_estandar) VALUES (?,?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$nombre, $coseguro]);
                echo json_encode(["status" => true, "message" => "Obra Social creada"]);
            } else {
                $id = desencriptar($id_enc);
                $sql = "UPDATE obras_sociales SET nombre=?, coseguro_estandar=? WHERE id=?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$nombre, $coseguro, $id]);
                echo json_encode(["status" => true, "message" => "Obra Social actualizada"]);
            }
        } catch (Exception $e) {
            echo json_encode(["status" => false, "message" => $e->getMessage()]);
        }
        break;

    case 'leer_uno':
        $id = desencriptar($_POST['id']);
        $stmt = $pdo->prepare("SELECT * FROM obras_sociales WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(["status" => true, "data" => $stmt->fetch()]);
        break;

    case 'estado':
        try {
            $id = desencriptar($_POST['id']);
            if (!$id) throw new Exception("ID no válido.");

            // Obtenemos el estado actual para invertirlo
            $stmt = $pdo->prepare("SELECT estado FROM obras_sociales WHERE id = ?");
            $stmt->execute([$id]);
            $actual = $stmt->fetchColumn();
            
            $nuevoEstado = ($actual == 1) ? 0 : 1;
            $msj = ($nuevoEstado == 1) ? "Obra Social reactivada." : "Obra Social suspendida/desactivada.";

            $update = $pdo->prepare("UPDATE obras_sociales SET estado = ? WHERE id = ?");
            $update->execute([$nuevoEstado, $id]);
            
            echo json_encode(["status" => true, "message" => $msj]);
        } catch (Exception $e) {
            echo json_encode(["status" => false, "message" => $e->getMessage()]);
        }
        exit;
        break;
}