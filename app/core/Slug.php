<?php
declare(strict_types=1);

namespace App\Core;

/**
 * URL slugs.
 *   Slug::make('Digital Pathways for Youth')              -> 'digital-pathways-for-youth'
 *   Slug::unique('posts', 'My title')                      -> 'my-title' or 'my-title-2' (unique in posts.slug)
 *   Slug::unique('posts', 'My title', ignoreId: 5)         -> ignore row 5 (editing)
 *   Slug::unique('posts', 'x', column: 'slug', where: ['post_type' => 'news'])   // unique within a scope
 *   Slug::isReserved('admin')                              -> true (cannot be used for a custom page)
 */
final class Slug
{
    public static function make(string $text, int $max = 120): string
    {
        $text = trim($text);
        if ($text === '') {
            return '';
        }
        if (function_exists('iconv')) {
            $t = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
            if (is_string($t) && $t !== '') {
                $text = $t;
            }
        }
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9]+/', '-', $text) ?? '';
        $text = trim($text, '-');
        if (strlen($text) > $max) {
            $text = rtrim(substr($text, 0, $max), '-');
        }
        return $text;
    }

    public static function isReserved(string $slug): bool
    {
        return in_array(strtolower($slug), (array) Config::get('reserved_slugs', []), true);
    }

    /**
     * @param array<string,mixed> $where extra equality conditions that define the uniqueness scope
     */
    public static function unique(string $table, string $text, ?int $ignoreId = null, string $column = 'slug', array $where = [], string $fallback = 'item'): string
    {
        $base = self::make($text);
        if ($base === '') {
            $base = $fallback;
        }
        $candidate = $base;
        $i = 2;
        while (self::exists($table, $column, $candidate, $ignoreId, $where)) {
            $suffix = '-' . $i++;
            $candidate = substr($base, 0, 120 - strlen($suffix)) . $suffix;
        }
        return $candidate;
    }

    private static function exists(string $table, string $column, string $slug, ?int $ignoreId, array $where): bool
    {
        $sql = 'SELECT COUNT(*) FROM ' . Database::ident($table) . ' WHERE ' . Database::ident($column) . ' = ?';
        $params = [$slug];
        foreach ($where as $col => $val) {
            $sql .= ' AND ' . Database::ident((string) $col) . ' = ?';
            $params[] = $val;
        }
        if ($ignoreId !== null) {
            $sql .= ' AND id <> ?';
            $params[] = $ignoreId;
        }
        return (int) Database::value($sql, $params, 0) > 0;
    }
}
