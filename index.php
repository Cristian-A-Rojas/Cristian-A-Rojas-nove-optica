<?php


declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/log.php';
require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/config/db.php';

$conexion = conectar_bd();

// =============================
// 🔒 Seguridad y registro
// =============================
registrar_log('acceso_index', 'Acceso a la página principal', 'INFO');

// =============================
// 🛍️ Productos destacados
// =============================
$sql = "
    SELECT p.id, p.nombre, p.codigo, p.precio,
           COALESCE(i.ruta, 'uploads/sin_imagen.png') AS imagen
      FROM productos p
 LEFT JOIN imagenes i ON i.id_producto = p.id AND i.principal = 1
     WHERE p.destacado = 1 AND p.activo = 1
  ORDER BY p.id DESC
     LIMIT 6
";

$destacados = [];
if ($resultado = mysqli_query($conexion, $sql)) {
    while ($fila = mysqli_fetch_assoc($resultado)) {
        $destacados[] = $fila;
    }
    mysqli_free_result($resultado);
}
?>
<?php include __DIR__ . '/includes/header.php'; ?>

<section class="hero">
  <div class="hero-contenido">
    <h1>Visión clara, estilo único</h1>
    <p>Monturas diseñadas especialmente para personas con daltonismo.</p>
    <a href="/tienda.php" class="boton-primario">Ver Tienda</a>
  </div>
</section>

<section class="destacados">
  <h2>Modelos destacados</h2>
  <div class="rejilla rejilla-3">
    <?php if (!empty($destacados)): ?>
      <?php foreach ($destacados as $p): ?>
        <article class="tarjeta" id="producto-<?php echo (int)$p['id']; ?>">
          <a href="/tienda.php#producto-<?php echo (int)$p['id']; ?>">
            <img src="<?php echo htmlspecialchars($p['imagen'], ENT_QUOTES, 'UTF-8'); ?>"
                 alt="<?php echo htmlspecialchars($p['nombre'], ENT_QUOTES, 'UTF-8'); ?>">
            <h3><?php echo htmlspecialchars($p['nombre'], ENT_QUOTES, 'UTF-8'); ?></h3>
            <p class="precio">€ <?php echo number_format((float)$p['precio'], 2, ',', '.'); ?></p>
          </a>
        </article>
      <?php endforeach; ?>
    <?php else: ?>
      <p style="text-align:center;">No hay productos destacados disponibles.</p>
    <?php endif; ?>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
<script src="/nove_optica/js/carrito.js"></script>
</body>
</html>

