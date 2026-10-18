<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Admin sidebar, auto-merged from app/config/admin_nav/*.php (one fragment per owning agent, so nobody edits a shared layout).
 *
 * Fragment format (the file returns ONE array):
 *   return [
 *     'key'   => 'content',            // unique area key
 *     'label' => 'Content',            // group heading in the sidebar ('' = no heading)
 *     'order' => 20,                   // group order (core=10, content=20, org=30, inbox=40)
 *     'items' => [
 *        ['key' => 'posts', 'label' => 'Posts', 'url' => 'admin/posts', 'icon' => 'file-text', 'can' => 'content.manage', 'order' => 10,
 *         'badge' => null],           // badge: null | int | string | callable(): int|string|null  (e.g. unread counts; 0/null hides it)
 *     ],
 *   ];
 *
 * An item is active when its key equals the 'nav' option given to render(), else when the request path starts with its url
 * (longest url wins). Items whose 'can' ability the user lacks are hidden; empty groups are hidden.
 */
final class AdminNav
{
    /** @return list<array{key:string,label:string,order:int,items:list<array<string,mixed>>}> */
    public static function groups(?string $activeKey = null): array
    {
        $path = ltrim(Request::current()?->path() ?? '/', '/');
        $groups = [];
        $merges = [];
        $files = glob(APP_PATH . '/config/admin_nav/*.php') ?: [];
        sort($files, SORT_STRING);
        foreach ($files as $file) {
            if (str_starts_with(basename($file), '_')) {
                continue;
            }
            try {
                $frag = require $file;
            } catch (\Throwable $e) {
                Log::error('admin_nav fragment failed: ' . basename($file) . ' - ' . $e->getMessage());
                continue;
            }
            if (!is_array($frag)) {
                continue;
            }
            $items = [];
            foreach ((array) ($frag['items'] ?? []) as $it) {
                if (empty($it['key']) || empty($it['url'])) {
                    continue;
                }
                if (!empty($it['can']) && !can((string) $it['can'])) {
                    continue;
                }
                $badge = $it['badge'] ?? null;
                if (is_callable($badge)) {
                    try {
                        $badge = $badge();
                    } catch (\Throwable) {
                        $badge = null;
                    }
                }
                $items[] = [
                    'key'    => (string) $it['key'],
                    'label'  => (string) ($it['label'] ?? $it['key']),
                    'url'    => url(trim((string) $it['url'], '/')),
                    'path'   => trim((string) $it['url'], '/'),
                    'icon'   => (string) ($it['icon'] ?? 'circle'),
                    'order'  => (int) ($it['order'] ?? 100),
                    'badge'  => ($badge === null || $badge === 0 || $badge === '0' || $badge === '') ? null : $badge,
                    'active' => false,
                ];
            }
            if ($items === []) {
                continue;
            }
            usort($items, static fn(array $a, array $b): int => [$a['order'], $a['label']] <=> [$b['order'], $b['label']]);
            if (!empty($frag['merge_into'])) {                      // items join another group (e.g. Meetings inside Calendar) instead of forming their own
                $merges[(string) $frag['merge_into']] = array_merge($merges[(string) $frag['merge_into']] ?? [], $items);
                continue;
            }
            $groups[] = [
                'key'   => (string) ($frag['key'] ?? basename($file, '.php')),
                'label' => (string) ($frag['label'] ?? ''),
                'order' => (int) ($frag['order'] ?? 100),
                'items' => $items,
            ];
        }
        foreach ($groups as $gi => $g) {
            if (isset($merges[$g['key']])) {
                $groups[$gi]['items'] = array_merge($g['items'], $merges[$g['key']]);
                usort($groups[$gi]['items'], static fn(array $a, array $b): int => [$a['order'], $a['label']] <=> [$b['order'], $b['label']]);
                unset($merges[$g['key']]);
            }
        }
        usort($groups, static fn(array $a, array $b): int => $a['order'] <=> $b['order']);

        // active item: explicit key, else longest path-prefix match
        $best = null;
        $bestLen = -1;
        foreach ($groups as $gi => $g) {
            foreach ($g['items'] as $ii => $it) {
                if ($activeKey !== null && $it['key'] === $activeKey) {
                    $best = [$gi, $ii];
                    $bestLen = PHP_INT_MAX;
                } elseif ($activeKey === null || $bestLen !== PHP_INT_MAX) {
                    $p = $it['path'];
                    if (($path === $p || str_starts_with($path, $p . '/')) && strlen($p) > $bestLen) {
                        $best = [$gi, $ii];
                        $bestLen = strlen($p);
                    }
                }
            }
        }
        if ($best !== null) {
            $groups[$best[0]]['items'][$best[1]]['active'] = true;
        }
        return $groups;
    }
}
