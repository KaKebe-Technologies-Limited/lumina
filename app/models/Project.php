<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/** Table `projects`. Generic API: see App\Core\Model. */
final class Project extends Model
{
    protected static string $table = 'projects';
    protected static string $order = 'sort_order ASC, id ASC';

    public const STATUSES = ['planned', 'ongoing', 'completed'];

    public static function active(?int $limit = null): array
    {
        return self::where(['is_active' => 1], 'sort_order ASC, id ASC', $limit);
    }
}
