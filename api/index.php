<?php
/**
 * Point d'entrée Vercel — route toutes les pages PHP du site.
 */
declare(strict_types=1);

$root = dirname(__DIR__);
chdir($root);

if (getenv('VERCEL')) {
    ini_set('session.save_path', '/tmp');
}

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$path = '/' . trim(rawurldecode($path), '/');
if ($path !== '/') {
    $path = rtrim($path, '/') ?: '/';
}

$staticPages = [
    '/' => 'index.php',
    '/index.php' => 'index.php',
    '/install.php' => 'install.php',
];

if (isset($staticPages[$path])) {
    require $root . '/' . $staticPages[$path];
    return;
}

if (preg_match('#^/admin(?:/(.*))?$#', $path, $matches)) {
    $file = $matches[1] ?? '';
    if ($file === '' || $file === '/') {
        $file = 'index.php';
    }
    $file = basename(str_replace(['..', '\\'], '', $file));
    if (!str_ends_with($file, '.php')) {
        $file .= '.php';
    }
    $target = $root . '/admin/' . $file;
    if (is_file($target)) {
        require $target;
        return;
    }
}

http_response_code(404);
header('Content-Type: text/html; charset=utf-8');
echo '<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><title>404</title></head>';
echo '<body><h1>Page introuvable</h1><p><a href="/">Retour à l\'accueil</a></p></body></html>';
