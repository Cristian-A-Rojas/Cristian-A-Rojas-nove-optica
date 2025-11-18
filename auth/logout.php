<?php

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../config/db.php';

$conn = conectar_bd();


// Validación CSRF y método

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    registrar_log('logout_fallido', 'Intento de cierre de sesión no-POST', 'WARN');
    http_response_code(405);
    exit('Método no permitido.');
}

if (!csrf_verificar_token($_POST['csrf_token'] ?? '')) {
    registrar_log('logout_fallido', 'Token CSRF inválido o ausente', 'WARN');
    http_response_code(403);
    exit('Acción no autorizada.');
}


// Datos de auditoría

$id   = $_SESSION['usuario_id'] ?? null;
$usr  = $_SESSION['usuario'] ?? 'desconocido';
$ip   = $_SERVER['REMOTE_ADDR'] ?? 'no_detectada';


// Destrucción segura

$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $p['path'],
        $p['domain'],
        $p['secure'],
        $p['httponly']
    );
}

session_destroy();


// Regenerar entorno limpio

session_start([
    'cookie_httponly' => true,
    'cookie_secure'   => !empty($_SERVER['HTTPS']),
    'cookie_samesite' => 'Strict',
]);
session_regenerate_id(true);


// Registrar auditoría

if (!empty($id)) {
    $accion = 'logout';
    $descripcion = "Cierre de sesión seguro por $usr desde IP $ip";

    $sql = "INSERT INTO log_admin (id_admin, accion, descripcion) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "iss", $id, $accion, $descripcion);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    registrar_log($accion, $descripcion, 'INFO');
}

cerrar_bd($conn);


// Redirección segura

header('Location: /nove_optica/index.php?logout=ok');
exit();
?>
