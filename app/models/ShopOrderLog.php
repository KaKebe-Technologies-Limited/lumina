<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/** Table `shop_order_log`. Generic API: see App\Core\Model. The order timeline (no customer data). */
final class ShopOrderLog extends Model
{
    protected static string $table = 'shop_order_log';
    protected static string $order = 'id ASC';
}
