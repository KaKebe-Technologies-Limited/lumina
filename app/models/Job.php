<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/** Table `jobs`. Generic API: see App\Core\Model. */
final class Job extends Model
{
    protected static string $table = 'jobs';
    protected static string $order = 'sort_order ASC, id DESC';

    public const TYPES = ['full_time', 'part_time', 'contract', 'internship', 'volunteer'];

    /** Active vacancies whose closing date has not passed (or is unset). */
    public static function open(): array
    {
        return self::where(['is_active' => 1, '@raw' => ['closing_date IS NULL OR closing_date >= CURDATE()', []]], 'sort_order ASC, id DESC');
    }
}
