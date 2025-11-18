<?php
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/security.php';
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../config/db.php';

require_admin();

$csrf = $_POST['csrf_token'] ?? '';
if (!csrf_verificar_token($csrf)) die("CSRF token inválido");

$id = intval($_POST['id'] ?? 0);
$nombre = limpiar($_POST['nombre'] ?? '');
$precio = (float)($_POST['precio'] ?? 0);
$descripcion = limpiar($_POST['descripcion'] ?? '');

$conn = conectar_bd();

// ---- Si hay nueva imagen ----
$imagen_final = null;

if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0) {
    $ext = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
    $imagen_final = uniqid("prod_") . "." . strtolower($ext);
    move_uploaded_file($_FILES['imagen']['tmp_name'], __DIR__ . '/../../uploads/' . $imagen_final);
} else {
    // Mantener la existente
    $res = mysqli_query($conn, "SELECT imagen FROM productos WHERE id=$id");
    $fila = mysqli_fetch_assoc($res);
    $imagen_final = $fila['imagen'];
}

$sql = "UPDATE productos SET nombre=?, precio=?, descripcion=?, imagen=? WHERE id=?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "sdssi", $nombre, $precio, $descripcion, $imagen_final, $id);
mysqli_stmt_execute($stmt);

registrar_log('producto_update', "Producto actualizado ID $id", 'INFO');

header("Location: /nove_optica/admin/panel.php");
exit;
