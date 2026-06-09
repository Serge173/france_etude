<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/env.php';

$status = [
    'ok' => true,
    'php' => PHP_VERSION,
    'vercel' => env_var('VERCEL') === '1',
    'db_driver' => env_var('DB_DRIVER'),
    'has_postgres_url' => postgres_url() !== null,
];

try {
    require_once __DIR__ . '/../includes/db.php';
    init_database(db());
    $status['database'] = DB_DRIVER;
    $status['db_ok'] = true;
} catch (Throwable $e) {
    $status['ok'] = false;
    $status['db_ok'] = false;
    $status['error'] = $e->getMessage();
}

http_response_code($status['ok'] ? 200 : 503);
echo json_encode($status, JSON_UNESCAPED_UNICODE);
