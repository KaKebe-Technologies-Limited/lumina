<?php
declare(strict_types=1);

namespace App\Core;

/**
 * ORGANISATION-WIDE settings (contact details, socials, donation info, branding, notify email, analytics).
 * Page copy is NOT here - it lives in content blocks (see Content / PageRegistry).
 *
 * The form AND the fallbacks are driven by app/config/settings_schema.php:
 *   Settings::get('email_primary')              // DB value, else schema default, else $default (null)
 *   Settings::get('phone_1', '')                // explicit fallback for keys not in the schema
 *   Settings::all()                             // every schema key resolved (DB overrides defaults)
 *   Settings::set('tagline', 'New tagline');    // insert/update + cache flush
 *   Settings::setMany(['a' => '1', 'b' => '2']);
 *   Settings::schema()                          // ['groups' => [...]] for the admin form
 *   Settings::definition('donate_url')          // one field definition or null
 *
 * Cached once per request (a single SELECT). Safe when the DB/table is missing: schema defaults are returned.
 */
final class Settings
{
    /** @var array<string,string>|null */
    private static ?array $db = null;
    /** @var array<string,mixed>|null */
    private static ?array $schema = null;

    public static function schema(): array
    {
        if (self::$schema === null) {
            $file = APP_PATH . '/config/settings_schema.php';
            $data = is_file($file) ? require $file : [];
            self::$schema = is_array($data) ? $data : [];
        }
        return self::$schema;
    }

    /** @return array<string,array<string,mixed>> key => field definition */
    public static function definitions(): array
    {
        $out = [];
        foreach ((array) (self::schema()['groups'] ?? []) as $group) {
            foreach ((array) ($group['fields'] ?? []) as $key => $def) {
                $out[(string) $key] = (array) $def;
            }
        }
        return $out;
    }

    public static function definition(string $key): ?array
    {
        return self::definitions()[$key] ?? null;
    }

    private static function load(): array
    {
        if (self::$db !== null) {
            return self::$db;
        }
        self::$db = [];
        try {
            foreach (Database::all('SELECT setting_key, setting_value FROM settings') as $row) {
                self::$db[(string) $row['setting_key']] = (string) ($row['setting_value'] ?? '');
            }
        } catch (\Throwable) {
            // DB or table not available yet: defaults only
        }
        return self::$db;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $db = self::load();
        if (array_key_exists($key, $db)) {
            return $db[$key];            // an explicitly saved empty string clears the default (e.g. owner removes a social link)
        }
        $def = self::definition($key);
        if ($def !== null && array_key_exists('default', $def)) {
            return $def['default'];
        }
        return $default;
    }

    /** @return array<string,mixed> */
    public static function all(): array
    {
        $out = [];
        foreach (self::definitions() as $key => $def) {
            $out[$key] = self::get($key, $def['default'] ?? '');
        }
        return $out;
    }

    /** True when the owner has explicitly saved a value for the key (even an empty one). */
    public static function isCustomised(string $key): bool
    {
        return array_key_exists($key, self::load());
    }

    public static function set(string $key, ?string $value): void
    {
        Database::execute(
            'INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)',
            [$key, $value ?? '']
        );
        self::flush();
    }

    /** @param array<string,string|null> $values */
    public static function setMany(array $values): void
    {
        Database::transaction(static function () use ($values): void {
            foreach ($values as $k => $v) {
                Database::execute(
                    'INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)',
                    [(string) $k, $v ?? '']
                );
            }
        });
        self::flush();
    }

    /** Remove the saved value so the schema default applies again. */
    public static function reset(string $key): void
    {
        Database::execute('DELETE FROM settings WHERE setting_key = ?', [$key]);
        self::flush();
    }

    public static function flush(): void
    {
        self::$db = null;
    }
}
