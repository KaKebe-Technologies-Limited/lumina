<?php
declare(strict_types=1);

namespace App\Services\Shop;

use App\Core\Config;
use App\Core\Database;
use App\Core\Log;
use App\Models\ShopOrder;

/**
 * ORDERS: creation (the only place stock is reserved), the fulfilment STATUS machine, stock release, holds, search, timeline.
 * Payments are PaymentService's job; totals are PricingService's. Personal data (name, e-mail, phone, address) is stored here and nowhere else;
 * it is never written to logs or audit entries (order NUMBERS and ids only).
 *
 * STATUS MACHINE (order.status; "-> x" = allowed next):
 *   pending     -> confirmed processing ready cancelled           (new; booking waits for staff)
 *   confirmed   -> processing ready shipped completed refunded cancelled
 *   processing  -> confirmed ready shipped completed refunded cancelled
 *   ready       -> processing shipped completed refunded cancelled     (ready for pickup)
 *   shipped     -> completed ready refunded cancelled
 *   completed   -> refunded
 *   cancelled, refunded: final.
 *   Rules: "completed" needs payment_status = paid (opts allow_unpaid overrides, e.g. a free order); "refunded" needs a PAID order and also
 *   marks the payment refunded; "cancelled" gives the reserved stock back (always while unpaid; while paid only if setting shop_restock_on_cancel).
 *
 * STOCK: reserved when the order is created (row-locked, inside the transaction), released ONCE (shop_orders.stock_released) when the order is
 * cancelled / expires / is refunded (setting) / is deleted while still open.
 *
 * HOLDS: an unpaid order keeps its stock until hold_until (book/cod: shop_hold_days, momo/bank: shop_manual_hold_days, flutterwave:
 * shop_gateway_hold_hours). releaseExpiredHolds() cancels orders that are still pending + unpaid/pending/failed past that time. It runs lazily
 * at the start of create() and from bin/shop-maintenance.php. Staff confirming a booking clears the hold. `review` orders never expire.
 */
final class OrderService
{
    public const FLOW = [
        'pending'    => ['confirmed', 'processing', 'ready', 'cancelled'],
        'confirmed'  => ['processing', 'ready', 'shipped', 'completed', 'refunded', 'cancelled'],
        'processing' => ['confirmed', 'ready', 'shipped', 'completed', 'refunded', 'cancelled'],
        'ready'      => ['processing', 'shipped', 'completed', 'refunded', 'cancelled'],
        'shipped'    => ['completed', 'ready', 'refunded', 'cancelled'],
        'completed'  => ['refunded'],
        'cancelled'  => [],
        'refunded'   => [],
    ];
    /** Statuses after which the customer is told by e-mail. */
    public const NOTIFY_STATUSES = ['confirmed', 'ready', 'shipped', 'completed', 'cancelled', 'refunded'];
    private const NUMBER_ALPHABET = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';

    private static ?int $newCountCache = null;

    // ------------------------------------------------------------------------------------------------ lookups

    public static function find(int $id): ?array
    {
        return Database::one('SELECT * FROM shop_orders WHERE id = ?', [$id]);
    }

    /** By the secret link token. The token must be 40 hex characters; anything else is "not found" without touching the database. */
    public static function findByToken(string $token): ?array
    {
        if (preg_match('/^[a-f0-9]{40}$/', $token) !== 1) {
            return null;
        }
        return Database::one('SELECT * FROM shop_orders WHERE token = ?', [$token]);
    }

    public static function findByNumber(string $number): ?array
    {
        return Database::one('SELECT * FROM shop_orders WHERE order_number = ?', [trim($number)]);
    }

    /** @return list<array<string,mixed>> */
    public static function items(int $orderId): array
    {
        return Database::all('SELECT * FROM shop_order_items WHERE order_id = ? ORDER BY id ASC', [$orderId]);
    }

    /** The timeline, oldest first. @return list<array<string,mixed>> */
    public static function log(int $orderId): array
    {
        return Database::all('SELECT l.*, u.name AS user_name FROM shop_order_log l LEFT JOIN users u ON u.id = l.user_id WHERE l.order_id = ? ORDER BY l.id ASC', [$orderId]);
    }

    public static function addLog(int $orderId, string $kind, string $message, ?int $userId = null): void
    {
        Database::insert('shop_order_log', ['order_id' => $orderId, 'kind' => mb_substr($kind, 0, 30), 'message' => mb_substr($message, 0, 255), 'user_id' => $userId]);
    }

    /** Link a customer can use to open the order again (relative to the site). */
    public static function url(array $order): string
    {
        return url('shop/order/' . $order['token']);
    }

    public static function absUrl(array $order): string
    {
        return abs_url('shop/order/' . $order['token']);
    }

    public static function statusLabel(string $status): string
    {
        $reg = ShopSettings::registryText('status.' . $status);
        return $reg !== '' ? $reg : ucfirst(str_replace('_', ' ', $status));
    }

    public static function paymentStatusLabel(string $status): string
    {
        $reg = ShopSettings::registryText('pay_status.' . $status);
        return $reg !== '' ? $reg : ucfirst(str_replace('_', ' ', $status));
    }

    public static function canTransition(string $from, string $to): bool
    {
        return in_array($to, self::FLOW[$from] ?? [], true);
    }

    /** @return list<string> */
    public static function allowedNext(string $from): array
    {
        return self::FLOW[$from] ?? [];
    }

    /** Orders that need a person: new (pending) or a payment to review. Memoised per request; 0 when the tables are missing. */
    public static function newCount(): int
    {
        if (self::$newCountCache !== null) {
            return self::$newCountCache;
        }
        try {
            self::$newCountCache = (int) Database::value("SELECT COUNT(*) FROM shop_orders WHERE status = 'pending' OR payment_status = 'review'", [], 0);
        } catch (\Throwable) {
            self::$newCountCache = 0;
        }
        return self::$newCountCache;
    }

    public static function flushCounts(): void
    {
        self::$newCountCache = null;
    }

    // ------------------------------------------------------------------------------------------------ create

    /**
     * Turn the cart into an order. EVERYTHING is checked again here from the database: the browser's prices and totals are ignored.
     *
     * @param array{name?:mixed,email?:mixed,phone?:mixed,address?:mixed,note?:mixed,consent?:mixed} $customer
     * @param list<array<string,mixed>> $lines cart lines (CartService::lines()): only product_id, selection and qty are used
     * @param array{ip_hash?:string,notify?:bool,now?:\DateTimeImmutable} $ctx notify=false skips the e-mails (tests)
     * @return array{ok:bool,error?:string,fields?:array<string,string>,lines?:array<string,string>,order?:array<string,mixed>,items?:list<array<string,mixed>>}
     *   errors: closed | empty | customer (fields: name|email|phone|address|note|consent => code) | delivery | payment | unavailable | options | qty | stock (lines: key => code)
     */
    public static function create(array $customer, array $lines, string $deliveryMethod, string $paymentMethod, array $ctx = []): array
    {
        $fail = static fn(string $code, array $extra = []): array => ['ok' => false, 'error' => $code] + $extra;
        if (!ShopSettings::isOpen()) {
            return $fail('closed');
        }
        $lines = array_values($lines);
        if ($lines === [] || count($lines) > CartService::MAX_LINES) {
            return $fail('empty');
        }
        $clean = self::cleanCustomer($customer, $deliveryMethod);
        if ($clean['errors'] !== []) {
            return $fail('customer', ['fields' => $clean['errors']]);
        }
        $methods = ShopSettings::deliveryMethods();
        if (!in_array($deliveryMethod, $methods, true)) {
            return $fail('delivery');
        }
        if (!PaymentMethods::isKey($paymentMethod)) {
            return $fail('payment');
        }
        try {
            self::releaseExpiredHolds(10);
        } catch (\Throwable $e) {
            Log::warning('Shop: releasing expired holds failed: ' . $e::class);
        }

        $now = $ctx['now'] ?? new \DateTimeImmutable('now');
        $cur = ShopSettings::currency();
        try {
            $created = Database::transaction(function () use ($clean, $lines, $deliveryMethod, $paymentMethod, $ctx, $now, $cur): array {
                // ---- lock every product involved (in id order: two checkouts can never wait for each other in a circle)
                $ids = array_values(array_unique(array_map(static fn(array $l): int => (int) ($l['product_id'] ?? 0), $lines)));
                sort($ids);
                if ($ids === [] || $ids[0] < 1) {
                    throw new ShopException('unavailable', []);
                }
                $in = implode(',', $ids);
                $rows = [];
                foreach (Database::all("SELECT * FROM shop_products WHERE id IN ({$in}) ORDER BY id FOR UPDATE") as $r) {
                    $rows[(int) $r['id']] = $r;
                }
                $visible = array_map('intval', array_column(Database::all("SELECT p.id FROM shop_products p WHERE p.id IN ({$in}) AND " . ProductService::visibleSql('p')), 'id'));

                $plan = [];                 // product id => list of [qty, selection]
                $items = [];
                $quoteLines = [];
                $allBookable = true;
                $problems = [];
                foreach ($lines as $l) {
                    $key = (string) ($l['key'] ?? '');
                    $pid = (int) ($l['product_id'] ?? 0);
                    $row = $rows[$pid] ?? null;
                    if ($row === null || !in_array($pid, $visible, true)) {
                        $problems[$key] = 'unavailable';
                        continue;
                    }
                    $check = ProductService::validateSelection($row, $l['selection'] ?? []);
                    if (!$check['ok']) {
                        $problems[$key] = in_array('out_of_stock', $check['errors'], true) ? 'stock' : 'options';
                        continue;
                    }
                    $qty = (int) ($l['qty'] ?? 0);
                    if ($qty < 1 || $qty > ShopSettings::maxQty()) {
                        $problems[$key] = 'qty';
                        continue;
                    }
                    if ((int) $row['allow_booking'] !== 1) {
                        $allBookable = false;
                    }
                    $unit = Money::roundTo(ProductService::priceInfo($row, $now)['unit'], $cur);
                    $plan[$pid][] = ['qty' => $qty, 'selection' => $check['selection']];
                    $quoteLines[] = ['unit_minor' => $unit, 'qty' => $qty];
                    $img = Database::value('SELECT path FROM shop_product_images WHERE product_id = ? ORDER BY is_cover DESC, sort_order ASC, id ASC LIMIT 1', [$pid]);
                    $items[] = [
                        'product_id' => $pid, 'name' => mb_substr((string) $row['name'], 0, 190), 'options_text' => $check['text'] === '' ? null : mb_substr($check['text'], 0, 255),
                        'selection' => $check['selection'] === [] ? null : json_encode($check['selection'], JSON_UNESCAPED_UNICODE), 'unit' => $unit, 'qty' => $qty,
                        'sku' => $row['sku'] === null || $row['sku'] === '' ? null : mb_substr((string) $row['sku'], 0, 60), 'image' => $img === null ? null : (string) $img,
                    ];
                }
                if ($problems !== []) {
                    $worst = in_array('unavailable', $problems, true) ? 'unavailable' : (in_array('stock', $problems, true) ? 'stock' : (in_array('options', $problems, true) ? 'options' : 'qty'));
                    throw new ShopException($worst, ['lines' => $problems]);
                }
                if (!PaymentMethods::isAvailable($paymentMethod, $allBookable)) {
                    throw new ShopException('payment', []);
                }

                // ---- stock: enough of everything? (the whole order is refused if one line is short)
                $stockProblems = [];
                foreach ($plan as $pid => $list) {
                    $short = self::stockShort($rows[$pid], $list);
                    if ($short) {
                        foreach ($lines as $l) {
                            if ((int) $l['product_id'] === $pid) {
                                $stockProblems[(string) $l['key']] = 'stock';
                            }
                        }
                    }
                }
                if ($stockProblems !== []) {
                    throw new ShopException('stock', ['lines' => $stockProblems]);
                }

                // ---- totals (PricingService only)
                $quote = PricingService::quote($quoteLines, $deliveryMethod);

                // ---- the order
                $hold = self::holdUntil($paymentMethod, $now);
                $order = [
                    'token' => bin2hex(random_bytes(20)), 'status' => 'pending', 'payment_status' => 'unpaid', 'payment_method' => $paymentMethod,
                    'customer_name' => $clean['name'], 'customer_email' => $clean['email'], 'customer_phone' => $clean['phone'],
                    'delivery_method' => $quote['delivery_method'], 'delivery_address' => $quote['delivery_method'] === 'delivery' ? $clean['address'] : null,
                    'delivery_fee' => Money::toDb($quote['delivery_fee']), 'subtotal' => Money::toDb($quote['subtotal']), 'total' => Money::toDb($quote['total']),
                    'currency' => $cur, 'customer_note' => $clean['note'] === '' ? null : $clean['note'], 'hold_until' => $hold,
                    'consent_at' => $now->format('Y-m-d H:i:s'), 'ip_hash' => (string) ($ctx['ip_hash'] ?? '') === '' ? null : substr((string) $ctx['ip_hash'], 0, 64),
                ];
                $orderId = 0;
                for ($try = 0; $try < 6 && $orderId === 0; $try++) {
                    $order['order_number'] = self::newNumber($now, $try >= 4 ? 5 : 4);
                    try {
                        $orderId = Database::insert('shop_orders', $order);
                    } catch (\PDOException $e) {
                        if ($try === 5 || !str_contains($e->getMessage(), 'Duplicate')) {
                            throw $e;
                        }
                    }
                }
                foreach ($items as $it) {
                    Database::insert('shop_order_items', [
                        'order_id' => $orderId, 'product_id' => $it['product_id'], 'name' => $it['name'], 'options_text' => $it['options_text'], 'selection' => $it['selection'],
                        'unit_price' => Money::toDb($it['unit']), 'qty' => $it['qty'], 'line_total' => Money::toDb(Money::mul($it['unit'], $it['qty'])), 'sku' => $it['sku'], 'image' => $it['image'],
                    ]);
                }
                // ---- reserve the stock
                foreach ($plan as $pid => $list) {
                    self::applyStock($rows[$pid], $list, -1);
                }
                self::addLog($orderId, 'created', 'Order placed (' . $paymentMethod . ', ' . $quote['delivery_method'] . ').');
                return ['id' => $orderId];
            });
        } catch (ShopException $e) {
            return $fail($e->errorCode, $e->details);
        } catch (\OverflowException) {
            return $fail('qty');                                                      // a total beyond what the money columns hold: refuse, never a half-made order
        }
        self::flushCounts();
        $order = self::find((int) $created['id']) ?? [];
        $items = self::items((int) $created['id']);
        if (($ctx['notify'] ?? true) !== false && $order !== []) {
            try {
                ShopMailer::orderPlaced($order, $items);
            } catch (\Throwable $e) {
                Log::error('Shop order e-mail failed', ['order' => $order['order_number'] ?? '', 'error' => $e::class]);
            }
        }
        return ['ok' => true, 'order' => $order, 'items' => $items];
    }

    /**
     * Normalise and check the customer's details (also used by the checkout controller to show field errors BEFORE anything is reserved).
     * @return array{name:string,email:string,phone:string,address:string,note:string,errors:array<string,string>}
     *   error codes: required | invalid | too_long | consent
     */
    public static function cleanCustomer(array $in, string $deliveryMethod = 'pickup'): array
    {
        $txt = static fn(mixed $v, int $max): string => is_scalar($v) ? mb_substr(trim((string) preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', str_replace(["\r\n", "\r"], "\n", (string) $v))), 0, $max) : '';
        $one = static fn(string $s): string => trim((string) preg_replace('/\s+/u', ' ', strip_tags($s)));
        $errors = [];
        $name = $one($txt($in['name'] ?? '', 200));
        if ($name === '') {
            $errors['name'] = 'required';
        } elseif (mb_strlen($name) < 2 || mb_strlen($name) > 150) {
            $errors['name'] = mb_strlen($name) > 150 ? 'too_long' : 'invalid';
        }
        $email = strtolower($one($txt($in['email'] ?? '', 300)));
        if ($email === '') {
            $errors['email'] = 'required';
        } elseif (strlen($email) > 190 || filter_var($email, FILTER_VALIDATE_EMAIL) === false || preg_match('/[\r\n\0<>,;"]/', $email) === 1) {
            $errors['email'] = 'invalid';
        }
        $phone = $one($txt($in['phone'] ?? '', 100));
        if ($phone === '') {
            $errors['phone'] = 'required';
        } else {
            $norm = self::normalisePhone($phone);
            if ($norm === null) {
                $errors['phone'] = 'invalid';
            } else {
                $phone = $norm;
            }
        }
        $address = trim(strip_tags($txt($in['address'] ?? '', 800)));
        if ($deliveryMethod === 'delivery') {
            if ($address === '') {
                $errors['address'] = 'required';
            } elseif (mb_strlen($address) < 5 || mb_strlen($address) > 500) {
                $errors['address'] = mb_strlen($address) > 500 ? 'too_long' : 'invalid';
            }
        }
        $note = trim(strip_tags($txt($in['note'] ?? '', 2000)));
        if (mb_strlen($note) > 1000) {
            $errors['note'] = 'too_long';
        }
        if (!filter_var($in['consent'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            $errors['consent'] = 'consent';
        }
        return ['name' => $name, 'email' => $email, 'phone' => $phone, 'address' => $address, 'note' => $note, 'errors' => $errors];
    }

    /**
     * Uganda numbers: 0700123456 / 0700 123 456 / +256700123456 / 256700123456 -> "+256700123456"; other countries: "+" or "00" followed by 8-14 digits.
     * @return ?string normalised number, or null when it is not a phone number
     */
    public static function normalisePhone(string $raw): ?string
    {
        $s = preg_replace('/[\s().\-]/', '', $raw) ?? '';
        if (preg_match('/^\+?[0-9]{7,16}$/', $s) !== 1) {
            return null;
        }
        if (str_starts_with($s, '00')) {
            $s = '+' . substr($s, 2);
        }
        if (preg_match('/^0([347][0-9]{8})$/', $s, $m) === 1) {
            return '+256' . $m[1];
        }
        if (preg_match('/^\+?256([347][0-9]{8})$/', $s, $m) === 1) {
            return '+256' . $m[1];
        }
        if (str_starts_with($s, '+')) {
            return preg_match('/^\+[1-9][0-9]{7,14}$/', $s) === 1 ? $s : null;
        }
        return null;                                    // local-looking numbers that are not Ugandan mobile / landline patterns
    }

    // ------------------------------------------------------------------------------------------------ holds / numbers

    /** hold_until for a new order of this method (EAT datetime string). */
    public static function holdUntil(string $paymentMethod, ?\DateTimeImmutable $now = null): string
    {
        $now ??= new \DateTimeImmutable('now');
        $interval = match ($paymentMethod) {
            'momo', 'bank' => 'P' . ShopSettings::int('shop_manual_hold_days', 1, 60) . 'D',
            'flutterwave'  => 'PT' . ShopSettings::int('shop_gateway_hold_hours', 1, 720) . 'H',
            default        => 'P' . ShopSettings::int('shop_hold_days', 1, 90) . 'D',
        };
        return $now->add(new \DateInterval($interval))->format('Y-m-d H:i:s');
    }

    /** PREFIX-YYMMDD-XXXX (no look-alike characters). Not guaranteed unique: the unique index decides. */
    public static function newNumber(?\DateTimeInterface $at = null, int $len = 4): string
    {
        $at ??= new \DateTimeImmutable('now');
        $code = '';
        $n = strlen(self::NUMBER_ALPHABET);
        for ($i = 0; $i < $len; $i++) {
            $code .= self::NUMBER_ALPHABET[random_int(0, $n - 1)];
        }
        return ShopSettings::orderPrefix() . '-' . $at->format('ymd') . '-' . $code;
    }

    /**
     * Orders that are still waiting and whose hold ran out are cancelled and their stock goes back. Returns how many.
     * An order with an online payment try that may still turn into money is first CHECKED WITH THE GATEWAY (at most $verifyBudget orders per call, so the lazy call at
     * checkout stays quick): a payment whose webhook was lost is then recorded instead of being cancelled. If the gateway cannot be reached the order is cancelled
     * as before, and the cron keeps re-checking cancelled orders for a while (money that turns up is flagged `review`, never hidden).
     */
    public static function releaseExpiredHolds(int $limit = 25, int $verifyBudget = 2): int
    {
        $canVerify = PaymentMethods::enabled('flutterwave') && PaymentMethods::ready('flutterwave');
        $now = date('Y-m-d H:i:s');
        $ids = Database::all(
            "SELECT id FROM shop_orders WHERE status = 'pending' AND payment_status IN ('unpaid','pending','failed') AND hold_until IS NOT NULL AND hold_until < ? ORDER BY hold_until ASC LIMIT " . max(1, min(200, $limit)),
            [$now]
        );
        $n = 0;
        foreach ($ids as $r) {
            if ($canVerify && $verifyBudget > 0 && OnlinePayment::hasOpenAttempt((int) $r['id'])) {
                $verifyBudget--;
                try {
                    OnlinePayment::recheck((int) $r['id']);
                } catch (\Throwable $e) {
                    Log::warning('Shop: checking an expiring online payment failed: ' . $e::class);
                }
            }
            $res = self::setStatus((int) $r['id'], 'cancelled', ['note' => 'The reservation time ran out.', 'only_if_expired' => true]);
            if ($res['ok']) {
                $n++;
            }
        }
        return $n;
    }

    // ------------------------------------------------------------------------------------------------ status

    /**
     * Change the fulfilment status.
     * @param array{user_id?:?int,note?:string,allow_unpaid?:bool,notify?:bool,only_if_expired?:bool} $opts
     * @return array{ok:bool,error?:string,order?:array<string,mixed>}  errors: not_found | same | transition | not_paid | expired_no_more
     */
    public static function setStatus(int $orderId, string $to, array $opts = []): array
    {
        if (!in_array($to, ShopOrder::STATUSES, true)) {
            return ['ok' => false, 'error' => 'transition'];
        }
        $userId = isset($opts['user_id']) ? (int) $opts['user_id'] : null;
        try {
            $result = Database::transaction(function () use ($orderId, $to, $opts, $userId): array {
                $o = Database::one('SELECT * FROM shop_orders WHERE id = ? FOR UPDATE', [$orderId]);
                if ($o === null) {
                    throw new ShopException('not_found');
                }
                if (!empty($opts['only_if_expired'])) {
                    if ($o['status'] !== 'pending' || !in_array($o['payment_status'], ['unpaid', 'pending', 'failed'], true) || $o['hold_until'] === null || (string) $o['hold_until'] >= date('Y-m-d H:i:s')) {
                        throw new ShopException('expired_no_more');
                    }
                }
                $from = (string) $o['status'];
                if ($from === $to) {
                    throw new ShopException('same');
                }
                if (!self::canTransition($from, $to)) {
                    throw new ShopException('transition');
                }
                if ($to === 'completed' && $o['payment_status'] !== 'paid' && empty($opts['allow_unpaid'])) {
                    throw new ShopException('not_paid');
                }
                if ($to === 'refunded') {
                    if ($o['payment_status'] !== 'paid') {
                        throw new ShopException('not_paid');
                    }
                    PaymentService::refundInTransaction($o, $userId);
                }
                // the reservation clock only means something while the order is still `pending`: any move forward (confirmed, ready ...) ends it
                Database::execute('UPDATE shop_orders SET status = ?, hold_until = NULL WHERE id = ?', [$to, $orderId]);
                self::addLog($orderId, 'status', 'Status: ' . self::statusLabel($from) . ' -> ' . self::statusLabel($to) . (($opts['note'] ?? '') !== '' ? '. ' . mb_substr((string) $opts['note'], 0, 150) : '') . '.', $userId);
                if ($to === 'cancelled') {
                    if ($o['payment_status'] !== 'paid' || ShopSettings::bool('shop_restock_on_cancel')) {
                        self::releaseStockInTransaction($o, 'cancelled');
                    }
                } elseif ($to === 'refunded' && ShopSettings::bool('shop_restock_on_refund')) {
                    self::releaseStockInTransaction($o, 'refunded');
                }
                return ['from' => $from];
            });
        } catch (ShopException $e) {
            return ['ok' => false, 'error' => $e->errorCode];
        }
        self::flushCounts();
        $order = self::find($orderId) ?? [];
        if (($opts['notify'] ?? true) !== false && in_array($to, self::NOTIFY_STATUSES, true) && $order !== []) {
            try {
                ShopMailer::statusChanged($order, $to);
            } catch (\Throwable $e) {
                Log::error('Shop status e-mail failed', ['order' => $order['order_number'] ?? '', 'error' => $e::class]);
            }
        }
        return ['ok' => true, 'order' => $order];
    }

    /** Staff accept a booking: pending -> confirmed and the stock stays reserved (the hold ends). */
    public static function confirmBooking(int $orderId, ?int $userId = null): array
    {
        $r = self::setStatus($orderId, 'confirmed', ['user_id' => $userId, 'note' => 'Booking confirmed']);
        if ($r['ok']) {
            Database::execute('UPDATE shop_orders SET hold_until = NULL WHERE id = ?', [$orderId]);
        }
        return $r;
    }

    public static function cancel(int $orderId, ?int $userId = null, string $reason = ''): array
    {
        return self::setStatus($orderId, 'cancelled', ['user_id' => $userId, 'note' => $reason]);
    }

    /** Append a staff note (admin only) and a timeline line. */
    public static function addNote(int $orderId, string $note, ?int $userId = null, string $who = ''): bool
    {
        $note = trim(strip_tags($note));
        if ($note === '') {
            return false;
        }
        $line = '[' . date('Y-m-d H:i') . ($who !== '' ? ' ' . mb_substr(trim(strip_tags($who)), 0, 60) : '') . '] ' . mb_substr($note, 0, 1500);
        $n = Database::execute("UPDATE shop_orders SET staff_note = CONCAT(COALESCE(staff_note, ''), IF(staff_note IS NULL OR staff_note = '', '', CHAR(10)), ?) WHERE id = ?", [$line, $orderId]);
        if ($n > 0) {
            self::addLog($orderId, 'note', 'A staff note was added.', $userId);
        }
        return $n > 0;
    }

    /** Delete an order for good (shop.delete). Open orders give their stock back first. @return bool false when it does not exist */
    public static function delete(int $orderId): bool
    {
        return (bool) Database::transaction(function () use ($orderId): bool {
            $o = Database::one('SELECT * FROM shop_orders WHERE id = ? FOR UPDATE', [$orderId]);
            if ($o === null) {
                return false;
            }
            if (in_array($o['status'], ['pending', 'confirmed', 'processing', 'ready'], true)) {
                self::releaseStockInTransaction($o, 'deleted');
            }
            Database::execute('DELETE FROM shop_orders WHERE id = ?', [$orderId]);
            self::flushCounts();
            return true;
        });
    }

    // ------------------------------------------------------------------------------------------------ stock

    /** Give the reserved units back (once). Public wrapper for staff tools. @return bool true when stock was released now */
    public static function releaseStock(int $orderId, string $why = 'manual'): bool
    {
        return (bool) Database::transaction(function () use ($orderId, $why): bool {
            $o = Database::one('SELECT * FROM shop_orders WHERE id = ? FOR UPDATE', [$orderId]);
            return $o !== null && self::releaseStockInTransaction($o, $why);
        });
    }

    /** @param array<string,mixed> $order a row locked FOR UPDATE by the caller */
    private static function releaseStockInTransaction(array $order, string $why): bool
    {
        if ((int) $order['stock_released'] === 1) {
            return false;
        }
        $byProduct = [];
        foreach (self::items((int) $order['id']) as $it) {
            if ($it['product_id'] === null) {
                continue;
            }
            $sel = json_decode((string) ($it['selection'] ?? ''), true);
            $byProduct[(int) $it['product_id']][] = ['qty' => (int) $it['qty'], 'selection' => is_array($sel) ? $sel : []];
        }
        $ids = array_keys($byProduct);
        sort($ids);
        if ($ids !== []) {
            foreach (Database::all('SELECT * FROM shop_products WHERE id IN (' . implode(',', array_map('intval', $ids)) . ') ORDER BY id FOR UPDATE') as $row) {
                self::applyStock($row, $byProduct[(int) $row['id']], +1);
            }
        }
        Database::execute('UPDATE shop_orders SET stock_released = 1 WHERE id = ?', [$order['id']]);
        self::addLog((int) $order['id'], 'stock', 'Stock returned to the shelf (' . $why . ').');
        return true;
    }

    /**
     * Would these lines take more than the shelf holds? (product stock and option stock; only for tracked products)
     * @param list<array{qty:int,selection:array<string,string>}> $list
     */
    private static function stockShort(array $row, array $list): bool
    {
        if ((int) $row['track_stock'] !== 1) {
            return false;
        }
        $total = 0;
        $perValue = [];
        foreach ($list as $l) {
            $total += $l['qty'];
            foreach ($l['selection'] as $g => $v) {
                $perValue[$g . "\0" . $v] = ($perValue[$g . "\0" . $v] ?? 0) + $l['qty'];
            }
        }
        if ($total > (int) $row['stock_qty']) {
            return true;
        }
        foreach (ProductService::parseOptions($row['options'] ?? null) as $g) {
            foreach ($g['values'] as $v) {
                if ($v['stock'] !== null && ($perValue[$g['key'] . "\0" . $v['value']] ?? 0) > $v['stock']) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Add ($sign +1) or take ($sign -1) units of a locked product row. Option stock follows the chosen values. Untracked products are untouched.
     * @param list<array{qty:int,selection:array<string,string>}> $list
     */
    private static function applyStock(array $row, array $list, int $sign): void
    {
        if ((int) $row['track_stock'] !== 1) {
            return;
        }
        $total = 0;
        $perValue = [];
        foreach ($list as $l) {
            $total += (int) $l['qty'];
            foreach ((array) $l['selection'] as $g => $v) {
                if (is_string($g) && is_string($v)) {
                    $perValue[$g . "\0" . $v] = ($perValue[$g . "\0" . $v] ?? 0) + (int) $l['qty'];
                }
            }
        }
        $groups = ProductService::parseOptions($row['options'] ?? null);
        $changed = false;
        foreach ($groups as &$g) {
            foreach ($g['values'] as &$v) {
                $q = $perValue[$g['key'] . "\0" . $v['value']] ?? 0;
                if ($q > 0 && $v['stock'] !== null) {
                    $v['stock'] = max(0, $v['stock'] + $sign * $q);
                    $changed = true;
                }
            }
            unset($v);
        }
        unset($g);
        $qty = max(0, (int) $row['stock_qty'] + $sign * $total);
        if ($changed) {
            Database::execute('UPDATE shop_products SET stock_qty = ?, options = ? WHERE id = ?', [$qty, ProductService::encodeOptions($groups), $row['id']]);
        } else {
            Database::execute('UPDATE shop_products SET stock_qty = ? WHERE id = ?', [$qty, $row['id']]);
        }
    }

    // ------------------------------------------------------------------------------------------------ payment instructions

    /**
     * What the confirmation page shows about paying.
     * @return array{method:string,label:string,instructions:string,details:list<array<string,string>>,reference:string,paid:bool,
     *   can_enter_reference:bool,can_pay_online:bool,awaiting_verification:bool}
     */
    public static function paymentInfo(array $order): array
    {
        $m = (string) $order['payment_method'];
        $open = $order['status'] === 'pending' && in_array($order['payment_status'], ['unpaid', 'pending', 'failed'], true);
        return [
            'method'                => $m,
            'label'                 => PaymentMethods::label($m),
            'instructions'          => PaymentMethods::instructions($m),
            'details'               => PaymentMethods::details($m),
            'reference'             => (string) $order['order_number'],
            'paid'                  => $order['payment_status'] === 'paid',
            'can_enter_reference'   => in_array($m, ['momo', 'bank'], true) && $open,
            'can_pay_online'        => $m === 'flutterwave' && $open && PaymentMethods::ready('flutterwave'),
            'awaiting_verification' => $order['payment_status'] === 'pending' && in_array($m, ['momo', 'bank'], true) && (string) ($order['payment_reference'] ?? '') !== '',
        ];
    }

    // ------------------------------------------------------------------------------------------------ search

    /**
     * Orders for the admin list and the export.
     * @param array{status?:string,payment_status?:string,method?:string,from?:string,to?:string,q?:string,needs_action?:bool} $filters from/to = Y-m-d
     * @return array{items:list<array<string,mixed>>,total:int,page:int,pages:int,perPage:int}
     */
    public static function search(array $filters, int $page = 1, int $perPage = 20): array
    {
        [$where, $params] = self::filterSql($filters);
        $perPage = max(1, min(200, $perPage));
        $total = (int) Database::value('SELECT COUNT(*) FROM shop_orders o WHERE ' . $where, $params, 0);
        $pages = max(1, (int) ceil($total / $perPage));
        $page = max(1, min($page, $pages));
        $rows = Database::all(
            'SELECT o.*, (SELECT COALESCE(SUM(qty), 0) FROM shop_order_items i WHERE i.order_id = o.id) AS units FROM shop_orders o WHERE ' . $where
            . ' ORDER BY o.id DESC LIMIT ' . $perPage . ' OFFSET ' . (($page - 1) * $perPage),
            $params
        );
        return ['items' => $rows, 'total' => $total, 'page' => $page, 'pages' => $pages, 'perPage' => $perPage];
    }

    /** Every matching order, newest first, in chunks (for CSV). @return \Generator<int,array<string,mixed>> */
    public static function searchAll(array $filters): \Generator
    {
        [$where, $params] = self::filterSql($filters);
        $lastId = PHP_INT_MAX;
        while (true) {
            $rows = Database::all('SELECT o.* FROM shop_orders o WHERE ' . $where . ' AND o.id < ? ORDER BY o.id DESC LIMIT 500', array_merge($params, [$lastId]));
            if ($rows === []) {
                return;
            }
            foreach ($rows as $r) {
                yield $r;
                $lastId = (int) $r['id'];
            }
        }
    }

    /** @return array{0:string,1:list<mixed>} */
    private static function filterSql(array $f): array
    {
        $w = ['1 = 1'];
        $p = [];
        if (in_array($f['status'] ?? '', ShopOrder::STATUSES, true)) {
            $w[] = 'o.status = ?';
            $p[] = $f['status'];
        }
        if (in_array($f['payment_status'] ?? '', ShopOrder::PAYMENT_STATUSES, true)) {
            $w[] = 'o.payment_status = ?';
            $p[] = $f['payment_status'];
        }
        if (PaymentMethods::isKey((string) ($f['method'] ?? ''))) {
            $w[] = 'o.payment_method = ?';
            $p[] = $f['method'];
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) ($f['from'] ?? '')) === 1) {
            $w[] = 'o.created_at >= ?';
            $p[] = $f['from'] . ' 00:00:00';
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) ($f['to'] ?? '')) === 1) {
            $w[] = 'o.created_at <= ?';
            $p[] = $f['to'] . ' 23:59:59';
        }
        $q = trim((string) ($f['q'] ?? ''));
        if ($q !== '') {
            $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], mb_substr($q, 0, 80)) . '%';
            // a phone typed the way people write it (0772 123 456, 077-212-3456) is stored normalised (+256772123456): search that form too
            $norm = self::normalisePhone($q);
            $phoneLike = $norm !== null ? '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $norm) . '%' : null;
            $w[] = '(o.order_number LIKE ? OR o.customer_name LIKE ? OR o.customer_email LIKE ? OR o.customer_phone LIKE ?' . ($phoneLike !== null ? ' OR o.customer_phone LIKE ?' : '') . ')';
            array_push($p, $like, $like, $like, $like);
            if ($phoneLike !== null) {
                $p[] = $phoneLike;
            }
        }
        if (!empty($f['needs_action'])) {
            $w[] = "(o.status = 'pending' OR o.payment_status = 'review')";
        }
        return [implode(' AND ', $w), $p];
    }

    /** keyed hash of an IP for the ip_hash column (never the address itself). */
    public static function ipHash(string $ip): string
    {
        return hash_hmac('sha256', 'shop-ip|' . $ip, Config::appKey());
    }
}
