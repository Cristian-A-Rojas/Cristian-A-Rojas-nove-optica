<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/csrf.php';

require_login(); // se requiere sesión activa

header('Content-Type: application/json; charset=utf-8');

// validar CSRF
$csrf = $_POST['csrf_token'] ?? '';
if (!csrf_verificar_token($csrf)) {
    registrar_log('csrf_fail', 'CSRF en carrito/agregar.php', 'ALERT');
    http_response_code(403);
    echo json_encode(['error' => 'Token CSRF inválido']);
    exit;
}

// obtener datos
$codigo   = limpiar($_POST['codigo'] ?? '');
$nombre   = limpiar($_POST['nombre'] ?? '');
$precio   = (float)($_POST['precio'] ?? 0);
$imagen   = limpiar($_POST['imagen'] ?? '');
$cantidad = (int)($_POST['cantidad'] ?? 1);

// validar datos
if ($codigo === '' || $nombre === '' || $precio <= 0) {
    registrar_log('carrito_datos_invalidos', "Código: $codigo", 'WARN');
    echo json_encode(['error' => 'Datos inválidos']);
    exit;
}

// limitar cantidad (anti abuso)
if ($cantidad < 1) $cantidad = 1;
if ($cantidad > 10) $cantidad = 10;

// inicializar carrito
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// añadir o incrementar
if (isset($_SESSION['carrito'][$codigo])) {
    $_SESSION['carrito'][$codigo]['cantidad'] += $cantidad;
} else {
    $_SESSION['carrito'][$codigo] = [
        'nombre'   => $nombre,
        'precio'   => $precio,
        'imagen'   => $imagen,
        'cantidad' => $cantidad
    ];
}

registrar_log(
    'carrito_agregar',
    "Añadido al carrito → $codigo ($nombre), cant: $cantidad",
    'INFO'
);

// total de ítems en carrito
$total_items = 0;
foreach ($_SESSION['carrito'] as $p) {
    $total_items += $p['cantidad'];
}

echo json_encode([
    'ok' => true,
    'total_items' => $total_items
]);
exit;
?>
