<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/** Table `subscribers`. Generic API: see App\Core\Model. */
final class Subscriber extends Model
{
    protected static string $table = 'subscribers';
    protected static string $order = 'id DESC';

    public static function findByToken(string $token): ?array
    {
        return self::findBy('token', $token);
    }
}
