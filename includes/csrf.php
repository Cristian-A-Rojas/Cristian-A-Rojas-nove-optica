<?php

// ===============================
//  CSRF – GENERAR TOKEN
// ===============================
function csrf_generate() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    $_SESSION['csrf_hmac'] = hash_hmac('sha256', $_SESSION['csrf_token'], session_id());
    return $_SESSION['csrf_token'];
}

// ===============================
//  CSRF – VERIFICAR TOKEN
// ===============================
function csrf_verify($token) {
    if (!isset($_SESSION['csrf_token'], $_SESSION['csrf_hmac'])) return false;

    $hmac = hash_hmac('sha256', $_SESSION['csrf_token'], session_id());

    return hash_equals($_SESSION['csrf_token'], $token)
        && hash_equals($_SESSION['csrf_hmac'], $hmac);
}

?>
