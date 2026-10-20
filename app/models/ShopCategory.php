<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/** Table `shop_categories`. Generic API: see App\Core\Model. Rules: docs/SHOP.md. */
final class ShopCategory extends Model
{
    protected static string $table = 'shop_categories';
    protected static string $order = 'sort_order ASC, id ASC';
}
