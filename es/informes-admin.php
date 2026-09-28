<?php
session_start();
require_once "../php/conexionBDD.php";

$conexion = conectarBD();
if (!$conexion) {
	$_SESSION['mensaje'] = "Error al conectar con la base de datos";
	$_SESSION['tipoError'] = "error";
    header("Location: inicio.php");
	exit;
}

$usuariosRegistrados = $activosDisponibles = $usuariosActivos = $registrados24 = $tecnicos = $usuarios = $activos = 
$activosArreglados = $ticketsSolucionados = $ticketsSinTecnico = $activosEnReparacion = $activosDisponibles = $activosPrestados = "";

if ((int) ($_SESSION['rol'] ?? 0) === 3) {
    $sql = "SELECT COUNT(id_usuario) as total FROM usuario";$sqlQ = mysqli_query($conexion, $sql);$sqlQ = mysqli_fetch_assoc($sqlQ);
    $usuariosRegistrados = $sqlQ['total'];

    $sql = "SELECT COUNT(id_usuario) as total FROM usuario WHERE activo = 1";$sqlQ = mysqli_query($conexion, $sql);$sqlQ = mysqli_fetch_assoc($sqlQ);
    $usuariosActivos = $sqlQ['total'];

    $sql = "SELECT COUNT(id_usuario) as total FROM usuario WHERE fecha_creacion >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";$sqlQ = mysqli_query($conexion, $sql); $sqlQ = mysqli_fetch_assoc($sqlQ);
    $registrados24 = $sqlQ['total'];

    $sql = "SELECT COUNT(id_usuario) as total FROM usuario WHERE id_rol = 2";$sqlQ = mysqli_query($conexion, $sql);$sqlQ = mysqli_fetch_assoc($sqlQ);
    $tecnicos = $sqlQ['total'];

    $sql = "SELECT COUNT(id_usuario) as total FROM usuario WHERE id_rol = 1";$sqlQ = mysqli_query($conexion, $sql);$sqlQ = mysqli_fetch_assoc($sqlQ);
    $usuarios = $sqlQ['total'];

    $sql = "SELECT COUNT(id_activo) as total FROM activo";$sqlQ = mysqli_query($conexion, $sql);$sqlQ = mysqli_fetch_assoc($sqlQ);
    $activos = $sqlQ['total'];

    $sql = "SELECT COUNT(id_activo) as total FROM activo WHERE id_estado = 1";$sqlQ = mysqli_query($conexion, $sql);$sqlQ = mysqli_fetch_assoc($sqlQ);
    $activosDisponibles = $sqlQ['total'];

    $detallesTecnicos = "SELECT u.id_usuario, CONCAT(nombre, ' ', apellido) AS NombreApellido, 
            (SELECT COUNT(t.id_activo) FROM ticket t WHERE t.id_tecnico = u.id_usuario AND t.id_tecnico IS NOT NULL) AS Activos_Asignados,
            (SELECT COUNT(*) FROM ticket t WHERE t.id_tecnico = u.id_usuario) AS Tickets_Asignados,
            (SELECT COUNT(t.id_activo) FROM ticket t WHERE t.id_tecnico = u.id_usuario AND t.id_tecnico IS NOT NULL AND t.id_estado IN (4,5)) AS Activos_Arreglados,
            (SELECT COUNT(t.id_activo) FROM ticket t WHERE t.id_tecnico = u.id_usuario AND t.id_tecnico IS NOT NULL AND t.id_estado = 2) AS Activos_Sin_Arreglar,
            (SELECT COUNT(*) FROM ticket t WHERE t.id_tecnico = u.id_usuario AND t.id_estado IN (4,5)) AS Tickets_Solucionados,
            (SELECT COUNT(*) FROM ticket t WHERE t.id_tecnico = u.id_usuario AND t.id_estado = 3) AS Tickets_Sin_Solucionar
            FROM usuario as u WHERE u.id_rol = 2";
    $detallesTecnicosResultado = mysqli_query($conexion, $detallesTecnicos);

    $detallesUsuarios = "SELECT u.id_usuario, CONCAT(nombre, ' ', apellido) AS NombreApellido, 
            (SELECT COUNT(a.id_activo) FROM activo a WHERE a.id_usuario = u.id_usuario AND a.id_estado = 3) AS Activos_En_Reparacion,
            (SELECT COUNT(a.id_activo) FROM activo a WHERE a.id_usuario = u.id_usuario) AS Activos_Solicitados,
            (SELECT COUNT(*) FROM ticket t WHERE t.id_solicitante = u.id_usuario) AS Tickets_Creados
            FROM usuario as u WHERE u.id_rol = 1";
    $detallesUsuariosResultado = mysqli_query($conexion, $detallesUsuarios);

} else {
	$_SESSION['mensaje'] = "No tienes permiso para ver esta pagina";
	$_SESSION['tipoError'] = "error";
    header("Location: inicio.php");
	exit;
}
?>
<!doctype html>
<html lang="es">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Informes | Kinetix</title>
	<link rel="stylesheet" href="../css/panel-admin.css">
</head>
<body>
    <nav class="menu">
        <div class="menu-content">
            <div class="menu-logo">
                <img src="../imagenes/Logo1.png" alt="Logo Kinetix">
            </div>
            <ul>
                <li> <a href="inicio.php">Inicio</a> </li>
                <li> <a href="panel-admin.php">Volver al Panel</a></li>
                <li> <a href="../php/logout.php">Cerrar sesion</a> </li>
            </ul>
        </div>
    </nav>
	<main class="admin-panel informes-admin">
		<header class="page-header">
			<div>
				<h1>Informe general</h1>
				<p>Resumen de actividad y rendimiento de la plataforma.</p>
			</div>
			<a class="btn btn-primario" href="panel-admin.php">Volver al panel</a>
		</header>

		<section class="estadisticas-grid" aria-label="Resumen de informes">
			<section class="estadisticas-grupo">
				<h2 class="estadisticas-grupo-title">Usuarios</h2>
				<div class="estadisticas-grupo-grid">
					<article class="estadistica-card"><span>Usuarios registrados total</span><strong><?= $usuariosRegistrados ?></strong></article>
					<article class="estadistica-card"><span>Usuarios activos</span><strong><?= $usuariosActivos ?></strong></article>
					<article class="estadistica-card"><span>Técnicos</span><strong><?= $tecnicos ?></strong></article>
					<article class="estadistica-card"><span>Usuarios</span><strong><?= $usuarios ?></strong></article>
					<article class="estadistica-card"><span>Registros últimas 24 h</span><strong><?= $registrados24 ?></strong></article>
					<article class="estadistica-card"><span>Visitas a la página principal</span><strong>0</strong></article>
				</div>
			</section>

			<section class="estadisticas-grupo">
				<h2 class="estadisticas-grupo-title">Activos</h2>
				<div class="estadisticas-grupo-grid">
					<article class="estadistica-card"><span>Activos registrados</span><strong><?= $activos ?></strong></article>
					<article class="estadistica-card"><span>Activos arreglados</span><strong><?= $activosArreglados ?></strong></article>
					<article class="estadistica-card"><span>Activos disponibles</span><strong><?= $activosDisponibles ?></strong></article>
					<article class="estadistica-card"><span>Activos en reparación</span><strong><?= $activosEnReparacion ?></strong></article>
					<article class="estadistica-card"><span>Activos prestados</span><strong><?= $activosPrestados ?></strong></article>
				</div>
			</section>

			<section class="estadisticas-grupo">
				<h2 class="estadisticas-grupo-title">Tickets</h2>
				<div class="estadisticas-grupo-grid">
					<article class="estadistica-card"><span>Tickets solucionados</span><strong><?= $ticketsSolucionados ?></strong></article>
					<article class="estadistica-card"><span>Tickets sin técnico asignado</span><strong><?= $ticketsSinTecnico ?></strong></article>
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
								<h3> <?= $reg['NombreApellido'] ?></h3>
							</div>
							<span class="tecnicos-id">ID: <?= $reg['id_usuario'] ?></span>
						</div>
						<div class="tecnicos-detalles">
                            <p><span>Tickets asignados</span><strong> <?= $reg['Tickets_Asignados'] ?> </strong></p>
                            <p><span>Activos asignados</span><strong> <?= $reg['Activos_Asignados'] ?> </strong></p>
							<p><span>Tickets sin solucionar</span><strong> <?= $reg['Tickets_Sin_Solucionar'] ?> </strong></p>
                            <p><span>Activos sin arreglar</span><strong> <?= $reg['Activos_Asignados'] ?> </strong></p>
                            <p><span>Tickets solucionados</span><strong> <?= $reg['Tickets_Solucionados'] ?> </strong></p>
							<p><span>Activos arreglados</span><strong> <?= $reg['Activos_Arreglados'] ?> </strong></p>
						</div>
					</article>
                <?php } ?>
			</div>
            <div class="section-heading usuarios-heading"><h2>Informe general sobre los usuarios</h2><span><?= date('d/m/Y H:i') ?></span></div>
            <div class="tecnicos-heading">
				<h2>Detalles de los usuarios</h2>
				<span>Informacion sobre la actividad de los usuarios</span>
			</div>
            <div class="tecnicos-grid">
                <?php while ($reg = mysqli_fetch_assoc($detallesUsuariosResultado)) {?>
					<article class="tecnicos-card">
						<div class="tecnicos-card-header">
							<div>
								<span class="tecnicos-label">Usuarios</span>
								<h3> <?= $reg['NombreApellido'] ?></h3>
							</div>
							<span class="tecnicos-id">ID: <?= $reg['id_usuario'] ?></span>
						</div>
						<div class="tecnicos-detalles">
                            <p><span>Tickets creados</span><strong> <?= $reg['Tickets_Creados'] ?> </strong></p>
                            <p><span>Activos en posesion</span><strong> <?= $reg['Activos_En_Reparacion'] ?> </strong></p>
                            <p><span>Activos sin arreglar</span><strong> <?= $reg['Activos_Solicitados'] ?> </strong></p>
						</div>
					</article>
                <?php } ?>
			</div>
		</section>
	</main>
</body>
</html>
