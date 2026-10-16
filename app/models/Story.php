<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/** Table `stories`. Generic API: see App\Core\Model. */
final class Story extends Model
{
    protected static string $table = 'stories';
    protected static string $order = 'published_at DESC, id DESC';

    /** Stories safe to show publicly: active AND consent confirmed AND published. */
    public static function published(?int $limit = null): array
    {
        return self::where(['is_active' => 1, 'consent_confirmed' => 1, '@raw' => ['published_at IS NULL OR published_at <= NOW()', []]], 'published_at DESC, id DESC', $limit);
    }
}
