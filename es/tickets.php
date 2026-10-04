<?php 
session_start();

require_once "../php/conexionBDD.php";

// Leer el rol y el ID del usuario que inicio sesion.
$rol = isset($_SESSION['rol']) ? (int) $_SESSION['rol'] : null;
$idUsuario = (int) ($_SESSION['idUsuario'] ?? 0);
$visitante = "";

// Permitir el acceso solo a usuarios y tecnicos.
if ($rol === null || $rol === 3) {
    $_SESSION['mensaje'] = "No estas autorizado a ver esta pagina";
    $_SESSION['tipoError'] = "error";
    header("Location: inicio.php");
    exit;
} else if ($rol === 1) {
    $visitante = "Usuario";
}  else if ($rol === 2) {
    $visitante = "Tecnico";
}

// Aplicar el filtro de categoria si se recibio desde el formulario.
$filtroTipo = ($_GET['filtro'] ?? '');
$filtroCategoria = '';
if (!empty($filtroTipo)) {
    if ($filtroTipo == "categoria") {
        $id_categoria = (int) ($_GET['id_categoria'] ?? 0);
        if ($id_categoria > 0) {
            $filtroCategoria = " AND ct.id_categoria = $id_categoria";
        }
    }
    // Aca van mas filtros en un futuro.
}

// Los tecnicos ven sus tickets y los que aun no tienen tecnico asignado.
// Los usuarios solo ven los tickets que ellos crearon.
$filtroTickets = $rol === 2
    ? "(t.id_tecnico = $idUsuario OR t.id_tecnico IS NULL)"
    : "t.id_solicitante = $idUsuario";

$conexion = conectarBD();
// Consultar los tickets que corresponden al rol y al filtro actual.
$ticketslist = "SELECT t.id_ticket, t.titulo, e.estado, u.nombre, u.apellido, pt.prioridad, ct.categoria
                FROM ticket           AS t 
                JOIN estado_ticket    AS e   ON t.id_estado = e.id_estado
                JOIN usuario          AS u   ON t.id_solicitante = u.id_usuario
                JOIN prioridad_ticket AS pt  ON t.id_prioridad = pt.id_prioridad
                JOIN categoria_ticket AS ct  ON t.id_categoria = ct.id_categoria
                WHERE $filtroTickets$filtroCategoria
                ORDER BY t.fecha_creacion, pt.prioridad DESC;";

$ticketlistresult = mysqli_query($conexion, $ticketslist);
$mensajesResultado = null;
$ticketTitulo = null;
$ticketEstado = null;
$id_ticket = (int) ($_GET['id'] ?? $_POST['id_ticket'] ?? 0);
$historialTicket = null;

// Cargar el ticket seleccionado y revisar que el usuario pueda verlo.
if ($id_ticket > 0) {
    $ticket = "SELECT id_tecnico, id_solicitante, titulo, id_estado
               FROM ticket
               WHERE id_ticket = $id_ticket
               LIMIT 1";
    $ticketResultado = mysqli_query($conexion, $ticket);
    $ticketData = mysqli_fetch_assoc($ticketResultado);
    $ticketPermitido = $ticketData && (($rol === 2 && (empty($ticketData['id_tecnico']) || (int) $ticketData['id_tecnico'] === $idUsuario)) || ($rol === 1 && (int) $ticketData['id_solicitante'] === $idUsuario));

    if ($ticketPermitido) {
        $ticketTitulo = $ticketData['titulo'];
        $ticketEstado = (int) $ticketData['id_estado'];

        // Asignar al tecnico actual si el ticket todavia no tiene uno.
        if (empty($ticketData['id_tecnico']) && $rol === 2 && (int) $ticketEstado !== 2) {
            $sql = "UPDATE ticket
                    SET id_tecnico = $idUsuario, id_estado = 3
                    WHERE id_ticket = $id_ticket";
            mysqli_query($conexion, $sql);
            $ticketEstado = 3;

            $accion = 'El tecnico (ID:' . $idUsuario . ') fue asignado a este ticket';
            $sql = "INSERT INTO historial_ticket (id_ticket, estado_anterior, estado_nuevo, accion)
                    VALUES ($id_ticket, 1, 3, '$accion')";
            mysqli_query($conexion, $sql);
        }

        // Cargar el historial y procesar la accion enviada por un formulario.
        $historialTicket = "SELECT fecha, accion FROM historial_ticket WHERE id_ticket = $id_ticket ORDER BY fecha ASC";
        $historialTicket = mysqli_query($conexion, $historialTicket);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Guardar un mensaje nuevo y dejar registro en el historial.
            if (($_POST['accion'] ?? '') === 'mandarMensaje' && !(in_array($ticketEstado, [2, 4, 5]))) {
                $contenidoMensaje = trim($_POST['contenido'] ?? '');

                if ($contenidoMensaje !== '') {
                    $contenidoMensaje = mysqli_real_escape_string($conexion, $contenidoMensaje);
                    $sql = "INSERT INTO mensaje_ticket (id_ticket, id_usuario, contenido)
                            VALUES ($id_ticket, $idUsuario, '$contenidoMensaje')";
                    mysqli_query($conexion, $sql);

                    $accion = "El $visitante mando un mensaje. Mensaje: $contenidoMensaje";
                    $sql = "INSERT INTO historial_ticket (id_ticket, estado_anterior, estado_nuevo, accion)
                            VALUES ($id_ticket, $ticketEstado, $ticketEstado, '$accion')";
                    mysqli_query($conexion, $sql);
                }

                header("Location: tickets.php?id=$id_ticket");
                exit;
            }

            // Eliminar un mensaje escrito por el usuario actual.
            if (($_POST['accion'] ?? '') === 'eliminarMensaje') {
                $idMensaje = (int) ($_POST['id_mensaje'] ?? 0);

                if ($idMensaje > 0) {
                    // obtener el mensaje que el usuario quiere eliminar
                    $sql = "SELECT contenido FROM mensaje_ticket where id_mensaje = $idMensaje LIMIT 1";
                    $resultado = mysqli_query($conexion, $sql);
                    $mensaje = mysqli_fetch_assoc($resultado);
                    $contenido = $mensaje['contenido'] ?? '';

                    $sql = "DELETE FROM mensaje_ticket
                            WHERE id_mensaje = $idMensaje
                            AND id_ticket = $id_ticket
                            AND id_usuario = $idUsuario";
                    mysqli_query($conexion, $sql);


                    $accion = "El $visitante elimino un mensaje. Mensaje: $contenido";
                    $sql = "INSERT INTO historial_ticket (id_ticket, estado_anterior, estado_nuevo, accion) 
                            VALUES ($id_ticket, $ticketEstado, $ticketEstado, '$accion')";
                    mysqli_query($conexion, $sql);
                }

                header("Location: tickets.php?id=$id_ticket");
                exit;
            }

            // Cambiar la categoria del ticket.
            if (($_POST['accion'] ?? '') === 'cambiarCategoria') {
                $id_categoria = $_POST['categoria'];

                $sql = "UPDATE ticket
                        SET id_categoria = $id_categoria
                        WHERE id_ticket = $id_ticket";
                mysqli_query($conexion, $sql);

                $sql = "INSERT INTO historial_ticket (id_ticket, estado_anterior, estado_nuevo, accion)
                        VALUES ($id_ticket, $ticketEstado, $ticketEstado, 'El tecnico cambio la categoria de este ticket.')";
                mysqli_query($conexion, $sql);

                header("Location: tickets.php?id=$id_ticket");
                exit;
            }

            // Marcar el ticket como cancelado y guardar el cambio.
            if (($_POST['accion'] ?? '') === 'cancelarTicket') {
                $id_ticket = $_POST['id_ticket'] ?? 0;
                $sql = "UPDATE ticket
                        SET id_estado = 2
                        WHERE id_ticket = $id_ticket";
                mysqli_query($conexion, $sql);
                
                $uEstado = "SELECT estado_nuevo 
                            FROM historial_ticket 
                            WHERE id_ticket = $id_ticket
                            ORDER BY id_historial DESC
                            LIMIT 1";
                $sqlS = mysqli_query($conexion, $uEstado);
                $sqlS = mysqli_fetch_array($sqlS);
                $ultimoEstado = $sqlS['estado_nuevo'];
                    
                $sql = "INSERT INTO historial_ticket (id_ticket, estado_anterior, estado_nuevo, accion)
                        VALUES ($id_ticket, $ultimoEstado, 2, 'Ticket cancelado por el usuario')";
                mysqli_query($conexion, $sql);

                header("Location: tickets.php?id=$id_ticket");
                exit;
            }

            // Guardar la solucion y cerrar el ticket para el tecnico.
            if (($_POST['accion'] ?? '') === 'cerrarTicket' && $rol === 2) {
                $solucion = trim($_POST['solucion'] ?? '');

                if ($solucion !== '' && (int) $ticketEstado !== 2) {
                    $solucion = mysqli_real_escape_string($conexion, $solucion);
                    $resultado = mysqli_query($conexion, "SELECT id_resultado
                                                         FROM resultado_ticket
                                                         WHERE id_ticket = $id_ticket
                                                         LIMIT 1");
                    $resultadoData = mysqli_fetch_assoc($resultado);

                    if ($resultadoData) {
                        $sql = "UPDATE resultado_ticket
                                SET solucion = '$solucion', id_tecnico = $idUsuario,
                                    fecha_resolucion = CURDATE()
                                WHERE id_ticket = $id_ticket";
                    } else {
                        $sql = "INSERT INTO resultado_ticket
                                    (solucion, id_ticket, id_tecnico)
                                VALUES ('$solucion', $id_ticket, $idUsuario)";
                    }
                    mysqli_query($conexion, $sql);

                    $estadoAnterior = (int) $ticketEstado;
                    $sql = "UPDATE ticket
                            SET id_estado = 5
                            WHERE id_ticket = $id_ticket";
                    mysqli_query($conexion, $sql);

                    $accion = "Ticket cerrado por el tecnico con solucion: " . $solucion . ".";
                    $sql = "INSERT INTO historial_ticket (id_ticket, estado_anterior, estado_nuevo, accion)
                            VALUES ($id_ticket, $estadoAnterior, 4, '$accion')";
                    mysqli_query($conexion, $sql);
                    $ticketEstado = 4;
                    
                    $sql = "INSERT INTO mensaje_ticket (id_ticket, id_usuario, contenido) VALUES ('$id_ticket', '$idUsuario', 'Solucion: $solucion')";
                    $registro = mysqli_query($conexion, $sql);
                }

                header("Location: tickets.php?id=$id_ticket");
                exit;
            }

            // Cambiar el estado del ticket y agregarlo al historial.
            if (($_POST['accion'] ?? '') === 'cambiarEstado') {
                $id_estado= $_POST['estado'];
                $ticketEstado = $id_estado;

                if($id_estado !== 1 && $id_estado !== 2) {
                    $sql = "UPDATE ticket
                            SET id_estado = $id_estado
                            WHERE id_ticket = $id_ticket";
                    mysqli_query($conexion, $sql);

                    $uEstado = "SELECT estado_nuevo 
                                FROM historial_ticket 
                                WHERE id_ticket = $id_ticket
                                ORDER BY id_historial DESC
                                LIMIT 1";
                    $sqlS = mysqli_query($conexion, $uEstado);
                    $sqlS = mysqli_fetch_array($sqlS);
                    $ultimoEstado = $sqlS['estado_nuevo'];
                    
                    $sql = "INSERT INTO historial_ticket (id_ticket, estado_anterior, estado_nuevo, accion)
                            VALUES ($id_ticket, $ultimoEstado, $id_estado, 'Tecnico cambio el estado del ticket')";
                    mysqli_query($conexion, $sql);
                }

                header("Location: tickets.php?id=$id_ticket");
                exit;
            }

            // Borrar el ticket y volver a la lista.
            if (($_POST['accion'] ?? '') === 'borrarTicket') {
                $sql = "DELETE FROM ticket 
                        WHERE id_ticket = $id_ticket";
                mysqli_query($conexion, $sql);

                header("Location: tickets.php");
                exit;
            }

            // Cambiar la prioridad y guardar el cambio en el historial.
            if (($_POST['accion'] ?? '') === 'cambiarPrioridad') {
                $id_prioridad = $_POST['prioridad']; 

                $sql = "UPDATE ticket
                        SET id_prioridad = $id_prioridad
                        WHERE id_ticket = $id_ticket";
                mysqli_query($conexion, $sql);

                $sql = "INSERT INTO historial_ticket (id_ticket, estado_anterior, estado_nuevo, accion)
                        VALUES ($id_ticket, $ticketEstado, $ticketEstado, 'El tecnico cambio la prioridad de este ticket.')";
                mysqli_query($conexion, $sql);

                header("Location: tickets.php?id=$id_ticket");
                exit;
            }
        }

        // Cargar los mensajes del ticket en orden de llegada.
        $mensajes = "SELECT mt.id_mensaje, mt.id_usuario, mt.contenido, mt.fecha_creacion,
                            u.nombre, u.apellido
                    FROM mensaje_ticket AS mt
                    JOIN usuario AS u ON mt.id_usuario = u.id_usuario
                    WHERE mt.id_ticket = $id_ticket
                    ORDER BY mt.fecha_creacion ASC;";
        $mensajesResultado = mysqli_query($conexion, $mensajes);
    } else {
        $id_ticket = 0;
    }
}

// Cargar las opciones que se muestran en los filtros y controles.
$prioridades = "SELECT id_prioridad, prioridad FROM prioridad_ticket";
$prioridadesResultado = mysqli_query($conexion, $prioridades);
$categorias = "SELECT id_categoria, categoria FROM categoria_ticket";
$categoriasFiltroResultado = mysqli_query($conexion, $categorias);
$categoriasResultado = mysqli_query($conexion, $categorias);
$estados = "SELECT id_estado, estado FROM estado_ticket";
$estadosResultado = mysqli_query($conexion, $estados);

?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tickets</title>
<link rel="stylesheet" href="../css/sistema-de-tickets-tecnico.css">
<link rel="stylesheet" href="../css/kinetix-theme.css">
</head>

<body>
    <!-- Menu principal con enlaces a las secciones del sitio. -->
    <nav class="menu">
        <div class="menu-content">
            <div class="menu-logo">
                <img src="../imagenes/Logo1.png" alt="Logo Kinetix">
            </div>

            <ul>
                <li><a href="inicio.php">Inicio</a></li>
                <li><a href="FAQ-pagina-cliente.html">FAQ</a></li>
                <li><a href="tickets.php" class="active" aria-current="page">Tickets</a></li>
                <li><a href="inventario.php">Inventario</a></li>
                <li><a href="usuario.php?id=<?= $idUsuario ?>">Mi Cuenta</a></li>
                <li> <button type="button" id="themeToggle" class="theme-toggle" aria-label="Cambiar tema">🌙</button></li>
            </ul>
        </div>
    </nav>
    <div class="page">
        <div class="container">
            <!-- Lista de tickets, filtro por categoria y boton para cancelar. -->
            <div class="sidebar">
                <h2>Tickets</h2>
                <div class="ticket-list">
                    <div class="ticket-filter">
                        <form method="GET">
                            <input type="hidden" name="filtro" value="categoria">
                            <select name="id_categoria" id="id_categoria" onchange="this.form.submit()">
                                <option value="0">Categoria</option>
                                <?php while($reg = mysqli_fetch_array($categoriasFiltroResultado)) { ?>
                                <option value="<?= $reg['id_categoria'] ?>"> <?=  $reg['categoria'] ?></option>
                                <?php } ?>
                                <option value="0"> Todos </option>
                            </select>
                        </form>
                    </div>
                    <!-- Mostrar cada ticket encontrado. -->
                    <?php while($reg = mysqli_fetch_assoc($ticketlistresult)) { ?>
                        <a class="ticket" href="tickets.php?id=<?= $reg['id_ticket']?>">
                            <div class="ticket-top">
                                <span class="ticket-id">#<?= str_pad($reg['id_ticket'], 5, '0', STR_PAD_LEFT) ?></span>
                                <div class="ticket-status <?= strtolower(str_replace(' ', '-', $reg['estado'])) ?>">
                                    <span class="status-dot"></span>
                                    <?= $reg['estado'] ?>
                                </div>
                            </div>
                            <div class="ticket-category-row">
                                <h6 style="font-size:13px;">Categoria: <?= $reg['categoria']?> </h6>
                                <?php if ($rol !== 2 && !($reg['estado'] === "Cancelado" || $reg['estado'] === "Resuelto" || $reg['estado'] === "Cerrado")) {?>
                                <form method="POST">
                                    <input type="hidden" name="accion" value="cancelarTicket">
                                    <input type="hidden" name="id_ticket" value="<?= $reg['id_ticket'] ?>">
                                    <button class="btn-eliminar">Cancelar</button>
                                </form>
                                <?php }?>
                            </div>
                            <div class="ticket-title">
                                <h6 style="font-size:16px; font-weight: lighter;"><?= htmlspecialchars($reg['titulo'], ENT_QUOTES, 'UTF-8') ?></h6>
                            </div>
                        </a>
                    <?php } ?>
                </div>
            </div>

            <!-- Panel principal con el chat del ticket seleccionado. -->

            <div class="main">
                <div class="header">
                    <h1 style="text-align: center; padding: 15px;"><?= htmlspecialchars($ticketTitulo ?? 'Seleccioná un ticket', ENT_QUOTES, 'UTF-8') ?></h1>
                </div>
                <!-- Mostrar los mensajes y permitir borrar los propios. -->
                <div class="chat">
                    <?php while($mensajesResultado && $reg = mysqli_fetch_assoc($mensajesResultado)) { ?>
                        <div class="message <?= $idUsuario === (int) $reg['id_usuario'] ? 'sent' : 'received' ?>">
                            <strong><?= $reg['nombre'] . ' ' . $reg['apellido'] ?></strong>
                            <div><?= $reg['contenido'] ?></div>
                            <?php if ($idUsuario === (int) $reg['id_usuario'] && !in_array($ticketEstado, [2, 4, 5])) { ?>
                                <form method="POST">
                                    <input type="hidden" name="accion" value="eliminarMensaje">
                                    <input type="hidden" name="id_ticket" value="<?= $id_ticket ?>">
                                    <input type="hidden" name="id_mensaje" value="<?= $reg['id_mensaje'] ?>">
                                    <button type="submit">Eliminar</button>
                                </form>
                            <?php } ?>
                        </div>
                    <?php }?>
                </div>
                <!-- CONTROL DEL TICKET PARA EL TECNICO -->
                <?php if ($rol === 2) {?>
                    <div class="resolution-panel" <?= ($id_ticket <= 0 || in_array($ticketEstado, [2, 4, 5])) ? 'style="visibility: hidden;"' : ''; ?>>
                        <h2>Resultado del ticket</h2>
                        <form method="POST">
                            <input type="hidden" name="accion" value="cerrarTicket">
                            <input type="hidden" name="id_ticket" value="<?= $id_ticket ?>">
                            <textarea name="solucion" rows="2"  <?= ($id_ticket <= 0 || in_array($ticketEstado, [2, 4, 5])) ? 'placeholder="El ticket esta cerrado." disabled' : 'placeholder="Escribí cuál fue la solución aplicada..."' ?>></textarea>
                            <button type="submit" class="btn-editar" <?= ($id_ticket <= 0 || in_array($ticketEstado, [2, 4, 5])) ? 'disabled' : '' ?>>Cerrar ticket y guardar resultado</button>
                        </form>
                    </div>
                    <div class="tech-controls" <?= $id_ticket > 0 ? '' : 'style="visibility: hidden;"'; ?>>
                        <div>
                            <form method="POST">
                                <input type="hidden" name="accion" value="cambiarCategoria">
                                <select name="categoria" id="categoria" onchange="this.form.submit()">
                                    <option value="">Cambiar categoria</option>
                                    <?php while($reg = mysqli_fetch_array($categoriasResultado)) { ?>
                                    <option value="<?= $reg['id_categoria'] ?>"> <?=  $reg['categoria'] ?></option>
                                    <?php } ?>
                                </select>
                            </form>
                        </div>
                        <div>
                            <form method="POST">
                                <input type="hidden" name="accion" value="cambiarPrioridad">
                                <select name="prioridad" id="prioridad" onchange="this.form.submit()">
                                    <option value="">Cambiar prioridad</option>
                                    <?php while($reg = mysqli_fetch_array($prioridadesResultado)) { ?>
                                    <option value="<?= $reg['id_prioridad'] ?>"> <?=  $reg['prioridad'] ?></option>
                                    <?php } ?>
                                </select>
                            </form>
                        </div>
                        <div>
                            <form method="POST">
                                <input type="hidden" name="accion" value="cambiarEstado">
                                <select name="estado" id="estado" onchange="this.form.submit()">
                                    <option value="">Cambiar estado</option>
                                    <?php while($reg = mysqli_fetch_array($estadosResultado)) { ?>
                                    <option value="<?= $reg['id_estado'] ?>"> <?=  $reg['estado'] ?></option>
                                    <?php } ?>
                                </select>
                            </form>
                        </div>
                        <div>
                            <form method="POST">
                                <input type="hidden" name="accion" value="borrarTicket">
                                <button type="submit" class="btn-editar" style="background-color:red; color:white"> Borrar Ticket </button>
                            </form>
                        </div>
                    </div>
                <?php }?>
                <!-- Formulario para enviar mensajes mientras el ticket esta abierto. -->
                <div class="chat-input">
                    <form method="POST">
                        <input type="hidden" name="accion" value="mandarMensaje">
                        <input type="hidden" name="id_ticket" value="<?= $id_ticket ?>">
                        <textarea name="contenido" rows="2" cols="10" <?= ($id_ticket <= 0 || (in_array($ticketEstado, [2, 4, 5])))  ? 'placeholder="El ticket esta cerrado." disabled' : 'placeholder="Escriba un mensaje..."' ?> onkeydown="if (event.key === 'Enter') { this.form.submit(); }"></textarea>
                        <button type="submit" class="send-button" <?= ($id_ticket <= 0 || (in_array($ticketEstado, [2, 4, 5]))) ? 'disabled' : '' ?>> Enviar
                </div>
            </div>

            <!-- Historial de cambios y acciones del ticket, visible solo para tecnicos. -->
            <?php if ($rol === 2) { ?>
                <aside class="ticket-history" aria-label="Historial del ticket">
                    <div class="ticket-history-header">
                        <h2>Historial del ticket</h2>
                        <span><?= $id_ticket > 0 ? '#' . str_pad($id_ticket, 5, '0', STR_PAD_LEFT) : 'Sin seleccionar' ?></span>
                    </div>

                    <div class="ticket-history-list">
                        <?php if (empty($historialTicket)) { ?>
                            <p class="ticket-history-empty">Aca se mostrara el historial del ticket seleccionado.</p>
                        <?php } else { ?>
                            <?php while ($reg = mysqli_fetch_assoc($historialTicket)) { ?>
                                <article class="ticket-history-item">
                                    <strong><?= $reg['accion'] ?? '' ?></strong>
                                    <span><?= $reg['fecha'] ?? '' ?></span>
                                </article>
                            <?php } ?>
                        <?php } ?>
                    </div>
                </aside>
            <?php } ?>
        </div>
    </div>
    <!-- Aplicar el tema guardado y permitir cambiarlo desde el menu. -->
  <script>
    const themeToggle = document.getElementById('themeToggle');

    function applyTheme(theme) {
      const body = document.body;
      const isDark = theme === 'dark';
      body.classList.toggle('dark-mode', isDark);
      body.classList.toggle('light-mode', !isDark);
      if (themeToggle) {
        themeToggle.textContent = isDark ? '☀️' : '🌙';
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
  </script></body>
</html>