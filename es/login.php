<?php
// login
session_start();


require_once "../php/conexionBDD.php";

$conexion = conectarBD();

if (isset($_SESSION['rol'])) {
    $_SESSION['mensaje'] = "Usted ya inicio sesion";
    $_SESSION['tipoError'] = "error";
    header("Location: inicio");
    exit;
}

$email = $_SESSION['email'] ?? null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $email             = $_POST['email'] ?? '';
      $password       = $_POST['password'] ?? '';

    try {
        $sql = "SELECT id_usuario, nombre, apellido, password, activo, id_rol FROM usuario WHERE email = '$email' LIMIT 1";
        $query = mysqli_query($conexion, $sql);
        $usuario = mysqli_fetch_assoc($query);

        if (!$usuario || !password_verify($password, $usuario['password'])) {
            $_SESSION['tipoError'] = "error";
            $_SESSION['mensaje'] = "Lo que ingreso parece estar mal, intentelo de nuevo.";
        } elseif (((int) $usuario['activo']) === 0) {
            $_SESSION['tipoError'] = "error";
            $_SESSION['mensaje'] = "Este usuario fue desactivado. Intente registrarse de nuevo";
            header("Location: inicio");
            exit;
        } else {
            $_SESSION['idUsuario'] = $usuario['id_usuario'];
            $_SESSION['email'] = $email;
            $_SESSION['nombre'] = $usuario['nombre'];
            $_SESSION['apellido'] = $usuario['apellido'];
            $_SESSION['rol'] = $usuario['id_rol'];

            $_SESSION['mensaje'] = "Inicio de sesión exitoso. ";
            $_SESSION['tipoError'] = "success";
            header("Location: inicio");
            exit;
        }
    } catch (Throwable $e) {
            $_SESSION['tipoError'] = "error";
            $_SESSION['mensaje'] = "Error al iniciar sesion. Intentalo de nuevo.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title data-i18n="login.title">Iniciar sesión | Kinetix</title>
    <link rel="icon" type="image/png" href="../imagenes/favicon.png">
    <link rel="stylesheet" href="../css/Login.css">
    <link rel="stylesheet" href="../css/kinetix-theme.css">
    <script src="../js/theme.js" defer></script>
    <script src="../js/i18n.js?v=4" defer></script>
</head>

<body>
    <!-- Menu -->
    <nav class="menu">
        <div class="menu-content">
            <div class="menu-logo">
                <img class="img--logo" src="../imagenes/logo1.png" width="50" height="50" alt="Logo Vrision">
            </div>
            <ul>
                <li><a href="inicio" data-i18n="common.back">Volver</a></li>
                <li><a href="registrarse" data-i18n="common.register">Registrarse</a></li>
<li>
                    <button type="button" id="themeToggle" class="theme-toggle" aria-label="Cambiar tema">🌙</button>
                </li>
                <li>
                    <button type="button" id="langToggle" class="switch" data-i18n-toggle aria-label="Cambiar idioma">
                        <span class="lang left">ES</span><span class="lang right">EN</span><span class="slider"></span>
                    </button>
                </li>
            </ul>
    </nav>

    <?php if (isset($_SESSION['mensaje'])) { ?>
    <div class="toast-wrapper">
        <div id="formMessage" class="form-message <?= $_SESSION['tipoError'] ?>">
            <?= $_SESSION['mensaje']?>
        </div>
    </div>
    <?php unset($_SESSION['mensaje'], $_SESSION['tipoError']); } ?>

    <!-- Contenedor principal -->
    <section class="login-container">
        <div class="login-box">
                <h2 data-i18n="login.h2">Acceso a clientes</h2>
                <p class="login-subtitle" data-i18n-html="true" data-i18n="login.subtitle">
                    Iniciá sesión para poder acceder a nuestro servicio.<br>
                    <small>(Por favor no uses un gmail real o contraseña personal)</small>
                </p>
            <form method="post" class="login-form">
                <div class="input-group">
                    <label for="email" data-i18n="login.label_email">Correo electrónico</label>
                    <input type="email" id="email" name="email" placeholder="ejemplo@gmail.com" value="<?= $email ?>" data-i18n-attrs='{"placeholder":"login.placeholder_email"}' required>
                </div>

                <div class="input-group">
                    <label for="password" data-i18n="login.label_password">Contraseña</label>
                    <div class="password-input-wrapper">
                        <input type="password" id="password" name="password" placeholder="••••••••" required minlength="6">
                        <button type="button" class="btn-toggle-pwd" onclick="showPassword()" aria-label="Mostrar contraseña" data-i18n-attrs='{"aria-label":"common.show_password"}'>
                            <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-login" data-i18n="login.btn">Iniciar sesión</button>
            </form>

            <p class="login-footer" data-i18n-html="true" data-i18n="login.footer">
                ¿No tenés una cuenta? <a href="registrarse">Registrate aquí</a>
            </p>
        </div>
    </section>

    <!-- Pie de página -->
    <footer class="footer">
        <p data-i18n="footer.copyright">© 2026 Kinetix. Todos los derechos reservados.</p>
    </footer>
    <script>
    const urlParams = new URLSearchParams(window.location.search);
    const status = urlParams.get('status');
    const emailParametro = urlParams.get('email')

    if (emailParametro) {
        const emailInput = document.getElementById('email');
        if (emailInput) {
            emailInput.value = emailParametro;
        }
    }

    if (emailParametro) {
        document.getElementById('password').focus();
    }

    //MOSTRAR CONTRASENIA
    function showPassword() {
        var x = document.getElementById("password")
        if (x.type === "password") {
            x.type = "text";
        } else {
            x.type = "password";
        }
    }
    </script>
</body>

</html>
