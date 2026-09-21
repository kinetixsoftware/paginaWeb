<?php
// login
session_start();


require_once "../php/conexionBDD.php";

$conexion = conectarBD();

if (isset($_SESSION['rol'])) {
    $_SESSION['mensaje'] = "Usted ya inicio sesion";
    $_SESSION['tipoError'] = "error";
    header("Location: inicio.php");
    exit;
}

$mensaje = $_SESSION['mensaje'] ?? "";
$tipoError = $_SESSION['tipoError'] ?? "";
$email = $_SESSION['email'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $email             = $_POST['email'] ?? '';
      $password       = $_POST['password'] ?? '';

    try {
        
        $sql = "SELECT id_usuario, nombre, apellido, password, activo, id_rol FROM usuario WHERE email = '$email' LIMIT 1";
        $query = mysqli_query($conexion, $sql);
        $usuario = mysqli_fetch_assoc($query);

        if (!$usuario || !password_verify($password, $usuario['password'])) {
            $tipoError = "error";
            $mensaje = "Lo que ingreso parece estar mal, intentelo de nuevo.";
        } elseif ($usuario['activo'] == 0) {
            $tipoError = "error";
            $mensaje = "Este usuario fue desactivado por un administrador, intente registrarse de nuevo";
        } else {
            $_SESSION['idUsuario'] = $usuario['id_usuario'];
            $_SESSION['email'] = $email;
            $_SESSION['nombre'] = $usuario['nombre'];
            $_SESSION['apellido'] = $usuario['apellido'];
            $_SESSION['rol'] = $usuario['id_rol'];

            $_SESSION['mensaje'] = "Inicio de sesión exitoso. ";
            $_SESSION['tipoError'] = "success";
            header("Location: inicio.php");
            exit;
        }
    } catch (Throwable $e) {
            $tipoError = "error";
            $mensaje = "Error al iniciar sesion. Intentalo de nuevo.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión | Kinetix</title>
    <link rel="icon" type="image/png" href="imagenes\Icon.png">
    <link rel="stylesheet" href="..\css\login.css">
</head>

<body>
    <!-- Menu -->
    <nav class="menu">
        <div class="menu-content">
            <div class="menu-logo">
                <img class="img--logo" src="../imagenes/logo1.png" width="50" height="50" alt="Logo Vrision">
            </div>
            <ul>
                <li><a href="inicio.php">Volver</a></li>
                <li><a href="registrarse.php">Registrarse</a></li>
                <li>
                    <button type="button" id="themeToggle" class="theme-toggle" aria-label="Cambiar tema"> 🌙 </button>
                </li>
            </ul>
    </nav>
    <div class="toast-wrapper">
        <?php if($mensaje): ?>
        <div id="formMessage" class="form-message <?= $tipoError ?>">
            <?= htmlspecialchars($mensaje) ?>
        </div>
        <?php endif; ?>
    </div>
    <!-- Contenedor principal -->
    <section class="login-container">
        <div class="login-box">
            <h2>Acceso a clientes</h2>
            <p class="login-subtitle">
                Iniciá sesión para poder acceder a nuestro servicio.<br>
                <small>(Porfavor no uses un gmail real o contraseña personal)</small>
            </p>
            <form method="post" class="login-form">
                <div class="input-group">
                    <label for="email">Correo electrónico</label>
                    <input type="email" id="email" name="email" placeholder="ejemplo@gmail.com" value="<?= $email ?>" required>
                </div>

                <div class="input-group">
                    <label for="password">Contraseña</label>
                    <div class="password-input-wrapper">
                        <input type="password" id="password" name="password" placeholder="••••••••" required minlength="6">
                        <button type="button" class="btn-toggle-pwd" onclick="showPassword()" aria-label="Mostrar contraseña">
                            <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-login">Iniciar sesión</button>
            </form>

            <p class="login-footer">
                ¿No tenés una cuenta? <a href="registrarse.php">Registrate aquí</a>
            </p>
        </div>
    </section>

    <!-- Pie de página -->
    <footer class="footer">
        <p>© 2026 Kinetix. Todos los derechos reservados.</p>
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

    //DARK MODE LIGHT MODE SWITCH
    const themeToggle = document.getElementById('themeToggle');

    function applyTheme(theme) {
        const body = document.body;
        const isDark = theme === 'dark';
        body.classList.toggle('dark-mode', isDark);
        body.classList.toggle('light-mode', !isDark);
        themeToggle.textContent = isDark ? '☀️' : '🌙';
        themeToggle.setAttribute('aria-label', isDark ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro');
    }

    const savedTheme = localStorage.getItem('theme') || 'dark';
    applyTheme(savedTheme);

    themeToggle.addEventListener('click', () => {
        const nextTheme = document.body.classList.contains('light-mode') ? 'dark' : 'light';
        applyTheme(nextTheme);
        localStorage.setItem('theme', nextTheme);
    });

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