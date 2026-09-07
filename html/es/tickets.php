<?php 
session_start();

require_once "../php/conexionBDD.php";

$conexion = conectarBD();

$rol = isset($_SESSION['rol']) ? (int) $_SESSION['rol'] : null;
$idUsuario = (int) ($_SESSION['idUsuario'] ?? 0);
$visitante = "";
if ($rol === null || $rol === 3) {
    $_SESSION['mensaje'] = "No estas autorizado a ver esta pagina";
    $_SESSION['tipoError'] = "error";
    header("Location: login.php");
    exit;
} else if ($rol === 1) {
    $visitante = "Usuario";
}  else if ($rol === 2) {
    $visitante = "Tecnico";
}

$filtroTickets = $rol === 2
    ? "(t.id_tecnico = $idUsuario OR t.id_tecnico IS NULL)"
    : "t.id_solicitante = $idUsuario";

$ticketslist = "SELECT t.id_ticket, t.titulo, e.estado, u.nombre, u.apellido, pt.prioridad, ct.categoria
                FROM ticket           AS t 
                JOIN estado_ticket    AS e   ON t.id_estado = e.id_estado
                JOIN usuario          AS u   ON t.id_solicitante = u.id_usuario
                JOIN prioridad_ticket AS pt  ON t.id_prioridad = pt.id_prioridad
                JOIN categoria_ticket AS ct  ON t.id_categoria = ct.id_categoria
                WHERE $filtroTickets
                ORDER BY t.fecha_creacion DESC;";

$ticketlistresult = mysqli_query($conexion, $ticketslist);
$mensajesResultado = null;
$ticketTitulo = null;
$id_ticket = (int) ($_GET['id'] ?? $_POST['id_ticket'] ?? 0);

if ($id_ticket > 0) {

    $ticket = "SELECT id_tecnico, id_solicitante, titulo
               FROM ticket
               WHERE id_ticket = $id_ticket
               LIMIT 1";
    $ticketResultado = mysqli_query($conexion, $ticket);
    $ticketData = mysqli_fetch_assoc($ticketResultado);
    $ticketPermitido = $ticketData && (($rol === 2 && (empty($ticketData['id_tecnico']) || (int) $ticketData['id_tecnico'] === $idUsuario)) || ($rol === 1 && (int) $ticketData['id_solicitante'] === $idUsuario));

    if ($ticketPermitido) {
        $ticketTitulo = $ticketData['titulo'];

        if (empty($ticketData['id_tecnico']) && $rol === 2) {
            $sql = "UPDATE ticket
                    SET id_tecnico = $idUsuario, id_estado = 3
                    WHERE id_ticket = $id_ticket";
            mysqli_query($conexion, $sql);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'mandarMensaje') {
            $contenidoMensaje = trim($_POST['contenido'] ?? '');

            if ($contenidoMensaje !== '') {
                $contenidoMensaje = mysqli_real_escape_string($conexion, $contenidoMensaje);
                $sql = "INSERT INTO mensaje_ticket (id_ticket, id_usuario, contenido)
                        VALUES ($id_ticket, $idUsuario, '$contenidoMensaje')";
                mysqli_query($conexion, $sql);
            }

            header("Location: tickets.php?id=$id_ticket");
            exit;
        }

        $mensajes = "SELECT mt.id_usuario, mt.contenido, mt.fecha_creacion
                    FROM mensaje_ticket AS mt
                    WHERE mt.id_ticket = $id_ticket
                    ORDER BY mt.fecha_creacion ASC;";
        $mensajesResultado = mysqli_query($conexion, $mensajes);
    } else {
        $id_ticket = 0;
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tickets</title>
<link rel="stylesheet" href="../css/sistema-de-tickets-tecnico.css">
</head>

<body>
    <!-- Menu -->
    <nav class="menu">
        <div class="menu-content">
            <div class="menu-logo">
                <img src="../../imagenes/Logo1.png" alt="Logo Kinetix">
            </div>

            <ul>
                <li><a href="inicio.php">Inicio</a></li>
                <li><a href="FAQ-pagina-cliente.html">FAQ</a></li>
                <li><a href="tickets.php" class="active" aria-current="page">Tickets</a></li>
                <li><a href="inventario.php">Inventario</a></li>
                <li><a href="Login.php">Mi Cuenta</a></li>
                <li> <button type="button" id="themeToggle" class="theme-toggle" aria-label="Cambiar tema">🌙 Dark</button></li>
            </ul>
        </div>
    </nav>
    <div class="page">
        <div class="container">
            <!-- LISTA DE TICKETS -->
            <div class="sidebar">
                <h2>Tickets</h2>
                <div class="ticket-list">
                    <!-- aca van los tickets -->
                    <!-- algunos tickets de ejemplo -->
                    <?php while($reg = mysqli_fetch_assoc($ticketlistresult)) { ?>
                        <a class="ticket" href="tickets.php?id=<?= $reg['id_ticket']?>">
                            <div class="ticket-top">
                                <span class="ticket-id">#<?= str_pad($reg['id_ticket'], 5, '0', STR_PAD_LEFT) ?></span>
                                <div class="ticket-status <?= strtolower(str_replace(' ', '-', $reg['estado'])) ?>">
                                    <span class="status-dot"></span>
                                    <?= $reg['estado'] ?>
                                </div>
                            </div>
                            <h6 style="font-size:13px; padding-bottom:-5px;">Categoria: <?= $reg['categoria']?> </h6>
                            <div class="ticket-title">
                                <h6 style="font-size:16px; font-weight: lighter;"><?= htmlspecialchars($reg['titulo'], ENT_QUOTES, 'UTF-8') ?></h6>
                            </div>
                        </a>
                    <?php } ?>
                </div>
            </div>

            <!-- CHAT -->

            <div class="main">
                <div class="header">
                    <h1 style="text-align: center; padding: 15px;"><?= htmlspecialchars($ticketTitulo ?? 'Seleccioná un ticket', ENT_QUOTES, 'UTF-8') ?></h1>
                </div>
                <div class="chat">
                    <?php while($mensajesResultado && $reg = mysqli_fetch_assoc($mensajesResultado)) { ?>
                        <div class="message <?= $idUsuario === (int) $reg['id_usuario'] ? 'sent' : 'received' ?>">
                            <?= $reg['contenido'] ?>
                        </div>
                    <?php }?>
                </div>
                <div class="chat-input">
                    <form method="POST">
                        <input type="hidden" name="accion" value="mandarMensaje">
                        <input type="hidden" name="id_ticket" value="<?= $id_ticket ?>">
                        <textarea name="contenido" rows="2" cols="10" placeholder="Escriba un mensaje..." <?= $id_ticket > 0 ? '' : 'disabled' ?>></textarea>
                        <button type="submit" style="background-color: rgb(78, 131, 0);" <?= $id_ticket > 0 ? '' : 'disabled' ?>> Enviar </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
  <script>
    const themeToggle = document.getElementById('themeToggle');

    function applyTheme(theme) {
      const body = document.body;
      const isDark = theme === 'dark';
      body.classList.toggle('dark-mode', isDark);
      body.classList.toggle('light-mode', !isDark);
      if (themeToggle) {
        themeToggle.textContent = isDark ? '☀️ Light' : '🌙 Dark';
        themeToggle.setAttribute('aria-label', isDark ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro');
      }
    }

    const savedTheme = localStorage.getItem('theme') || 'dark';
    applyTheme(savedTheme);

    if (themeToggle) {
      themeToggle.addEventListener('click', () => {
        const nextTheme = document.body.classList.contains('light-mode') ? 'dark' : 'light';
        applyTheme(nextTheme);
        localStorage.setItem('theme', nextTheme);
      });
    }
  </script></body>
</html>