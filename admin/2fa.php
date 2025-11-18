<?php
/**
 * NOVE ÓPTICA – Zero Trust Build V13.3
 * Verificación en dos pasos (2FA por correo)
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/mail.php';

$conn = conectar_bd();
$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
$mensaje = "";

/* ---------------------------------------------------------
   🚧 EVITAR ACCESOS ILEGALES SIN SESIÓN DE LOGIN
--------------------------------------------------------- */
if (empty($_SESSION['usuario']) || empty($_SESSION['correo'])) {
    registrar_log('2FA_denegado', "Acceso sin sesión (IP $ip)", 'WARN');
    header("Location: /nove_optica/auth/login.php?error=no_session");
    exit;
}

/* ---------------------------------------------------------
   🚧 EVITAR VOLVER A 2FA SI YA ESTÁ VALIDADO
--------------------------------------------------------- */
if (!empty($_SESSION['2fa_validado'])) {
    $rol = $_SESSION['rol'] ?? 'usuario';
    header("Location: " . ($rol === 'admin'
        ? "/nove_optica/admin/panel.php"
        : "/nove_optica/productos/listar.php"));
    exit;
}

/* ---------------------------------------------------------
   ⏱ CONTROL DE REENVÍO (ANTI-SPAM)
--------------------------------------------------------- */
if (isset($_SESSION['ultimo_envio']) && time() - $_SESSION['ultimo_envio'] < 30) {
    $mensaje = "Debes esperar unos segundos antes de pedir un nuevo código.";
} else {
    $_SESSION['ultimo_envio'] = time();
}

/* ---------------------------------------------------------
   🔐 GENERAR O REENVIAR CÓDIGO
--------------------------------------------------------- */
if (!isset($_SESSION['2fa_hash']) || isset($_POST['reenviar'])) {

  $codigo = random_int(100000, 999999);

  $_SESSION['2fa_hash']   = password_hash((string)$codigo, PASSWORD_DEFAULT);
  $_SESSION['2fa_expira'] = time() + 600; // 10 minutos

  registrar_log('2FA_envio', "Código enviado a {$_SESSION['correo']} (IP $ip)", 'INFO');

  // ✔ CORRECCIÓN AQUÍ
  correo_codigo_2FA($_SESSION['correo'], $_SESSION['usuario']);

  $mensaje = "Código de verificación enviado.";
}

/* ---------------------------------------------------------
   🔍 VALIDAR CÓDIGO ENVIADO
--------------------------------------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST['reenviar'])) {

    $csrf = $_POST['csrf_token'] ?? '';
    $codigo_ingresado = trim($_POST['codigo'] ?? '');

    if (!csrf_verificar_token($csrf)) {
        registrar_log('2FA_csrf', "CSRF detectado (IP $ip)", 'WARN');
        $mensaje = "Petición no autorizada.";
    }
    elseif (empty($codigo_ingresado)) {
        $mensaje = "Introduce el código enviado.";
    }
    elseif (time() > ($_SESSION['2fa_expira'] ?? 0)) {
        unset($_SESSION['2fa_hash'], $_SESSION['2fa_expira']);
        registrar_log('2FA_expirado', "Código expirado (IP $ip)", 'INFO');
        $mensaje = "El código ha expirado. Inicia sesión de nuevo.";
    }
    elseif (password_verify($codigo_ingresado, $_SESSION['2fa_hash'])) {

        $_SESSION['2fa_validado'] = true;
        unset($_SESSION['2fa_hash'], $_SESSION['2fa_expira']);

        registrar_log('2FA_ok', "Verificación correcta (IP $ip)", 'INFO');

        $rol = $_SESSION['rol'] ?? 'usuario';

        header("Location: " . ($rol === 'admin'
            ? "/nove_optica/admin/panel.php"
            : "/nove_optica/productos/listar.php"));
        exit;
    }
    else {
        registrar_log('2FA_fail', "Código incorrecto (IP $ip)", 'WARN');
        $mensaje = "Código incorrecto.";
    }

    // anti timing attack
    usleep(random_int(150000, 350000));
}

?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<section class="formulario">
  <h2>Verificación en dos pasos</h2>
  <p>Introduce el código de 6 dígitos enviado a tu correo.</p>

  <?php if ($mensaje): ?>
      <p class="mensaje error"><?= htmlspecialchars($mensaje) ?></p>
  <?php endif; ?>

  <form method="post">
    <?php csrf_input(); ?>
    <label for="codigo">Código</label>
    <input type="text" id="codigo" name="codigo" maxlength="6"
           inputmode="numeric" pattern="\d{6}" required>
    <button class="boton-primario">Verificar</button>
  </form>

  <form method="post" style="margin-top:14px;">
    <?php csrf_input(); ?>
    <button name="reenviar" value="1" class="boton-secundario">Reenviar código</button>
  </form>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
