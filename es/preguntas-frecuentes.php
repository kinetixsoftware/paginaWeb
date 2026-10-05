<?php 

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
                <li><a href="inicio.php">Inicio</a></li>
                <li><a href="tickets.php">Tickets</a></li>
                <li><a href="inventario.php">Inventario</a></li>
                <li><a href="inicio.php">Mi Cuenta</a></li>
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
                    <button id="faqSearchButton" type="button">Buscar</button>
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
                    <a href="../es/crear-ticket.php" class="btn-contacto">Contactar Soporte</a>
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
            themeToggle.textContent = isDark ? '☀️' : '🌙';
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

    const faqSearchInput = document.getElementById('faqSearchInput');
    const faqSearchButton = document.getElementById('faqSearchButton');
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
    faqSearchButton.addEventListener('click', filterFaq);
    </script>
</body>

</html>