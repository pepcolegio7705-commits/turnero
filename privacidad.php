<?php include 'core/config.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Política de Privacidad | Sintek Salud</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f8f9fa; }
        .legal-container { background: white; border-radius: 15px; padding: 40px; margin-top: 50px; margin-bottom: 50px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); }
        h2 { color: #00c3ff; font-weight: 700; margin-bottom: 25px; }
        .highlight-box { border-left: 4px solid #00c3ff; background: #e1f5fe; padding: 20px; border-radius: 0 10px 10px 0; margin: 25px 0; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top shadow-sm py-3">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="#">
            <span class="fw-bold fs-3 text-primary">SINTEK<span class="text-info">SALUD</span></span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto fw-medium">
                <li class="nav-item"><a class="nav-link px-3" href="./">Inicio</a></li>
                <li class="nav-item"><a class="nav-link px-3" href="./#nosotros">Institucional</a></li>
                <li class="nav-item"><a class="nav-link px-3" href="./#staff">Cuerpo Médico</a></li>
                <li class="nav-item ms-lg-3">
                    <a class="btn btn-primary rounded-pill px-4 shadow-sm" href="#turnos">
                        <i class="bi bi-calendar-check me-2"></i>Turnos Online
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
<div class="container">
    <div class="legal-container">
        <h2>Política de Privacidad</h2>
        <p>En <strong>Sintek Salud</strong>, valoramos la privacidad de nuestros pacientes. Esta política explica cómo manejamos sus datos personales de acuerdo con las normativas vigentes.</p>

        <div class="highlight-box">
            <strong>Compromiso de Confidencialidad:</strong> Sus datos nunca serán vendidos, cedidos ni distribuidos a terceros con fines comerciales. Solo se utilizarán para la gestión de turnos médicos y comunicación administrativa.
        </div>

        <h4>Recolección de Datos</h4>
        <p>Recopilamos información cuando usted completa nuestro formulario de reserva, específicamente:</p>
        <ul>
            <li>Nombre y Apellido.</li>
            <li>Teléfono de contacto.</li>
            <li>Obra Social o Prepaga.</li>
            <li>Profesional médico de interés.</li>
        </ul>

        <h4>Uso de Cookies</h4>
        <p>Utilizamos cookies técnicas para mejorar la velocidad de carga del sitio y analizar el tráfico de manera anónima a través de Google Analytics.</p>

        <h4>Derechos del Usuario (ARCO)</h4>
        <p>Usted tiene derecho a solicitar el acceso, rectificación o eliminación de sus datos de nuestra base de datos en cualquier momento enviando un correo a <strong>contacto@sinteksalud.com.ar</strong>.</p>
    </div>
</div>
<footer class="footer-sintek text-white pt-5">
    <div class="container">
        <div class="row g-4 pb-4">
            <!-- Columna 1: Logo y Eslogan -->
            <div class="col-lg-3 col-md-6">
                <img src="assets/img/staff/logo.png" alt="Sintek Salud" class="mb-4" style="max-height: 70px; ">
                <p class="small opacity-75">
                    Más de 25 años comprometidos con la excelencia en su salud. Centro médico de excelencia con tecnología de vanguardia.
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
                    <p class="small mb-1 opacity-75">Av. San Martín 896, Rawson, Chubut (9103)</p>
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
                    <a href="#" class="small opacity-50 me-3 text-white text-decoration-none">Política de Privacidad</a>
                    <a href="#" class="small opacity-50 text-white text-decoration-none">Términos de Uso</a>
                </div>
            </div>
        </div>
    </div>
</footer>
</body>
</html>