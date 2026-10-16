<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/** Table `faqs`. Generic API: see App\Core\Model. */
final class Faq extends Model
{
    protected static string $table = 'faqs';
    protected static string $order = 'sort_order ASC, id ASC';

    public static function forGroup(string $group): array
    {
        return self::where(['group_key' => $group, 'is_active' => 1], 'sort_order ASC, id ASC');
    }
}
