<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/** Table `partners`. Generic API: see App\Core\Model. */
final class Partner extends Model
{
    protected static string $table = 'partners';
    protected static string $order = 'sort_order ASC, id ASC';

    public const TYPES = ['partner', 'funder', 'donor', 'collaborator'];

    public static function active(?string $type = null): array
    {
        return self::where($type !== null ? ['is_active' => 1, 'partner_type' => $type] : ['is_active' => 1], 'sort_order ASC, id ASC');
    }
}
