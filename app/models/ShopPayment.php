<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/** Table `shop_payments`. Generic API: see App\Core\Model. The payment ledger of an order: App\Services\Shop\PaymentService. */
final class ShopPayment extends Model
{
    protected static string $table = 'shop_payments';
    protected static string $order = 'id ASC';

    public const STATUSES = ['pending', 'success', 'failed', 'review', 'refunded', 'duplicate', 'superseded'];
    public const SOURCES = ['customer', 'staff', 'gateway', 'webhook'];
}
