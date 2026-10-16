<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/** Table `activities`. Generic API: see App\Core\Model. */
final class Activity extends Model
{
    protected static string $table = 'activities';
    protected static string $order = 'activity_date DESC, id DESC';

    public static function active(?int $limit = null): array
    {
        return self::where(['is_active' => 1], 'activity_date DESC, id DESC', $limit);
    }
}
