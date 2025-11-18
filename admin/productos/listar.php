<?php
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/security.php';
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../config/db.php';

require_admin();

$conn = conectar_bd();
$res = mysqli_query($conn, "SELECT id, nombre, precio, imagen FROM productos ORDER BY id DESC");

include __DIR__ . '/../../includes/header.php';
?>

<div class="contenedor">
  <h2>📦 Gestión de productos</h2>

  <a href="nuevo.php" class="boton-primario" style="margin-bottom:15px; display:inline-block;">
    ➕ Añadir producto
  </a>

  <table class="tabla" style="width:100%; margin-top:20px;">
    <thead>
      <tr>
        <th>ID</th>
        <th>Imagen</th>
        <th>Nombre</th>
        <th>Precio</th>
        <th>Acciones</th>
      </tr>
    </thead>

    <tbody>
      <?php while ($p = mysqli_fetch_assoc($res)): ?>
      <tr>
        <td><?= $p['id'] ?></td>
        <td>
          <img src="/nove_optica/uploads/<?= htmlspecialchars($p['imagen']) ?>"
               style="height:50px; border-radius:6px;">
        </td>
        <td><?= htmlspecialchars($p['nombre']) ?></td>
        <td><?= number_format($p['precio'],2) ?> €</td>

        <td>
          <a class="boton-secundario" 
             href="editar.php?id=<?= $p['id'] ?>">Editar</a>

          <form action="eliminar.php" method="post" style="display:inline;">
            <?php csrf_input(); ?>
            <input type="hidden" name="id" value="<?= $p['id'] ?>">
            <button class="boton-secundario"
                    onclick="return confirm('¿Eliminar producto?')">Eliminar</button>
          </form>
        </td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
