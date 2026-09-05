<?php
session_start();

require_once "../php/conexionBDD.php";

$conexion = conectarBD();

$rol = $_SESSION['rol'] ?? null;

if (!$rol == 3) {
    $_SESSION['mensaje'] = "No tienes permiso para ver esta pagina";
    $_SESSION['tipoError'] = "error";
    header("Location: ../es/inicio.php");
    exit;
}


$tablas = [
    'activo' => ['id_activo', 'codigo_inventario', 'nombre', 'descripcion', 'marca', 'modelo', 'numero_serie', 'fecha_adquisicion', 'fecha_baja', 'id_categoria', 'id_estado', 'id_ubicacion', 'id_marca'],
    'activo_marca' => ['id_marca', 'marca'],
    'categoria_activo' => ['id_categoria', 'categoria'],
    'categoria_ticket' => ['id_categoria', 'categoria'],
    'diagnostico' => ['id_diagnostico', 'descripcion', 'fecha', 'id_ticket', 'id_tecnico'],
    'estado_activo' => ['id_estado', 'estado'],
    'estado_solicitud' => ['id_estado', 'estado'],
    'estado_ticket' => ['id_estado', 'estado'],
    'historial_ticket' => ['id_historial', 'id_ticket', 'id_usuario', 'estado_anterior', 'estado_nuevo', 'fecha'],
    'mensaje' => ['id_mensaje', 'mensaje', 'imagenPATH', 'id_usuario', 'id_ticket'],
    'mensaje_ticket' => ['id_mensaje', 'id_ticket', 'id_usuario', 'contenido', 'imagen_path', 'fecha_creacion'],
    'prioridad_ticket' => ['id_prioridad', 'prioridad'],
    'resultado_ticket' => ['id_resultado', 'solucion', 'fecha_resolucion', 'id_ticket', 'id_tecnico'],
    'rol' => ['id_rol', 'nombre_rol'],
    'solicitud_servicio' => ['id_solicitud', 'titulo', 'descripcion', 'fecha_creacion', 'aprobacion', 'id_estado', 'id_solicitante'],
    'ticket' => ['id_ticket', 'titulo', 'descripcion', 'fecha_creacion', 'id_estado', 'id_prioridad', 'id_solicitante', 'id_tecnico', 'id_activo', 'id_categoria'],
    'ubicacion' => ['id_ubicacion', 'calle', 'id_ciudad'],
    'ciudad' => ['id_ciudad', 'ciudad', 'id_departamento'],
    'usuario' => ['id_usuario', 'nombre', 'apellido', 'email', 'password', 'activo', 'id_rol']
];

$tabla = $_POST['tabla'] ?? $_GET['tabla'] ?? '';

if (!isset($tablas[$tabla])) {
    $_SESSION['mensaje'] = 'Tabla no válida.';
    $_SESSION['tipoError'] = 'error';
    header('Location: ../es/panel-admin.php');
    exit;
}

$columnas = $tablas[$tabla];
$clavePrimaria = $columnas[0];

mysqli_set_charset($conexion, 'utf8mb4');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'ingresar') {
    $campos = [];

    foreach ($columnas as $columna) {
        if ($columna === $clavePrimaria) {
            continue;
        }

        $valor = $_POST[$columna] ?? '';

        if ($tabla === 'usuario' && $columna === 'password') {
            if ($valor === '') {
                continue;
            }
            $valor = password_hash($valor, PASSWORD_DEFAULT);
        }

        if ($valor === '' && in_array($columna, ['fecha_baja', 'imagenPATH', 'imagen_path'], true)) {
            $campos[] = "`$columna` = NULL";
        } else {
            $valor = mysqli_real_escape_string($conexion, trim($valor));
            $campos[] = "`$columna` = '$valor'";
        }
    }

    if (empty($campos)) {
        $_SESSION['mensaje'] = 'No se recibieron campos para insertar.';
        $_SESSION['tipoError'] = 'error';
        header('Location: ../es/panel-admin.php');
        exit;
    }

    $consultaIngresar = "INSERT INTO `$tabla` SET " . implode(', ', $campos);

    if (!mysqli_query($conexion, $consultaIngresar)) {
        die('Error al ingresar el registro: ' . mysqli_error($conexion));
    }

    $_SESSION['mensaje'] = "Registro ingresado correctamente.";
    $_SESSION['tipoError'] = "success";
    header('Location: ../es/panel-admin.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel administrador</title>
    <link rel="stylesheet" href="../css/admin-panel.css">
</head>
<body>
    <header class="encabezado">
        <a href="../es/inicio.php"><img src="../imagenes/logo1.png" alt="Kinetix"></a>
    </header>

    <main class="contenedor">
        <section class="formulario">
            <form method="post">
                <input type="hidden" name="tabla" value="<?= $tabla?>">
                <input type="hidden" name="accion" value="ingresar">
                <?php foreach ($columnas as $columna) { ?>
                    <?php if ($columna === $clavePrimaria) { continue; } ?>
                    <label for="<?= $columna?>"><?= $columna?></label>
                    <?php if (in_array($columna, ['descripcion', 'mensaje', 'contenido', 'solucion'], true)) { ?>
                        <textarea id="<?= $columna?>" name="<?= $columna?>"><?= $registroEditar[$columna] ?? ''?></textarea>
                    <?php } else { ?>
                        <input id="<?= $columna?>" name="<?= $columna?>" type="<?= $columna === 'password' ? 'password' : 'text' ?>" value="<?= $columna === 'password' ? '' : $registroEditar[$columna] ?? ''?>">
                    <?php } ?>
                <?php } ?>

                <button type="submit">Ingresar</button>
                <a href="../es/panel-admin.php">Cancelar</a>
            </form>
        </section>
    </main>
</body>
</html>