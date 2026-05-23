<?php
    // Aumentamos un nivel si core está en la raíz de /turnero/
    // Si core está dentro de /admin/, deja los dos niveles ../../
    $root = $_SERVER['DOCUMENT_ROOT'] . '/turnero/'; 
    
    require_once $root . 'core/Database.php';  
    require_once $root . 'core/config.php';
    
    session_start();

    // 1. Verificar si la conexión existe
    if (!isset($pdo)) {
        die("Error: La variable de conexión \$pdo no está definida. Revisa Database.php");
    }

    if (isset($_SESSION['medico_id'])) {
        header("Location: panel-medico"); // Usamos la URL amigable
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $dni = trim($_POST['dni']);
        $password = $_POST['password'];

        try {
            $stmt = $pdo->prepare("SELECT * FROM medicos WHERE dni = ?");
            $stmt->execute([$dni]);
            $medico = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($medico && password_verify($password, $medico['password'])) {
                // Seteamos la sesión
                $_SESSION['medico_id'] = $medico['id'];
                $_SESSION['medico_nombre'] = $medico['nombre'] . " " . $medico['apellido'];
                
                // IMPORTANTE: Redirigir a la URL amigable definida en .htaccess
                header("Location: panel-medico"); 
                exit();
            } else {
                $error = "DNI o contraseña incorrectos.";
            }
        } catch (PDOException $e) {
            $error = "Error en la base de datos: " . $e->getMessage();
        }
    }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Profesionales | Turnero</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        /* Heredamos tus estilos exactos */
        :root { --primary-color: #0ea5e9; --dark-color: #0f172a; } /* Color celeste médico */
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; height: 100vh; margin: 0; display: flex; align-items: center; justify-content: center; }
        .login-container { background: white; border-radius: 1.5rem; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.1); max-width: 1000px; width: 95%; display: flex; min-height: 600px; }
        .login-image { background-image: url('https://images.unsplash.com/photo-1622253692010-333f2da6031d?auto=format&fit=crop&q=80&w=2000'); background-size: cover; background-position: center; width: 50%; position: relative; }
        .login-image-overlay { position: absolute; bottom: 0; left: 0; right: 0; padding: 3rem; background: linear-gradient(transparent, rgba(15, 23, 42, 0.8)); color: white; }
        .login-form-side { width: 50%; padding: 4rem; display: flex; flex-direction: column; justify-content: center; }
        .form-control { padding: 0.75rem 1rem; border-radius: 0.75rem; border: 1px solid #e2e8f0; background-color: #f8fafc; }
        .btn-primary { background-color: var(--primary-color); border: none; padding: 0.8rem; border-radius: 0.75rem; font-weight: 600; transition: all 0.3s ease; }
        .icon-box { width: 48px; height: 48px; background: #f0f9ff; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--primary-color); margin-bottom: 1.5rem; }
        @media (max-width: 768px) { .login-image { display: none; } .login-form-side { width: 100%; padding: 2rem; } }
    </style>
</head>
<body>

    <div class="login-container">
        <div class="login-image">
            <div class="login-image-overlay">
                <h2 class="fw-bold">Panel de Profesionales</h2>
                <p class="mb-0 text-white-50">Acceda a su agenda del día y gestione sus llamados de forma inmediata.</p>
            </div>
        </div>

        <div class="login-form-side">
            <div class="icon-box">
                <i data-lucide="user-cog"></i>
            </div>
            <h3 class="fw-bold text-dark mb-1">Acceso Médico</h3>
            <p class="text-muted mb-4">Ingrese su DNI y contraseña asignada.</p>

            <?php if(isset($error)): ?>
                <div class="alert alert-danger d-flex align-items-center" role="alert">
                    <i data-lucide="alert-circle" class="me-2" style="width: 18px;"></i>
                    <div class="small"><?php echo $error; ?></div>
                </div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">Número de DNI</label>
                    <input type="text" name="dni" class="form-control" placeholder="Ingrese su DNI" required autofocus>
                </div>
                
                <div class="mb-4">
                    <label class="form-label small fw-bold text-muted">Contraseña</label>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>

                <button type="submit" class="btn btn-primary w-100 mb-3 text-white">
                    Ingresar al Panel
                </button>
                
                <div class="text-center">
                    <a href="login-admin" class="text-decoration-none small text-muted">
                        <i data-lucide="arrow-left" class="me-1" style="width: 14px;"></i> Volver al Login Administrativo
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://unpkg.com/lucide@latest"></script>
    <script>lucide.createIcons();</script>
</body>
</html>