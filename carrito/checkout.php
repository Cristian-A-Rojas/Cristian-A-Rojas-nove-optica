<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../config/db.php';

require_login(); // usuario debe estar autenticado

$conn = conectar_bd();

// validar CSRF
$csrf = $_POST['csrf_token'] ?? '';
if (!csrf_verificar_token($csrf)) {
    registrar_log('csrf_fail', 'CSRF en checkout.php', 'ALERT');
    die('Token CSRF inválido');
}

// carrito vacío
$carrito = $_SESSION['carrito'] ?? [];
if (empty($carrito)) {
    registrar_log('checkout_vacio', 'Intento checkout sin carrito', 'WARN');
    die('Tu carrito está vacío.');
}

// calcular total
$total = 0;
foreach ($carrito as $item) {
    $cant = max(1, (int)$item['cantidad']);
    $precio = max(0, (float)$item['precio']);
    $total += $cant * $precio;
}

// iniciar transacción
mysqli_begin_transaction($conn);

try {
    // crear pedido
    $sqlPed = "INSERT INTO pedidos (id_usuario, total, estado) VALUES (?, ?, 'pendiente')";
    $stmtPed = mysqli_prepare($conn, $sqlPed);
    mysqli_stmt_bind_param($stmtPed, 'id', $_SESSION['usuario_id'], $total);
    mysqli_stmt_execute($stmtPed);
    $id_pedido = mysqli_insert_id($conn);

    // preparar inserción items
    $sqlItem = "INSERT INTO pedido_items (id_pedido, id_producto, cantidad, precio_unitario)
                VALUES (?, ?, ?, ?)";
    $stmtItem = mysqli_prepare($conn, $sqlItem);

    foreach ($carrito as $codigo => $i) {
        $idp = obtener_id_producto($conn, $codigo);
        if ($idp <= 0) {
            throw new Exception("Producto no encontrado en DB: $codigo");
        }

        $cant = max(1, (int)$i['cantidad']);
        $precio = max(0, (float)$i['precio']);

        mysqli_stmt_bind_param($stmtItem, 'iiid',
            $id_pedido,
            $idp,
            $cant,
            $precio
        );
        mysqli_stmt_execute($stmtItem);
    }

    // confirmar transacción
    mysqli_commit($conn);

    // limpiar carrito
    unset($_SESSION['carrito']);

    registrar_log(
        'pedido_creado',
        "Pedido #$id_pedido creado por usuario_id=" . $_SESSION['usuario_id'],
        'INFO'
    );

    header("Location: /nove_optica/confirmacion.php?pedido=$id_pedido");
    exit;

} catch (Exception $e) {

    mysqli_rollback($conn);
    registrar_log('pedido_error', $e->getMessage(), 'ERROR');
    die("Error al procesar el pedido.");
}

// -------- FUNCIONES --------
function obtener_id_producto($conn, $codigo) {
    $stmt = mysqli_prepare($conn, "SELECT id FROM productos WHERE codigo = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, 's', $codigo);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $r = mysqli_fetch_assoc($res);
    return $r['id'] ?? 0;
}
?>
