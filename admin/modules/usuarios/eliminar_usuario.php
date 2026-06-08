<?php
require_once '../../../core/config.php';
session_start();
header('Content-Type: application/json');

if (defined('DEMO_MODE') && DEMO_MODE) {
    echo json_encode(['status' => 'error', 'message' => 'Acción no disponible en el entorno de demostración.']);
    exit;
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    if ($id == $_SESSION['usuario_id']) {
        echo json_encode(['status' => 'error', 'message' => 'No puedes desactivarte a ti mismo.']);
        exit;
    }

    try {
        // En lugar de borrar, cambiamos estado a 0
        $stmt = $pdo->prepare("UPDATE usuarios SET estado = 0 WHERE id = ?");
        $stmt->execute([$id]);

        echo json_encode(['status' => 'success', 'message' => 'El usuario ha sido desactivado.']);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}