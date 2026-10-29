<?php 
session_start();

require_once "../../../php/conexionBDD.php";

$conexion = conectarBD();

$rol = (int) ($_SESSION['rol'] ?? 0);
if ($rol !== 3) {
    $_SESSION['mensaje'] = "No tienes permiso para ver esta pagina";
    $_SESSION['tipoError'] = "error";
    header("Location: /Kinetix/es/inicio");
    exit;
}

if (!$conexion) {
    die("Connection failed: " . mysqli_connect_error());
}  

$tablas = [
    'activo' => ['id_activo', 'codigo_inventario', 'nombre', 'descripcion', 'numero_serie', 'fecha_adquisicion', 'id_categoria', 'id_estado', 'id_ubicacion', 'id_marca', 'modelo', 'id_usuario'],
    'activo_marca' => ['id_marca', 'marca'],
    'categoria_activo' => ['id_categoria', 'categoria'],
    'categoria_ticket' => ['id_categoria', 'categoria'],
    'diagnostico' => ['id_diagnostico', 'descripcion', 'fecha', 'id_ticket', 'id_tecnico'],
    'estado_activo' => ['id_estado', 'estado'],
    'estado_solicitud' => ['id_estado', 'estado'],
    'estado_ticket' => ['id_estado', 'estado'],
    'historial_activo' => ['id_historial', 'id_activo', 'fecha', 'accion'],
    'historial_ticket' => ['id_historial', 'id_ticket', 'estado_anterior', 'estado_nuevo', 'fecha'],
    'mensaje_ticket' => ['id_mensaje', 'id_ticket', 'id_usuario', 'contenido', 'fecha_creacion'],
    'prioridad_ticket' => ['id_prioridad', 'prioridad'],
    'pregunta_frecuente' => ['id_pregunta', 'pregunta', 'respuesta'],
    'resultado_ticket' => ['id_resultado', 'solucion', 'fecha_resolucion', 'id_ticket', 'id_tecnico'],
    'rol' => ['id_rol', 'nombre_rol'],
    'solicitud_servicio' => ['id_solicitud', 'descripcion', 'fecha_creacion', 'aprobacion', 'id_estado', 'id_solicitante', 'id_ubicacion', 'id_activo', 'prioridad', 'tipo_servicio'],
    'ticket' => ['id_ticket', 'titulo', 'descripcion', 'fecha_creacion', 'id_estado', 'id_prioridad', 'id_solicitante', 'id_tecnico', 'id_categoria'],
    'ubicacion' => ['id_ubicacion', 'calle', 'id_ciudad'],
    'ciudad' => ['id_ciudad', 'ciudad', 'departamento'],
    'usuario' => ['id_usuario', 'nombre', 'apellido', 'email', 'password', 'activo', 'id_rol', 'id_ubicacion']
];

$tabla = $_POST['tabla'] ?? '';
$idRegistro = filter_var($_POST['idRegistro'] ?? $_POST['id'] ?? 0, FILTER_VALIDATE_INT);

if (!isset($tablas[$tabla]) || !$idRegistro || $idRegistro <= 0) {
    $_SESSION['mensaje'] = 'El registro seleccionado no es válido.';
    $_SESSION['tipoError'] = 'error';
    header('Location: /Kinetix/es/admin/panel-admin');
    exit;
}

$urlRetorno = '/Kinetix/es/admin/panel-admin';

$columnas = $tablas[$tabla];
$clavePrimaria = $columnas[0];

mysqli_set_charset($conexion, 'utf8mb4');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'guardar') {
    $cambios = [];

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

        $camposOpcionales = [
            'activo' => ['fecha_baja', 'id_usuario'],
            'ticket' => ['id_tecnico', 'id_activo'],
            'solicitud_servicio' => ['id_ubicacion', 'id_activo']
        ];

        if ($valor === '' && (
            in_array($columna, $camposOpcionales[$tabla] ?? [], true)
            || in_array($columna, ['imagenPATH', 'imagen_path'], true)
        )) {
            $cambios[] = "`$columna` = NULL";
        } else {
            $valor = mysqli_real_escape_string($conexion, trim($valor));
            $cambios[] = "`$columna` = '$valor'";
        }
    }

    $consultaActualizar = "UPDATE `$tabla` SET " . implode(', ', $cambios) . " WHERE `$clavePrimaria` = $idRegistro";

    if (!mysqli_query($conexion, $consultaActualizar)) {
        $_SESSION['mensaje'] = "Error al actualizar este registro: " . mysqli_error($conexion);
        $_SESSION['tipoError'] = "error";
        header('Location: ' . $urlRetorno);
        exit;
    }

    $_SESSION['mensaje'] = "Registro actualizado correctamente. ";
    $_SESSION['tipoError'] = "success";
    header('Location: ' . $urlRetorno);
    exit;
}

$registroEditar = null;

$consultaEditar = "SELECT * FROM `$tabla` WHERE `$clavePrimaria` = $idRegistro LIMIT 1";
$resultadoEditar = mysqli_query($conexion, $consultaEditar);
if (!$resultadoEditar) {
    throw new RuntimeException('No se pudo cargar el registro: ' . mysqli_error($conexion));
}
$registroEditar = mysqli_fetch_assoc($resultadoEditar);

if (!$registroEditar) {
    die('No se encontró el registro solicitado.');
}

?>


<!DOCTYPE html>
<html lang="es">
<head>
    <base href="/Kinetix/es/admin/registro/">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel administrador</title>
    <link rel="icon" type="image/png" href="../../../imagenes/favicon.png">
    <link rel="stylesheet" href="../css/panel-admin.css">
    <link rel="stylesheet" href="../../../css/kinetix-theme.css">
    <link rel="stylesheet" href="../../../css/menu-layout.css">
</head>
<body class="admin-record-body">
    <header class="admin-record-header">
        <a href="<?= $urlRetorno ?>" class="admin-record-brand">
            <img src="../imagenes/Logo1.png" alt="Kinetix">
            <span>Centro de administración</span>
        </a>
        <a class="admin-record-back" href="<?= $urlRetorno ?>">Volver</a>
    </header>

    <main class="admin-record-page">
        <section class="admin-record-card">
            <div class="admin-record-intro">
                <p class="admin-record-eyebrow">GESTIÓN DE DATOS</p>
                <h1>Editar registro</h1>
                <p>Actualizá los campos de <strong><?= $tabla ?></strong>.</p>
            </div>
            <form class="admin-record-form" method="post">
                <input type="hidden" name="tabla" value="<?= $tabla ?>">
                <input type="hidden" name="idRegistro" value="<?= $idRegistro ?>">
                <input type="hidden" name="accion" value="guardar">
                <?php foreach ($columnas as $columna) { ?>
                    <?php if ($columna == $clavePrimaria) {continue;} ?>
                    <div class="admin-record-field <?= in_array($columna, ['descripcion', 'mensaje', 'contenido', 'solucion', 'pregunta', 'respuesta', 'accion'], true) ? 'wide' : '' ?>">
                    <label for="<?= $columna ?>"><?= ucfirst(str_replace('_', ' ', $columna)) ?></label>
                    <?php if (in_array($columna, ['descripcion', 'mensaje', 'contenido', 'solucion', 'pregunta', 'respuesta', 'accion'])) { ?>
                        <textarea id="<?= $columna ?>" name="<?= $columna ?>" rows="4"><?= $registroEditar[$columna] ?? '' ?></textarea>
                    <?php } else { ?>
                        <input id="<?= $columna ?>" name="<?= $columna ?>" type="<?= $columna == 'password' ? 'password' : 'text' ?>" value="<?= $columna == 'password' ? '' : $registroEditar[$columna] ?? '' ?>" <?= $columna === 'password' ? 'autocomplete="new-password" placeholder="Dejar vacío para mantener la contraseña"' : '' ?>>
                    <?php } ?>
                    </div>
                <?php } ?>

                <div class="admin-record-actions">
                    <button type="submit">Guardar cambios</button>
                    <a href="<?= $urlRetorno ?>">Cancelar</a>
                </div>
            </form>
        </section>
    </main>
</body>
</html>