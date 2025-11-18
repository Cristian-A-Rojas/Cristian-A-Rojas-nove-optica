<?php
/**
 * NOVE ÓPTICA – Zero Trust Build V13.2
 * Módulo de protección CSRF reforzado
 * Ultra-Stable AppServ Edition
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/session.php';

// 
// ⚙️ PARÁMETROS GENERALES
// 
define('CSRF_TTL', 600); // 10 minutos

 
// 🔐 CREAR TOKEN NUEVO

function csrf_generar_token() {
    if (empty($_SESSION['csrf_token']) || time() > ($_SESSION['csrf_expira'] ?? 0)) {
        $token  bin2hex(random_bytes(32)); // 256 bits
        $hmac   hash_hmac('sha256', $token, session_id());
        $_SESSION['csrf_token']   $token;
        $_SESSION['csrf_hmac']    $hmac;
        $_SESSION['csrf_expira']  time() + CSRF_TTL;

        registrar_log('csrf_new', 'Nuevo token CSRF generado', 'DEBUG');
    }
    return $_SESSION['csrf_token'];
}

 
// ✅ VALIDAR TOKEN
 
function csrf_verificar_token($token_recibido) {
    if (empty($_SESSION['csrf_token']) || empty($_SESSION['csrf_hmac'])) {
        registrar_log('csrf_fail', 'Token inexistente en sesión', 'WARN');
        return false;
    }
    if (time() > ($_SESSION['csrf_expira'] ?? 0)) {
        registrar_log('csrf_expired', 'Token expirado', 'INFO');
        return false;
    }

    $hmac_local  hash_hmac('sha256', $_SESSION['csrf_token'], session_id());
    $token_ok    hash_equals($_SESSION['csrf_token'], $token_recibido);
    $hmac_ok     hash_equals($_SESSION['csrf_hmac'], $hmac_local);

    if (!$token_ok || !$hmac_ok) {
        registrar_log('csrf_invalid', 'Token o HMAC incorrecto', 'WARN');
        return false;
    }

    return true;
}

 
// 🔄 ROTAR TOKEN (por seguridad extra)
 
function csrf_rotar_token() {
    unset($_SESSION['csrf_token'], $_SESSION['csrf_hmac'], $_SESSION['csrf_expira']);
    return csrf_generar_token();
}

 
// 🧩 IMPRIMIR TOKEN EN FORMULARIOS

function csrf_input() {
    $t  csrf_generar_token();
    echo '<input type"hidden" name"csrf_token" value"' . 
         htmlspecialchars($t, ENT_QUOTES, 'UTF-8') . 
         '" autocomplete"off">';
}

// 🧾 REGISTRO DE EVENTO
 
registrar_log('csrf_check', 'Módulo CSRF inicializado correctamente', 'DEBUG');
?>
