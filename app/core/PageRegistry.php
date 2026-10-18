<?php
declare(strict_types=1);

namespace App\Core;

/**
 * PAGE REGISTRY - the list of editable public pages and the content blocks each one exposes to the admin.
 * One file per page: app/config/pages/<slug>.php returning an array (files starting with "_" are ignored). See
 * docs/ARCHITECTURE.md section "Page registry" for the full format and a worked example.
 *
 *   PageRegistry::all()                     // slug => normalised page definition (sorted by nav order)
 *   PageRegistry::get('about')              // one definition or null
 *   PageRegistry::fields('about')           // 'hero.title' => normalised field definition (+ 'section' key)
 *   PageRegistry::field('about', 'hero.title')
 *   PageRegistry::defaults('about')         // 'hero.title' => default value (repeaters: array of rows)
 *   PageRegistry::sections('about')         // section key => normalised section
 *   PageRegistry::sectionDefaultVisible('about', 'values')
 *   PageRegistry::flush()
 */
final class PageRegistry
{
    public const TYPES = ['text', 'textarea', 'richtext', 'image', 'url', 'email', 'number', 'boolean', 'select', 'repeater'];

    /** @var array<string,array<string,mixed>>|null */
    private static ?array $pages = null;

    /** @return array<string,array<string,mixed>> */
    public static function all(): array
    {
        if (self::$pages !== null) {
            return self::$pages;
        }
        $pages = [];
        $files = glob(APP_PATH . '/config/pages/*.php') ?: [];
        sort($files, SORT_STRING);
        foreach ($files as $file) {
            if (str_starts_with(basename($file), '_')) {
                continue;
            }
            try {
                $def = require $file;
            } catch (\Throwable $e) {
                Log::error('Page registry file failed: ' . basename($file) . ' - ' . $e->getMessage());
                continue;
            }
            if (!is_array($def) || empty($def['slug'])) {
                Log::warning('Page registry file ignored (no slug): ' . basename($file));
                continue;
            }
            $n = self::normalisePage($def, basename($file, '.php'));
            $pages[$n['slug']] = $n;
        }
        uasort($pages, static fn(array $a, array $b): int => [$a['nav']['order'], $a['title']] <=> [$b['nav']['order'], $b['title']]);
        return self::$pages = $pages;
    }

    public static function get(string $slug): ?array
    {
        return self::all()[$slug] ?? null;
    }

    public static function has(string $slug): bool
    {
        return isset(self::all()[$slug]);
    }

    /** @return array<string,array<string,mixed>> */
    public static function sections(string $slug): array
    {
        return self::get($slug)['sections'] ?? [];
    }

    /** @return array<string,array<string,mixed>> flat 'section.field' => field definition */
    public static function fields(string $slug): array
    {
        $out = [];
        foreach (self::sections($slug) as $skey => $section) {
            foreach ($section['fields'] as $fkey => $field) {
                $out[$skey . '.' . $fkey] = $field + ['section' => $skey];
            }
        }
        return $out;
    }

    public static function field(string $slug, string $blockKey): ?array
    {
        return self::fields($slug)[$blockKey] ?? null;
    }

    /** @return array<string,mixed> */
    public static function defaults(string $slug): array
    {
        $out = [];
        foreach (self::fields($slug) as $key => $field) {
            $out[$key] = $field['default'];
        }
        return $out;
    }

    public static function sectionDefaultVisible(string $slug, string $sectionKey): bool
    {
        return (bool) (self::sections($slug)[$sectionKey]['default_visible'] ?? true);
    }

    public static function flush(): void
    {
        self::$pages = null;
    }

    // ---------------------------------------------------------------- normalisation

    private static function normalisePage(array $d, string $fileSlug): array
    {
        $slug = (string) $d['slug'];
        $nav = (array) ($d['nav'] ?? []);
        $seo = (array) ($d['seo'] ?? []);
        $title = (string) ($d['title'] ?? ucfirst($slug));
        $sections = [];
        foreach ((array) ($d['sections'] ?? []) as $s) {
            if (empty($s['key'])) {
                continue;
            }
            $skey = (string) $s['key'];
            $fields = [];
            foreach ((array) ($s['fields'] ?? []) as $f) {
                if (empty($f['key'])) {
                    continue;
                }
                $fields[(string) $f['key']] = self::normaliseField($f);
            }
            $sections[$skey] = [
                'key'             => $skey,
                'label'           => (string) ($s['label'] ?? ucfirst($skey)),
                'help'            => (string) ($s['help'] ?? ''),
                'toggle'          => (bool) ($s['toggle'] ?? false),
                'default_visible' => (bool) ($s['default_visible'] ?? true),
                'fields'          => $fields,
            ];
        }
        return [
            'slug'     => $slug,
            'title'    => $title,
            'virtual'  => (bool) ($d['virtual'] ?? false),
            'route'    => (bool) ($d['virtual'] ?? false) ? '' : '/' . trim((string) ($d['route'] ?? ($slug === 'home' ? '/' : '/' . $slug)), '/'),
            'template' => (string) ($d['template'] ?? 'default'),
            'help'     => (string) ($d['help'] ?? ''),
            'nav'      => [
                'label'    => (string) ($nav['label'] ?? $title),
                'order'    => (int) ($nav['order'] ?? 500),
                'header'   => (bool) ($nav['header'] ?? false),
                'footer'   => (bool) ($nav['footer'] ?? false),
                'parent'   => isset($nav['parent']) && $nav['parent'] !== '' ? (string) $nav['parent'] : null,
                'button'   => (bool) ($nav['button'] ?? false),
                'requires' => isset($nav['requires']) && is_array($nav['requires']) ? $nav['requires'] : null,
            ],
            'seo'      => [
                'title'       => (string) ($seo['title'] ?? $title),
                'description' => (string) ($seo['description'] ?? ''),
                'og_image'    => (string) ($seo['og_image'] ?? ''),
            ],
            'sections' => $sections,
            'file'     => $fileSlug,
        ];
    }

    private static function normaliseField(array $f): array
    {
        $type = in_array($f['type'] ?? 'text', self::TYPES, true) ? (string) ($f['type'] ?? 'text') : 'text';
        $key = (string) $f['key'];
        $out = [
            'key'     => $key,
            'label'   => (string) ($f['label'] ?? ucfirst(str_replace('_', ' ', $key))),
            'type'    => $type,
            'help'    => (string) ($f['help'] ?? ''),
            'default' => $f['default'] ?? self::emptyFor($type),
        ];
        foreach (['maxlength', 'rows', 'min', 'max', 'step', 'placeholder', 'alt', 'item_label', 'add_label', 'max_rows', 'min_rows', 'required', 'filter'] as $opt) {
            if (array_key_exists($opt, $f)) {
                $out[$opt] = $f[$opt];
            }
        }
        if ($type === 'select') {
            $opts = [];
            foreach ((array) ($f['options'] ?? []) as $k => $v) {
                $opts[is_int($k) && !is_array($v) ? (string) $v : (string) $k] = (string) $v;
            }
            $out['options'] = $opts;
        }
        if ($type === 'repeater') {
            $subs = [];
            foreach ((array) ($f['fields'] ?? []) as $sf) {
                if (!empty($sf['key'])) {
                    $sub = self::normaliseField($sf);
                    if ($sub['type'] !== 'repeater') {       // one level of nesting only
                        $subs[(string) $sf['key']] = $sub;
                    }
                }
            }
            $out['fields'] = $subs;
            $out['default'] = is_array($out['default']) ? array_values($out['default']) : [];
        }
        if ($type === 'boolean') {
            $out['default'] = (bool) $out['default'];
        }
        return $out;
    }

    private static function emptyFor(string $type): mixed
    {
        return match ($type) {
            'repeater' => [],
            'boolean'  => false,
            default    => '',
        };
    }
}
