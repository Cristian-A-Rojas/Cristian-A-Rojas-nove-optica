<?php
/**
 * NOVE ÓPTICA – Zero Trust Build V13.2
 * Módulo de envío de correos seguros + verificación 2FA
 * Ultra-Stable AppServ Edition
 */

require_once __DIR__ . '/db.php';


// ✉️ CONFIGURACIÓN GLOBAL

define('MAIL_FROM',    'seguridad@noveoptica.com');
define('MAIL_NAME',    'NOVE Óptica');
define('MAIL_REPLY',   'no-responder@noveoptica.com');


// 🧰 FUNCIÓN PRINCIPAL DE ENVÍO

function enviar_correo($destinatario, $asunto, $mensaje, $modo_html = false) {
    $destinatario = limpiar($destinatario);
    $asunto = limpiar($asunto);

    if (empty($destinatario) || !filter_var($destinatario, FILTER_VALIDATE_EMAIL)) {
        error_log("[" . date('Y-m-d H:i:s') . "] Correo inválido: $destinatario");
        return false;
    }

    // Cabeceras seguras
    $headers  = "From: " . MAIL_NAME . " <" . MAIL_FROM . ">\r\n";
    $headers .= "Reply-To: " . MAIL_REPLY . "\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: " . ($modo_html ? "text/html; charset=UTF-8" : "text/plain; charset=UTF-8") . "\r\n";
    $headers .= "X-Content-Type-Options: nosniff\r\n";
    $headers .= "X-Frame-Options: DENY\r\n";

    $resultado = @mail($destinatario, $asunto, $mensaje, $headers);

    $estado = $resultado ? 'ENVIADO' : 'FALLO';
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'sin_ip';
    registrar_log("correo_$estado", "Para: $destinatario | Asunto: $asunto | IP:$ip");

    return $resultado;
}


// 🎨 PLANTILLAS HTML (modo pastel + oscuro)

function plantilla_html($titulo, $contenido_html) {
    return "
    <html><head><style>
    body {
        font-family: 'Segoe UI', sans-serif;
        background: #f4f5fa;
        color: #333;
        padding: 25px;
    }
    @media (prefers-color-scheme: dark) {
        body { background: #1e1e1e; color: #eaeaea; }
        .card { background: #2b2b2b; }
    }
    .card {
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.15);
        padding: 25px;
        max-width: 600px;
        margin: auto;
    }
    h2 { color: #6a5acd; text-align: center; }
    .footer {
        text-align:center;
        color:#999;
        font-size:12px;
        margin-top:20px;
    }
    a.btn {
        background:#6a5acd;
        color:#fff;
        padding:10px 20px;
        text-decoration:none;
        border-radius:6px;
    }
    </style></head><body>
        <div class='card'>
            <h2>$titulo</h2>
            $contenido_html
            <p class='footer'>Mensaje automático de NOVE Óptica — No responder</p>
        </div>
    </body></html>";
}


// 📩 Verificación de cuenta

function correo_verificacion($correo, $nombre, $token) {
    $enlace = "http://" . $_SERVER['HTTP_HOST'] . "/nove_optica/auth/verificar.php?token=$token";
    $asunto = "Confirma tu cuenta en NOVE Óptica";

    $contenido = "
        <p>Hola <b>" . limpiar($nombre) . "</b>,</p>
        <p>Gracias por registrarte en <strong>NOVE Óptica</strong>.</p>
        <p>Confirma tu cuenta haciendo clic en el siguiente botón:</p>
        <p style='text-align:center;'><a href='$enlace' class='btn'>Verificar cuenta</a></p>
        <p>Este enlace expira en <strong>15 minutos</strong>.</p>
        <hr><small>Si no realizaste esta acción, ignora este mensaje.</small>
    ";

    $mensaje = plantilla_html("Verificación de cuenta", $contenido);
    return enviar_correo($correo, $asunto, $mensaje, true);
}


// 🔐 Segundo factor (2FA administrador)

function correo_verificacion_admin($correo, $token) {
    $enlace = "http://" . $_SERVER['HTTP_HOST'] . "/nove_optica/auth/verificar.php?token=$token";
    $asunto = "Verifica tu acceso administrador — NOVE Óptica";

    $contenido = "
        <p>Confirmación de acceso al panel de administración:</p>
        <p style='text-align:center;'><a href='$enlace' class='btn'>Verificar acceso</a></p>
        <p>El enlace expira en <strong>10 minutos</strong>.</p>
        <hr><small>Si no solicitaste este acceso, cambia tu contraseña inmediatamente.</small>
    ";

    $mensaje = plantilla_html("Acceso administrador", $contenido);
    return enviar_correo($correo, $asunto, $mensaje, true);
}


// 🔑 Envío de código 2FA temporal

function correo_codigo_2FA($correo, $usuario) {
    $codigo = random_int(100000, 999999);
    $_SESSION['2fa_code'] = $codigo;
    $_SESSION['2fa_expira'] = time() + 300; // 5 minutos

    $asunto = "Código de verificación - NOVE Óptica";
    $contenido = "
        <p>Hola <b>" . limpiar($usuario) . "</b>,</p>
        <p>Tu código de verificación es:</p>
        <h2 style='text-align:center;font-size:32px;'>$codigo</h2>
        <p>Este código caduca en 5 minutos.</p>
        <hr><small>Si no has intentado iniciar sesión, ignora este mensaje.</small>
    ";

    $mensaje = plantilla_html("Verificación en dos pasos", $contenido);
    return enviar_correo($correo, $asunto, $mensaje, true);
}


// ✅ Verificar código 2FA

function verificar_codigo_2FA($codigo_ingresado) {
    if (empty($_SESSION['2fa_code']) || empty($_SESSION['2fa_expira'])) return false;
    if (time() > $_SESSION['2fa_expira']) {
        unset($_SESSION['2fa_code'], $_SESSION['2fa_expira']);
        return false;
    }
    $valido = hash_equals((string)$_SESSION['2fa_code'], (string)$codigo_ingresado);
    if ($valido) unset($_SESSION['2fa_code'], $_SESSION['2fa_expira']);
    return $valido;
}
?>
