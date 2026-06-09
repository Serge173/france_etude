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

function postgres_url(): ?string
{
    foreach ([
        'POSTGRES_URL',
        'DATABASE_URL',
        'POSTGRES_URL_NON_POOLING',
        'POSTGRES_PRISMA_URL',
        'POSTGRES_URL_NO_SSL',
    ] as $key) {
        $url = env_var($key);
        if ($url) {
            return $url;
        }
    }
    return null;
}
