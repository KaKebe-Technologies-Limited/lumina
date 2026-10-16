<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/** Table `login_attempts`. Generic API: see App\Core\Model. */
final class LoginAttempt extends Model
{
    protected static string $table = 'login_attempts';
    protected static string $order = 'id DESC';
}
