<?php
/**
 * Encripta un ID u otro dato sensible
 */
function encriptar($data) {
    $key = hash('sha256', HASH_KEY);
    $iv = substr(hash('sha256', SECRET_IV), 0, 16);
    $output = openssl_encrypt($data, METHOD, $key, 0, $iv);
    return base64_encode($output);
}

/**
 * Desencripta un dato
 */
function desencriptar($data) {
    $key = hash('sha256', HASH_KEY);
    $iv = substr(hash('sha256', SECRET_IV), 0, 16);
    $output = openssl_decrypt(base64_decode($data), METHOD, $key, 0, $iv);
    return $output;
}

/**
 * Limpia entradas de texto para evitar XSS
 */
function limpiarCadena($cadena) {
    // Si la cadena es null o no está definida, devolvemos un string vacío
    $cadena = $cadena ?? ""; 
    
    // Ahora sí podemos usar trim y otras funciones de string
    $cadena = trim($cadena);
    $cadena = stripslashes($cadena);
    $cadena = htmlspecialchars($cadena, ENT_QUOTES, 'UTF-8');
    
    return $cadena;
}

/**
 * Formateador de fechas para humanos (Ej: 2026-04-28 -> 28/04/2026)
 */
function formatearFecha($fecha) {
    return date('d/m/Y', strtotime($fecha));
}

/**
 * Genera una respuesta JSON estandarizada para AJAX
 */
function responderJSON($estado, $mensaje, $data = null) {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => $estado, // true o false
        'message' => $mensaje,
        'data' => $data
    ]);
    exit;
}