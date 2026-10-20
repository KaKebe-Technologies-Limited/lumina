<?php
declare(strict_types=1);

namespace App\Services\Shop;

use App\Core\Database;
use App\Models\ShopOrder;

/**
 * Numbers for the admin: sales summary, revenue by method, units by product, low-stock alerts, new orders. Read only; all amounts in minor units.
 * "Revenue" = orders whose payment_status is paid and that are not cancelled / refunded (a paid order that was then cancelled is money waiting to be refunded, not revenue). Period filters are dates (Y-m-d) on the order date.
 */
final class ShopStats
{
    /**
     * @return array{currency:string,orders:int,units:int,revenue:int,outstanding:int,average:int,by_status:array<string,int>,by_payment:array<string,int>,
     *   revenue_by_method:list<array{method:string,orders:int,amount:int}>,units_by_product:list<array{name:string,units:int,amount:int}>,
     *   new_orders:int,review:int}
     */
    public static function summary(?string $from = null, ?string $to = null): array
    {
        [$where, $p] = self::range($from, $to);
        $byStatus = array_fill_keys(ShopOrder::STATUSES, 0);
        foreach (Database::all("SELECT status, COUNT(*) AS n FROM shop_orders o WHERE {$where} GROUP BY status", $p) as $r) {
            $byStatus[(string) $r['status']] = (int) $r['n'];
        }
        $byPay = array_fill_keys(ShopOrder::PAYMENT_STATUSES, 0);
        foreach (Database::all("SELECT payment_status, COUNT(*) AS n FROM shop_orders o WHERE {$where} GROUP BY payment_status", $p) as $r) {
            $byPay[(string) $r['payment_status']] = (int) $r['n'];
        }
        $methods = [];
        $revenue = 0;
        foreach (Database::all("SELECT payment_method, COUNT(*) AS n, SUM(total) AS amt FROM shop_orders o WHERE {$where} AND payment_status = 'paid' AND status NOT IN ('cancelled','refunded') GROUP BY payment_method ORDER BY amt DESC", $p) as $r) {
            $amt = Money::fromDb($r['amt']);
            $revenue += $amt;
            $methods[] = ['method' => (string) $r['payment_method'], 'orders' => (int) $r['n'], 'amount' => $amt];
        }
        $outstanding = Money::fromDb(Database::value(
            "SELECT COALESCE(SUM(total), 0) FROM shop_orders o WHERE {$where} AND payment_status IN ('unpaid','pending','failed','review') AND status NOT IN ('cancelled','refunded')",
            $p,
            0
        ));
        $units = (int) Database::value("SELECT COALESCE(SUM(i.qty), 0) FROM shop_order_items i JOIN shop_orders o ON o.id = i.order_id WHERE {$where} AND o.status NOT IN ('cancelled','refunded')", $p, 0);
        $products = [];
        foreach (Database::all(
            "SELECT i.name, SUM(i.qty) AS u, SUM(i.line_total) AS amt FROM shop_order_items i JOIN shop_orders o ON o.id = i.order_id WHERE {$where} AND o.status NOT IN ('cancelled','refunded')
             GROUP BY COALESCE(i.product_id, 0), i.name ORDER BY u DESC, amt DESC LIMIT 10",
            $p
        ) as $r) {
            $products[] = ['name' => (string) $r['name'], 'units' => (int) $r['u'], 'amount' => Money::fromDb($r['amt'])];
        }
        $paidOrders = array_sum(array_column($methods, 'orders'));
        $total = array_sum($byStatus);
        return [
            'currency' => ShopSettings::currency(), 'orders' => $total, 'units' => $units, 'revenue' => $revenue, 'outstanding' => $outstanding,
            'average' => $paidOrders > 0 ? intdiv($revenue, $paidOrders) : 0, 'by_status' => $byStatus, 'by_payment' => $byPay,
            'revenue_by_method' => $methods, 'units_by_product' => $products, 'new_orders' => OrderService::newCount(), 'review' => $byPay['review'],
        ];
    }

    /**
     * Products that are sold out or running low (stock tracked, published or draft, not archived).
     * @return list<array{id:int,name:string,sku:string,qty:int,threshold:int,out:bool,status:string}>
     */
    public static function lowStock(int $limit = 20): array
    {
        $rows = Database::all(
            "SELECT id, name, sku, stock_qty, low_stock, status FROM shop_products WHERE track_stock = 1 AND status <> 'archived' AND stock_qty <= low_stock ORDER BY stock_qty ASC, name ASC LIMIT " . max(1, min(100, $limit))
        );
        return array_map(static fn(array $r): array => [
            'id' => (int) $r['id'], 'name' => (string) $r['name'], 'sku' => (string) ($r['sku'] ?? ''), 'qty' => max(0, (int) $r['stock_qty']),
            'threshold' => (int) $r['low_stock'], 'out' => (int) $r['stock_qty'] <= 0, 'status' => (string) $r['status'],
        ], $rows);
    }

    /** Orders and paid revenue per day for the last $days days. @return list<array{date:string,orders:int,revenue:int}> */
    public static function byDay(int $days = 30): array
    {
        $days = max(1, min(365, $days));
        $since = date('Y-m-d', strtotime('-' . ($days - 1) . ' days') ?: time());
        $map = [];
        foreach (Database::all(
            "SELECT DATE(created_at) AS d, COUNT(*) AS n, SUM(CASE WHEN payment_status = 'paid' AND status NOT IN ('cancelled','refunded') THEN total ELSE 0 END) AS rev FROM shop_orders WHERE created_at >= ? GROUP BY DATE(created_at)",
            [$since . ' 00:00:00']
        ) as $r) {
            $map[(string) $r['d']] = ['orders' => (int) $r['n'], 'revenue' => Money::fromDb($r['rev'])];
        }
        $out = [];
        for ($i = 0; $i < $days; $i++) {
            $d = date('Y-m-d', strtotime($since . ' +' . $i . ' days') ?: time());
            $out[] = ['date' => $d] + ($map[$d] ?? ['orders' => 0, 'revenue' => 0]);
        }
        return $out;
    }

    /** Newest orders for the dashboard card. @return list<array<string,mixed>> */
    public static function recent(int $limit = 5): array
    {
        return Database::all('SELECT id, order_number, status, payment_status, payment_method, total, currency, created_at FROM shop_orders ORDER BY id DESC LIMIT ' . max(1, min(50, $limit)));
    }

    /** @return array{0:string,1:list<string>} */
    private static function range(?string $from, ?string $to): array
    {
        $w = ['1 = 1'];
        $p = [];
        if ($from !== null && preg_match('/^\d{4}-\d{2}-\d{2}$/', $from) === 1) {
            $w[] = 'o.created_at >= ?';
            $p[] = $from . ' 00:00:00';
        }
        if ($to !== null && preg_match('/^\d{4}-\d{2}-\d{2}$/', $to) === 1) {
            $w[] = 'o.created_at <= ?';
            $p[] = $to . ' 23:59:59';
        }
        return [implode(' AND ', $w), $p];
    }
}
