<?php
require_once '../../../core/config.php';
header('Content-Type: application/json');

if (defined('DEMO_MODE') && DEMO_MODE) {
    echo json_encode(['status' => 'error', 'message' => 'Acción no disponible en el entorno de demostración.']);
    exit;
}

if ($_POST) {
    $id      = $_POST['id'];
    $nombre  = $_POST['nombre'];
    $usuario = $_POST['usuario'];
    $rol     = $_POST['rol'];
    $estado  = $_POST['estado'];
    $pass    = $_POST['password'] ?? '';

    try {
        // 1. Iniciamos la base de la consulta
        $sql = "UPDATE usuarios SET nombre = ?, usuario = ?, rol = ?, estado = ?";
        $params = [$nombre, $usuario, $rol, $estado];

        // 2. ¿Se escribió algo en el password?
        if (!empty(trim($pass))) {
            $sql .= ", password = ?";
            $params[] = password_hash($pass, PASSWORD_DEFAULT);
        }

        // 3. Cerramos la consulta con el ID
        $sql .= " WHERE id = ?";
        $params[] = $id;

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        echo json_encode(['status' => 'success', 'message' => 'Usuario actualizado correctamente.']);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error al actualizar: ' . $e->getMessage()]);
    }
}