<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/** Table `donations`. Generic API: see App\Core\Model. */
final class Donation extends Model
{
    protected static string $table = 'donations';
    protected static string $order = 'id DESC';

    public const METHODS = ['gofundme', 'bank', 'mobile_money', 'paypal', 'cash', 'other'];
    public const STATUSES = ['pledged', 'received', 'cancelled'];
}
