<?php

namespace App;

/**
 * Chargeur minimaliste de fichier .env (évite une dépendance à vlucas/phpdotenv
 * dans ce squelette). Remplaçable par un vrai package une fois composer install
 * disponible.
 */
final class Env
{
    private static bool $loaded = false;

    public static function load(string $path): void
    {
        if (self::$loaded || !is_file($path)) {
            return;
        }

        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }
            [$key, $value] = array_map('trim', explode('=', $line, 2));
            $value = trim($value, "\"'");
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }

        self::$loaded = true;
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        return $_ENV[$key] ?? getenv($key) ?: $default;
    }
}
