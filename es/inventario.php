<?php 
session_start();

require_once "../php/conexionBDD.php";
require_once "../php/image-upload.php";

$conexion = conectarBD();
$escape = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');

$mensaje = $_SESSION['mensaje'] ?? '';
$tipoError = $_SESSION['tipoError'] ?? '';

if (!isset($_SESSION['rol'])) {
    $_SESSION['mensaje'] = "Porfavor incia sesion para obtener acceso a esta pagina";
    $_SESSION['tipoError'] = "error";
    header("Location: /Kinetix/es/login");
  exit;
}

$rolUsuario = (int) $_SESSION['rol'];

$condicion = 'a.id_marca = m.id_marca AND a.id_estado = e.id_estado AND a.id_categoria = c.id_categoria';
$idUsuarioInventario = 0;
if ($rolUsuario === 3 && isset($_GET['usuario'])) {
    $idUsuarioInventario = filter_var($_GET['usuario'], FILTER_VALIDATE_INT);
    if (!$idUsuarioInventario || $idUsuarioInventario < 1) {
        $_SESSION['mensaje'] = 'El usuario seleccionado no es válido.';
        $_SESSION['tipoError'] = 'error';
        $idUsuarioInventario = -1;
    }
    $condicion .= " AND a.id_usuario = " . (int) $idUsuarioInventario;
} elseif ($rolUsuario === 1 && $_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === "verItemsPersonales") {
    $idUsuario = (int) $_SESSION['idUsuario'];
    $condicion .= " AND a.id_usuario = $idUsuario";
}

$activo = "SELECT a.id_activo, a.nombre, a.modelo, a.imagen_path, a.id_marca, a.id_estado, a.id_categoria, m.marca, e.estado, c.categoria
           FROM activo as a, activo_marca as m, estado_activo as e, categoria_activo as c
           WHERE $condicion
           ORDER BY id_activo ASC";
$resultadoactivo = mysqli_query($conexion, $activo);

$categorias = "SELECT * FROM categoria_activo ORDER BY id_categoria ASC";
$resultadocategorias = mysqli_query($conexion, $categorias);

$estados = "SELECT * FROM estado_activo ORDER BY id_estado ASC";
$resultadoestados = mysqli_query($conexion, $estados);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<base href="/Kinetix/es/">
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" type="image/png" href="../imagenes/favicon.png">
<link rel="stylesheet" href="../css/inventario.css">
<link rel="stylesheet" href="../css/kinetix-theme.css">
<script src="../js/theme.js" defer></script>
<script src="../js/i18n.js?v=4" defer></script>
<script src="../js/inventory.js" defer></script>
<title data-i18n="inventario.title">Inventario</title>
</head>

<body>
    <!-- Menu -->
    <nav class="menu">
        <div class="menu-content">
            <div class="menu-logo">
                <img src="..\imagenes\Logo1.png" alt="Logo Kinetix">
            </div>
            <ul>
                <li><a href="inicio" data-i18n="nav.inicio">Inicio</a></li>
                <li><a href="preguntas-frecuentes" data-i18n="nav.ayuda">Ayuda</a></li>
                <li><a href="tickets" data-i18n="nav.tickets">Tickets</a></li>
                <li><a href="inventario" class="active" aria-current="page" data-i18n="nav.inventario">Inventario</a></li>
                <li><a href="usuario/<?= (int) $_SESSION['idUsuario'] ?>" data-i18n="nav.mi_cuenta">Mi Cuenta</a></li>
                <li><a class="logout-button" href="../php/logout"><span class="logout-sign" aria-hidden="true"></span><span class="logout-text" data-i18n="nav.cerrar_sesion">Cerrar sesión</span></a></li>
                <li>
                    <button type="button" id="themeToggle" class="theme-toggle" aria-label="Cambiar tema">🌙 Dark</button>
                </li>
                <li>
                    <button type="button" id="langToggle" class="switch" data-i18n-toggle aria-label="Cambiar idioma">
                        <span class="lang left">ES</span><span class="lang right">EN</span><span class="slider"></span>
                    </button>
                </li>
            </ul>
        </div>
    </nav>

    <?php if (isset($_SESSION['mensaje'])) { ?>
    <div class="toast-wrapper">
        <div id="formMessage" class="form-message <?= htmlspecialchars((string) ($_SESSION['tipoError'] ?? 'success'), ENT_QUOTES, 'UTF-8') ?>">
            <?= htmlspecialchars((string) $_SESSION['mensaje'], ENT_QUOTES, 'UTF-8') ?>
        </div>
    </div>
    <?php unset($_SESSION['mensaje'], $_SESSION['tipoError']); } ?>
    
    <div class="page">
        <div class="container">
            <div class="header">
                <div>
                    <h1 data-i18n="inventory.heading">Inventario</h1>
                    <p data-i18n="inventory.description">Seleccione un componente para ver su informacion.</p>
                </div>
                <?php if ($rolUsuario === 2) { ?>
                <a href="../php/inventario-agregar-item" class="btn-login" data-i18n="inventory.add_item">Agregar Item</a>
                <?php } elseif ($rolUsuario === 1) { ?>
                <form method="POST">
                    <input type="hidden" name="accion" value="verItemsPersonales">
                    <button type="submit" class="btn-login" data-i18n="inventory.my_items"> Ver Mis Items</button>
                </form>
                <?php } elseif ($rolUsuario === 3) { ?>
                <a href="inventario" class="btn-login" data-i18n="inventory.all_items">Ver todo el inventario</a>
                <?php } ?>
            </div>
            <section class="inventory-toolbar" aria-label="Búsqueda del inventario">
                <input id="inventorySearch" type="search" placeholder="Buscar por equipo, marca o modelo" aria-label="Buscar activos" data-i18n-attrs='{"placeholder":"inventory.search_placeholder","aria-label":"inventory.search_label"}'>
            </section>
            <div class="inventory-layout">
                <aside class="filters" aria-label="Filtros del inventario">
                    <h2 data-i18n="inventory.filters">Filtrar resultados</h2>
                    <label for="categoryFilter" data-i18n="inventory.category">Categoría</label>
                    <select id="categoryFilter" aria-label="Filtrar por categoría" data-i18n-attrs='{"aria-label":"inventory.filter_category"}'>
                        <option value="" data-i18n="inventory.all_categories">Todas las categorías</option>
                        <?php while ($reg = mysqli_fetch_array($resultadocategorias)) {?>
                        <option value="<?= (int) $reg['id_categoria'] ?>" data-i18n-db><?= $escape($reg['categoria']) ?></option>
                        <?php }?>
                    </select>
                    <label for="stateFilter" data-i18n="inventory.status">Estado</label>
                    <select id="stateFilter" aria-label="Filtrar por estado" data-i18n-attrs='{"aria-label":"inventory.filter_state"}'>
                        <option value="" data-i18n="inventory.all_states">Todos los estados</option>
                        <?php while ($reg = mysqli_fetch_array($resultadoestados)) {?>
                        <option value="<?= (int) $reg['id_estado'] ?>" data-i18n-db><?= $escape($reg['estado']) ?></option>
                        <?php }?>
                    </select>
                </aside>
                <section class="inventory-results" aria-label="Activos">
                    <div class="inventory">
                        <?php while ($reg = mysqli_fetch_array($resultadoactivo)) {
                            $imagenActivo = urlImagenSubida($reg['imagen_path']);
                        ?>
                        <a class="card" href="../php/activo/<?= (int) $reg['id_activo'] ?>" data-category="<?= (int) $reg['id_categoria'] ?>" data-state="<?= (int) $reg['id_estado'] ?>">
                            <div class="asset-visual">
                                <?php if ($imagenActivo !== null) { ?>
                                    <img src="<?= $escape($imagenActivo) ?>" alt="<?= $escape($reg['nombre']) ?>" loading="lazy">
                                <?php } else { ?>
                                    <span aria-hidden="true">IT</span>
                                <?php } ?>
                            </div>
                            <div class="info">
                                <span class="inventory-card-category" data-i18n-db><?= $escape($reg['categoria']) ?></span>
                                <h3><?= $escape($reg['nombre']) ?></h3>
                                <p class="brand"><?= $escape($reg['marca']) ?> · <?= $escape($reg['modelo']) ?></p>
                                <div class="card-meta">
                                    <span class="status <?= $escape($reg['estado']) ?>"><span data-i18n-db><?= $escape($reg['estado']) ?></span></span>
                                    <span class="inventory-card-action" data-i18n="inventory.view_item">Ver artículo</span>
                                </div>
                            </div>
                        </a>
                        <?php } ?>
                    </div>
                </section>
            </div>
            <p id="inventoryEmpty" class="inventory-empty" data-i18n="inventory.no_items" hidden>No se encontraron activos.</p>
        </div>
    </div>
    <?php 
    if(isset($_SESSION['mensaje'] )){
        $_SESSION['mensaje'] = null;
        $_SESSION['tipoError'] = null;
    }
  ?>
  </body>
</html>
