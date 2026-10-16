<?php
declare(strict_types=1);

namespace App\Core;

/**
 * CONTENT BLOCKS: the editable copy of every registry page. A block is addressed by (page slug, "section.field").
 * Value = the owner's saved override (table content_blocks) or, when none exists, the registry DEFAULT. So the site renders
 * completely even with an empty database. Cached per page per request (ONE query per page).
 *
 * Reading (views use the helpers content(), content_html(), content_img(), content_list(), section_on()):
 *   Content::get('home', 'hero.title')             // string | bool (boolean) | int|float|string (number) | array (repeater)
 *   Content::image('home', 'hero.image')           // ['path','src','alt','has']
 *   Content::list('about', 'values.items')         // repeater rows (each row has every sub-field key)
 *   Content::sectionOn('home', 'impact')           // bool
 * Writing (admin Pages editor):
 *   Content::saveMany('about', $input, $userId)    // $input = ['hero.title' => '...', 'hero.image' => 'path', 'hero.image.alt' => '...',
 *                                                  //           'values._visible' => '1', 'values.items' => [ ['title' => ..], .. ]]
 *   Content::set($slug, $key, $value, $userId)     // one block (sanitised per field type; equal-to-default => override removed)
 *   Content::reset($slug, $key) / resetSection($slug, $sectionKey) / resetPage($slug)
 *   Content::isOverridden($slug, $key)
 * Special keys: "<section>._visible" ('1'/'0') and "<imageField>.alt" are always valid for known sections / image fields.
 */
final class Content
{
    /** @var array<string,array<string,string>> slug => [block_key => raw db value] */
    private static array $overrides = [];

    // -------------------------------------------------------------- read

    /** @return array<string,string> raw saved overrides for a page (single query, cached). */
    public static function overrides(string $slug): array
    {
        if (!isset(self::$overrides[$slug])) {
            self::$overrides[$slug] = [];
            try {
                foreach (Database::all('SELECT block_key, value FROM content_blocks WHERE page_slug = ?', [$slug]) as $r) {
                    self::$overrides[$slug][(string) $r['block_key']] = (string) ($r['value'] ?? '');
                }
            } catch (\Throwable) {
                // no database yet: defaults only
            }
        }
        return self::$overrides[$slug];
    }

    public static function isOverridden(string $slug, string $key): bool
    {
        return array_key_exists($key, self::overrides($slug));
    }

    public static function get(string $slug, string $key, mixed $default = null): mixed
    {
        $field = PageRegistry::field($slug, $key);
        $over = self::overrides($slug);
        if ($field === null) {
            return $over[$key] ?? $default;              // keys outside the registry (rare): raw string
        }
        if (array_key_exists($key, $over)) {
            return self::decode($field, $over[$key]);
        }
        return $field['default'] ?? $default;
    }

    /** Typed value of a stored string for a field definition. */
    private static function decode(array $field, string $raw): mixed
    {
        switch ($field['type']) {
            case 'boolean':
                return $raw === '1';
            case 'number':
                return is_numeric($raw) ? (str_contains($raw, '.') ? (float) $raw : (int) $raw) : '';
            case 'repeater':
                $rows = json_decode($raw, true);
                return is_array($rows) ? array_values($rows) : $field['default'];
            default:
                return $raw;
        }
    }

    /** @return array{path:string,src:string,alt:string,has:bool} */
    public static function image(string $slug, string $key): array
    {
        $field = PageRegistry::field($slug, $key);
        $path = (string) self::get($slug, $key, '');
        $over = self::overrides($slug);
        $alt = array_key_exists($key . '.alt', $over) ? $over[$key . '.alt'] : (string) ($field['alt'] ?? '');
        return ['path' => $path, 'src' => $path !== '' ? img_url($path) : '', 'alt' => $alt, 'has' => $path !== ''];
    }

    /** @return array<int,array<string,mixed>> */
    public static function list(string $slug, string $key): array
    {
        $field = PageRegistry::field($slug, $key);
        $rows = self::get($slug, $key, []);
        if (!is_array($rows)) {
            return [];
        }
        $subs = (array) ($field['fields'] ?? []);
        $out = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $filled = [];
            foreach ($subs as $sk => $sf) {
                $filled[$sk] = array_key_exists($sk, $row) ? $row[$sk] : ($sf['default'] ?? '');
                if ($sf['type'] === 'image') {
                    $filled[$sk . '_alt'] = $row[$sk . '_alt'] ?? ($sf['alt'] ?? '');
                }
            }
            $out[] = $filled + $row;
        }
        return $out;
    }

    public static function sectionOn(string $slug, string $sectionKey): bool
    {
        $section = PageRegistry::sections($slug)[$sectionKey] ?? null;
        if ($section === null) {
            return true;
        }
        if (!$section['toggle']) {
            return true;
        }
        $over = self::overrides($slug);
        $k = $sectionKey . '._visible';
        return array_key_exists($k, $over) ? $over[$k] === '1' : (bool) $section['default_visible'];
    }

    /** Resolved snapshot of every field of a page, for the admin editor: key => ['value','default','overridden','field']. */
    public static function snapshot(string $slug): array
    {
        $out = [];
        foreach (PageRegistry::fields($slug) as $key => $field) {
            $out[$key] = [
                'field'      => $field,
                'value'      => self::get($slug, $key),
                'default'    => $field['default'],
                'overridden' => self::isOverridden($slug, $key),
            ];
            if ($field['type'] === 'image') {
                $out[$key]['alt'] = self::image($slug, $key)['alt'];
            }
        }
        return $out;
    }

    // -------------------------------------------------------------- write

    /**
     * Save many blocks at once (single transaction). Unknown keys are ignored. Returns the list of keys that were saved or reset.
     * @param array<string,mixed> $input
     * @return string[]
     */
    public static function saveMany(string $slug, array $input, ?int $userId = null): array
    {
        if (!PageRegistry::has($slug)) {
            throw new \InvalidArgumentException("Unknown registry page: {$slug}");
        }
        self::ensurePageRow($slug);
        $changed = [];
        Database::transaction(function () use ($slug, $input, $userId, &$changed): void {
            foreach ($input as $key => $value) {
                if (self::apply($slug, (string) $key, $value, $userId)) {
                    $changed[] = (string) $key;
                }
            }
        });
        self::flush($slug);
        return $changed;
    }

    public static function set(string $slug, string $key, mixed $value, ?int $userId = null): void
    {
        self::saveMany($slug, [$key => $value], $userId);
    }

    /** @return bool true if the stored state changed */
    private static function apply(string $slug, string $key, mixed $value, ?int $userId): bool
    {
        // section visibility
        if (str_ends_with($key, '._visible')) {
            $skey = substr($key, 0, -9);
            $section = PageRegistry::sections($slug)[$skey] ?? null;
            if ($section === null || !$section['toggle']) {
                return false;
            }
            $v = self::truthy($value) ? '1' : '0';
            if ($v === ($section['default_visible'] ? '1' : '0')) {
                return self::delete($slug, $key);
            }
            return self::store($slug, $key, $v, $userId);
        }
        // image alt text
        if (str_ends_with($key, '.alt')) {
            $base = substr($key, 0, -4);
            $field = PageRegistry::field($slug, $base);
            if ($field === null || $field['type'] !== 'image') {
                return false;
            }
            $alt = mb_substr(Sanitizer::text(is_scalar($value) ? (string) $value : ''), 0, 250);
            return $alt === (string) ($field['alt'] ?? '') ? self::delete($slug, $key) : self::store($slug, $key, $alt, $userId);
        }
        $field = PageRegistry::field($slug, $key);
        if ($field === null) {
            return false;
        }
        $clean = self::sanitise($field, $value);
        if (self::same($field, $clean, $field['default'])) {
            return self::delete($slug, $key);
        }
        return self::store($slug, $key, $clean, $userId);
    }

    private static function store(string $slug, string $key, string $value, ?int $userId): bool
    {
        // already saved with exactly this value: not a change (keeps "Saved (N changes)" and the activity log honest)
        $saved = self::overrides($slug);
        if (array_key_exists($key, $saved) && $saved[$key] === $value) {
            return false;
        }
        Database::execute(
            'INSERT INTO content_blocks (page_slug, block_key, value, updated_by) VALUES (?, ?, ?, ?) '
            . 'ON DUPLICATE KEY UPDATE value = VALUES(value), updated_by = VALUES(updated_by)',
            [$slug, $key, $value, $userId]
        );
        return true;
    }

    private static function delete(string $slug, string $key): bool
    {
        return Database::execute('DELETE FROM content_blocks WHERE page_slug = ? AND block_key = ?', [$slug, $key]) > 0;
    }

    public static function reset(string $slug, string $key): void
    {
        Database::execute('DELETE FROM content_blocks WHERE page_slug = ? AND (block_key = ? OR block_key = ?)', [$slug, $key, $key . '.alt']);
        self::flush($slug);
    }

    public static function resetSection(string $slug, string $sectionKey): void
    {
        Database::execute('DELETE FROM content_blocks WHERE page_slug = ? AND block_key LIKE ?', [$slug, str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $sectionKey) . '.%']);
        self::flush($slug);
    }

    public static function resetPage(string $slug): void
    {
        Database::execute('DELETE FROM content_blocks WHERE page_slug = ?', [$slug]);
        self::flush($slug);
    }

    public static function flush(?string $slug = null): void
    {
        if ($slug === null) {
            self::$overrides = [];
        } else {
            unset(self::$overrides[$slug]);
        }
    }

    private static function ensurePageRow(string $slug): void
    {
        if ((int) Database::value('SELECT COUNT(*) FROM pages WHERE slug = ?', [$slug], 0) === 0) {
            SitePages::sync();
        }
    }

    // -------------------------------------------------------------- sanitising

    /** Normalise a submitted value for a field definition to its stored string form. Public: reused by the admin editor. */
    public static function sanitise(array $field, mixed $value): string
    {
        $type = $field['type'];
        if ($type === 'repeater') {
            return self::sanitiseRepeater($field, $value);
        }
        $s = is_scalar($value) ? (string) $value : '';
        switch ($type) {
            case 'richtext':
                return Sanitizer::html($s);
            case 'textarea':
                $s = str_replace(["\r\n", "\r"], "\n", strip_tags($s));
                $max = (int) ($field['maxlength'] ?? 5000);
                return trim(mb_substr($s, 0, $max));
            case 'url':
                return Sanitizer::url($s);
            case 'email':
                $s = trim($s);
                return filter_var($s, FILTER_VALIDATE_EMAIL) ? $s : '';
            case 'number':
                $s = trim($s);
                return is_numeric($s) ? (string) $s : '';
            case 'boolean':
                return self::truthy($value) ? '1' : '0';
            case 'select':
                return array_key_exists($s, (array) ($field['options'] ?? [])) ? $s : (string) ($field['default'] ?? '');
            case 'image':
                return self::cleanImagePath($s);
            default:   // text
                $max = (int) ($field['maxlength'] ?? 500);
                return trim(mb_substr(preg_replace('/\s+/u', ' ', strip_tags($s)) ?? '', 0, $max));
        }
    }

    private static function sanitiseRepeater(array $field, mixed $value): string
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            $value = is_array($decoded) ? $decoded : [];
        }
        $rows = [];
        $max = (int) ($field['max_rows'] ?? 50);
        foreach ((array) $value as $row) {
            if (!is_array($row) || count($rows) >= $max) {
                continue;
            }
            $clean = [];
            $hasContent = false;
            foreach ((array) $field['fields'] as $sk => $sf) {
                $val = self::sanitise($sf, $row[$sk] ?? '');
                $clean[$sk] = $val;
                if ($sf['type'] === 'image') {
                    $clean[$sk . '_alt'] = mb_substr(Sanitizer::text((string) ($row[$sk . '_alt'] ?? '')), 0, 250);
                }
                if ($val !== '' && $val !== '0' && $sf['type'] !== 'select' && $sf['type'] !== 'boolean') {
                    $hasContent = true;
                }
            }
            if ($hasContent) {
                $rows[] = $clean;
            }
        }
        return (string) json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /** Image paths: '' | 'assets/...' (bundled) | 'YYYY/MM/file.ext' (uploads) | https URL. Never absolute server paths or '..'. */
    public static function cleanImagePath(string $s): string
    {
        $s = trim($s);
        if ($s === '') {
            return '';
        }
        // no ( ) ; \ ` { } | ^ quotes or whitespace: the value ends up inside CSS url() on banners, where those would break out
        if (preg_match('~^https?://[^\s"\'<>()\\\\;`{}|^]+$~i', $s)) {
            return $s;
        }
        if (str_contains($s, '..') || str_starts_with($s, '/') || !preg_match('~^[A-Za-z0-9_\-./]+$~', $s)) {
            return '';
        }
        return $s;
    }

    private static function truthy(mixed $v): bool
    {
        return in_array(is_string($v) ? strtolower($v) : $v, [true, 1, '1', 'on', 'yes', 'true'], true);
    }

    /** Is the sanitised string equal to the (typed) default? Used to drop redundant overrides. */
    private static function same(array $field, string $clean, mixed $default): bool
    {
        switch ($field['type']) {
            case 'repeater':
                $a = json_decode($clean, true) ?: [];
                $b = (array) $default;
                // default rows may omit *_alt keys; compare only the keys present in the default
                if (count($a) !== count($b)) {
                    return false;
                }
                foreach ($b as $i => $row) {
                    foreach ((array) $row as $k => $v) {
                        if ((string) ($a[$i][$k] ?? '') !== (string) $v) {
                            return false;
                        }
                    }
                    foreach ((array) ($a[$i] ?? []) as $k => $v) {
                        if (!array_key_exists($k, (array) $row) && (string) $v !== '' && !str_ends_with((string) $k, '_alt')) {
                            return false;
                        }
                    }
                }
                return true;
            case 'boolean':
                return $clean === ($default ? '1' : '0');
            case 'richtext':
                return $clean === Sanitizer::html((string) $default);
            default:
                return $clean === (string) $default;
        }
    }
}
