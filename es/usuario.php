<?php
session_start();

require_once "../php/conexionBDD.php";
require_once "../php/image-upload.php";

$idUsuario = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
if (!$idUsuario || $idUsuario < 1) {
    $_SESSION['mensaje'] = "No tienes permiso para ver este perfil.";
    $_SESSION['tipoError'] = "error";
    header("Location: /Kinetix/es/inicio");
    exit;
}

$conexion = conectarBD();
$escape = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$consulta = mysqli_query($conexion,
    "SELECT u.id_usuario, u.nombre, u.apellido, u.email, u.fecha_creacion, u.foto_path,
            r.nombre_rol, ub.calle, c.ciudad, ub.telefono,
            (SELECT COUNT(*) FROM activo AS a WHERE a.id_usuario = u.id_usuario) AS cantidad_activos
     FROM usuario AS u
     LEFT JOIN rol AS r ON r.id_rol = u.id_rol
     LEFT JOIN ubicacion AS ub ON ub.id_ubicacion = u.id_ubicacion
     LEFT JOIN ciudad AS c ON c.id_ciudad = ub.id_ciudad
     WHERE u.id_usuario = $idUsuario LIMIT 1"
);
$usuario = $consulta ? mysqli_fetch_assoc($consulta) : false;

if (!$usuario) {
    $_SESSION['mensaje'] = "El usuario no existe.";
    $_SESSION['tipoError'] = "error";
    header("Location: /Kinetix/es/inicio");
    exit;
}
if ($usuario['nombre_rol'] === 'Administrador') {
    $_SESSION['mensaje'] = "Este usuario es un administrador, no tienes permiso para ver este perfil.";
    $_SESSION['tipoError'] = "error";
    header("Location: /Kinetix/es/inicio");
    exit;
}

$esPropio = $idUsuario === (int) ($_SESSION['idUsuario'] ?? 0);
$nombreCompleto = trim($usuario['nombre'] . ' ' . $usuario['apellido']);
$fotoPerfil = urlImagenSubida($usuario['foto_path']);
$ubicacion = $esPropio ? trim(($usuario['calle'] ?? '') . ' - ' . ($usuario['ciudad'] ?? ''), ' -') : ($usuario['ciudad'] ?? '');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <base href="/Kinetix/es/">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title data-i18n="usuario.title">Perfil | Kinetix</title>
    <link rel="icon" type="image/png" href="../imagenes/favicon.png">
    <link rel="stylesheet" href="../css/usuario.css">
    <link rel="stylesheet" href="../css/kinetix-theme.css">
    <link rel="stylesheet" href="../css/menu-layout.css">
    <script src="../js/theme.js" defer></script>
    <script src="../js/i18n.js?v=4" defer></script>
</head>
<body>
    <nav class="menu">
        <div class="menu-content">
            <a class="menu-logo" href="inicio" aria-label="Volver al inicio" data-i18n-attrs='{"aria-label":"common.back"}'>
                <img src="../imagenes/Logo1.png" alt="Logo Kinetix">
            </a>
            <div class="nav-links">
                <ul>
                    <li><a href="inicio" data-i18n="nav.inicio">Inicio</a></li>
                    <li><a href="preguntas-frecuentes" data-i18n="nav.ayuda">Ayuda</a></li>
                    <li><a href="tickets" data-i18n="nav.tickets">Tickets</a></li>
                    <li><a href="inventario" data-i18n="nav.inventario">Inventario</a></li>
                    <li><a href="usuario/<?= $idUsuario ?>" class="active" aria-current="page" data-i18n="nav.mi_cuenta">Mi Cuenta</a></li>
                </ul>
            </div>
            <div class="nav-actions">
                <ul>
                    <li><a class="logout-button" href="../php/logout"><span class="logout-sign" aria-hidden="true"></span><span class="logout-text" data-i18n="nav.cerrar_sesion">Cerrar sesión</span></a></li>
                    <?php if (isset($_SESSION['idUsuario'])) { ?>
                    <li><button type="button" id="themeToggle" class="theme-toggle" aria-label="Cambiar tema">🌙</button></li>
                    <?php } ?>
                    <li><button type="button" id="langToggle" class="switch" data-i18n-toggle aria-label="Cambiar idioma">
                        <span class="lang left">ES</span><span class="lang right">EN</span><span class="slider"></span>
                    </button></li>
                </ul>
            </div>
        </div>
    </nav>

    <?php if (isset($_SESSION['mensaje'])) { ?>
        <div class="toast-wrapper">
            <div class="form-message <?= $escape($_SESSION['tipoError'] ?? 'error') ?>" role="status"><?= $escape($_SESSION['mensaje']) ?></div>
        </div>
        <?php unset($_SESSION['mensaje'], $_SESSION['tipoError']); ?>
    <?php } ?>

    <main class="perfil-page">
        <section class="perfil-header">
            <div>
                <p class="eyebrow" data-i18n="usuario.eyebrow">Cuenta personal</p>
                <h1 data-i18n="usuario.profile_heading">Perfil de usuario</h1>
                <p data-i18n="usuario.p">Consultá la información asociada a esta cuenta de Kinetix.</p>
            </div>
            <?php if ($esPropio) { ?>
                <a class="perfil-action" href="usuario-config" data-i18n="usuario.btn_config">Configurar perfil</a>
            <?php } ?>
        </section>

        <section class="perfil-card" aria-labelledby="perfil-name">
            <?php if ($fotoPerfil !== null) { ?>
                <img class="perfil-avatar" src="<?= $escape($fotoPerfil) ?>" alt="Foto de <?= $escape($nombreCompleto) ?>">
            <?php } else { ?>
                <div class="perfil-avatar perfil-avatar-placeholder" aria-hidden="true"><?= $escape(strtoupper(substr($usuario['nombre'], 0, 1))) ?></div>
            <?php } ?>
            <div class="perfil-summary">
                <p class="perfil-label" data-i18n="usuario.user_label" data-i18n-params='{"id":"<?= (int) $usuario['id_usuario'] ?>"}'>Usuario #<?= (int) $usuario['id_usuario'] ?></p>
                <h2 id="perfil-name"><?= $escape($nombreCompleto) ?></h2>
                <p class="perfil-role" data-i18n-db><?= $escape($usuario['nombre_rol']) ?></p>
            </div>
            <div class="perfil-stats">
                <div><strong><?= (int) $usuario['cantidad_activos'] ?></strong><span data-i18n="usuario.detail.items">Activos asignados</span></div>
                <div><strong><?= $escape(date('d/m/Y', strtotime($usuario['fecha_creacion']))) ?></strong><span data-i18n="usuario.detail.fecha">Fecha de registro</span></div>
            </div>
        </section>

        <section class="details-section" aria-labelledby="details-title">
            <div class="section-heading">
                <p class="eyebrow" data-i18n="usuario.datos.eyebrow">Información registrada</p>
                <h2 id="details-title" data-i18n="usuario.datos.h2">Datos de la cuenta</h2>
            </div>
            <div class="details-grid">
                <article class="detail-item">
                    <span data-i18n="usuario.detail.nombre">Nombre completo</span>
                    <strong><?= $escape($nombreCompleto) ?></strong>
                </article>
                <article class="detail-item">
                    <span data-i18n="usuario.detail.rol">Rol</span>
                    <strong data-i18n-db><?= $escape($usuario['nombre_rol']) ?></strong>
                </article>
                <?php if ($ubicacion !== '') { ?>
                    <article class="detail-item">
                        <span data-i18n="usuario.detail.ubicacion">Ubicación</span>
                        <strong><?= $escape($ubicacion) ?></strong>
                    </article>
                <?php } ?>
                <?php if ($esPropio) { ?>
                    <article class="detail-item">
                        <span data-i18n="usuario.detail.email">Correo electrónico</span>
                        <strong><?= $escape($usuario['email']) ?></strong>
                    </article>
                    <?php if (!empty($usuario['telefono'])) { ?>
                        <article class="detail-item">
                            <span data-i18n="usuario.detail.phone">Teléfono</span>
                            <strong><?= $escape($usuario['telefono']) ?></strong>
                        </article>
                    <?php } ?>
                    <article class="detail-item">
                        <span data-i18n="usuario.detail.id">Número de cuenta</span>
                        <strong>#<?= (int) $usuario['id_usuario'] ?></strong>
                    </article>
                <?php } ?>
            </div>
        </section>
    </main>
    <footer class="footer"><p data-i18n="footer.copyright">© 2026 Kinetix. Todos los derechos reservados.</p></footer>
</body>
</html>
