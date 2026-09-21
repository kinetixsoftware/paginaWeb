<?php 
session_start();

require_once "../php/conexionBDD.php";

$conexion = conectarBD();

if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 3) {
    $_SESSION['mensaje'] = "Usted no tiene permiso para ver esta pagina";
    $_SESSION['tipoError'] = "error";
    header("Location: inicio.php");
    exit;
}

$mensaje = "";
$tipoError = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'ingresarCustomSQL') {
    $sql = $_POST['customQuery'] ?? '';

    if (stripos($sql, 'insert') !== false || stripos($sql, 'delete') !== false || stripos($sql, 'update') !== false || stripos($sql, 'modify') !== false) {
        if (mysqli_query($conexion, $sql)) {
            $mensaje = "Query ejecutado correctamente, Registros afectados: " . mysqli_affected_rows($conexion);
            $tipoError = "success";
        } else {
            $mensaje = "No se pudo ejecutar el Query: " . mysqli_error($conexion);
            $tipoError = "error";
        }
    } elseif (empty($sql)) {
        $mensaje = "SQL Query esta vacio, pruebe a ingresar texto antes de intentar ingresar el Query";
        $tipoError = "error";
    } else {
        $mensaje = "SQL Query invalido. Los unicos Queries validos son: INSERT, DELETE, UPDATE, MODIFY";
        $tipoError = "error";
    }
}

$tablasDisponibles = [
    'activo' => ['id_activo', 'codigo_inventario', 'nombre', 'descripcion', 'marca', 'modelo', 'numero_serie', 'fecha_adquisicion', 'fecha_baja', 'id_categoria', 'id_estado', 'id_ubicacion', 'id_marca', 'id_usuario'],
    'activo_marca' => ['id_marca', 'marca'],
    'categoria_activo' => ['id_categoria', 'categoria'],
    'categoria_ticket' => ['id_categoria', 'categoria'],
    'diagnostico' => ['id_diagnostico', 'descripcion', 'fecha', 'id_ticket', 'id_tecnico'],
    'estado_activo' => ['id_estado', 'estado'],
    'estado_solicitud' => ['id_estado', 'estado'],
    'estado_ticket' => ['id_estado', 'estado'],
    'historial_ticket' => ['id_historial', 'id_ticket', 'estado_anterior', 'estado_nuevo', 'fecha'],
    'mensaje_ticket' => ['id_mensaje', 'id_ticket', 'id_usuario', 'contenido', 'fecha_creacion'],
    'prioridad_ticket' => ['id_prioridad', 'prioridad'],
    'resultado_ticket' => ['id_resultado', 'solucion', 'fecha_resolucion', 'id_ticket', 'id_tecnico'],
    'rol' => ['id_rol', 'nombre_rol'],
    'solicitud_servicio' => ['id_solicitud', 'titulo', 'descripcion', 'fecha_creacion', 'aprobacion', 'id_estado', 'id_solicitante', 'id_activo'],
    'ticket' => ['id_ticket', 'titulo', 'descripcion', 'fecha_creacion', 'id_estado', 'id_prioridad', 'id_solicitante', 'id_tecnico', 'id_activo', 'id_categoria'],
    'ubicacion' => ['id_ubicacion', 'calle', 'id_ciudad'],
    'ciudad' => ['id_ciudad', 'ciudad', 'departamento'],
    'usuario' => ['id_usuario', 'nombre', 'apellido', 'email', 'password', 'activo', 'id_rol', 'id_ubicacion']
];

$tablaSeleccionada = $_GET['tabla'] ?? '';
$columnasTablaSeleccionada = $tablasDisponibles[$tablaSeleccionada] ?? [];
$resultadoTablaSeleccionada = null;

if ($columnasTablaSeleccionada) {
    $clavePrimaria = $columnasTablaSeleccionada[0];
    $consultaTablaSeleccionada = "SELECT * FROM `$tablaSeleccionada` ORDER BY `$clavePrimaria` ASC";
    $resultadoTablaSeleccionada = mysqli_query($conexion, $consultaTablaSeleccionada);
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kinetix - Administración</title>
    <link rel="stylesheet" href="../css/panel-admin.css">
</head>

<body>
    <nav class="menu">
        <div class="menu-content">
            <div class="menu-logo">
                <img src="../imagenes/Logo1.png" alt="Logo Kinetix">
            </div>
            <ul>
                <li> <a href="inicio.php">Inicio</a> </li>
                <li> <a href="informes-admin.php">Informes sobre la pagina</a></li>
                <li> <a href="../php/logout.php">Cerrar sesion</a> </li>
            </ul>
        </div>
    </nav>

    <?php if ($mensaje) { ?>
    <div class="form-message <?= $tipoError ?>">
        <?= $mensaje ?>
    </div>
    <?php } ?>

    <section class="section--top">
        <div class="banner--overlay">
            <h1>Panel de Administración</h1>
            <p> Gestioná las tablas y registros de Kinetix </p>
        </div>
    </section>
    <main class="contenedor">
        <div class="admin-panel">
            <div class="admin-header">
                <h2>Administración de tablas </h2>
                <p>Consultá y gestioná la información registrada en Kinetix.</p>
                <form method="POST">
                    <textarea class="customQuery" name="customQuery"></textarea>
                    <input type="hidden" name="accion" value="ingresarCustomSQL"> 
                    <button class="btn-ingresarQuery" type="submit"> Ingresar Custom SQL Query</button>
                </form>
            </div>

            <section class="selector-tabla tabla-container">
                <h2>Seleccionar tabla</h2>
                <form method="GET" class="form-selector-tabla">
                    <label for="tabla">Elegí la tabla que querés administrar</label>
                    <select name="tabla" id="tabla" required>
                        <option value="">Seleccionar tabla</option>
                        <?php foreach ($tablasDisponibles as $nombreTabla => $columnas) { ?>
                            <option value="<?= $nombreTabla ?>" <?= $tablaSeleccionada === $nombreTabla ? 'selected' : '' ?>>
                                <?= $nombreTabla ?>
                            </option>
                        <?php } ?>
                    </select>
                    <button type="submit" class="btn-editar">Seleccionar</button>
                </form>
            </section>

            <?php if ($tablaSeleccionada && $columnasTablaSeleccionada) { ?>
                <section class="tabla-seleccionada tabla-container">
                    <div class="tabla-seleccionada-header">
                        <h2><?= $tablaSeleccionada ?></h2>
                        <form action="../php/ingresarRegistro.php" method="GET">
                            <input type="hidden" name="tipo" value="insert">
                            <input type="hidden" name="tabla" value="<?= $tablaSeleccionada ?>">
                            <button type="submit" class="btn-editar">Ingresar nuevo registro</button>
                        </form>
                    </div>
                    <div class="tabla-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <?php foreach ($columnasTablaSeleccionada as $columna) { ?>
                                        <th><?= $columna ?></th>
                                    <?php } ?>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($reg = mysqli_fetch_assoc($resultadoTablaSeleccionada)) { ?>
                                    <tr>
                                        <?php foreach ($columnasTablaSeleccionada as $columna) { ?>
                                            <td><?= $reg[$columna] ?? '' ?></td>
                                        <?php } ?>
                                        <td class="acciones">
                                            <form action="../php/editarRegistro.php" method="POST" style="display:inline;">
                                                <input type="hidden" name="tabla" value="<?= $tablaSeleccionada ?>">
                                                <input type="hidden" name="id" value="<?= $reg[$columnasTablaSeleccionada[0]] ?>">
                                                <button type="submit" class="btn-editar">Editar</button>
                                            </form>
                                            <form action="../php/eliminarRegistro.php" method="POST" style="display:inline;">
                                                <input type="hidden" name="tabla" value="<?= $tablaSeleccionada ?>">
                                                <input type="hidden" name="id" value="<?= $reg[$columnasTablaSeleccionada[0]] ?>">
                                                <button type="submit" class="btn-eliminar">Eliminar</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            <?php } elseif ($tablaSeleccionada) { ?>
                <div class="form-message error">La tabla seleccionada no es válida.</div>
            <?php } ?>
        </div>
    </main>

    <footer class="footer">
        <p>© 2026 Kinetix. Todos los derechos reservados. Panel exclusivo para administradores.</p>
    </footer>
</body>
</html>