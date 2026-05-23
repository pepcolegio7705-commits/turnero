<?php
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/turnero/',
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_start();

$root = $_SERVER['DOCUMENT_ROOT'] . '/turnero/';
require_once $root . 'core/config.php'; 

header('Content-Type: application/json');

// 1. Verificación de sesión
if (!isset($_SESSION['medico_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Sesión no encontrada.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $turno_id = $_POST['turno_id'] ?? null;
    $accion = $_POST['accion'] ?? null;

    if (!$turno_id || !$accion) {
        echo json_encode(['success' => false, 'message' => 'Faltan datos']);
        exit;
    }

    try {
        $sql = "";
        
        // 2. Definimos la consulta según la acción
        if ($accion === 'llamar') {
            $sql = "UPDATE turnos SET estado = 'Llamando', updated_at = NOW() WHERE id = ?";
        } elseif ($accion === 'atendiendo') {
            $sql = "UPDATE turnos SET estado = 'Atendiendo' WHERE id = ?";
        } elseif ($accion === 'atendido') {
            $sql = "UPDATE turnos SET estado = 'Atendido' WHERE id = ?";
        }

        // 3. Ejecutamos una sola vez si la acción es válida
        if ($sql !== "") {
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute([$turno_id]);

            if ($result) {
                echo json_encode([
                    'success' => true, 
                    'nuevo_estado' => $accion,
                    'message' => 'Estado actualizado correctamente'
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'No se pudo actualizar el registro']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Acción no reconocida']);
        }

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error de BD: ' . $e->getMessage()]);
    }
    exit;
}