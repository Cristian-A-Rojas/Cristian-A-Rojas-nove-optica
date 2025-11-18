<?php

// ===============================
//  SISTEMA DE LOGS
// ===============================

function registrar_log($accion, $descripcion = '', $nivel = 'INFO') {
    $fecha = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'sin_ip';
    $usuario = $_SESSION['usuario'] ?? 'anonimo';
    $msg = "[$fecha] [$nivel] [$usuario@$ip] $accion - $descripcion" . PHP_EOL;

    $ruta = __DIR__ . '/../logs/auditoria.log';
    file_put_contents($ruta, $msg, FILE_APPEND | LOCK_EX);
}

?>
