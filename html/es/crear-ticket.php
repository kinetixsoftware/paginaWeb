<?php 
session_start();

require_once "../php/conexionBDD.php";

$conexion = conectarBD();

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== "1") {
    $_SESSION['mensaje'] = "Solo los usuarios pueden crear tickets";
    $_SESSION['tipoError'] = "error";
    header("Location: inicio.php");
}

$categorias = "SELECT * FROM categoria_ticket ORDER BY id_categoria ASC";
$resultadocategorias = mysqli_query($conexion, $categorias);

$prioridad = "SELECT * FROM prioridad_ticket ORDER BY id_prioridad ASC";
$resultadoprioridad = mysqli_query($conexion, $prioridad);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$ticketTitulo = $_POST['ticketTitulo'] ?? '';
	$ticketCategoria = $_POST['ticketCategoria'];
	$ticketPrioridad = $_POST['ticketPrioridad'];
	$descripcion = $_POST['descripcion'];
	$idUsuario = $_SESSION['idUsuario'];
    $last_id = null;

	try {
        $sql = "INSERT INTO ticket (titulo, descripcion, id_estado, id_prioridad, id_solicitante, id_categoria)
        VALUES ('$ticketTitulo', '$descripcion', 1, $ticketPrioridad, $idUsuario, $ticketCategoria)";
        $registro = mysqli_query($conexion, $sql);

        if($registro) {$last_id = $conexion->insert_id;} // obtener el id del ticket creado

        $sql2 = "INSERT INTO historial_ticket (id_ticket, estado_nuevo) VALUES ('$last_id', 1)";
        $registro2 = mysqli_query($conexion, $sql2);

        if($registro && $registro2) {
            header("Location: tickets.php");
            exit;
        } else {
            echo "Error al crear el ticket: " . mysqli_error($conexion);
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
    <link rel="stylesheet" href="..\css\crear-ticket.css">
    <title>Crear Ticket</title>
</head>

<body>
    <!-- Menu -->
    <nav class="menu">
        <div class="menu-content">
            <div class="menu-logo">
                <img src="../../imagenes/Logo1.png" alt="Logo Kinetix">
            </div>

            <ul>
                <li><a href="inicio.html">Inicio</a></li>
                <li><a href="FAQ-pagina-cliente.html">FAQ</a></li>
                <li><a href="pagina-de-tickets-tecnico.html">Tickets</a></li>
                <li><a href="inventario.html">Inventario</a></li>
                <li><a href="Login.html">Mi Cuenta</a></li>
                <div class="language-switch">
                    <input type="checkbox" id="langToggle">

                    <label for="langToggle" class="switch">
                        <span class="lang left">ESP</span>
                        <span class="lang right">ENG</span>
                        <span class="slider"></span>

                        <script>
                        const toggle = document.getElementById("langToggle");

                        toggle.addEventListener("change", function() {
                            setTimeout(() => {
                                window.location.href = this.checked ? "../../en/open-ticket.html" :
                                    "../../es/crear=ticket/.html";
                            }, 300);
                        });
                        </script>
                    </label>
                </div>
                <li>
                    <button type="button" id="themeToggle" class="theme-toggle" aria-label="Cambiar tema">🌙
                        Dark</button>
                </li>
            </ul>
        </div>
    </nav>

    <div class="toast-wrapper">
        <?php if(isset($_SESSION['mensaje'])) { ?>
        <div id="formMessage" class="form-message <?= $_SESSION['tipoError'] ?>">
            <?= $_SESSION['mensaje'] ?>
        </div>
        <?php } ?>
    </div>

    <div class="page">
        <div class="container">
            <h1>Crear Ticket</h1>
            <p class="subtitle"> Completa el formulario para enviar una solicitud al equipo de soporte. </p>
            <form method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="ticketTitulo">Asunto *</label>
                    <input type="text" id="ticketTitulo" name="ticketTitulo" placeholder="Ej.: No puedo iniciar sesión">
                </div>
                <div class="form-group">
                    <label for="ticketCategoria">Tipo de ticket</label>
                    <select name="ticketCategoria" id="ticketCategoria">
                        <?php while ($reg = mysqli_fetch_array($resultadocategorias)) { ?>
                        <option name="<?=$reg['categoria'] ?>" value="<?= $reg['id_categoria'] ?>">
                            <?= $reg['categoria'] ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="ticketPrioridad">Prioridad *</label>
                    <select id="ticketPrioridad" name="ticketPrioridad">
                        <?php while ($reg = mysqli_fetch_array($resultadoprioridad)) { ?>
                        <option name="<?=$reg['prioridad'] ?>" value="<?= $reg['id_prioridad'] ?>">
                            <h5><?= $reg['prioridad'] ?></h5>
                        </option>
                        <?php } ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="descripcion">Descripción *</label>
                    <textarea id="descripcion" name="descripcion" rows="5" cols="40" placeholder="Describa detalladamente el problema..."></textarea>
                </div>
                <div class="buttons">
                    <button type="button" class="btn-login" style="background:#d5d5d5;"
                        onclick="window.location.href='faq-pagina-cliente.html'"> Cancelar </button>
                    <button type="submit" class="btn-login">Crear Ticket</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    const themeToggle = document.getElementById('themeToggle');

    function applyTheme(theme) {
        const body = document.body;
        const isDark = theme === 'dark';
        body.classList.toggle('dark-mode', isDark);
        if (themeToggle) {
            themeToggle.textContent = isDark ? '☀️ Light' : '🌙 Dark';
            themeToggle.setAttribute('aria-label', isDark ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro');
        }
    }
    const savedTheme = localStorage.getItem('theme') || 'dark';
    applyTheme(savedTheme);

    if (themeToggle) {
        themeToggle.addEventListener('click', () => {
            const isDark = document.body.classList.contains('dark-mode');
            applyTheme(isDark ? 'light' : 'dark');
        });
    }

    const formMessage = document.getElementById('formMessage');
    const params = new URLSearchParams(window.location.search);
    const status = params.get('status');
    const msg = params.get('msg');

    if (status && msg) {
        formMessage.hidden = false;
        formMessage.textContent = decodeURIComponent(msg);
        formMessage.classList.add(status === 'success' ? 'success' : 'error');
    }
    </script>
</body>

</html>