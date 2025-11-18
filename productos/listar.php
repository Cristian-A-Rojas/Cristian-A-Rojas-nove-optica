<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../config/db.php';

require_login();

$conn = conectar_bd();
$sql = "SELECT id, nombre, precio, imagen FROM productos ORDER BY id DESC";
$res = mysqli_query($conn, $sql);

include __DIR__ . '/../includes/header.php';
?>

<div class="contenedor">
  <h2>Catálogo de productos</h2>

  <div class="rejilla rejilla-4">
    <?php while ($p = mysqli_fetch_assoc($res)): ?>
      <div class="tarjeta">
        <img src="/nove_optica/uploads/<?= htmlspecialchars($p['imagen']) ?>" alt="">
        <h3><?= htmlspecialchars($p['nombre']) ?></h3>
        <p class="precio"><?= number_format($p['precio'], 2) ?> €</p>

        <a class="boton-primario" href="/nove_optica/productos/ver.php?id=<?= $p['id'] ?>">
          Ver detalles
        </a>
      </div>
    <?php endwhile; ?>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
