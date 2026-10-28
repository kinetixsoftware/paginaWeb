<?php
session_start();

require_once "../php/conexionBDD.php";
require_once "../php/image-upload.php";

$idUsuario = (int) ($_SESSION['idUsuario'] ?? 0);
if ($idUsuario <= 0) {
    $_SESSION['mensaje'] = "Necesitas iniciar sesión para configurar tu cuenta.";
    $_SESSION['tipoError'] = "error";
    header("Location: inicio");
    exit;
}

$conexion = conectarBD();
$escape = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$consulta = mysqli_query($conexion,
            "SELECT u.nombre, u.apellido, u.password, u.email, u.foto_path, u.id_ubicacion, r.nombre_rol
            FROM usuario AS u
            LEFT JOIN rol AS r ON r.id_rol = u.id_rol
            WHERE u.id_usuario = $idUsuario LIMIT 1");
$datos = $consulta ? mysqli_fetch_assoc($consulta) : false;

if (!$datos) {
    $_SESSION['mensaje'] = "No se pudo cargar la configuración de la cuenta.";
    $_SESSION['tipoError'] = "error";
    header("Location: inicio");
    exit;
}

$tab = 'Cuenta';
$mensaje = '';
$claveMensaje = '';
$tipoMensaje = '';

$resultadoUbicaciones = mysqli_query(
    $conexion,
    "SELECT u.id_ubicacion, u.calle, c.ciudad
     FROM ubicacion AS u
     LEFT JOIN ciudad AS c ON c.id_ciudad = u.id_ciudad
     ORDER BY c.ciudad, u.calle"
);
if (!$resultadoUbicaciones) {
    throw new RuntimeException('No se pudieron cargar las ubicaciones: ' . mysqli_error($conexion));
}
$ubicaciones = [];
while ($ubicacion = mysqli_fetch_assoc($resultadoUbicaciones)) {
    $ubicaciones[] = $ubicacion;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    $tabEnviado = $_POST['tab'] ?? '';

    if ($accion === 'verTab') {
        $tab = in_array($tabEnviado, ['Cuenta', 'Contraseña', 'Ubicación'], true) ? $tabEnviado : 'Cuenta';
    } elseif ($accion === 'cambiarDatos') {
        $tab = 'Cuenta';
        $nombre = trim(is_string($_POST['nombre'] ?? null) ? $_POST['nombre'] : '');
        $apellido = trim(is_string($_POST['apellido'] ?? null) ? $_POST['apellido'] : '');
        $email = trim(is_string($_POST['email'] ?? null) ? $_POST['email'] : '');
        $nombreCaracteres = preg_match_all('/./us', $nombre);
        $apellidoCaracteres = preg_match_all('/./us', $apellido);

        if ($nombre === '' || $apellido === '' || $nombreCaracteres === false || $nombreCaracteres > 50 || $apellidoCaracteres === false || $apellidoCaracteres > 100 || strlen($email) > 255 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $mensaje = 'Ingresa nombre, apellido y un correo electrónico válido.';
            $claveMensaje = 'config.error_data';
            $tipoMensaje = 'error';
            $datos['nombre'] = $nombre;
            $datos['apellido'] = $apellido;
            $datos['email'] = $email;
        } else {
            $nombreSQL = mysqli_real_escape_string($conexion, $nombre);
            $apellidoSQL = mysqli_real_escape_string($conexion, $apellido);
            $emailSQL = mysqli_real_escape_string($conexion, $email);
            $actualizado = false;
            $errorActualizacion = 0;
            $nuevaFoto = null;
            $consultaIniciada = false;
            $errorFoto = '';
            try {
                $nuevaFoto = guardarImagenSubida($_FILES['foto'] ?? [], 'usuarios');
                $fotoSQL = $nuevaFoto === null ? '' : ", foto_path = '" . mysqli_real_escape_string($conexion, $nuevaFoto) . "'";
                $consultaIniciada = true;
                $actualizado = mysqli_query($conexion,
                    "UPDATE usuario SET nombre = '$nombreSQL', apellido = '$apellidoSQL', email = '$emailSQL'$fotoSQL
                     WHERE id_usuario = $idUsuario"
                );
            } catch (Throwable $error) {
                $errorActualizacion = (int) $error->getCode();
                if (!$consultaIniciada) {
                    $errorFoto = $error->getMessage();
                }
                error_log('Account settings update failed: ' . $error->getMessage());
            }

            if ($actualizado) {
                if ($nuevaFoto !== null) {
                    try {
                        eliminarImagenSubida($datos['foto_path'] ?? null);
                    } catch (Throwable $error) {
                        error_log('Previous profile image cleanup failed: ' . $error->getMessage());
                    }
                    $datos['foto_path'] = $nuevaFoto;
                }
                $mensaje = 'Los datos de tu cuenta se actualizaron correctamente.';
                $claveMensaje = 'config.success_account';
                $tipoMensaje = 'success';
                $datos['nombre'] = $nombre;
                $datos['apellido'] = $apellido;
                $datos['email'] = $email;
            } else {
                if ($nuevaFoto !== null) {
                    try {
                        eliminarImagenSubida($nuevaFoto);
                    } catch (Throwable $error) {
                        error_log('New profile image cleanup failed: ' . $error->getMessage());
                    }
                }
                $mensaje = $errorFoto !== ''
                    ? $errorFoto
                    : ((($errorActualizacion ?: mysqli_errno($conexion)) === 1062)
                        ? 'Ese correo electrónico ya está asociado a otra cuenta.'
                        : 'No se pudieron guardar los cambios. Inténtalo nuevamente.');
                $claveMensaje = $errorFoto !== ''
                    ? ''
                    : ((($errorActualizacion ?: mysqli_errno($conexion)) === 1062) ? 'config.error_email_taken' : 'config.error_save');
                $tipoMensaje = 'error';
                $datos['nombre'] = $nombre;
                $datos['apellido'] = $apellido;
                $datos['email'] = $email;
            }
        }
    } elseif ($accion === 'cambiarUbicacion') {
        $tab = 'Ubicación';
        $ubicacionEnviada = $_POST['id_ubicacion'] ?? '';
        $idUbicacion = $ubicacionEnviada === '' ? null : filter_var($ubicacionEnviada, FILTER_VALIDATE_INT);

        $idsUbicaciones = array_map('intval', array_column($ubicaciones, 'id_ubicacion'));
        if ($ubicacionEnviada !== '' && ($idUbicacion === false || !in_array((int) $idUbicacion, $idsUbicaciones, true))) {
            $mensaje = 'Seleccioná una ubicación válida.';
            $claveMensaje = 'config.error_location';
            $tipoMensaje = 'error';
        } else {
            $ubicacionSQL = $idUbicacion === null ? 'NULL' : (string) (int) $idUbicacion;
            try {
                $actualizado = mysqli_query(
                    $conexion,
                    "UPDATE usuario SET id_ubicacion = $ubicacionSQL WHERE id_usuario = $idUsuario"
                );
            } catch (mysqli_sql_exception $error) {
                error_log('Account location update failed: ' . $error->getMessage());
                $actualizado = false;
            }

            if ($actualizado) {
                $datos['id_ubicacion'] = $idUbicacion === null ? null : (int) $idUbicacion;
                $_SESSION['ubicacion'] = $datos['id_ubicacion'];
                $mensaje = 'La ubicación se actualizó correctamente.';
                $claveMensaje = 'config.success_location';
                $tipoMensaje = 'success';
            } else {
                $mensaje = 'No se pudo actualizar la ubicación. Inténtalo nuevamente.';
                $claveMensaje = 'config.error_location_save';
                $tipoMensaje = 'error';
            }
        }
    } elseif ($accion === 'cambiarPassword') {
        $tab = 'Contraseña';
        $actual = is_string($_POST['password_actual'] ?? null) ? $_POST['password_actual'] : '';
        $nueva = is_string($_POST['password_nueva'] ?? null) ? $_POST['password_nueva'] : '';
        $confirmacion = is_string($_POST['password_confirmacion'] ?? null) ? $_POST['password_confirmacion'] : '';

        if (!password_verify($actual, $datos['password'])) {
            $mensaje = 'La contraseña actual no es correcta.';
            $claveMensaje = 'config.error_password_current';
            $tipoMensaje = 'error';
        } elseif (strlen($nueva) < 6) {
            $mensaje = 'La nueva contraseña debe tener al menos 6 caracteres.';
            $claveMensaje = 'config.error_password_length';
            $tipoMensaje = 'error';
        } elseif ($nueva !== $confirmacion) {
            $mensaje = 'La confirmación no coincide con la nueva contraseña.';
            $claveMensaje = 'config.error_password_match';
            $tipoMensaje = 'error';
        } else {
            $passwordSQL = mysqli_real_escape_string($conexion, password_hash($nueva, PASSWORD_DEFAULT));
            $actualizado = false;
            try {
                $actualizado = mysqli_query(
                    $conexion,
                    "UPDATE usuario SET password = '$passwordSQL' WHERE id_usuario = $idUsuario"
                );
            } catch (mysqli_sql_exception $error) {
                error_log('Account password update failed: ' . $error->getMessage());
            }

            $mensaje = $actualizado
                ? 'La contraseña se actualizó correctamente.'
                : 'No se pudo actualizar la contraseña. Inténtalo nuevamente.';
            $claveMensaje = $actualizado ? 'config.success_password' : 'config.error_password_save';
            $tipoMensaje = $actualizado ? 'success' : 'error';
            if ($actualizado) {
                $datos['password'] = $passwordSQL;
            }
        }
    } elseif ($accion === 'desactivarCuenta') {
        $cuentaActualizada = false;
        $transaccionIniciada = false;
        try {
            $transaccionIniciada = mysqli_begin_transaction($conexion);
            if ($transaccionIniciada) {
                $activosActualizados = mysqli_query($conexion,
                    "UPDATE activo SET id_usuario = NULL WHERE id_usuario = $idUsuario"
                );
                $cuentaActualizada = $activosActualizados && mysqli_query($conexion,
                    "UPDATE usuario SET activo = 0 WHERE id_usuario = $idUsuario"
                );

                if ($cuentaActualizada && mysqli_commit($conexion)) {
                    $transaccionIniciada = false;
                    header("Location: ../php/logout");
                    exit;
                }
            } else {
                error_log('Account deactivation could not start a database transaction: ' . mysqli_error($conexion));
            }
        } catch (mysqli_sql_exception $error) {
            error_log('Account deactivation failed: ' . $error->getMessage());
        }

        if ($transaccionIniciada) {
            try {
                mysqli_rollback($conexion);
            } catch (mysqli_sql_exception $error) {
                error_log('Account deactivation rollback failed: ' . $error->getMessage());
            }
        }
        $tab = 'Cuenta';
        $mensaje = 'No se pudo desactivar la cuenta. Inténtalo nuevamente o contacta a un administrador.';
        $claveMensaje = 'config.error_deactivate';
        $tipoMensaje = 'error';
    }
}

$nombreCompleto = trim($datos['nombre'] . ' ' . $datos['apellido']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <base href="/Kinetix/es/">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title data-i18n="config.title">Configuración | Kinetix</title>
    <link rel="icon" type="image/png" href="../imagenes/favicon.png">
    <link rel="stylesheet" href="../css/kinetix-theme.css">
    <link rel="stylesheet" href="../css/usuario-config.css">
    <script src="../js/theme.js" defer></script>
    <script src="../js/i18n.js?v=4" defer></script>
</head>
<body>
    <nav class="menu">
        <div class="menu-content">
            <a class="menu-logo" href="inicio" aria-label="Volver al inicio" data-i18n-attrs='{"aria-label":"common.back"}'>
                <img src="../imagenes/Logo1.png" alt="Logo Kinetix">
            </a>
            <ul>
                <li><a href="inicio" data-i18n="nav.inicio">Inicio</a></li>
                <li><a href="tickets" data-i18n="nav.tickets">Tickets</a></li>
                <li><a href="preguntas-frecuentes" data-i18n="nav.ayuda">Ayuda</a></li>
                <li><a href="inventario" data-i18n="nav.inventario">Inventario</a></li>
                <li><a class="active" href="usuario-config" aria-current="page" data-i18n="nav.configuracion">Configuración</a></li>
                <li><a class="logout-button" href="../php/logout"><span class="logout-sign" aria-hidden="true"></span><span class="logout-text" data-i18n="nav.cerrar_sesion">Cerrar sesión</span></a></li>
                <li><button type="button" id="themeToggle" class="theme-toggle" aria-label="Cambiar tema">🌙</button></li>
                <li>
                    <button type="button" id="langToggle" class="switch" data-i18n-toggle aria-label="Cambiar idioma">
                        <span class="lang left">ES</span><span class="lang right">EN</span><span class="slider"></span>
                    </button>
                </li>
            </ul>
        </div>
    </nav>

    <main class="settings-page">
        <?php if ($mensaje !== '') { ?>
            <p class="form-message <?= $escape($tipoMensaje) ?>" data-i18n="<?= $escape($claveMensaje) ?>" role="status"><?= $escape($mensaje) ?></p>
        <?php } ?>
        <div class="settings-container">
            <aside class="settings-sidebar">
                <input type="checkbox" id="modal-desactivar" class="modal-toggle" hidden>
                <div class="sidebar-profile">
                    <?php $fotoPerfil = urlImagenSubida($datos['foto_path'] ?? null); ?>
                    <?php if ($fotoPerfil !== null) { ?>
                        <img class="avatar" src="<?= $escape($fotoPerfil) ?>" alt="Foto de perfil">
                    <?php } else { ?>
                        <div class="avatar"><?= $escape(strtoupper(substr($datos['nombre'], 0, 1))) ?></div>
                    <?php } ?>
                    <div class="profile-info">
                        <h3><?= $escape($nombreCompleto) ?></h3>
                        <p><?= $escape($datos['email']) ?></p>
                    </div>
                </div>

                <nav class="settings-nav" aria-label="Configuración de cuenta">
                    <?php foreach (['Cuenta' => 'config.tab_cuenta', 'Ubicación' => 'config.tab_ubicacion', 'Contraseña' => 'config.tab_contrasena'] as $nombreTab => $claveTab) { ?>
                        <form method="POST">
                            <input type="hidden" name="accion" value="verTab">
                            <input type="hidden" name="tab" value="<?= $escape($nombreTab) ?>">
                            <button type="submit" class="nav-item <?= $tab === $nombreTab ? 'active' : '' ?>" <?= $tab === $nombreTab ? 'aria-current="page"' : '' ?>>
                                <span class="nav-icon" aria-hidden="true"><?= $nombreTab === 'Cuenta' ? '&#128100;' : ($nombreTab === 'Ubicación' ? '&#128205;' : '&#128273;') ?></span>
                                <span class="nav-label" data-i18n="<?= $escape($claveTab) ?>"><?= $escape($nombreTab) ?></span>
                            </button>
                        </form>
                    <?php } ?>
                    <label for="modal-desactivar" class="nav-item nav-danger">
                        <span class="nav-icon" aria-hidden="true">&#9888;</span>
                        <span class="nav-label" data-i18n="config.tab_desactivar">Desactivar cuenta</span>
                    </label>
                </nav>

                <div class="modal-overlay">
                    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="modal-title">
                        <div class="modal-body">
                            <div class="modal-icon" aria-hidden="true">⚠️</div>
                            <h2 class="modal-title" id="modal-title" data-i18n="config.modal.titulo">¿Estás seguro?</h2>
                            <p data-i18n="config.modal.p">Al desactivar tu cuenta:</p>
                            <ul class="modal-lista">
                                <li data-i18n="config.modal.item1">No podrás iniciar sesión ni acceder a tus tickets.</li>
                                <li data-i18n="config.no_assets">Tus activos quedarán sin dueño.</li>
                                <li data-i18n="config.modal.item2">Tu información personal quedará inactiva.</li>
                                <li data-i18n="config.modal.item3">Esta acción no se puede deshacer.</li>
                            </ul>
                        </div>
                        <div class="modal-footer">
                            <label for="modal-desactivar" class="btn-cancel" data-i18n="config.modal.cancelar">Cancelar</label>
                            <form method="POST">
                                <input type="hidden" name="accion" value="desactivarCuenta">
                                <button type="submit" class="btn-accept" data-i18n="config.modal.aceptar">Aceptar</button>
                            </form>
                        </div>
                    </div>
                </div>
            </aside>

            <section class="settings-content">
                <header class="content-header">
                    <h1 data-i18n="<?= $tab === 'Cuenta' ? 'config.tab_cuenta' : ($tab === 'Ubicación' ? 'config.tab_ubicacion' : 'config.tab_contrasena') ?>"><?= $escape($tab) ?></h1>
                    <p data-i18n="config.p">Gestioná tu información personal y de acceso.</p>
                </header>

                <?php if ($tab === 'Cuenta') { ?>
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="accion" value="cambiarDatos">
                        <div class="settings-group">
                            <div class="setting-row">
                                <div class="setting-info">
                                    <h3 data-i18n="config.profile_photo">Foto de perfil</h3>
                                    <p data-i18n="config.profile_photo_hint">JPG, PNG o WebP; máximo 5 MB.</p>
                                </div>
                                <div class="setting-control">
                                    <input type="file" name="foto" accept="image/jpeg,image/png,image/webp">
                                </div>
                            </div>
                            <div class="setting-row">
                                <div class="setting-info">
                                    <h3 data-i18n="config.first_name">Nombre</h3>
                                    <p data-i18n="config.p_nombre">Este nombre se muestra en tu perfil y en los tickets.</p>
                                </div>
                                <div class="setting-control">
                                    <input type="text" name="nombre" autocomplete="given-name" maxlength="50" value="<?= $escape($datos['nombre']) ?>" required>
                                </div>
                            </div>
                            <div class="setting-row">
                                <div class="setting-info">
                                    <h3 data-i18n="config.last_name">Apellido</h3>
                                    <p data-i18n="config.p_last_name">Este apellido se muestra en tu perfil y en los tickets.</p>
                                </div>
                                <div class="setting-control">
                                    <input type="text" name="apellido" autocomplete="family-name" maxlength="100" value="<?= $escape($datos['apellido']) ?>" required>
                                </div>
                            </div>
                            <div class="setting-row">
                                <div class="setting-info">
                                    <h3 data-i18n="config.label_email">Correo electrónico</h3>
                                    <p data-i18n="config.p_email">Usado para iniciar sesión y recibir notificaciones.</p>
                                </div>
                                <div class="setting-control">
                                    <input type="email" name="email" autocomplete="email" maxlength="255" value="<?= $escape($datos['email']) ?>" required>
                                </div>
                            </div>
                            <div class="setting-row">
                                <div class="setting-info">
                                    <h3 data-i18n="config.label_rol">Rol de cuenta</h3>
                                    <p data-i18n="config.p_rol">Rol asignado a tu cuenta.</p>
                                </div>
                                <div class="setting-control">
                                    <input type="text" value="<?= $escape($datos['nombre_rol'] ?? '') ?>" readonly>
                                </div>
                            </div>
                        </div>
                        <button class="btn-save" type="submit" data-i18n="config.btn_guardar">Guardar cambios</button>
                    </form>
                <?php } elseif ($tab === 'Ubicación') { ?>
                    <form method="POST">
                        <input type="hidden" name="accion" value="cambiarUbicacion">
                        <div class="settings-group">
                            <div class="setting-row">
                                <div class="setting-info">
                                    <h3 data-i18n="config.location">Ubicación de trabajo</h3>
                                    <p data-i18n="config.location_hint">Necesitás una ubicación para solicitar activos.</p>
                                </div>
                                <div class="setting-control">
                                    <select name="id_ubicacion">
                                        <option value="" data-i18n="config.choose_location">Seleccioná una ubicación</option>
                                        <?php foreach ($ubicaciones as $ubicacion) { ?>
                                            <option value="<?= (int) $ubicacion['id_ubicacion'] ?>" <?= (int) ($datos['id_ubicacion'] ?? 0) === (int) $ubicacion['id_ubicacion'] ? 'selected' : '' ?>>
                                                <?= $escape($ubicacion['calle'] . ' - ' . ($ubicacion['ciudad'] ?? 'Sin ciudad')) ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <button class="btn-save" type="submit" data-i18n="config.btn_guardar">Guardar cambios</button>
                    </form>
                <?php } else { ?>
                    <form method="POST">
                        <input type="hidden" name="accion" value="cambiarPassword">
                        <div class="settings-group">
                            <div class="setting-row">
                                <div class="setting-info">
                                    <h3 data-i18n="config.current_password">Contraseña actual</h3>
                                </div>
                                <div class="setting-control">
                                    <input type="password" name="password_actual" autocomplete="current-password" required>
                                </div>
                            </div>
                            <div class="setting-row">
                                <div class="setting-info">
                                    <h3 data-i18n="config.password_placeholder">Nueva contraseña</h3>
                                </div>
                                <div class="setting-control">
                                    <input type="password" name="password_nueva" autocomplete="new-password" minlength="6" required>
                                </div>
                            </div>
                            <div class="setting-row">
                                <div class="setting-info">
                                    <h3 data-i18n="config.password_confirm">Confirmar contraseña</h3>
                                </div>
                                <div class="setting-control">
                                    <input type="password" name="password_confirmacion" autocomplete="new-password" minlength="6" required>
                                </div>
                            </div>
                        </div>
                        <button class="btn-save" type="submit" data-i18n="config.btn_guardar">Guardar cambios</button>
                    </form>
                <?php } ?>
            </section>
        </div>
    </main>

    <footer class="footer">
        <p data-i18n="footer.copyright">© 2026 Kinetix. Todos los derechos reservados.</p>
    </footer>
</body>
</html>
