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
    $keys = [
        'POSTGRES_URL',
        'DATABASE_URL',
        'POSTGRES_URL_NON_POOLING',
        'POSTGRES_PRISMA_URL',
        'POSTGRES_URL_NO_SSL',
        // Préfixe Vercel Storage (ex. STORAGE_POSTGRES_URL)
        'STORAGE_POSTGRES_URL',
        'STORAGE_DATABASE_URL',
        'STORAGE_POSTGRES_URL_NON_POOLING',
        'STORAGE_POSTGRES_PRISMA_URL',
    ];

    foreach ($keys as $key) {
        $url = env_var($key);
        if ($url) {
            return $url;
        }
    }

    // Détection automatique si Vercel utilise un autre préfixe custom
    foreach ([$_ENV, $_SERVER] as $source) {
        foreach ($source as $key => $value) {
            if (!is_string($key) || !is_string($value) || $value === '') {
                continue;
            }
            if (preg_match('/^(.*_)?(POSTGRES_URL|DATABASE_URL)$/', $key)) {
                return $value;
            }
        }
    }

    return null;
}
