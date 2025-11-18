<?php
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/security.php';
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../config/db.php';

require_admin();

$csrf = $_POST['csrf_token'] ?? '';
if (!csrf_verificar_token($csrf)) die("CSRF inválido");

$id = intval($_POST['id'] ?? 0);
if ($id < 1) die("ID inválido");

$conn = conectar_bd();

// borrar imagen física
$res = mysqli_query($conn, "SELECT imagen FROM productos WHERE id=$id");
$p = mysqli_fetch_assoc($res);
if ($p && file_exists(__DIR__ . '/../../uploads/' . $p['imagen'])) {
    unlink(__DIR__ . '/../../uploads/' . $p['imagen']);
}

// borrar BD
$stmt = mysqli_prepare($conn, "DELETE FROM productos WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

registrar_log('producto_delete', "Producto eliminado ID $id", 'WARN');

header("Location: /nove_optica/admin/panel.php");
exit;
