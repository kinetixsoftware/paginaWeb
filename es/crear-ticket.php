<?php 
session_start();

require_once "../php/conexionBDD.php";

$conexion = conectarBD();

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== "1") {
    $_SESSION['mensaje'] = "Solo los usuarios pueden crear tickets";
    $_SESSION['tipoError'] = "error";
    header("Location: inicio");
}

$idUsuario = (int) ($_SESSION['idUsuario'] ?? 0);

$categorias = "SELECT * FROM categoria_ticket ORDER BY id_categoria ASC";
$resultadocategorias = mysqli_query($conexion, $categorias);

$prioridad = "SELECT * FROM prioridad_ticket ORDER BY id_prioridad ASC";
$resultadoprioridad = mysqli_query($conexion, $prioridad);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$ticketTitulo = mysqli_real_escape_string($conexion, $_POST['ticketTitulo'] ?? '');
	$ticketCategoria = $_POST['ticketCategoria'];
	$ticketPrioridad = $_POST['ticketPrioridad'];
	$descripcion = mysqli_real_escape_string($conexion, $_POST['descripcion']);
	$idUsuario = $_SESSION['idUsuario'];
    $last_id = null;

	try {
        $sql = "INSERT INTO ticket (titulo, descripcion, id_estado, id_prioridad, id_solicitante, id_categoria)
        VALUES ('$ticketTitulo', '$descripcion', 1, $ticketPrioridad, $idUsuario, $ticketCategoria)";
        $registro = mysqli_query($conexion, $sql);

        if($registro) {$last_id = $conexion->insert_id;} // obtener el id del ticket creado

        $sql2 = "INSERT INTO historial_ticket (id_ticket, estado_nuevo, accion) VALUES ($last_id, 1, 'Ticket creado por el usuario')";
        $registro2 = mysqli_query($conexion, $sql2);

        $sql3 = "INSERT INTO mensaje_ticket (id_ticket, id_usuario, contenido) VALUES ($last_id, '$idUsuario', 'Descripcion: $descripcion')";
        $registro3 = mysqli_query($conexion, $sql3);

        if($registro && $registro2 && $registro3) {
            header("Location: ticket/" . (int) $last_id);
            exit;
        } else {
            $tipoError = "error";
            $mensaje = "Error al crear ticket: " . mysqli_error($conexion);
        }
	} catch (Throwable $e) {
		$tipoError = "error";
        $mensaje = "No se pudo crear el ticket: " . $e->getMessage();
	}
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../imagenes/favicon.png">
    <link rel="stylesheet" href="../css/kinetix-theme.css">
    <link rel="stylesheet" href="../css/crear-ticket.css">
    <script src="../js/theme.js" defer></script>
    <script src="../js/i18n.js?v=4" defer></script>
    <title data-i18n="ticket_create.title">Crear Ticket</title>
</head>

<body>
    <!-- Menu -->
    <nav class="menu">
        <div class="menu-content">
            <div class="menu-logo">
                <img src="../imagenes/Logo1.png" alt="Logo Kinetix">
            </div>

            <ul>
                <li><a href="inicio" data-i18n="nav.inicio">Inicio</a></li>
                <li><a href="preguntas-frecuentes" data-i18n="nav.ayuda" active>Ayuda</a></li>
                <li><a href="tickets" data-i18n="nav.tickets">Tickets</a></li>
                <li><a href="inventario" data-i18n="nav.inventario">Inventario</a></li>
                <li><a href="usuario/<?= $idUsuario ?>" data-i18n="nav.mi_cuenta">Mi Cuenta</a></li>
                <li><a class="logout-button" href="../php/logout"><span class="logout-sign" aria-hidden="true"></span><span class="logout-text" data-i18n="nav.cerrar_sesion">Cerrar sesión</span></a></li>
                <li>
                    <button type="button" id="themeToggle" class="theme-toggle" aria-label="Cambiar tema">🌙
                        Dark</button>
                </li>
                <li>
                    <button type="button" id="langToggle" class="switch" data-i18n-toggle aria-label="Cambiar idioma">
                        <span class="lang left">ES</span><span class="lang right">EN</span><span class="slider"></span>
                    </button>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Mensaje error -->
    <?php if (isset($_SESSION['mensaje'])) { ?>
    <div class="toast-wrapper">
        <div id="formMessage" class="form-message <?= $_SESSION['tipoError'] ?>">
            <?= $_SESSION['mensaje']?>
        </div>
    </div>
    <?php unset($_SESSION['mensaje'], $_SESSION['tipoError']); } ?>

    <div class="page">
        <div class="container">
            <h1 data-i18n="ticket_create.heading">Crear Ticket</h1>
            <p class="subtitle" data-i18n="ticket_create.subtitle"> Completa el formulario para enviar una solicitud al equipo de soporte. </p>
            <form method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="ticketTitulo" data-i18n="ticket_create.subject">Asunto *</label>
                    <input type="text" id="ticketTitulo" name="ticketTitulo" placeholder="Ej.: No puedo iniciar sesión" data-i18n-attrs='{"placeholder":"ticket_create.subject_placeholder"}'>
                </div>
                <div class="form-group">
                    <label for="ticketCategoria" data-i18n="ticket_create.type">Tipo de ticket</label>
                    <select name="ticketCategoria" id="ticketCategoria">
                        <?php while ($reg = mysqli_fetch_array($resultadocategorias)) { ?>
                        <option name="<?=$reg['categoria'] ?>" value="<?= $reg['id_categoria'] ?>" data-i18n-db>
                            <?= $reg['categoria'] ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="ticketPrioridad" data-i18n="ticket_create.priority">Prioridad *</label>
                    <select id="ticketPrioridad" name="ticketPrioridad">
                        <?php while ($reg = mysqli_fetch_array($resultadoprioridad)) { ?>
                        <option name="<?=$reg['prioridad'] ?>" value="<?= $reg['id_prioridad'] ?>" data-i18n-db>
                            <h5 data-i18n-db><?= $reg['prioridad'] ?></h5>
                        </option>
                        <?php } ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="descripcion" data-i18n="ticket_create.description">Descripción *</label>
                    <textarea id="descripcion" name="descripcion" rows="5" cols="40" placeholder="Describa detalladamente el problema..." data-i18n-attrs='{"placeholder":"ticket_create.description_placeholder"}'></textarea>
                </div>
                <div class="buttons">
                    <button type="button" class="cancel"
                        onclick="window.location.href='preguntas-frecuentes'"><span data-i18n="ticket_create.cancel">Cancelar</span></button>
                    <button type="submit" class="btn-login" data-i18n="ticket_create.submit">Crear Ticket</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
