<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Table `members` = membership applications and members (one row per person per application). Generic API: see App\Core\Model.
 * PERSONAL DATA: never expose publicly, never write column values to logs or audit details (ids / refs only).
 * Business rules (numbers, transitions, expiry, fee snapshot...) live in App\Services\Membership\MembershipService: use it, do not re-implement.
 */
final class Member extends Model
{
    protected static string $table = 'members';
    protected static string $order = 'id DESC';

    public const STATUSES = ['pending', 'approved', 'rejected', 'suspended', 'expired', 'cancelled'];
    public const GENDERS = ['female', 'male', 'prefer_not_to_say'];
}
