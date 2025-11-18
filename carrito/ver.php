<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../config/db.php';

require_login();

// carrito en sesión
$carrito = $_SESSION['carrito'] ?? [];
$conn = conectar_bd();

// obtener datos reales desde BD
$items = [];
$total = 0;

if (!empty($carrito)) {
    $ids = array_keys($carrito);
    $in  = implode(',', array_fill(0, count($ids), '?'));

    $sql = "SELECT id, nombre, precio, imagen FROM productos WHERE id IN ($in)";
    $stmt = mysqli_prepare($conn, $sql);

    $types = str_repeat('i', count($ids));
    mysqli_stmt_bind_param($stmt, $types, ...$ids);
    mysqli_stmt_execute($stmt);

    $res = mysqli_stmt_get_result($stmt);

    while ($p = mysqli_fetch_assoc($res)) {
        $id = $p['id'];
        $cant = $carrito[$id]['cantidad'];

        $p['cantidad'] = $cant;
        $p['subtotal'] = $cant * $p['precio'];

        $items[] = $p;
        $total += $p['subtotal'];
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="contenedor">
  <h2>🛒 Tu carrito</h2>

  <?php if (empty($items)): ?>
    <p>Tu carrito está vacío.</p>

  <?php else: ?>
    <table class="tabla">
      <thead>
        <tr>
          <th>Producto</th>
          <th>Cant.</th>
          <th>Precio</th>
          <th>Subtotal</th>
          <th></th>
        </tr>
      </thead>

      <tbody>
        <?php foreach ($items as $p): ?>
          <tr>
            <td>
              <img src="/nove_optica/uploads/<?= htmlspecialchars($p['imagen']) ?>" 
                   alt="" style="height:40px;border-radius:6px;margin-right:8px;">
              <?= htmlspecialchars($p['nombre']) ?>
            </td>

            <td><?= (int)$p['cantidad'] ?></td>

            <td><?= number_format($p['precio'], 2, ',', '.') ?> €</td>

            <td><?= number_format($p['subtotal'], 2, ',', '.') ?> €</td>

            <td>
              <form action="/nove_optica/carrito/eliminar.php" method="post">
                <?php csrf_input(); ?>
                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                <button type="submit" class="boton-secundario">Eliminar</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <h3>Total: <?= number_format($total, 2, ',', '.') ?> €</h3>

    <form action="/nove_optica/carrito/vaciar.php" method="post" style="margin-top:15px;">
      <?php csrf_input(); ?>
      <button type="submit" class="boton-secundario">Vaciar carrito</button>
    </form>

    <form action="/nove_optica/carrito/checkout.php" method="post" style="margin-top:10px;">
      <?php csrf_input(); ?>
      <button type="submit" class="boton-primario">Finalizar compra</button>
    </form>

  <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
