<?php

function conectarBD(): mysqli
{
    $conexion = new mysqli(
        "TU-SERVIDOR",
        "TU-USUARIO",
        "TU-CONTRASEÑA",
        "NOMBRE-DE-LA-BASE-DE-DATOS"
    );

    if ($conexion->connect_error) {
        die("Error de conexión: " . $conexion->connect_error);
    }

    $conexion->set_charset("utf8mb4");

    return $conexion;
}

#Para poder usar este ejemplo, cambiar las variables dentro de $conexion y cambiar el nombre de este archivo a -> conexionBDD.php