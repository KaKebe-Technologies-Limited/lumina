<?php
declare(strict_types=1);

namespace App\Services\Shop;

use App\Core\AuditLog;
use App\Core\Database;
use App\Core\Log;
use App\Models\ShopOrder;

/**
 * PAYMENTS of an order: the ledger (shop_payments), the payment-status machine, idempotent recording, the gateway settlement rules.
 *
 * PAYMENT STATUS MACHINE (order.payment_status):
 *   unpaid  -> pending paid failed review      pending -> unpaid paid failed review      failed -> pending unpaid paid review
 *   review  -> paid failed unpaid refunded     paid    -> refunded                       refunded: final
 *   unpaid = nothing yet | pending = waiting (gateway started, or the customer typed a transaction ID that staff must verify) | paid |
 *   failed = the gateway said no / staff marked it | review = something does not add up (amount or currency mismatch, money for a closed order): staff decide.
 *
 * LEDGER rows (shop_payments.status): pending (an attempt / a reference waiting to be verified) | success | failed | review | refunded | duplicate | superseded (a reference the customer typed that was never matched: the order was paid under another one)
 * (a second payment for an already paid order: nothing is changed, staff must refund it). One row per attempt; a pending row becomes success/failed.
 *
 * IDEMPOTENCY: markPaid() with a reference that already has a success row for the order does nothing and says duplicate => a webhook that is
 * delivered twice, a replay, or a return page reloaded ten times never pays (or e-mails) twice.
 *
 * RULES FOR MONEY FROM THE GATEWAY (settleGateway): paid ONLY when the verified transaction is "successful" AND its tx_ref belongs to this order
 * AND its amount >= the order total AND its currency equals the order currency. Anything else that looks like a payment sets the order to
 * `review` instead of paying it. Nothing in the return URL or the webhook body is trusted: the amounts come from the server-to-server verify call.
 * Staff can record several partial payments (the order becomes paid when their sum reaches the total).
 */
final class PaymentService
{
    public const FLOW = [
        'unpaid'   => ['pending', 'paid', 'failed', 'review'],
        'pending'  => ['unpaid', 'paid', 'failed', 'review'],
        'failed'   => ['pending', 'unpaid', 'paid', 'review'],
        'review'   => ['paid', 'failed', 'unpaid', 'refunded'],
        'paid'     => ['refunded'],
        'refunded' => [],
    ];
    public const MAX_GATEWAY_ATTEMPTS = 10;
    public const MAX_CUSTOMER_REFERENCES = 5;

    public static function canTransition(string $from, string $to): bool
    {
        return in_array($to, self::FLOW[$from] ?? [], true);
    }

    // ------------------------------------------------------------------------------------------------ ledger

    /** @return list<array<string,mixed>> oldest first */
    public static function ledger(int $orderId): array
    {
        return Database::all('SELECT p.*, u.name AS user_name FROM shop_payments p LEFT JOIN users u ON u.id = p.user_id WHERE p.order_id = ? ORDER BY p.id ASC', [$orderId]);
    }

    /** Money received so far (success rows, minor units). */
    public static function paidMinor(int $orderId): int
    {
        $rows = Database::all("SELECT amount FROM shop_payments WHERE order_id = ? AND status = 'success'", [$orderId]);
        return Money::add(0, ...array_map(static fn(array $r): int => Money::fromDb($r['amount']), $rows));
    }

    /** What is still to pay (minor units, never negative). */
    public static function balanceMinor(array $order): int
    {
        return max(0, Money::fromDb($order['total']) - self::paidMinor((int) $order['id']));
    }

    // ------------------------------------------------------------------------------------------------ gateway attempts

    /**
     * Begin a Flutterwave attempt: a NEW tx_ref (<order number>-<attempt>), a pending ledger row, the order becomes payment-pending and its hold is extended.
     * @return array{ok:bool,error?:string,tx_ref?:string,attempt?:int,amount_minor?:int,currency?:string,order?:array<string,mixed>}
     *   errors: not_found | not_payable | too_many_attempts
     */
    public static function startAttempt(int $orderId): array
    {
        try {
            $r = Database::transaction(function () use ($orderId): array {
                $o = Database::one('SELECT * FROM shop_orders WHERE id = ? FOR UPDATE', [$orderId]);
                if ($o === null) {
                    throw new ShopException('not_found');
                }
                if ($o['payment_method'] !== 'flutterwave' || $o['status'] !== 'pending' || !in_array($o['payment_status'], ['unpaid', 'pending', 'failed'], true)) {
                    throw new ShopException('not_payable');
                }
                if ((int) $o['gateway_attempts'] >= self::MAX_GATEWAY_ATTEMPTS) {
                    throw new ShopException('too_many_attempts');
                }
                $attempt = (int) $o['gateway_attempts'] + 1;
                $txRef = $o['order_number'] . '-' . $attempt;
                Database::execute(
                    "UPDATE shop_orders SET gateway_attempts = ?, gateway_tx_ref = ?, payment_status = 'pending', hold_until = ? WHERE id = ?",
                    [$attempt, $txRef, OrderService::holdUntil('flutterwave'), $orderId]
                );
                Database::insert('shop_payments', [
                    'order_id' => $orderId, 'method' => 'flutterwave', 'amount' => $o['total'], 'currency' => $o['currency'], 'reference' => $txRef,
                    'status' => 'pending', 'source' => 'gateway', 'meta' => 'Payment page opened (attempt ' . $attempt . ')',
                ]);
                OrderService::addLog($orderId, 'gateway', 'Online payment started (attempt ' . $attempt . ').');
                return ['tx_ref' => $txRef, 'attempt' => $attempt, 'amount_minor' => Money::fromDb($o['total']), 'currency' => (string) $o['currency']];
            });
        } catch (ShopException $e) {
            return ['ok' => false, 'error' => $e->errorCode];
        }
        self::recordEvent('payment_started', $r['tx_ref'], $orderId, 'started');
        OrderService::flushCounts();
        return ['ok' => true] + $r + ['order' => OrderService::find($orderId)];
    }

    /**
     * The order a tx_ref belongs to - or null. A tx_ref is "<order number>-<attempt>": the order must exist, the attempt must have been started
     * (1..gateway_attempts) AND a ledger row with exactly this reference must exist for that order. Anything else is not ours.
     */
    public static function orderForTxRef(string $txRef): ?array
    {
        if (preg_match('/^([A-Za-z0-9-]{6,40})-(\d{1,3})$/', $txRef, $m) !== 1) {
            return null;
        }
        $order = OrderService::findByNumber($m[1]);
        if ($order === null || (int) $m[2] < 1 || (int) $m[2] > (int) $order['gateway_attempts']) {
            return null;
        }
        $row = Database::one("SELECT id FROM shop_payments WHERE order_id = ? AND reference = ? AND method = 'flutterwave' LIMIT 1", [$order['id'], $txRef]);
        return $row === null ? null : $order;
    }

    /**
     * Apply a VERIFIED Flutterwave transaction (from FlutterwaveClient::verifyByReference / verifyById) to the order its tx_ref belongs to.
     * Used by the return page and by the webhook alike.
     * @param array{ok:bool,status:string,tx_ref:string,id:?string,amount_minor:?int,currency:string} $v FlutterwaveClient::normalise() output
     * @param string $source 'gateway' (customer came back) | 'webhook'
     * @return array{ok:bool,state:string,order?:array<string,mixed>}  state: paid | duplicate | failed | pending | review | unknown_order | verify_failed | ignored
     */
    public static function settleGateway(string $txRef, array $v, string $source = 'gateway'): array
    {
        $order = self::orderForTxRef($txRef);
        if ($order === null) {
            self::recordEvent('unknown_tx_ref', mb_substr($txRef, 0, 120), null, 'ignored');
            return ['ok' => false, 'state' => 'unknown_order'];
        }
        $oid = (int) $order['id'];
        if (empty($v['ok'])) {
            self::recordEvent('verify_failed', $txRef, $oid, 'error');
            return ['ok' => false, 'state' => 'verify_failed', 'order' => $order];
        }
        if (($v['tx_ref'] ?? '') !== $txRef) {
            self::recordEvent('mismatch', $txRef, $oid, 'tx_ref');
            self::markReview($oid, $txRef, 'The gateway answered for a different reference.', $source);
            return ['ok' => false, 'state' => 'review', 'order' => OrderService::find($oid)];
        }
        $status = strtolower((string) ($v['status'] ?? ''));
        if ($status === 'successful') {                                                    // the only value the documented verify answer uses for money received
            $res = self::markPaid($oid, [
                'method' => 'flutterwave', 'reference' => $txRef, 'amount' => $v['amount_minor'], 'currency' => (string) ($v['currency'] ?? ''), 'source' => $source,
                'gateway_id' => $v['id'] ?? null, 'meta' => 'Verified with Flutterwave',
            ]);
            $state = !empty($res['review']) ? 'review' : (!empty($res['duplicate']) ? 'duplicate' : 'paid');
            self::recordEvent($source === 'webhook' ? 'webhook_verified' : 'return_verified', $txRef, $oid, $state);
            return ['ok' => !empty($res['ok']), 'state' => $state, 'order' => $res['order'] ?? OrderService::find($oid)];
        }
        if (in_array($status, ['failed', 'cancelled'], true)) {
            if ((string) ($order['gateway_tx_ref'] ?? '') !== $txRef && (string) ($order['gateway_tx_ref'] ?? '') !== '') {
                // an EARLIER attempt failed while a newer one is running (or was paid): close only that attempt's ledger row, never the order's payment status
                self::closeAttempt($oid, $txRef, 'Payment ' . $status . ' at the gateway (an earlier try).', $source);
                self::recordEvent($source === 'webhook' ? 'webhook_verified' : 'return_verified', $txRef, $oid, 'superseded');
                return ['ok' => true, 'state' => 'superseded', 'order' => OrderService::find($oid)];
            }
            self::markFailed($oid, $txRef, 'Payment ' . $status . ' at the gateway.', $source);
            self::recordEvent($source === 'webhook' ? 'webhook_verified' : 'return_verified', $txRef, $oid, 'failed');
            return ['ok' => true, 'state' => 'failed', 'order' => OrderService::find($oid)];
        }
        self::recordEvent($source === 'webhook' ? 'webhook_verified' : 'return_verified', $txRef, $oid, 'pending');
        return ['ok' => true, 'state' => 'pending', 'order' => $order];
    }

    /** Close the pending ledger row of one gateway attempt as failed WITHOUT touching the order (used for an earlier try while a later one is running). */
    private static function closeAttempt(int $orderId, string $txRef, string $reason, string $source): void
    {
        Database::execute(
            "UPDATE shop_payments SET status = 'failed', source = ?, meta = ? WHERE order_id = ? AND reference = ? AND method = 'flutterwave' AND status = 'pending'",
            [in_array($source, ['gateway', 'webhook'], true) ? $source : 'gateway', mb_substr($reason, 0, 255), $orderId, $txRef]
        );
        OrderService::addLog($orderId, 'payment', 'An earlier online payment try failed at the gateway.');
    }

    /**
     * Every tx_ref of this order that may still turn into money: the ledger rows of its gateway attempts that are still pending, newest first, plus the latest
     * tx_ref when the order is not settled (so a re-check of a failed order still asks about its last try). Used by the re-check and the cron.
     * @return list<string>
     */
    public static function openGatewayRefs(int $orderId): array
    {
        $refs = array_map(
            static fn(array $r): string => (string) $r['reference'],
            Database::all("SELECT reference FROM shop_payments WHERE order_id = ? AND method = 'flutterwave' AND status = 'pending' AND reference IS NOT NULL ORDER BY id DESC LIMIT " . self::MAX_GATEWAY_ATTEMPTS, [$orderId])
        );
        $o = OrderService::find($orderId);
        if ($o !== null && (string) ($o['gateway_tx_ref'] ?? '') !== '' && !in_array($o['payment_status'], ['paid', 'refunded'], true) && !in_array($o['gateway_tx_ref'], $refs, true)) {
            $refs[] = (string) $o['gateway_tx_ref'];
        }
        return $refs;
    }

    /**
     * The money staff can still hand back on a CANCELLED order: what was received (success rows), or - for a payment that arrived after the order was closed and is
     * waiting in `review` - what the gateway reported. Minor units; 0 when there is nothing to refund.
     * @param array<string,mixed> $order
     */
    public static function refundableMinor(array $order): int
    {
        if ($order['status'] !== 'cancelled' || $order['payment_status'] === 'refunded') {
            return 0;
        }
        $paid = self::paidMinor((int) $order['id']);
        if ($paid > 0) {
            return $paid;
        }
        $rows = Database::all("SELECT amount FROM shop_payments WHERE order_id = ? AND status = 'review' AND amount > 0", [(int) $order['id']]);
        return Money::add(0, ...array_map(static fn(array $r): int => Money::fromDb($r['amount']), $rows));
    }

    // ------------------------------------------------------------------------------------------------ customer reference (manual methods)

    /**
     * The customer typed their mobile-money transaction ID / bank transfer reference: staff must VERIFY it. The order becomes payment-pending
     * and its hold is extended. Nothing is marked paid.
     * @return array{ok:bool,error?:string}  errors: not_found | invalid | not_allowed | too_many
     */
    public static function recordCustomerReference(int $orderId, string $reference): array
    {
        $reference = trim((string) preg_replace('/\s+/u', ' ', $reference));
        if (preg_match('/^[A-Za-z0-9][A-Za-z0-9 ._\/-]{3,59}$/', $reference) !== 1) {
            return ['ok' => false, 'error' => 'invalid'];
        }
        $stored = false;
        try {
            Database::transaction(function () use ($orderId, $reference, &$stored): void {
                $o = Database::one('SELECT * FROM shop_orders WHERE id = ? FOR UPDATE', [$orderId]);
                if ($o === null) {
                    throw new ShopException('not_found');
                }
                if (!in_array($o['payment_method'], ['momo', 'bank'], true) || $o['status'] !== 'pending' || !in_array($o['payment_status'], ['unpaid', 'pending', 'failed'], true)) {
                    throw new ShopException('not_allowed');
                }
                if (Database::one("SELECT id FROM shop_payments WHERE order_id = ? AND reference = ? AND source = 'customer'", [$orderId, $reference]) !== null) {
                    return;                                                            // the same reference again: nothing new (and no second e-mail to staff)
                }
                if ((int) Database::value("SELECT COUNT(*) FROM shop_payments WHERE order_id = ? AND source = 'customer'", [$orderId], 0) >= self::MAX_CUSTOMER_REFERENCES) {
                    throw new ShopException('too_many');
                }
                Database::insert('shop_payments', [
                    'order_id' => $orderId, 'method' => $o['payment_method'], 'amount' => $o['total'], 'currency' => $o['currency'], 'reference' => $reference,
                    'status' => 'pending', 'source' => 'customer', 'meta' => 'Reference entered by the customer - to be verified',
                ]);
                Database::execute(
                    "UPDATE shop_orders SET payment_reference = ?, payment_status = 'pending', hold_until = GREATEST(COALESCE(hold_until, '1970-01-01 00:00:00'), ?) WHERE id = ?",
                    [$reference, OrderService::holdUntil($o['payment_method']), $orderId]
                );
                OrderService::addLog($orderId, 'payment', 'The customer entered a payment reference (waiting for staff to verify).');
                $stored = true;
            });
        } catch (ShopException $e) {
            return ['ok' => false, 'error' => $e->errorCode];
        }
        OrderService::flushCounts();
        $order = OrderService::find($orderId);
        if ($order !== null && $stored) {
            try {
                ShopMailer::referenceSubmitted($order);
            } catch (\Throwable $e) {
                Log::error('Shop reference e-mail failed', ['order' => $order['order_number'], 'error' => $e::class]);
            }
        }
        return ['ok' => true];
    }

    // ------------------------------------------------------------------------------------------------ recording

    /**
     * Record a payment. IDEMPOTENT per (order, reference).
     * @param array{method?:string,reference?:?string,amount?:?int,currency?:string,source?:string,user_id?:?int,gateway_id?:?string,meta?:string,notify?:bool} $p
     *        amount in minor units; source: staff | gateway | webhook
     * @return array{ok:bool,error?:string,duplicate?:bool,review?:bool,paid?:bool,partial?:bool,order?:array<string,mixed>}
     *   errors: not_found | closed (order cancelled / refunded: staff only) | invalid_amount | currency | already_refunded
     */
    public static function markPaid(int $orderId, array $p): array
    {
        $source = in_array($p['source'] ?? '', ['staff', 'gateway', 'webhook'], true) ? (string) $p['source'] : 'staff';
        $online = $source !== 'staff';
        $reference = isset($p['reference']) && $p['reference'] !== null && trim((string) $p['reference']) !== '' ? mb_substr(trim((string) $p['reference']), 0, 120) : null;
        $method = mb_substr((string) ($p['method'] ?? 'cod'), 0, 30);
        $userId = isset($p['user_id']) ? (int) $p['user_id'] : null;
        $notify = ($p['notify'] ?? true) !== false;
        $out = ['ok' => true];
        try {
            $out = Database::transaction(function () use ($orderId, $p, $source, $online, $reference, $method, $userId): array {
                $o = Database::one('SELECT * FROM shop_orders WHERE id = ? FOR UPDATE', [$orderId]);
                if ($o === null) {
                    throw new ShopException('not_found');
                }
                $total = Money::fromDb($o['total']);
                $amount = isset($p['amount']) && $p['amount'] !== null ? (int) $p['amount'] : null;
                $currency = strtoupper((string) ($p['currency'] ?? $o['currency']));
                if (!$online && $reference !== null && Database::one("SELECT id FROM shop_payments WHERE reference = ? AND status = 'success' AND order_id <> ? LIMIT 1", [$reference, $orderId]) !== null) {
                    throw new ShopException('reference_used');                                  // one transaction ID cannot pay two orders (typo, reused ID or fraud): staff add a letter if it really did
                }
                $existing = $reference === null ? null : Database::one("SELECT * FROM shop_payments WHERE order_id = ? AND reference = ? AND status = 'success' LIMIT 1", [$orderId, $reference]);
                if ($existing !== null) {
                    return ['ok' => true, 'duplicate' => true];                                    // replay: nothing changes
                }
                if ($o['payment_status'] === 'refunded') {
                    throw new ShopException('already_refunded');
                }
                if (in_array($o['status'], ['cancelled', 'refunded'], true)) {
                    if (!$online) {
                        throw new ShopException('closed');
                    }
                    // money arrived for an order that was already closed: never hide it, never mark it paid
                    if (self::ledgerWrite($orderId, $o, $method, $amount ?? 0, $currency, $reference, 'review', $source, $p, 'Payment received for a closed order')) {
                        Database::execute("UPDATE shop_orders SET payment_status = 'review' WHERE id = ? AND payment_status NOT IN ('paid','refunded')", [$orderId]);
                        OrderService::addLog($orderId, 'payment', 'A payment arrived for a ' . $o['status'] . ' order. Check it and refund the customer if needed.');
                    }
                    return ['ok' => false, 'review' => true];
                }
                if ($online && ($amount === null || $amount <= 0 || $currency !== $o['currency'] || $amount < $total)) {
                    // the gateway says "paid" but not for this order's money: flag, do not pay
                    $why = $currency !== $o['currency'] ? 'currency' : 'amount';
                    if ($o['payment_status'] !== 'paid' && self::ledgerWrite($orderId, $o, $method, max(0, (int) $amount), $currency, $reference, 'review', $source, $p, 'Amount or currency does not match (' . $why . ')')) {
                        Database::execute("UPDATE shop_orders SET payment_status = 'review' WHERE id = ?", [$orderId]);
                        OrderService::addLog($orderId, 'payment', 'A payment was reported but its ' . $why . ' does not match the order. Check it with the gateway before releasing the goods.');
                    }
                    return ['ok' => false, 'review' => true];
                }
                if (!$online && ($amount === null || $amount <= 0)) {
                    throw new ShopException('invalid_amount');
                }
                if (!$online && $currency !== $o['currency']) {
                    throw new ShopException('currency');
                }
                if ($o['payment_status'] === 'paid') {
                    // already paid in full: this is a second payment
                    if (self::ledgerWrite($orderId, $o, $method, (int) $amount, $currency, $reference, 'duplicate', $source, $p, 'Second payment for an order that is already paid')) {
                        OrderService::addLog($orderId, 'payment', 'A second payment arrived for an order that is already paid. Refund it.');
                    }
                    return ['ok' => true, 'duplicate' => true];
                }
                self::ledgerWrite($orderId, $o, $method, (int) $amount, $currency, $reference, 'success', $source, $p, (string) ($p['meta'] ?? ''), $userId);
                $paid = self::paidMinor($orderId);
                $full = $paid >= $total;
                if ($full) {
                    // references the customer typed that were never matched (staff recorded the money under another reference, or the customer corrected a typo)
                    Database::execute(
                        "UPDATE shop_payments SET status = 'superseded', meta = 'The order was paid under another reference' WHERE order_id = ? AND source = 'customer' AND status = 'pending' AND (reference IS NULL OR reference <> ?)",
                        [$orderId, (string) $reference]
                    );
                    $autoConfirm = $o['status'] === 'pending' && ShopSettings::bool('shop_auto_confirm_paid');
                    Database::execute(
                        "UPDATE shop_orders SET payment_status = 'paid', paid_at = ?, hold_until = NULL, payment_reference = COALESCE(?, payment_reference)" . ($autoConfirm ? ", status = 'confirmed'" : '') . ' WHERE id = ?',
                        [date('Y-m-d H:i:s'), $reference, $orderId]
                    );
                    OrderService::addLog($orderId, 'payment', 'Payment received: ' . Money::format((int) $amount, (string) $o['currency']) . ' (' . $method . ', ' . $source . ').' . ($autoConfirm ? ' Order confirmed.' : ''), $userId);
                } else {
                    Database::execute("UPDATE shop_orders SET payment_status = 'pending', hold_until = NULL, payment_reference = COALESCE(?, payment_reference) WHERE id = ?", [$reference, $orderId]);
                    OrderService::addLog($orderId, 'payment', 'Part payment received: ' . Money::format((int) $amount, (string) $o['currency']) . '; ' . Money::format($total - $paid, (string) $o['currency']) . ' still to pay.', $userId);
                }
                return ['ok' => true, 'paid' => $full, 'partial' => !$full];
            });
        } catch (ShopException $e) {
            return ['ok' => false, 'error' => $e->errorCode];
        }
        OrderService::flushCounts();
        $order = OrderService::find($orderId);
        $out['order'] = $order;
        if ($online) {
            AuditLog::record('shop.payment', 'order', $order['order_number'] ?? $orderId, 'Online payment ' . (!empty($out['review']) ? 'flagged for review' : (!empty($out['duplicate']) ? 'repeated (ignored)' : 'recorded')), ['source' => $source, 'method' => $method]);
        }
        if (!empty($out['paid']) && $notify && $order !== null) {
            try {
                ShopMailer::paymentReceived($order, $source);
            } catch (\Throwable $e) {
                Log::error('Shop payment e-mail failed', ['order' => $order['order_number'], 'error' => $e::class]);
            }
        }
        return $out;
    }

    /** The gateway (or staff) says the payment failed. Never touches a paid / refunded order. Idempotent per reference. @return array{ok:bool,duplicate?:bool,order?:array<string,mixed>} */
    public static function markFailed(int $orderId, ?string $reference, string $reason = '', string $source = 'staff', ?int $userId = null): array
    {
        $source = in_array($source, ['staff', 'gateway', 'webhook'], true) ? $source : 'staff';
        $reference = $reference !== null && trim($reference) !== '' ? mb_substr(trim($reference), 0, 120) : null;
        $out = Database::transaction(function () use ($orderId, $reference, $reason, $source, $userId): array {
            $o = Database::one('SELECT * FROM shop_orders WHERE id = ? FOR UPDATE', [$orderId]);
            if ($o === null) {
                return ['ok' => false];
            }
            if (in_array($o['payment_status'], ['paid', 'refunded'], true)) {
                return ['ok' => true, 'duplicate' => true];
            }
            if ($reference !== null && Database::one("SELECT id FROM shop_payments WHERE order_id = ? AND reference = ? AND status = 'failed'", [$orderId, $reference]) !== null) {
                return ['ok' => true, 'duplicate' => true];
            }
            $pending = $reference === null ? null : Database::one("SELECT id FROM shop_payments WHERE order_id = ? AND reference = ? AND status = 'pending' ORDER BY id DESC LIMIT 1", [$orderId, $reference]);
            if ($pending !== null) {
                Database::execute("UPDATE shop_payments SET status = 'failed', source = ?, user_id = ?, meta = ? WHERE id = ?", [$source, $userId, mb_substr($reason, 0, 255), $pending['id']]);
            } else {
                Database::insert('shop_payments', ['order_id' => $orderId, 'method' => $o['payment_method'], 'amount' => $o['total'], 'currency' => $o['currency'], 'reference' => $reference,
                    'status' => 'failed', 'source' => $source, 'user_id' => $userId, 'meta' => mb_substr($reason, 0, 255)]);
            }
            Database::execute("UPDATE shop_orders SET payment_status = 'failed' WHERE id = ?", [$orderId]);
            OrderService::addLog($orderId, 'payment', 'Payment failed' . ($reason !== '' ? ': ' . mb_substr($reason, 0, 150) : '') . '.', $userId);
            return ['ok' => true];
        });
        OrderService::flushCounts();
        $out['order'] = OrderService::find($orderId);
        return $out;
    }

    /** Flag an order for staff attention. Never touches a paid / refunded order. */
    public static function markReview(int $orderId, ?string $reference, string $reason, string $source = 'gateway'): void
    {
        Database::transaction(function () use ($orderId, $reference, $reason, $source): void {
            $o = Database::one('SELECT * FROM shop_orders WHERE id = ? FOR UPDATE', [$orderId]);
            if ($o === null || in_array($o['payment_status'], ['paid', 'refunded'], true)) {
                return;
            }
            if (self::ledgerWrite($orderId, $o, (string) $o['payment_method'], 0, (string) $o['currency'], $reference, 'review', in_array($source, ['staff', 'gateway', 'webhook'], true) ? $source : 'gateway', [], mb_substr($reason, 0, 250))) {
                Database::execute("UPDATE shop_orders SET payment_status = 'review' WHERE id = ?", [$orderId]);
                OrderService::addLog($orderId, 'payment', 'Payment needs review: ' . mb_substr($reason, 0, 150));
            }
        });
        OrderService::flushCounts();
    }

    /**
     * Staff change the payment status by hand within the machine (unpaid / failed / pending). 'paid' goes through markPaid(); 'refunded' goes through
     * OrderService::setStatus('refunded') for a live order and through THIS method ('refunded') for a cancelled order that still holds money (see refundableMinor()).
     * @return array{ok:bool,error?:string}  errors: not_found | same | transition | nothing_to_refund
     */
    public static function staffSetStatus(int $orderId, string $to, ?int $userId = null, string $note = ''): array
    {
        if (!in_array($to, ['unpaid', 'failed', 'pending', 'refunded'], true)) {
            return ['ok' => false, 'error' => 'transition'];
        }
        try {
            Database::transaction(function () use ($orderId, $to, $userId, $note): void {
                $o = Database::one('SELECT * FROM shop_orders WHERE id = ? FOR UPDATE', [$orderId]);
                if ($o === null) {
                    throw new ShopException('not_found');
                }
                if ($o['payment_status'] === $to) {
                    throw new ShopException('same');
                }
                if ($to === 'refunded') {
                    // money that is held for a CANCELLED order (it was paid before the cancel, part of it was paid, or it arrived after the cancel and sits in `review`):
                    // the order status cannot move to "refunded" any more, so staff record the refund here, once they have returned the money outside this site
                    $refund = self::refundableMinor($o);
                    if ($refund <= 0) {
                        throw new ShopException($o['status'] === 'cancelled' ? 'nothing_to_refund' : 'transition');
                    }
                    Database::insert('shop_payments', [
                        'order_id' => $orderId, 'method' => $o['payment_method'], 'amount' => Money::toDb($refund), 'currency' => $o['currency'], 'reference' => $o['payment_reference'],
                        'status' => 'refunded', 'source' => 'staff', 'user_id' => $userId,
                        'meta' => mb_substr($note !== '' ? $note : 'Refund recorded by staff for a cancelled order (the money itself is returned outside this site)', 0, 255),
                    ]);
                    Database::execute("UPDATE shop_orders SET payment_status = 'refunded' WHERE id = ?", [$orderId]);
                    OrderService::addLog($orderId, 'payment', 'Refund recorded: ' . Money::format($refund, (string) $o['currency']) . '. Return the money to the customer outside this site.', $userId);
                    return;
                }
                if (!self::canTransition((string) $o['payment_status'], $to)) {
                    throw new ShopException('transition');
                }
                Database::execute('UPDATE shop_orders SET payment_status = ? WHERE id = ?', [$to, $orderId]);
                if ($to === 'failed') {
                    Database::insert('shop_payments', ['order_id' => $orderId, 'method' => $o['payment_method'], 'amount' => $o['total'], 'currency' => $o['currency'], 'reference' => $o['payment_reference'],
                        'status' => 'failed', 'source' => 'staff', 'user_id' => $userId, 'meta' => mb_substr($note !== '' ? $note : 'Marked as failed by staff', 0, 255)]);
                }
                OrderService::addLog($orderId, 'payment', 'Payment status: ' . OrderService::paymentStatusLabel((string) $o['payment_status']) . ' -> ' . OrderService::paymentStatusLabel($to) . '.', $userId);
            });
        } catch (ShopException $e) {
            return ['ok' => false, 'error' => $e->errorCode];
        }
        OrderService::flushCounts();
        return ['ok' => true];
    }

    /**
     * Part of OrderService::setStatus('refunded'): ledger row + payment_status refunded, inside the caller's transaction.
     * @param array<string,mixed> $order the order row, locked FOR UPDATE by the caller
     */
    public static function refundInTransaction(array $order, ?int $userId = null): void
    {
        $paid = self::paidMinor((int) $order['id']);
        Database::insert('shop_payments', [
            'order_id' => $order['id'], 'method' => $order['payment_method'], 'amount' => Money::toDb($paid), 'currency' => $order['currency'], 'reference' => $order['payment_reference'],
            'status' => 'refunded', 'source' => 'staff', 'user_id' => $userId, 'meta' => 'Refund recorded by staff (the money itself is returned outside this site)',
        ]);
        Database::execute("UPDATE shop_orders SET payment_status = 'refunded' WHERE id = ?", [$order['id']]);
        OrderService::addLog((int) $order['id'], 'payment', 'Refund recorded: ' . Money::format($paid, (string) $order['currency']) . '. Return the money to the customer outside this site.', $userId);
    }

    // ------------------------------------------------------------------------------------------------ events (gateway log)

    /** One line in the gateway event log: kind, reference, status. NEVER a payload, a name, an e-mail or a key. */
    public static function recordEvent(string $kind, ?string $ref, ?int $orderId, ?string $status): void
    {
        try {
            $id = Database::insert('shop_events', [
                'kind' => mb_substr(preg_replace('/[^a-z0-9_]/', '', strtolower($kind)) ?: 'event', 0, 40), 'ref' => $ref === null ? null : mb_substr($ref, 0, 120),
                'order_id' => $orderId, 'status' => $status === null ? null : mb_substr($status, 0, 30),
            ]);
            if ($id % 50 === 0) {
                Database::execute('DELETE FROM shop_events WHERE id < ?', [$id - 2000]);       // keep the log small
            }
        } catch (\Throwable $e) {
            Log::warning('shop_events write failed: ' . $e::class);
        }
    }

    /** @return list<array<string,mixed>> newest first */
    public static function events(int $limit = 20): array
    {
        return Database::all('SELECT e.*, o.order_number FROM shop_events e LEFT JOIN shop_orders o ON o.id = e.order_id ORDER BY e.id DESC LIMIT ' . max(1, min(200, $limit)));
    }

    // ------------------------------------------------------------------------------------------------ internals

    /** @param array<string,mixed> $o @param array<string,mixed> $p */
    private static function ledgerWrite(int $orderId, array $o, string $method, int $amount, string $currency, ?string $reference, string $status, string $source, array $p, string $meta = '', ?int $userId = null): bool
    {
        // idempotent: the same review / duplicate for the same reference is written once
        if ($reference !== null && in_array($status, ['review', 'duplicate'], true)
            && Database::one('SELECT id FROM shop_payments WHERE order_id = ? AND reference = ? AND status = ? LIMIT 1', [$orderId, $reference, $status]) !== null) {
            return false;
        }
        // a pending row for the same reference (the attempt / the customer's reference) becomes this row (success, failed, review or duplicate) instead of adding another one
        $pending = $reference === null ? null : Database::one("SELECT id FROM shop_payments WHERE order_id = ? AND reference = ? AND status = 'pending' ORDER BY id DESC LIMIT 1", [$orderId, $reference]);
        $gw = isset($p['gateway_id']) && $p['gateway_id'] !== null ? preg_replace('/\D/', '', (string) $p['gateway_id']) : null;
        $row = ['method' => $method, 'amount' => Money::toDb(max(0, $amount)), 'currency' => Money::validCurrency($currency) ? $currency : $o['currency'], 'status' => $status, 'source' => $source,
            'gateway_id' => $gw === '' ? null : ($gw === null ? null : substr($gw, 0, 40)), 'user_id' => $userId, 'meta' => mb_substr($meta, 0, 255)];
        if ($pending !== null) {
            Database::update('shop_payments', $row, 'id = ?', [$pending['id']]);
            return true;
        }
        Database::insert('shop_payments', $row + ['order_id' => $orderId, 'reference' => $reference]);
        return true;
    }

    /** Statuses of ShopOrder::PAYMENT_STATUSES (for the admin filter). @return list<string> */
    public static function statuses(): array
    {
        return ShopOrder::PAYMENT_STATUSES;
    }
}
