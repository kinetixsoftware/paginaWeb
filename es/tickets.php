<?php 
session_start();

require_once "../php/conexionBDD.php";
require_once "../php/image-upload.php";

// Leer el rol y el ID del usuario
$rol = isset($_SESSION['rol']) ? (int) $_SESSION['rol'] : null;
$idUsuario = (int) ($_SESSION['idUsuario'] ?? 0);
$visitante = "";

// Permitir el acceso a usuarios, tecnicos y administradores
if ($rol === null || !in_array($rol, [1, 2, 3], true)) {
    $_SESSION['mensaje'] = "No estas autorizado a ver esta pagina";
    $_SESSION['tipoError'] = "error";
    header("Location: /Kinetix/es/inicio");
    exit;
} else if ($rol === 1) {
    $visitante = "Usuario";
}  else if ($rol === 2) {
    $visitante = "Tecnico";
} else if ($rol === 3) {
    $visitante = "Administrador";
}

$conexion = conectarBD();
$escape = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$estadosFiltroResultado = mysqli_query(
    $conexion,
    "SELECT id_estado, estado FROM estado_ticket ORDER BY id_estado"
);
if (!$estadosFiltroResultado) {
    throw new RuntimeException('No se pudieron cargar los estados de los tickets: ' . mysqli_error($conexion));
}

// Aplicar el filtro de categoria si se recibio desde el formulario.
$id_categoria = filter_var($_GET['id_categoria'] ?? 0, FILTER_VALIDATE_INT);
$id_categoria = $id_categoria === false ? 0 : (int) $id_categoria;
$filtroCategoria = '';
if ($id_categoria > 0) {
    $filtroCategoria = " AND ct.id_categoria = $id_categoria";
}

// Los tecnicos ven sus tickets y los que aun no tienen tecnico asignado.
// Los usuarios solo ven los tickets que ellos crearon.
$filtroTickets = match ($rol) {
    2 => "(t.id_tecnico = $idUsuario OR t.id_tecnico IS NULL)",
    3 => '1 = 1',
    default => "t.id_solicitante = $idUsuario",
};
$estadoFiltro = filter_var($_GET['estado'] ?? 0, FILTER_VALIDATE_INT);
$estadoFiltro = $estadoFiltro === false ? 0 : (int) $estadoFiltro;
$estadoValido = false;
if ($rol === 3 && $estadoFiltro > 0) {
    $estadoValidacion = mysqli_query(
        $conexion,
        "SELECT id_estado FROM estado_ticket WHERE id_estado = $estadoFiltro LIMIT 1"
    );
    if (!$estadoValidacion) {
        throw new RuntimeException('No se pudo validar el estado seleccionado: ' . mysqli_error($conexion));
    }
    $estadoValido = mysqli_fetch_assoc($estadoValidacion) !== null;
}
$filtroEstado = $estadoValido ? " AND t.id_estado = $estadoFiltro" : '';
$ordenTickets = ($_GET['orden'] ?? '') === 'antiguos' ? 'antiguos' : 'recientes';
$ordenSQL = $ordenTickets === 'antiguos'
    ? 't.fecha_creacion ASC, t.id_ticket ASC'
    : 't.fecha_creacion DESC, t.id_ticket DESC';

// Consultar los tickets que corresponden al rol y al filtro actual.
$ticketslist = "SELECT t.id_ticket, t.titulo, t.fecha_creacion, e.estado, u.nombre, u.apellido,
                       tecnico.nombre AS tecnico_nombre, tecnico.apellido AS tecnico_apellido,
                       pt.prioridad, ct.categoria
                FROM ticket           AS t 
                JOIN estado_ticket    AS e   ON t.id_estado = e.id_estado
                JOIN usuario          AS u   ON t.id_solicitante = u.id_usuario
                LEFT JOIN usuario     AS tecnico ON t.id_tecnico = tecnico.id_usuario
                JOIN prioridad_ticket AS pt  ON t.id_prioridad = pt.id_prioridad
                JOIN categoria_ticket AS ct  ON t.id_categoria = ct.id_categoria
                WHERE $filtroTickets$filtroCategoria$filtroEstado
                ORDER BY $ordenSQL;";

$ticketlistresult = mysqli_query($conexion, $ticketslist);
$mensajesResultado = null;
$ticketTitulo = null;
$ticketEstado = null;
$ticketData = null;
$id_ticket = (int) ($_GET['id'] ?? $_POST['id_ticket'] ?? 0);
$historialTicket = null;

// Cargar el ticket seleccionado y revisar que el usuario pueda verlo.
if ($id_ticket > 0) {
    $ticket = "SELECT t.id_tecnico, t.id_solicitante, t.titulo, t.id_estado, t.id_categoria,
                      t.id_prioridad, tecnico.nombre AS tecnico_nombre, tecnico.apellido AS tecnico_apellido
               FROM ticket AS t
               LEFT JOIN usuario AS tecnico ON t.id_tecnico = tecnico.id_usuario
               WHERE id_ticket = $id_ticket
               LIMIT 1";
    $ticketResultado = mysqli_query($conexion, $ticket);
    $ticketData = mysqli_fetch_assoc($ticketResultado);
    $ticketPermitido = $ticketData && (
        $rol === 3
        || ($rol === 2 && (empty($ticketData['id_tecnico']) || (int) $ticketData['id_tecnico'] === $idUsuario))
        || ($rol === 1 && (int) $ticketData['id_solicitante'] === $idUsuario)
    );

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
            if (($_POST['accion'] ?? '') === 'mandarMensaje' && $rol !== 3 && !in_array($ticketEstado, [2, 4, 5], true)) {
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

                header("Location: /Kinetix/es/ticket/$id_ticket");
                exit;
            }

            // Eliminar un mensaje escrito por el usuario actual.
            if (($_POST['accion'] ?? '') === 'eliminarMensaje' && $rol !== 3) {
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

                header("Location: /Kinetix/es/ticket/$id_ticket");
                exit;
            }

            // Cambiar la categoria del ticket.
            if (($_POST['accion'] ?? '') === 'cambiarCategoria' && in_array($rol, [2, 3], true)) {
                $nuevaCategoria = filter_var($_POST['categoria'] ?? null, FILTER_VALIDATE_INT);
                $categoriaResultado = $nuevaCategoria
                    ? mysqli_query($conexion, "SELECT categoria FROM categoria_ticket WHERE id_categoria = $nuevaCategoria LIMIT 1")
                    : false;
                $categoriaData = $categoriaResultado ? mysqli_fetch_assoc($categoriaResultado) : null;

                if ($categoriaData && (int) $nuevaCategoria !== (int) $ticketData['id_categoria']) {
                    $actualizada = mysqli_query($conexion, "UPDATE ticket SET id_categoria = $nuevaCategoria WHERE id_ticket = $id_ticket");
                    if ($actualizada) {
                        $actor = $rol === 3 ? 'administrador' : 'tecnico';
                        $accion = mysqli_real_escape_string($conexion, "El $actor cambio la categoria del ticket a: " . $categoriaData['categoria']);
                        if (!mysqli_query($conexion, "INSERT INTO historial_ticket (id_ticket, estado_anterior, estado_nuevo, accion) VALUES ($id_ticket, $ticketEstado, $ticketEstado, '$accion')")) {
                            throw new RuntimeException('No se pudo guardar el cambio de categoría en el historial: ' . mysqli_error($conexion));
                        }
                    }
                }

                header("Location: /Kinetix/es/ticket/$id_ticket");
                exit;
            }

            // Marcar el ticket como cancelado y guardar el cambio.
            if (($_POST['accion'] ?? '') === 'cancelarTicket' && $rol === 1 && (int) $ticketData['id_solicitante'] === $idUsuario) {
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
                $sqlS = mysqli_fetch_assoc($sqlS);
                $ultimoEstado = $sqlS['estado_nuevo'];
                    
                $sql = "INSERT INTO historial_ticket (id_ticket, estado_anterior, estado_nuevo, accion)
                        VALUES ($id_ticket, $ultimoEstado, 2, 'Ticket cancelado por el usuario')";
                mysqli_query($conexion, $sql);

                header("Location: /Kinetix/es/ticket/$id_ticket");
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

                header("Location: /Kinetix/es/ticket/$id_ticket");
                exit;
            }

            // Cambiar el estado del ticket y agregarlo al historial.
            if (($_POST['accion'] ?? '') === 'cambiarEstado' && in_array($rol, [2, 3], true)) {
                $nuevoEstado = filter_var($_POST['estado'] ?? null, FILTER_VALIDATE_INT);
                $estadosPermitidos = $rol === 3 ? [1, 3] : [3, 4, 5];
                $estadoResultado = $nuevoEstado !== false
                    ? mysqli_query($conexion, "SELECT id_estado FROM estado_ticket WHERE id_estado = " . (int) $nuevoEstado . " LIMIT 1")
                    : false;
                $estadoValido = $estadoResultado
                    && mysqli_fetch_assoc($estadoResultado)
                    && in_array((int) $nuevoEstado, $estadosPermitidos, true);

                if ($estadoValido && (int) $nuevoEstado !== $ticketEstado) {
                    $actualizado = mysqli_query($conexion, "UPDATE ticket SET id_estado = " . (int) $nuevoEstado . " WHERE id_ticket = $id_ticket");
                    if ($actualizado) {
                        $actor = $rol === 3 ? 'administrador' : 'tecnico';
                        $accion = "El $actor cambio el estado del ticket.";
                        if (!mysqli_query($conexion, "INSERT INTO historial_ticket (id_ticket, estado_anterior, estado_nuevo, accion) VALUES ($id_ticket, $ticketEstado, " . (int) $nuevoEstado . ", '$accion')")) {
                            throw new RuntimeException('No se pudo guardar el cambio de estado en el historial: ' . mysqli_error($conexion));
                        }
                    }
                }

                header("Location: /Kinetix/es/ticket/$id_ticket");
                exit;
            }

            // Cambiar la prioridad y guardar el cambio en el historial.
            if (($_POST['accion'] ?? '') === 'cambiarPrioridad' && in_array($rol, [2, 3], true)) {
                $nuevaPrioridad = filter_var($_POST['prioridad'] ?? null, FILTER_VALIDATE_INT);
                $prioridadResultado = $nuevaPrioridad
                    ? mysqli_query($conexion, "SELECT prioridad FROM prioridad_ticket WHERE id_prioridad = $nuevaPrioridad LIMIT 1")
                    : false;
                $prioridadData = $prioridadResultado ? mysqli_fetch_assoc($prioridadResultado) : null;

                if ($prioridadData && (int) $nuevaPrioridad !== (int) $ticketData['id_prioridad']) {
                    $actualizada = mysqli_query($conexion, "UPDATE ticket SET id_prioridad = $nuevaPrioridad WHERE id_ticket = $id_ticket");
                    if ($actualizada) {
                        $actor = $rol === 3 ? 'administrador' : 'tecnico';
                        $accion = mysqli_real_escape_string($conexion, "El $actor cambio la prioridad del ticket a: " . $prioridadData['prioridad']);
                        if (!mysqli_query($conexion, "INSERT INTO historial_ticket (id_ticket, estado_anterior, estado_nuevo, accion) VALUES ($id_ticket, $ticketEstado, $ticketEstado, '$accion')")) {
                            throw new RuntimeException('No se pudo guardar el cambio de prioridad en el historial: ' . mysqli_error($conexion));
                        }
                    }
                }

                header("Location: /Kinetix/es/ticket/$id_ticket");
                exit;
            }

            if (($_POST['accion'] ?? '') === 'quitarTecnico' && $rol === 3 && !empty($ticketData['id_tecnico'])) {
                $idTecnicoAnterior = (int) $ticketData['id_tecnico'];
                $nuevoEstado = in_array($ticketEstado, [1, 3], true) ? 1 : $ticketEstado;
                $transaccionIniciada = false;
                try {
                    $transaccionIniciada = mysqli_begin_transaction($conexion);
                    if (!$transaccionIniciada) {
                        throw new RuntimeException('No se pudo iniciar la eliminación de la asignación.');
                    }

                    $actualizada = mysqli_query(
                        $conexion,
                        "UPDATE ticket SET id_tecnico = NULL, id_estado = $nuevoEstado
                         WHERE id_ticket = $id_ticket AND id_tecnico = $idTecnicoAnterior"
                    );
                    if (!$actualizada || mysqli_affected_rows($conexion) !== 1) {
                        throw new RuntimeException('No se pudo eliminar la asignación del técnico.');
                    }

                    $accion = mysqli_real_escape_string(
                        $conexion,
                        'El administrador elimino al tecnico de este ticket, brevemente otro tecnico procedera a ayudarte.'
                    );
                    if (!mysqli_query(
                        $conexion,
                        "INSERT INTO historial_ticket (id_ticket, estado_anterior, estado_nuevo, accion)
                         VALUES ($id_ticket, $ticketEstado, $nuevoEstado, '$accion')"
                    )) {
                        throw new RuntimeException('No se pudo registrar la eliminación de la asignación en el historial: ' . mysqli_error($conexion));
                    }
                    if (!mysqli_commit($conexion)) {
                        throw new RuntimeException('No se pudo confirmar la eliminación de la asignación.');
                    }
                    $transaccionIniciada = false;
                } catch (mysqli_sql_exception | RuntimeException $error) {
                    if ($transaccionIniciada) {
                        try {
                            mysqli_rollback($conexion);
                        } catch (mysqli_sql_exception $errorRollback) {
                            error_log('Ticket technician unassignment rollback failed: ' . $errorRollback->getMessage());
                        }
                    }
                    error_log('Ticket technician unassignment failed: ' . $error->getMessage());
                    $_SESSION['mensaje'] = 'No se pudo quitar el técnico asignado. Inténtalo nuevamente.';
                    $_SESSION['tipoError'] = 'error';
                    header("Location: /Kinetix/es/ticket/$id_ticket");
                    exit;
                }

                header("Location: /Kinetix/es/ticket/$id_ticket");
                exit;
            }
        }

        // Cargar los mensajes del ticket en orden de llegada.
        $mensajes = "SELECT mt.id_mensaje, mt.id_usuario, mt.contenido, mt.fecha_creacion,
                            u.nombre, u.apellido, u.foto_path
                    FROM mensaje_ticket AS mt
                    JOIN usuario AS u ON mt.id_usuario = u.id_usuario
                    WHERE mt.id_ticket = $id_ticket
                    ORDER BY mt.fecha_creacion ASC;";
        $mensajesResultado = mysqli_query($conexion, $mensajes);
    } else {
        $id_ticket = 0;
    }
}

// Cargar solicitudes propias para usuarios y solicitudes de activos de la ubicacion del tecnico.
$vista = ($_GET['vista'] ?? 'tickets') === 'solicitudes' ? 'solicitudes' : 'tickets';
$solicitudesResultado = null;
$solicitudData = null;
$historialActivo = null;
$id_solicitud = (int) ($_GET['id_solicitud'] ?? $_POST['id_solicitud'] ?? 0);

if ($vista === 'solicitudes') {
    $filtroSolicitudes = $rol === 2
        ? "tecnico.id_usuario = $idUsuario AND (tecnico.id_ubicacion = ss.id_ubicacion OR tecnico.id_ubicacion = solicitante.id_ubicacion OR tecnico.id_ubicacion = a.id_ubicacion)"
        : "ss.id_solicitante = $idUsuario";
    $solicitudes = "SELECT ss.id_solicitud, ss.descripcion, ss.fecha_creacion, ss.prioridad, ss.tipo_servicio,
                           es.estado, a.id_activo, a.nombre AS activo,
                           solicitante.nombre AS solicitante_nombre, solicitante.apellido AS solicitante_apellido,
                           solicitante.email AS solicitante_email, ss.id_ubicacion
                    FROM solicitud_servicio AS ss
                    JOIN estado_solicitud AS es ON ss.id_estado = es.id_estado
                    JOIN activo AS a ON ss.id_activo = a.id_activo
                    JOIN usuario AS solicitante ON solicitante.id_usuario = ss.id_solicitante
                    JOIN usuario AS tecnico ON tecnico.id_usuario = $idUsuario
                    WHERE $filtroSolicitudes
                    ORDER BY ss.fecha_creacion DESC";
    $solicitudesResultado = mysqli_query($conexion, $solicitudes);

    if ($id_solicitud > 0) {
        $solicitud = "SELECT ss.id_solicitud, ss.descripcion, ss.fecha_creacion, ss.prioridad, ss.tipo_servicio,
                             ss.id_estado, ss.id_activo, a.nombre AS activo, es.estado,
                             solicitante.nombre AS solicitante_nombre, solicitante.apellido AS solicitante_apellido,
                             solicitante.email AS solicitante_email, ss.id_ubicacion,
                             uubicacion.calle AS ubicacion_calle, ciu.ciudad AS ubicacion_ciudad
                      FROM solicitud_servicio AS ss
                      JOIN estado_solicitud AS es ON ss.id_estado = es.id_estado
                      JOIN activo AS a ON ss.id_activo = a.id_activo
                      JOIN usuario AS solicitante ON solicitante.id_usuario = ss.id_solicitante
                      JOIN usuario AS tecnico ON tecnico.id_usuario = $idUsuario
                      LEFT JOIN ubicacion AS uubicacion ON uubicacion.id_ubicacion = ss.id_ubicacion
                      LEFT JOIN ciudad AS ciu ON ciu.id_ciudad = uubicacion.id_ciudad
                      WHERE ss.id_solicitud = $id_solicitud AND $filtroSolicitudes
                      LIMIT 1";
        $solicitudResultado = mysqli_query($conexion, $solicitud);
        $solicitudData = mysqli_fetch_assoc($solicitudResultado);

        if ($solicitudData && $_SERVER['REQUEST_METHOD'] === 'POST' && $rol === 2) {
            $accionPost = $_POST['accion'] ?? '';

            if ($accionPost === 'cambiarEstadoSolicitud') {
                $nuevoEstado = (int) ($_POST['estado_solicitud'] ?? 0);
                $estadoResultado = mysqli_query($conexion, "SELECT estado FROM estado_solicitud WHERE id_estado = $nuevoEstado LIMIT 1");
                $estadoData = mysqli_fetch_assoc($estadoResultado);

                if ($estadoData && $nuevoEstado !== (int) $solicitudData['id_estado']) {
                    $sql = "UPDATE solicitud_servicio SET id_estado = $nuevoEstado WHERE id_solicitud = $id_solicitud";

                    if (mysqli_query($conexion, $sql)) {
                        $estadoNombre = $estadoData['estado'];
                        $accion = "El tecnico (ID: $idUsuario) cambio la solicitud #$id_solicitud al estado: $estadoNombre";
                        $sql = "INSERT INTO historial_activo (id_activo, accion) VALUES (" . (int) $solicitudData['id_activo'] . ", '$accion')";
                        mysqli_query($conexion, $sql);
                    }
                }

                header("Location: /Kinetix/es/solicitud/$id_solicitud");
                exit;
            }

            if ($accionPost === 'aceptarSolicitud') {
                $sql = "UPDATE solicitud_servicio SET id_estado = 2 WHERE id_solicitud = $id_solicitud AND id_estado IN (1, 2)";
                if (mysqli_query($conexion, $sql)) {
                    $accion = "El tecnico (ID: $idUsuario) acepto y reviso la solicitud #$id_solicitud";
                    $sql = "INSERT INTO historial_activo (id_activo, accion) VALUES (" . (int) $solicitudData['id_activo'] . ", '$accion')";
                    mysqli_query($conexion, $sql);
                }
                header("Location: /Kinetix/es/solicitud/$id_solicitud");
                exit;
            }

            if ($accionPost === 'resolverSolicitud') {
                $sql = "UPDATE solicitud_servicio SET id_estado = 4 WHERE id_solicitud = $id_solicitud";
                if (mysqli_query($conexion, $sql)) {
                    $accion = "El tecnico (ID: $idUsuario) marco la solicitud #$id_solicitud como solucionada";
                    $sql = "INSERT INTO historial_activo (id_activo, accion) VALUES (" . (int) $solicitudData['id_activo'] . ", '$accion')";
                    mysqli_query($conexion, $sql);
                }
                header("Location: /Kinetix/es/solicitud/$id_solicitud");
                exit;
            }

            if ($accionPost === 'cerrarSolicitud') {
                $sql = "UPDATE solicitud_servicio SET id_estado = 3 WHERE id_solicitud = $id_solicitud";
                if (mysqli_query($conexion, $sql)) {
                    $accion = "El tecnico (ID: $idUsuario) cerro la solicitud #$id_solicitud";
                    $sql = "INSERT INTO historial_activo (id_activo, accion) VALUES (" . (int) $solicitudData['id_activo'] . ", '$accion')";
                    mysqli_query($conexion, $sql);
                }
                header("Location: /Kinetix/es/solicitud/$id_solicitud");
                exit;
            }
        }

        if ($solicitudData) {
            $historialActivo = mysqli_query(
                $conexion,
                "SELECT fecha, accion FROM historial_activo WHERE id_activo = " . (int) $solicitudData['id_activo'] . " ORDER BY fecha ASC"
            );
        } else {
            $id_solicitud = 0;
        }
    }
}

// Cargar las opciones que se muestran en los filtros y controles.
$prioridades = "SELECT id_prioridad, prioridad FROM prioridad_ticket";
$prioridadesResultado = mysqli_query($conexion, $prioridades);
$categorias = "SELECT id_categoria, categoria FROM categoria_ticket";
$categoriasFiltroResultado = mysqli_query($conexion, $categorias);
$categoriasResultado = mysqli_query($conexion, $categorias);
$estadosResultado = mysqli_query($conexion, "SELECT id_estado, estado FROM estado_ticket");
$estadosSolicitudResultado = mysqli_query($conexion, "SELECT id_estado, estado FROM estado_solicitud ORDER BY id_estado ASC");

?>

<!DOCTYPE html>
<html lang="es">
<head>
<base href="/Kinetix/es/">
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title data-i18n="tickets.title">Tickets</title>
<link rel="stylesheet" href="../css/sistema-de-tickets-tecnico.css">
<link rel="icon" type="image/png" href="../imagenes/favicon.png">
<link rel="stylesheet" href="../css/kinetix-theme.css">
<link rel="stylesheet" href="../css/menu-layout.css">
<script src="../js/theme.js" defer></script>
<script src="../js/i18n.js?v=4" defer></script>
</head>

<body>
    <!-- Menu principal con enlaces a las secciones del sitio. -->
    <nav class="menu">
        <div class="menu-content">
            <div class="menu-logo">
                <img src="../imagenes/Logo1.png" alt="Logo Kinetix">
            </div>

            <div class="nav-links">
                <ul>
                    <li><a href="inicio" data-i18n="nav.inicio">Inicio</a></li>
                    <li><a href="preguntas-frecuentes" data-i18n="nav.ayuda">Ayuda</a></li>
                    <li><a href="tickets" class="active" aria-current="page" data-i18n="nav.tickets">Tickets</a></li>
                    <li><a href="inventario" data-i18n="nav.inventario">Inventario</a></li>
                    <li><a href="usuario/<?= $idUsuario ?>" data-i18n="nav.mi_cuenta">Mi Cuenta</a></li>
                </ul>
            </div>
            <div class="nav-actions">
                <ul>
                    <li><a class="logout-button" href="../php/logout"><span class="logout-sign" aria-hidden="true"></span><span class="logout-text" data-i18n="nav.cerrar_sesion">Cerrar sesión</span></a></li>
                    <li> <button type="button" id="themeToggle" class="theme-toggle" aria-label="Cambiar tema">🌙</button></li>
                    <li>
                        <button type="button" id="langToggle" class="switch" data-i18n-toggle aria-label="Cambiar idioma">
                            <span class="lang left">ES</span><span class="lang right">EN</span><span class="slider"></span>
                        </button>
                    </li>
                </ul>
            </div>
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
    
    <div class="page">
        <div class="container">
            <!-- Lista de tickets o solicitudes de servicio. -->
            <div class="sidebar">
                <div class="ticket-tabs" role="tablist" aria-label="Tipo de solicitud" data-i18n-attrs='{"aria-label":"tickets.request_type"}'>
                    <a href="tickets" class="<?= $vista === 'tickets' ? 'active' : '' ?>" <?= $vista === 'tickets' ? 'aria-current="page"' : '' ?> data-i18n="nav.tickets">Tickets</a>
                    <a href="tickets/solicitudes" class="<?= $vista === 'solicitudes' ? 'active' : '' ?>" <?= $vista === 'solicitudes' ? 'aria-current="page"' : '' ?> data-i18n="tickets.requests">Solicitudes</a>
                </div>
                <div class="ticket-list">
                    <?php if ($vista === 'tickets') { ?>
                    <form class="ticket-filter" method="GET" action="tickets">
                        <label for="id_categoria" data-i18n="tickets.filter_category">Categoría</label>
                        <select id="id_categoria" name="id_categoria" onchange="this.form.submit()">
                            <option value="0" <?= $id_categoria === 0 ? 'selected' : '' ?> data-i18n="tickets.all">Todos</option>
                                <?php while ($reg = mysqli_fetch_assoc($categoriasFiltroResultado)) { ?>
                                <option value="<?= (int) $reg['id_categoria'] ?>" <?= $id_categoria === (int) $reg['id_categoria'] ? 'selected' : '' ?> data-i18n-db><?=  $reg['categoria'] ?></option>
                                <?php } ?>
                        </select>
                        <?php if ($rol === 3) { ?>
                            <label for="estadoFiltro" data-i18n="tickets.filter_status">Estado</label>
                            <select id="estadoFiltro" name="estado" onchange="this.form.submit()">
                                <option value="0" <?= $estadoFiltro === 0 ? 'selected' : '' ?> data-i18n="tickets.all_statuses">Todos los estados</option>
                                <?php while ($estado = mysqli_fetch_assoc($estadosFiltroResultado)) { ?>
                                    <option value="<?= (int) $estado['id_estado'] ?>" <?= $estadoFiltro === (int) $estado['id_estado'] ? 'selected' : '' ?> data-i18n-db><?= $escape($estado['estado']) ?></option>
                                <?php } ?>
                            </select>
                            <label for="ordenFiltro" data-i18n="tickets.filter_age">Antigüedad</label>
                            <select id="ordenFiltro" name="orden" onchange="this.form.submit()">
                                <option value="recientes" <?= $ordenTickets === 'recientes' ? 'selected' : '' ?> data-i18n="tickets.newest_first">Más recientes primero</option>
                                <option value="antiguos" <?= $ordenTickets === 'antiguos' ? 'selected' : '' ?> data-i18n="tickets.oldest_first">Más antiguos primero</option>
                            </select>
                        <?php } ?>
                    </form>
                    <!-- Mostrar cada ticket encontrado. -->
                    <?php while($reg = mysqli_fetch_assoc($ticketlistresult)) { ?>
                        <a class="ticket" href="ticket/<?= (int) $reg['id_ticket'] ?>">
                            <div class="ticket-top">
                                <span class="ticket-id">#<?= str_pad($reg['id_ticket'], 5, '0', STR_PAD_LEFT) ?></span>
                                <div class="ticket-status <?= strtolower(str_replace(' ', '-', $reg['estado'])) ?>">
                                    <span class="status-dot"></span>
                                    <span data-i18n-db><?= $reg['estado'] ?></span>
                                </div>
                            </div>
                            <div class="ticket-category-row">
                                <h6 style="font-size:13px;"><span data-i18n="tickets.ticket_category">Categoria:</span> <span data-i18n-db><?= $reg['categoria']?></span></h6>
                                <?php if ($rol === 1 && !($reg['estado'] === "Cancelado" || $reg['estado'] === "Resuelto" || $reg['estado'] === "Cerrado")) {?>
                                <form method="POST">
                                    <input type="hidden" name="accion" value="cancelarTicket">
                                    <input type="hidden" name="id_ticket" value="<?= $reg['id_ticket'] ?>">
                                    <button class="btn-eliminar" data-i18n="tickets.cancel">Cancelar</button>
                                </form>
                                <?php }?>
                            </div>
                            <div class="ticket-title">
                                <h6 style="font-size:16px; font-weight: lighter;"><?= $reg['titulo'] ?></h6>
                            </div>
                        </a>
                    <?php } } else { ?>
                        <?php while ($reg = mysqli_fetch_assoc($solicitudesResultado)) { ?>
                            <a class="ticket" href="solicitud/<?= (int) $reg['id_solicitud'] ?>">
                                <div class="ticket-top">
                                    <span class="ticket-id"><span data-i18n="tickets.request">Solicitud</span> #<?= str_pad($reg['id_solicitud'], 5, '0', STR_PAD_LEFT) ?></span>
                                    <span class="ticket-status"><span class="status-dot"></span><span data-i18n-db><?= $reg['estado'] ?></span></span>
                                </div>
                                <div class="ticket-title">
                                    <h6><?= $reg['activo'] ?></h6>
                                </div>
                                <p class="request-list-meta"><span data-i18n-db><?= ucfirst($reg['tipo_servicio'] ?? '') ?></span> · <span data-i18n-db><?= $reg['prioridad'] ?? 'Sin prioridad' ?></span></p>
                            </a>
                        <?php } ?>
                    <?php } ?>
                </div>
            </div>

            <!-- Panel principal con el chat del ticket seleccionado. -->

            <div class="main">
                <div class="header">
                    <h1 style="text-align: center; padding: 15px;">
                        <?= $vista === 'solicitudes'
                            ? ($solicitudData ? '<span data-i18n="tickets.request">Solicitud</span> #' . $solicitudData['id_solicitud'] . ' · ' . $solicitudData['activo'] : '<span data-i18n="tickets.select_request">Seleccioná una solicitud</span>')
                            : ($ticketTitulo ?? '<span data-i18n="tickets.select_ticket">Seleccioná un ticket</span>') ?>
                    </h1>
                </div>
                <?php if ($rol === 3 && $vista === 'tickets' && $id_ticket > 0 && $ticketData) { ?>
                    <section class="admin-ticket-summary" aria-label="Datos del ticket">
                        <p><strong>Solicitante:</strong> #<?= (int) $ticketData['id_solicitante'] ?></p>
                        <p><strong>Técnico asignado:</strong>
                            <?= !empty($ticketData['id_tecnico'])
                                ? $escape(trim($ticketData['tecnico_nombre'] . ' ' . $ticketData['tecnico_apellido'])) . ' (#' . (int) $ticketData['id_tecnico'] . ')'
                                : 'Sin asignar' ?>
                        </p>
                    </section>
                    <?php if (!empty($ticketData['id_tecnico'])) { ?>
                        <form class="admin-unassign-form" method="POST" action="ticket/<?= $id_ticket ?>">
                            <input type="hidden" name="accion" value="quitarTecnico">
                            <input type="hidden" name="id_ticket" value="<?= $id_ticket ?>">
                            <button type="submit" class="btn-eliminar">Quitar técnico asignado</button>
                        </form>
                    <?php } ?>
                <?php } ?>
                <?php if ($vista === 'solicitudes') { ?>
                    <div class="chat">
                        <?php if ($solicitudData) { ?>
                            <article class="request-details">
                                <dl>
                                    <div><dt data-i18n="tickets.requester">Solicitante</dt><dd><?= htmlspecialchars($solicitudData['solicitante_nombre'] ?? '') ?> <?= htmlspecialchars($solicitudData['solicitante_apellido'] ?? '') ?> (<?= htmlspecialchars($solicitudData['solicitante_email'] ?? '') ?>)</dd></div>
                                    <div><dt data-i18n="tickets.asset">Activo</dt><dd><?= $solicitudData['activo'] ?> (ID: <?= (int) $solicitudData['id_activo'] ?>)</dd></div>
                                    <div><dt data-i18n="tickets.service_type">Tipo de servicio</dt><dd><span data-i18n-db><?= ucfirst($solicitudData['tipo_servicio'] ?? '') ?></span></dd></div>
                                    <div><dt data-i18n="tickets.priority">Prioridad</dt><dd><span data-i18n-db><?= $solicitudData['prioridad'] ?? '' ?></span></dd></div>
                                    <div><dt data-i18n="tickets.status">Estado</dt><dd><span data-i18n-db><?= $solicitudData['estado'] ?></span></dd></div>
                                    <div><dt data-i18n="tickets.location">Sede</dt><dd><?php if (empty($solicitudData['ubicacion_calle'])) { ?><span data-i18n="tickets.undefined_location">No definida</span><?php } else { ?><?= htmlspecialchars($solicitudData['ubicacion_calle']) ?><?php } ?> <?= !empty($solicitudData['ubicacion_ciudad']) ? '· ' . htmlspecialchars($solicitudData['ubicacion_ciudad']) : '' ?></dd></div>
                                    <div><dt data-i18n="tickets.created">Creada</dt><dd><?= $solicitudData['fecha_creacion'] ?></dd></div>
                                </dl>
                                <p><?= nl2br($solicitudData['descripcion']) ?></p>
                            </article>
                            <?php if ($historialActivo) { ?>
                                <div class="ticket-history request-history">
                                    <h3 data-i18n="tickets.request_history">Historial de la solicitud</h3>
                                    <?php while ($historial = mysqli_fetch_assoc($historialActivo)) { ?>
                                        <div class="history-item">
                                            <small><?= $historial['fecha'] ?></small>
                                            <p><?= nl2br(htmlspecialchars($historial['accion'])) ?></p>
                                        </div>
                                    <?php } ?>
                                </div>
                            <?php } ?>
                        <?php } else { ?>
                            <p class="ticket-history-empty" data-i18n="tickets.select_request_from_list">Seleccioná una solicitud de la lista.</p>
                        <?php } ?>
                    </div>
                    <?php if ($rol === 2 && $solicitudData) { ?>
                        <div class="tech-controls request-state-controls">
                            <form method="POST" action="solicitud/<?= $id_solicitud ?>">
                                <input type="hidden" name="accion" value="cambiarEstadoSolicitud">
                                <input type="hidden" name="id_solicitud" value="<?= $id_solicitud ?>">
                                <select name="estado_solicitud" aria-label="Cambiar estado de la solicitud" data-i18n-attrs='{"aria-label":"tickets.change_status"}' onchange="this.form.submit()">
                                    <?php while ($estado = mysqli_fetch_assoc($estadosSolicitudResultado)) { ?>
                                        <option value="<?= (int) $estado['id_estado'] ?>" <?= (int) $estado['id_estado'] === (int) $solicitudData['id_estado'] ? 'selected' : '' ?> data-i18n-db><?= $estado['estado'] ?></option>
                                    <?php } ?>
                                </select>
                            </form>
                            <div class="request-action-row">
                                <form method="POST" action="solicitud/<?= $id_solicitud ?>">
                                    <input type="hidden" name="accion" value="aceptarSolicitud">
                                    <input type="hidden" name="id_solicitud" value="<?= $id_solicitud ?>">
                                    <button type="submit" data-i18n="tickets.accept_review">Aceptar y revisar</button>
                                </form>
                                <form method="POST" action="solicitud/<?= $id_solicitud ?>">
                                    <input type="hidden" name="accion" value="resolverSolicitud">
                                    <input type="hidden" name="id_solicitud" value="<?= $id_solicitud ?>">
                                    <button type="submit" data-i18n="tickets.mark_resolved">Marcar solucionada</button>
                                </form>
                                <form method="POST" action="solicitud/<?= $id_solicitud ?>">
                                    <input type="hidden" name="accion" value="cerrarSolicitud">
                                    <input type="hidden" name="id_solicitud" value="<?= $id_solicitud ?>">
                                    <button type="submit" data-i18n="tickets.close_request">Cerrar solicitud</button>
                                </form>
                            </div>
                        </div>
                    <?php } ?>
                <?php } else { ?>
                <!-- Mostrar los mensajes y permitir borrar los propios. -->
                <div class="chat">
                    <?php while($mensajesResultado && $reg = mysqli_fetch_assoc($mensajesResultado)) { ?>
                        <?php $fotoMensaje = urlImagenSubida($reg['foto_path']); ?>
                        <div class="message <?= $idUsuario === (int) $reg['id_usuario'] ? 'sent' : 'received' ?>">
                            <div class="message-author">
                                <?php if ($fotoMensaje !== null) { ?>
                                    <img src="<?= $escape($fotoMensaje) ?>" alt="" loading="lazy">
                                <?php } else { ?>
                                    <span class="message-avatar-placeholder" aria-hidden="true"><?= $escape(strtoupper(substr($reg['nombre'], 0, 1))) ?></span>
                                <?php } ?>
                                <strong><?= $escape($reg['nombre'] . ' ' . $reg['apellido']) ?></strong>
                            </div>
                            <div class="message-body"><?= $escape($reg['contenido']) ?></div>
                            <?php if ($rol !== 3 && $idUsuario === (int) $reg['id_usuario'] && !in_array($ticketEstado, [2, 4, 5], true)) { ?>
                                <form method="POST">
                                    <input type="hidden" name="accion" value="eliminarMensaje">
                                    <input type="hidden" name="id_ticket" value="<?= $id_ticket ?>">
                                    <input type="hidden" name="id_mensaje" value="<?= $reg['id_mensaje'] ?>">
                                    <button type="submit" data-i18n="tickets.delete">Eliminar</button>
                                </form>
                            <?php } ?>
                        </div>
                    <?php }?>
                </div>
                <!-- Controles de resolución exclusivos del técnico. -->
                <?php if ($rol === 2) {?>
                    <div class="resolution-panel" <?= ($id_ticket <= 0 || in_array($ticketEstado, [2, 4, 5])) ? 'style="visibility: hidden;"' : ''; ?>>
                        <h2 data-i18n="tickets.resolution">Resultado del ticket</h2>
                        <form method="POST">
                            <input type="hidden" name="accion" value="cerrarTicket">
                            <input type="hidden" name="id_ticket" value="<?= $id_ticket ?>">
                            <textarea name="solucion" rows="2" <?= ($id_ticket <= 0 || in_array($ticketEstado, [2, 4, 5])) ? 'placeholder="El ticket esta cerrado." disabled data-i18n-attrs=\'{"placeholder":"tickets.closed_placeholder"}\'' : 'placeholder="Escribí cuál fue la solución aplicada..." data-i18n-attrs=\'{"placeholder":"tickets.solution_placeholder"}\'' ?>></textarea>
                            <button type="submit" class="btn-editar" <?= ($id_ticket <= 0 || in_array($ticketEstado, [2, 4, 5])) ? 'disabled' : '' ?> data-i18n="tickets.close_save_resolution">Cerrar ticket y guardar resultado</button>
                        </form>
                    </div>
                <?php } ?>
                <?php if (in_array($rol, [2, 3], true) && $id_ticket > 0) { ?>
                    <div class="tech-controls">
                        <div>
                            <form method="POST">
                                <input type="hidden" name="accion" value="cambiarCategoria">
                                <select name="categoria" id="categoria" onchange="this.form.submit()">
                                    <option value="" data-i18n="tickets.change_category">Cambiar categoria</option>
                                    <?php while ($reg = mysqli_fetch_assoc($categoriasResultado)) { ?>
                                    <option value="<?= $reg['id_categoria'] ?>" data-i18n-db><?=  $reg['categoria'] ?></option>
                                    <?php } ?>
                                </select>
                            </form>
                        </div>
                        <div>
                            <form method="POST">
                                <input type="hidden" name="accion" value="cambiarPrioridad">
                                <select name="prioridad" id="prioridad" onchange="this.form.submit()">
                                    <option value="" data-i18n="tickets.change_priority">Cambiar prioridad</option>
                                    <?php while ($reg = mysqli_fetch_assoc($prioridadesResultado)) { ?>
                                    <option value="<?= $reg['id_prioridad'] ?>" data-i18n-db><?=  $reg['prioridad'] ?></option>
                                    <?php } ?>
                                </select>
                            </form>
                        </div>
                        <div>
                            <form method="POST">
                                <input type="hidden" name="accion" value="cambiarEstado">
                                <select name="estado" id="estado" onchange="this.form.submit()">
                                    <option value="" data-i18n="tickets.change_status">Cambiar estado</option>
                                    <?php while ($reg = mysqli_fetch_assoc($estadosResultado)) { ?>
                                        <?php if ($rol === 2 || in_array((int) $reg['id_estado'], [1, 3], true)) { ?>
                                            <option value="<?= (int) $reg['id_estado'] ?>" <?= (int) $reg['id_estado'] === $ticketEstado ? 'selected' : '' ?> data-i18n-db><?= $escape($reg['estado']) ?></option>
                                        <?php } ?>
                                    <?php } ?>
                                </select>
                            </form>
                        </div>
                    </div>
                <?php }?>
                <!-- Formulario para enviar mensajes mientras el ticket esta abierto. -->
                <?php if ($rol !== 3) { ?>
                <div class="chat-input">
                    <form method="POST">
                        <input type="hidden" name="accion" value="mandarMensaje">
                        <input type="hidden" name="id_ticket" value="<?= $id_ticket ?>">
                        <textarea name="contenido" rows="2" cols="10" <?= ($id_ticket <= 0 || in_array($ticketEstado, [2, 4, 5])) ? 'placeholder="El ticket esta cerrado." disabled data-i18n-attrs=\'{"placeholder":"tickets.closed_placeholder"}\'' : 'placeholder="Escriba un mensaje..." data-i18n-attrs=\'{"placeholder":"tickets.message_placeholder"}\'' ?> onkeydown="if (event.key === 'Enter') { this.form.submit(); }"></textarea>
                        <button type="submit" class="send-button" <?= ($id_ticket <= 0 || in_array($ticketEstado, [2, 4, 5])) ? 'disabled' : '' ?> data-i18n="tickets.send">Enviar</button>
                    </form>
                </div>
                <?php } ?>
                <?php } ?>
            </div>

            <!-- Historial del ticket o del activo, visible solo para tecnicos. -->
            <?php if (in_array($rol, [2, 3], true) && ($rol === 2 || $vista === 'tickets')) { ?>
                <?php $historialActual = $vista === 'solicitudes' ? $historialActivo : $historialTicket; ?>
                <aside class="ticket-history" aria-label="Historial del <?= $vista === 'solicitudes' ? 'activo' : 'ticket' ?>" data-i18n-attrs='{"aria-label":"<?= $vista === 'solicitudes' ? 'tickets.asset_history' : 'tickets.ticket_history' ?>"}'>
                    <div class="ticket-history-header">
                        <h2 data-i18n="<?= $vista === 'solicitudes' ? 'tickets.asset_history' : 'tickets.ticket_history' ?>"><?= $vista === 'solicitudes' ? 'Historial del activo' : 'Historial del ticket' ?></h2>
                        <span><?= $vista === 'solicitudes'
                            ? ($solicitudData ? $solicitudData['activo'] : '<span data-i18n="tickets.no_selection">Sin seleccionar</span>')
                            : ($id_ticket > 0 ? '#' . str_pad($id_ticket, 5, '0', STR_PAD_LEFT) : '<span data-i18n="tickets.no_selection">Sin seleccionar</span>') ?></span>
                    </div>
                    <div class="ticket-history-list">
                        <?php if (empty($historialActual)) { ?>
                            <p class="ticket-history-empty" data-i18n="<?= $vista === 'solicitudes' ? 'tickets.asset_history_empty' : 'tickets.ticket_history_empty' ?>">Aca se mostrara el historial del <?= $vista === 'solicitudes' ? 'activo seleccionado' : 'ticket seleccionado' ?>.</p>
                        <?php } else { ?>
                            <?php while ($reg = mysqli_fetch_assoc($historialActual)) { ?>
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
</body>
</html>
