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
    <link rel="icon" type="image/png" href="../imagenes/favicon.png">
    <link rel="stylesheet" href="../css/FAQ-pagina-cliente.css">
    <link rel="stylesheet" href="../css/kinetix-theme.css">
    <script src="../js/theme.js" defer></script>
    <script src="../js/i18n.js?v=4" defer></script>
    <title data-i18n="faq.title">Preguntas Frecuentes</title>
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
                <li><a href="tickets" data-i18n="nav.tickets">Tickets</a></li>
                <li><a href="preguntas-frecuentes" class="active" data-i18n="nav.ayuda">Ayuda</a></li>
                <li><a href="inventario" data-i18n="nav.inventario">Inventario</a></li>
                <li><a href="inicio" data-i18n="nav.mi_cuenta">Mi Cuenta</a></li>
                <?php if (isset($_SESSION['rol'])) { ?>
                <li><a class="logout-button" href="../php/logout"><span class="logout-sign" aria-hidden="true"></span><span class="logout-text" data-i18n="nav.cerrar_sesion">Cerrar sesión</span></a></li>
                <?php } ?>
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
                <h1 data-i18n="faq.heading">Preguntas Frecuentes</h1>
                <p class="subtitle" data-i18n="faq.subtitle">
                    Información útil para gestionar activos, tickets y solicitudes de servicio en las distintas sedes.
                </p>
                <div class="search-box">
                    <input id="faqSearchInput" type="text" placeholder="Escribe tu duda aquí..."
                        aria-label="Buscar en preguntas frecuentes" data-i18n-attrs='{"placeholder":"faq.search_placeholder","aria-label":"faq.search_label"}'>
                </div>
                <div id="faqNoResults" class="faq-no-results" hidden>
                    <span data-i18n="faq.no_results">No se encontraron coincidencias para tu búsqueda, considera abrir un ticket para hablar con un tecnico!.</span>
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
                    <h2 data-i18n="faq.still_questions">¿Aún tienes dudas?</h2>
                    <p data-i18n="faq.support_text">
                        Si no encontraste la respuesta, crea un ticket con los detalles y la sede afectada para que el
                        equipo de soporte pueda orientarte.
                    </p>
                    <a href="crear-ticket" class="btn-contacto" data-i18n="faq.contact_support">Contactar Soporte</a>
                </div>
            </div>
        </div>
    </div>
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
    </script>
</body>

</html>
