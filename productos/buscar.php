<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../config/db.php';

require_login();

$q = trim($_GET['q'] ?? '');
$conn = conectar_bd();

// // buscar productos
$sql = "SELECT id, nombre, precio, imagen 
        FROM productos 
        WHERE nombre LIKE ? 
        ORDER BY nombre ASC";
$stmt = mysqli_prepare($conn, $sql);
$param = "%" . $q . "%";
mysqli_stmt_bind_param($stmt, "s", $param);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);

include __DIR__ . '/../includes/header.php';
?>

<div class="contenedor">
  <h2>Resultados de búsqueda: “<?= htmlspecialchars($q, ENT_QUOTES, 'UTF-8'); ?>”</h2>

  <?php if (mysqli_num_rows($res) === 0): ?>
      <p class="mensaje alerta">No se encontraron productos que coincidan con tu búsqueda.</p>
  <?php endif; ?>

  <div class="rejilla rejilla-4">
    <?php while ($p = mysqli_fetch_assoc($res)): ?>
      <div class="tarjeta">
        <img src="/nove_optica/uploads/<?= htmlspecialchars($p['imagen'], ENT_QUOTES, 'UTF-8'); ?>" alt="Imagen del producto">

        <h3><?= htmlspecialchars($p['nombre'], ENT_QUOTES, 'UTF-8'); ?></h3>

        <p class="precio">
          € <?= number_format($p['precio'], 2, ',', '.'); ?>
        </p>

        <a class="boton-primario" 
           href="/nove_optica/productos/ver.php?id=<?= $p['id']; ?>">
           Ver detalles
        </a>
      </div>
    <?php endwhile; ?>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
