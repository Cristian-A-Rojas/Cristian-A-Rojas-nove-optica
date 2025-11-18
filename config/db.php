<?php
/**
 * NOVE ÓPTICA – Zero Trust Build V13.2
 * Configuración de seguridad, conexión y auditoría
 * Ultra-Stable AppServ Edition
 */

// ****************************** SESIÓN SEGURA ******************************
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_secure'   => !empty($_SERVER['HTTPS']),
        'cookie_samesite' => 'Strict',
        'use_strict_mode' => true,
        'use_only_cookies'=> true,
        'sid_length'      => 64,
        'sid_bits_per_character' => 6
    ]);
}

// Fingerprint de sesión (Zero-Trust baseline)
$fingerprint = hash('sha256', ($_SERVER['HTTP_USER_AGENT'] ?? '') . ($_SERVER['REMOTE_ADDR'] ?? ''));
if (!isset($_SESSION['fingerprint'])) {
    $_SESSION['fingerprint'] = $fingerprint;
} elseif ($_SESSION['fingerprint'] !== $fingerprint) {
    session_unset();
    session_destroy();
    die("Sesión no válida.");
}

// ****************************** CABECERAS DE SEGURIDAD ******************************
$headers = [
    "X-Frame-Options: DENY",
    "X-Content-Type-Options: nosniff",
    "Referrer-Policy: strict-origin",
    "Permissions-Policy: camera=(), microphone=(), geolocation=(), usb=()",
    "Cross-Origin-Opener-Policy: same-origin",
    "Cross-Origin-Embedder-Policy: require-corp",
    "Cross-Origin-Resource-Policy: same-origin",
    "Content-Security-Policy:
        default-src 'self';
        script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com;
        style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net;
        img-src 'self' data: https://cdn.jsdelivr.net;
        font-src 'self' https://fonts.gstatic.com;
        frame-ancestors 'none';
        object-src 'none';
        base-uri 'self';
        form-action 'self';
        upgrade-insecure-requests;"
];
foreach ($headers as $h) header($h);

// ****************************** LOGS Y ERRORES ******************************
date_default_timezone_set('Europe/Madrid');
$debug = false;

error_reporting(E_ALL);
ini_set('display_errors', $debug ? 1 : 0);
ini_set('log_errors', 1);

$log_main = __DIR__ . '/../logs/error_log.txt';
if (file_exists($log_main) && filesize($log_main) > 1048576) {
    rename($log_main, __DIR__ . '/../logs/error_log_' . date('Ymd_His') . '.txt');
}
ini_set('error_log', $log_main);


// ****************************** CONEXIÓN A BASE DE DATOS ******************************
function conectar_bd() {
    $srv = "localhost";
    $usr = "nove_supremo";
    $pwd = "tr3c3.1120+4";
    $db  = "nove_optica";

    $con = @mysqli_connect($srv, $usr, $pwd, $db);
    if (!$con) {
        error_log("[" . date('Y-m-d H:i:s') . "] Error de conexión: " . mysqli_connect_error());
        die("Error interno del sistema.");
    }
    mysqli_set_charset($con, "utf8mb4");
    return $con;
}

// ****************************** SANITIZACIÓN ******************************
function limpiar($valor) {
    $valor = trim($valor ?? '');
    $valor = strip_tags($valor);
    return htmlspecialchars($valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// ****************************** CSRF TOKEN ******************************
function generar_token_csrf() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    $_SESSION['csrf_hmac'] = hash_hmac('sha256', $_SESSION['csrf_token'], session_id());
    return $_SESSION['csrf_token'];
}
function verificar_token_csrf($token) {
    if (!isset($_SESSION['csrf_token'], $_SESSION['csrf_hmac'])) return false;
    $hmac = hash_hmac('sha256', $_SESSION['csrf_token'], session_id());
    return hash_equals($_SESSION['csrf_token'], $token) && hash_equals($_SESSION['csrf_hmac'], $hmac);
}

// ****************************** AUDITORÍA ******************************
function registrar_log($accion, $descripcion = '', $nivel = 'INFO') {
    $fecha = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'sin_ip';
    $usuario = $_SESSION['usuario'] ?? 'anonimo';
    $msg = "[$fecha] [$nivel] [$usuario@$ip] $accion - $descripcion" . PHP_EOL;
    file_put_contents(__DIR__ . '/../logs/auditoria.log', $msg, FILE_APPEND | LOCK_EX);
}

// ****************************** CIERRE ******************************
function cerrar_bd($conexion) {
    if ($conexion && mysqli_ping($conexion)) {
        mysqli_close($conexion);
    }
}
?>
