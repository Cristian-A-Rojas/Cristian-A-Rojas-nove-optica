<?php
/**
 * NOVE ÓPTICA – Zero Trust Build V13.3
 * Verificación de cuenta (por enlace enviado al registrar)
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/security.php'; // para registrar_log

$conn = conectar_bd();

$mensaje = "";
$mensaje_tipo = "error";
$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

/* ---------------------------------------------------------
   🔍 VALIDACIÓN DEL TOKEN
--------------------------------------------------------- */
if (!empty($_GET['token'])) {

    $token = limpiar($_GET['token']);

    $sql = "SELECT correo, expiracion FROM verificaciones WHERE token=? LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $token);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);

    if ($res && mysqli_num_rows($res) === 1) {

        $v = mysqli_fetch_assoc($res);
        $correo = $v['correo'];
        $expira = strtotime($v['expiracion']);

        if (time() <= $expira) {

            /* -----------------------------------------
               ✔ Obtener datos del usuario
            ----------------------------------------- */
            $sqlU = "SELECT id, nombre, tipo FROM usuarios WHERE correo=? LIMIT 1";
            $stmtU = mysqli_prepare($conn, $sqlU);
            mysqli_stmt_bind_param($stmtU, "s", $correo);
            mysqli_stmt_execute($stmtU);
            $resU = mysqli_stmt_get_result($stmtU);

            if ($resU && mysqli_num_rows($resU) === 1) {

                $u = mysqli_fetch_assoc($resU);

                /* -----------------------------------------
                   ✔ Activar cuenta
                ----------------------------------------- */
                $sqlUp = "UPDATE usuarios SET verificado=1 WHERE correo=?";
                $stmtUp = mysqli_prepare($conn, $sqlUp);
                mysqli_stmt_bind_param($stmtUp, "s", $correo);
                mysqli_stmt_execute($stmtUp);

                /* -----------------------------------------
                   ✔ Borrar el token de verificación
                ----------------------------------------- */
                $sqlDel = "DELETE FROM verificaciones WHERE token=?";
                $stmtDel = mysqli_prepare($conn, $sqlDel);
                mysqli_stmt_bind_param($stmtDel, "s", $token);
                mysqli_stmt_execute($stmtDel);

                /* -----------------------------------------
                   ✔ Iniciar sesión automática
                ----------------------------------------- */
                session_regenerate_id(true);
                $_SESSION['usuario']    = $u['nombre'];
                $_SESSION['usuario_id'] = $u['id'];
                $_SESSION['rol']        = $u['tipo'];
                $_SESSION['2fa_validado'] = true; // coherencia global
                $_SESSION['correo']     = $correo;

                registrar_log(
                    'verificacion_exitosa',
                    "Cuenta verificada para $correo (IP $ip)",
                    'INFO'
                );

                $mensaje = "¡Cuenta verificada con éxito, {$u['nombre']}!";
                $mensaje_tipo = "exito";

                // Redirección correcta hacia la tienda de tu proyecto actual
                header("Refresh: 2; url=/nove_optica/productos/listar.php");
            }
            else {
                registrar_log('verificacion_fallida', "Usuario no encontrado ($correo, IP $ip)", 'WARN');
                $mensaje = "Usuario no encontrado.";
            }

        } else {
            /* -----------------------------------------
               ❌ Token expirado — eliminarlo siempre
            ----------------------------------------- */
            $sqlDel = "DELETE FROM verificaciones WHERE token=?";
            $stmtDel = mysqli_prepare($conn, $sqlDel);
            mysqli_stmt_bind_param($stmtDel, "s", $token);
            mysqli_stmt_execute($stmtDel);

            registrar_log('token_expirado', "Token expirado ($correo, IP $ip)", 'WARN');
            $mensaje = "El enlace ha expirado. Solicita un nuevo registro.";
        }
    }
    else {
        registrar_log('token_invalido', "Token inexistente/reutilizado (IP $ip)", 'WARN');
        $mensaje = "Token inválido o ya utilizado.";
    }

    usleep(random_int(130000, 350000)); // anti-bot
} else {
    $mensaje = "No se recibió ningún token de verificación.";
}
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<section class="formulario">
  <h2>Verificación de cuenta</h2>

  <?php if ($mensaje): ?>
  <p class="mensaje <?= $mensaje_tipo === 'exito' ? 'exito' : 'error'; ?>">
      <?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8'); ?>
  </p>
  <?php endif; ?>

  <p><a href="/nove_optica/auth/login.php" class="boton-secundario">Volver al inicio de sesión</a></p>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
