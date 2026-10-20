<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/** Table `shop_products`. Generic API: see App\Core\Model. Visibility, price and stock rules: App\Services\Shop\ProductService. */
final class ShopProduct extends Model
{
    protected static string $table = 'shop_products';
    protected static string $order = 'sort_order ASC, id DESC';

    public const STATUSES = ['draft', 'published', 'archived'];
}
