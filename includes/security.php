<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/csrf.php';

/*
   🔐 UTILIDADES DE ROL
 */
function is_admin() {
    return ($_SESSION['rol'] ?? '') === 'admin';
}

function is_cliente() {
    return ($_SESSION['rol'] ?? '') === 'cliente';
}

function sesion_activa() {
    return !empty($_SESSION['usuario_id']);
}

/* 
   🔐 ENFORCE LOGIN (solo rutas privadas)
 */
function require_login() {
    if (!sesion_activa()) {
        header("Location: /nove_optica/auth/login.php");
        exit;
    }
}

/* 
   🔐 ENFORCE ADMIN + 2FA
 */
function enforce_admin() {

    if (!sesion_activa()) {
        header("Location: /nove_optica/auth/login.php");
        exit;
    }

    if (!is_admin()) {
        header('HTTP/1.1 403 Forbidden');
        exit("Acceso denegado.");
    }

    // 2FA requerido
    if (empty($_SESSION['2fa_validado'])) {
        header("Location: /nove_optica/auth/2fa.php");
        exit;
    }
}

/* 
   🔐 CSRF GLOBAL PARA PETICIONES POST
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && empty($_POST['check_integrity'])
    && basename($_SERVER['PHP_SELF']) !== 'logout.php'
) {
    $token = $_POST['csrf_token'] ?? '';
    if (!csrf_verificar_token($token)) {
        registrar_log('csrf_fail', 'CSRF detectado en POST', 'WARN');
        cerrar_sesion_segura();
    }
}

/* 
   🔐 BLOQUEO TEMPORAL
 */
if (is_locked()) {
    exit("Cuenta bloqueada temporalmente.");
}

/* 
   🔐 CONTROL RUTAS PRIVADAS POR CARPETA
 */

// Rutas de administración
if (strpos($_SERVER['PHP_SELF'], '/admin/') !== false) {
    enforce_admin();
}

// Rutas privadas de cliente
$privadas = [
    '/carrito/',
    '/checkout/',
    '/perfil.php'
];

foreach ($privadas as $r) {
    if (strpos($_SERVER['PHP_SELF'], $r) !== false) {
        require_login();
    }
}

/* 
   🔍 LOG DE ACCESO
 */
registrar_log(
    'acceso_ok',
    "Ruta {$_SERVER['PHP_SELF']} | Usuario " . ($_SESSION['usuario'] ?? 'publico'),
    'DEBUG'
);

