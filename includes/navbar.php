<?php
// Navbar dinámico según sesión y rol
$usuario_logueado = !empty($_SESSION['usuario']);
$es_admin         = ($usuario_logueado && ($_SESSION['rol'] ?? '') === 'admin');
?>

<nav class="navbar">
  <ul>

    <!-- Inicio -->
    <li><a href="/nove_optica/index.php">Inicio</a></li>

    <!-- Tienda -->
    <li><a href="/nove_optica/productos/listar.php">Tienda</a></li>

    <!-- Carrito -->
    <li><a href="/nove_optica/carrito/ver.php">Carrito</a></li>

    <?php if ($usuario_logueado): ?>
      
      <!-- Si es admin -->
      <?php if ($es_admin): ?>
        <li><a href="/nove_optica/admin/panel.php">Admin</a></li>
      <?php endif; ?>

      <!-- Logout seguro -->
      <li>
        <form action="/nove_optica/auth/logout.php" method="post" class="logout-form">
          <?php csrf_input(); ?>
          <button type="submit" class="logout-btn">Cerrar sesión</button>
        </form>
      </li>

    <?php else: ?>

      <!-- Invitado -->
      <li><a href="/nove_optica/auth/login.php">Iniciar sesión</a></li>
      <li><a href="/nove_optica/auth/register.php">Registrarse</a></li>

    <?php endif; ?>

  </ul>
</nav>

<style>
/* Ajuste rápido si no está en estilos.css */
.logout-form { display:inline; }
.logout-btn {
  background:none;
  border:none;
  color:#d9534f;
  cursor:pointer;
  font-weight:bold;
}
.logout-btn:hover { text-decoration:underline; }
</style>
