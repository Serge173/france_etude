<?php

declare(strict_types=1);

function env_var(string $key): ?string
{
    $value = getenv($key);
    if ($value !== false && $value !== '') {
        return $value;
    }
    if (!empty($_ENV[$key])) {
        return (string) $_ENV[$key];
    }
    if (!empty($_SERVER[$key])) {
        return (string) $_SERVER[$key];
    }
    return null;
}

function postgres_env_prefixes(): array
{
    return ['', 'STORAGE_', 'STORAGE'];
}

function postgres_env_keys(): array
{
    return [
        'POSTGRES_URL',
        'DATABASE_URL',
        'POSTGRES_URL_NON_POOLING',
        'POSTGRES_PRISMA_URL',
        'POSTGRES_URL_NO_SSL',
        'POSTGRES_HOST',
        'POSTGRES_USER',
        'POSTGRES_PASSWORD',
        'POSTGRES_DATABASE',
        'PGHOST',
        'PGUSER',
        'PGPASSWORD',
        'PGDATABASE',
    ];
}

/** @return list<string> */
function postgres_detected_env_keys(): array
{
    $found = [];
    $patterns = ['POSTGRES', 'DATABASE', 'PGHOST', 'PGUSER', 'PGPASSWORD', 'PGDATABASE'];

    foreach ([$_ENV, $_SERVER] as $source) {
        foreach ($source as $key => $value) {
            if (!is_string($key) || !is_string($value) || $value === '') {
                continue;
            }
            foreach ($patterns as $pattern) {
                if (str_contains($key, $pattern) && !in_array($key, $found, true)) {
                    $found[] = $key;
                }
            }
        }
    }

    sort($found);
    return $found;
}

function postgres_url(): ?string
{
    foreach (postgres_env_prefixes() as $prefix) {
        foreach (['POSTGRES_URL', 'DATABASE_URL', 'POSTGRES_URL_NON_POOLING', 'POSTGRES_PRISMA_URL', 'POSTGRES_URL_NO_SSL'] as $suffix) {
            $url = env_var($prefix . $suffix);
            if ($url) {
                return $url;
            }
        }
    }

    foreach ([$_ENV, $_SERVER] as $source) {
        foreach ($source as $key => $value) {
            if (!is_string($key) || !is_string($value) || $value === '') {
                continue;
            }
            if (preg_match('/(POSTGRES_URL|DATABASE_URL)/', $key)) {
                return $value;
            }
        }
    }

    return null;
}

/**
 * @return array{host:string,port:int,user:string,pass:string,dbname:string}|null
 */
function postgres_parts(): ?array
{
    foreach (postgres_env_prefixes() as $prefix) {
        $host = env_var($prefix . 'POSTGRES_HOST') ?: env_var($prefix . 'PGHOST');
        $user = env_var($prefix . 'POSTGRES_USER') ?: env_var($prefix . 'PGUSER');
        $pass = env_var($prefix . 'POSTGRES_PASSWORD') ?: env_var($prefix . 'PGPASSWORD');
        $db = env_var($prefix . 'POSTGRES_DATABASE') ?: env_var($prefix . 'PGDATABASE');

        if ($host && $user && $pass !== null && $db) {
            return [
                'host' => $host,
                'port' => (int) (env_var($prefix . 'POSTGRES_PORT') ?: env_var($prefix . 'PGPORT') ?: 5432),
                'user' => $user,
                'pass' => $pass,
                'dbname' => $db,
            ];
        }
    }

    return null;
}

function postgres_available(): bool
{
    return postgres_url() !== null || postgres_parts() !== null;
}

function pdo_from_postgres_config(): PDO
{
    $url = postgres_url();
    if ($url) {
        return pdo_from_database_url($url);
    }

    $parts = postgres_parts();
    if (!$parts) {
        throw new RuntimeException('POSTGRES_URL ou DATABASE_URL requis pour PostgreSQL.');
    }

    $dsn = sprintf(
        'pgsql:host=%s;port=%d;dbname=%s;sslmode=require',
        $parts['host'],
        $parts['port'],
        $parts['dbname']
    );

    return new PDO($dsn, $parts['user'], $parts['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
}

function pdo_from_database_url(string $url): PDO
{
    $parts = parse_url($url);
    if ($parts === false || empty($parts['host'])) {
        throw new RuntimeException('URL de base de données invalide.');
    }

    $host = $parts['host'];
    $port = $parts['port'] ?? 5432;
    $user = rawurldecode($parts['user'] ?? '');
    $pass = rawurldecode($parts['pass'] ?? '');
    $dbname = ltrim($parts['path'] ?? '', '/');
    $dsn = "pgsql:host={$host};port={$port};dbname={$dbname}";
    if (!empty($parts['query'])) {
        parse_str($parts['query'], $params);
        foreach ($params as $key => $value) {
            $dsn .= ';' . $key . '=' . $value;
        }
    } else {
        $dsn .= ';sslmode=require';
    }

    return new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
}
