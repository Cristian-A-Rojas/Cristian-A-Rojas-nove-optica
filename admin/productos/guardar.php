<?php
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/security.php';
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../config/db.php';

require_admin();

$csrf = $_POST['csrf_token'] ?? '';
if (!csrf_verificar_token($csrf)) die('CSRF token inválido');

$nombre = limpiar($_POST['nombre'] ?? '');
$precio = (float)($_POST['precio'] ?? 0);
$descripcion = limpiar($_POST['descripcion'] ?? '');

if (!$nombre || !$precio || !$descripcion) die("Datos incompletos.");

// ---- Subir imagen ----
if (!isset($_FILES['imagen']) || $_FILES['imagen']['error'] !== 0)
    die("Error al subir imagen.");

$ext = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
$nombre_img = uniqid("prod_") . "." . strtolower($ext);
$ruta = __DIR__ . '/../../uploads/' . $nombre_img;

move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta);

$conn = conectar_bd();
$sql = "INSERT INTO productos (nombre, precio, descripcion, imagen)
        VALUES (?, ?, ?, ?)";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "sdss", $nombre, $precio, $descripcion, $nombre_img);
mysqli_stmt_execute($stmt);

registrar_log('producto_nuevo', "Producto creado: $nombre", 'INFO');

header("Location: /nove_optica/admin/panel.php");
exit;
