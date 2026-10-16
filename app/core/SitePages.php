<?php
declare(strict_types=1);

namespace App\Core;

/**
 * The effective list of public pages = registry pages (code defaults) overlaid with the `pages` table (owner edits)
 * + custom pages (rows with type='custom'). Cached per request. Works with an empty/missing database (registry defaults only).
 *
 *   SitePages::all()                  // slug => resolved page (see keys below)
 *   SitePages::get('about')           // resolved page or null
 *   SitePages::isPublished('about')   // unknown slugs count as published
 *   SitePages::meta('about', [...])   // SEO array used by layouts (helper: page_meta())
 *   SitePages::sync()                 // insert missing pages rows for every registry page; returns number created
 *   SitePages::flush()
 *
 * Resolved page keys: id, slug, type (registry|custom), title, route ('/about'), url (full), is_published, show_in_nav,
 * show_in_footer, nav_label, nav_order, parent_slug, button (bool, header CTA), requires (array|null), seo_title,
 * seo_description, og_image, template, body, subtitle, hero_image, hero_image_alt, in_db, registry (bool).
 */
final class SitePages
{
    /** @var array<string,array<string,mixed>>|null */
    private static ?array $all = null;
    /** @var array<string,bool> */
    private static array $requirementCache = [];

    /** @return array<string,array<string,mixed>> */
    public static function all(): array
    {
        if (self::$all !== null) {
            return self::$all;
        }
        $rows = [];
        try {
            foreach (Database::all('SELECT * FROM pages') as $r) {
                $rows[(string) $r['slug']] = $r;
            }
        } catch (\Throwable) {
            // DB/table missing: registry defaults only
        }

        $out = [];
        foreach (PageRegistry::all() as $slug => $reg) {
            $r = $rows[$slug] ?? null;
            $out[$slug] = [
                'id'             => $r ? (int) $r['id'] : null,
                'slug'           => $slug,
                'type'           => 'registry',
                'title'          => $r && (string) $r['title'] !== '' ? (string) $r['title'] : $reg['title'],
                'route'          => $reg['route'],
                'virtual'        => $reg['virtual'],
                'is_published'   => $reg['virtual'] ? true : ($r ? (bool) $r['is_published'] : true),
                'show_in_nav'    => $reg['virtual'] ? false : ($r ? (bool) $r['show_in_nav'] : $reg['nav']['header']),
                'show_in_footer' => $reg['virtual'] ? false : ($r ? (bool) $r['show_in_footer'] : $reg['nav']['footer']),
                'nav_label'      => $r && (string) $r['nav_label'] !== '' ? (string) $r['nav_label'] : $reg['nav']['label'],
                'nav_order'      => $r ? (int) $r['nav_order'] : $reg['nav']['order'],
                'parent_slug'    => $r ? ($r['parent_slug'] !== null && $r['parent_slug'] !== '' ? (string) $r['parent_slug'] : null) : $reg['nav']['parent'],
                'button'         => $reg['nav']['button'],
                'requires'       => $reg['nav']['requires'],
                'seo_title'      => $r ? (string) ($r['seo_title'] ?? '') : '',
                'seo_description' => $r ? (string) ($r['seo_description'] ?? '') : '',
                'og_image'       => $r ? (string) ($r['og_image'] ?? '') : '',
                'template'       => $r && (string) ($r['template'] ?? '') !== '' ? (string) $r['template'] : $reg['template'],
                'body'           => '',
                'subtitle'       => '',
                'hero_image'     => '',
                'hero_image_alt' => '',
                'in_db'          => $r !== null,
                'registry'       => true,
            ];
        }
        foreach ($rows as $slug => $r) {
            if ((string) $r['type'] !== 'custom' || isset($out[$slug])) {
                continue;
            }
            $out[$slug] = [
                'id'             => (int) $r['id'],
                'slug'           => $slug,
                'type'           => 'custom',
                'title'          => (string) $r['title'],
                'route'          => '/' . $slug,
                'virtual'        => false,
                'is_published'   => (bool) $r['is_published'],
                'show_in_nav'    => (bool) $r['show_in_nav'],
                'show_in_footer' => (bool) $r['show_in_footer'],
                'nav_label'      => (string) ($r['nav_label'] ?? '') !== '' ? (string) $r['nav_label'] : (string) $r['title'],
                'nav_order'      => (int) $r['nav_order'],
                'parent_slug'    => $r['parent_slug'] !== null && $r['parent_slug'] !== '' ? (string) $r['parent_slug'] : null,
                'button'         => false,
                'requires'       => null,
                'seo_title'      => (string) ($r['seo_title'] ?? ''),
                'seo_description' => (string) ($r['seo_description'] ?? ''),
                'og_image'       => (string) ($r['og_image'] ?? ''),
                'template'       => (string) ($r['template'] ?? '') !== '' ? (string) $r['template'] : 'default',
                'body'           => (string) ($r['body'] ?? ''),
                'subtitle'       => (string) ($r['subtitle'] ?? ''),
                'hero_image'     => (string) ($r['hero_image'] ?? ''),
                'hero_image_alt' => (string) ($r['hero_image_alt'] ?? ''),
                'in_db'          => true,
                'registry'       => false,
            ];
        }
        foreach ($out as $slug => &$p) {
            $p['url'] = $p['virtual'] ? '' : url(ltrim($p['route'], '/'));
        }
        unset($p);
        return self::$all = $out;
    }

    public static function get(string $slug): ?array
    {
        return self::all()[$slug] ?? null;
    }

    public static function isPublished(string $slug): bool
    {
        $p = self::get($slug);
        return $p === null ? true : (bool) $p['is_published'];
    }

    /** Does a nav 'requires' rule hold (e.g. at least one active team member exists)? */
    public static function requirementMet(?array $requires): bool
    {
        if ($requires === null || empty($requires['table'])) {
            return true;
        }
        $table = (string) $requires['table'];
        $where = trim((string) ($requires['where'] ?? '1=1'));
        $key = $table . '|' . $where;
        if (!isset(self::$requirementCache[$key])) {
            try {
                self::$requirementCache[$key] = Database::one('SELECT 1 AS x FROM ' . Database::ident($table) . ' WHERE ' . $where . ' LIMIT 1') !== null;
            } catch (\Throwable) {
                self::$requirementCache[$key] = false;
            }
        }
        return self::$requirementCache[$key];
    }

    /**
     * SEO/meta for a page. $override keys: title, description, og_image (path or URL), og_type, robots, canonical (path or URL).
     * @return array{title:string,full_title:string,description:string,og_image:string,og_type:string,robots:string,canonical:string,site_name:string}
     */
    public static function meta(string $slug, array $override = []): array
    {
        $p = $slug !== '' ? self::get($slug) : null;
        $reg = $slug !== '' ? PageRegistry::get($slug) : null;
        $site = (string) setting('site_name', 'Lumina Africa Foundation');

        $title = (string) ($override['title'] ?? '');
        if ($title === '' && $p !== null) {
            $title = $p['seo_title'] !== '' ? $p['seo_title'] : ($reg['seo']['title'] ?? '');
            if ($title === '') {
                $title = $p['title'];
            }
        }
        if ($title === '') {
            $title = $site;
        }
        $desc = (string) ($override['description'] ?? '');
        if ($desc === '' && $p !== null) {
            $desc = $p['seo_description'] !== '' ? $p['seo_description'] : ($reg['seo']['description'] ?? '');
        }
        if ($desc === '') {
            $desc = (string) setting('meta_description', '');
        }
        $img = (string) ($override['og_image'] ?? '');
        if ($img === '' && $p !== null) {
            $img = $p['og_image'] !== '' ? $p['og_image'] : ($reg['seo']['og_image'] ?? '');
        }
        if ($img === '') {
            $img = (string) setting('og_image', '');
        }
        if ($img === '') {
            $img = (string) setting('logo_path', 'assets/img/brand/logo-512.png');
        }

        $canonical = (string) ($override['canonical'] ?? '');
        if ($canonical === '') {
            $path = $p !== null ? $p['route'] : (Request::current()?->path() ?? '/');
            if (isset($override['title']) || $p === null) {
                $path = Request::current()?->path() ?? $path;
            }
            $canonical = abs_url(ltrim($path, '/'));
            // page 2, 3... of a list are different documents: each canonicalises to itself, not to page 1
            $pageNo = Request::current()?->page() ?? 1;
            if ($pageNo > 1) {
                $canonical .= '?page=' . $pageNo;
            }
        } elseif (!preg_match('~^https?://~i', $canonical)) {
            $canonical = abs_url(ltrim($canonical, '/'));
        }

        $robots = (string) ($override['robots'] ?? '');
        if ($robots === '') {
            $robots = ($p !== null && !$p['is_published']) ? 'noindex,nofollow' : 'index,follow';
        }
        $isHome = $slug === 'home';
        $full = stripos($title, $site) !== false ? $title : $title . ' | ' . $site;

        return [
            'title'       => $title,
            'full_title'  => $full,
            'description' => excerpt($desc, 175),
            'og_image'    => abs_url_for_image($img),
            'og_type'     => (string) ($override['og_type'] ?? 'website'),
            'robots'      => $robots,
            'canonical'   => $canonical,
            'site_name'   => $site,
        ];
    }

    /**
     * Make sure a `pages` row exists for every registry page (idempotent; never overwrites existing rows).
     * Called by bin/setup.php and by the admin Pages list. Returns the number of rows created.
     */
    public static function sync(): int
    {
        $created = [];
        foreach (PageRegistry::all() as $slug => $reg) {
            if ((int) Database::value('SELECT COUNT(*) FROM pages WHERE slug = ?', [$slug], 0) > 0) {
                continue;
            }
            Database::insert('pages', [
                'slug'           => $slug,
                'title'          => $reg['title'],
                'type'           => 'registry',
                'template'       => $reg['template'],
                'is_published'   => 1,
                'show_in_nav'    => $reg['nav']['header'] ? 1 : 0,
                'show_in_footer' => $reg['nav']['footer'] ? 1 : 0,
                'nav_label'      => $reg['nav']['label'],
                'nav_order'      => $reg['nav']['order'],
                'parent_slug'    => null,
            ]);
            $created[] = $slug;
        }
        // parents are linked in a second pass (the FK needs the parent row to exist first); only for rows created just now
        foreach ($created as $slug) {
            $parent = PageRegistry::get($slug)['nav']['parent'] ?? null;
            if ($parent !== null && (int) Database::value('SELECT COUNT(*) FROM pages WHERE slug = ?', [$parent], 0) > 0) {
                Database::execute('UPDATE pages SET parent_slug = ? WHERE slug = ?', [$parent, $slug]);
            }
        }
        self::flush();
        return count($created);
    }

    public static function flush(): void
    {
        self::$all = null;
        self::$requirementCache = [];
    }
}
