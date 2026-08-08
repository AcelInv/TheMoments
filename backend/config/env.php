<?php
declare(strict_types=1);

/**
 * Memuat konfigurasi lokal dari .env tanpa menimpa environment variable yang
 * diberikan oleh server hosting/production.
 */
function loadFloraticaEnvironment(): void {
    static $loaded = false;
    if ($loaded) return;
    $loaded = true;

    $envFile = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . '.env';
    if (!is_file($envFile)) return;

    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) continue;
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        if ($key === '' || getenv($key) !== false) continue;
        putenv($key . '=' . trim($value));
    }
}
