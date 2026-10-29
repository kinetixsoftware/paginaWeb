<?php 
session_start();

require_once "../php/conexionBDD.php";


$mensaje = "";
$tipoError = "";

if (isset($_SESSION['rol'])) {
    $_SESSION['mensaje'] = "Porfavor incia sesion para obtener acceso a esta pagina";
    $_SESSION['tipoError'] = "error";
    header("Location: login");
    exit;
}

$conexion = conectarBD();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre      = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $email       = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $id_rol      = (int) ($_POST['rol'] ?? 0);

    if ($nombre === '' || $apellido === '' || $email === '' || $password === '' || $id_rol === 0) {
      $tipoError = "error";
      $mensaje = "Completá todos los campos del formulario.";
    } else {
        try {

            mysqli_set_charset($conexion, "utf8mb4");

            $nombreSQL = mysqli_real_escape_string($conexion, $nombre);
            $apellidoSQL = mysqli_real_escape_string($conexion, $apellido);
            $emailSQL = mysqli_real_escape_string($conexion, $email);

            $sql = "SELECT id_usuario, nombre, apellido, password, id_rol FROM usuario WHERE email = '$emailSQL' LIMIT 1";
            $query = mysqli_query($conexion, $sql);

            if (!$query) {
                throw new Exception(mysqli_error($conexion));
            }

            $usuario = mysqli_fetch_assoc($query);

            if ($usuario) {
                $tipoError = "error";
                $mensaje = "El correo electrónico ya está registrado.";
            } else {
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $passwordSQL = mysqli_real_escape_string($conexion, $passwordHash);
                $sqlQ = "INSERT INTO usuario (nombre, apellido, email, password, activo, id_rol, id_ubicacion)
                    VALUES ('$nombreSQL', '$apellidoSQL', '$emailSQL', '$passwordSQL', 1, $id_rol, NULL)";
                $query = mysqli_query($conexion, $sqlQ);
                $queryInsert = $query === true;

                if ($queryInsert) {
                    $_SESSION['tipoError'] = "success";
                    $_SESSION['mensaje'] = "¡Cuenta creada con éxito! Ingresá tu contraseña para iniciar sesión.";
                    $_SESSION['email'] = $email;
                    $_SESSION['ubicacion'] = null;
                    header("Location: login");
                    exit;
                } else {
                    $tipoError = "error";
                    $mensaje = "Error al registrar el usuario. Intentalo de nuevo";
                }
            }
        } catch (Throwable $e) {
            $tipoError = "error";
            $mensaje = "Error al registrar el usuario: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title data-i18n="registrarse.title">Registrarse | Kinetix</title>
    <!-- Podés usar tu mismo archivo de estilos (ajustando la ruta si es necesario) -->
     <link rel="icon" type="image/png" href="../imagenes/favicon.png">
    <link rel="stylesheet" href="../css/Login.css">
    <link rel="stylesheet" href="../css/kinetix-theme.css">
    <link rel="stylesheet" href="../css/menu-layout.css">
    <script src="../js/theme.js" defer></script>
    <script src="../js/i18n.js?v=4" defer></script>
</head>

<body>
    <!-- Menu -->
    <nav class="menu">
        <div class="menu-content">
            <div class="menu-logo">
                <img class="img--logo" src="../../imagenes/Logo1.png" width="50" height="50" alt="Logo Vrision">
            </div>

            <div class="nav-links"><ul>
                <li><a href="inicio" data-i18n="common.back">Volver al inicio</a></li>
                <li><a href="login" data-i18n="nav.iniciar_sesion">Iniciar Sesion</a></li>
            </ul></div>
            <div class="nav-actions">
                <ul>
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
        </div>
    </nav>
    <!-- Mensaje error -->
    <?php if (isset($_SESSION['mensaje'])) { ?>
    <div class="toast-wrapper">
        <div id="formMessage" class="form-message <?= $_SESSION['tipoError'] ?>">
            <?= $_SESSION['mensaje']?>
        </div>
    </div>
    <?php unset($_SESSION['mensaje'], $_SESSION['tipoError']); } ?>

    <!-- Contenedor principal de registro -->
    <section class="login-container">
        <div class="login-box">
            <h2 data-i18n="register.heading">Crear cuenta</h2>
            <p class="login-subtitle" data-i18n="register.subtitle"> Completá tus datos para registrarte en nuestro servicio. </p>

            <form method="post" class="login-form">
                <div class="input-row">
                    <div class="input-group">
                        <label for="nombre" data-i18n="register.first_name">Nombre</label>
                        <input type="text" id="nombre" name="nombre" placeholder="Tu nombre" data-i18n-attrs='{"placeholder":"register.first_name_placeholder"}' required>
                    </div>

                    <div class="input-group">
                        <label for="apellido" data-i18n="register.last_name">Apellido</label>
                        <input type="text" id="apellido" name="apellido" placeholder="Tu apellido" data-i18n-attrs='{"placeholder":"register.last_name_placeholder"}' required>
                    </div>
                </div>

                <div class="input-group">
                    <label for="email" data-i18n="login.label_email">Correo electrónico</label>
                    <input type="email" id="email" name="email" placeholder="ejemplo@gmail.com" data-i18n-attrs='{"placeholder":"login.placeholder_email"}' required>
                </div>

                <div class="input-group">
                    <label for="password" data-i18n="login.label_password">Contraseña</label>
                    <div class="password-input-wrapper">
                        <input type="password" id="password" name="password" placeholder="••••••••" required
                            minlength="6">
                        <button type="button" class="btn-toggle-pwd" onclick="showPassword()"
                            aria-label="Mostrar contraseña" data-i18n-attrs='{"aria-label":"common.show_password"}'>
                            <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </button>
                    </div>
                </div>

                <h3 data-i18n="register.role_question">¿En que rol te gustaria trabajar?</h3>
                <div class="input-switch">
                    <div class="tooltip">
                        <span class="tooltiptext" data-i18n="register.role_tooltip"> En este rol principalmente vas a interactuar con el tecnico, podes
                            crear tickets, responder en ellos, solicitar productos. </span>
                        <div class="input-box1">
                            <input id="cliente" type="radio" onchange="alternateCheckboxes()" name="rol" value="1">
                            <label for="cliente" id="clienteTexto" data-i18n="register.client"> Cliente </label>
                        </div>
                    </div>
                    <div class="input-box1">
                        <input id="tecnico" type="radio" onchange="alternateCheckboxes()" name="rol" value="2">
                        <label for="tecnico" id="tecnicoTexto" data-i18n="register.technician"> Tecnico </label>
                    </div>
                </div>
                <p><span data-i18n="register.terms_prefix">Al crear una cuenta, aceptas nuestros</span> <a href="#" class="terms-link" data-i18n="register.terms">Términos y Política de Privacidad.</a>.</p>
                <div class="input-buttons">
                    <!-- En caso de     -->
                    <button type="submit" class="btn-login" data-i18n="register.submit">Registrarse</button>
                </div>
            </form>

            <p class="login-footer">
                <span data-i18n="register.have_account">¿Ya tenés una cuenta?</span> <a href="login" data-i18n="register.sign_in">Iniciá sesión</a>
            </p>
        </div>
    </section>

    <!-- Pie de página -->
    <footer class="footer">
        <p data-i18n="register.footer">© 2026 Kinetix Softwares. Todos los derechos reservados.</p>
    </footer>

    <script>
    function showPassword() {
        var x = document.getElementById("password")
        if (x.type === "password") {
            x.type = "text";
        } else {
            x.type = "password";
        }
    }

    function alternateCheckboxes() {
        const cliente = document.getElementById("cliente");
        const tecnico = document.getElementById("tecnico");
        const clienteTexto = document.getElementById("clienteTexto");
        const tecnicoTexto = document.getElementById("tecnicoTexto");

        if (cliente.checked) {
            clienteTexto.style.color = 'lightgreen';
            tecnicoTexto.style.color = 'lightgrey';
        } else if (tecnico.checked) {
            tecnicoTexto.style.color = 'lightgreen';
            clienteTexto.style.color = 'lightgrey';
        } else {
            clienteTexto.style.color = 'white';
            tecnicoTexto.style.color = 'white';
        }
    }
    </script>
</body>

</html>
