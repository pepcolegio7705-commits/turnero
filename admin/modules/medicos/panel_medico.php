<?php
    // Suponiendo que ya tienes la sesión del médico iniciada y $medico_id definida
    session_start();

    // 2. Definir la ruta raíz y requerir la configuración de la base de datos
    $root = $_SERVER['DOCUMENT_ROOT'] . '/turnero/';
    require_once $root . 'core/config.php'; 

    // 3. Verificar si el médico realmente está logueado
    if (!isset($_SESSION['medico_id'])) {
        // Si no hay sesión, mandarlo al login
        header("Location: /turnero/admin/modules/medicos/login-medico.php");
        exit;
    }

// Ahora sí, ya puedes usar $pdo y $_SESSION['medico_id']
    $medico_id = $_SESSION['medico_id'];
    $nombre_medico = $_SESSION['medico_nombre'] ?? 'Médico';

    $sql = "SELECT 
                t.id AS turno_id, 
                t.hora, 
                t.estado, 
                p.nombre AS paciente_nombre, 
                p.apellido AS paciente_apellido,
                p.dni AS paciente_dni
            FROM turnos t
            JOIN pacientes p ON t.paciente_id = p.id
            WHERE t.medico_id = ? 
            AND t.fecha = CURDATE()
            AND t.estado IN ('Espera', 'Llamando', 'Atendiendo')
            ORDER BY t.hora ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$medico_id]);
    $pacientes = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Médico - Dr. <?php echo $nombre_medico; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
        .navbar { background-color: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .card { border: none; border-radius: 1rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
        
        /* Colores de estados */
        .badge-llamar { background-color: #ef4444; color: white; } /* Rojo */
        .badge-espera { background-color: #3b82f6; color: white; } /* Azul */
        .badge-atendiendo { background-color: #f59e0b; color: white; } /* Naranja */
        .badge-confirmado { background-color: #10b981; color: white; } /* Verde */
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg mb-4">
    <div class="container">
        <span class="navbar-brand fw-bold text-primary">Panel Profesional</span>
        <div class="d-flex align-items-center">
            <span class="me-3 text-muted small">Bienvenido, <strong>Dr. <?php echo $nombre_medico; ?></strong></span>
            <a href="salir-medico" class="btn btn-outline-danger btn-sm rounded-pill">Cerrar Sesión</a>
        </div>
    </div>
</nav>

<div class="container">
    <div class="row mb-4">
        <div class="col">
            <h2 class="fw-bold">Pacientes en Espera</h2>
            <p class="text-muted">Gestión de llamados en tiempo real - <?php echo date('d/m/Y'); ?></p>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Hora</th>
                            <th>Paciente</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-pacientes-body">
                        <!-- Carga inicial: Traemos las filas desde el archivo externo para evitar duplicar código -->
                        <?php include __DIR__ . '/obtener_filas_tablas.php'; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ... aquí termina tu tabla y tu HTML ... -->

<script src="https://unpkg.com/lucide@latest"></script>
<script>
    // Inicializar iconos de Lucide
lucide.createIcons();

let estaProcesando = false;

// Función para refrescar solo la tabla
async function refrescarTabla() {
    try {
        // La barra inicial / es clave para que busque desde la raíz de localhost
        const response = await fetch('/turnero/admin/modules/medicos/obtener_filas_tablas.php');
        
        if (response.status === 401) {
            window.location.href = '/turnero/admin/login-medico';
            return;
        }

        if (response.ok) {
            const html = await response.text();
            const contenedor = document.getElementById('tabla-pacientes-body');
            if (contenedor) {
                contenedor.innerHTML = html;
                if (typeof lucide !== 'undefined') lucide.createIcons();
            }
        }
    } catch (error) {
        console.error("Error en refresco automático:", error);
    }
}

// Configurar el intervalo (cada 10 segundos)
setInterval(refrescarTabla, 10000);

// Función para procesar acciones (Llamar, Entró, Finalizar)
async function procesarAccion(id, accion) {
    if (estaProcesando) return;
    
    // Bloqueamos refresco automático mientras procesamos
    estaProcesando = true; 

    const formData = new FormData();
    formData.append('turno_id', id);
    formData.append('accion', accion);

    try {
        const response = await fetch('/turnero/admin/modules/medicos/acciones_medico.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            // IMPORTANTE: Forzamos el refresco inmediato de la tabla
            await refrescarTabla(); 
        } else {
            alert("Error: " + result.message);
        }
    } catch (error) {
        console.error("Error en la acción:", error);
        alert("No se pudo procesar la acción.");
    } finally {
        // Liberamos el bloqueo
        estaProcesando = false; 
    }
}
</script>
</body>
</html>