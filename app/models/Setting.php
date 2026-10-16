<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/** Table `settings`. Generic API: see App\Core\Model. */
final class Setting extends Model
{
    protected static string $table = 'settings';
    protected static string $order = 'setting_key ASC';
}
