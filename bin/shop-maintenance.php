<?php
/**
 * SHOP MAINTENANCE (CLI only; run from cron every 15-30 minutes):
 *
 *   /usr/bin/php /path/to/lumina/bin/shop-maintenance.php            (locally: /c/xampp/php/php.exe bin/shop-maintenance.php)
 *   --no-recheck                skip asking Flutterwave about online payments that are still waiting
 *
 * 1. RE-CHECK WAITING ONLINE PAYMENTS (runs FIRST): orders with a Flutterwave try that is more than 5 minutes old and still not settled are verified with the
 *    gateway - EVERY open try of the order, not only the latest - to catch a webhook that never arrived and a customer who closed the page. It includes orders that
 *    were CANCELLED in the last 7 days (an expired hold must never hide a payment: money that turns up for a cancelled order is flagged "review" for staff).
 *    At most 25 per run. Skipped when the gateway is not configured.
 * 2. RELEASE EXPIRED HOLDS: an order that is still "pending" and unpaid / waiting / failed after its hold_until time (booking and pay-on-pickup: shop_hold_days,
 *    mobile money / bank: shop_manual_hold_days, online payment: shop_gateway_hold_hours) is cancelled and its stock goes back on the shelf. This also
 *    abandons stale unpaid online-payment orders (after they were checked in step 1). Orders whose payment is "under review" never expire.
 *
 * Prints one summary line, never any customer data. Exit code 0 (1 only when the database is unreachable). A lock file prevents two runs overlapping.
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Database;
use App\Core\Log;
use App\Services\Shop\OnlinePayment;
use App\Services\Shop\OrderService;
use App\Services\Shop\PaymentMethods;

$lockFile = STORAGE_PATH . '/cache/shop-maintenance.lock';
@mkdir(dirname($lockFile), 0775, true);
$lock = fopen($lockFile, 'c');
if ($lock === false || !flock($lock, LOCK_EX | LOCK_NB)) {
    echo "Another shop-maintenance run is active.\n";
    exit(0);
}

try {
    if (!Database::tableExists('shop_orders')) {
        echo "The shop tables are not imported yet.\n";
        exit(0);
    }
    $checked = 0;
    $paid = 0;
    $flagged = 0;
    if (!in_array('--no-recheck', $argv, true) && PaymentMethods::enabled('flutterwave') && PaymentMethods::ready('flutterwave')) {
        $cut = date('Y-m-d H:i:s', time() - 300);
        $grace = date('Y-m-d H:i:s', time() - 7 * 86400);
        $ids = Database::all(
            "SELECT DISTINCT o.id FROM shop_orders o JOIN shop_payments p ON p.order_id = o.id AND p.method = 'flutterwave' AND p.status = 'pending' AND p.created_at < ?
             WHERE o.payment_method = 'flutterwave' AND o.payment_status IN ('pending','failed') AND (o.status = 'pending' OR (o.status = 'cancelled' AND o.updated_at > ?))
             ORDER BY o.id ASC LIMIT 25",
            [$cut, $grace]
        );
        foreach ($ids as $r) {
            $res = OnlinePayment::recheck((int) $r['id']);
            $checked++;
            if ($res['state'] === 'paid') {
                $paid++;
            } elseif ($res['state'] === 'review') {
                $flagged++;
            }
        }
    }
    $released = OrderService::releaseExpiredHolds(200, 10);
    OrderService::flushCounts();
    echo "shop-maintenance: re-checked {$checked} online payment(s), {$paid} newly paid, {$flagged} flagged for review; released {$released} expired order(s).\n";
    if ($released > 0 || $paid > 0 || $flagged > 0) {
        Log::info('shop-maintenance', ['released' => $released, 'rechecked' => $checked, 'paid' => $paid, 'review' => $flagged]);
    }
} catch (Throwable $e) {
    fwrite(STDERR, 'shop-maintenance failed: ' . get_class($e) . "\n");
    Log::error('shop-maintenance failed', ['error' => get_class($e)]);
    exit(1);
} finally {
    flock($lock, LOCK_UN);
}
