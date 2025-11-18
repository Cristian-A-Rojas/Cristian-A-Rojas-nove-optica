<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/security.php';

/*
   🔐 CABECERAS DE SEGURIDAD (CSP, XFO, Referrer, etc.)
 */
header("Referrer-Policy: strict-origin");
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("Permissions-Policy: camera=(), microphone=(), geolocation=()");
header("Content-Security-Policy: 
    default-src 'self';
    img-src 'self' data:;
    style-src 'self' 'unsafe-inline';
    script-src 'self' 'unsafe-inline';
    font-src 'self' data:;
");
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>NOVE Óptica</title>

<link rel="icon" href="/nove_optica/uploads/favicon.ico" type="image/x-icon">
<link rel="stylesheet" href="/nove_optica/css/estilos.css">
<meta name="theme-color" content="#0d0d0d">
</head>

<body>

<header class="header-bar">
  <div class="header-wrap">

    <!-- Logo -->
    <a href="/nove_optica/index.php" class="header-logo">
      <?php
      $logo = __DIR__ . '/../uploads/logo.png';
      if (file_exists($logo)) {
          echo '<img src="/nove_optica/uploads/logo.png" alt="NOVE Óptica" class="logo-img">';
      } else {
          echo '<span class="logo-text">NOVE Óptica</span>';
      }
      ?>
    </a>

    <!-- Buscador -->
    <form class="search-box" action="/nove_optica/tienda.php" method="get" autocomplete="off">
      <?php csrf_input(); ?>
      <input type="text" name="q" class="search-input" placeholder="Buscar montura..." maxlength="60">
      <button type="submit" class="search-btn">🔍</button>
    </form>

  </div>
</header>

<?php include __DIR__ . '/navbar.php'; ?>

<main class="principal">
