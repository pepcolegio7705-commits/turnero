<?php
 
    require_once '../core/config.php';
    session_start();

    // Si ya tiene sesión, mandarlo al dashboard (URL AMIGABLE)
    if (isset($_SESSION['usuario_id'])) {
        header("Location: /turnero/admin/dashboard");
        exit();
    }

    if ($_POST) {
        $usuario = trim($_POST['usuario']);
        $password = $_POST['password'];

        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE usuario = ? and estado = 1");
        $stmt->execute([$usuario]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Guardamos los datos clave en la SESSION
            $_SESSION['usuario_id'] = $user['id'];
            $_SESSION['usuario_nombre'] = $user['nombre'];
            $_SESSION['usuario_rol'] = $user['rol'];
            // Redirección al Dashboard Amigable
            header("Location: /turnero/admin/dashboard");
            exit();
        } else {
            $error = "El usuario o la contraseña son incorrectos o no existe.";
        }
    }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Administrativo | Turnero</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2563eb;
            --dark-color: #0f172a;
        }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f8fafc;
            height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: white;
            border-radius: 1.5rem;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.1);
            max-width: 1000px;
            width: 95%;
            display: flex;
            min-height: 600px;
        }
        .login-image {
            background-image: url('https://images.unsplash.com/photo-1576091160550-2173dba999ef?auto=format&fit=crop&q=80&w=2070');
            background-size: cover;
            background-position: center;
            width: 50%;
            position: relative;
        }
        .login-image-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 3rem;
            background: linear-gradient(transparent, rgba(15, 23, 42, 0.8));
            color: white;
        }
        .login-form-side {
            width: 50%;
            padding: 4rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .form-control {
            padding: 0.75rem 1rem;
            border-radius: 0.75rem;
            border: 1px solid #e2e8f0;
            background-color: #f8fafc;
        }
        .form-control:focus {
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
            border-color: var(--primary-color);
        }
        .btn-primary {
            background-color: var(--primary-color);
            border: none;
            padding: 0.8rem;
            border-radius: 0.75rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #1d4ed8;
            transform: translateY(-2px);
        }
        .icon-box {
            width: 48px;
            height: 48px;
            background: #eff6ff;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
        }

        @media (max-width: 768px) {
            .login-image { display: none; }
            .login-form-side { width: 100%; padding: 2rem; }
        }
    </style>
</head>
<body>

    <div class="login-container">
        <div class="login-image">
            <div class="login-image-overlay">
                <h2 class="fw-bold">Gestión Hospitalaria Eficiente</h2>
                <p class="mb-0 text-white-50">Optimiza la atención de tus pacientes con nuestro sistema inteligente de turnos.</p>
            </div>
        </div>

        <div class="login-form-side">
            <div class="icon-box">
                <i data-lucide="shield-check"></i>
            </div>
            <h3 class="fw-bold text-dark mb-1">Bienvenido de nuevo</h3>
            <p class="text-muted mb-4">Ingresa tus credenciales para acceder al panel.</p>

            <?php if(isset($error)): ?>
                <div class="alert alert-danger d-flex align-items-center" role="alert">
                    <i data-lucide="alert-circle" class="me-2" style="width: 18px;"></i>
                    <div class="small"><?php echo $error; ?></div>
                </div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">Nombre de Usuario</label>
                    <div class="position-relative">
                        <input type="text" name="usuario" class="form-control" placeholder="admin" required autofocus>
                    </div>
                </div>
                
                <div class="mb-4">
                    <div class="d-flex justify-content-between">
                        <label class="form-label small fw-bold text-muted">Contraseña</label>
                    </div>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>

                <button type="submit" class="btn btn-primary w-100 mb-3">
                    Iniciar Sesión
                </button>

                <div class="position-relative my-4 text-center">
                    <hr class="text-muted">
                    <span class="position-absolute top-50 start-50 translate-middle bg-white px-3 small text-muted">¿Eres Profesional?</span>
                </div>

                <a href="login-medico" class="btn btn-outline-info w-100 mb-4 rounded-3 d-flex align-items-center justify-content-center py-2 border-2" style="border-radius: 0.75rem !important;">
                    <i data-lucide="user-cog" class="me-2" style="width: 18px;"></i> Acceso Panel Médico
                </a>

                <div class="text-center">
                    <a href="../" class="text-decoration-none small text-muted">
                        <i data-lucide="arrow-left" class="me-1" style="width: 14px;"></i> Volver a la Landing Page
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        lucide.createIcons();
    </script>
</body>
</html>