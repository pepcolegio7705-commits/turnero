<?php 

    include "core/config.php";


 ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sintek Salud | Compromiso con su Bienestar</title>
    <!-- Bootstrap 5 & Google Fonts (Poppins para ese look moderno) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<!-- Navegación Estilo Promedic -->
<nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top shadow-sm">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="#">
            <span class="fw-bold fs-3 text-primary">SINTEK<span class="text-info">SALUD</span></span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto fw-medium">
                <li class="nav-item"><a class="nav-link px-3" href="#inicio">Inicio</a></li>
                <li class="nav-item"><a class="nav-link px-3" href="#nosotros">Institucional</a></li>
                <li class="nav-item"><a class="nav-link px-3" href="#staff">Cuerpo Médico</a></li>
                <li class="nav-item ms-lg-3">
                    <a class="btn btn-primary rounded-pill px-4 shadow-sm" href="admin/login">
                        <i class="bi bi-calendar-check me-2"></i>Turnos Online
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Hero Section: El primer impacto -->
<section id="inicio" class="hero-section position-relative overflow-hidden">
    <div class="container">
        <div class="row align-items-center min-vh-75">
            <div class="col-lg-6 text-white mb-5 mb-lg-0">
                <h6 class="text-uppercase fw-bold text-info mb-3">Más de 20 años de trayectoria</h6>
                <h1 class="display-3 fw-bold mb-4">Tu salud, nuestra máxima prioridad.</h1>
                <p class="lead mb-4">Brindamos atención médica de excelencia con tecnología de vanguardia y un equipo humano altamente capacitado.</p>
                <div class="d-flex gap-3">
                    <a href="#staff" class="btn btn-outline-light btn-lg rounded-pill px-4">Conocer Staff</a>
                    <a href="https://wa.me/TUNUMERO" class="btn btn-success btn-lg rounded-pill px-4"><i class="bi bi-whatsapp me-2"></i>Urgencias</a>
                </div>
            </div>
            
           <!-- Selector de Turno Flotante -->
            <div class="col-lg-5 offset-lg-1">
                <div class="card border-0 shadow-lg rounded-4 p-4 p-md-5 bg-white">
                    <h3 class="fw-bold text-dark mb-4 text-center">Reserva tu Turno</h3>
                    <form id="formTurnoPublico">
                        <!-- PASO 1: Identificación -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">DNI del Paciente</label>
                            <div class="input-group">
                                <input type="number" id="paciente_dni" name="dni" class="form-control" required>
                                <button type="button" class="btn btn-info text-white" onclick="verificarPaciente()">Verificar</button>
                            </div>
                            <div id="msj_verificacion"></div>
                        </div>

                        <!-- Campos de paciente nuevo (Se activan si existe === false) -->
                        <div id="campos_paciente_nuevo" class="d-none border-start border-warning border-4 ps-3 mb-3 bg-light p-2 rounded">
                            <p class="small text-warning fw-bold mb-2">Registro de Paciente Nuevo:</p>
                            <div class="mb-2">
                                <input type="text" id="p_nombre" name="nombre" class="form-control form-control-sm" placeholder="Nombre">
                            </div>
                            <div class="mb-2">
                                <input type="text" id="p_apellido" name="apellido" class="form-control form-control-sm" placeholder="Apellido">
                            </div>
                            <div class="mb-2">
                                <input type="tel" id="p_telefono" name="telefono" class="form-control form-control-sm" placeholder="Teléfono/WhatsApp">
                            </div>
                        </div>

                        <!-- PASO 2: Selección de Médico -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Profesional</label>
                            <select class="form-select" id="select_medico" name="medico_id" required onchange="cargarConfiguracionMedico(this.value)">
                                <option value="">Seleccione profesional...</option>
                                <?php
                                $stmt = $pdo->query("SELECT id, apellido, nombre FROM medicos WHERE estado = 1 ORDER BY apellido ASC");
                                while($m = $stmt->fetch()) echo "<option value='{$m['id']}'>Dr. {$m['apellido']} {$m['nombre']}</option>";
                                ?>
                            </select>
                        </div>

                        <!-- Selección de Fecha -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Fecha del Turno</label>
                            <input type="date" id="fecha_turno" name="fecha" class="form-control border-light bg-light" 
                                   min="<?= date('Y-m-d') ?>" onchange="cargarHorariosDisponibles()" disabled>
                        </div>

                       <!-- Selección de Horario (Estilo Chips) -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Horarios Disponibles</label>
                            <!-- Contenedor donde se cargarán los botones de hora -->
                            <div id="contenedor_slots" class="d-flex flex-wrap gap-2 p-2 border rounded bg-light" style="min-height: 50px;">
                                <small class="text-muted">Seleccione una fecha para ver horarios.</small>
                            </div>
                            <!-- Input oculto para enviar la hora seleccionada en el form -->
                            <input type="hidden" id="hora_final" name="hora" required>
                        </div>

                        <!-- Selector de Cobertura -->
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-muted">Cobertura Médica</label>
                            <select class="form-select" id="select_os" name="obra_social_id" required disabled>
                                <option value="">Seleccione cobertura...</option>
                                <!-- Opción para pacientes particulares -->
                                <option value="NULL">Particular / Sin Cobertura</option>
                            </select>
                        </div>

                        <button type="submit" id="btn_solicitar" class="btn btn-primary w-100" disabled>SOLICITAR TURNO</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Sección Staff Médico -->
<section id="staff" class="py-5 bg-light">
    <div class="container py-5">
        <div class="text-center mb-5">
            <h6 class="text-primary fw-bold text-uppercase">Nuestros Profesionales</h6>
            <h2 class="display-5 fw-bold">Cuerpo Médico Especializado</h2>
            <p class="text-muted mx-auto" style="max-width: 600px;">
                Contamos con un equipo de especialistas comprometidos con la excelencia y la calidez en la atención de cada paciente.
            </p>
        </div>

        <div class="row g-4">
            <?php
            // Consulta para obtener médicos y sus especialidades
            $sql_staff = "SELECT m.*, e.nombre as especialidad 
                          FROM medicos m 
                          LEFT JOIN especialidades e ON m.especialidad_id = e.id 
                          WHERE m.estado = 1 
                          ORDER BY m.apellido ASC";
            $stmt_staff = $pdo->query($sql_staff);
            
            // Contador para rotar imágenes genéricas si quieres variedad
            $i = 1; 
            $total_fotos_genericas = 4; // Supongamos que tienes medico_1.jpg hasta medico_4.jpg

            while($medico = $stmt_staff->fetch()):
                // Opción A: Usar una imagen fija para todos
                // $foto = 'assets/img/staff/doctor-generic.jpg';

                // Opción B: Rotar entre fotos genéricas para que el staff se vea variado
                $num_foto = ($i % $total_fotos_genericas) + 1;
                $foto = "assets/img/staff/medico_{$num_foto}.png";
                
                // Si prefieres usar una lógica por género (asumiendo que existe el campo 'sexo')
                /*
                if($medico['sexo'] == 'F') {
                    $foto = 'assets/img/staff/doctora-generic.jpg';
                } else {
                    $foto = 'assets/img/staff/doctor-generic.jpg';
                }
                */
            ?>
            <div class="col-md-6 col-lg-3">
                <div class="card h-100 border-0 shadow-sm card-staff">
                    <div class="position-relative overflow-hidden" style="height: 350px;">
                        <img src="<?php echo $foto; ?>" 
                             class="card-img-top img-fluid h-100 w-100" 
                             style="object-fit: cover;"
                             alt="Dr. <?php echo $medico['apellido']; ?>">
                        <div class="card-staff-overlay">
                            <button onclick="seleccionarMedico(<?php echo $medico['id']; ?>)" 
                                    class="btn btn-light btn-sm rounded-pill px-4 fw-bold">
                                AGENDAR TURNO
                            </button>
                        </div>
                    </div>
                    <div class="card-body text-center bg-white">
                        <h5 class="fw-bold mb-1 text-dark">
                            Dr. <?php echo $medico['apellido'] . ' ' . $medico['nombre']; ?>
                        </h5>
                        <p class="text-primary small fw-bold mb-0">
                            <?php echo strtoupper($medico['especialidad'] ?? 'Especialista'); ?>
                        </p>
                        <hr class="mx-auto w-25 my-3" style="border-top: 2px solid var(--celeste-sintek);">
                        <div class="d-flex justify-content-center gap-3">
                            <a href="https://wa.me/TUNUMERO" class="text-muted small"><i class="bi bi-whatsapp"></i></a>
                            <a href="#inicio" onclick="seleccionarMedico(<?php echo $medico['id']; ?>)" class="text-muted small"><i class="bi bi-calendar-event"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            <?php 
                $i++; 
            endwhile; 
            ?>
        </div>
    </div>
</section>

<!-- Sección Especialidades -->
<section id="especialidades" class="py-5 bg-white">
    <div class="container py-5">
        <div class="row align-items-center mb-5">
            <div class="col-lg-6">
                <h6 class="text-primary fw-bold text-uppercase">Nuestros Servicios</h6>
                <h2 class="display-5 fw-bold">Especialidades Médicas</h2>
            </div>
            <div class="col-lg-6">
                <p class="text-muted mb-0">
                    En **Sintek Salud**, ofrecemos una amplia gama de servicios médicos especializados, 
                    respaldados por profesionales de primer nivel y tecnología de última generación.
                </p>
            </div>
        </div>

        <div class="row g-4">
            <?php
            // Consultamos las especialidades registradas
            $stmt_esp = $pdo->query("SELECT * FROM especialidades ORDER BY nombre ASC");
            
            // Mapeo de iconos sugeridos según el nombre (opcional)
            $iconos = [
                'Cardiología' => 'bi-heart-pulse',
                'Pediatría' => 'bi-person-arms-up',
                'Traumatología' => 'bi-bandaid',
                'Ginecología' => 'bi-gender-female',
                'Dermatología' => 'bi-droplet-half',
                'Nutrición' => 'bi-apple',
                'Clínica Médica' => 'bi-hospital'
            ];

            while($esp = $stmt_esp->fetch()):
                // Seleccionamos icono del array o uno por defecto
                $icon_class = $iconos[$esp['nombre']] ?? 'bi-shield-plus';
            ?>
            <div class="col-6 col-md-4 col-lg-3">
                <div class="card h-100 border-0 shadow-sm item-especialidad text-center p-4">
                    <div class="icon-box mb-3 mx-auto d-flex align-items-center justify-content-center">
                        <i class="bi <?php echo $icon_class; ?> fs-2 text-primary"></i>
                    </div>
                    <h5 class="fw-bold mb-0"><?php echo $esp['nombre']; ?></h5>
                    <a href="#inicio" onclick="filtrarPorEspecialidad(<?php echo $esp['id']; ?>)" class="stretched-link"></a>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>
<!-- Sección Institucional -->
<section id="nosotros" class="py-5 bg-light">
    <div class="container py-5">
        <div class="row align-items-center">
            <!-- Columna de Imagen -->
            <div class="col-lg-6 mb-5 mb-lg-0">
                <div class="position-relative">
                    <!-- Imagen principal de la clínica -->
                    <img src="assets/img/institucional.png" alt="Instalaciones Sintek Salud" class="img-fluid rounded-4 shadow-lg">
                    
                    <!-- Cuadro de Experiencia Flotante -->
                    <div class="bg-primary text-white p-4 rounded-4 position-absolute bottom-0 start-0 translate-middle-x d-none d-md-block shadow-lg" style="margin-left: 50px; margin-bottom: -30px;">
                        <h2 class="fw-bold mb-0">20+</h2>
                        <p class="small mb-0">Años de Trayectoria</p>
                    </div>
                </div>
            </div>

            <!-- Columna de Texto -->
            <div class="col-lg-6 ps-lg-5">
                <h6 class="text-primary fw-bold text-uppercase mb-3">Nuestra Institución</h6>
                <h2 class="display-5 fw-bold mb-4">Comprometidos con la Excelencia Médica</h2>
                <p class="lead text-muted mb-4">
                    **Sintek Salud** nació con la misión de transformar la atención médica en la región, 
                    combinando el más alto nivel profesional con un trato profundamente humano.
                </p>
                
                <!-- Lista de Valores/Pilares -->
                <div class="row g-4 mb-4">
                    <div class="col-sm-6">
                        <div class="d-flex align-items-center">
                            <div class="bg-white p-2 rounded-circle shadow-sm me-3">
                                <i class="bi bi-check-lg text-primary fw-bold"></i>
                            </div>
                            <span class="fw-bold">Alta Complejidad</span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="d-flex align-items-center">
                            <div class="bg-white p-2 rounded-circle shadow-sm me-3">
                                <i class="bi bi-check-lg text-primary fw-bold"></i>
                            </div>
                            <span class="fw-bold">Atención Integral</span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="d-flex align-items-center">
                            <div class="bg-white p-2 rounded-circle shadow-sm me-3">
                                <i class="bi bi-check-lg text-primary fw-bold"></i>
                            </div>
                            <span class="fw-bold">Tecnología de Punta</span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="d-flex align-items-center">
                            <div class="bg-white p-2 rounded-circle shadow-sm me-3">
                                <i class="bi bi-check-lg text-primary fw-bold"></i>
                            </div>
                            <span class="fw-bold">Calidez Humana</span>
                        </div>
                    </div>
                </div>

                <a href="" class="btn btn-primary btn-lg rounded-pill px-5 shadow">Conocer Instalaciones</a>
            </div>
        </div>
    </div>
</section>

<!-- Sección Ubicación -->
<section id="contacto" class="py-5 bg-white">
    <div class="container py-5">
        <!-- Encabezado -->
        <div class="text-center mb-5">
            <h6 class="text-primary fw-bold text-uppercase">Encuéntrenos</h6>
            <h2 class="display-5 fw-bold">Ubicación Estratégica</h2>
            <p class="text-muted mx-auto" style="max-width: 600px;">
                Contamos con instalaciones modernas y de fácil acceso para su mayor comodidad.
            </p>
        </div>

        <!-- Botones de Selección de Sede (Tabs) -->
        <div class="d-flex justify-content-center gap-3 mb-5">
            <ul class="nav nav-pills custom-location-tabs" id="pills-tab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active rounded-pill px-4" id="pills-sede1-tab" data-bs-toggle="pill" data-bs-target="#pills-sede1" type="button" role="tab">
                        <i class="bi bi-building me-2"></i>Sintek Centro Médico
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-pill px-4" id="pills-sede2-tab" data-bs-toggle="pill" data-bs-target="#pills-sede2" type="button" role="tab">
                        <i class="bi bi-geo-alt me-2"></i>Sintek Salud Sur
                    </button>
                </li>
            </ul>
        </div>

        <!-- Contenido de las Sedes -->
        <div class="tab-content" id="pills-tabContent">
            <!-- SEDE 1 -->
            <div class="tab-pane fade show active" id="pills-sede1" role="tabpanel">
                <div class="row align-items-center bg-light rounded-4 overflow-hidden shadow-sm">
                    <div class="col-lg-5 p-5">
                        <h3 class="fw-bold mb-4">Sintek Centro Médico</h3>
                        
                        <div class="d-flex mb-4">
                            <div class="icon-circle bg-white text-primary me-3 shadow-sm">
                                <i class="bi bi-geo-alt-fill"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0">Dirección</h6>
                                <p class="text-muted mb-0">Av. Santa Fé 896<br>Rawson, Chubut (9103)</p>
                            </div>
                        </div>

                        <div class="d-flex mb-4">
                            <div class="icon-circle bg-white text-primary me-3 shadow-sm">
                                <i class="bi bi-clock-fill"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0">Horarios</h6>
                                <p class="text-muted mb-0">Lun-Vie: 08:00 - 20:00<br>Sábados: 08:00 - 12:00</p>
                            </div>
                        </div>
                        
                        <a href="https://maps.google.com" target="_blank" class="btn btn-outline-primary rounded-pill px-4 mt-3">
                            Cómo llegar <i class="bi bi-arrow-right ms-2"></i>
                        </a>
                    </div>
                    <div class="col-lg-7 p-0">
                        <!-- Mapa de Google Maps -->
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3115.54!2d-65.03!3d-43.3!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zNDPCsDE4JzAwLjAiUyA2NcKwMDEnNDguMCJX!5e0!3m2!1ses-419!2sar!4v1620000000000" width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                    </div>
                </div>
            </div>
            
            <!-- SEDE 2 (Opcional) -->
            <div class="tab-pane fade" id="pills-sede2" role="tabpanel">
                <!-- Estructura similar a la anterior con otros datos -->
            </div>
        </div>
    </div>
</section>

<footer class="footer-sintek text-white pt-5">
    <div class="container">
        <div class="row g-4 pb-4">
            <!-- Columna 1: Logo y Eslogan -->
            <div class="col-lg-3 col-md-6">
                <img src="assets/img/staff/logo.png" alt="Sintek Salud" class="mb-4" style="max-height: 70px; ">
                <p class="small opacity-75">
                    Más de 20 años comprometidos con la excelencia en su salud. Centro médico de excelencia con tecnología de vanguardia.
                </p>
            </div>

            <!-- Columna 2: Navegación -->
            <div class="col-lg-2 col-md-6 ps-lg-5">
                <h5 class="fw-bold mb-4">Navegación</h5>
                <ul class="list-unstyled footer-links">
                    <li><a href="#inicio">Inicio</a></li>
                    <li><a href="#especialidades">Especialidades</a></li>
                    <li><a href="#staff">Profesionales</a></li>
                    <li><a href="#contacto">Ubicación</a></li>
                </ul>
            </div>

            <!-- Columna 3: Contacto -->
            <div class="col-lg-3 col-md-6">
                <h5 class="fw-bold mb-4">Contacto</h5>
                <p class="mb-2 small"><span class="text-info fw-bold">WhatsApp</span><br> 2804-XXXXXX</p>
                <p class="small"><span class="text-info fw-bold">Email</span><br> contacto@sinteksalud.com.ar</p>
            </div>

            <!-- Columna 4: Sedes y Horarios -->
            <div class="col-lg-4 col-md-6">
                <h5 class="fw-bold mb-4">Ubicación y Horarios</h5>
                <div class="mb-4">
                    <h6 class="text-info small fw-bold mb-1">Sintek Centro Médico</h6>
                    <p class="small mb-1 opacity-75">Av. Santa Fé 896, Rawson, Chubut (9103)</p>
                    <p class="small mb-0 opacity-75">Lun-Vie: 08:00 - 20:00 | Sáb: 08:00 - 12:00</p>
                </div>
                <div class="pt-2 border-top border-secondary">
                    <h6 class="text-info small fw-bold mb-1">Sintek Salud Sur</h6>
                    <p class="small mb-1 opacity-75">Av. Antártida Argentina XXX, Rawson</p>
                    <p class="small mb-0 opacity-75">Lun-Vie: 08:00 - 20:00</p>
                </div>
            </div>
        </div>

        <!-- Barra Inferior de Copyright -->
        <div class="footer-bottom py-3 border-top border-secondary">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="small mb-0 opacity-50">© 2026 Sintek Salud S.A. Todos los derechos reservados.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="privacidad" class="small opacity-50 me-3 text-white text-decoration-none">Política de Privacidad</a>
                    <a href="terminos" class="small opacity-50 text-white text-decoration-none">Términos de Uso</a>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- Botón de WhatsApp Flotante -->
<a href="https://wa.me/TUNUMERO" class="wa-float" target="_blank">
    <i class="bi bi-whatsapp"></i>
</a>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    /**
     * 1. SELECCIÓN DESDE TARJETAS DE STAFF
     */
    function seleccionarMedico(id) {
        // 1. Limpiar primero lo que sea que esté seleccionado
        document.getElementById('fecha_turno').value = "";
        document.getElementById('hora_final').value = "";
        
        // 2. Seleccionar el médico en el combo
        const select = document.getElementById('select_medico');
        select.value = id;
        
        // 3. Disparar manualmente la carga de configuración
        cargarConfiguracionMedico(id);
        
        // 4. Mover el foco al formulario
        document.getElementById('formTurnoPublico').scrollIntoView({ behavior: 'smooth' });
    }

    /**
     * 2. VERIFICACIÓN DE PACIENTE (DNI)
     */
    function verificarPaciente() {
        const dni = document.getElementById('paciente_dni').value;
        const msj = document.getElementById('msj_verificacion');
        const divNuevo = document.getElementById('campos_paciente_nuevo');
        const btn = document.getElementById('btn_solicitar');

        if (dni.length < 7) {
            msj.innerHTML = "<small class='text-danger'>DNI inválido</small>";
            return;
        }

        msj.innerHTML = "<small class='text-muted'>Verificando...</small>";

        fetch(`verificar_paciente.php?dni=${dni}`)
            .then(res => res.json())
            .then(data => {
                if (data.existe) {
                    msj.innerHTML = `<div class='text-success small mt-1 fw-bold'>✓ Paciente: ${data.apellido}, ${data.nombre}</div>`;
                    divNuevo.classList.add('d-none');
                    toggleInputsNuevo(false);
                } else {
                    msj.innerHTML = `<div class='text-warning small mt-1 fw-bold'>! Paciente no registrado. Complete los datos:</div>`;
                    divNuevo.classList.remove('d-none');
                    toggleInputsNuevo(true);
                }
                btn.disabled = false;
            })
            .catch(err => {
                msj.innerHTML = "<small class='text-danger'>Error al verificar</small>";
                console.error(err);
            });
    }

    function toggleInputsNuevo(estado) {
        const ids = ['p_nombre', 'p_apellido', 'p_telefono'];
        ids.forEach(id => {
            const el = document.getElementById(id);
            if (el) el.required = estado;
        });
    }

    // Permitir Enter en el DNI
    document.getElementById('paciente_dni').addEventListener('keypress', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            verificarPaciente();
        }
    });

    /**
     * 3. CONFIGURACIÓN DEL MÉDICO (Obra Social y habilitar fecha)
     */
    function cargarConfiguracionMedico(medicoId) {
        const inputFecha = document.getElementById('fecha_turno');
        const contenedorSlots = document.getElementById('contenedor_slots');
        const inputHora = document.getElementById('hora_final');
        const selectOS = document.getElementById('select_os');

        if (!medicoId) {
            inputFecha.disabled = true;
            inputFecha.value = "";
            selectOS.disabled = true;
            selectOS.innerHTML = '<option value="">Seleccione cobertura...</option>';
            return;
        }

        // 1. Habilitamos la fecha y reseteamos valor
        inputFecha.disabled = false;
        inputFecha.value = ""; 
        
        // 2. Limpiamos selección previa de horarios
        inputHora.value = "";
        contenedorSlots.innerHTML = '<small class="text-muted">Seleccione una fecha para ver horarios.</small>';
        
        // 3. Cargamos las coberturas y habilitamos el select de OS
        cargarCoberturasMedico(medicoId); 
    }

    /**
     * 4. CARGA DE HORARIOS DISPONIBLES POR FECHA
     */
    function cargarHorariosDisponibles() {
        const medico_id = document.getElementById('select_medico').value;
        const fecha = document.getElementById('fecha_turno').value;
        const contenedor = document.getElementById('contenedor_slots');
        const inputHora = document.getElementById('hora_final');

        if (!medico_id || !fecha) return;

        // Reset de selección previa
        inputHora.value = "";
        contenedor.innerHTML = '<div class="spinner-border spinner-border-sm text-primary"></div>';

        // FormData para enviar los datos por POST (como hace tu sistema)
        let formData = new FormData();
        formData.append('medico_id', medico_id);
        formData.append('fecha', fecha);

        fetch('get_horarios_disponibles.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.status && data.slots.length > 0) {
                let html = '';
                data.slots.forEach(hora => {
                    // Creamos los botones con la misma estética de tu panel
                    html += `<button type="button" 
                                class="btn btn-sm btn-outline-primary fw-bold btn-hora-chip" 
                                onclick="seleccionarHora(this, '${hora}')">${hora}</button>`;
                });
                contenedor.innerHTML = html;
            } else {
                contenedor.innerHTML = '<small class="text-danger fw-bold">No hay turnos disponibles para esta fecha.</small>';
            }
        })
        .catch(err => {
            console.error(err);
            contenedor.innerHTML = '<small class="text-danger">Error al cargar horarios.</small>';
        });
    }

    /**
     * Maneja el clic en los botones de hora
     */
    function seleccionarHora(elemento, hora) {
        // 1. Quitamos el estilo activo de todos los botones
        document.querySelectorAll('.btn-hora-chip').forEach(btn => {
            btn.classList.remove('btn-primary', 'text-white');
            btn.classList.add('btn-outline-primary');
        });

        // 2. Aplicamos estilo activo al botón clickeado
        elemento.classList.remove('btn-outline-primary');
        elemento.classList.add('btn-primary', 'text-white');

        // 3. Guardamos el valor en el input oculto para que lo reciba el PHP
        document.getElementById('hora_final').value = hora;
        
        // 4. Habilitamos el botón de solicitar si todo lo demás está ok
        verificarHabilitacionBoton();
    }

    /**
     * Verifica si se puede habilitar el botón de envío
     */
    function verificarHabilitacionBoton() {
        const hora = document.getElementById('hora_final').value;
        const btn = document.getElementById('btn_solicitar');
        if(hora !== "") {
            btn.disabled = false;
        }
    }

    /**
     * 5. ENVÍO DEL FORMULARIO
     */
    document.getElementById('formTurnoPublico').onsubmit = function(e) {
        e.preventDefault();
        const btn = document.getElementById('btn_solicitar');
        const respuesta = document.getElementById('respuesta_final'); 
        
        // Bloqueo de interfaz
        btn.disabled = true;
        const originalText = "SOLICITAR TURNO"; 
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Procesando...';

        fetch('procesar_turno.php', {
            method: 'POST',
            body: new FormData(this)
        })
        .then(r => {
            if (!r.ok) throw new Error('Error en la respuesta del servidor');
            return r.json();
        })
        .then(data => {
            if (data.status === 'success') {
                Swal.fire({
                    title: '¡Turno Agendado!',
                    text: data.message,
                    icon: 'success',
                    showCancelButton: true,
                    confirmButtonText: '<i class="bi bi-file-pdf"></i> Ver Comprobante',
                    cancelButtonText: 'Cerrar',
                    reverseButtons: true
                }).then((result) => {
                    // --- REINICIO TOTAL DEL FORMULARIO (Se ejecuta siempre al cerrar el Swal) ---
                    const form = document.getElementById('formTurnoPublico');
                    if (form) {
                        form.reset(); 

                        // 1. Limpiar contenedores y mensajes
                        document.getElementById('msj_verificacion').innerHTML = "";
                        document.getElementById('contenedor_slots').innerHTML = 
                            '<small class="text-muted">Seleccione una fecha para ver horarios.</small>';
                        
                        // 2. Vaciar campos ocultos y habilitar entrada inicial
                        document.getElementById('hora_final').value = "";
                        document.getElementById('paciente_dni').disabled = false; // Aseguramos que el DNI sea editable

                        // 3. Bloquear campos dependientes
                        document.getElementById('fecha_turno').disabled = true;
                        document.getElementById('select_os').disabled = true;
                        document.getElementById('btn_solicitar').disabled = true;
                        
                        // 4. Resetear Obra Social y ocultar campos de nuevo paciente
                        const divNuevo = document.getElementById('campos_paciente_nuevo');
                        if(divNuevo) divNuevo.classList.add('d-none');
                        document.getElementById('select_os').innerHTML = '<option value="">Seleccione cobertura...</option>';

                        // 5. Scroll al inicio del formulario para el siguiente usuario
                        form.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }

                    // --- ABRIR PDF SI SE CONFIRMÓ ---
                    if (result.isConfirmed) {
                        window.open(`imprimir_ticket.php?id=${data.turno_id}`, '_blank');
                    }
                });
            } else {
                Swal.fire({
                    title: 'Atención',
                    text: data.message,
                    icon: 'warning',
                    confirmButtonColor: '#3085d6'
                });
            }
        })
        .catch(error => {
            console.error("Error en el envío:", error);
            Swal.fire('Error', 'Hubo un problema de conexión. Intente nuevamente.', 'error');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerText = originalText;
        });
    };

   function cargarCoberturasMedico(medicoId) {
        const selectOS = document.getElementById('select_os');
        
        fetch(`get_coberturas_medico.php?medico_id=${medicoId}`)
            .then(res => res.json())
            .then(data => {
                // Iniciamos el HTML con la opción por defecto y la opción Particular
                let html = '<option value="">Seleccione cobertura...</option>';
                html += '<option value="NULL">Particular / Sin Cobertura</option>';
                
                // Agregamos las que vienen de la base de datos
                if(data && data.length > 0) {
                    data.forEach(os => {
                        html += `<option value="${os.id}">${os.nombre}</option>`;
                    });
                }
                
                selectOS.innerHTML = html;
                selectOS.disabled = false;
            })
            .catch(err => {
                console.error("Error cargando coberturas:", err);
                // En caso de error, al menos permitimos la opción Particular
                selectOS.innerHTML = '<option value="">Seleccione cobertura...</option><option value="NULL">Particular / Sin Cobertura</option>';
                selectOS.disabled = false;
            });
    }
</script>
</body>
</html>