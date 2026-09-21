<?php 
session_start();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kinetix - Panel de Cliente</title>
    <!-- estilo -->
    <link rel="stylesheet" href="..\css\pagina-inicio.css">
    <style>
    [hidden] {
        display: none !important;
    }
    </style>
</head>

<body>
    <!-- Menu -->
    <nav class="menu">
        <div class="menu-content">
            <div class="menu-logo">
                <img src="..\imagenes\Logo1.png" alt="Logo Kinetix">
            </div>
            <ul>
                <?php 
                if (isset($_SESSION["rol"])) { 
                    if ($_SESSION['rol'] == 1 || $_SESSION['rol'] == 2) {?>
                <li> <a href="inicio.php" class="active" aria-current="page">Inicio</a></li>
                <li> <a href="FAQ-pagina-cliente.html">FAQ</a></li>
                <li> <a href="tickets.php">Mis Tickets</a></li>
                <li> <a href="inventario.php">Inventario</a></li>
                <li> <a href="usuario.php?id=<?= $_SESSION['idUsuario'] ?>">Mi Perfil</a></li>
                <li> <a href="../php/logout.php">Cerrar sesión</a></li>
                    <?php } elseif ($_SESSION['rol'] == 3) {?>
                <li> <a href="panel-admin.php">Panel Administrador</a></li>
                <li> <a href="informes-admin.php">Informes sobre la pagina</a></li>
                <li> <a href="../php/logout.php">Cerrar sesión</a></li>
                <?php } } else { ?>
                <li> <a href="login.php">Iniciar sesión</a></li>
                <li> <a href="registrarse.php">Registrarse</a></li>
                <?php } ?>
                <div class="language-switch">
                    <input type="checkbox" id="langToggle">
                    <label for="langToggle" class="switch">
                        <span class="lang left" style="color:black;">🇪🇸</span>
                        <span class="lang right" style="color:black;">🇺🇸</span>
                        <span class="slider"></span>

                        <!-- Esperar .3s antes de cambiar de pagina -->
                        <script>
                        const toggle = document.getElementById("langToggle");
                        toggle.addEventListener("change", function() {
                            setTimeout(() => {
                                window.location.href = this.checked ? "../en/home.html" :
                                    "../es/inicio.html";
                            }, 300);
                        });
                        </script>
                    </label>
                </div>
                <li>
                    <button type="button" id="themeToggle" class="theme-toggle" aria-label="Cambiar tema">🌙 Dark</button>
                </li>
            </ul>
        </div>
    </nav>
    <div class="toast-wrapper">
        <?php if(isset($_SESSION['mensaje'])) { ?>
        <div id="formMessage" class="form-message <?= $_SESSION['tipoError'] ?>">
            <?= htmlspecialchars($_SESSION['mensaje']) ?>
        </div>
        <?php } ?>
    </div>
    <?php 
    if(!isset($_SESSION['rol'])) {
  ?>
    <main id="vista-visitante" class="role-banner">
        <h1>Bienvenido a Kinetix</h1>
        <p>Para acceder a nuestros servicios, inicia sesión o regístrate.</p>
        <a href="login.php" class="btn-contacto">Iniciar sesión</a>
    </main>

    <section class="section--top">
        <div class="banner--overlay">
            <h1>¡Te damos la bienvenida!</h1>
            <p>Tu espacio digital, todo en un solo lugar</p>
        </div>
    </section>
    <?php } else { ?>
    <div id="vista-usuario" class="role-banner">
        <h1>Hola, <?php echo $_SESSION['nombre'] ?? '' ?></h1>
        <p id="mensaje-rol">Bienvenido a tu panel de cliente.</p>
    </div>
    <?php }?>

    <!-- Bienvenida al cliente -->
    <section class="section--about">
        <h2>Nos alegra tenerte acá</h2>
        <p>
            ¡Hola <?php if ($_SESSION['nombre']) {echo $_SESSION['nombre'];} ?>! Queremos darte la bienvenida oficial a tu panel de cliente en Kinetix. Este es el punto de encuentro
            centralizado donde vas a poder gestionar, revisar y seguir de cerca cada etapa de la construcción de tu
            presencia digital.
            <br><br>
            Nuestro compromiso es acompañarte en cada paso del camino. Desde esta plataforma, vas a tener visibilidad
            total sobre los avances de tu sitio web, acceso directo a nuestro equipo técnico y todas las herramientas
            necesarias para que el proceso sea transparente, ágil y alineado con tus metas.
            <br><br>
            Creemos que una comunicación constante es la clave para un proyecto exitoso. Por eso, diseñamos este entorno
            para que puedas resolver dudas, solicitar ajustes y ver cómo tus ideas se transforman en una plataforma
            estable y lista para evolucionar.
            <br><br>
            Explorá las secciones de tu panel y descubrí todo lo que tenemos preparado para impulsar tu proyecto hacia
            adelante. ¡Empecemos a trabajar juntos!
        </p>
    </section>

    <!--información -->
    <section class="info-section">
        <div class="info-block">
            <div class="info-text">
                <h2>Tu Proyecto e Inventario</h2>
                <p>
                    En la sección de <strong>Inventario</strong> vas a poder ver el estado actual de tu desarrollo en
                    tiempo real.
                    Revisá la estructura del sitio, los módulos que estamos implementando y los componentes técnicos
                    asignados a tu cuenta,
                    asegurándote de que todo avance exactamente al ritmo y de la forma que tu negocio lo necesita.
                </p>
            </div>
            <div class="info-image">
                <img src="imagenes\Adaptabilidad.jpg" alt="screenshot de la pagina inventario">
            </div>
        </div>

        <div class="info-block reverse">
            <div class="info-text">
                <div class="text-content">
                    <h2>Soporte y Consultas Técnicas</h2>
                    <p>
                        ¿Surgió alguna duda o necesitás realizar un ajuste? Nuestro sistema de <strong>Tickets</strong>
                        está a tu disposición.
                        Podés abrir una solicitud en cualquier momento para que nuestro equipo técnico analice tu caso,
                        te brinde
                        respuestas rápidas y garantice que el rendimiento y la seguridad de tu plataforma se mantengan
                        impecables.
                    </p>
                </div>
            </div>
            <div class="info-image">
                <img src="imagenes\Calidad.jpg" alt="screenshot de la pagina tickets">
            </div>
        </div>

        <div class="info-block">
            <div class="info-text">
                <h2>Centro de Ayuda y FAQ</h2>
                <p>
                    Queremos que te sientas con total autonomía en tu entorno web. En la sección de <strong>FAQ</strong>
                    encontrarás guías prácticas, respuestas a dudas frecuentes sobre optimización, mantenimiento y
                    actualización de contenidos, pensadas especialmente para resolver tus consultas cotidianas de forma
                    inmediata.
                </p>
            </div>
            <div class="info-image">
                <img src="imagenes\.png" alt="algo generico no se...">
            </div>
        </div>
    </section>

    <!-- Fondo decorativo (para el futuro)-->
    <div class="fondo-personalizado"></div>


    <!-- Footer contactos -->
    <div class="footer-contacto">
        <h3>¿Tenés alguna consulta urgente?</h3>
        <a href="crear-ticket.php" class="btn-contacto">Abrir un Ticket</a>

        <!-- Footer -->
        <footer class="footer">
            <p>© 2026 Kinetix. Todos los derechos reservados. Panel exclusivo para clientes registrados.</p>
        </footer>

        <?php 
            if(isset($_SESSION['mensaje'] )){
                $_SESSION['mensaje'] = null;
                $_SESSION['tipoError'] = null;
            }
        ?>
        <script>
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    setTimeout(() => {
                        entry.target.classList.add("show");
                    }, 250);
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.25
        });
        document.querySelectorAll(".info-text").forEach(item => {
            observer.observe(item);
        });
        //DARK MODE LIGHT MODE SWITCH
        const themeToggle = document.getElementById('themeToggle');

        function mostrarElemento(id) {
            const elemento = document.getElementById(id);
            if (elemento) elemento.hidden = false;
        }


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
        </script>
</body>

</html>