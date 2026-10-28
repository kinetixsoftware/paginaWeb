<?php
session_start();

if ((int) ($_SESSION['rol'] ?? 0) !== 3) {
    $_SESSION['mensaje'] = 'Usted no tiene permiso para descargar el backup.';
    $_SESSION['tipoError'] = 'error';
    header('Location: /Kinetix/es/inicio');
    exit;
}

require_once __DIR__ . '/../../php/conexionBDD.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conexion = conectarBD();

    $nombreBase = $conexion->query('SELECT DATABASE()')->fetch_row()[0];

    if (!$nombreBase) {
        throw new RuntimeException('No hay una base de datos seleccionada.');
    }

    $citar = fn($nombre) => '`' . str_replace('`', '``', $nombre) . '`';

    $backup = "CREATE DATABASE IF NOT EXISTS " . $citar($nombreBase) . ";\n";
    $backup .= "USE " . $citar($nombreBase) . ";\n";
    $backup .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";

    $tablas = $conexion->query('SHOW FULL TABLES');

    while ($fila = $tablas->fetch_row()) {
        // Solo tablas, no vistas.
        if (($fila[1] ?? 'BASE TABLE') !== 'BASE TABLE') {
            continue;
        }

        $tabla = $citar($fila[0]);
        $crear = $conexion->query("SHOW CREATE TABLE $tabla")->fetch_row();

        $backup .= "DROP TABLE IF EXISTS $tabla;\n";
        $backup .= $crear[1] . ";\n\n";
    }

    $backup .= "SET FOREIGN_KEY_CHECKS = 1;\n";

    header('Content-Type: application/sql; charset=UTF-8');
    header('Content-Disposition: attachment; filename="kinetix-estructura-' . date('Ymd-His') . '.sql"');
    header('Cache-Control: no-store');

    echo $backup;
    exit;

} catch (Throwable $error) {
    error_log('Error al generar backup: ' . $error->getMessage());
    http_response_code(500);
    exit('No se pudo generar el backup de la estructura de la base de datos.');
}