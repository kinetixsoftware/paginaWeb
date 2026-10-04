<?php 
session_start();
$idUsuario = (int) ($_SESSION['idUsuario'] ?? 0);
$rol = (int) ($_SESSION['rol'] ?? 0);
$nombre = htmlspecialchars((string) ($_SESSION['nombre'] ?? ''), ENT_QUOTES, 'UTF-8');
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kinetix - Panel de Cliente</title>
    <!-- estilo -->
    <link rel="stylesheet" href="../css/pagina-inicio.css">
    <link rel="stylesheet" href="../css/kinetix-theme.css">
</head>

<body>
    <nav class="menu">
        <div class="menu-content">
            <a class="menu-logo" href="inicio.php" aria-label="Kinetix, inicio">
                <img src="../imagenes/Logo1.png" alt="Kinetix Software">
            </a>
            <ul>
                <?php if ($rol === 1 || $rol === 2) { ?>
                <li><a href="inicio.php" class="active" aria-current="page">Inicio</a></li>
                <li><a href="FAQ-pagina-cliente.html">Ayuda</a></li>
                <li><a href="tickets.php">Tickets</a></li>
                <li><a href="inventario.php">Inventario</a></li>
                <li><a href="usuario.php?id=<?= $idUsuario ?>">Mi cuenta</a></li>
                <li><a href="../php/logout.php">Salir</a></li>
                <?php } elseif ($rol === 3) { ?>
                <li><a href="inicio.php" class="active" aria-current="page">Inicio</a></li>
                <li><a href="panel-admin.php">Administración</a></li>
                <li><a href="informes-admin.php">Informes</a></li>
                <li><a href="../php/logout.php">Salir</a></li>
                <?php } else { ?>
                <li><a href="login.php">Iniciar sesión</a></li>
                <li><a href="registrarse.php">Crear cuenta</a></li>
                <?php } ?>
                <li class="language-switch">
                    <input type="checkbox" id="langToggle" aria-label="Cambiar idioma">
                    <label for="langToggle" class="switch" title="Cambiar idioma">
                        <span class="lang left">ES</span>
                        <span class="lang right">EN</span>
                        <span class="slider"></span>
                    </label>
                </li>
                <li><button type="button" id="themeToggle" class="theme-toggle" aria-label="Cambiar tema">🌙</button></li>
            </ul>
        </div>
    </nav>

    <?php if (isset($_SESSION['mensaje'])) { ?>
    <div class="toast-wrapper">
        <div id="formMessage" class="form-message <?= htmlspecialchars((string) ($_SESSION['tipoError'] ?? 'success'), ENT_QUOTES, 'UTF-8') ?>">
            <?= htmlspecialchars((string) $_SESSION['mensaje'], ENT_QUOTES, 'UTF-8') ?>
        </div>
    </div>
    <?php unset($_SESSION['mensaje'], $_SESSION['tipoError']); } ?>

    <main class="home-shell">
        <section class="home-intro">
            <div class="home-intro-copy">
                <p class="home-eyebrow">KINETIX <span>·</span> ESPACIO DIGITAL</p>
                <?php if ($rol === 1 || $rol === 2) { ?>
                <h1>Hola, <?= $nombre ?>.</h1>
                <p class="home-lead">Tu proyecto, soporte y recursos en un solo lugar.</p>
                <a class="home-primary-action" href="tickets.php">Ir a mis tickets <span aria-hidden="true">→</span></a>
                <?php } elseif ($rol === 3) { ?>
                <h1>Panel de control.</h1>
                <p class="home-lead">Administrá Kinetix y consultá el estado general de la plataforma.</p>
                <a class="home-primary-action" href="panel-admin.php">Abrir administración <span aria-hidden="true">→</span></a>
                <?php } else { ?>
                <h1>Todo tu proyecto, en movimiento.</h1>
                <p class="home-lead">Un espacio claro para acompañar cada etapa, resolver consultas y mantener tus recursos organizados.</p>
                <a class="home-primary-action" href="login.php">Ingresar a Kinetix <span aria-hidden="true">→</span></a>
                <?php } ?>
            </div>
            <div class="home-intro-mark" aria-hidden="true">
                <span class="mark-line"></span>
                <span class="mark-node">K</span>
                <span class="mark-caption">IDEAS<br>EN ACCIÓN</span>
            </div>
        </section>

        <section class="home-links" aria-labelledby="home-links-title">
            <div class="home-section-heading">
                <div>
                    <p class="home-eyebrow">ACCESOS DIRECTOS</p>
                    <h2 id="home-links-title">¿A dónde vamos?</h2>
                </div>
                <span class="home-section-note">Tu espacio de trabajo</span>
            </div>
            <div class="home-link-grid">
                <?php if ($rol === 1 || $rol === 2) { ?>
                <a class="home-link-card" href="inventario.php">
                    <span class="home-link-index">01</span><span class="home-link-icon" aria-hidden="true">▦</span>
                    <h3>Inventario</h3><p>Consultá los recursos vinculados a tu proyecto.</p><span class="home-link-arrow" aria-hidden="true">↗</span>
                </a>
                <a class="home-link-card" href="tickets.php">
                    <span class="home-link-index">02</span><span class="home-link-icon" aria-hidden="true">↗</span>
                    <h3>Soporte técnico</h3><p>Seguí tus consultas y conversá con el equipo.</p><span class="home-link-arrow" aria-hidden="true">↗</span>
                </a>
                <a class="home-link-card" href="FAQ-pagina-cliente.html">
                    <span class="home-link-index">03</span><span class="home-link-icon" aria-hidden="true">?</span>
                    <h3>Centro de ayuda</h3><p>Encontrá respuestas a preguntas frecuentes.</p><span class="home-link-arrow" aria-hidden="true">↗</span>
                </a>
                <a class="home-link-card" href="usuario.php?id=<?= $idUsuario ?>">
                    <span class="home-link-index">04</span><span class="home-link-icon" aria-hidden="true">◎</span>
                    <h3>Mi cuenta</h3><p>Revisá los datos de tu perfil.</p><span class="home-link-arrow" aria-hidden="true">↗</span>
                </a>
                <?php } elseif ($rol === 3) { ?>
                <a class="home-link-card" href="panel-admin.php">
                    <span class="home-link-index">01</span><span class="home-link-icon" aria-hidden="true">▦</span>
                    <h3>Administración</h3><p>Gestioná tablas y registros de la plataforma.</p><span class="home-link-arrow" aria-hidden="true">↗</span>
                </a>
                <a class="home-link-card" href="informes-admin.php">
                    <span class="home-link-index">02</span><span class="home-link-icon" aria-hidden="true">⌁</span>
                    <h3>Informes</h3><p>Consultá las métricas y el resumen del sistema.</p><span class="home-link-arrow" aria-hidden="true">↗</span>
                </a>
                <?php } else { ?>
                <a class="home-link-card" href="FAQ-pagina-cliente.html">
                    <span class="home-link-index">01</span><span class="home-link-icon" aria-hidden="true">?</span>
                    <h3>Centro de ayuda</h3><p>Explorá respuestas y orientación para empezar.</p><span class="home-link-arrow" aria-hidden="true">↗</span>
                </a>
                <a class="home-link-card" href="login.php">
                    <span class="home-link-index">02</span><span class="home-link-icon" aria-hidden="true">＋</span>
                    <h3>Iniciar Sesion</h3><p>Inicia sesin para acceder a tu espacio Kinetix.</p><span class="home-link-arrow" aria-hidden="true">↗</span>
                </a>
                <a class="home-link-card" href="registrarse.php">
                    <span class="home-link-index">03</span><span class="home-link-icon" aria-hidden="true">＋</span>
                    <h3>Crear cuenta</h3><p>Registrate para acceder a tu espacio Kinetix.</p><span class="home-link-arrow" aria-hidden="true">↗</span>
                </a>
                <?php } ?>
            </div>
        </section>

        <section class="home-note">
            <span class="home-note-rule" aria-hidden="true"></span>
            <div><p class="home-eyebrow">KINETIX SOFTWARE</p><h2>La tecnología funciona mejor cuando todo está conectado.</h2></div>
            <p>Desde el seguimiento del proyecto hasta el soporte diario, encontrá cada herramienta donde la necesitás.</p>
        </section>
    </main>

    <footer class="home-footer">
        <span>© 2026 Kinetix</span><span>Software para avanzar.</span>
    </footer>

    <script>
    const languageToggle = document.getElementById('langToggle');
    languageToggle?.addEventListener('change', function () {
        setTimeout(() => {
            window.location.href = this.checked ? '../en/home.html' : '../es/inicio.php';
        }, 250);
    });

    const themeToggle = document.getElementById('themeToggle');
    function applyTheme(theme) {
        const isDark = theme === 'dark';
        document.body.classList.toggle('dark-mode', isDark);
        document.body.classList.toggle('light-mode', !isDark);
        themeToggle.textContent = isDark ? '☀️' : '🌙';
        themeToggle.setAttribute('aria-label', isDark ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro');
    }

    applyTheme(localStorage.getItem('theme') || 'dark');
    themeToggle.addEventListener('click', () => {
        const nextTheme = document.body.classList.contains('light-mode') ? 'dark' : 'light';
        applyTheme(nextTheme);
        localStorage.setItem('theme', nextTheme);
    });
    </script>
</body>
</html>