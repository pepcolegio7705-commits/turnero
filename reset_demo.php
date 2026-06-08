<?php
/**
 * Script Automático de Reseteo de Demo (SINTEK - Sistema Turnero)
 * 
 * Diseñado para ejecutarse automáticamente vía Cron Job a las 03:00 AM.
 * Borra la base de datos de turnos y la restaura desde turnero.sql para
 * mantener la demo fresca.
 */

// 1. MEDIDA DE SEGURIDAD CRÍTICA
// Se requiere que se ejecute por CLI (Terminal/Cron) o con un token web explícito habilitado.
$allow_web_trigger = false; // Cambiar a true únicamente si necesitas forzar el reseteo desde el navegador
$secret_token = "SintekDemoReset_2026_SecureKey!";
$is_cli = (php_sapi_name() === 'cli');
$is_valid_web_request = ($allow_web_trigger && isset($_GET['token']) && $_GET['token'] === $secret_token);

if (!$is_cli && !$is_valid_web_request) {
    http_response_code(403);
    die("Acceso denegado. Solo ejecuciones locales por consola (CLI) permitidas por seguridad.");
}

// 2. CONFIGURACIÓN DE CONEXIONES Y ARCHIVOS SQL (Cargado de config.php)
require_once __DIR__ . '/core/config.php';

$sql_file = __DIR__ . '/turnero.sql';

echo "=== INICIANDO RUTINA DE RESETEO DIARIO DE TURNERO ===\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n\n";

if (!file_exists($sql_file)) {
    die("ERROR: No se encontró el archivo SQL limpio: {$sql_file}\n\n");
}

try {
    // Conexión directa con PDO utilizando la configuración existente
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Leer el contenido del archivo SQL
    $sqlContent = file_get_contents($sql_file);
    
    // Ejecutar el script SQL completo
    $pdo->exec($sqlContent);
    
    echo "ÉXITO: Base de datos Turnero (BD: " . DB_NAME . ") restaurada correctamente.\n\n";
} catch (PDOException $e) {
    echo "ERROR CRÍTICO: " . $e->getMessage() . "\n\n";
}

echo "=== RUTINA FINALIZADA ===\n";
?>
