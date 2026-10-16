<?php
declare(strict_types=1);

namespace App\Controllers\Admin\Support;

use App\Core\Database;
use App\Core\Uploader;

/**
 * Tidies up uploaded images that an edit made unused (OWNER: B3 admin-core).
 * Image fields upload immediately (AJAX), so replacing/removing a picture leaves the old file behind. After a save the
 * controller passes the paths that disappeared; this class deletes a file ONLY when it is a real upload (never a bundled
 * assets/... image or an external URL) and no other record still references it.
 */
final class MediaCleaner
{
    /** table => image/html columns that may reference an upload path */
    private const REFERENCES = [
        'content_blocks'  => ['value'],
        'pages'           => ['hero_image', 'og_image', 'body'],
        'settings'        => ['setting_value'],
        'programs'        => ['image', 'body'],
        'projects'        => ['image', 'body'],
        'posts'           => ['cover_image', 'body'],
        'activities'      => ['image', 'body'],
        'gallery_images'  => ['image'],
        'team_members'    => ['photo'],
        'partners'        => ['logo'],
        'events'          => ['image', 'body'],
        'stories'         => ['image', 'body'],
    ];

    /** Is this a stored path that points at a file in uploads/ ("2026/10/abc.jpg")? */
    public static function isUpload(?string $path): bool
    {
        return (bool) preg_match('~^\d{4}/\d{2}/[A-Za-z0-9_\-]+\.(?:jpe?g|png|gif|webp)$~i', trim((string) $path));
    }

    /**
     * Every upload path found in a stored content-block value (plain image path, or a repeater's JSON rows).
     * @return list<string>
     */
    public static function pathsIn(string $stored): array
    {
        if ($stored === '') {
            return [];
        }
        preg_match_all('~\d{4}/\d{2}/[A-Za-z0-9_\-]+\.(?:jpe?g|png|gif|webp)~i', str_replace('\\/', '/', $stored), $m);
        return array_values(array_unique($m[0] ?? []));
    }

    /** All upload paths currently stored in a page's content blocks. @return list<string> */
    public static function forPage(string $slug): array
    {
        $paths = [];
        foreach (Database::all('SELECT value FROM content_blocks WHERE page_slug = ?', [$slug]) as $r) {
            foreach (self::pathsIn((string) ($r['value'] ?? '')) as $p) {
                $paths[$p] = true;
            }
        }
        return array_keys($paths);
    }

    /** Delete the given uploads when nothing references them any more. Returns how many files were removed. */
    public static function purge(array $candidates): int
    {
        $removed = 0;
        foreach (array_unique($candidates) as $path) {
            $path = (string) $path;
            if (!self::isUpload($path) || self::isReferenced($path)) {
                continue;
            }
            if (Uploader::delete($path)) {
                $removed++;
            }
        }
        return $removed;
    }

    public static function isReferenced(string $path): bool
    {
        $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $path) . '%';
        foreach (self::REFERENCES as $table => $columns) {
            foreach ($columns as $col) {
                try {
                    $hit = Database::one('SELECT 1 AS x FROM ' . Database::ident($table) . ' WHERE ' . Database::ident($col) . ' LIKE ? LIMIT 1', [$like]);
                } catch (\Throwable) {
                    continue;                          // table/column not present: nothing to protect there
                }
                if ($hit !== null) {
                    return true;
                }
            }
        }
        return false;
    }
}
