<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../config/db.php';

require_login();

$id = intval($_GET['id'] ?? 0);
$conn = conectar_bd();

// // obtener producto
$sql = "SELECT id, nombre, descripcion, precio, imagen, codigo 
        FROM productos 
        WHERE id = ? 
        LIMIT 1";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);

if (!$res || mysqli_num_rows($res) === 0) {
    die("Producto no encontrado.");
}

$p = mysqli_fetch_assoc($res);

include __DIR__ . '/../includes/header.php';
?>

<div class="contenedor">
  <h2><?= htmlspecialchars($p['nombre'], ENT_QUOTES, 'UTF-8'); ?></h2>

  <div class="tarjeta">
    <img src="/nove_optica/uploads/<?= htmlspecialchars($p['imagen'], ENT_QUOTES, 'UTF-8'); ?>">

    <h3><?= htmlspecialchars($p['nombre'], ENT_QUOTES, 'UTF-8'); ?></h3>

    <p><?= nl2br(htmlspecialchars($p['descripcion'], ENT_QUOTES, 'UTF-8')); ?></p>

    <p class="precio">
      € <?= number_format($p['precio'], 2, ',', '.'); ?>
    </p>

    <!-- // botón AJAX para agregar al carrito -->
    <button class="boton-primario btn-add"
        data-codigo="<?= htmlspecialchars($p['codigo'], ENT_QUOTES, 'UTF-8'); ?>"
        data-nombre="<?= htmlspecialchars($p['nombre'], ENT_QUOTES, 'UTF-8'); ?>"
        data-precio="<?= number_format($p['precio'], 2, '.', ''); ?>"
        data-imagen="/nove_optica/uploads/<?= htmlspecialchars($p['imagen'], ENT_QUOTES, 'UTF-8'); ?>">
        Añadir al carrito 🛒
    </button>
  </div>
</div>

<script src="/nove_optica/js/carrito.js"></script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
