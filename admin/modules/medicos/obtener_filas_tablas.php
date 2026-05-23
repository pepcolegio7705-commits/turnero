<?php
// Solo iniciamos sesión si no está iniciada (prevención de errores)
if (session_status() === PHP_SESSION_NONE) { session_start(); }

$root = $_SERVER['DOCUMENT_ROOT'] . '/turnero/';
require_once $root . 'core/config.php';

if (!isset($_SESSION['medico_id'])) {
    http_response_code(401);
    exit;
}

$medico_id = $_SESSION['medico_id'];

// La consulta EXCLUYE el estado 'Atendido', por eso desaparecen al finalizar
$sql = "SELECT t.id AS turno_id, t.hora, t.estado, 
               p.nombre AS paciente_nombre, p.apellido AS paciente_apellido, p.dni AS paciente_dni
        FROM turnos t
        JOIN pacientes p ON t.paciente_id = p.id
        WHERE t.medico_id = ? 
        AND t.fecha = CURDATE()
        AND t.estado IN ('Espera', 'Llamando', 'Atendiendo')
        ORDER BY t.hora ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$medico_id]);
$pacientes = $stmt->fetchAll();

if (empty($pacientes)) {
    echo '<tr><td colspan="4" class="text-center text-muted p-4">No hay pacientes en espera</td></tr>';
    exit;
}

foreach ($pacientes as $p): 
    $badgeClass = ($p['estado'] == 'Llamando') ? 'bg-danger' : (($p['estado'] == 'Atendiendo') ? 'bg-warning' : 'bg-secondary');
?>
    <tr>
        <td><?php echo date('H:i', strtotime($p['hora'])); ?></td>
        <td>
            <strong><?php echo $p['paciente_nombre'] . ' ' . $p['paciente_apellido']; ?></strong><br>
            <small class="text-muted">DNI: <?php echo $p['paciente_dni']; ?></small>
        </td>
        <td>
            <span class="badge <?php echo $badgeClass; ?>"><?php echo $p['estado']; ?></span>
        </td>
        <td>
            <div class="btn-group">
                <button onclick="procesarAccion(<?php echo $p['turno_id']; ?>, 'llamar')" 
                        class="btn btn-primary btn-sm rounded-pill px-3 <?php echo ($p['estado'] == 'Llamando') ? 'disabled' : ''; ?>">
                    Llamar
                </button>
                <button onclick="procesarAccion(<?php echo $p['turno_id']; ?>, 'atendiendo')" 
                        class="btn btn-warning btn-sm px-3 <?php echo ($p['estado'] == 'Atendiendo') ? 'disabled' : ''; ?>">
                    Entró
                </button>
                <button onclick="procesarAccion(<?php echo $p['turno_id']; ?>, 'atendido')" 
                        class="btn btn-success btn-sm px-3">
                    Finalizar
                </button>
            </div>
        </td>
    </tr>
<?php endforeach; ?>