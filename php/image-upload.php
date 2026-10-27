<?php

function guardarImagenSubida(array $archivo, string $tipo): ?string
{
    $directorios = [
        'activos' => dirname(__DIR__) . '/imagenes/activos',
        'usuarios' => dirname(__DIR__) . '/imagenes/usuarios',
    ];
    if (!isset($directorios[$tipo])) {
        throw new InvalidArgumentException('Tipo de imagen no válido.');
    }

    if (($archivo['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if (($archivo['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('No se pudo recibir la imagen. Inténtalo nuevamente.');
    }
    if (($archivo['size'] ?? 0) > 5 * 1024 * 1024) {
        throw new RuntimeException('La imagen supera el límite de 5 MB.');
    }

    $temporal = $archivo['tmp_name'] ?? '';
    if (!is_uploaded_file($temporal)) {
        throw new RuntimeException('El archivo de imagen recibido no es válido.');
    }

    $informacion = getimagesize($temporal);
    $tiposMime = [
        IMAGETYPE_JPEG => 'jpg',
        IMAGETYPE_PNG => 'png',
        IMAGETYPE_WEBP => 'webp',
    ];
    if ($informacion === false || !isset($tiposMime[$informacion[2]])) {
        throw new RuntimeException('Usa una imagen JPG, PNG o WebP.');
    }

    if (!is_dir($directorios[$tipo]) && !mkdir($directorios[$tipo], 0755, true) && !is_dir($directorios[$tipo])) {
        throw new RuntimeException('No se pudo preparar el almacenamiento de imágenes.');
    }

    $nombre = bin2hex(random_bytes(16)) . '.' . $tiposMime[$informacion[2]];
    $destino = $directorios[$tipo] . DIRECTORY_SEPARATOR . $nombre;
    if (!move_uploaded_file($temporal, $destino)) {
        throw new RuntimeException('No se pudo guardar la imagen.');
    }

    return 'imagenes/' . $tipo . '/' . $nombre;
}

function urlImagenSubida(?string $ruta): ?string
{
    if (!$ruta || !preg_match('#^imagenes/(activos|usuarios)/[a-f0-9]{32}\.(jpg|png|webp)$#D', $ruta)) {
        return null;
    }

    return '/Kinetix/' . $ruta;
}

function eliminarImagenSubida(?string $ruta): void
{
    if (!$ruta || !preg_match('#^imagenes/(activos|usuarios)/[a-f0-9]{32}\.(jpg|png|webp)$#D', $ruta)) {
        return;
    }

    $destino = dirname(__DIR__) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $ruta);
    if (is_file($destino) && !unlink($destino)) {
        throw new RuntimeException('No se pudo eliminar la imagen anterior.');
    }
}
