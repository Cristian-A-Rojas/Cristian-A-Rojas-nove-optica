<?php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/config/db.php';

require_login(); // sesión obligatoria

$conn = conectar_bd();

// id pedido
$id = intval($_GET['pedido'] ?? 0);
if ($id <= 0) {
    registrar_log('pedido_invalido', "ID invalido en confirmacion", 'WARN');
    die("Pedido no válido.");
}

// verificar que pertenece al usuario logueado
$sql = "SELECT id, total, estado, fecha
        FROM pedidos
        WHERE id = ? AND id_usuario = ?
        LIMIT 1";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'ii', $id, $_SESSION['usuario_id']);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);

if (!$res || mysqli_num_rows($res) === 0) {
    registrar_log('pedido_no_autorizado', "Intento ver pedido ajeno (#$id)", 'ALERT');
    die("No tienes permiso para ver este pedido.");
}

$pedido = mysqli_fetch_assoc($res);

// obtener items
$sql2 = "SELECT pi.cantidad, pi.precio_unitario, p.nombre, p.codigo
         FROM pedido_items pi
         JOIN productos p ON p.id = pi.id_producto
         WHERE pi.id_pedido = ?";
$stmt2 = mysqli_prepare($conn, $sql2);
mysqli_stmt_bind_param($stmt2, 'i', $id);
mysqli_stmt_execute($stmt2);
$items = mysqli_stmt_get_result($stmt2);

registrar_log('pedido_confirmacion', "Vista confirmación pedido #$id", 'INFO');

include __DIR__ . '/includes/header.php';
?>

<section class="contenedor" style="margin-top:40px;">
  <h2>🎉 ¡Pedido completado con éxito!</h2>
  <p>Gracias por confiar en <strong>NOVE Óptica</strong>.</p>

  <div class="tarjeta" style="max-width:600px;margin:auto;">
    <h3>Detalles del pedido</h3>

    <p><strong>Nº Pedido:</strong> <?= htmlspecialchars($pedido['id']); ?></p>
    <p><strong>Estado:</strong> <?= htmlspecialchars($pedido['estado']); ?></p>
    <p><strong>Fecha:</strong> <?= htmlspecialchars($pedido['fecha']); ?></p>

    <h4>Artículos</h4>
    <ul style="list-style:none;padding-left:0;">
      <?php while ($i = mysqli_fetch_assoc($items)): ?>
        <li style="margin-bottom:8px;">
          <?= htmlspecialchars($i['nombre']); ?>
          (x<?= (int)$i['cantidad']; ?>)
          — €
          <?= number_format($i['precio_unitario'], 2, ',', '.'); ?>
        </li>
      <?php endwhile; ?>
    </ul>

    <h3>Total pagado: € <?= number_format($pedido['total'], 2, ',', '.'); ?></h3>

    <div style="margin-top:20px;text-align:center;">
      <a href="/nove_optica/index.php" class="boton-primario">Volver al inicio</a>
      <a href="/nove_optica/tienda.php" class="boton-secundario">Seguir comprando</a>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
