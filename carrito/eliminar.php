<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/csrf.php';

require_login();

// validar token
$csrf = $_POST['csrf_token'] ?? '';
if (!csrf_verificar_token($csrf)) {
    registrar_log('csrf_fail', 'Intento CSRF en eliminar.php', 'ALERT');
    cerrar_sesion_segura();
}

// ID de producto
$id = $_POST['id'] ?? '';

if ($id !== '' && isset($_SESSION['carrito'][$id])) {
    unset($_SESSION['carrito'][$id]);
    registrar_log('carrito_eliminar', "Producto eliminado del carrito: ID $id", 'INFO');
}

header('Location: /nove_optica/carrito/ver.php');
exit;
?>
