<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/** Table `users`. Generic API: see App\Core\Model. */
final class User extends Model
{
    protected static string $table = 'users';
    protected static string $order = 'name ASC';

    /** Built-in roles; the real list (with custom roles) is App\Core\Roles::slugs(). */
    public const ROLES = ['admin', 'editor'];

    public static function findByEmail(string $email): ?array
    {
        return self::findBy('email', strtolower(trim($email)));
    }
}
