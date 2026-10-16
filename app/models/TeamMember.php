<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/** Table `team_members`. Generic API: see App\Core\Model. */
final class TeamMember extends Model
{
    protected static string $table = 'team_members';
    protected static string $order = 'sort_order ASC, id ASC';

    public const GROUPS = ['board' => 'Board of Directors', 'staff' => 'Staff', 'advisor' => 'Advisors', 'volunteer' => 'Volunteers'];

    /** Active members of one group (board|staff|advisor|volunteer) or all groups when null. */
    public static function active(?string $group = null): array
    {
        return self::where($group !== null ? ['is_active' => 1, 'team_group' => $group] : ['is_active' => 1], 'sort_order ASC, id ASC');
    }
}
