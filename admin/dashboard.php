<?php
    // Incluimos tu cabecera (que ya tiene el session_start y la validación)
    session_start();
    require_once '../core/config.php';

    $hoy = date('Y-m-d');
    $es_admin = ($_SESSION['usuario_rol'] === 'admin');

    try {
        // Consultas para las métricas
        $t_hoy = $pdo->prepare("SELECT COUNT(*) FROM turnos WHERE fecha = ? AND estado != 'Cancelado'");
        $t_hoy->execute([$hoy]);
        $cant_hoy = $t_hoy->fetchColumn();

        $p_total = $pdo->query("SELECT COUNT(*) FROM pacientes")->fetchColumn();
        
        // Próximos 5 turnos para la tabla rápida
        $stmt = $pdo->prepare("SELECT t.hora, p.nombre, p.apellido, m.apellido as med_ape 
                               FROM turnos t 
                               INNER JOIN pacientes p ON t.paciente_id = p.id 
                               INNER JOIN medicos m ON t.medico_id = m.id 
                               WHERE t.fecha = ? AND t.hora >= TIME(NOW())
                               ORDER BY t.hora ASC LIMIT 5");
        $stmt->execute([$hoy]);
        $proximos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        error_log($e->getMessage());
    }

    try {
        // 1. Especialidades más solicitadas (Mayor a Menor)
        $sql_especialidades = "SELECT e.nombre, COUNT(t.id) as total 
                               FROM turnos t
                               INNER JOIN medicos m ON t.medico_id = m.id
                               INNER JOIN especialidades e ON m.especialidad_id = e.id
                               WHERE t.estado != 'Cancelado'
                               GROUP BY e.id 
                               ORDER BY total DESC";
        $esp_mas_pedidas = $pdo->query($sql_especialidades)->fetchAll(PDO::FETCH_ASSOC);

        // 2. Horarios más solicitados (Mayor a Menor)
        // Usamos TIME_FORMAT para agrupar por hora exacta
        $sql_horarios = "SELECT TIME_FORMAT(hora, '%H:00') as rango, COUNT(*) as total 
                         FROM turnos 
                         WHERE estado != 'Cancelado'
                         GROUP BY rango 
                         ORDER BY total DESC 
                         LIMIT 5";
        $horas_pico = $pdo->query($sql_horarios)->fetchAll(PDO::FETCH_ASSOC);

        // 3. Médicos con MENOS turnos (Menor a Mayor)
        // Útil para ver quién tiene agenda disponible o menos demanda
        $sql_medicos_demanda = "SELECT m.apellido, COUNT(t.id) as total 
                                FROM medicos m
                                LEFT JOIN turnos t ON m.id = t.medico_id AND t.estado != 'Cancelado'
                                WHERE m.estado = 1
                                GROUP BY m.id 
                                ORDER BY total ASC";
        $medicos_menos_pedidos = $pdo->query($sql_medicos_demanda)->fetchAll(PDO::FETCH_ASSOC);

        // 4. Días de más tránsito (Días de la semana)
        // 0 = Lunes (en algunas configs), usamos DAYNAME o DAYOFWEEK
        $sql_dias = "SELECT DAYNAME(fecha) as dia, COUNT(*) as total 
                     FROM turnos 
                     WHERE estado != 'Cancelado'
                     GROUP BY dia 
                     ORDER BY total DESC";
        // Nota: Si los nombres salen en inglés, se puede usar un CASE o SET lc_time_names
        $dias_transito = $pdo->query($sql_dias)->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        error_log("Error en estadísticas: " . $e->getMessage());
    }

    $rol = $_SESSION['usuario_rol'];
    $es_admin = ($rol === 'admin');

    // Consultas básicas para las tarjetas (Métricas generales)
    $stmt = $pdo->query("SELECT COUNT(*) FROM pacientes");
    $total_pacientes = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM turnos WHERE fecha = CURDATE()");
    $turnos_hoy = $stmt->fetchColumn();

    $total_especialidades = $pdo->query("SELECT COUNT(*) FROM especialidades")->fetchColumn();
    $medicos_activos = $pdo->query("SELECT COUNT(*) FROM medicos WHERE estado = 1")->fetchColumn();
?>

    <?php include("includes/header.php"); ?>

    <div class="main-card animate__animated animate__fadeIn">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-0">Panel de Control</h2>
                <p class="text-muted">Bienvenido, <?php echo $_SESSION['usuario_nombre']; ?> (<?php echo ucfirst($rol); ?>)</p>
            </div>
        </div>

        <!-- TARJETAS: Visibles para todos -->
        <div class="row g-4">
            <!-- Pacientes -->
            <div class="col-md-6 col-lg-3">
                <div class="card border-0 shadow-sm p-3 rounded-4 bg-primary text-white">
                    <div class="d-flex align-items-center mb-2">
                        <i data-lucide="users" class="me-2 opacity-75" style="width: 20px;"></i>
                        <div class="small fw-bold opacity-75">Pacientes Totales</div>
                    </div>
                    <div class="h3 fw-bold mb-0"><?php echo $total_pacientes; ?></div>
                </div>
            </div>

            <!-- Turnos -->
            <div class="col-md-6 col-lg-3">
                <div class="card border-0 shadow-sm p-3 rounded-4 bg-success text-white">
                    <div class="d-flex align-items-center mb-2">
                        <i data-lucide="calendar-check" class="me-2 opacity-75" style="width: 20px;"></i>
                        <div class="small fw-bold opacity-75">Turnos de Hoy</div>
                    </div>
                    <div class="h3 fw-bold mb-0"><?php echo $turnos_hoy; ?></div>
                </div>
            </div>

            <!-- Especialidades -->
            <div class="col-md-6 col-lg-3">
                <div class="card border-0 shadow-sm p-3 rounded-4 bg-info text-white">
                    <div class="d-flex align-items-center mb-2">
                        <i data-lucide="stethoscope" class="me-2 opacity-75" style="width: 20px;"></i>
                        <div class="small fw-bold opacity-75">Especialidades</div>
                    </div>
                    <div class="h3 fw-bold mb-0"><?php echo $total_especialidades; ?></div>
                </div>
            </div>

            <!-- Médicos Activos -->
            <div class="col-md-6 col-lg-3">
                <div class="card border-0 shadow-sm p-3 rounded-4 bg-warning text-white">
                    <div class="d-flex align-items-center mb-2">
                        <i data-lucide="user-md" class="me-2 opacity-75" style="width: 20px;"></i>
                        <div class="small fw-bold opacity-75">Médicos Activos</div>
                    </div>
                    <div class="h3 fw-bold mb-0"><?php echo $medicos_activos; ?></div>
                </div>
            </div>
        </div>

        <div class="row mt-5">
            <div class="col-12">
                <h4 class="fw-bold mb-4">Módulos Disponibles</h4>
                <div class="row g-3">
                    <!-- Siempre visible para Recepción y Admin -->
                    <div class="col-md-4">
                        <a href="<?php echo $url_base; ?>pacientes/" class="text-decoration-none">
                            <div class="p-4 bg-white rounded-4 border shadow-sm text-center">
                                <i data-lucide="users" class="text-primary mb-2"></i>
                                <div class="fw-bold text-dark">Gestión de Pacientes</div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="<?php echo $url_base; ?>turnos/lista" class="text-decoration-none">
                            <div class="p-4 bg-white rounded-4 border shadow-sm text-center">
                                <i data-lucide="calendar" class="text-primary mb-2"></i>
                                <div class="fw-bold text-dark">Control de Turnos</div>
                            </div>
                        </a>
                    </div>

                    <!-- SOLO ADMIN -->
                    <?php if ($es_admin): ?>
                    <div class="col-md-4">
                        <a href="<?php echo $url_base; ?>medicos/" class="text-decoration-none">
                            <div class="p-4 bg-white rounded-4 border shadow-sm text-center border-primary" style="border-style: dashed !important;">
                                <i data-lucide="user-cog" class="text-primary mb-2"></i>
                                <div class="fw-bold text-dark">Configuración Médicos</div>
                            </div>
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mt-2">
    <!-- Especialidades -->
    <div class="col-md-6 col-lg-3">
        <div class="card h-100 border-0 shadow-sm rounded-4">
            <div class="card-body">
                <h6 class="fw-bold mb-3"><i data-lucide="stethoscope" class="me-2 text-primary"></i>Especialidades</h6>
                <?php foreach(array_slice($esp_mas_pedidas, 0, 4) as $esp): ?>
                    <div class="mb-2">
                        <div class="d-flex justify-content-between small">
                            <span><?php echo $esp['nombre']; ?></span>
                            <span class="fw-bold"><?php echo $esp['total']; ?></span>
                        </div>
                        <div class="progress" style="height: 5px;">
                            <div class="progress-bar bg-primary" style="width: <?php echo ($esp['total'] * 100 / ($esp_mas_pedidas[0]['total'] ?: 1)); ?>%"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Horarios Pico -->
    <div class="col-md-6 col-lg-3">
        <div class="card h-100 border-0 shadow-sm rounded-4">
            <div class="card-body">
                <h6 class="fw-bold mb-3"><i data-lucide="clock" class="me-2 text-success"></i>Horas Pico</h6>
                <ul class="list-unstyled mb-0">
                    <?php foreach($horas_pico as $h): ?>
                        <li class="d-flex justify-content-between mb-2 small">
                            <span><?php echo $h['rango']; ?> hs</span>
                            <span class="badge bg-light text-dark"><?php echo $h['total']; ?> turnos</span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>

    <!-- Médicos Disponibles (Menos pedidos) -->
    <div class="col-md-6 col-lg-3">
        <div class="card h-100 border-0 shadow-sm rounded-4">
            <div class="card-body">
                <h6 class="fw-bold mb-3"><i data-lucide="user-plus" class="me-2 text-info"></i>Menos Demandados</h6>
                <?php foreach(array_slice($medicos_menos_pedidos, 0, 4) as $med): ?>
                    <div class="d-flex align-items-center mb-2">
                        <div class="flex-grow-1 small">Dr/a. <?php echo $med['apellido']; ?></div>
                        <div class="badge rounded-pill bg-soft-info text-info small"><?php echo $med['total']; ?> t.</div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Días de Tránsito -->
    <div class="col-md-6 col-lg-3">
        <div class="card h-100 border-0 shadow-sm rounded-4">
            <div class="card-body">
                <h6 class="fw-bold mb-3"><i data-lucide="trending-up" class="me-2 text-warning"></i>Días Pico</h6>
                <?php foreach(array_slice($dias_transito, 0, 3) as $d): ?>
                    <div class="mb-2 small d-flex justify-content-between">
                        <span class="text-capitalize"><?php echo $d['dia']; ?></span>
                        <span class="fw-bold"><?php echo $d['total']; ?> pac.</span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

    <?php include("includes/footer.php"); ?>

<script>
    lucide.createIcons();
</script>
