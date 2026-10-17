<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

/**
 * Table `membership_types` = the FEE CONFIGURATION (Admin > Membership types). Generic API: see App\Core\Model.
 * A type's fee only affects NEW applications (members keep their own fee snapshot). Display helpers live in
 * App\Services\Membership\MembershipService (money, period labels, "fee to be announced").
 */
final class MembershipType extends Model
{
    protected static string $table = 'membership_types';
    protected static string $order = 'sort_order ASC, id ASC';

    public const PERIODS = ['one_time', 'monthly', 'quarterly', 'annual', 'lifetime'];

    /** Types shown on the public site and in the form (is_active = 1). @return array<int,array<string,mixed>> */
    public static function active(): array
    {
        return self::where(['is_active' => 1], 'sort_order ASC, id ASC');
    }

    /** Number of members per type id (all statuses). @return array<int,int> */
    public static function memberCounts(): array
    {
        $out = [];
        foreach (Database::all('SELECT type_id, COUNT(*) AS n FROM members GROUP BY type_id') as $r) {
            $out[(int) $r['type_id']] = (int) $r['n'];
        }
        return $out;
    }
}
