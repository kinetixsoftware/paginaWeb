<?php
session_start();

require_once "../php/conexionBDD.php";

$conexion = conectarBD();

$rol = (int) ($_SESSION['rol'] ?? 0);

if ($rol !== 3) {
    $_SESSION['mensaje'] = "No tienes permiso para ver esta pagina";
    $_SESSION['tipoError'] = "error";
    header("Location: ../es/inicio.php");
    exit;
}


$tablas = [
    'activo' => ['id_activo', 'codigo_inventario', 'nombre', 'descripcion', 'marca', 'modelo', 'numero_serie', 'fecha_adquisicion', 'fecha_baja', 'id_categoria', 'id_estado', 'id_ubicacion', 'id_marca', 'id_usuario'],
    'activo_marca' => ['id_marca', 'marca'],
    'categoria_activo' => ['id_categoria', 'categoria'],
    'categoria_ticket' => ['id_categoria', 'categoria'],
    'diagnostico' => ['id_diagnostico', 'descripcion', 'fecha', 'id_ticket', 'id_tecnico'],
    'estado_activo' => ['id_estado', 'estado'],
    'estado_solicitud' => ['id_estado', 'estado'],
    'estado_ticket' => ['id_estado', 'estado'],
    'historial_activo' => ['id_historial', 'id_activo', 'fecha', 'accion'],
    'historial_ticket' => ['id_historial', 'id_ticket', 'estado_anterior', 'estado_nuevo', 'fecha'],
    'mensaje_ticket' => ['id_mensaje', 'id_ticket', 'id_usuario', 'contenido', 'fecha_creacion'],
    'prioridad_ticket' => ['id_prioridad', 'prioridad'],
    'pregunta_frecuente' => ['id_pregunta', 'pregunta', 'respuesta'],
    'resultado_ticket' => ['id_resultado', 'solucion', 'fecha_resolucion', 'id_ticket', 'id_tecnico'],
    'rol' => ['id_rol', 'nombre_rol'],
    'solicitud_servicio' => ['id_solicitud', 'descripcion', 'fecha_creacion', 'aprobacion', 'id_estado', 'id_solicitante', 'id_ubicacion', 'id_activo', 'prioridad', 'tipo_servicio'],
    'ticket' => ['id_ticket', 'titulo', 'descripcion', 'fecha_creacion', 'id_estado', 'id_prioridad', 'id_solicitante', 'id_tecnico', 'id_activo', 'id_categoria'],
    'ubicacion' => ['id_ubicacion', 'calle', 'id_ciudad'],
    'ciudad' => ['id_ciudad', 'ciudad', 'departamento'],
    'usuario' => ['id_usuario', 'nombre', 'apellido', 'email', 'password', 'activo', 'id_rol', 'id_ubicacion']
];

$tabla = $_POST['tabla'] ?? $_GET['tabla'] ?? '';

if (!isset($tablas[$tabla])) {
    $_SESSION['mensaje'] = 'Tabla no válida.';
    $_SESSION['tipoError'] = 'error';
    header('Location: ../es/panel-admin.php');
    exit;
}

$columnas = $tablas[$tabla];
$clavePrimaria = $columnas[0];

mysqli_set_charset($conexion, 'utf8mb4');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'ingresar') {
    $campos = [];

    foreach ($columnas as $columna) {
        if ($columna === $clavePrimaria) {
            continue;
        }

        $valor = $_POST[$columna] ?? '';

        if ($tabla === 'usuario' && $columna === 'password') {
            if ($valor === '') {
                continue;
            }
            $valor = password_hash($valor, PASSWORD_DEFAULT);
        }

        if ($valor === '' && in_array($columna, ['fecha_baja', 'imagenPATH', 'imagen_path'], true)) {
            $campos[] = "`$columna` = NULL";
        } else {
            $valor = mysqli_real_escape_string($conexion, trim($valor));
            $campos[] = "`$columna` = '$valor'";
        }
    }

    if (empty($campos)) {
        $_SESSION['mensaje'] = 'No se recibieron campos para insertar.';
        $_SESSION['tipoError'] = 'error';
        header('Location: ../es/panel-admin.php');
        exit;
    }

    $consultaIngresar = "INSERT INTO `$tabla` SET " . implode(', ', $campos);

    if (!mysqli_query($conexion, $consultaIngresar)) {
        $_SESSION['mensaje'] = "Error al ingresar registro: " . mysqli_error($conexion);
        $_SESSION['tipoError'] = "error";
        header('Location: ../es/panel-admin.php');
        exit;
    }

    $_SESSION['mensaje'] = "Registro ingresado correctamente.";
    $_SESSION['tipoError'] = "success";
    header('Location: ../es/panel-admin.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel administrador</title>
    <link rel="stylesheet" href="../css/panel-admin.css">
    <link rel="stylesheet" href="../css/kinetix-theme.css">
</head>
<body class="admin-record-body">
    <header class="admin-record-header">
        <a href="../es/panel-admin.php" class="admin-record-brand">
            <img src="../imagenes/Logo1.png" alt="Kinetix">
            <span>Centro de administración</span>
        </a>
        <a class="admin-record-back" href="../es/panel-admin.php">Volver al panel</a>
    </header>

    <main class="admin-record-page">
        <section class="admin-record-card">
            <div class="admin-record-intro">
                <p class="admin-record-eyebrow">GESTIÓN DE DATOS</p>
                <h1>Agregar registro</h1>
                <p>Completá los campos para agregar información a <strong><?= htmlspecialchars($tabla, ENT_QUOTES, 'UTF-8') ?></strong>.</p>
            </div>
            <form class="admin-record-form" method="post">
                <input type="hidden" name="tabla" value="<?= $tabla?>">
                <input type="hidden" name="accion" value="ingresar">
                <?php foreach ($columnas as $columna) { ?>
                    <?php if ($columna === $clavePrimaria) { continue; } ?>
                    <div class="admin-record-field <?= in_array($columna, ['descripcion', 'mensaje', 'contenido', 'solucion', 'pregunta', 'respuesta', 'accion'], true) ? 'wide' : '' ?>">
                    <label for="<?= htmlspecialchars($columna, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $columna)), ENT_QUOTES, 'UTF-8') ?></label>
                    <?php if (in_array($columna, ['descripcion', 'mensaje', 'contenido', 'solucion', 'pregunta', 'respuesta', 'accion'], true)) { ?>
                        <textarea id="<?= htmlspecialchars($columna, ENT_QUOTES, 'UTF-8') ?>" name="<?= htmlspecialchars($columna, ENT_QUOTES, 'UTF-8') ?>" rows="4"></textarea>
                    <?php } else { ?>
                        <input id="<?= htmlspecialchars($columna, ENT_QUOTES, 'UTF-8') ?>" name="<?= htmlspecialchars($columna, ENT_QUOTES, 'UTF-8') ?>" type="<?= $columna === 'password' ? 'password' : 'text' ?>" <?= $columna === 'password' ? 'autocomplete="new-password"' : '' ?>>
                    <?php } ?>
                    </div>
                <?php } ?>

                <div class="admin-record-actions">
                    <button type="submit">Agregar registro</button>
                    <a href="../es/panel-admin.php">Cancelar</a>
                </div>
            </form>
        </section>
    </main>
</body>
</html>