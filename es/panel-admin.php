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

if (isset($_SESSION['admin_mensaje'])) {
    $mensaje = $_SESSION['admin_mensaje'];
    $tipoError = $_SESSION['error'] ?? 'success';
    unset($_SESSION['admin_mensaje'], $_SESSION['error']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && str_starts_with($_POST['accion'] ?? '', 'faq_')) {
    $accion = $_POST['accion'];
    $pregunta = $_POST['pregunta'] ?? '';
    $respuesta = $_POST['respuesta'] ?? '';
    $idPregunta = (int) ($_POST['id_pregunta'] ?? 0);
    $destino = 'panel-admin.php#preguntas-frecuentes';

    if ($accion === 'faq_eliminar') {
        if ($idPregunta <= 0) {
            $mensaje = 'La pregunta seleccionada no es válida.';
            $tipoError = 'error';
        } else {
            $consulta = "DELETE FROM pregunta_frecuente WHERE id_pregunta = $idPregunta";
            if (mysqli_query($conexion, $consulta)) {
                $afectadas = mysqli_affected_rows($conexion);
                $_SESSION['admin_mensaje'] = $afectadas > 0
                    ? 'Pregunta frecuente eliminada.'
                    : 'La pregunta ya no existe.';
                $_SESSION['error'] = $afectadas > 0 ? 'success' : 'error';
                header("Location: $destino");
                exit;
            } else {
                $mensaje = 'No se pudo eliminar la pregunta: ' . mysqli_error($conexion);
                $tipoError = 'error';
            }
        }
    } elseif ($pregunta === '' || $respuesta === '') {
        $mensaje = 'Completá tanto la pregunta como la respuesta.';
        $tipoError = 'error';
    } elseif ($accion === 'faq_guardar' && $idPregunta > 0) {
        $consulta = "UPDATE pregunta_frecuente
                     SET pregunta = '$pregunta', respuesta = '$respuesta'
                     WHERE id_pregunta = $idPregunta";
        if (mysqli_query($conexion, $consulta)) {
            $_SESSION['admin_mensaje'] = 'Pregunta frecuente actualizada.';
            $_SESSION['error'] = 'success';
            header("Location: $destino");
            exit;
        } else {
            $mensaje = 'No se pudo actualizar la pregunta: ' . mysqli_error($conexion);
            $tipoError = 'error';
        }
    } elseif ($accion === 'faq_crear') {
        $consulta = "INSERT INTO pregunta_frecuente (pregunta, respuesta)
                     VALUES ('$pregunta', '$respuesta')";
        if (mysqli_query($conexion, $consulta)) {
            $_SESSION['admin_mensaje'] = 'Pregunta frecuente creada.';
            $_SESSION['error'] = 'success';
            header("Location: $destino");
            exit;
        } else {
            $mensaje = 'No se pudo crear la pregunta: ' . mysqli_error($conexion);
            $tipoError = 'error';
        }
    } else {
        $mensaje = 'La acción solicitada no es válida.';
        $tipoError = 'error';
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
    'historial_activo' => ['id_historial', 'id_activo', 'fecha', 'accion'],
    'historial_ticket' => ['id_historial', 'id_ticket', 'estado_anterior', 'estado_nuevo', 'fecha'],
    'mensaje_ticket' => ['id_mensaje', 'id_ticket', 'id_usuario', 'contenido', 'fecha_creacion'],
    'prioridad_ticket' => ['id_prioridad', 'prioridad'],
    'pregunta_frecuente' => ['id_pregunta', 'pregunta', 'respuesta'],
    'resultado_ticket' => ['id_resultado', 'solucion', 'fecha_resolucion', 'id_ticket', 'id_tecnico'],
    'rol' => ['id_rol', 'nombre_rol'],
    'solicitud_servicio' => ['id_solicitud', 'descripcion', 'fecha_creacion', 'aprobacion', 'id_estado', 'id_solicitante', 'id_ubicacion', 'id_activo', 'prioridad', 'tipo_servicio'],
    'ticket' => ['id_ticket', 'titulo', 'descripcion', 'fecha_creacion', 'id_estado', 'id_prioridad', 'id_solicitante', 'id_tecnico', 'id_activo', 'id_categoria'],
    'ubicacion' => ['id_ubicacion', 'calle', 'id_ciudad'],
    'ciudad' => ['id_ciudad', 'ciudad', 'departamento'],
    'usuario' => ['id_usuario', 'nombre', 'apellido', 'email', 'password', 'activo', 'id_rol', 'id_ubicacion']
];

$gruposAdministracion = [
    'Soporte' => [
        'ticket' => 'Tickets',
        'solicitud_servicio' => 'Solicitudes de servicio',
        'mensaje_ticket' => 'Mensajes',
        'historial_activo' => 'Historial de activos',
        'historial_ticket' => 'Historial de tickets',
        'diagnostico' => 'Diagnósticos',
        'resultado_ticket' => 'Resultados',
        'estado_ticket' => 'Estados de tickets',
        'estado_solicitud' => 'Estados de solicitudes',
        'prioridad_ticket' => 'Prioridades',
        'categoria_ticket' => 'Categorías de tickets'
    ],
    'Inventario' => [
        'activo' => 'Activos',
        'activo_marca' => 'Marcas',
        'categoria_activo' => 'Categorías de activos',
        'estado_activo' => 'Estados de activos'
    ],
    'Personas y sedes' => [
        'usuario' => 'Usuarios',
        'rol' => 'Roles',
        'ubicacion' => 'Ubicaciones',
        'ciudad' => 'Ciudades'
    ],
    'Contenido' => [
        'pregunta_frecuente' => 'Preguntas frecuentes'
    ]
];

$estadisticas = [];
foreach ([
    'Tickets' => 'ticket',
    'Solicitudes' => 'solicitud_servicio',
    'Activos' => 'activo',
    'Usuarios' => 'usuario',
    'Sedes' => 'ubicacion',
    'Preguntas frecuentes' => 'pregunta_frecuente'
] as $etiqueta => $tablaContador) {
    $resultadoConteo = mysqli_query($conexion, "SELECT COUNT(*) AS total FROM `$tablaContador`");
    if (!$resultadoConteo) {
        throw new RuntimeException('No se pudo contar ' . $tablaContador . ': ' . mysqli_error($conexion));
    }
    $estadisticas[$etiqueta] = (int) mysqli_fetch_assoc($resultadoConteo)['total'];
}

$preguntasFrecuentes = [];
$resultadoFaq = mysqli_query($conexion, 'SELECT id_pregunta, pregunta, respuesta FROM pregunta_frecuente ORDER BY id_pregunta DESC');
if (!$resultadoFaq) {
    throw new RuntimeException('No se pudieron cargar las preguntas frecuentes: ' . mysqli_error($conexion));
}
while ($faq = mysqli_fetch_assoc($resultadoFaq)) {
    $preguntasFrecuentes[] = $faq;
}

$preguntaEnEdicion = null;
$idEdicionFaq = (int) ($_GET['editar_pregunta'] ?? 0);
if ($idEdicionFaq > 0) {
    $resultadoEdicionFaq = mysqli_query(
        $conexion,
        "SELECT id_pregunta, pregunta, respuesta FROM pregunta_frecuente WHERE id_pregunta = $idEdicionFaq LIMIT 1"
    );
    if (!$resultadoEdicionFaq) {
        throw new RuntimeException('No se pudo cargar la pregunta para editar: ' . mysqli_error($conexion));
    }
    $preguntaEnEdicion = mysqli_fetch_assoc($resultadoEdicionFaq) ?: null;
}

$tablaSeleccionada = $_GET['tabla'] ?? '';
$columnasTablaSeleccionada = $tablasDisponibles[$tablaSeleccionada] ?? [];
$resultadoTablaSeleccionada = null;

if ($columnasTablaSeleccionada) {
    $clavePrimaria = $columnasTablaSeleccionada[0];
    $consultaTablaSeleccionada = "SELECT * FROM `$tablaSeleccionada` ORDER BY `$clavePrimaria` ASC";
    $resultadoTablaSeleccionada = mysqli_query($conexion, $consultaTablaSeleccionada);
    if (!$resultadoTablaSeleccionada) {
        throw new RuntimeException('No se pudo consultar la tabla seleccionada: ' . mysqli_error($conexion));
    }
}

$esc = static fn($valor): string => htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kinetix - Administración</title>
    <link rel="stylesheet" href="../css/panel-admin.css">
    <link rel="stylesheet" href="../css/kinetix-theme.css">
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

    <main class="admin-dashboard">
        <header class="admin-hero">
            <div>
                <p class="admin-eyebrow">KINETIX · ADMINISTRACIÓN</p>
                <h1>Centro de administración</h1>
                <p>Gestioná soporte, inventario, personas, sedes y el contenido de ayuda desde un solo lugar.</p>
            </div>
            <a class="admin-report-link" href="informes-admin.php">Ver informes <span aria-hidden="true">→</span></a>
        </header>

        <?php if ($mensaje) { ?>
            <div class="form-message <?= $esc($tipoError) ?>" role="status"><?= $esc($mensaje) ?></div>
        <?php } ?>

        <section class="admin-stats" aria-label="Resumen del sistema">
            <?php foreach ($estadisticas as $etiqueta => $total) { ?>
                <article class="admin-stat">
                    <span><?= $esc($etiqueta) ?></span>
                    <strong><?= $total ?></strong>
                </article>
            <?php } ?>
        </section>

        <section class="admin-section" aria-labelledby="accesos-title">
            <div class="admin-section-heading">
                <div>
                    <p class="admin-eyebrow">ACCESO RÁPIDO</p>
                    <h2 id="accesos-title">Áreas del sistema</h2>
                </div>
                <p>Elegí un registro para consultar, editar o agregar información.</p>
            </div>
            <div class="admin-groups">
                <?php foreach ($gruposAdministracion as $nombreGrupo => $tablasGrupo) { ?>
                    <section class="admin-group">
                        <h3><?= $esc($nombreGrupo) ?></h3>
                        <div class="admin-shortcuts">
                            <?php foreach ($tablasGrupo as $tablaGrupo => $etiquetaTabla) { ?>
                                <a href="?tabla=<?= rawurlencode($tablaGrupo) ?>#gestionar-tabla">
                                    <span><?= $esc($etiquetaTabla) ?></span>
                                    <span aria-hidden="true">↗</span>
                                </a>
                            <?php } ?>
                        </div>
                    </section>
                <?php } ?>
            </div>
        </section>

        <section class="admin-section faq-admin" id="preguntas-frecuentes" aria-labelledby="faq-admin-title">
            <div class="admin-section-heading">
                <div>
                    <p class="admin-eyebrow">CONTENIDO DE AYUDA</p>
                    <h2 id="faq-admin-title">Preguntas frecuentes</h2>
                </div>
                <p>Las preguntas guardadas aquí se administran desde la tabla pregunta_frecuente.</p>
            </div>

            <div class="faq-admin-layout">
                <form class="faq-admin-form" method="POST" action="panel-admin.php#preguntas-frecuentes">
                    <h3><?= $preguntaEnEdicion ? 'Editar pregunta' : 'Agregar una pregunta' ?></h3>
                    <input type="hidden" name="accion" value="<?= $preguntaEnEdicion ? 'faq_guardar' : 'faq_crear' ?>">
                    <?php if ($preguntaEnEdicion) { ?>
                        <input type="hidden" name="id_pregunta" value="<?= (int) $preguntaEnEdicion['id_pregunta'] ?>">
                    <?php } ?>
                    <label for="pregunta">Pregunta</label>
                    <textarea id="pregunta" name="pregunta" rows="3" required><?= $esc($preguntaEnEdicion['pregunta'] ?? '') ?></textarea>
                    <label for="respuesta">Respuesta</label>
                    <textarea id="respuesta" name="respuesta" rows="6" required><?= $esc($preguntaEnEdicion['respuesta'] ?? '') ?></textarea>
                    <div class="faq-form-actions">
                        <button type="submit" class="admin-button primary"><?= $preguntaEnEdicion ? 'Guardar cambios' : 'Agregar pregunta' ?></button>
                        <?php if ($preguntaEnEdicion) { ?>
                            <a class="admin-button secondary" href="panel-admin.php#preguntas-frecuentes">Cancelar</a>
                        <?php } ?>
                    </div>
                </form>

                <div class="faq-admin-list">
                    <?php if (!$preguntasFrecuentes) { ?>
                        <p class="admin-empty">Todavía no hay preguntas frecuentes. Agregá la primera con el formulario.</p>
                    <?php } ?>
                    <?php foreach ($preguntasFrecuentes as $faq) { ?>
                        <article class="faq-admin-item">
                            <div>
                                <h3><?= $esc($faq['pregunta']) ?></h3>
                                <p><?= nl2br($esc($faq['respuesta'])) ?></p>
                            </div>
                            <div class="faq-item-actions">
                                <a class="admin-button secondary" href="?editar_pregunta=<?= (int) $faq['id_pregunta'] ?>#preguntas-frecuentes">Editar</a>
                                <form method="POST" action="panel-admin.php#preguntas-frecuentes" onsubmit="return confirm('¿Eliminar esta pregunta frecuente?');">
                                    <input type="hidden" name="accion" value="faq_eliminar">
                                    <input type="hidden" name="id_pregunta" value="<?= (int) $faq['id_pregunta'] ?>">
                                    <button type="submit" class="admin-button danger">Eliminar</button>
                                </form>
                            </div>
                        </article>
                    <?php } ?>
                </div>
            </div>
        </section>

        <section class="admin-section" id="gestionar-tabla" aria-labelledby="tabla-title">
            <div class="admin-section-heading">
                <div>
                    <p class="admin-eyebrow">GESTIÓN DE REGISTROS</p>
                    <h2 id="tabla-title">Explorar una tabla</h2>
                </div>
                <p>La vista detallada permite editar y eliminar registros existentes.</p>
            </div>
            <form method="GET" class="form-selector-tabla">
                <label for="tabla">Tabla</label>
                <select name="tabla" id="tabla" required>
                    <option value="">Seleccioná un área o tabla</option>
                    <?php foreach ($gruposAdministracion as $nombreGrupo => $tablasGrupo) { ?>
                        <optgroup label="<?= $esc($nombreGrupo) ?>">
                            <?php foreach ($tablasGrupo as $nombreTabla => $etiquetaTabla) { ?>
                                <option value="<?= $esc($nombreTabla) ?>" <?= $tablaSeleccionada === $nombreTabla ? 'selected' : '' ?>>
                                    <?= $esc($etiquetaTabla) ?>
                                </option>
                            <?php } ?>
                        </optgroup>
                    <?php } ?>
                </select>
                <button type="submit" class="admin-button primary">Abrir tabla</button>
            </form>

            <?php if ($tablaSeleccionada && $columnasTablaSeleccionada) { ?>
                <section class="tabla-seleccionada tabla-container">
                    <div class="tabla-seleccionada-header">
                        <div>
                            <p class="admin-eyebrow">TABLA SELECCIONADA</p>
                            <h2><?= $esc($tablaSeleccionada) ?></h2>
                        </div>
                        <form action="../php/IngresarRegistro.php" method="GET">
                            <input type="hidden" name="tipo" value="insert">
                            <input type="hidden" name="tabla" value="<?= $esc($tablaSeleccionada) ?>">
                            <button type="submit" class="admin-button primary">Ingresar registro</button>
                        </form>
                    </div>
                    <div class="tabla-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <?php foreach ($columnasTablaSeleccionada as $columna) { ?>
                                        <th><?= $esc($columna) ?></th>
                                    <?php } ?>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($reg = mysqli_fetch_assoc($resultadoTablaSeleccionada)) { ?>
                                    <tr>
                                        <?php foreach ($columnasTablaSeleccionada as $columna) { ?>
                                            <td><?= $tablaSeleccionada === 'usuario' && $columna === 'password' ? '••••••••' : $esc($reg[$columna] ?? '') ?></td>
                                        <?php } ?>
                                        <td class="acciones">
                                            <form action="../php/editarRegistro.php" method="POST">
                                                <input type="hidden" name="tabla" value="<?= $esc($tablaSeleccionada) ?>">
                                                <input type="hidden" name="id" value="<?= $esc($reg[$columnasTablaSeleccionada[0]]) ?>">
                                                <button type="submit" class="admin-button secondary">Editar</button>
                                            </form>
                                            <form action="../php/eliminarRegistro.php" method="POST" onsubmit="return confirm('¿Eliminar este registro?');">
                                                <input type="hidden" name="tabla" value="<?= $esc($tablaSeleccionada) ?>">
                                                <input type="hidden" name="id" value="<?= $esc($reg[$columnasTablaSeleccionada[0]]) ?>">
                                                <button type="submit" class="admin-button danger">Eliminar</button>
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
        </section>
    </main>

    <footer class="footer">
        <p>© 2026 Kinetix. Todos los derechos reservados. Panel exclusivo para administradores.</p>
    </footer>
</body>
</html>