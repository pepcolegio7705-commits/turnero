<?php
// Configuración de la Base de Datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'turnero');
define('DB_USER', 'root');
define('DB_PASS', '');

// Configuración de Seguridad para Encriptación
// IMPORTANTE: Cambia esta clave por algo aleatorio y no la pierdas
define('HASH_KEY', 'CMA_Austral_Secure_2026_#99'); 
define('SECRET_IV', '101214'); // Vector de inicialización para cifrado
define('METHOD', 'aes-256-cbc');

// Modo Demostración (Oculta y bloquea la gestión de usuarios)
define('DEMO_MODE', true);

// Configuración de zona horaria (Rawson, Chubut)
date_default_timezone_set('America/Argentina/Buenos_Aires');

// URL Base para facilitar links (ajusta según tu servidor local)
define('BASE_URL', 'http://localhost/turnero/');

define('ROOT_PATH', dirname(__DIR__) . '/');

try {
    // Conexión mediante PDO
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    // En producción, no mostrar el error real por seguridad
    error_log("Error de conexión: " . $e->getMessage());
    die("Lo sentimos, hubo un problema técnico. Intente más tarde.");
}
$pdo->exec("SET lc_time_names = 'es_ES'");