<?php 
session_start();

require_once "../php/conexionBDD.php";

$conexion = conectarBD();

if (!isset($_SESSION['rol']) || $_SESSION['rol'] === 1) {
    $_SESSION['mensaje'] = "No tienes acceso a esta pagina";
    $_SESSION['tipoError'] = "error";
    header("Location: login.php");
  exit;
}

$marcas = "SELECT * FROM activo_marca ORDER BY id_marca ASC";
$resultadomarcas = mysqli_query($conexion, $marcas);

$categoria = "SELECT * FROM categoria_activo ORDER BY id_categoria ASC";
$resultadocategorias = mysqli_query($conexion, $categoria);

$estados = "SELECT * FROM estado_activo WHERE NOT id_estado = 2 ORDER BY id_estado ASC";
$resultadoestados = mysqli_query($conexion, $estados);

$sqlUbicaciones = "SELECT u.id_ubicacion, u.calle, c.ciudad
    FROM ubicacion AS u
    LEFT JOIN ciudad AS c ON c.id_ciudad = u.id_ciudad
    ORDER BY c.ciudad, u.calle";
$resultadoUbicacion = mysqli_query($conexion, $sqlUbicaciones);

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo = $_POST['codigo_inventario'];
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'] ?? "";
    $modelo = $_POST['modelo'];
    $numero_serie = $_POST['numero_serie'];
    $categoria = (int) $_POST['categoria'];
    $marca_id = (int) $_POST['marca'];
    $fecha_adquisicion = date('Y-m-d');
    $fecha_baja = null;
    $id_ubicacion = (int) $_POST['ubicacion'];

    $sql = "INSERT INTO activo(codigo_inventario, nombre, descripcion, modelo, numero_serie, fecha_adquisicion, fecha_baja, id_estado, id_categoria, id_ubicacion, id_marca)
            VALUES ('$codigo', '$nombre', '$descripcion', '$modelo', '$numero_serie', '$fecha_adquisicion', '$fecha_baja', 1, $categoria, $id_ubicacion, $marca_id)";
    $sql = mysqli_query($conexion, $sql);
    
    if($sql) {
        $_SESSION['mensaje'] = "Activo registrado correctamente";
        $_SESSION['tipoError'] = "success";
    } else {
        $_SESSION['mensaje'] = "El activo no pudo ser registrado";
        $_SESSION['tipoError'] = "error";
    }
    Header("Location: ../es/inventario.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../css/inventario.css">
<title>Inventario</title>
</head>

<body>
    <!-- Menu -->
    <nav class="menu">
        <div class="menu-content">
            <div class="menu-logo">
                <img src="../imagenes/Logo1.png" alt="Logo Kinetix">
            </div>
            <ul>
                <li><a href="../es/inicio.php">Inicio</a></li>
                <li><a href="../es/inventario.php">Volver</a></li>
                <li style="font-size: 15px;">||</li>
                <li>
                    <button type="button" id="themeToggle" class="theme-toggle" aria-label="Cambiar tema">🌙 Dark</button>
                </li>
            </ul>
        </div>
    </nav>

    <div class="page">
        <div class="container">
            <div class="header">
                <div class="contenedor">
                    <h1>Agregar item</h1>
                    <section class="formulario">
                        <form method="post">
                            <label for="codigo_inventario">Codigo de inventario</label>
                            <input id="codigo_inventario" name="codigo_inventario" type="text">
                            <label for="nombre">Nombre</label>
                            <input id="nombre" name="nombre" type="text">
                            <label for="Descripcion">Descripcion</label>
                            <input id="Descripcion" name="descripcion" type="text">
                            <label for="modelo">Modelo</label>
                            <input id="modelo" name="modelo" type="text">
                            <label for="numero_serie">Numero de serie</label>
                            <input id="numero_serie" name="numero_serie" type="text">
                            <label for="activoMarca"> Marca </label>
                                <select id="activoMarca" name="marca">
                                    <?php  while ($reg = mysqli_fetch_array($resultadomarcas)) { ?>
                                        <option name="<?=$reg['marca'] ?>" value="<?= $reg['id_marca'] ?>" > <h5><?= $reg['marca'] ?></h5></option>
                                    <?php } ?>
                                </select>
                            <label for="activoCategoria"> Categoria </label>
                                <select id="activoCategoria" name="categoria">
                                    <?php  while ($reg = mysqli_fetch_array($resultadocategorias)) { ?>
                                        <option name="<?=$reg['categoria'] ?>" value="<?= $reg['id_categoria'] ?>" > <h5><?= $reg['categoria'] ?></h5></option>
                                    <?php } ?>
                                </select>
                            <label for="activoUbicacion"> Ubicacion </label>
                                <select id="activoUbicacion" name="ubicacion">
                                    <?php  while ($reg = mysqli_fetch_array($resultadoUbicacion)) { ?>
                                        <option name="<?=$reg['id_ubicacion'] ?>" value="<?= $reg['id_ubicacion'] ?>" > <h5><?=$reg['calle'] . ' - ' . $reg['ciudad'] ?></h5></option>
                                    <?php } ?>
                                </select>
                            <button type="submit">Guardar</button>
                        </form>
                    </section>
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
  </script></body>
</html>