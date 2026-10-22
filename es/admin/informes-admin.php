<?php
session_start();
require_once "../../php/conexionBDD.php";

$conexion = conectarBD();
if ((int) ($_SESSION['rol'] ?? 0) !== 3) {
	$_SESSION['mensaje'] = "No tienes permiso para ver esta pagina";
	$_SESSION['tipoError'] = "error";
    header("Location: ../inicio");
	exit;
}

$usuariosResumen = mysqli_fetch_assoc(mysqli_query($conexion,
    "SELECT COUNT(*) AS total, SUM(activo = 1) AS activos,
            SUM(fecha_creacion >= DATE_SUB(NOW(), INTERVAL 24 HOUR)) AS recientes,
            SUM(id_rol = 2) AS tecnicos, SUM(id_rol = 1) AS usuarios
     FROM usuario"
));
$activosResumen = mysqli_fetch_assoc(mysqli_query($conexion,
    "SELECT COUNT(*) AS total, SUM(id_estado = 1) AS disponibles,
            SUM(id_estado = 3) AS reparacion, SUM(id_estado = 2) AS prestados
     FROM activo"
));
$ticketsResumen = mysqli_fetch_assoc(mysqli_query($conexion,
    "SELECT SUM(id_estado IN (4, 5)) AS solucionados,
            COUNT(DISTINCT CASE WHEN id_estado IN (4, 5) THEN id_activo END) AS activos_arreglados,
            SUM(id_tecnico IS NULL) AS sin_tecnico
     FROM ticket"
));
$detallesTecnicosResultado = mysqli_query($conexion,
    "SELECT u.id_usuario, CONCAT(u.nombre, ' ', u.apellido) AS NombreApellido,
            (SELECT COUNT(DISTINCT t.id_activo) FROM ticket t WHERE t.id_tecnico = u.id_usuario AND t.id_activo IS NOT NULL) AS Activos_Asignados,
            (SELECT COUNT(*) FROM ticket t WHERE t.id_tecnico = u.id_usuario) AS Tickets_Asignados,
            (SELECT COUNT(DISTINCT t.id_activo) FROM ticket t WHERE t.id_tecnico = u.id_usuario AND t.id_estado IN (4, 5) AND t.id_activo IS NOT NULL) AS Activos_Arreglados,
            (SELECT COUNT(DISTINCT t.id_activo) FROM ticket t WHERE t.id_tecnico = u.id_usuario AND t.id_estado IN (1, 3) AND t.id_activo IS NOT NULL) AS Activos_Sin_Arreglar,
            (SELECT COUNT(*) FROM ticket t WHERE t.id_tecnico = u.id_usuario AND t.id_estado IN (4, 5)) AS Tickets_Solucionados,
            (SELECT COUNT(*) FROM ticket t WHERE t.id_tecnico = u.id_usuario AND t.id_estado IN (1, 3)) AS Tickets_Sin_Solucionar
     FROM usuario u WHERE u.id_rol = 2 ORDER BY u.apellido, u.nombre"
);
$detallesUsuariosResultado = mysqli_query($conexion,
    "SELECT u.id_usuario, CONCAT(u.nombre, ' ', u.apellido) AS NombreApellido,
            (SELECT COUNT(*) FROM activo a WHERE a.id_usuario = u.id_usuario) AS Activos_Asignados,
            (SELECT COUNT(*) FROM activo a WHERE a.id_usuario = u.id_usuario AND a.id_estado = 3) AS Activos_En_Reparacion,
            (SELECT COUNT(*) FROM ticket t WHERE t.id_solicitante = u.id_usuario) AS Tickets_Creados
     FROM usuario u WHERE u.id_rol = 1 ORDER BY u.apellido, u.nombre"
);

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    if ($accion === "crear_tecnico") {
        $idUsuario = $_POST['id_usuario'] ?? '';
        if(!empty($idUsuario)) {
            $sql2 = mysqli_query($conexion, 'UPDATE usuario SET id_rol = 2 WHERE id_usuario = $idUsuario');
            $sql2 = mysqli_query($conexion, 'UPDATE activo SET id_usuario = null, id_estado = 1');
        }
    }
}
?>
<!doctype html>
<html lang="es">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Informes | Kinetix</title>
	<link rel="stylesheet" href="css/panel-admin.css">
	<link rel="stylesheet" href="../../css/kinetix-theme.css">
</head>
<body>
    <nav class="menu">
        <div class="menu-content">
            <div class="menu-logo">
                <img src="../../imagenes/Logo1.png" alt="Logo Kinetix">
            </div>
            <ul>
                <li> <a href="../inicio">Inicio</a> </li>
                <li> <a href="panel-admin">Volver al Panel</a></li>
                <li> <a href="#usuarios">Usuarios</a></li>
                <li><a class="logout-button" href="../../php/logout"><span class="logout-sign" aria-hidden="true"></span><span class="logout-text">Cerrar sesión</span></a></li>
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

	<main class="admin-panel informes-admin">
		<header class="page-header">
			<div>
				<h1>Informe general</h1>
				<p>Resumen de actividad y rendimiento de la plataforma.</p>
			</div>
			<a class="btn btn-primario" href="panel-admin">Volver al panel</a>
		</header>

		<section class="estadisticas-grid" aria-label="Resumen de informes">
			<section class="estadisticas-grupo">
				<h2 class="estadisticas-grupo-title">Usuarios</h2>
				<div class="estadisticas-grupo-grid">
					<article class="estadistica-card"><span>Usuarios registrados total</span><strong><?= (int) $usuariosResumen['total'] ?></strong></article>
					<article class="estadistica-card"><span>Usuarios activos</span><strong><?= (int) $usuariosResumen['activos'] ?></strong></article>
					<article class="estadistica-card"><span>Técnicos</span><strong><?= (int) $usuariosResumen['tecnicos'] ?></strong></article>
					<article class="estadistica-card"><span>Usuarios</span><strong><?= (int) $usuariosResumen['usuarios'] ?></strong></article>
					<article class="estadistica-card"><span>Registros últimas 24 h</span><strong><?= (int) $usuariosResumen['recientes'] ?></strong></article>
				</div>
			</section>

			<section class="estadisticas-grupo">
				<h2 class="estadisticas-grupo-title">Activos</h2>
				<div class="estadisticas-grupo-grid">
					<article class="estadistica-card"><span>Activos registrados</span><strong><?= (int) $activosResumen['total'] ?></strong></article>
					<article class="estadistica-card"><span>Activos arreglados (con ticket solucionado)</span><strong><?= (int) $ticketsResumen['activos_arreglados'] ?></strong></article>
					<article class="estadistica-card"><span>Activos disponibles</span><strong><?= (int) $activosResumen['disponibles'] ?></strong></article>
					<article class="estadistica-card"><span>Activos en reparación</span><strong><?= (int) $activosResumen['reparacion'] ?></strong></article>
					<article class="estadistica-card"><span>Activos prestados</span><strong><?= (int) $activosResumen['prestados'] ?></strong></article>
				</div>
			</section>

			<section class="estadisticas-grupo">
				<h2 class="estadisticas-grupo-title">Tickets</h2>
				<div class="estadisticas-grupo-grid">
					<article class="estadistica-card"><span>Tickets solucionados</span><strong><?= (int) $ticketsResumen['solucionados'] ?></strong></article>
					<article class="estadistica-card"><span>Tickets sin técnico asignado</span><strong><?= (int) $ticketsResumen['sin_tecnico'] ?></strong></article>
				</div>
			</section>
		</section>
        <!--informacion sobre los tecnicos-->
		<section class="admin-card report-summary">
			<div class="section-heading"><h2>Informe general sobre los tecnicos</h2><span><?= date('d/m/Y H:i') ?></span></div>
			<div class="tecnicos-heading">
				<h2>Detalles de los tecnicos</h2>
				<span>Informacion sobre el rendimiento de los tecnicos</span>
			</div>
			<div class="tecnicos-grid">
                <?php while ($reg = mysqli_fetch_assoc($detallesTecnicosResultado)) {?>
					<article class="tecnicos-card">
						<div class="tecnicos-card-header">
							<div>
								<span class="tecnicos-label">Tecnico</span>
								<h3><?= $reg['NombreApellido'] ?></h3>
							</div>
							<span class="tecnicos-id">ID: <?= (int) $reg['id_usuario'] ?></span>
						</div>
						<div class="tecnicos-detalles">
                            <p><span>Tickets asignados</span><strong> <?= $reg['Tickets_Asignados'] ?> </strong></p>
                            <p><span>Activos asignados</span><strong> <?= $reg['Activos_Asignados'] ?> </strong></p>
							<p><span>Tickets sin solucionar</span><strong> <?= $reg['Tickets_Sin_Solucionar'] ?> </strong></p>
                            <p><span>Activos sin arreglar</span><strong> <?= $reg['Activos_Sin_Arreglar'] ?> </strong></p>
                            <p><span>Tickets solucionados</span><strong> <?= $reg['Tickets_Solucionados'] ?> </strong></p>
							<p><span>Activos arreglados</span><strong> <?= $reg['Activos_Arreglados'] ?> </strong></p>
						</div>
					</article>
                <?php } ?>
			</div>
            <div class="section-heading usuarios-heading" id="usuarios">
                <h2>Informe general sobre los usuarios</h2>
                <span><?= date('d/m/Y H:i') ?></span>
            </div>
            <div class="tecnicos-heading">
                <h2>Detalles de los usuarios</h2>
                <span>Información sobre la actividad de los usuarios</span>
            </div>
            <div class="tecnicos-grid">
                <?php while ($reg = mysqli_fetch_assoc($detallesUsuariosResultado)) { ?>
                    <article class="tecnicos-card">
                        <div class="tecnicos-card-header">
                            <div>
                                <span class="tecnicos-label">Usuario</span>
                                <h3><?= htmlspecialchars($reg['NombreApellido'], ENT_QUOTES, 'UTF-8') ?></h3>
                            </div>
                            <div class="usuario-id-actions">
                                <form method="POST">
                                    <input type="hidden" name="accion" value="crear_tecnico">
                                    <input type="hidden" name="idUsuario" value="<?= (int) $reg['id_usuario'] ?>">
                                    <button class="btn btn-primario" href="../inventario/usuario/<?= (int) $reg['id_usuario'] ?>">Hacer Tecnico</button>
                                </form>
                                <a class="btn btn-primario" href="../inventario/usuario/<?= (int) $reg['id_usuario'] ?>">Mostrar Activos</a>
                                <span class="tecnicos-id">ID: <?= (int) $reg['id_usuario'] ?></span>
                            </div>
                        </div>
                        <div class="tecnicos-detalles">
                            <p><span>Tickets creados</span><strong><?= (int) $reg['Tickets_Creados'] ?></strong></p>
                            <p><span>Activos asignados</span><strong><?= (int) $reg['Activos_Asignados'] ?></strong></p>
                            <p><span>Activos en reparación</span><strong><?= (int) $reg['Activos_En_Reparacion'] ?></strong></p>
                        </div>
                    </article>
                <?php } ?>
            </div>
		</section>
	</main>
</body>
</html>
