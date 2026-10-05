<?php
session_start();

require_once "../php/conexionBDD.php";

$idUsuario = (int) ($_GET['id'] ?? 0);

if ($idUsuario <= 0 ) {
	$_SESSION['mensaje'] = "No tienes permiso para ver este perfil.";
	$_SESSION['tipoError'] = "error";
	header("Location: inicio.php");
	exit;
}

$conexion = conectarBD();
$sql = "SELECT u.id_usuario, u.nombre, u.apellido, u.email, u.fecha_creacion,
			   r.nombre_rol, ub.calle, c.ciudad, ub.telefono,
               (SELECT COUNT(*) FROM activo AS a WHERE a.id_usuario = $idUsuario AND a.id_usuario IS NOT NULL) as Cantidad_Activos ## aca van mas 
		FROM usuario AS u
		LEFT JOIN rol AS r ON r.id_rol = u.id_rol
		LEFT JOIN ubicacion AS ub ON ub.id_ubicacion = u.id_ubicacion
		LEFT JOIN ciudad AS c ON c.id_ciudad = ub.id_ciudad
		WHERE u.id_usuario = $idUsuario
		LIMIT 1";
$resultado = mysqli_query($conexion, $sql);
$usuario = mysqli_fetch_assoc($resultado);

if (!$usuario) {
	$_SESSION['mensaje'] = "El usuario no existe.";
	$_SESSION['tipoError'] = "error";
	header("Location: inicio.php");
	exit;
} elseif($usuario['nombre_rol'] == "Administrador") {
    $_SESSION['mensaje'] = "Este usuario es un administrador, no tienes permiso para ver este perfil.";
	$_SESSION['tipoError'] = "error";
	header("Location: inicio.php");
	exit;
}

$nombreCompleto = $usuario['nombre'] . ' ' . $usuario['apellido'];
$ubicacion = $usuario['calle'] . ' - ' . $usuario['ciudad'] . ' <br> Telefono: ' . $usuario['telefono'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Mi cuenta | Kinetix</title>
	<link rel="icon" type="image/png" href="../imagenes/Icon.png">
	<link rel="stylesheet" href="../css/usuario.css">
	<link rel="stylesheet" href="../css/kinetix-theme.css">
</head>
<body>
	<nav class="menu">
		<div class="menu-content">
			<a class="menu-logo" href="inicio.php" aria-label="Volver al inicio">
				<img src="../imagenes/Logo1.png" alt="Logo Kinetix">
			</a>
			<ul>
				<li><a href="inicio.php">Inicio</a></li>
				<li><a href="tickets.php">Tickets</a></li>
				<li><a href="preguntas-frecuentes.php">Ayuda</a></li>
				<li><a href="inventario.php">Inventario</a></li>
				<li><a class="active" href="usuario.php?id=<?= $idUsuario ?>">Mi cuenta</a></li>
				<li><a href="../php/logout.php">Cerrar sesión</a></li>
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
	
	<main class="perfil-page">
		<section class="perfil-header">
			<div>
				<p class="eyebrow">Cuenta personal</p>
				<h1>Perfil del Usuario #<?= $usuario['id_usuario'] ?></h1>
				<p>Consultá la información asociada a esta cuenta de Kinetix.</p>
			</div>
			<?php if ($idUsuario === ((int) $_SESSION['idUsuario'] ?? 0)) { ?>
				<a class="perfil-action" href="usuario-config.php>">Configurar perfil</a>
			<?php } ?>
		</section>

		<section class="perfil-card" aria-labelledby="perfil-name">
			<div class="perfil-avatar" aria-hidden="true">
				<?= $usuario['nombre'] ?>
			</div>
			<div class="perfil-summary">
				<p class="perfil-label">Usuario #<?= $usuario['id_usuario'] ?></p>
				<h2 id="perfil-name"><?= $nombreCompleto ?></h2>
				<p><?= $usuario['nombre_rol'] ?></p>
			</div>
		</section>

		<section class="details-section" aria-labelledby="details-title">
			<div class="section-heading">
				<p class="eyebrow">Información registrada</p>
				<h2 id="details-title">Datos de la cuenta</h2>
			</div>
			<div class="details-grid">
				<article class="detail-item">
					<span>Nombre completo</span>
					<strong><?= $nombreCompleto ?></strong>
				</article>
                <?php if ($idUsuario === ((int) $_SESSION['idUsuario'] ?? 0)) {?>
				<article class="detail-item">
					<span>Correo electrónico</span>
					<strong><?= $usuario['email'] ?></strong>
				</article>
                <?php } ?>
				<article class="detail-item">
					<span>Rol</span>
					<strong><?= $usuario['nombre_rol'] ?></strong>
				</article>
				<article class="detail-item">
					<span>Ubicación de trabajo</span>
					<strong><?= $ubicacion ?></strong>
				</article>
                <article class="detail-item">
					<span>Fecha de registro</span>
					<strong><?= $usuario['fecha_creacion'] ?></strong>
				</article>
                <?php if ($usuario['nombre_rol'] == "Usuario") {?>
                <article class="detail-item">
					<span>Cantidad de items en posesion</span>
					<strong><?= $usuario['Cantidad_Activos'] ?></strong>
				</article>
                <?php } ?>
			</div>
		</section>
	</main>

	<footer class="footer">
		<p>© 2026 Kinetix. Todos los derechos reservados.</p>
	</footer>
</body>
</html>
