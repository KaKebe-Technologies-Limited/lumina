<?php
declare(strict_types=1);

namespace App\Core;

/**
 * PUBLIC menus, built from the pages table (+ registry defaults) so that creating a page, reordering, renaming or toggling
 * visibility in the admin updates header and footer instantly. Entries whose registry nav.requires content rule is not met
 * (e.g. no active team members yet) are hidden automatically. Unpublished pages never appear.
 *
 *   foreach (Nav::header() as $item) {   // top-level items (children nested one level)
 *       $item['label'], $item['url'], $item['slug'], $item['active'], $item['button'], $item['children'] (list of the same shape)
 *   }
 *   Nav::footer()   // flat list of items with show_in_footer
 *   Nav::buttons()  // header items flagged nav.button (e.g. Donate) - Nav::header() excludes them
 */
final class Nav
{
    /** @var array<string,list<array<string,mixed>>>|null */
    private static ?array $cache = null;

    /** @return list<array<string,mixed>> */
    public static function header(): array
    {
        return self::build()['header'];
    }

    /** @return list<array<string,mixed>> */
    public static function buttons(): array
    {
        return self::build()['buttons'];
    }

    /** @return list<array<string,mixed>> */
    public static function footer(): array
    {
        return self::build()['footer'];
    }

    public static function flush(): void
    {
        self::$cache = null;
    }

    /** @return array{header:list<array<string,mixed>>,buttons:list<array<string,mixed>>,footer:list<array<string,mixed>>} */
    private static function build(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }
        $path = Request::current()?->path() ?? '/';
        $visible = [];
        foreach (SitePages::all() as $slug => $p) {
            if ($p['virtual'] || !$p['is_published'] || !SitePages::requirementMet($p['requires'])) {
                continue;
            }
            $visible[$slug] = $p;
        }
        uasort($visible, static fn(array $a, array $b): int => [$a['nav_order'], $a['nav_label']] <=> [$b['nav_order'], $b['nav_label']]);

        $item = static function (array $p) use ($path): array {
            $route = $p['route'];
            $active = $route === '/' ? $path === '/' : ($path === $route || str_starts_with($path, $route . '/'));
            return [
                'slug'     => $p['slug'],
                'label'    => $p['nav_label'],
                'url'      => $p['url'],
                'path'     => $route,
                'active'   => $active,
                'button'   => (bool) $p['button'],
                'children' => [],
            ];
        };

        $header = [];
        $buttons = [];
        $inHeader = array_filter($visible, static fn(array $p): bool => $p['show_in_nav']);
        foreach ($inHeader as $slug => $p) {
            $parent = $p['parent_slug'];
            if ($parent !== null && isset($inHeader[$parent]) && $parent !== $slug && $inHeader[$parent]['parent_slug'] === null) {
                continue;                   // rendered as a child below
            }
            $it = $item($p);
            foreach ($inHeader as $cslug => $c) {
                if ($c['parent_slug'] === $slug && $cslug !== $slug) {
                    $child = $item($c);
                    $it['children'][] = $child;
                    $it['active'] = $it['active'] || $child['active'];
                }
            }
            if ($it['button']) {
                $buttons[] = $it;
            } else {
                $header[] = $it;
            }
        }

        $footer = [];
        foreach ($visible as $p) {
            if ($p['show_in_footer']) {
                $footer[] = $item($p);
            }
        }
        return self::$cache = ['header' => $header, 'buttons' => $buttons, 'footer' => $footer];
    }
}
