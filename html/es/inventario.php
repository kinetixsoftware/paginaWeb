<?php 
session_start();
$mensaje = $_SESSION['mensaje'] ?? '';
$tipoError = $_SESSION['tipoError'] ?? '';

if (!isset($_SESSION['rol'])) {
    $_SESSION['mensaje'] = "Porfavor incia sesion para obtener acceso a esta pagina";
    $_SESSION['tipoError'] = "error";
    header("Location: login.php");
  exit;
}

$rolUsuario = (int) $_SESSION['rol'];

$servername = "localhost";
$username = "root";
$passwordbd = "";
$dbname = "kinetixsoftware";

$conexion = mysqli_connect($servername, $username, $passwordbd, $dbname);

if (!$conexion) {
    die("Connection failed: " . mysqli_connect_error());
}   

//Query secundario (no se si esta bien)
//SELECT a.id_activo, a.nombre, a.modelo, a.id_marca, m.marca, a.id_estado, e.estado, a.id_categoria, c.categoria FROM activo AS a 
// JOIN activo_marca AS m ON a.id_marca = m.id_marca 
// JOIN estado_activo AS e ON a.id_estado = e.id_estado
// JOIN categoria_activo AS c ON a.id_categoria = c.id_categoria
//ORDER BY a.id_activo ASC;

$activo = "SELECT a.id_activo, a.nombre, a.modelo, a.id_marca, a.id_estado, a.id_categoria, m.marca, e.estado, c.categoria 
           FROM activo as a, activo_marca as m, estado_activo as e, categoria_activo as c 
           WHERE a.id_marca = m.id_marca 
                AND a.id_estado = e.id_estado
                AND a.id_categoria = c.id_categoria
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
                <img src="..\imagenes\Logo1.png" alt="Logo Kinetix">
            </div>
            <ul>
                <li><a href="inicio.php">Inicio</a></li>
                <li><a href="FAQ-pagina-cliente.html">FAQ</a></li>
                <li><a href="pagina-de-tickets-tecnico.html">Tickets</a></li>
                <li><a href="Login.php">Mi Cuenta</a></li>
                <li>
                    <button type="button" id="themeToggle" class="theme-toggle" aria-label="Cambiar tema">🌙 Dark</button>
                </li>
            </ul>
        </div>
    </nav>

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
                <a href="../php/inventario-agregar-item.php" class="btn-login" <?php if ($rolUsuario === 1) echo 'hidden'; ?>>Agregar Item</a>
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
                <a class="card" href="activo.php?id=<?= $reg['id_activo']?>">
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
