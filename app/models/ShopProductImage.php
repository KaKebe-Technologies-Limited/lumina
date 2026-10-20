<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/** Table `shop_product_images`. Generic API: see App\Core\Model. Picture handling: App\Services\Shop\ProductImages. */
final class ShopProductImage extends Model
{
    protected static string $table = 'shop_product_images';
    protected static string $order = 'sort_order ASC, id ASC';
}
