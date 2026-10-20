<?php
session_start();

require_once "../php/conexionBDD.php";

$idUsuario = (int) $_SESSION['idUsuario'] ?? 0;
if ($idUsuario <= 0) {
	$_SESSION['mensaje'] = "Necesitas logearte para configurar tu cuenta.";
	$_SESSION['tipoError'] = "error";
	header("Location: inicio");
	exit;
}
$conexion = conectarBD();
$sql = "SELECT nombre, apellido, password, email FROM usuario WHERE id_usuario = $idUsuario LIMIT 1"; $sql = mysqli_query($conexion, $sql); $datos = mysqli_fetch_assoc($sql);

$tab = "";
$infoCuenta = "SELECT CONCAT()";
if($_SERVER['REQUEST_METHOD'] === "POST") {
	$tab = ($_POST['tab'] ?? '');
	if(($_POST['accion'] ?? '') === "cambiarDatos") {}
	if (($_POST['accion'] ?? '') === "desactivarCuenta") { 
		$sql1 = "UPDATE usuario SET activo = 0 WHERE id_usuario = $idUsuario";
		$sql1 = mysqli_query($conexion, $sql1);

		$sql2 = "UPDATE activo SET id_usuario = 0 WHERE id_usuario = $idUsuario";
		$sql2 = mysqli_query($conexion, $sql2);
		
		if ($sql1 && $sql2) {
			header("Location: ../php/logout");
			exit;
		} else {
			$_SESSION['mensaje'] = "Error al desactivar la cuenta. Si sigues teniendo problemas, contacta a un administrador";
			$_SESSION['tipoError'] = "error";
			header("Location: inicio");
			exit;
		}
	}
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
	<base href="/Kinetix/es/">
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Configuración | Kinetix</title>
	<link rel="icon" type="image/png" href="../imagenes/Icon.png">
	<link rel="stylesheet" href="../css/kinetix-theme.css">
	<link rel="stylesheet" href="../css/usuario-config.css">
</head>
<body>
	<nav class="menu">
		<div class="menu-content">
			<a class="menu-logo" href="inicio" aria-label="Volver al inicio">
				<img src="../imagenes/Logo1.png" alt="Logo Kinetix">
			</a>
			<ul>
				<li><a href="inicio">Inicio</a></li>
				<li><a href="tickets">Tickets</a></li>
				<li><a href="preguntas-frecuentes">Ayuda</a></li>
				<li><a href="inventario">Inventario</a></li>
				<li><a class="active" href="usuario-config">Configuración</a></li>
				<li><a class="logout-button" href="../php/logout"><span class="logout-sign" aria-hidden="true"></span><span class="logout-text">Cerrar sesión</span></a></li>
			</ul>
		</div>
	</nav>

	<main class="settings-page">
		<div class="settings-container">
			<aside class="settings-sidebar">
				<input type="checkbox" id="modal-desactivar" class="modal-toggle" hidden>

				<div class="sidebar-profile">
					<div class="avatar">N</div>
					<div class="profile-info">
						<h3>Nombre completo</h3>
						<p>Email</p>
					</div>
				</div>
				<nav class="settings-nav">
					<form class="settings-nav" method="POST">
						<input type="hidden" name="accion" value="verTab"> 
						<input type="hidden" name="tab" value="Cuenta"> 
						<button class="nav-item <?= $tabActivo = $tab === "Cuenta" ? "active" : '' ?>" aria-current="page">
							<span class="nav-icon">&#128100;</span>
							<span class="nav-label">Cuenta</span>
						</button>
					</form>
					<form class="settings-nav" method="POST">
						<input type="hidden" name="accion" value="verTab"> 
						<input type="hidden" name="tab" value="Contraseña"> 
						<button class="nav-item <?= $tabActivo = $tab === "Contraseña" ? "active" : '' ?>" aria-current="page">
							<span class="nav-icon">🔒</span>
							<span class="nav-label">Contraseña</span>
						</button>
					</form>
					<form class="settings-nav" method="POST">
						<input type="hidden" name="accion" value="verTab"> 
						<input type="hidden" name="tab" value="Placeholder1"> 
						<button class="nav-item <?= $tabActivo = $tab === "Placeholder1" ? "active" : '' ?>" aria-current="page">
							<span class="nav-icon">🔒</span>
							<span class="nav-label">Placeholder1</span>
						</button>
					</form>
					<form class="settings-nav" method="POST">
						<input type="hidden" name="accion" value="verTab"> 
						<input type="hidden" name="tab" value="Placeholder2"> 
						<button class="nav-item <?= $tabActivo = $tab === "Placeholder2" ? "active" : '' ?>" aria-current="page">
							<span class="nav-icon">🔒</span>
							<span class="nav-label">Placeholder2</span>
						</button>
					</form>
					<label for="modal-desactivar" class="nav-item nav-danger">
						<span class="nav-icon">&#9888;</span>
						<span class="nav-label">Desactivar cuenta</span>
					</label>
				</nav>

				<div class="modal-overlay">
					<div class="modal">
					<div class="modal-body">
						<div class="modal-icon">
							<!-- Icono de basura -->
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width:48px;height:48px;color:var(--danger);">
								<path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"></path>
							</svg>
						</div>
						<h3 class="modal-title">¿Estás seguro?</h3>
						<p>Al desactivar tu cuenta:</p>
						<ul class="modal-lista">
							<li>No podrás iniciar sesión ni acceder a tus tickets.</li>
							<li>Tus activos quedaran sin dueño.</li>
							<li>Tu información personal quedará inactiva.</li>
							<li>Esta acción no se puede deshacer.</li>
						</ul>
					</div>
						<div class="modal-footer">
							<label for="modal-desactivar" class="btn-cancel">Cancelar</label>
							<form method="POST">
								<input type="hidden" name="accion" value="desactivarCuenta">
								<button type="submit" class="btn-accept">Aceptar</button>
							</form>
						</div>
					</div>
				</div>
			</aside>

			<?php if (($_POST['accion'] ?? '') === "verTab") {?>
				<?php if (($_POST['tab'] ?? '') === "Cuenta")  {?>
			<form method="POST">
				<input type="hidden" name="accion" value="cambiarDatos">
				<section class="settings-content">
					<header class="content-header">
						<h1>Cuenta</h1>
						<p>Gestioná tu información personal y de acceso.</p>
					</header>

					<div class="settings-group">
						<div class="setting-row">
							<div class="setting-info">
								<h3>Nombre</h3>
								<p>Este nombre se muestra en tu perfil y en los tickets.</p>
							</div>
							<div class="setting-control">
								<input type="text" placeholder="Nombre" value="Nombre">
							</div>
						</div>

						<div class="setting-row">
							<div class="setting-info">
								<h3>Apellido</h3>
								<p>Este apellido se muestra en tu perfil y en los tickets.</p>
							</div>
							<div class="setting-control">
								<input type="text" placeholder="apellido" value="Apellido">
							</div>
						</div>

						<div class="setting-row">
							<div class="setting-info">
								<h3>Correo electrónico</h3>
								<p>Usado para iniciar sesión y recibir notificaciones.</p>
							</div>
							<div class="setting-control">
								<input type="email" placeholder="correo@ejemplo.com" value="email">
							</div>
						</div>

						<div class="setting-row">
							<div class="setting-info">
								<h3>Teléfono</h3>
								<p>Número de contacto opcional.</p>
							</div>
							<div class="setting-control">
								<input type="tel" placeholder="+54 9 11 1234-5678" value="telefono">
							</div>
						</div>

						<div class="setting-row">
							<div class="setting-info">
								<h3>Rol de cuenta</h3>
								<p>Usuario estándar.</p>
							</div>
							<div class="setting-control">
								<select>
									<option>Usuario</option>
								</select>
							</div>
						</div>
					</div>

					<button class="btn-save" type="submit">Guardar cambios</button>
				</form>
			</section>
			<?php } ?> 
			<?php if (($_POST['tab'] ?? '') === "Contraseña")  {?>
			<form method="POST">
				<section class="settings-content">
					<header class="content-header">
						<h1>Cuenta</h1>
						<p>Gestioná tu información personal y de acceso.</p>
					</header>

					<div class="settings-group">
						<div class="setting-row">
							<div class="setting-info">
								<h3>Contraseña</h3>
							</div>
							<div class="setting-control">
								<input type="text" placeholder="Nombre" value="Nombre">
							</div>
						</div>
						<div class="setting-row">
							<div class="setting-info">
								<h3>Confirmar contraseña</h3>
							</div>
							<div class="setting-control">
								<input type="text" placeholder="apellido" value="Apellido">
							</div>
						</div>
					</div>
					<button class="btn-save" type="submit">Guardar cambios</button>
				</form>
			</section>
			<?php } ?> 
			<?php }?>
		</div>
	</main>

	<footer class="footer">
		<p>© 2026 Kinetix. Todos los derechos reservados.</p>
	</footer>
</body>
</html>