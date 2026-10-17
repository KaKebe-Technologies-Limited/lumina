<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/** Table `member_payments`. Generic API: see App\Core\Model. Balance and receipt rules: App\Services\Membership\MembershipService. */
final class MemberPayment extends Model
{
    protected static string $table = 'member_payments';
    protected static string $order = 'id DESC';

    public const STATUSES = ['received', 'pending', 'waived'];
    public const PURPOSES = ['joining', 'membership', 'renewal', 'other'];
}
