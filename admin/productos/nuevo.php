<?php
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/security.php';
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../config/db.php';

require_admin();

include __DIR__ . '/../../includes/header.php';
?>

<div class="contenedor">
  <h2>➕ Nuevo producto</h2>

  <form action="guardar.php" method="post" enctype="multipart/form-data" class="formulario">
    <?php csrf_input(); ?>

    <label>Nombre:</label>
    <input type="text" name="nombre" required maxlength="120">

    <label>Precio (€):</label>
    <input type="number" name="precio" step="0.01" min="0" required>

    <label>Descripción:</label>
    <textarea name="descripcion" rows="5" required></textarea>

    <label>Imagen:</label>
    <input type="file" name="imagen" accept="image/*" required>

    <button type="submit" class="boton-primario">Guardar producto</button>
  </form>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
