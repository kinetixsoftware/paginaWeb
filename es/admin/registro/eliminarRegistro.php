<?php 
session_start();

require_once "../../../php/conexionBDD.php";

$conexion = conectarBD();

$rol = (int) ($_SESSION['rol'] ?? 0);
if ($rol !== 3){
    $_SESSION['mensaje'] = "No tienes permiso para ver esta pagina";
    $_SESSION['tipoError'] = "error";
    header("Location: /Kinetix/es/inicio");
    exit;
}

if (!$conexion) {
    die("Connection failed: " . mysqli_connect_error());
}  



if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tabla'], $_POST['id'])) {
    $tabla = $_POST['tabla'];
    $id = (int) $_POST['id'];
    $urlRetorno = '/Kinetix/es/admin/panel-admin';

    if ($tabla === 'usuario' && $id === (int) ($_SESSION['idUsuario'] ?? 0)) {
        $_SESSION['mensaje'] = 'No puedes eliminar tu propio usuario mientras tienes la sesión iniciada.';
        $_SESSION['tipoError'] = 'error';
        header("Location: $urlRetorno");
        exit;
    }

    $primaryKey = [
        //Activos
        'activo' => 'id_activo',
        'activo_marca' => 'id_marca',
        'categoria_activo' => 'id_categoria',
        'estado_activo' => 'id_estado',
        //Tickets
        'diagnostico' => 'id_diagnostico',
        'historial_activo' => 'id_historial',
        'categoria_ticket' => 'id_categoria',
        'resultado_ticket' => 'id_resultado',
        'estado_ticket' => 'id_estado',
        'historial_ticket' => 'id_historial',
        'mensaje_ticket' => 'id_mensaje',
        'ticket' => 'id_ticket',
        'pregunta_frecuente' => 'id_pregunta',
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
        header("Location: $urlRetorno");
        exit;
    }

    $campoID = $primaryKey[$tabla];

    try {
        if (!mysqli_query($conexion, "DELETE FROM `$tabla` WHERE `$campoID` = $id")) {
            throw new RuntimeException(mysqli_error($conexion));
        }
        if (mysqli_affected_rows($conexion) === 0) {
            $_SESSION['mensaje'] = "El registro ya no existe.";
            $_SESSION['tipoError'] = "error";
        } else {
            $_SESSION['mensaje'] = "Registro eliminado correctamente.";
            $_SESSION['tipoError'] = "success";
        }
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() === 1451) {
            $_SESSION['mensaje'] = "No se puede eliminar este registro porque está relacionado con otros registros.";
        } else {
            $_SESSION['mensaje'] = "No se pudo eliminar el registro.";
        }
        $_SESSION['tipoError'] = "error";
    } catch (RuntimeException $e) {
        $_SESSION['mensaje'] = "No se pudo eliminar el registro.";
        $_SESSION['tipoError'] = "error";
    }

    header("Location: $urlRetorno");
    exit;
}

?>