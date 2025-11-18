require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/mail.php';

$conn = conectar_bd();
$mensaje = "";
$mensaje_tipo = "error";
$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

/* 
   ⏱ RATE LIMIT LOCAL (ANTI-SPAM)
 */
if (isset($_SESSION['ultimo_registro']) && time() - $_SESSION['ultimo_registro'] < 10) {
    $mensaje = "Por favor, espera unos segundos antes de volver a intentarlo.";
} else {
    $_SESSION['ultimo_registro'] = time();
}

/* 
   📝 PROCESAR REGISTRO
 */
if ($_SERVER["REQUEST_METHOD"] === "POST" && empty($mensaje)) {

    $nombre   = limpiar($_POST["nombre"] ?? '');
    $correo   = limpiar($_POST["correo"] ?? '');
    $clave    = $_POST["clave"] ?? '';
    $telefono = limpiar($_POST["telefono"] ?? '');
    $token    = $_POST["csrf_token"] ?? '';

    //  CSRF --
    if (!csrf_verificar_token($token)) {
        $mensaje = "Petición no autorizada (CSRF detectado).";
    }
    //  Validación --
    elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $mensaje = "Correo electrónico no válido.";
    }
    elseif ($telefono !== '' && !preg_match('/^[0-9+\s-]*$/', $telefono)) {
        $mensaje = "Número de teléfono no válido.";
    }
    elseif (strlen($clave) < 8) {
        $mensaje = "La contraseña debe tener al menos 8 caracteres.";
    }
    elseif (strlen($nombre) < 2) {
        $mensaje = "El nombre es demasiado corto.";
    }
    else {
        /* ---------✔ Comprobar si ya existe el correo
                $sqlC = "SELECT id FROM usuarios WHERE correo=? LIMIT 1";
        $stmtC = mysqli_prepare($conn, $sqlC);
        mysqli_stmt_bind_param($stmtC, "s", $correo);
        mysqli_stmt_execute($stmtC);
        $resC = mysqli_stmt_get_result($stmtC);

        if ($resC && mysqli_num_rows($resC) === 0) {

            $hash = password_hash($clave, PASSWORD_DEFAULT);

            $sqlI = "INSERT INTO usuarios (nombre, correo, clave, telefono, verificado, tipo)
                     VALUES (?, ?, ?, ?, 0, 'cliente')";
            $stmtI = mysqli_prepare($conn, $sqlI);
            mysqli_stmt_bind_param($stmtI, "ssss", $nombre, $correo, $hash, $telefono);
            $ok = mysqli_stmt_execute($stmtI);

            if ($ok) {

                /* ---------
                   ✔ Crear token de verificación por correo
                --------- */
                $tokenV = bin2hex(random_bytes(32));
                $exp = date("Y-m-d H:i:s", strtotime("+15 minutes"));

                $sqlV = "INSERT INTO verificaciones (correo, token, expiracion) VALUES (?, ?, ?)";
                $stmtV = mysqli_prepare($conn, $sqlV);
                mysqli_stmt_bind_param($stmtV, "sss", $correo, $tokenV, $exp);
                mysqli_stmt_execute($stmtV);

                /* ---------
                   ✔ Enviar correo de verificación
                --------- */
                correo_verificacion($correo, $nombre, $tokenV);

                registrar_log('registro_ok', "Nuevo usuario $correo desde IP $ip", 'INFO');

                $mensaje = "Registro completado. Revisa tu correo para confirmar tu cuenta.";
                $mensaje_tipo = "exito";
                session_regenerate_id(true);
            }
            else {
                registrar_log('registro_error', "Error SQL al registrar $correo ($ip)", 'ERROR');
                $mensaje = "Error al registrar el usuario.";
            }
        }
        else {
            registrar_log('registro_fallido', "Correo duplicado: $correo ($ip)", 'WARN');
            $mensaje = "El correo ya está registrado.";
        }
    }

    // Retardo anti-timing
    usleep(random_int(200000, 600000));
}
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<section class="formulario" aria-live="polite">
  <h2>Crear cuenta</h2>

  <?php if ($mensaje): ?>
  <p class="mensaje <?= $mensaje_tipo === 'exito' ? 'exito' : 'error'; ?>">
    <?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8'); ?>
  </p>
  <?php endif; ?>

  <form method="post" autocomplete="off">
    <?php csrf_input(); ?>

    <label for="nombre">Nombre completo</label>
    <input type="text" id="nombre" name="nombre" required minlength="2" maxlength="77">

    <label for="correo">Correo electrónico</label>
    <input type="email" id="correo" name="correo" required maxlength="77">

    <label for="telefono">Teléfono</label>
    <input type="tel" id="telefono" name="telefono" maxlength="33" pattern="^[0-9+\s-]*$">

    <label for="clave">Contraseña</label>
    <input type="password" id="clave" name="clave" required minlength="8" maxlength="255">

    <button class="boton-primario">Registrarme</button>
    <p>¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a></p>
  </form>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
