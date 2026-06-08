<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: /turnero/admin/login.php");
    exit();
}
$url_base = "/turnero/admin/modules/";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administrativo | Sistema de Turnos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-body: #f8fafc;
            --primary: #2563eb;
            --nav-dark: #0f172a;
        }
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-color: var(--bg-body);
            color: #1e293b;
        }
        .navbar { 
            background-color: var(--nav-dark) !important;
            padding: 1rem 0;
        }
        .nav-link {
            font-weight: 500;
            padding: 0.5rem 1rem !important;
            transition: all 0.3s;
        }
        .nav-link:hover { color: #3b82f6 !important; }
        .nav-link.active { color: #3b82f6 !important; background: rgba(59, 130, 246, 0.1); border-radius: 8px; }
        
        /* Estilo para los contenedores de los módulos */
        .main-card {
            background: white;
            border: none;
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            padding: 2rem;
            margin-top: 2rem;
        }
        .btn-primary {
            background-color: var(--primary);
            border: none;
            padding: 0.6rem 1.2rem;
            border-radius: 0.75rem;
            font-weight: 600;
        }
        /* Limpieza de Tablas */
        .table thead th {
            background-color: #f1f5f9;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            color: #64748b;
            border: none;
            padding: 1rem;
        }
        .table tbody td {
            padding: 1rem;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold d-flex align-items-center" href="">
            <span class="bg-primary p-2 rounded-3 me-2 text-white">
                <i data-lucide="activity" style="width: 20px; height: 20px;"></i>
            </span>
            TurneroAdmin
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto me-auto">

                <?php if ($_SESSION['usuario_rol'] === 'admin' && (!defined('DEMO_MODE') || !DEMO_MODE)): ?>
                <li class="nav-item">
                    <a class="nav-link px-3" href="/turnero/admin/usuarios">
                        <i data-lucide="shield-check" class="me-1" style="width:18px;"></i> Usuarios
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle px-3" href="#" id="navbarMedicos" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Médicos
                    </a>
                    <ul class="dropdown-menu border-0 shadow-sm" aria-labelledby="navbarMedicos">
                        <li><a class="dropdown-item" href="<?php echo $url_base; ?>medicos/">Gestionar Médicos</a></li>
                        <li><a class="dropdown-item" href="<?php echo $url_base; ?>medicos/horarios">Configurar Horarios</a></li>
                    </ul>
                </li>

                <li class="nav-item"><a class="nav-link px-3" href="<?php echo $url_base; ?>especialidades/">Especialidades</a></li>
                 <?php endif; ?>

                 <?php if ($_SESSION['usuario_rol'] === 'admin' || $_SESSION['usuario_rol'] === 'recepcion'): ?>
                <li class="nav-item"><a class="nav-link px-3" href="<?php echo $url_base; ?>pacientes/">Pacientes</a></li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle px-3" href="#" id="navbarTurnos" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Turnos
                    </a>
                    <ul class="dropdown-menu border-0 shadow-sm" aria-labelledby="navbarTurnos">
                        <li>
                            <a class="dropdown-item" href="<?php echo $url_base; ?>turnos/lista">
                                <i class="bi bi-list-ul me-2"></i> Gestión de Turnos
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="<?php echo $url_base; ?>turnos">
                                <i class="bi bi-plus-circle me-2"></i> Otorgar Nuevo Turno
                            </a>
                        </li>
                        
                    </ul>
                </li>
                <?php endif; ?>

                <?php if ($_SESSION['usuario_rol'] === 'admin'): ?>
                <li class="nav-item"><a class="nav-link px-3" href="<?php echo $url_base; ?>obras_sociales/">Obras Sociales</a></li>
                <li class="nav-item">
                    <a class="nav-link px-3" href="<?php echo $url_base; ?>auditoria/">
                        <i data-lucide="bar-chart-3" class="me-1" style="width:18px;"></i> Auditoría
                    </a>
                </li>                    
                <?php endif ?>

            </ul>
            <div class="d-flex align-items-center">
                <div class="text-end me-3">
                    <div class="text-white small fw-bold"><?php echo $_SESSION['usuario_nombre']; ?></div>
                    <div class="text-muted" style="font-size: 10px;">Administrador</div>
                </div>
                <a href="/turnero/admin/logout.php" class="btn btn-sm btn-outline-danger rounded-pill">Salir</a>
            </div>
        </div>
    </div>
</nav>
<div class="container pb-5">