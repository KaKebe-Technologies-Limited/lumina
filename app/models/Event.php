<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/** Table `events`. Generic API: see App\Core\Model. */
final class Event extends Model
{
    protected static string $table = 'events';
    protected static string $order = 'start_at DESC';

    public static function upcoming(?int $limit = null): array
    {
        return self::where(['is_active' => 1, '@raw' => ['COALESCE(end_at, start_at) >= NOW()', []]], 'start_at ASC', $limit);
    }

    public static function past(?int $limit = null): array
    {
        return self::where(['is_active' => 1, '@raw' => ['COALESCE(end_at, start_at) < NOW()', []]], 'start_at DESC', $limit);
    }
}
