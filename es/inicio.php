<?php 
session_start();
$idUsuario = (int) ($_SESSION['idUsuario'] ?? 0);
$rol = (int) ($_SESSION['rol'] ?? 0);
$nombre = htmlspecialchars((string) ($_SESSION['nombre'] ?? ''), ENT_QUOTES, 'UTF-8');
?>

<!DOCTYPE html>
<html lang="es" data-lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title data-i18n="inicio.title">Kinetix - Panel de Cliente</title>
    <!-- estilo -->
    <link rel="icon" type="image/png" href="../imagenes/favicon.png">
    <link rel="stylesheet" href="../css/kinetix-theme.css">
    <link rel="stylesheet" href="../css/pagina-inicio.css">
    <link rel="stylesheet" href="../css/menu-layout.css">
    <script src="../js/theme.js" defer></script>
    <script src="../js/i18n.js?v=4" defer></script>
</head>

<body>
    <nav class="menu">
        <div class="menu-content">
            <a class="menu-logo" href="inicio" aria-label="Kinetix, inicio">
                <img src="../imagenes/Logo1.png" alt="Kinetix Software">
            </a>

            <div class="nav-links">
                <ul>
                    <?php if ($rol === 1 || $rol === 2) { ?>
                    <li><a href="inicio" class="active" aria-current="page" data-i18n="nav.inicio">Inicio</a></li>
                    <li><a href="preguntas-frecuentes" data-i18n="nav.ayuda">Ayuda</a></li>
                    <li><a href="tickets" data-i18n="nav.tickets">Tickets</a></li>
                    <li><a href="inventario" data-i18n="nav.inventario">Inventario</a></li>
                    <li><a href="usuario/<?= $idUsuario ?>" data-i18n="nav.mi_cuenta">Mi cuenta</a></li>
                    <?php } elseif ($rol === 3) { ?>
                    <li><a href="inicio" class="active" aria-current="page" data-i18n="nav.inicio">Inicio</a></li>
                    <li><a href="tickets" data-i18n="nav.tickets">Tickets</a></li>
                    <li><a href="admin/panel-admin" data-i18n="nav.administracion">Administración</a></li>
                    <li><a href="admin/informes-admin" data-i18n="nav.informes">Informes</a></li>
                    <?php } else { ?>
                    <li><a href="login" data-i18n="nav.iniciar_sesion">Iniciar sesión</a></li>
                    <li><a href="registrarse" data-i18n="nav.crear_cuenta">Crear cuenta</a></li>
                    <?php } ?>
                </ul>
            </div>

            <div class="nav-actions">
                <ul>
                    <?php if ($rol === 1 || $rol === 2 || $rol === 3) { ?>
                    <li><a class="logout-button" href="../php/logout"><span class="logout-sign" aria-hidden="true"></span><span class="logout-text" data-i18n="nav.cerrar_sesion">Cerrar sesión</span></a></li>
                    <?php } ?>
                    <li class="language-switch">
                        <button type="button" id="langToggle" class="switch" data-i18n-toggle aria-label="Cambiar a inglés">
                            <span class="lang left">ES</span>
                            <span class="lang right">EN</span>
                            <span class="slider"></span>
                        </button>
                    </li>
                    <li><button type="button" id="themeToggle" class="theme-toggle" aria-label="Cambiar tema">🌙</button></li>
                </ul>
            </div>
        </div>
    </nav>

    <?php if (isset($_SESSION['mensaje'])) { ?>
    <div class="toast-wrapper">
        <div id="formMessage" class="form-message <?= $_SESSION['tipoError'] ?>">
            <?= $_SESSION['mensaje']?>
        </div>
    </div>
    <?php unset($_SESSION['mensaje'], $_SESSION['tipoError']); } ?>

    <main class="home-shell">
        <section class="home-intro">
            <div class="home-intro-copy">
                <p class="home-eyebrow" data-i18n="inicio.eyebrow">KINETIX <span>·</span> TECNOLOGÍA QUE ACOMPAÑA</p>
                <?php if ($rol === 1 || $rol === 2) { ?>
                <h1 data-i18n-html="true" data-i18n-params='{"nombre":"<?= $nombre ?>"}' data-i18n="inicio.h1_cliente">Hola, <?= $nombre ?>.</h1>
                <p class="home-lead" data-i18n="inicio.lead_cliente">Todo lo que necesitás para seguir tu proyecto, resolver consultas y avanzar con claridad.</p>
                <a class="home-primary-action home-ticket-action" href="tickets" data-i18n="inicio.btn_tickets">Ir a mis tickets <span aria-hidden="true">→</span></a>
                <?php } elseif ($rol === 3) { ?>
                <h1 data-i18n="inicio.h1_admin">Ideas en marcha. Gestión en control.</h1>
                <p class="home-lead" data-i18n="inicio.lead_admin">Administrá Kinetix y encontrá en un mismo lugar las herramientas para mantener todo en movimiento.</p>
                <a class="home-primary-action home-ticket-action" href="admin/panel-admin" data-i18n="inicio.btn_admin">Abrir administración <span aria-hidden="true">→</span></a>
                <?php } else { ?>
                <h1 data-i18n="inicio.h1_invitado">La tecnología también puede sentirse simple.</h1>
                <p class="home-lead" data-i18n="inicio.lead_invitado">Conectamos tus proyectos con las personas, los recursos y el soporte que necesitás para avanzar.</p>
                <div class="home-intro-actions">
                    <a class="home-primary-action" href="registrarse" data-i18n="inicio.btn_registrarse">Empezar ahora <span aria-hidden="true">→</span></a>
                    <a class="home-secondary-action" href="preguntas-frecuentes" data-i18n="inicio.btn_ayuda">Conocer más</a>
                </div>
                <?php } ?>
            </div>
            <div class="home-intro-mark" aria-hidden="true">
                <span class="mark-orbit mark-orbit-one"></span>
                <span class="mark-orbit mark-orbit-two"></span>
                <span class="mark-node">K</span>
                <span class="mark-float mark-float-top"><b>01</b> <span data-i18n="inicio.float.connect">Conectá tus ideas</span></span>
                <span class="mark-float mark-float-bottom"><b>02</b> <span data-i18n="inicio.float.advance">Hacé que avancen</span> <i></i></span>
            </div>
        </section>

        <section class="home-showcase" aria-labelledby="home-showcase-title">
            <div class="home-showcase-heading">
                <p class="home-eyebrow">VISTA DE DEMOSTRACIÓN</p>
                <h2 id="home-showcase-title">Kinetix en acción</h2>
                <p>Capturas reales del sistema, con datos ficticios de usuarios, tickets, mensajes, activos y solicitudes.</p>
            </div>
            <div class="showcase-slideshow" id="showcaseSlideshow">
                <?php
                $slides = [
                    ['img' => 'Screenshot_Inventario.png', 'title' => 'Inventario', 'desc' => 'Consultá los activos registrados, su categoría, marca y estado.'],
                    ['img' => 'Screenshot_Tickets.png', 'title' => 'Tickets', 'desc' => 'Seguimiento de consultas con historial y chat.'],
                    ['img' => 'Screenshot_Perfil.png', 'title' => 'Perfil', 'desc' => 'Datos y configuración de tu cuenta.'],
                    ['img' => 'Screenshot_Ayuda.png', 'title' => 'Ayuda', 'desc' => 'Centro de preguntas frecuentes.'],
                ];
                foreach ($slides as $i => $s) { ?>
                <figure class="showcase-slide <?= $i === 0 ? 'active' : '' ?>" data-index="<?= $i ?>">
                    <img src="../imagenes/<?= $s['img'] ?>" alt="<?= $s['title'] ?>" loading="lazy">
                    <figcaption>
                        <strong><?= $s['title'] ?></strong>
                        <span><?= $s['desc'] ?></span>
                    </figcaption>
                </figure>
                <?php } ?>
                <button type="button" class="showcase-prev" aria-label="Anterior">‹</button>
                <button type="button" class="showcase-next" aria-label="Siguiente">›</button>
                <div class="showcase-dots">
                    <?php foreach ($slides as $i => $s) { ?>
                    <button type="button" class="showcase-dot <?= $i === 0 ? 'active' : '' ?>" data-index="<?= $i ?>" aria-label="Ir a <?= $s['title'] ?>"></button>
                    <?php } ?>
                </div>
            </div>
            <div class="showcase-modal" id="showcaseModal" hidden>
                <button type="button" class="showcase-modal-close" aria-label="Cerrar">×</button>
                <img src="" alt="" id="showcaseModalImg">
            </div>
            <p class="home-showcase-caption">Las cuentas, equipos, mensajes y solicitudes visibles son ficticios y se crearon para esta demostración.</p>
        </section>
        <script>
        (function(){
            const slides = document.querySelectorAll('#showcaseSlideshow .showcase-slide');
            const dots = document.querySelectorAll('#showcaseSlideshow .showcase-dot');
            let i = 0;
            function show(n){ i=(n+slides.length)%slides.length; slides.forEach((s,k)=>s.classList.toggle('active',k===i)); dots.forEach((d,k)=>d.classList.toggle('active',k===i)); }
            document.querySelector('#showcaseSlideshow .showcase-prev').addEventListener('click',()=>show(i-1));
            document.querySelector('#showcaseSlideshow .showcase-next').addEventListener('click',()=>show(i+1));
            dots.forEach(d=>d.addEventListener('click',()=>show(+d.dataset.index)));
            setInterval(()=>show(i+1),5000);
            const modal=document.getElementById('showcaseModal');
            const modalImg=document.getElementById('showcaseModalImg');
            slides.forEach(s=>s.querySelector('img').addEventListener('click',()=>{modalImg.src=s.querySelector('img').src;modalImg.alt=s.querySelector('img').alt;modal.hidden=false;}));
            modal.querySelector('.showcase-modal-close').addEventListener('click',()=>modal.hidden=true);
            modal.addEventListener('click',e=>{if(e.target===modal)modal.hidden=true;});
        })();
        </script>

        <section class="home-overview" aria-labelledby="home-overview-title">
            <div class="home-overview-intro">
                <p class="home-eyebrow" data-i18n="inicio.overview.eyebrow">UNA PLATAFORMA PARA EL DÍA A DÍA</p>
                <h2 id="home-overview-title" data-i18n-html="true" data-i18n="inicio.overview.h1">Gestioná tus activos y el soporte <span>desde un mismo lugar.</span></h2>
                <p data-i18n="inicio.overview.p">Kinetix reúne herramientas para consultar los recursos tecnológicos, pedir asistencia y mantener cada solicitud organizada. Así, usuarios y equipo técnico pueden dar seguimiento a las necesidades del trabajo diario.</p>
            </div>
            <div class="home-overview-grid">
                <article class="home-overview-card">
                    <span class="home-overview-index">01 <i data-i18n="inicio.overview.inventory_index">INVENTARIO</i></span>
                    <h3 data-i18n="inicio.overview.inventory.h3">Información de tus activos</h3>
                    <p data-i18n="inicio.overview.inventory.p">Consultá los equipos registrados, su categoría, marca y estado. Según el acceso de tu cuenta, también podés revisar los activos asociados a vos.</p>
                    <a href="<?= ($rol === 1 || $rol === 2) ? 'inventario' : 'login' ?>" data-i18n="inicio.overview.inventory.link">Explorar inventario →</a>
                </article>
                <article class="home-overview-card">
                    <span class="home-overview-index">02 <i data-i18n="inicio.overview.support_index">SOPORTE</i></span>
                    <h3 data-i18n="inicio.overview.support.h3">Consultas con seguimiento</h3>
                    <p data-i18n="inicio.overview.support.p">Creá tickets con una descripción, categoría y prioridad. Seguí su estado, consultá el historial y mantené la conversación con el equipo técnico.</p>
                    <a href="<?= ($rol === 1 || $rol === 2) ? 'tickets' : 'preguntas-frecuentes' ?>" data-i18n="<?= ($rol === 1 || $rol === 2) ? 'inicio.overview.support.link_user' : 'inicio.overview.support.link_guest' ?>"><?= ($rol === 1 || $rol === 2) ? 'Ir a soporte' : 'Visitar el centro de ayuda' ?> →</a>
                </article>
                <article class="home-overview-card">
                    <span class="home-overview-index">03 <i data-i18n="inicio.overview.services">SERVICIOS</i></span>
                    <h3 data-i18n="inicio.overview.services.h3">Atención para cada equipo</h3>
                    <p data-i18n="inicio.overview.services.p">Cuando un activo lo necesita, podés solicitar mantenimiento, reparación, revisión o instalación e indicar el nivel de prioridad.</p>
                    <a href="<?= ($rol === 1 || $rol === 2) ? 'inventario' : 'registrarse' ?>" data-i18n="<?= ($rol === 1 || $rol === 2) ? 'inicio.overview.services.link_user' : 'inicio.overview.services.link_guest' ?>"><?= ($rol === 1 || $rol === 2) ? 'Ver mis activos' : 'Crear una cuenta' ?> →</a>
                </article>
            </div>
        </section>

        <section class="home-process" aria-labelledby="home-process-title">
            <div class="home-process-heading">
                <p class="home-eyebrow" data-i18n="inicio.process.eyebrow">UN FLUJO CLARO</p>
                <h2 id="home-process-title" data-i18n="inicio.process.h1">De la necesidad a la solución.</h2>
                <p data-i18n="inicio.process.p">Las herramientas de Kinetix ayudan a que cada pedido tenga contexto y un lugar donde continuar.</p>
            </div>
            <ol class="home-process-steps">
                <li>
                    <span class="home-process-number">01</span>
                    <div><h3 data-i18n="inicio.process.step1.h3">Encontrá el equipo</h3><p data-i18n="inicio.process.step1.p">Buscá el activo en el inventario y revisá su información disponible.</p></div>
                </li>
                <li>
                    <span class="home-process-number">02</span>
                    <div><h3 data-i18n="inicio.process.step2.h3">Contanos qué necesitás</h3><p data-i18n="inicio.process.step2.p">Creá un ticket o iniciá una solicitud de servicio con los detalles del caso.</p></div>
                </li>
                <li>
                    <span class="home-process-number">03</span>
                    <div><h3 data-i18n="inicio.process.step3.h3">Seguí el avance</h3><p data-i18n="inicio.process.step3.p">Consultá el estado y el historial para saber cómo continúa la atención.</p></div>
                </li>
            </ol>
        </section>

        <section class="home-links" id="herramientas" aria-labelledby="home-links-title">
            <div class="home-section-heading">
                <div>
                    <p class="home-eyebrow" data-i18n="inicio.links.eyebrow">ACCESOS DIRECTOS</p>
                    <h2 id="home-links-title" data-i18n="inicio.links.h1">¿A dónde vamos?</h2>
                </div>
                <span class="home-section-note" data-i18n="inicio.links.note">Tu espacio de trabajo</span>
            </div>
            <div class="home-link-grid">
                <?php if ($rol === 1 || $rol === 2) { ?>
                <a class="home-link-card" href="inventario">
                    <span class="home-link-index">01</span><span class="home-link-icon" aria-hidden="true">▦</span>
                    <h3 data-i18n="inicio.card.inventario.h3">Inventario</h3><p data-i18n="inicio.card.inventario.p">Consultá los recursos vinculados a tu proyecto.</p><span class="home-link-arrow" aria-hidden="true">↗</span>
                </a>
                <a class="home-link-card" href="tickets">
                    <span class="home-link-index">02</span><span class="home-link-icon" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M9 12C9 12.5523 8.55228 13 8 13C7.44772 13 7 12.5523 7 12C7 11.4477 7.44772 11 8 11C8.55228 11 9 11.4477 9 12Z" fill="#17988f"></path><path d="M13 12C13 12.5523 12.5523 13 12 13C11.4477 13 11 12.5523 11 12C11 11.4477 11.4477 11 12 11C12.5523 11 13 11.4477 13 12Z" fill="#17988f"></path><path d="M17 12C17 12.5523 16.5523 13 16 13C15.4477 13 15 12.5523 15 12C15 11.4477 15.4477 11 16 11C16.5523 11 17 11.4477 17 12Z" fill="#17988f"></path><path fill-rule="evenodd" clip-rule="evenodd" d="M22.75 12C22.75 6.06294 17.9371 1.25 12 1.25C6.06294 1.25 1.25 6.06294 1.25 12C1.25 13.7183 1.65371 15.3445 2.37213 16.7869C2.47933 17.0021 2.50208 17.2219 2.4526 17.4068L1.857 19.6328C1.44927 21.1566 2.84337 22.5507 4.3672 22.143L6.59324 21.5474C6.77814 21.4979 6.99791 21.5207 7.21315 21.6279C8.65553 22.3463 10.2817 22.75 12 22.75C17.9371 22.75 22.75 17.9371 22.75 12ZM12 2.75C17.1086 2.75 21.25 6.89137 21.25 12C21.25 17.1086 17.1086 21.25 12 21.25C10.5189 21.25 9.12121 20.9025 7.88191 20.2852C7.38451 20.0375 6.78973 19.9421 6.20553 20.0984L3.97949 20.694C3.57066 20.8034 3.19663 20.4293 3.30602 20.0205L3.90163 17.7945C4.05794 17.2103 3.96254 16.6155 3.7148 16.1181C3.09752 14.8788 2.75 13.4811 2.75 12C2.75 6.89137 6.89137 2.75 12 2.75Z" fill="#17988f"></path></svg></span>
                    <h3 data-i18n="inicio.card.tickets.h3">Soporte técnico</h3><p data-i18n="inicio.card.tickets.p">Seguí tus consultas y conversá con el equipo.</p><span class="home-link-arrow" aria-hidden="true">↗</span>
                </a>
                <a class="home-link-card" href="preguntas-frecuentes">
                    <span class="home-link-index">03</span><span class="home-link-icon" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 2.75C6.89137 2.75 2.75 6.89137 2.75 12C2.75 17.1086 6.89137 21.25 12 21.25C17.1086 21.25 21.25 17.1086 21.25 12C21.25 6.89137 17.1086 2.75 12 2.75ZM1.25 12C1.25 6.06294 6.06294 1.25 12 1.25C17.9371 1.25 22.75 6.06294 22.75 12C22.75 17.9371 17.9371 22.75 12 22.75C6.06294 22.75 1.25 17.9371 1.25 12ZM12 7.75C11.3787 7.75 10.875 8.25368 10.875 8.875C10.875 9.28921 10.5392 9.625 10.125 9.625C9.71079 9.625 9.375 9.28921 9.375 8.875C9.375 7.42525 10.5503 6.25 12 6.25C13.4497 6.25 14.625 7.42525 14.625 8.875C14.625 9.83834 14.1056 10.6796 13.3353 11.1354C13.1385 11.2518 12.9761 11.3789 12.8703 11.5036C12.7675 11.6246 12.75 11.7036 12.75 11.75V13C12.75 13.4142 12.4142 13.75 12 13.75C11.5858 13.75 11.25 13.4142 11.25 13V11.75C11.25 11.2441 11.4715 10.8336 11.7266 10.533C11.9786 10.236 12.2929 10.0092 12.5715 9.84439C12.9044 9.64739 13.125 9.28655 13.125 8.875C13.125 8.25368 12.6213 7.75 12 7.75ZM12 17C12.5523 17 13 16.5523 13 16C13 15.4477 12.5523 15 12 15C11.4477 15 11 15.4477 11 16C11 16.5523 11.4477 17 12 17Z" fill="#17988f"></path></svg></span>
                    <h3 data-i18n="inicio.card.ayuda.h3">Centro de ayuda</h3><p data-i18n="inicio.card.ayuda.p">Encontrá respuestas a preguntas frecuentes.</p><span class="home-link-arrow" aria-hidden="true">↗</span>
                </a>
                <a class="home-link-card" href="usuario/<?= $idUsuario ?>">
                    <span class="home-link-index">04</span><span class="home-link-icon" aria-hidden="true">◎</span>
                    <h3 data-i18n="inicio.card.cuenta.h3">Mi cuenta</h3><p data-i18n="inicio.card.cuenta.p">Revisá los datos de tu perfil.</p><span class="home-link-arrow" aria-hidden="true">↗</span>
                </a>
                <?php } elseif ($rol === 3) { ?>
                <a class="home-link-card" href="admin/panel-admin">
                    <span class="home-link-index">01</span><span class="home-link-icon" aria-hidden="true">▦</span>
                    <h3 data-i18n="inicio.card.admin.h3">Administración</h3><p data-i18n="inicio.card.admin.p">Gestioná tablas y registros de la plataforma.</p><span class="home-link-arrow" aria-hidden="true">↗</span>
                </a>
                <a class="home-link-card" href="admin/informes-admin">
                    <span class="home-link-index">02</span><span class="home-link-icon" aria-hidden="true">⌁</span>
                    <h3 data-i18n="inicio.card.informes.h3">Informes</h3><p data-i18n="inicio.card.informes.p">Consultá las métricas y el resumen del sistema.</p><span class="home-link-arrow" aria-hidden="true">↗</span>
                </a>
                <?php } else { ?>
                <a class="home-link-card" href="preguntas-frecuentes">
                    <span class="home-link-index">01</span><span class="home-link-icon" aria-hidden="true">?</span>
                    <h3 data-i18n="inicio.card.ayuda.h3">Centro de ayuda</h3><p data-i18n="inicio.card.ayuda.guest_p">Explorá respuestas y orientación para empezar.</p><span class="home-link-arrow" aria-hidden="true">↗</span>
                </a>
                <a class="home-link-card" href="login">
                    <span class="home-link-index">02</span><span class="home-link-icon" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 1.25C9.37665 1.25 7.25 3.37665 7.25 6C7.25 8.62335 9.37665 10.75 12 10.75C14.6234 10.75 16.75 8.62335 16.75 6C16.75 3.37665 14.6234 1.25 12 1.25ZM8.75 6C8.75 4.20507 10.2051 2.75 12 2.75C13.7949 2.75 15.25 4.20507 15.25 6C15.25 7.79493 13.7949 9.25 12 9.25C10.2051 9.25 8.75 7.79493 8.75 6Z" fill="#17988f"></path><path d="M18.8555 16.5729C19.1527 16.8614 19.1599 17.3362 18.8714 17.6334L17.0382 19.5223C16.8901 19.6749 16.6842 19.7575 16.4717 19.7495C16.2591 19.7414 16.06 19.6436 15.9239 19.4802L15.0904 18.4802C14.8252 18.162 14.8681 17.6891 15.1863 17.4239C15.5045 17.1587 15.9774 17.2016 16.2426 17.5198L16.5424 17.8794L17.795 16.5888C18.0834 16.2915 18.5583 16.2844 18.8555 16.5729Z" fill="#17988f"></path><path fill-rule="evenodd" clip-rule="evenodd" d="M13.9107 21.6083C13.2991 21.7009 12.6587 21.75 12 21.75C9.96067 21.75 8.07752 21.2792 6.67815 20.4796C5.3 19.6921 4.25 18.4899 4.25 17C4.25 15.5101 5.3 14.3079 6.67815 13.5204C8.07752 12.7208 9.96067 12.25 12 12.25C13.8045 12.25 15.4825 12.6184 16.8117 13.2537C16.8742 13.2512 16.937 13.25 17 13.25C19.6234 13.25 21.75 15.3766 21.75 18C21.75 20.6234 19.6234 22.75 17 22.75C15.8204 22.75 14.7413 22.32 13.9107 21.6083ZM13.75 18C13.75 16.2051 15.2051 14.75 17 14.75C18.7949 14.75 20.25 16.2051 20.25 18C20.25 19.7949 18.7949 21.25 17 21.25C15.2051 21.25 13.75 19.7949 13.75 18ZM14.4176 14.0127C13.113 14.8593 12.25 16.3289 12.25 18C12.25 18.8029 12.4492 19.5593 12.8009 20.2224C12.5388 20.2406 12.2715 20.25 12 20.25C10.1733 20.25 8.55649 19.8253 7.42236 19.1772C6.26701 18.517 5.75 17.7193 5.75 17C5.75 16.2807 6.26701 15.483 7.42236 14.8228C8.55649 14.1747 10.1733 13.75 12 13.75C12.8611 13.75 13.6767 13.8444 14.4176 14.0127Z" fill="#17988f"></path></svg></span>
                    <h3 data-i18n="nav.iniciar_sesion">Iniciar sesión</h3><p data-i18n="inicio.guest.login_p">Ingresá para acceder a tu espacio Kinetix.</p><span class="home-link-arrow" aria-hidden="true">↗</span>
                </a>
                <a class="home-link-card" href="registrarse">
                    <span class="home-link-index">03</span><span class="home-link-icon" aria-hidden="true">＋</span>
                    <h3 data-i18n="nav.crear_cuenta">Crear cuenta</h3><p data-i18n="inicio.guest.register_p">Registrate para acceder a tu espacio Kinetix.</p><span class="home-link-arrow" aria-hidden="true">↗</span>
                </a>
                <?php } ?>
            </div>
        </section>

        <section class="home-note">
            <span class="home-note-rule" aria-hidden="true"></span>
            <div><p class="home-eyebrow" data-i18n="inicio.note.eyebrow">KINETIX SOFTWARE</p><h2 data-i18n="inicio.note.h2">La tecnología funciona mejor cuando todo está conectado.</h2></div>
            <p data-i18n="inicio.note.p">Desde el seguimiento del proyecto hasta el soporte diario, encontrá cada herramienta donde la necesitás.</p>
        </section>

        <?php if ($rol !== 1 && $rol !== 2 && $rol !== 3) { ?>
        <section class="home-cta" aria-labelledby="home-cta-title">
            <div>
                <p class="home-eyebrow" data-i18n="inicio.cta.eyebrow">EL PRÓXIMO PASO EMPIEZA ACÁ</p>
                <h2 id="home-cta-title" data-i18n="inicio.cta.h2">¿Lo ponemos en marcha?</h2>
            </div>
            <a class="home-primary-action" href="registrarse"><span data-i18n="inicio.cta_registrarse">Crear mi cuenta</span> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path fill-rule="evenodd" clip-rule="evenodd" d="M13.4697 5.46967C13.7626 5.17678 14.2374 5.17678 14.5303 5.46967L20.5303 11.4697C20.8232 11.7626 20.8232 12.2374 20.5303 12.5303L14.5303 18.5303C14.2374 18.8232 13.7626 18.8232 13.4697 18.5303C13.1768 18.2374 13.1768 17.7626 13.4697 17.4697L18.1893 12.75H4C3.58579 12.75 3.25 12.4142 3.25 12C3.25 11.5858 3.58579 11.25 4 11.25H18.1893L13.4697 6.53033C13.1768 6.23744 13.1768 5.76256 13.4697 5.46967Z" fill="currentColor"></path></svg></a>
        </section>
        <?php } ?>
    </main>

    <footer class="home-footer">
        <span data-i18n="footer.copyright">© 2026 Kinetix</span><span data-i18n="footer.software">Software para avanzar.</span>
    </footer>

</body>
</html>
