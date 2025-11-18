<?php
require_once __DIR__ . '/../config/db.php';

// // parámetros de sesión
if (session_status() === PHP_SESSION_NONE) {
    session_name('NOVESESSID');
    session_start([
        'cookie_httponly'        => true,
        'cookie_secure'          => !empty($_SERVER['HTTPS']),
        'cookie_samesite'        => 'Strict',
        'use_strict_mode'        => true,
        'use_only_cookies'       => true,
        'sid_length'             => 64,
        'sid_bits_per_character' => 6,
        'gc_maxlifetime'         => 1800 // vida sesión
    ]);
}

// // generar fingerprint reforzado
function generar_fingerprint() {
    $ip  = $_SERVER['REMOTE_ADDR'] ?? '';
    $ua  = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $salt = 'NOVEOPTICA_SAAS_2025'; // salt fija segura
    return hash_hmac('sha256', $ip . $ua, $salt);
}

$fingerprint_actual = generar_fingerprint();

// // validar fingerprint al crear
if (!isset($_SESSION['fingerprint'])) {
    $_SESSION['fingerprint'] = $fingerprint_actual;
}

// // proteger contra secuestro
if (!hash_equals($_SESSION['fingerprint'], $fingerprint_actual)) {
    session_unset();
    session_destroy();
    die('Sesión no válida.');
}

// // expiración por inactividad
$timeout = 900; // 15 min
if (isset($_SESSION['ultimo_uso']) && (time() - $_SESSION['ultimo_uso']) > $timeout) {
    session_unset();
    session_destroy();
    die('Sesión expirada.');
}
$_SESSION['ultimo_uso'] = time();

// // rotación de ID
if (!isset($_SESSION['creado'])) {
    $_SESSION['creado'] = time();
} elseif (time() - $_SESSION['creado'] > 600) {
    session_regenerate_id(true);
    $_SESSION['creado'] = time();
}

// // bloqueo temporal
if (!isset($_SESSION['lockout_until'])) $_SESSION['lockout_until'] = 0;
function bloquear_usuario_temporalmente($segundos = 300) {
    $_SESSION['lockout_until'] = time() + $segundos;
}
function is_locked() {
    return (time() < ($_SESSION['lockout_until'] ?? 0));
}
if (is_locked()) {
    die('Cuenta bloqueada temporalmente.');
}

// // sanitización global
if (!function_exists('limpiar')) {
    function limpiar($v) { return htmlspecialchars(trim($v), ENT_QUOTES, 'UTF-8'); }
}
foreach ($_POST as $k => $v) $_POST[$k] = limpiar($v);
foreach ($_GET as $k => $v)  $_GET[$k]  = limpiar($v);

// // sesión activa
function sesion_activa() {
    return !empty($_SESSION['usuario_id']);
}

// // cerrar sesión
function cerrar_sesion_segura() {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        setcookie(session_name(), '', time() - 42000, '/');
    }
    session_destroy();
    header('Location: /nove_optica/auth/login.php');
    exit;
}
?>
