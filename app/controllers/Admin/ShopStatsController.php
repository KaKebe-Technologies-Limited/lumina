<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\AdminController;
use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Services\Shop\ShopStats;

/**
 * ADMIN SHOP: the overview (sales summary cards, revenue by method, units by product, low-stock alerts, newest orders) and the stats JSON.
 * OWNER: ADMIN agent (view app/views/admin/shop/overview.php). Contract: docs/SHOP.md. Allowed with shop.orders OR shop.manage; money figures need shop.orders.
 * The figures are ShopStats's; the view only lays them out.
 */
final class ShopStatsController extends AdminController
{
    public function index(Request $request): Response
    {
        if (!can('shop.orders') && !can('shop.manage')) {
            throw new HttpException(403, 'Forbidden');
        }
        $money = can('shop.orders');
        return $this->render('admin/shop/overview', [
            'summary'  => $money ? ShopStats::summary($request->string('from') ?: null, $request->string('to') ?: null) : null,
            'low'      => ShopStats::lowStock(20),
            'recent'   => $money ? ShopStats::recent(8) : [],
            'by_day'   => $money ? ShopStats::byDay(30) : [],
            'money'    => $money,
            'from'     => preg_match('/^\d{4}-\d{2}-\d{2}$/', $request->string('from')) === 1 ? $request->string('from') : '',
            'to'       => preg_match('/^\d{4}-\d{2}-\d{2}$/', $request->string('to')) === 1 ? $request->string('to') : '',
        ], ['title' => 'Shop', 'nav' => 'shop_overview', 'breadcrumbs' => [['Shop', null]]]);
    }

    public function statsJson(Request $request): Response
    {
        $this->authorize('shop.orders');
        return $this->json(['ok' => true, 'summary' => ShopStats::summary($request->string('from') ?: null, $request->string('to') ?: null), 'low_stock' => ShopStats::lowStock(20), 'by_day' => ShopStats::byDay(30)]);
    }
}
