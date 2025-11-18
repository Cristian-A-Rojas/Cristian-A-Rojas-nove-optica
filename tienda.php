<?php
/**
 * NOVE ÓPTICA – Zero Trust Build V13.2
 * Catálogo general de productos (buscador + seguridad Zero-Trust)
 * Ultra-Stable AppServ Edition
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/config/db.php';

$conn = conectar_bd();

// =========================================================
// 🧠 Filtro de búsqueda (saneado)
// =========================================================
$busqueda = trim($_GET['q'] ?? '');
$productos = [];

if ($busqueda !== '') {
    $sql = "
        SELECT p.id, p.nombre, p.codigo, p.precio, p.descripcion,
               COALESCE(i.ruta,'uploads/sin_imagen.png') AS imagen
          FROM productos p
     LEFT JOIN imagenes i ON i.id_producto = p.id AND i.principal = 1
         WHERE p.activo = 1 AND p.nombre LIKE ?
      ORDER BY p.nombre ASC
    ";
    $stmt = mysqli_prepare($conn, $sql);
    $like = '%' . $busqueda . '%';
    mysqli_stmt_bind_param($stmt, 's', $like);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
} else {
    $sql = "
        SELECT p.id, p.nombre, p.codigo, p.precio, p.descripcion,
               COALESCE(i.ruta,'uploads/sin_imagen.png') AS imagen
          FROM productos p
     LEFT JOIN imagenes i ON i.id_producto = p.id AND i.principal = 1
         WHERE p.activo = 1
      ORDER BY p.id ASC
    ";
    $res = mysqli_query($conn, $sql);
}

if ($res && mysqli_num_rows($res) > 0) {
    while ($row = mysqli_fetch_assoc($res)) {
        $productos[] = $row;
    }
    mysqli_free_result($res);
}

registrar_log(
    'acceso_tienda',
    "Acceso a tienda por " . ($_SESSION['usuario'] ?? 'visitante') .
    " (IP " . ($_SERVER['REMOTE_ADDR'] ?? 'N/A') . ")", 
    'INFO'
);
?>
<?php include __DIR__ . '/includes/header.php'; ?>

<section class="tienda">
  <h2>Catálogo de monturas</h2>

  <form class="buscador" action="/tienda.php" method="get" style="text-align:center;margin-bottom:20px;">
    <?php csrf_input(); ?>
    <input type="text" name="q" placeholder="Buscar montura..." 
           value="<?php echo htmlspecialchars($busqueda, ENT_QUOTES, 'UTF-8'); ?>" maxlength="60">
    <button type="submit" class="boton-primario">Buscar</button>
  </form>

  <div class="rejilla rejilla-4">
    <?php if (!empty($productos)): ?>
      <?php foreach ($productos as $p): ?>
        <article class="tarjeta" id="producto-<?php echo (int)$p['id']; ?>">
          <img src="/<?php echo htmlspecialchars($p['imagen'], ENT_QUOTES, 'UTF-8'); ?>"
               alt="<?php echo htmlspecialchars($p['nombre'], ENT_QUOTES, 'UTF-8'); ?>">
          <h3><?php echo htmlspecialchars($p['nombre'], ENT_QUOTES, 'UTF-8'); ?></h3>
          <p><?php echo htmlspecialchars($p['descripcion'], ENT_QUOTES, 'UTF-8'); ?></p>
          <p class="precio">€ <?php echo number_format((float)$p['precio'], 2, ',', '.'); ?></p>
          <div class="acciones">
            <button class="boton-primario btn-add"
              data-codigo="<?php echo htmlspecialchars($p['codigo'], ENT_QUOTES, 'UTF-8'); ?>"
              data-nombre="<?php echo htmlspecialchars($p['nombre'], ENT_QUOTES, 'UTF-8'); ?>"
              data-precio="<?php echo number_format((float)$p['precio'], 2, '.', ''); ?>"
              data-imagen="/<?php echo htmlspecialchars($p['imagen'], ENT_QUOTES, 'UTF-8'); ?>">
              Añadir al carrito
            </button>
          </div>
        </article>
      <?php endforeach; ?>
    <?php else: ?>
      <p style="text-align:center;">No hay productos disponibles.</p>
    <?php endif; ?>
  </div>
</section>

<div id="modal-suscripcion" class="modal" style="display:none;">
  <div class="modal-contenido">
    <h3>¡Únete a NOVE Óptica!</h3>
    <p>Regístrate y obtén un <strong>10 % de descuento</strong> en tu primera compra.</p>
    <div class="botones-modal">
      <a href="/auth/login.php" class="boton-primario">Iniciar sesión</a>
      <a href="/auth/register.php" class="boton-secundario">Crear cuenta</a>
      <button class="cerrar-modal">Cerrar</button>
    </div>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
<script src="/nove_optica/js/carrito.js"></script>
</body>
</html>
