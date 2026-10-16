<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/** Table `rate_limits`. Generic API: see App\Core\Model. */
final class RateLimitRow extends Model
{
    protected static string $table = 'rate_limits';
    protected static string $order = 'id DESC';
}
