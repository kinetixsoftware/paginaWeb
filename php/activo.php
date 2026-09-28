<?php 
session_start();

require_once "conexionBDD.php";

$conexion = conectarBD();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Activo no encontrado.");
}

$id_activo = (int) $_GET['id'];

$sql = "SELECT a.id_activo, a.nombre, a.modelo, a.descripcion, a.numero_serie, a.fecha_adquisicion, 
               a.fecha_baja, a.id_marca, a.id_usuario, m.marca, a.id_estado, e.estado, a.id_categoria, c.categoria,
               (SELECT CONCAT('Nombre: ', 'nombre', ' Apellido: ', 'apellido') from usuario as u WHERE a.id_usuario = u.id_usuario) as NombreApellido
        FROM activo as a
        JOIN activo_marca m      ON a.id_marca = m.id_marca
        JOIN estado_activo e     ON a.id_estado = e.id_estado
        JOIN categoria_activo c  ON a.id_categoria = c.id_categoria
        LEFT JOIN usuario u      ON a.id_usuario = u.id_usuario
        WHERE a.id_activo = '$id_activo'";
$sql = mysqli_query($conexion, $sql);
$resultados = mysqli_fetch_assoc($sql);

if (empty($resultados['id_usuario']) && empty($resultados['fecha_baja'])) {
    $fechaBaja = "Aun en funcionamiento";
    $usuario = "Sin Asignar";
} else {
    $fechaBaja = $resultados['fecha_baja'];
    $usuario = $resultados['NombreApellido'];
}
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle del activo | Kinetix</title>
    <link rel="stylesheet" href="../css/inventario.css">
</head>
<body>
    <nav class="menu">
        <div class="menu-content">
            <div class="menu-logo">
                <img src="../imagenes/Logo1.png" alt="Logo Kinetix">
            </div>
            <ul>
                <li><a href="../es/inicio.php">Inicio</a></li>
                <li><a href="../es/inventario.php" class="active" aria-current="page">Inventario</a></li>
                <li><a href="../es/tickets.php">Tickets</a></li>
                <li><a href="../es/login.php">Mi cuenta</a></li>
                <li><button type="button" class="theme-toggle" aria-label="Cambiar tema">Modo</button></li>
            </ul>
        </div>
    </nav>

    <main class="page">
        <div class="container">
            <header class="header">
                <div>
                    <p class="brand">Inventario / Detalle del activo</p>
                    <h1>Información del activo</h1>
                    <p>Consulta el estado, los datos técnicos y las acciones disponibles.</p>
                </div>
                <a class="btn-login" href="../es/inventario.php">Volver al inventario</a>
            </header>

            <section class="asset-detail" aria-labelledby="asset-title">
                <div class="asset-hero">
                    <img src="https://placehold.co/800x560/0f172a/e5e7eb?text=<?= $resultados['nombre'] ?>" alt="Imagen del activo">
                    <div class="asset-hero-info">
                        <span class="status <?= $resultados['estado'] ?>"><?= $resultados['estado'] ?></span>
                        <p class="category">ID del activo: <?= $resultados['id_activo'] ?></p>
                        <h2 id="asset-title"><?= $resultados['nombre'] ?></h2>
                        <p class="brand"><?= $resultados['marca'] ?> · <?= $resultados['modelo'] ?></p>
                        <p class="asset-description"><?= $resultados['descripcion'] ?></p>
                    </div>
                </div>

                <div class="asset-layout">
                    <div class="asset-content">
                        <section class="asset-section" aria-labelledby="technical-title">
                            <h2 id="technical-title">Datos técnicos</h2>
                            <dl class="asset-data">
                                <div><dt>Número de serie</dt><dd><?= $resultados['numero_serie'] ?></dd></div>
                                <div><dt>Categoría</dt><dd><?= $resultados['categoria'] ?></dd></div>
                                <div><dt>Marca</dt><dd><?= $resultados['marca'] ?></dd></div>
                                <div><dt>Modelo</dt><dd><?= $resultados['modelo'] ?></dd></div>
                                <div><dt>Fecha de adquisición</dt><dd><?= $resultados['fecha_adquisicion'] ?></dd></div>
                                <div><dt>Fecha de baja</dt><dd><?= $fechaBaja ?></dd></div>
                            </dl>
                        </section>

                        <section class="asset-section" aria-labelledby="status-title">
                            <h2 id="status-title">Estado y seguimiento</h2>
                            <div class="asset-timeline">
                                <article><strong>Estado actual</strong><span><?= $resultados['estado'] ?></span></article>
                                <article><strong>Última actualización</strong><span>Sin actualizaciones recientes</span></article>
                                <article><strong>Responsable</strong><span><?=$usuario?></span></article>
                                <article><strong>Ubicacion</strong><span><?=$usuario?></span></article>
                            </div>
                        </section>
                    </div>
                    <?php if ($resultados['id_usuario'] === ((int) $_SESSION['idUsuario'] ?? 0)) { ?>
                    <aside class="asset-actions" aria-labelledby="actions-title">
                        <h2 id="actions-title">Acciones del activo</h2>
                        <a class="btn-login" href="#solicitar-servicio">Solicitar servicio</a>
                        <a class="asset-action-link" href="#reportar-problema">Reportar un problema</a>
                        <a class="asset-action-link" href="#asignacion">Consultar asignación</a>
                        <a class="asset-action-link" href="#historial">Ver historial</a>
                    </aside>
                    <?php } ?>
                </div>
            </section>

            <section class="formulario service-request" id="solicitar-servicio" aria-labelledby="service-title">
                <h2 id="service-title">Solicitar servicio para este activo</h2>
                <p class="brand">Completa la solicitud para que el equipo técnico pueda revisarla.</p>
                <form action="#" method="post">
                    <input type="hidden" name="id_activo" value="1">
                    <label for="service-type">Tipo de servicio</label>
                    <select id="service-type" name="tipo_servicio" required>
                        <option value="">Seleccionar servicio</option>
                        <option value="mantenimiento">Mantenimiento</option>
                        <option value="reparacion">Reparación</option>
                        <option value="revision">Revisión general</option>
                        <option value="instalacion">Instalación o configuración</option>
                    </select>

                    <label for="service-priority">Prioridad</label>
                    <select id="service-priority" name="prioridad" required>
                        <option value="">Seleccionar prioridad</option>
                        <option value="baja">Baja</option>
                        <option value="media">Media</option>
                        <option value="alta">Alta</option>
                    </select>

                    <label for="service-description">Descripción de la solicitud</label>
                    <textarea id="service-description" name="descripcion" rows="5" placeholder="Describe qué necesita el activo..." required></textarea>

                    <button type="submit">Enviar solicitud</button>
                </form>
            </section>
        </div>
    </main>

    <script>
        const themeButton = document.querySelector('.theme-toggle');
        const savedTheme = localStorage.getItem('theme') || 'dark';

        document.body.classList.toggle('light-mode', savedTheme === 'light');
        document.body.classList.toggle('dark-mode', savedTheme !== 'light');

        themeButton.addEventListener('click', () => {
            const isLight = document.body.classList.toggle('light-mode');
            document.body.classList.toggle('dark-mode', !isLight);
            localStorage.setItem('theme', isLight ? 'light' : 'dark');
        });
    </script>
</body>
</html>