<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/** Table `stats`. Generic API: see App\Core\Model. */
final class Stat extends Model
{
    protected static string $table = 'stats';
    protected static string $order = 'sort_order ASC, id ASC';

    /** Active stats for a group (default 'home'), in display order. */
    public static function forGroup(string $group = 'home'): array
    {
        return self::where(['group_key' => $group, 'is_active' => 1], 'sort_order ASC, id ASC');
    }
}
