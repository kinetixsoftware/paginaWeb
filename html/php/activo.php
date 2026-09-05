<?php 
session_start();

$servername = "localhost";
$username = "root";
$passwordbd = "";
$dbname = "kinetixsoftware";

$conexion = mysqli_connect($servername, $username, $passwordbd, $dbname);

if (!$conexion) {
    die("Connection failed: " . mysqli_connect_error());
}  

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Activo no encontrado.");
}

$id_activo = (int) $_GET['id'];

$sql = "
    SELECT a.id_activo, a.nombre, a.modelo, a.descripcion, a.numero_serie, a.fecha_adquisicion, a.fecha_baja, a.imagen_path, a.id_marca, m.marca, a.id_estado, e.estado, a.id_categoria, c.categoria
    FROM activo AS a
    JOIN activo_marca AS m
        ON a.id_marca = m.id_marca
    JOIN estado_activo AS e
        ON a.id_estado = e.id_estado
    JOIN categoria_activo AS c
        ON a.id_categoria = c.id_categoria
    WHERE a.id_activo = '$id_activo'
";

$resultado = mysqli_query($conexion, $sql);
$reg = mysqli_fetch_array($resultado);

if (!$reg) {
    die("Activo no encontrado.");
}
?>

