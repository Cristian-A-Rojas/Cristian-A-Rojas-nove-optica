<?php
// ===============================
//  SESIÓN SEGURA
// ===============================
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_secure'   => !empty($_SERVER['HTTPS']),
        'cookie_samesite' => 'Strict',
        'use_strict_mode' => true,
        'use_only_cookies'=> true
    ]);
}

// ===============================
//  FINGERPRINT ZERO-TRUST
// ===============================
$fingerprint = hash('sha256', ($_SERVER['HTTP_USER_AGENT'] ?? '') . ($_SERVER['REMOTE_ADDR'] ?? ''));
if (!isset($_SESSION['fingerprint'])) {
    $_SESSION['fingerprint'] = $fingerprint;
} elseif ($_SESSION['fingerprint'] !== $fingerprint) {
    session_unset();
    session_destroy();
    die("Sesión no válida.");
}

// ===============================
//  CABECERAS DE SEGURIDAD
// ===============================
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin");
header("Permissions-Policy: camera=(), microphone=(), geolocation=(), usb=()");
header("Cross-Origin-Opener-Policy: same-origin");
header("Cross-Origin-Embedder-Policy: require-corp");
header("Cross-Origin-Resource-Policy: same-origin");

header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net; img-src 'self' data: https://cdn.jsdelivr.net; font-src 'self' https://fonts.gstatic.com; object-src 'none'; frame-ancestors 'none'; form-action 'self'; base-uri 'self'; upgrade-insecure-requests");


// ===============================
//  BRUTE-FORCE PROTECTION
// ===============================
if (!isset($_SESSION['failed_attempts'])) $_SESSION['failed_attempts'] = 0;
if (!isset($_SESSION['lockout_until'])) $_SESSION['lockout_until'] = 0;

function is_locked() {
    return time() < ($_SESSION['lockout_until'] ?? 0);
}

function register_fail() {
    $_SESSION['failed_attempts']++;
    if ($_SESSION['failed_attempts'] >= 5) {
        $_SESSION['lockout_until'] = time() + 300;
        $_SESSION['failed_attempts'] = 0;
    }
}

function reset_fails() {
    $_SESSION['failed_attempts'] = 0;
    $_SESSION['lockout_until'] = 0;
}

// ===============================
//  CSRF PROTECTION
// ===============================
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
    return hash_equals($_SESSION['csrf_token'], $token)
        && hash_equals($_SESSION['csrf_hmac'], $hmac);
}
?>

