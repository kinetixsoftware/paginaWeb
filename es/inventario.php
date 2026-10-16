<?php 
session_start();

require_once "../php/conexionBDD.php";

$conexion = conectarBD();

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

$activo = "SELECT a.id_activo, a.nombre, a.modelo, a.id_marca, a.id_estado, a.id_categoria, m.marca, e.estado, c.categoria 
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
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../css/inventario.css">
<link rel="stylesheet" href="../css/kinetix-theme.css">
<title>Inventario</title>
</head>

<body>
    <!-- Menu -->
    <nav class="menu">
        <div class="menu-content">
            <div class="menu-logo">
                <img src="..\imagenes\Logo1.png" alt="Logo Kinetix">
            </div>
            <ul>
                <li><a href="inicio">Inicio</a></li>
                <li><a href="preguntas-frecuentes">Ayuda</a></li>
                <li><a href="tickets">Tickets</a></li>
                <li><a href="inventario" class="active" aria-current="page">Inventario</a></li>
                <li><a href="usuario/<?= (int) $_SESSION['idUsuario'] ?>">Mi Cuenta</a></li>
                <li><a class="logout-button" href="../php/logout"><span class="logout-sign" aria-hidden="true"></span><span class="logout-text">Cerrar sesión</span></a></li>
                <li>
                    <button type="button" id="themeToggle" class="theme-toggle" aria-label="Cambiar tema">🌙 Dark</button>
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
            <div class="toast-wrapper">
                <?php if(isset($_SESSION['mensaje'])): ?>
                <div id="formMessage" class="form-message <?= $_SESSION['tipoError'] ?>">
                    <?= htmlspecialchars($_SESSION['mensaje']) ?>
                </div>
                <?php endif; ?>
            </div>
            <div class="header">
                <div>
                    <h1>Inventario</h1>
                    <p>Seleccione un componente para ver su informacion.</p>
                </div>
                <?php if ($rolUsuario === 2) { ?>
                <a href="../php/inventario-agregar-item" class="btn-login">Agregar Item</a>
                <?php } elseif ($rolUsuario === 1) { ?>
                <form method="POST">
                    <input type="hidden" name="accion" value="verItemsPersonales">
                    <button type="submit" class="btn-login"> Ver Mis Items</button>
                </form>
                <?php } elseif ($rolUsuario === 3) { ?>
                <a href="inventario" class="btn-login">Ver todo el inventario</a>
                <?php } ?>
            </div>
            <div class="filters">
                <input id="inventorySearch" type="text" placeholder="Buscar componente" aria-label="Buscar componente">
                <select>
                    <option>Todas las categorías</option>
                    <?php while ($reg = mysqli_fetch_array($resultadocategorias)) {?>
                    <option> <?= $reg['categoria']?> </option>
                    <?php }?>
                </select>
                <select>
                    <option>Todos los estados</option>
                    <?php while ($reg = mysqli_fetch_array($resultadoestados)) {?>
                    <option> <?= $reg['estado']?> </option>
                    <?php }?>
                </select>
            </div>
            <div class="inventory">
                <?php  while ($reg = mysqli_fetch_array($resultadoactivo)) { ?>
                <a class="card" href="../php/activo/<?= (int) $reg['id_activo'] ?>">
                    <img src="https://placehold.co/400x300?text=<?=$reg['modelo']?>" alt="<?=$reg['modelo']?>">
                    <div class="info">
                        <h3><?=$reg['nombre']?></h3>
                        <p class="brand"><?=$reg['marca']?></p>
                        <div class="card-meta">
                            <span class="category"><strong>Categoría:</strong> <?=$reg['categoria']?></span>
                            <span class="status <?=$reg['estado']?>"> <?=$reg['estado']?></span>
                        </div>
                    </div>
                </a>
                <?php } ?>
            </div>
        </div>
    </div>
    <?php 
    if(isset($_SESSION['mensaje'] )){
        $_SESSION['mensaje'] = null;
        $_SESSION['tipoError'] = null;
    }
  ?>
  <script src="../js/theme.js"></script>
  <script>
    const inventorySearch = document.getElementById('inventorySearch');
    const cards = Array.from(document.querySelectorAll('.card'));

    if (inventorySearch) {
      inventorySearch.addEventListener('input', function () {
        const query = this.value.trim().toLowerCase();

        cards.forEach((card) => {
          const text = card.textContent.toLowerCase();
          const matches = !query || text.includes(query);
          card.style.display = matches ? '' : 'none';
        });
      });
    }
  </script></body>
</html>
