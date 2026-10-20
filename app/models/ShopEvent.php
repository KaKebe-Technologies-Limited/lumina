<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/** Table `shop_events`. Generic API: see App\Core\Model. Gateway event log: kind, reference and status only. No payloads, no personal data. */
final class ShopEvent extends Model
{
    protected static string $table = 'shop_events';
    protected static string $order = 'id DESC';
}
