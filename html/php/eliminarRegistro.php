<?php 
session_start();

$rol = $_SESSION['rol'];
if (!$rol === 3){
    $_SESSION['mensaje'] = "No tienes permiso para ver esta pagina";
    $_SESSION['tipoError'] = "error";
    header("Location: ../es/inicio.php");
}

$servername = "localhost";
$username = "root";
$passwordbd = "";
$dbname = "kinetixsoftware";

$conexion = mysqli_connect($servername, $username, $passwordbd, $dbname);

if (!$conexion) {
    die("Connection failed: " . mysqli_connect_error());
}  



if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tabla'], $_POST['id'])) {
    $tabla = $_POST['tabla'];
    $id = (int) $_POST['id'];

    $primaryKey = [
        //Activos
        'activo' => 'id_activo',
        'activo_marca' => 'id_marca',
        'categoria_activo' => 'id_categoria',
        'estado_activo' => 'id_estado',
        //Tickets
        'diagnostico' => 'id_diagnostico',
        'categoria_ticket' => 'id_categoria',
        'resultado_ticket' => 'id_resultado',
        'estado_ticket' => 'id_estado',
        'historial_ticket' => 'id_historial',
        'mensaje_ticket' => 'id_mensaje',
        'ticket' => 'id_ticket',
        //ubicacion
        'ciudad' => 'id_ciudad',
        'ubicacion' => 'id_ubicacion',
        //solicitud? no se
        'estado_solicitud' => 'id_estado',
        'solicitud_servicio' => 'id_solicitud',
        //usuario
        'rol' => 'id_rol',
        'usuario' => 'id_usuario',
    ];

    if (!isset($primaryKey[$tabla]) || $id <= 0) {
        $_SESSION['mensaje'] = "La solicitud de eliminación no es válida.";
        $_SESSION['tipoError'] = "error";
        header("Location: ../es/panel-admin.php");
        exit;
    }

    $campoID = $primaryKey[$tabla];

    try {
        mysqli_query($conexion, "DELETE FROM `$tabla` WHERE `$campoID` = $id");
        $_SESSION['mensaje'] = "Registro eliminado correctamente.";
        $_SESSION['tipoError'] = "success";
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() === 1451) {
            $_SESSION['mensaje'] = "No se puede eliminar este registro porque está relacionado con otros registros.";
        } else {
            $_SESSION['mensaje'] = "No se pudo eliminar el registro.";
        }
        $_SESSION['tipoError'] = "error";
    }

    header("Location: ../es/panel-admin.php");
    exit;
}

?>