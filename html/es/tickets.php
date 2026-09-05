<?php 
session_start();
$rol = $_SESSION['rol'];

if (!isset($_SESSION['rol'])) {
    $_SESSION['mensaje'] = "No estas autorizado a ver esta pagina";
    $_SESSTION['tipoError'] = "error";
    header("Location: login.php");
} 

$servername = "localhost";
$username = "root";
$passwordbd = "";
$dbname = "kinetixsoftware";

$conexion = mysqli_connect($servername, $username, $passwordbd, $dbname);

$ticketslist = "SELECT t.id_ticket, t.titulo, e.estado, u.nombre, u.apellido, p.prioridad, c.categoria
                FROM ticket AS t 
                JOIN estado_ticket AS e
                    ON t.id_estado = e.id_estado
                JOIN usuario AS u 
                    ON t.id_solicitante = u.id_usuario
                JOIN prioridad_ticket as p
                    ON t.id_prioridad = p.prioridad
                JOIN categoria_ticket as c
                    ON t.id_categoria = c.id_categoria
                WHERE t.id_tecnico = $rol OR t.id_tecnico = 0
                ORDER BY t.fecha_creacion DESC";

$ticketlistresult = mysqli_query($conexion, $ticketslist);

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
                <div class="ticket">
                    <div class="ticket-top">
                        <span class="ticket-id"><?=$reg['id_ticket']?></span>
                        <div class="ticket-status <?= $reg['estado']?>">
                            <span class="status-dot"></span>
                            <?= $reg['estado'] ?>
                        </div>
                    </div>
                    <h6 style="font-size:13px; padding-bottom:-5px;">Categoria: <?= $reg['categoria']?> </h6>
                    <div class="ticket-title">
                        <h6 style="font-size:16px; font-weight: lighter;"><?= $reg['titulo'] ?></h6>
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>

        <!-- CHAT -->

        <div class="main">
            <div class="header">
                <h1 style="text-align: center; padding: 15px;"> No enciende la PC</h1>
            </div>
            <div class="chat">
                <div class="message received">
                    Hola, necesito ayuda con mi computadora.
                </div>
                <div class="message sent">
                    Buen día. ¿Qué problema presenta?
                </div>
            </div>
            <div class="chat-input">
                <form method="POST">
                    <input type="text" placeholder="Escriba un mensaje...">
                    <button style="background-color: rgb(78, 131, 0);">
                        Enviar
                    </button>
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
