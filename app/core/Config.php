<?php
declare(strict_types=1);

namespace App\Core;

/** Dot-notation config store. Config::get('db.host'), Config::set('x.y', 1). */
final class Config
{
    /** @var array<string,mixed> */
    private static array $items = [];

    public static function load(string $file): void
    {
        $data = require $file;
        self::$items = is_array($data) ? $data : [];
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $node = self::$items;
        foreach (explode('.', $key) as $seg) {
            if (!is_array($node) || !array_key_exists($seg, $node)) {
                return $default;
            }
            $node = $node[$seg];
        }
        return $node;
    }

    public static function set(string $key, mixed $value): void
    {
        $ref = &self::$items;
        foreach (explode('.', $key) as $seg) {
            if (!isset($ref[$seg]) || !is_array($ref[$seg])) {
                $ref[$seg] = [];
            }
            $ref = &$ref[$seg];
        }
        $ref = $value;
    }

    public static function all(): array
    {
        return self::$items;
    }

    /** Secret used for signing. APP_KEY env, else a persistent random key in storage/app.key. */
    public static function appKey(): string
    {
        static $key = null;
        if ($key !== null) {
            return $key;
        }
        $k = (string) self::get('app.key', '');
        if ($k === '') {
            $file = STORAGE_PATH . '/app.key';
            if (is_file($file)) {
                $k = trim((string) file_get_contents($file));
            }
            if ($k === '') {
                $k = bin2hex(random_bytes(32));
                if (@file_put_contents($file, $k) === false) {
                    // storage/ is not writable: a random key that lives for one request only would make every signed honeypot
                    // timestamp of the NEXT request look forged (and silently drop real visitors' form submissions). Derive a
                    // stable key from secrets that already exist in the configuration instead, and say so in the log.
                    $k = hash('sha256', implode('|', [(string) self::get('db.pass', ''), (string) self::get('db.name', ''), (string) self::get('db.user', ''), BASE_PATH, 'lumina-key-fallback']));
                    Log::error('storage/app.key cannot be written (check folder permissions or set APP_KEY in .env): using a key derived from the configuration.');
                }
            }
        }
        return $key = $k;
    }

    /** True when the signing key comes from .env or from a readable storage/app.key (i.e. it survives between requests). */
    public static function appKeyPersisted(): bool
    {
        return (string) self::get('app.key', '') !== '' || (is_file(STORAGE_PATH . '/app.key') && trim((string) @file_get_contents(STORAGE_PATH . '/app.key')) !== '');
    }
}
