<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/** Table `shop_order_items`. Generic API: see App\Core\Model. Snapshot of what was bought (name, options, unit price). */
final class ShopOrderItem extends Model
{
    protected static string $table = 'shop_order_items';
    protected static string $order = 'id ASC';
}
