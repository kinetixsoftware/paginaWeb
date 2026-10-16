<?php
session_start();

require_once "../php/conexionBDD.php";

$conexion = conectarBD();

$sql = "SELECT pregunta, respuesta FROM pregunta_frecuente ORDER BY id_pregunta";
$sql = mysqli_query($conexion, $sql);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/FAQ-pagina-cliente.css">
    <link rel="stylesheet" href="../css/kinetix-theme.css">
    <title>Preguntas Frecuentes</title>
</head>

<body>
    <!-- Menu -->
    <nav class="menu">
        <div class="menu-content">
            <div class="menu-logo">
                <img src="../imagenes/Logo1.png" alt="Logo Kinetix">
            </div>

            <ul>
                <li><a href="inicio">Inicio</a></li>
                <li><a href="tickets">Tickets</a></li>
                <li><a href="inventario">Inventario</a></li>
                <li><a href="inicio">Mi Cuenta</a></li>
                <?php if (isset($_SESSION['rol'])) { ?>
                <li><a class="logout-button" href="../php/logout"><span class="logout-sign" aria-hidden="true"></span><span class="logout-text">Cerrar sesión</span></a></li>
                <?php } ?>
                <li>
                    <button type="button" id="themeToggle" class="theme-toggle" aria-label="Cambiar tema">🌙
                        Dark</button>
                </li>
            </ul>
        </div>
    </nav>

	<?php if (isset($_SESSION['mensaje'])) { ?>
    <div class="toast-wrapper">
        <div id="formMessage" class="form-message <?= $_SESSION['tipoError'] ?>">
            <?= $_SESSION['mensaje']?>
        </div>
    </div>
    <?php unset($_SESSION['mensaje'], $_SESSION['tipoError']); } ?>
	
    <div class="page">
        <div class="container">
            <div class="content">
                <h1>Preguntas Frecuentes</h1>
                <p class="subtitle">
                    Información útil para gestionar activos, tickets y solicitudes de servicio en las distintas sedes.
                </p>
                <div class="search-box">
                    <input id="faqSearchInput" type="text" placeholder="Escribe tu duda aquí..."
                        aria-label="Buscar en preguntas frecuentes">
                </div>
                <div id="faqNoResults" class="faq-no-results" hidden>
                    No se encontraron coincidencias para tu búsqueda, considera abrir un ticket para hablar con un
                    tecnico!.
                </div>
                <div class="faq">
                    <?php while ($reg = mysqli_fetch_assoc($sql)) {?>
                    <details>
                        <summary><?= $reg['pregunta'] ?></summary>
                        <div class="answer"> <?= $reg['respuesta'] ?> </div>
                    </details>
                    <?php } ?>
                </div>
                <div class="support">
                    <h2>¿Aún tienes dudas?</h2>
                    <p>
                        Si no encontraste la respuesta, crea un ticket con los detalles y la sede afectada para que el
                        equipo de soporte pueda orientarte.
                    </p>
                    <a href="crear-ticket" class="btn-contacto">Contactar Soporte</a>
                </div>
            </div>
        </div>
    </div>
    <script src="../js/theme.js"></script>
    <script>
    const faqSearchInput = document.getElementById('faqSearchInput');
    const faqNoResults = document.getElementById('faqNoResults');
    const faqItems = Array.from(document.querySelectorAll('.faq details'));

    function filterFaq() {
        const query = faqSearchInput.value.trim().toLowerCase();
        let visibleCount = 0;

        faqItems.forEach((item) => {
            const summaryText = item.querySelector('summary')?.textContent.toLowerCase() || '';
            const matches = !query || summaryText.includes(query);

            item.hidden = !matches;
            item.style.display = matches ? '' : 'none';

            if (matches) visibleCount++;
        });

        faqNoResults.hidden = visibleCount !== 0;
    }
    faqSearchInput.addEventListener('input', filterFaq);
    faqSearchInput.addEventListener('input', filterFaq);
    </script>
</body>

</html>