<?php
function uploadImage(array $file, string $prefix, string $defaultPath, array &$errors, int $maxBytes = 5242880): string
{
    if (empty($file['name'])) {
        return $defaultPath;
    }

    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        $errors[] = 'Erro no upload da imagem.';
        return $defaultPath;
    }

    if (($file['size'] ?? 0) > $maxBytes) {
        $errors[] = 'Imagem demasiado grande (max 5MB).';
        return $defaultPath;
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/avif' => 'avif',
    ];

    if (!isset($allowed[$mime])) {
        $errors[] = 'Formato inválido. Apenas JPG, PNG, WEBP, AVIF.';
        return $defaultPath;
    }

    $ext = $allowed[$mime];
    $newName = sprintf('%s_%s.%s', $prefix, bin2hex(random_bytes(8)), $ext);
    $path = 'uploads/' . $newName;

    if (!is_dir('uploads')) {
        mkdir('uploads', 0755, true);
    }

    if (!move_uploaded_file($file['tmp_name'], $path)) {
        $errors[] = 'Não foi possível guardar a imagem.';
        return $defaultPath;
    }

    return $path;
}
