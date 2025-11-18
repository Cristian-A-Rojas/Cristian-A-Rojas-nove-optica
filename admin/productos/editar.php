<?php
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/security.php';
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../config/db.php';

require_admin();

$id = intval($_GET['id'] ?? 0);
if ($id < 1) die("ID inválido");

$conn = conectar_bd();
$stmt = mysqli_prepare($conn, "SELECT * FROM productos WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$p = mysqli_fetch_assoc($res);

if (!$p) die("Producto no encontrado");

include __DIR__ . '/../../includes/header.php';
?>

<div class="contenedor">
  <h2>✏ Editar producto</h2>

  <form action="actualizar.php" method="post" enctype="multipart/form-data" class="formulario">
    <?php csrf_input(); ?>

    <input type="hidden" name="id" value="<?= $p['id'] ?>">

    <label>Nombre:</label>
    <input type="text" name="nombre" value="<?= htmlspecialchars($p['nombre']) ?>" required>

    <label>Precio (€):</label>
    <input type="number" step="0.01" min="0" name="precio" 
           value="<?= number_format($p['precio'], 2, '.', '') ?>" required>

    <label>Descripción:</label>
    <textarea name="descripcion" rows="5"><?= htmlspecialchars($p['descripcion']) ?></textarea>

    <p>Imagen actual:</p>
    <img src="/nove_optica/uploads/<?= htmlspecialchars($p['imagen']) ?>" width="150">

    <label>Nueva imagen (opcional):</label>
    <input type="file" name="imagen" accept="image/*">

    <button type="submit" class="boton-primario">Actualizar</button>
  </form>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
