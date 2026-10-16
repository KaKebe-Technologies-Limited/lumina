<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/** Table `posts`. Generic API: see App\Core\Model. */
final class Post extends Model
{
    protected static string $table = 'posts';
    protected static string $order = 'published_at DESC, id DESC';

    public const TYPES = ['blog', 'news', 'press'];

    /** Published posts (published_at in the past), newest first. $type = blog|news|press|null (all types). */
    public static function published(?string $type = null, ?int $limit = null): array
    {
        $c = ['status' => 'published', '@raw' => ['published_at <= NOW()', []]];
        if ($type !== null) {
            $c['post_type'] = $type;
        }
        return self::where($c, 'published_at DESC, id DESC', $limit);
    }

    public static function publishedBySlug(string $slug): ?array
    {
        return self::first(['slug' => $slug, 'status' => 'published', '@raw' => ['published_at <= NOW()', []]]);
    }
}
