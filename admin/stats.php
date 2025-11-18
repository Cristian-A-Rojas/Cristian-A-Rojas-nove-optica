<?php
/**
 * NOVE ÓPTICA – Zero Trust Build V13.2
 * Módulo de estadísticas dinámicas y gráficas seguras
 * Ultra-Stable AppServ Edition
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../config/db.php';

$conn = conectar_bd();
enforce_admin(); // exige sesión activa + rol admin + 2FA

registrar_log(
    'acceso_stats',
    "Acceso al módulo de estadísticas por {$_SESSION['usuario']} (IP {$_SERVER['REMOTE_ADDR']})",
    'INFO'
);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>📈 Estadísticas — NOVE Óptica</title>
<link rel="stylesheet" href="/nove_optica/css/admin.css">
<link rel="icon" href="/nove_optica/uploads/favicon.ico" type="image/x-icon">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
<div class="admin-contenedor">

  <header class="admin-header">
    <h1>📊 Estadísticas Generales</h1>
    <div class="usuario">👤 <?= htmlspecialchars($_SESSION['usuario'], ENT_QUOTES, 'UTF-8'); ?></div>
  </header>

  <aside class="sidebar">
    <h2>Menú</h2>
    <ul>
      <li><a href="/nove_optica/admin/panel.php">🏠 Dashboard</a></li>
      <li><a href="/nove_optica/admin/stats.php" class="activo">📈 Estadísticas</a></li>
      <li><a href="/nove_optica/productos/listar.php">🛍️ Productos</a></li>
      <li><a href="/nove_optica/usuarios/listar.php">👥 Usuarios</a></li>
      <li>
        <form action="/nove_optica/auth/logout.php" method="post" id="logoutForm" style="display:inline;">
          <?php csrf_input(); ?>
          <button type="submit" class="logout-link">🚪 Cerrar sesión</button>
        </form>
      </li>
    </ul>
  </aside>

  <main class="admin-main">

    <section class="grafico-contenedor">
      <h2>Filtrar por rango de fechas</h2>
      <form id="filtro-fechas" class="filtros" onsubmit="return false;">
        <?php csrf_input(); ?>
        <label for="fecha_inicio">Desde:</label>
        <input type="date" id="fecha_inicio" required>
        <label for="fecha_fin">Hasta:</label>
        <input type="date" id="fecha_fin" required>
        <button type="button" class="boton-admin">Actualizar</button>
      </form>
    </section>

    <section class="grafico-contenedor">
      <h2>Usuarios nuevos</h2>
      <canvas id="grafUsuarios"></canvas>
    </section>

    <section class="grafico-contenedor">
      <h2>Ventas diarias (€)</h2>
      <canvas id="grafVentas"></canvas>
    </section>

    <section class="grafico-contenedor">
      <h2>Top productos vendidos</h2>
      <canvas id="grafTop"></canvas>
    </section>

  </main>

  <footer class="admin-footer">
    © <?= date('Y'); ?> NOVE Óptica — Estadísticas seguras · Sesión cifrada
  </footer>
</div>

<!-- ==========================
     📊 Scripts modulares V13.2
========================== -->
<script src="/nove_optica/js/global.js"></script>
<script src="/nove_optica/js/dashboard.js"></script>
<script src="/nove_optica/js/integrity.js"></script>
<script src="/nove_optica/js/darkmode-toggle.js"></script>

<style>
.filtros {
  margin: 1.5em auto;
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  justify-content: center;
  align-items: center;
}
canvas {
  background: var(--color-card);
  border-radius: var(--radio);
  padding: 1em;
  box-shadow: var(--sombra);
}
</style>

<script>
// 🔒 Logout seguro
document.getElementById("logoutForm")?.addEventListener("submit", e=>{
  if(!confirm("¿Deseas cerrar sesión de forma segura?")) e.preventDefault();
});
</script>

</body>
</html>
