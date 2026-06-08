<?php
require_once '../../../core/config.php';
header('Content-Type: application/json');

if (defined('DEMO_MODE') && DEMO_MODE) {
    echo json_encode(['status' => 'error', 'message' => 'Acción no disponible en el entorno de demostración.']);
    exit;
}

if ($_POST) {
    $nombre   = $_POST['nombre'];
    $usuario  = $_POST['usuario'];
    $rol      = $_POST['rol'];
    $estado   = $_POST['estado'];
    
    // Encriptación obligatoria
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    try {
        // Verificar si el usuario ya existe
        $check = $pdo->prepare("SELECT id FROM usuarios WHERE usuario = ?");
        $check->execute([$usuario]);
        if ($check->fetch()) {
            throw new Exception("El nombre de usuario ya está en uso.");
        }

        $sql = "INSERT INTO usuarios (usuario, password, nombre, rol, estado, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$usuario, $password, $nombre, $rol, $estado]);

        echo json_encode(['status' => 'success', 'message' => 'Usuario creado correctamente.']);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}