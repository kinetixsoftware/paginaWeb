<?php 
session_start();

if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 3) {
    $_SESSION['mensaje'] = "Usted no tiene permiso para ver esta pagina";
    $_SESSION['tipoError'] = "error";
    header("Location: inicio.php");
  exit;
}

$mensaje = "";
$tipoError = "";

$servername = "localhost";
$username = "root";
$passwordbd = "";
$dbname = "kinetixsoftware";

$conexion = mysqli_connect($servername, $username, $passwordbd, $dbname);

if (!$conexion) {
    die("Connection failed: " . mysqli_connect_error());
}    

if($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] === 'ingresarCustomSQL') ) {
    $sql = $_POST['customQuery'] ?? '';

    if (stripos($sql, "insert") || stripos($sql, "delete") || stripos($sql, "update") || stripos($sql, "modify")) {
       $ingresarSQL = mysqli_query($conexion, $sql); 
       $ingresarSQLFetch = mysqli_fetch_assoc($ingresarSQL);

       if($ingresarSQLFetch) {
            $mensaje = "Query ejecutado correctamente, Registros afectados: " . mysqli_affected_rows($sql);
            $tipoError = "success";
       } else {
            $_SESSION['mensaje'] = "Usted no tiene permiso para ver esta pagina";
            $tipoError = "error";
       }
    } else if (empty($sql)) {
        $mensaje = "SQL Query esta vacio, pruebe a ingresar texto antes de intentar ingresar el Query";
        $tipoError = "error";
    } else {
        $mensaje = "SQL Query invalido. Los unicos Queries validos son: INSERT, DELETE, UPDATE, MODIFY";
        $tipoError = "error";
    }
}
$activo = "SELECT * FROM activo ORDER BY id_activo ASC";
$resultadoactivo = mysqli_query($conexion, $activo);

$activo_marca = "SELECT * FROM activo_marca ORDER BY id_marca ASC";
$resultadoactivo_marca = mysqli_query($conexion, $activo_marca);

$categorias = "SELECT * FROM categoria_activo ORDER BY id_categoria ASC";
$resultadocategorias_activo = mysqli_query($conexion, $categorias);

$estados = "SELECT * FROM estado_activo ORDER BY id_estado ASC";
$resultadoestados = mysqli_query($conexion, $estados);

$categorias_ticket = "SELECT * FROM categoria_ticket ORDER BY id_categoria ASC";
$resultadocategorias_ticket = mysqli_query($conexion, $categorias_ticket);

$ciudades = "SELECT * FROM ciudad ORDER BY id_ciudad ASC";
$resultadociudades = mysqli_query($conexion, $ciudades);

$departamentos = "SELECT * FROM departamento ORDER BY id_departamento ASC";
$resultadodepartamentos = mysqli_query($conexion, $departamentos);

$diagnosticos = "SELECT * FROM diagnostico ORDER BY id_diagnostico ASC";
$resultadodiagnosticos = mysqli_query($conexion, $diagnosticos);

$estados_solicitud = "SELECT * FROM estado_solicitud ORDER BY id_estado ASC";
$resultadoestados_solicitud = mysqli_query($conexion, $estados_solicitud);

$estados_ticket = "SELECT * FROM estado_ticket ORDER BY id_estado ASC";
$resultadoestados_ticket = mysqli_query($conexion, $estados_ticket);

$historiales_ticket = "SELECT * FROM historial_ticket ORDER BY id_historial ASC";
$resultadohistoriales_ticket = mysqli_query($conexion, $historiales_ticket);

$mensajes_ticket = "SELECT * FROM mensaje_ticket ORDER BY id_mensaje ASC";
$resultadomensajes_ticket = mysqli_query($conexion, $mensajes_ticket);

$prioridades_ticket = "SELECT * FROM prioridad_ticket ORDER BY id_prioridad ASC";
$resultadoprioridades_ticket = mysqli_query($conexion, $prioridades_ticket);

$resultados_ticket = "SELECT * FROM resultado_ticket ORDER BY id_resultado ASC";
$resultadoresultados_ticket = mysqli_query($conexion, $resultados_ticket);

$roles = "SELECT * FROM rol ORDER BY id_rol ASC";
$resultadoroles = mysqli_query($conexion, $roles);

$solicitudes_servicio = "SELECT * FROM solicitud_servicio ORDER BY id_solicitud ASC";
$resultadosolicitudes_servicio = mysqli_query($conexion, $solicitudes_servicio);

$tickets = "SELECT * FROM ticket ORDER BY id_ticket ASC";
$resultadotickets = mysqli_query($conexion, $tickets);

$ubicaciones = "SELECT * FROM ubicacion ORDER BY id_ubicacion ASC";
$resultadoubicaciones = mysqli_query($conexion, $ubicaciones);

$usuarios = "SELECT * FROM usuario ORDER BY id_usuario ASC";
$resultadousuarios = mysqli_query($conexion, $usuarios);

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

            <div class="buscador">
                <input type="search" placeholder="Buscar una tabla por nombre" id="busqueda">
                <button id="btnBuscar">Buscar</button>
            </div>

            <div class="tablas-lista">
                <section class="tabla-container">
                    <h2> Activos 
                        <form action="../php/ingresarRegistro.php" method="GET" style="display:inline;">
                            <button type="submit" class="btn-editar"> Ingresar Nuevo Activo </button>
                            <input type="hidden" name="tipo" value="insert">
                            <input type="hidden" name="tabla" value="activo">
                        </form> 
                    </h2>
                    <div class="tabla-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID activo</th>
                                    <th>Código inventario</th>
                                    <th>Nombre</th>
                                    <th>Descripción</th>
                                    <th>Número de serie</th>
                                    <th>modelo</th>
                                    <th>Fecha adquisición</th>
                                    <th>Fecha baja</th>
                                    <th>ID categoría</th>
                                    <th>ID estado</th>
                                    <th>ID ubicación</th>
                                    <th>ID marca</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($reg = mysqli_fetch_array($resultadoactivo)){?>
                                <tr>
                                    <td><?= $reg['id_activo'] ?></td>
                                    <td><?= $reg['codigo_inventario']?></td>
                                    <td><?= $reg['nombre']?></td>
                                    <td><?= $reg['descripcion']?></td>
                                    <td><?= $reg['numero_serie']?></td>
                                    <td><?= $reg['modelo']?></td>
                                    <td><?= $reg['fecha_adquisicion']?></td>
                                    <td><?= $reg['fecha_baja']?></td>
                                    <td><?= $reg['id_categoria']?></td>
                                    <td><?= $reg['id_estado']?></td>
                                    <td><?= $reg['id_ubicacion']?></td>
                                    <td><?= $reg['id_marca']?></td>
                                    <td class="acciones">
                                        <button class="btn-editar">Editar</button>
                                        <form action="../php/eliminarRegistro.php" method="POST"
                                            style="display:inline;">
                                            <input type="hidden" name="tabla" value="activo">
                                            <input type="hidden" name="id" value="<?= $reg['id_activo'] ?>">
                                            <button type="submit" class="btn-eliminar">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php }?>
                            </tbody>
                        </table>
                    </div>
                </section>
                <section class="tabla-container">
                    <h2>Marcas de activos
                        <form action="../php/ingresarRegistro.php" method="GET" style="display:inline;">
                            <button type="submit" class="btn-editar"> Ingresar Nueva Marca </button>
                            <input type="hidden" name="tipo" value="insert">
                            <input type="hidden" name="tabla" value="activo_marca">
                        </form> 
                    </h2>
                    <div class="tabla-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID marca</th>
                                    <th>Marca</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($reg = mysqli_fetch_array($resultadoactivo_marca)){?>
                                <tr>
                                    <td><?= $reg['id_marca'] ?></td>
                                    <td><?= $reg['marca'] ?></td>
                                    <td class="acciones">
                                        <form action="../php/editarRegistro.php" method="POST" style="display:inline;">
                                            <input type="hidden" name="tabla" value="activo_marca">
                                            <input type="hidden" name="id" value="<?= $reg['id_marca'] ?>">
                                            <button type="submit" class="btn-editar">Editar</button>
                                        </form>
                                        <form action="../php/eliminarRegistro.php" method="POST" style="display:inline;">
                                            <input type="hidden" name="tabla" value="activo_marca">
                                            <input type="hidden" name="id" value="<?= $reg['id_marca'] ?>">
                                            <button type="submit" class="btn-eliminar">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php }?>
                            </tbody>
                        </table>
                    </div>
                </section>
                <section class="tabla-container">
                    <h2>Categorías de activos
                        <form action="../php/ingresarRegistro.php" method="GET" style="display:inline;">
                            <button type="submit" class="btn-editar"> Ingresar Nueva Categoria </button>
                            <input type="hidden" name="tipo" value="insert">
                            <input type="hidden" name="tabla" value="categoria_activo">
                        </form> 
                    </h2>
                    <div class="tabla-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID categoría</th>
                                    <th>Categoría</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($reg = mysqli_fetch_array($resultadocategorias_activo)){?>
                                <tr>
                                    <td><?= $reg['id_categoria'] ?></td>
                                    <td><?= $reg['categoria'] ?></td>
                                    <td class="acciones">
                                        <form action="../php/editarRegistro.php" method="POST" style="display:inline;">
                                            <input type="hidden" name="tabla" value="categoria_activo">
                                            <input type="hidden" name="id" value="<?= $reg['id_categoria'] ?>">
                                            <button type="submit" class="btn-editar">Editar</button>
                                        </form>
                                        <form action="../php/eliminarRegistro.php" method="POST"
                                            style="display:inline;">
                                            <input type="hidden" name="tabla" value="categoria_activo">
                                            <input type="hidden" name="id" value="<?= $reg['id_categoria'] ?>">
                                            <button type="submit" class="btn-eliminar">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php }?>
                            </tbody>
                        </table>
                    </div>
                </section>
                <section class="tabla-container">
                    <h2>Estados de activos
                        <form action="../php/ingresarRegistro.php" method="GET" style="display:inline;">
                            <button type="submit" class="btn-editar"> Ingresar Nuevo Estado </button>
                            <input type="hidden" name="tipo" value="insert">
                            <input type="hidden" name="tabla" value="estado_activo">
                        </form> 
                    </h2>
                    <div class="tabla-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID estado</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($reg = mysqli_fetch_array($resultadoestados)){?>
                                <tr>
                                    <td><?= $reg['id_estado'] ?></td>
                                    <td><?= $reg['estado'] ?></td>
                                    <td class="acciones">
                                        <form action="../php/editarRegistro.php" method="POST" style="display:inline;">
                                            <input type="hidden" name="tabla" value="estado_activo">
                                            <input type="hidden" name="id" value="<?= $reg['id_estado'] ?>">
                                            <button type="submit" class="btn-editar">Editar</button>
                                        </form>
                                        <form action="../php/eliminarRegistro.php" method="POST"
                                            style="display:inline;">
                                            <input type="hidden" name="tabla" value="estado_activo">
                                            <input type="hidden" name="id" value="<?= $reg['id_estado'] ?>">
                                            <button type="submit" class="btn-eliminar">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php }?>
                            </tbody>
                        </table>
                    </div>
                </section>
                <section class="tabla-container">
                    <h2>Tickets</h2>
                    <div class="tabla-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID ticket</th>
                                    <th>Título</th>
                                    <th>Descripción</th>
                                    <th>Fecha creación</th>
                                    <th>ID estado</th>
                                    <th>ID prioridad</th>
                                    <th>ID solicitante</th>
                                    <th>ID técnico</th>
                                    <th>ID activo</th>
                                    <th>ID categoría</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($reg = mysqli_fetch_array($resultadotickets)){?>
                                <tr>
                                    <td><?= $reg['id_ticket'] ?></td>
                                    <td><?= $reg['titulo'] ?></td>
                                    <td><?= $reg['descripcion'] ?></td>
                                    <td><?= $reg['fecha_creacion'] ?></td>
                                    <td><?= $reg['id_estado'] ?></td>
                                    <td><?= $reg['id_prioridad'] ?></td>
                                    <td><?= $reg['id_solicitante'] ?></td>
                                    <td><?= $reg['id_tecnico'] ?></td>
                                    <td><?= $reg['id_activo'] ?></td>
                                    <td><?= $reg['id_categoria'] ?></td>
                                    <td class="acciones">
                                        <form action="../php/editarRegistro.php" method="POST" style="display:inline;">
                                            <input type="hidden" name="tabla" value="ticket">
                                            <input type="hidden" name="id" value="<?= $reg['id_ticket'] ?>">
                                            <button type="submit" class="btn-editar">Editar</button>
                                        </form>
                                        <form action="../php/eliminarRegistro.php" method="POST"
                                            style="display:inline;">
                                            <input type="hidden" name="tabla" value="ticket">
                                            <input type="hidden" name="id" value="<?= $reg['id_ticket'] ?>">
                                            <button type="submit" class="btn-eliminar">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php }?>
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="tabla-container">
                    <h2>Categorías de tickets
                        <form action="../php/ingresarRegistro.php" method="GET" style="display:inline;">
                            <button type="submit" class="btn-editar"> Ingresar Categoria </button>
                            <input type="hidden" name="tipo" value="insert">
                            <input type="hidden" name="tabla" value="categoria_ticket">
                        </form> 
                    </h2>
                    <div class="tabla-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID categoría</th>
                                    <th>Categoría</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($reg = mysqli_fetch_array($resultadocategorias_ticket)){?>
                                <tr>
                                    <td><?= $reg['id_categoria'] ?></td>
                                    <td><?= $reg['categoria'] ?></td>
                                    <td class="acciones">
                                        <form action="../php/editarRegistro.php" method="POST" style="display:inline;">
                                            <input type="hidden" name="tabla" value="categoria_ticket">
                                            <input type="hidden" name="id" value="<?= $reg['id_categoria'] ?>">
                                            <button type="submit" class="btn-editar">Editar</button>
                                        </form>
                                        <form action="../php/eliminarRegistro.php" method="POST"
                                            style="display:inline;">
                                            <input type="hidden" name="tabla" value="categoria_ticket">
                                            <input type="hidden" name="id" value="<?= $reg['id_categoria'] ?>">
                                            <button type="submit" class="btn-eliminar">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php }?>
                            </tbody>
                        </table>
                    </div>
                </section>
                <section class="tabla-container">
                    <h2>Ciudades
                        <form action="../php/ingresarRegistro.php" method="GET" style="display:inline;">
                            <button type="submit" class="btn-editar"> Ingresar Categoria </button>
                            <input type="hidden" name="tipo" value="insert">
                            <input type="hidden" name="tabla" value="ciudad">
                        </form> 
                    </h2>
                    <div class="tabla-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID ciudad</th>
                                    <th>Ciudad</th>
                                    <th>ID departamento</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($reg = mysqli_fetch_array($resultadociudades)){?>
                                <tr>
                                    <td><?= $reg['id_ciudad'] ?></td>
                                    <td><?= $reg['ciudad'] ?></td>
                                    <td><?= $reg['id_departamento'] ?></td>
                                    <td class="acciones">
                                        <form action="../php/editarRegistro.php" method="POST" style="display:inline;">
                                            <input type="hidden" name="tabla" value="ciudad">
                                            <input type="hidden" name="id" value="<?= $reg['id_ciudad'] ?>">
                                            <button type="submit" class="btn-editar">Editar</button>
                                        </form>
                                        <form action="../php/eliminarRegistro.php" method="POST"
                                            style="display:inline;">
                                            <input type="hidden" name="tabla" value="ciudad">
                                            <input type="hidden" name="id" value="<?= $reg['id_ciudad'] ?>">
                                            <button type="submit" class="btn-eliminar">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php }?>
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="tabla-container">
                    <h2>Departamentos</h2>
                    <div class="tabla-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID departamento</th>
                                    <th>Departamento</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($reg = mysqli_fetch_array($resultadodepartamentos)){?>
                                <tr>
                                    <td><?= $reg['id_departamento'] ?></td>
                                    <td><?= $reg['departamento'] ?></td>
                                </tr>
                                <?php }?>
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="tabla-container">
                    <h2>Diagnósticos</h2>
                    <div class="tabla-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID diagnóstico</th>
                                    <th>Descripción</th>
                                    <th>Fecha</th>
                                    <th>ID ticket</th>
                                    <th>ID técnico</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($reg = mysqli_fetch_array($resultadodiagnosticos)){?>
                                <tr>
                                    <td><?= $reg['id_diagnostico'] ?></td>
                                    <td><?= $reg['descripcion'] ?></td>
                                    <td><?= $reg['fecha'] ?></td>
                                    <td><?= $reg['id_ticket'] ?></td>
                                    <td><?= $reg['id_tecnico'] ?></td>
                                    <td class="acciones">
                                        <form action="../php/editarRegistro.php" method="POST" style="display:inline;">
                                            <input type="hidden" name="tabla" value="diagnostico">
                                            <input type="hidden" name="id" value="<?= $reg['id_diagnostico'] ?>">
                                            <button type="submit" class="btn-editar">Editar</button>
                                        </form>
                                        <form action="../php/eliminarRegistro.php" method="POST"
                                            style="display:inline;">
                                            <input type="hidden" name="tabla" value="diagnostico">
                                            <input type="hidden" name="id" value="<?= $reg['id_diagnostico'] ?>">
                                            <button type="submit" class="btn-eliminar">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php }?>
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="tabla-container">
                    <h2>Estados de solicitudes
                        <form action="../php/ingresarRegistro.php" method="GET" style="display:inline;">
                            <button type="submit" class="btn-editar"> Ingresar Nuevo Estado </button>
                            <input type="hidden" name="tipo" value="insert">
                            <input type="hidden" name="tabla" value="estado_solicitud">
                        </form>
                    </h2>
                    <div class="tabla-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID estado</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($reg = mysqli_fetch_array($resultadoestados_solicitud)){?>
                                <tr>
                                    <td><?= $reg['id_estado'] ?></td>
                                    <td><?= $reg['estado'] ?></td>
                                    <td class="acciones">
                                        <form action="../php/editarRegistro.php" method="POST" style="display:inline;">
                                            <input type="hidden" name="tabla" value="estado_solicitud">
                                            <input type="hidden" name="id" value="<?= $reg['id_estado'] ?>">
                                            <button type="submit" class="btn-editar">Editar</button>
                                        </form>
                                        <form action="../php/eliminarRegistro.php" method="POST"
                                            style="display:inline;">
                                            <input type="hidden" name="tabla" value="estado_solicitud">
                                            <input type="hidden" name="id" value="<?= $reg['id_estado'] ?>">
                                            <button type="submit" class="btn-eliminar">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php }?>
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="tabla-container">
                    <h2>Estados de tickets
                        <form action="../php/ingresarRegistro.php" method="GET" style="display:inline;">
                            <button type="submit" class="btn-editar"> Ingresar Nuevo Estado </button>
                            <input type="hidden" name="tipo" value="insert">
                            <input type="hidden" name="tabla" value="estado_ticket">
                        </form>
                    </h2>
                    <div class="tabla-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID estado</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($reg = mysqli_fetch_array($resultadoestados_ticket)){?>
                                <tr>
                                    <td><?= $reg['id_estado'] ?></td>
                                    <td><?= $reg['estado'] ?></td>
                                    <td class="acciones">
                                        <form action="../php/editarRegistro.php" method="POST" style="display:inline;">
                                            <input type="hidden" name="tabla" value="estado_ticket">
                                            <input type="hidden" name="id" value="<?= $reg['id_estado'] ?>">
                                            <button type="submit" class="btn-editar">Editar</button>
                                        </form>
                                        <form action="../php/eliminarRegistro.php" method="POST"
                                            style="display:inline;">
                                            <input type="hidden" name="tabla" value="estado_ticket">
                                            <input type="hidden" name="id" value="<?= $reg['id_estado'] ?>">
                                            <button type="submit" class="btn-eliminar">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php }?>
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="tabla-container">
                    <h2>Historial de tickets</h2>
                    <div class="tabla-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID historial</th>
                                    <th>ID ticket</th>
                                    <th>ID usuario</th>
                                    <th>Estado anterior</th>
                                    <th>Estado nuevo</th>
                                    <th>Fecha</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($reg = mysqli_fetch_array($resultadohistoriales_ticket)){?>
                                <tr>
                                    <td><?= $reg['id_historial'] ?></td>
                                    <td><?= $reg['id_ticket'] ?></td>
                                    <td><?= $reg['id_usuario'] ?></td>
                                    <td><?= $reg['estado_anterior'] ?></td>
                                    <td><?= $reg['estado_nuevo'] ?></td>
                                    <td><?= $reg['fecha'] ?></td>
                                    <td class="acciones">
                                        <form action="../php/editarRegistro.php" method="POST" style="display:inline;">
                                            <input type="hidden" name="tabla" value="historial_ticket">
                                            <input type="hidden" name="id" value="<?= $reg['id_historial'] ?>">
                                            <button type="submit" class="btn-editar">Editar</button>
                                        </form>
                                        <form action="../php/eliminarRegistro.php" method="POST"
                                            style="display:inline;">
                                            <input type="hidden" name="tabla" value="historial_ticket">
                                            <input type="hidden" name="id" value="<?= $reg['id_historial'] ?>">
                                            <button type="submit" class="btn-eliminar">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php }?>
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="tabla-container">
                    <h2>Mensajes de tickets</h2>
                    <div class="tabla-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID mensaje</th>
                                    <th>ID ticket</th>
                                    <th>ID usuario</th>
                                    <th>Contenido</th>
                                    <th>Imagen</th>
                                    <th>Fecha creación</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($reg = mysqli_fetch_array($resultadomensajes_ticket)){?>
                                <tr>
                                    <td><?= $reg['id_mensaje'] ?></td>
                                    <td><?= $reg['id_ticket'] ?></td>
                                    <td><?= $reg['id_usuario'] ?></td>
                                    <td><?= $reg['contenido'] ?></td>
                                    <td><?= $reg['imagen_path'] ?></td>
                                    <td><?= $reg['fecha_creacion'] ?></td>
                                    <td class="acciones">
                                        <form action="../php/editarRegistro.php" method="POST" style="display:inline;">
                                            <input type="hidden" name="tabla" value="mensaje_ticket">
                                            <input type="hidden" name="id" value="<?= $reg['id_mensaje'] ?>">
                                            <button type="submit" class="btn-editar">Editar</button>
                                        </form>
                                        <form action="../php/eliminarRegistro.php" method="POST"
                                            style="display:inline;">
                                            <input type="hidden" name="tabla" value="mensaje_ticket">
                                            <input type="hidden" name="id" value="<?= $reg['id_mensaje'] ?>">
                                            <button type="submit" class="btn-eliminar">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php }?>
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="tabla-container">
                    <h2>Prioridades de tickets
                        <form action="../php/ingresarRegistro.php" method="GET" style="display:inline;">
                            <button type="submit" class="btn-editar"> Ingresar Nueva Prioridad </button>
                            <input type="hidden" name="tipo" value="insert">
                            <input type="hidden" name="tabla" value="prioridad_ticket">
                        </form>
                    </h2>
                    <div class="tabla-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID prioridad</th>
                                    <th>Prioridad</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($reg = mysqli_fetch_array($resultadoprioridades_ticket)){?>
                                <tr>
                                    <td><?= $reg['id_prioridad'] ?></td>
                                    <td><?= $reg['prioridad'] ?></td>
                                    <td class="acciones">
                                        <form action="../php/editarRegistro.php" method="POST" style="display:inline;">
                                            <input type="hidden" name="tabla" value="prioridad_ticket">
                                            <input type="hidden" name="id" value="<?= $reg['id_prioridad'] ?>">
                                            <button type="submit" class="btn-editar">Editar</button>
                                        </form>
                                        <form action="../php/eliminarRegistro.php" method="POST"
                                            style="display:inline;">
                                            <input type="hidden" name="tabla" value="prioridad_ticket">
                                            <input type="hidden" name="id" value="<?= $reg['id_prioridad'] ?>">
                                            <button type="submit" class="btn-eliminar">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php }?>
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="tabla-container">
                    <h2>Resultados de tickets</h2>
                    <div class="tabla-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID resultado</th>
                                    <th>Solución</th>
                                    <th>Fecha resolución</th>
                                    <th>ID ticket</th>
                                    <th>ID técnico</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($reg = mysqli_fetch_array($resultadoresultados_ticket)){?>
                                <tr>
                                    <td><?= $reg['id_resultado'] ?></td>
                                    <td><?= $reg['solucion'] ?></td>
                                    <td><?= $reg['fecha_resolucion'] ?></td>
                                    <td><?= $reg['id_ticket'] ?></td>
                                    <td><?= $reg['id_tecnico'] ?></td>
                                    <td class="acciones">
                                        <form action="../php/editarRegistro.php" method="POST" style="display:inline;">
                                            <input type="hidden" name="tabla" value="resultado_ticket">
                                            <input type="hidden" name="id" value="<?= $reg['id_resultado'] ?>">
                                            <button type="submit" class="btn-editar">Editar</button>
                                        </form>
                                        <form action="../php/eliminarRegistro.php" method="POST"
                                            style="display:inline;">
                                            <input type="hidden" name="tabla" value="resultado_ticket">
                                            <input type="hidden" name="id" value="<?= $reg['id_resultado'] ?>">
                                            <button type="submit" class="btn-eliminar">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php }?>
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="tabla-container">
                    <h2>Roles</h2>
                    <div class="tabla-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID rol</th>
                                    <th>Nombre del rol</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($reg = mysqli_fetch_array($resultadoroles)){?>
                                <tr>
                                    <td><?= $reg['id_rol'] ?></td>
                                    <td><?= $reg['nombre_rol'] ?></td>
                                    <td class="acciones">
                                        <form action="../php/editarRegistro.php" method="POST" style="display:inline;">
                                            <input type="hidden" name="tabla" value="rol">
                                            <input type="hidden" name="id" value="<?= $reg['id_rol'] ?>">
                                            <button type="submit" class="btn-editar">Editar</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php }?>
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="tabla-container">
                    <h2>Solicitudes de servicio</h2>
                    <div class="tabla-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID solicitud</th>
                                    <th>Título</th>
                                    <th>Descripción</th>
                                    <th>Fecha creación</th>
                                    <th>Aprobación</th>
                                    <th>ID estado</th>
                                    <th>ID solicitante</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($reg = mysqli_fetch_array($resultadosolicitudes_servicio)){?>
                                <tr>
                                    <td><?= $reg['id_solicitud'] ?></td>
                                    <td><?= $reg['titulo'] ?></td>
                                    <td><?= $reg['descripcion'] ?></td>
                                    <td><?= $reg['fecha_creacion'] ?></td>
                                    <td><?= $reg['aprobacion'] ?></td>
                                    <td><?= $reg['id_estado'] ?></td>
                                    <td><?= $reg['id_solicitante'] ?></td>
                                    <td class="acciones">
                                        <form action="../php/editarRegistro.php" method="POST" style="display:inline;">
                                            <input type="hidden" name="tabla" value="solicitud_servicio">
                                            <input type="hidden" name="id" value="<?= $reg['id_solicitud'] ?>">
                                            <button type="submit" class="btn-editar">Editar</button>
                                        </form>
                                        <form action="../php/eliminarRegistro.php" method="POST"
                                            style="display:inline;">
                                            <input type="hidden" name="tabla" value="solicitud_servicio">
                                            <input type="hidden" name="id" value="<?= $reg['id_solicitud'] ?>">
                                            <button type="submit" class="btn-eliminar">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php }?>
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="tabla-container">
                    <h2>Ubicaciones</h2>
                    <div class="tabla-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID ubicación</th>
                                    <th>Calle</th>
                                    <th>ID ciudad</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($reg = mysqli_fetch_array($resultadoubicaciones)){?>
                                <tr>
                                    <td><?= $reg['id_ubicacion'] ?></td>
                                    <td><?= $reg['calle'] ?></td>
                                    <td><?= $reg['id_ciudad'] ?></td>
                                    <td class="acciones">
                                        <form action="../php/editarRegistro.php" method="POST" style="display:inline;">
                                            <input type="hidden" name="tabla" value="ubicacion">
                                            <input type="hidden" name="id" value="<?= $reg['id_ubicacion'] ?>">
                                            <button type="submit" class="btn-editar">Editar</button>
                                        </form>
                                        <form action="../php/eliminarRegistro.php" method="POST"
                                            style="display:inline;">
                                            <input type="hidden" name="tabla" value="ubicacion">
                                            <input type="hidden" name="id" value="<?= $reg['id_ubicacion'] ?>">
                                            <button type="submit" class="btn-eliminar">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php }?>
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="tabla-container">
                    <h2>Usuarios
                        <form action="../php/ingresarRegistro.php" method="GET" style="display:inline;">
                            <button type="submit" class="btn-editar"> Ingresar Nuevo Usuario </button>
                            <input type="hidden" name="tipo" value="insert">
                            <input type="hidden" name="tabla" value="usuario">
                        </form>
                    </h2>
                    <div class="tabla-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Email</th>
                                    <th>Nombre</th>
                                    <th>Apellido</th>
                                    <th>Activo</th>
                                    <th>ID rol</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($reg = mysqli_fetch_array($resultadousuarios)){?>
                                <tr>
                                    <td><?= $reg['id_usuario'] ?></td>
                                    <td><?= $reg['email'] ?></td>
                                    <td><?= $reg['nombre'] ?></td>
                                    <td><?= $reg['apellido'] ?></td>
                                    <td><?= $reg['activo'] ?></td>
                                    <td><?= $reg['id_rol'] ?></td>
                                    <td class="acciones">
                                        <form action="../php/editarRegistro.php" method="POST" style="display:inline;">
                                            <input type="hidden" name="tabla" value="usuario">
                                            <input type="hidden" name="id" value="<?= $reg['id_usuario'] ?>">
                                            <button type="submit" class="btn-editar">Editar</button>
                                        </form>
                                        <form action="../php/eliminarRegistro.php" method="POST"
                                            style="display:inline;">
                                            <input type="hidden" name="tabla" value="usuario">
                                            <input type="hidden" name="id" value="<?= $reg['id_usuario'] ?>">
                                            <button type="submit" class="btn-eliminar">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php }?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </div>
    </main>

    <footer class="footer">

        <p>
            © 2026 Kinetix. Todos los derechos reservados.
            Panel exclusivo para administradores.
        </p>

    </footer>

    <script>
    const busqueda = document.getElementById("busqueda");
    const tablas = document.querySelectorAll(".tabla-container");

    tablas.forEach(function(tabla) {
        const buscadorRegistro = document.createElement("input");
        buscadorRegistro.type = "search";
        buscadorRegistro.className = "buscador-registro";
        buscadorRegistro.placeholder = "Buscar registro";

        tabla.querySelector("h2").after(buscadorRegistro);

        buscadorRegistro.addEventListener("input", function() {
            const termino = this.value.toLowerCase().trim();

            tabla.querySelectorAll("tbody tr").forEach(function(fila) {
                fila.style.display = fila.textContent.toLowerCase().includes(termino) ? "" :
                    "none";
            });
        });
    });

    function filtrarTablasPorNombre() {
        const termino = busqueda.value.toLowerCase().trim();

        tablas.forEach(function(tabla) {
            const nombreTabla = tabla.querySelector("h2").textContent.toLowerCase();
            tabla.style.display = nombreTabla.includes(termino) ? "" : "none";
        });
    }

    document.getElementById("btnBuscar").addEventListener("click", filtrarTablasPorNombre);
    busqueda.addEventListener("input", filtrarTablasPorNombre);
    </script>

</body>

</html>