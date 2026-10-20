<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/** Table `shop_orders`. Generic API: see App\Core\Model. State machines and totals: App\Services\Shop\OrderService / PaymentService / PricingService. PERSONAL DATA. */
final class ShopOrder extends Model
{
    protected static string $table = 'shop_orders';
    protected static string $order = 'id DESC';

    public const STATUSES = ['pending', 'confirmed', 'processing', 'ready', 'shipped', 'completed', 'cancelled', 'refunded'];
    public const PAYMENT_STATUSES = ['unpaid', 'pending', 'paid', 'failed', 'refunded', 'review'];
    public const DELIVERY_METHODS = ['pickup', 'delivery'];
}
