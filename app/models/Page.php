<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/** Table `pages`. Generic API: see App\Core\Model. */
final class Page extends Model
{
    protected static string $table = 'pages';
    protected static string $order = 'nav_order ASC, title ASC';

    public const TEMPLATES = ['default', 'wide', 'landing'];

    public static function bySlug(string $slug): ?array
    {
        return self::findBy('slug', $slug);
    }
}
