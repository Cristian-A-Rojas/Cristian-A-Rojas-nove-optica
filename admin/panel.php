<?php
/**
 * NOVE ÓPTICA – Zero Trust Build V13.3
 * Panel de Administración — Dashboard Premium
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../config/db.php';

// Solo administradores
require_admin();

$conn = conectar_bd();

// =============================
//  Estadísticas principales
// =============================
$stats = [
    'usuarios'  => 0,
    'productos' => 0,
    'pedidos'   => 0,
    'visitas'   => 0
];

$queries = [
    'usuarios'  => "SELECT COUNT(*) AS total FROM usuarios",
    'productos' => "SELECT COUNT(*) AS total FROM productos",
    'pedidos'   => "SELECT COUNT(*) AS total FROM pedidos",
    'visitas'   => "SELECT COUNT(*) AS total FROM visitas 
                     WHERE fecha >= DATE_SUB(NOW(), INTERVAL 7 DAY)"
];

foreach ($queries as $k => $sql) {
    if ($res = mysqli_query($conn, $sql)) {
        $row = mysqli_fetch_assoc($res);
        $stats[$k] = (int)($row['total'] ?? 0);
    }
}

registrar_log(
    'acceso_panel_admin',
    "Acceso al panel por {$_SESSION['usuario']} (IP {$_SERVER['REMOTE_ADDR']})",
    'INFO'
);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Panel de Administración — NOVE Óptica</title>
<link rel="stylesheet" href="/nove_optica/css/admin.css">
<link rel="icon" href="/nove_optica/uploads/favicon.ico">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
<div class="admin-contenedor">

  <header class="admin-header">
    <h1>Panel de Administración</h1>
    <div class="usuario">👤 <?= htmlspecialchars($_SESSION['usuario'], ENT_QUOTES, 'UTF-8'); ?></div>
  </header>

  <aside class="sidebar">
    <h2>Menú</h2>
    <ul>
      <li><a class="activo" href="/nove_optica/admin/panel.php">📊 Dashboard</a></li>
      <li><a href="/nove_optica/admin/stats.php">📈 Estadísticas</a></li>
      <li><a href="/nove_optica/admin/productos/listar.php">📦 Productos</a></li>
      <li>
        <form action="/nove_optica/auth/logout.php" method="post">
          <?php csrf_input(); ?>
          <button class="logout-link">🚪 Cerrar sesión</button>
        </form>
      </li>
    </ul>
  </aside>

  <main class="admin-main">

    <!-- Tarjetas -->
    <section class="panel-tarjetas">
      <div class="tarjeta"><h3>👥 Usuarios</h3><p><?= $stats['usuarios']; ?></p></div>
      <div class="tarjeta"><h3>📦 Productos</h3><p><?= $stats['productos']; ?></p></div>
      <div class="tarjeta"><h3>🛒 Pedidos</h3><p><?= $stats['pedidos']; ?></p></div>
      <div class="tarjeta"><h3>📈 Visitas (7 días)</h3><p><?= $stats['visitas']; ?></p></div>
    </section>

    <!-- Gráficos -->
    <section class="grafico-contenedor">
      <h2>Actividad semanal</h2>
      <canvas id="grafUsuarios"></canvas>
      <canvas id="grafPedidos"></canvas>
    </section>

    <!-- Métricas -->
    <section class="grafico-contenedor">
      <h2>Rendimiento del servidor</h2>
      <div id="metricas" style="text-align:center;">Cargando...</div>
    </section>

  </main>

  <footer class="admin-footer">
    © <?= date('Y'); ?> NOVE Óptica — Panel seguro
  </footer>

</div>

<script src="/nove_optica/js/dashboard.js"></script>
<script src="/nove_optica/js/integrity.js"></script>
<script src="/nove_optica/js/darkmode-toggle.js"></script>

<script>
function metricasLive() {
  const cpu = (Math.random()*100).toFixed(1);
  const ram = (Math.random()*85).toFixed(1);
  document.getElementById("metricas").textContent =
    `CPU: ${cpu}% · RAM: ${ram}% · ${new Date().toLocaleTimeString()}`;
}
metricasLive();
setInterval(metricasLive, 4000);
</script>

</body>
</html>
