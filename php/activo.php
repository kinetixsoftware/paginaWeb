<?php 
session_start();

require_once "conexionBDD.php";

$conexion = conectarBD();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['mensaje'] = "Activo no encontrado";
    $_SESSION['tipoError'] = "error";
    header("Location: inicio.php");
    exit;
}

// Leer el rol y el ID del usuario que inicio sesion.
$rol = isset($_SESSION['rol']) ? (int) $_SESSION['rol'] : null;
$idUsuario = (int) ($_SESSION['idUsuario'] ?? 0);

// Permitir el acceso solo a usuarios y tecnicos.
if ($rol === null || $rol === 3) {
    $_SESSION['mensaje'] = "No estas autorizado a ver esta pagina";
    $_SESSION['tipoError'] = "error";
    header("Location: inicio.php");
    exit;
}

$id_activo = (int) $_GET['id'];
$resultados = null;
$verificarTicket = "SELECT COUNT(*) as CantidadActivos FROM activo WHERE id_activo = $id_activo";
$verificarTicket = mysqli_query($conexion, $verificarTicket);
$verificarTicket = mysqli_fetch_assoc($verificarTicket);

if ($verificarTicket['CantidadActivos'] == 0) {
    $_SESSION['mensaje'] = "No existe el activo que estas intentando ver";
    $_SESSION['tipoError'] = "error";
    header("Location: ../es/inicio.php");
    exit;
}

if($id_activo > 0) {
    $sql = "SELECT a.id_activo, a.nombre, a.modelo, a.descripcion, a.numero_serie, a.fecha_adquisicion, 
               a.fecha_baja, a.id_marca, a.id_usuario, m.marca, a.id_estado, e.estado, a.id_categoria, c.categoria,
               (SELECT CONCAT('Fecha: ', ha.fecha ,'<br> Accion: ', ha.accion) from historial_activo as ha WHERE ha.id_activo = a.id_activo ORDER BY fecha DESC LIMIT 1) as UltimaActualizacion,
               (SELECT CONCAT('Nombre: ', u.nombre, '<br> Apellido: ', u.apellido) from usuario as u WHERE a.id_usuario = u.id_usuario) as NombreApellido,
               (SELECT CONCAT('Calle: ', ub.calle, ' <br> Ciudad: ', c.ciudad,'<br> Departamento: ', c.departamento) FROM ubicacion AS ub JOIN ciudad AS c ON ub.id_ciudad = c.id_ciudad WHERE ub.id_ubicacion = a.id_ubicacion) AS UbicacionActivo
        FROM activo as a
        JOIN activo_marca m      ON a.id_marca = m.id_marca
        JOIN estado_activo e     ON a.id_estado = e.id_estado
        JOIN categoria_activo c  ON a.id_categoria = c.id_categoria
        LEFT JOIN usuario u      ON a.id_usuario = u.id_usuario
        WHERE a.id_activo = '$id_activo'";
    $sql = mysqli_query($conexion, $sql);
    $resultados = mysqli_fetch_assoc($sql);
    if($_SERVER['REQUEST_METHOD'] === 'POST') {
        if ($_POST['accion'] == "solicitarActivo"){
            $sql = "SELECT id_ubicacion FROM usuario WHERE id_usuario = $idUsuario";
            $sql = mysqli_query($conexion, $sql);
            $res = mysqli_fetch_assoc($sql);
            $ubicacion = $res['id_ubicacion'];

            $sql = "UPDATE activo SET id_usuario = $idUsuario, id_ubicacion = $ubicacion, id_estado = 2 WHERE id_activo = $id_activo AND id_usuario IS NULL";
            $sql = mysqli_query($conexion, $sql);
            if($sql) {
                $sql = "INSERT INTO historial_activo(id_activo, accion) VALUES ($id_activo, 'Usuario: $idUsuario ahora es responsable de este activo')";
                $sql = mysqli_query($conexion, $sql);

                header("Location: ../es/inventario.php");
                exit;
            }
        }
        if (($_POST['accion'] ?? '') === "solicitar" && $rol === 1 && (int) ($resultados['id_usuario'] ?? 0) === $idUsuario) {
            $tiposServicio = ['mantenimiento', 'reparacion', 'revision', 'instalacion'];
            $prioridades = ['baja' => 'Baja', 'media' => 'Media', 'alta' => 'Alta'];
            $tipoServicio = $_POST['tipo_servicio'] ?? '';
            $prioridadEnviada = strtolower($_POST['prioridad'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');

            if (in_array($tipoServicio, $tiposServicio, true) && isset($prioridades[$prioridadEnviada]) && $descripcion !== '') {
                $prioridad = $prioridades[$prioridadEnviada];

                $ubicacionUsuario = mysqli_query($conexion, "SELECT id_ubicacion FROM usuario WHERE id_usuario = $idUsuario LIMIT 1");
                $ubicacionData = mysqli_fetch_assoc($ubicacionUsuario);
                $idUbicacionSolicitud = (int) ($ubicacionData['id_ubicacion'] ?? 0);

                if ($idUbicacionSolicitud > 0) {
                    $sql = "INSERT INTO solicitud_servicio (descripcion, prioridad, tipo_servicio, id_ubicacion, id_activo, id_solicitante, id_estado)
                            VALUES ('$descripcion', '$prioridad', '$tipoServicio', $idUbicacionSolicitud, $id_activo, $idUsuario, 1)";

                    if (mysqli_query($conexion, $sql)) {
                        $accion = mysqli_real_escape_string($conexion, "El usuario (ID: $idUsuario) solicito un servicio ($tipoServicio)");
                        $sql = "INSERT INTO historial_activo (id_activo, accion)
                                VALUES ($id_activo, '$accion')";
                        mysqli_query($conexion, $sql);
                        header("Location: ../es/tickets.php?vista=solicitudes");
                        exit;
                    }
                }
            }
        }
        if ($_POST['accion'] == "solicitarServicio"){

        }
    }
}

$idResponsable = (int) ($resultados['id_usuario'] ?? 0);
$usuario = empty($resultados['id_usuario']) ? "Sin Asignar" : $resultados['NombreApellido']; 
$fechaBaja = $resultados['fecha_baja'] == "0000-00-00" ? "Aun en funcionamiento" : $resultados['fecha_baja'];
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $resultados['nombre'] ?> | Kinetix</title>
    <link rel="stylesheet" href="../css/inventario.css">
    <link rel="stylesheet" href="../css/kinetix-theme.css">
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
                <li><a href="../es/usuario.php?id=<?= $idUsuario ?>">Mi Cuenta</a></li>
                <li><button type="button" class="theme-toggle" aria-label="Cambiar tema">Modo</button></li>
            </ul>
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
                                <article><strong>Última actualización</strong><span><?=$resultados['UltimaActualizacion']?></span></article>
                                <article><strong>Responsable</strong><span><?=$usuario?></span></article>
                                <article><strong>Ubicacion</strong><span><?=$resultados['UbicacionActivo']?></span></article>
                            </div>
                        </section>
                    </div>
                    <aside class="asset-actions" aria-labelledby="actions-title">
                        <h2 id="actions-title">Acciones del activo</h2>
                        <?php if ($idResponsable === $idUsuario && $rol === 1) { ?>
                        <a class="btn-login" href="#solicitar-servicio">Solicitar servicio</a>
                        <a class="asset-action-link" href="#reportar-problema">Reportar un problema</a>
                        <a class="asset-action-link" href="#historial">Ver historial</a>
                        <?php } elseif ($rol === 1 && empty($resultados['id_usuario']) && $resultados['id_estado'] != 3) {?>
                        <form method="POST">
                            <input type="hidden" name="accion" value="solicitarActivo">
                            <button type="submit" class="btn-login">Solicitar Activo</button>
                        </form>
                        <?php } elseif ($rol === 2) {?>
                            <a class="asset-action-link">Un tecnico no puede utilizar estas acciones</a>
                        <?php } ?>
                    </aside>
                </div>
            </section>

            <?php if ($idResponsable === $idUsuario && $rol === 1) { ?>
            <section class="formulario service-request" id="solicitar-servicio" aria-labelledby="service-title">
                <h2 id="service-title">Solicitar servicio para este activo</h2>
                <p class="brand">Completa la solicitud para que el equipo técnico pueda revisarla.</p>
                <form method="post">
                    <input type="hidden" name="accion" value="solicitar">
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
                        <option value="Baja">Baja</option>
                        <option value="Media">Media</option>
                        <option value="Alta">Alta</option>
                    </select>

                    <label for="service-description">Descripción de la solicitud</label>
                    <textarea id="service-description" name="descripcion" rows="5" placeholder="Describe qué necesita el activo..." required></textarea>

                    <button type="submit">Enviar solicitud</button>
                </form>
            </section>
            <?php } ?>
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