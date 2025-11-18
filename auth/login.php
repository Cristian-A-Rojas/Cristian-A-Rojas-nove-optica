<?php
/**
 * NOVE ÓPTICA – Zero Trust Build V13.3
 * Login seguro + 2FA por código de 6 dígitos
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/mail.php';

$conn = conectar_bd();
$mensaje = "";
$mensaje_tipo = "error";
$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';


// 🔐 RATE LIMITING

if (is_locked()) {
    $mensaje = "Demasiados intentos. Espera unos minutos.";
} 
else if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $correo = limpiar($_POST["correo"] ?? '');
    $clave  = $_POST["clave"] ?? '';
    $token  = $_POST["csrf_token"] ?? '';

    
    // 🔐 CSRF PROTECCIÓN
    
    if (!csrf_verificar_token($token)) {
        registrar_log("csrf_login_fail", "Intento CSRF desde $ip", "WARN");
        $mensaje = "Petición no autorizada.";
    } 
    else {
        
        // 🔍 Buscar usuario
        
        $sql = "SELECT id, nombre, correo, clave, tipo FROM usuarios WHERE correo=? LIMIT 1";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $correo);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);

        if ($res && mysqli_num_rows($res) === 1) {
            $u = mysqli_fetch_assoc($res);

            if (password_verify($clave, $u['clave'])) {

                
                // ✅ LOGIN CORRECTO (FALTA 2FA)
                
                reset_fails();
                session_regenerate_id(true);

                $_SESSION['usuario']    = $u['nombre'];
                $_SESSION['usuario_id'] = $u['id'];
                $_SESSION['correo']     = $u['correo'];
                $_SESSION['rol']        = $u['tipo'];
                csrf_rotar_token();

                registrar_log("login_ok", "Acceso correcto {$u['correo']} (IP $ip)", "INFO");

                
                // 🔐 GENERAR CÓDIGO DE 6 DÍGITOS
                
                $codigo = random_int(100000, 999999);
                $_SESSION['2fa_hash']   = password_hash((string)$codigo, PASSWORD_DEFAULT);
                $_SESSION['2fa_expira'] = time() + 600; // 10 min

                // Enviar por correo con plantilla
                correo_codigo_2FA($u['correo'], $u['nombre']);

                registrar_log("2FA_envio", "Código enviado a {$u['correo']} (IP $ip)", "INFO");

                
                // ➡️ Redirigir a página de verificación
                
                header("Location: /nove_optica/auth/2fa.php");
                exit;
            }
            else {
                // contraseña incorrecta
                register_fail();
                registrar_log("login_fail", "Clave incorrecta ($correo, IP $ip)", "WARN");
                $mensaje = "Contraseña incorrecta.";
            }
        }
        else {
            // usuario no encontrado
            register_fail();
            registrar_log("login_fail", "Correo no registrado ($correo, IP $ip)", "WARN");
            $mensaje = "Correo no registrado.";
        }
    }

    // retardo anti-timing
    usleep(random_int(150000, 350000));
}
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<section class="formulario">
  <h2>Iniciar sesión</h2>

  <?php if ($mensaje): ?>
    <p class="mensaje <?= $mensaje_tipo === 'exito' ? 'exito' : 'error'; ?>">
      <?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8'); ?>
    </p>
  <?php endif; ?>

  <form action="" method="post" autocomplete="off">
    <?php csrf_input(); ?>
    
    <label for="correo">Correo electrónico</label>
    <input type="email" id="correo" name="correo" maxlength="100" required>

    <label for="clave">Contraseña</label>
    <input type="password" id="clave" name="clave" minlength="6" maxlength="64" required>

    <button type="submit" class="boton-primario">Acceder</button>

    <p>¿No tienes cuenta? <a href="register.php">Regístrate</a></p>
  </form>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
