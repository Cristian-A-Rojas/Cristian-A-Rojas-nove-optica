<?php
// ****************************** CONEXIÓN A BASE DE DATOS ******************************
function conectar_bd() {

    $host = getenv('MYSQL_HOST');
    $user = getenv('MYSQL_USER');
    $pass = getenv('MYSQL_PASS');
    $db   = getenv('MYSQL_DB');
    $port = getenv('MYSQL_PORT') ?: 3306;

    $con = @mysqli_connect($host, $user, $pass, $db, $port);

    if (!$con) {
        error_log("[" . date('Y-m-d H:i:s') . "] Error de conexión MySQL: " . mysqli_connect_error());
        die("Error interno del sistema.");
    }

    mysqli_set_charset($con, "utf8mb4");
    return $con;
}

function cerrar_bd($conexion) {
    if ($conexion && mysqli_ping($conexion)) {
        mysqli_close($conexion);
    }
}
?>

