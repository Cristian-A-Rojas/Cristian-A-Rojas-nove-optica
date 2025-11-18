<?php
/**
 * NOVE ÓPTICA – Zero Trust Build V13.2
 * Endpoint seguro de estadísticas (AJAX + JSON)
 * Ultra-Stable AppServ Edition
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json; charset=utf-8');
$conn = conectar_bd();
enforce_admin(); // bloquea si no es admin o sin 2FA

// ============================
// VALIDACIÓN CSRF (cabecera o POST)
// ============================
$csrf_token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['csrf_token'] ?? '');
if (!csrf_verificar_token($csrf_token)) {
    registrar_log('csrf_fail', 'Intento de acceso AJAX sin token válido', 'WARN');
    http_response_code(403);
    echo json_encode(['error' => 'Petición no autorizada']);
    usleep(random_int(120000, 400000));
    exit;
}

// ============================
// VALIDACIÓN DE FECHAS
// ============================
$inicio = $_POST['inicio'] ?? '';
$fin    = $_POST['fin'] ?? '';

if (!$inicio || !$fin) {
    echo json_encode(['error' => 'Fechas incompletas']);
    exit;
}

// ============================
// CONSULTAS DE DATOS
// ============================
$data = [
    'labelsUsuarios' => [], 'dataUsuarios' => [],
    'labelsVentas'   => [], 'dataVentas'   => [],
    'labelsTop'      => [], 'dataTop'      => []
];

// Usuarios nuevos
$sqlU = "SELECT DATE(fecha_registro) AS dia, COUNT(*) AS total
         FROM usuarios WHERE fecha_registro BETWEEN ? AND ? GROUP BY dia ORDER BY dia ASC";
$stmt = mysqli_prepare($conn, $sqlU);
mysqli_stmt_bind_param($stmt, "ss", $inicio, $fin);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
while ($r = mysqli_fetch_assoc($res)) {
    $data['labelsUsuarios'][] = $r['dia'];
    $data['dataUsuarios'][]   = (int)$r['total'];
}

// Ventas diarias
$sqlV = "SELECT DATE(fecha) AS dia, SUM(total) AS total
         FROM ventas WHERE fecha BETWEEN ? AND ? GROUP BY dia ORDER BY dia ASC";
$stmt = mysqli_prepare($conn, $sqlV);
mysqli_stmt_bind_param($stmt, "ss", $inicio, $fin);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
while ($r = mysqli_fetch_assoc($res)) {
    $data['labelsVentas'][] = $r['dia'];
    $data['dataVentas'][]   = (float)$r['total'];
}

// Productos más vendidos
$sqlT = "SELECT p.nombre, SUM(vd.cantidad) AS total
         FROM venta_detalle vd
         JOIN productos p ON p.id = vd.producto_id
         JOIN ventas v ON v.id = vd.venta_id
         WHERE v.fecha BETWEEN ? AND ?
         GROUP BY p.id ORDER BY total DESC LIMIT 5";
$stmt = mysqli_prepare($conn, $sqlT);
mysqli_stmt_bind_param($stmt, "ss", $inicio, $fin);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
while ($r = mysqli_fetch_assoc($res)) {
    $data['labelsTop'][] = $r['nombre'];
    $data['dataTop'][]   = (int)$r['total'];
}

registrar_log('stats_data_ok', "Consulta de estadísticas por {$_SESSION['usuario']} ({$inicio}–{$fin})", 'DEBUG');

echo json_encode($data, JSON_UNESCAPED_UNICODE);
?>
