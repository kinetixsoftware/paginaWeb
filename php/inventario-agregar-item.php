<?php
session_start();

require_once "conexionBDD.php";
require_once "image-upload.php";

$conexion = conectarBD();
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$escape = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$rol = (int) ($_SESSION['rol'] ?? 0);

if (!in_array($rol, [2, 3], true)) {
    $_SESSION['mensaje'] = "No tienes acceso a esta página";
    $_SESSION['tipoError'] = "error";
    header("Location: ../es/login");
    exit;
}

$idUsuario = (int) ($_SESSION['idUsuario'] ?? 0);
$marcas = mysqli_query($conexion, "SELECT id_marca, marca FROM activo_marca ORDER BY marca");
$categorias = mysqli_query($conexion, "SELECT id_categoria, categoria FROM categoria_activo ORDER BY categoria");
$ubicaciones = mysqli_query(
    $conexion,
    "SELECT u.id_ubicacion, u.calle, c.ciudad
     FROM ubicacion AS u LEFT JOIN ciudad AS c ON c.id_ciudad = u.id_ciudad
     ORDER BY c.ciudad, u.calle"
);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $imagenRuta = null;
    $transaccionIniciada = false;
    try {
        $codigo = trim(is_string($_POST['codigo_inventario'] ?? null) ? $_POST['codigo_inventario'] : '');
        $nombre = trim(is_string($_POST['nombre'] ?? null) ? $_POST['nombre'] : '');
        $descripcion = trim(is_string($_POST['descripcion'] ?? null) ? $_POST['descripcion'] : '');
        $modelo = trim(is_string($_POST['modelo'] ?? null) ? $_POST['modelo'] : '');
        $serie = trim(is_string($_POST['numero_serie'] ?? null) ? $_POST['numero_serie'] : '');
        $categoria = filter_var($_POST['categoria'] ?? null, FILTER_VALIDATE_INT);
        $marca = filter_var($_POST['marca'] ?? null, FILTER_VALIDATE_INT);
        $ubicacion = filter_var($_POST['ubicacion'] ?? null, FILTER_VALIDATE_INT);

        if ($codigo === '' || $nombre === '' || $modelo === '' || $serie === '' || !$categoria || !$marca || !$ubicacion) {
            throw new RuntimeException('Completa todos los campos requeridos.');
        }

        $imagenRuta = guardarImagenSubida($_FILES['imagen'] ?? [], 'activos');
        $codigoSQL = mysqli_real_escape_string($conexion, $codigo);
        $nombreSQL = mysqli_real_escape_string($conexion, $nombre);
        $descripcionSQL = mysqli_real_escape_string($conexion, $descripcion);
        $modeloSQL = mysqli_real_escape_string($conexion, $modelo);
        $serieSQL = mysqli_real_escape_string($conexion, $serie);
        $imagenSQL = $imagenRuta === null
            ? 'NULL'
            : "'" . mysqli_real_escape_string($conexion, $imagenRuta) . "'";

        $transaccionIniciada = mysqli_begin_transaction($conexion);
        if (!$transaccionIniciada) {
            throw new RuntimeException('No se pudo iniciar el registro del activo.');
        }
        mysqli_query(
            $conexion,
            "INSERT INTO activo
                (codigo_inventario, nombre, descripcion, modelo, numero_serie, fecha_adquisicion,
                 fecha_baja, id_estado, id_categoria, id_ubicacion, id_marca, imagen_path)
             VALUES ('$codigoSQL', '$nombreSQL', '$descripcionSQL', '$modeloSQL', '$serieSQL',
                     CURDATE(), NULL, 1, $categoria, $ubicacion, $marca, $imagenSQL)"
        );
        $idActivo = (int) $conexion->insert_id;
        $accionSQL = mysqli_real_escape_string($conexion, "Técnico ID: $idUsuario creó este activo");
        mysqli_query(
            $conexion,
            "INSERT INTO historial_activo(id_activo, accion) VALUES ($idActivo, '$accionSQL')"
        );
        mysqli_commit($conexion);
        $transaccionIniciada = false;

        $_SESSION['mensaje'] = "Activo registrado correctamente";
        $_SESSION['tipoError'] = "success";
    } catch (Throwable $error) {
        try {
            if ($transaccionIniciada) {
                mysqli_rollback($conexion);
                $transaccionIniciada = false;
            }
        } catch (Throwable $rollbackError) {
            error_log('Asset creation rollback failed: ' . $rollbackError->getMessage());
        }
        if ($imagenRuta !== null) {
            try {
                eliminarImagenSubida($imagenRuta);
            } catch (Throwable $cleanupError) {
                error_log('Asset image cleanup failed: ' . $cleanupError->getMessage());
            }
        }
        error_log('Asset creation failed: ' . $error->getMessage());
        $_SESSION['mensaje'] = $error instanceof RuntimeException
            ? $error->getMessage()
            : "El activo no pudo ser registrado";
        $_SESSION['tipoError'] = "error";
    }

    header("Location: ../php/inventario-agregar-item");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../imagenes/favicon.png">
    <link rel="stylesheet" href="../css/inventario.css">
    <link rel="stylesheet" href="../css/kinetix-theme.css">
    <title>Agregar activo | Kinetix</title>
</head>
<body>
    <nav class="menu">
        <div class="menu-content">
            <div class="menu-logo">
                <img src="../imagenes/Logo1.png" alt="Logo Kinetix">
            </div>
            <ul>
                <li><a href="../es/inicio">Inicio</a></li>
                <li><a href="../es/inventario">Volver al inventario</a></li>
                <li><a class="logout-button" href="logout"><span class="logout-sign" aria-hidden="true"></span><span class="logout-text">Cerrar sesión</span></a></li>
                <li><button type="button" id="themeToggle" class="theme-toggle" aria-label="Cambiar tema">🌙</button></li>
            </ul>
        </div>
    </nav>
    <?php if (isset($_SESSION['mensaje'])) { ?>
        <div class="toast-wrapper">
            <div class="form-message <?= $escape($_SESSION['tipoError'] ?? 'error') ?>" role="status"><?= $escape($_SESSION['mensaje']) ?></div>
        </div>
        <?php unset($_SESSION['mensaje'], $_SESSION['tipoError']); ?>
    <?php } ?>
    <main class="page">
        <div class="container">
            <header class="header">
                <div>
                    <h1>Agregar activo</h1>
                    <p>Registra un componente para incorporarlo al inventario.</p>
                </div>
            </header>
            <section class="formulario">
                <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="MAX_FILE_SIZE" value="5242880">
                    <label for="codigo_inventario">Código de inventario</label>
                    <input id="codigo_inventario" name="codigo_inventario" type="text" maxlength="50" required>
                    <label for="nombre">Nombre</label>
                    <input id="nombre" name="nombre" type="text" maxlength="255" required>
                    <label for="descripcion">Descripción</label>
                    <textarea id="descripcion" name="descripcion"></textarea>
                    <label for="modelo">Modelo</label>
                    <input id="modelo" name="modelo" type="text" maxlength="50" required>
                    <label for="numero_serie">Número de serie</label>
                    <input id="numero_serie" name="numero_serie" type="text" maxlength="100" required>
                    <label for="activoMarca">Marca</label>
                    <select id="activoMarca" name="marca" required>
                        <?php while ($reg = mysqli_fetch_assoc($marcas)) { ?>
                            <option value="<?= (int) $reg['id_marca'] ?>"><?= $escape($reg['marca']) ?></option>
                        <?php } ?>
                    </select>
                    <label for="activoCategoria">Categoría</label>
                    <select id="activoCategoria" name="categoria" required>
                        <?php while ($reg = mysqli_fetch_assoc($categorias)) { ?>
                            <option value="<?= (int) $reg['id_categoria'] ?>"><?= $escape($reg['categoria']) ?></option>
                        <?php } ?>
                    </select>
                    <label for="activoUbicacion">Ubicación</label>
                    <select id="activoUbicacion" name="ubicacion" required>
                        <?php while ($reg = mysqli_fetch_assoc($ubicaciones)) { ?>
                            <option value="<?= (int) $reg['id_ubicacion'] ?>"><?= $escape($reg['calle'] . ' - ' . $reg['ciudad']) ?></option>
                        <?php } ?>
                    </select>
                    <label for="imagen">Foto (JPG, PNG o WebP; máximo 5 MB)</label>
                    <input id="imagen" name="imagen" type="file" accept="image/jpeg,image/png,image/webp">
                    <button type="submit">Guardar activo</button>
                </form>
            </section>
        </div>
    </main>
    <script src="../js/theme.js"></script>
</body>
</html>
