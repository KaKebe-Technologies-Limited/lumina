<?php
declare(strict_types=1);

namespace App\Core;

/**
 * ROLES and what each role may do.
 *   - Roles live in the `roles` table (slug, name, description, abilities JSON list, is_system). The `admin` role always holds every ability
 *     (including abilities added later); `users.manage` and `roles.manage` can NEVER be held by any other role, whatever is stored.
 *   - Abilities are the names in config app.abilities (the catalogue for the admin screen is app/config/ability_catalog.php).
 *   - If the table does not exist yet (code uploaded before the SQL import) the old config map decides, so nobody is locked out.
 * Everything is cached per request; role edits apply on the very next request of every signed-in user.
 */
final class Roles
{
    public const NEVER_ASSIGNABLE = ['users.manage', 'roles.manage'];

    /** @var array<string,array<string,mixed>>|null */
    private static ?array $cache = null;

    public static function flush(): void
    {
        self::$cache = null;
    }

    /** @return array<string,array<string,mixed>> slug => row (abilities decoded to a list). Empty when the table is missing. */
    public static function all(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }
        $out = [];
        try {
            foreach (Database::all('SELECT id, slug, name, description, abilities, is_system FROM roles ORDER BY is_system DESC, name ASC') as $r) {
                $list = json_decode((string) $r['abilities'], true);
                $r['abilities'] = is_array($list) ? array_values(array_filter($list, 'is_string')) : [];
                $r['is_system'] = (int) $r['is_system'] === 1;
                $out[(string) $r['slug']] = $r;
            }
        } catch (\Throwable) {
            $out = [];
        }
        return self::$cache = $out;
    }

    public static function exists(string $slug): bool
    {
        $all = self::all();
        return $all === [] ? in_array($slug, ['admin', 'editor'], true) : isset($all[$slug]);
    }

    /** @return list<string> */
    public static function slugs(): array
    {
        $all = self::all();
        return $all === [] ? ['admin', 'editor'] : array_keys($all);
    }

    public static function name(string $slug): string
    {
        $all = self::all();
        return isset($all[$slug]) ? (string) $all[$slug]['name'] : ($slug === 'admin' ? 'Administrator' : ucfirst($slug));
    }

    /** Every ability name the application knows. @return list<string> */
    public static function knownAbilities(): array
    {
        return array_keys((array) Config::get('abilities', []));
    }

    /** Abilities a custom role may hold. @return list<string> */
    public static function assignable(): array
    {
        return array_values(array_diff(self::knownAbilities(), self::NEVER_ASSIGNABLE));
    }

    /** @return list<string> */
    public static function abilitiesFor(string $slug): array
    {
        if ($slug === 'admin') {
            return self::knownAbilities();
        }
        $all = self::all();
        if ($all === []) {                                   // table not imported yet: the built-in map
            $map = (array) Config::get('abilities', []);
            $list = [];
            foreach ($map as $ability => $roles) {
                if (in_array($slug, (array) $roles, true)) {
                    $list[] = (string) $ability;
                }
            }
            return $list;
        }
        if (!isset($all[$slug])) {
            return [];
        }
        $list = array_values(array_intersect((array) $all[$slug]['abilities'], self::assignable()));
        return in_array('dashboard.view', $list, true) ? $list : array_merge(['dashboard.view'], $list);
    }

    public static function can(?string $role, string $ability): bool
    {
        if ($role === null || $role === '') {
            return false;
        }
        if ($role === 'admin') {
            return in_array($ability, self::knownAbilities(), true);
        }
        return in_array($ability, self::abilitiesFor($role), true);
    }

    /** slug => "Name - description" (the users form splits at " - "). */
    public static function options(): array
    {
        $out = [];
        $all = self::all();
        if ($all === []) {
            return ['admin' => 'Administrator - can do everything, including users, roles, settings and menus', 'editor' => 'Editor - can edit pages and content and handle the inbox'];
        }
        foreach ($all as $slug => $r) {
            $out[$slug] = (string) $r['name'] . ' - ' . ((string) $r['description'] !== '' ? (string) $r['description'] : ($slug === 'admin' ? 'can do everything' : 'custom role'));
        }
        return $out;
    }

    public static function usersCount(string $slug): int
    {
        return (int) Database::value('SELECT COUNT(*) FROM users WHERE role = ?', [$slug]);
    }

    /** Lower-case slug from a name, unique among roles. */
    public static function makeSlug(string $name): string
    {
        $base = trim((string) preg_replace('/[^a-z0-9]+/', '-', strtolower($name)), '-');
        $base = $base === '' ? 'role' : substr($base, 0, 30);
        $slug = $base;
        for ($i = 2; self::exists($slug); $i++) {
            $slug = substr($base, 0, 26) . '-' . $i;
        }
        return $slug;
    }

    /** Keep only known, assignable ability names. @param array<int,mixed> $in @return list<string> */
    public static function clean(array $in): array
    {
        $ok = self::assignable();
        $out = [];
        foreach ($in as $a) {
            if (is_string($a) && in_array($a, $ok, true) && !in_array($a, $out, true)) {
                $out[] = $a;
            }
        }
        return $out;
    }
}
