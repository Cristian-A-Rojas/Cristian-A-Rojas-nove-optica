<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/csrf.php';

require_login(); // usuario debe estar autenticado

// validar CSRF
$csrf = $_POST['csrf_token'] ?? '';
if (!csrf_verificar_token($csrf)) {
    registrar_log('csrf_fail', 'Intento CSRF en vaciar.php', 'ALERT');
    cerrar_sesion_segura();
}

// vaciar carrito
if (!empty($_SESSION['carrito'])) {
    unset($_SESSION['carrito']);
    registrar_log('carrito_vaciar', 'Carrito vaciado correctamente', 'INFO');
}

header('Location: /nove_optica/carrito/ver.php');
exit;
?>
