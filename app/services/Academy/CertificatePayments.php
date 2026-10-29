<?php
declare(strict_types=1);

namespace App\Services\Academy;

use App\Core\Config;
use App\Core\Database;
use App\Core\Log;
use App\Core\Mailer;
use App\Core\RateLimit;
use App\Services\Payments\PesapalClient;

/**
 * The certificate fee of the LAF Academy (docs/PESAPAL.md). A learner who finished a course pays through Pesapal BEFORE the certificate is requested or issued;
 * CertificateService::request() and CourseService::checkCompletion() both ask required() first.
 *
 *   begin($learner, $course)       "Pay now": a pending row + a Pesapal order -> the hosted payment page to send the learner to
 *   handleReturn($query, $rid)     the learner comes back from Pesapal: the query string is NOT trusted, the order is asked about at Pesapal (server to server)
 *   handleIpn($query)              Pesapal's notification (GET): it carries only an id, so the status is asked back; idempotent; unknown references are ignored
 *   settle($row, $status, $src)    the ONLY place money is marked received: status completed AND the reference we sent AND the currency AND amount >= the fee
 *
 * Nothing the browser sends (amount, status, tracking id, course) decides anything: the amount is read from our own settings at the moment of payment and stored on
 * the row, the tracking id is the one Pesapal gave us, and a payment is believed only on Pesapal's own answer. A mismatch becomes `review` for the staff, never a
 * silent pass. A second payment for a course that is already paid is also put in `review` (to refund). The table is academy_payments (database/schema.sql).
 */
final class CertificatePayments
{
    public const CURRENCY = 'UGX';
    /** A pending payment that Pesapal never confirmed is closed after this many hours. */
    private const STALE_HOURS = 48;

    // ------------------------------------------------------------------------------------------------ rules

    /** Is the payments table there? (A site that has not run the update SQL yet keeps issuing certificates as before.) */
    public static function ready(): bool
    {
        static $ok = null;
        return $ok ??= Database::tableExists('academy_payments');
    }

    /** The fee for this course in whole currency units: its own (certificate_fee) or the site-wide one. 0 = free. */
    public static function fee(array $course): int
    {
        if (array_key_exists('certificate_fee', $course) && $course['certificate_fee'] !== null && $course['certificate_fee'] !== '') {
            return max(0, (int) $course['certificate_fee']);
        }
        return max(0, (int) setting('academy_cert_fee', '20000'));
    }

    public static function paid(int $learnerId, int $courseId): bool
    {
        return self::ready() && (int) Database::value("SELECT COUNT(*) FROM academy_payments WHERE learner_id = ? AND course_id = ? AND status IN ('paid','waived')", [$learnerId, $courseId], 0) > 0;
    }

    /** Must this learner still pay before the certificate of this course? */
    public static function required(int $learnerId, array $course): bool
    {
        return self::ready() && self::fee($course) > 0 && !self::paid($learnerId, (int) $course['id']);
    }

    /** The wording of the certificate button for the signed-in learner: "Pay & get my certificate" while the fee is unpaid, else "Request my certificate". */
    public static function label(string $courseSlug): string
    {
        $l = LearnerAuth::current();
        $c = $l !== null ? CourseService::bySlug($courseSlug, true) : null;
        return $l !== null && $c !== null && self::required((int) $l['id'], $c) ? 'Pay & get my certificate' : 'Request my certificate';
    }

    /** True when the site can take a payment right now (the Pesapal keys are in the .env file). */
    public static function available(): bool
    {
        return PesapalClient::configured();
    }

    public static function find(int $id): ?array
    {
        return Database::one('SELECT * FROM academy_payments WHERE id = ?', [$id]);
    }

    public static function byRef(string $ref): ?array
    {
        return self::validRef($ref) ? Database::one('SELECT * FROM academy_payments WHERE merchant_ref = ?', [$ref]) : null;
    }

    /** The learner's most recent payment row for the course (any status). */
    public static function latest(int $learnerId, int $courseId): ?array
    {
        return self::ready() ? Database::one('SELECT * FROM academy_payments WHERE learner_id = ? AND course_id = ? ORDER BY id DESC LIMIT 1', [$learnerId, $courseId]) : null;
    }

    public static function validRef(string $ref): bool
    {
        return preg_match('/^LAF-[A-F0-9]{12}$/', $ref) === 1;
    }

    /** Binds the return address to the browser that started the payment: only this server can compute it. 32 hex characters, a path segment. */
    public static function returnKey(string $ref): string
    {
        return substr(hash_hmac('sha256', 'academy-pay-return|' . $ref, Config::appKey()), 0, 32);
    }

    // ------------------------------------------------------------------------------------------------ start a payment

    /**
     * @return array{ok:bool,link?:string,error?:string,payment?:array<string,mixed>}
     *   errors: unavailable | not_eligible | not_required | already_paid | too_many | gateway (Pesapal said no or could not be reached) | no_key
     */
    public static function begin(array $learner, array $course): array
    {
        $lid = (int) $learner['id'];
        $cid = (int) $course['id'];
        if (!self::ready()) {
            return ['ok' => false, 'error' => 'unavailable'];
        }
        if (self::paid($lid, $cid)) {
            return ['ok' => false, 'error' => 'already_paid'];
        }
        $fee = self::fee($course);
        if ($fee <= 0) {
            return ['ok' => false, 'error' => 'not_required'];
        }
        if (!CertificateService::eligible($lid, $cid)) {
            return ['ok' => false, 'error' => 'not_eligible'];
        }
        if (!self::available()) {
            return ['ok' => false, 'error' => 'no_key'];
        }
        if (!RateLimit::attempt('academy-pay-begin:' . $lid, 8, 3600)) {
            return ['ok' => false, 'error' => 'too_many'];
        }
        // an earlier try may have been paid a moment ago (a slow mobile-money confirmation): ask Pesapal BEFORE opening another payable order, so nobody pays twice
        foreach (Database::all("SELECT * FROM academy_payments WHERE learner_id = ? AND course_id = ? AND status = 'pending' AND tracking_id IS NOT NULL ORDER BY id DESC LIMIT 3", [$lid, $cid]) as $old) {
            if (self::recheck($old, 'begin') === 'paid') {
                return ['ok' => false, 'error' => 'already_paid'];
            }
        }
        $ref = 'LAF-' . strtoupper(bin2hex(random_bytes(6)));
        $id = Database::insert('academy_payments', [
            'learner_id'    => $lid,
            'course_id'     => $cid,
            'learner_name'  => mb_substr((string) $learner['name'], 0, 120),
            'learner_email' => mb_substr((string) $learner['email'], 0, 190),
            'course_title'  => mb_substr((string) $course['title'], 0, 190),
            'merchant_ref'  => $ref,
            'amount'        => $fee,
            'currency'      => self::CURRENCY,
            'status'        => 'pending',
            'source'        => 'pesapal',
        ]);
        $base = PesapalClient::publicBase();
        $ipn = PesapalClient::ensureIpnId($base . '/academy/pay/ipn');
        if (!$ipn['ok']) {
            self::close($id, 'failed', 'Could not set up the Pesapal notification address (' . ($ipn['error'] ?? 'error') . ').');
            return ['ok' => false, 'error' => $ipn['error'] === 'no_key' ? 'no_key' : 'gateway'];
        }
        $order = PesapalClient::submitOrder([
            'id'              => $ref,
            'amount'          => (float) $fee,
            'currency'        => self::CURRENCY,
            'description'     => 'Certificate: ' . $course['title'],
            'callback_url'    => $base . '/academy/pay/return/' . self::returnKey($ref),
            'notification_id' => (string) $ipn['ipn_id'],
            'billing'         => ['name' => (string) $learner['name'], 'email' => (string) $learner['email'], 'phone' => self::phone((string) ($learner['phone'] ?? ''))],
        ]);
        if (!$order['ok']) {
            self::close($id, 'failed', 'Pesapal did not open the payment page (' . ($order['error'] ?? 'error') . ').');
            return ['ok' => false, 'error' => 'gateway'];
        }
        Database::update('academy_payments', ['tracking_id' => $order['tracking_id']], 'id = ?', [$id]);
        return ['ok' => true, 'link' => (string) $order['link'], 'payment' => (array) self::find($id)];
    }

    /** A Ugandan number as +256XXXXXXXXX so Pesapal pre-fills its own form, or '' when it cannot be read. */
    private static function phone(string $raw): string
    {
        $d = preg_replace('/\D+/', '', $raw) ?? '';
        if (str_starts_with($d, '00')) {
            $d = substr($d, 2);
        }
        if (strlen($d) === 10 && $d[0] === '0') {
            $d = '256' . substr($d, 1);
        } elseif (strlen($d) === 9) {
            $d = '256' . $d;
        }
        return preg_match('/^256[0-9]{9}$/', $d) === 1 ? '+' . $d : '';
    }

    // ------------------------------------------------------------------------------------------------ coming back / notifications

    /**
     * The learner is back from the Pesapal page. Only OrderMerchantReference is read from the query (format-checked); it is accepted ONLY with the key of that reference
     * (returnKey). Whatever else the query says is ignored: the order is asked about at Pesapal.
     * @param array<string,mixed> $query
     * @return array{state:string,payment:?array<string,mixed>}  state: paid | pending | failed | review | unbound | unknown | verify_failed
     */
    public static function handleReturn(array $query, string $rid): array
    {
        $ref = is_string($query['OrderMerchantReference'] ?? null) ? trim((string) $query['OrderMerchantReference']) : '';
        if (!self::validRef($ref) || preg_match('/^[a-f0-9]{32}$/', $rid) !== 1 || !hash_equals(self::returnKey($ref), $rid)) {
            return ['state' => 'unbound', 'payment' => null];
        }
        $row = self::byRef($ref);
        if ($row === null) {
            return ['state' => 'unknown', 'payment' => null];
        }
        if ($row['tracking_id'] === null) {
            // our own record of Pesapal's order id was lost: adopt the one in the query, but only if Pesapal itself says it belongs to this reference
            $t = is_string($query['OrderTrackingId'] ?? null) ? (string) $query['OrderTrackingId'] : '';
            if (PesapalClient::validId($t) && ($st = PesapalClient::status($t))['ok'] && hash_equals($ref, $st['merchant_ref'])) {
                Database::update('academy_payments', ['tracking_id' => $t], 'id = ?', [(int) $row['id']]);
                $row['tracking_id'] = $t;
            }
        }
        return ['state' => self::recheck($row, 'return'), 'payment' => self::find((int) $row['id'])];
    }

    /**
     * Pesapal's notification (a GET with OrderTrackingId, OrderMerchantReference, OrderNotificationType). Authenticated by asking Pesapal back, never by the ping itself.
     * @param array<string,mixed> $query
     * @return array{http:int,body:array<string,mixed>} http 200 = handled or deliberately ignored; 500 = Pesapal could not be asked (it will retry)
     */
    public static function handleIpn(array $query): array
    {
        $ref = is_string($query['OrderMerchantReference'] ?? null) ? trim((string) $query['OrderMerchantReference']) : '';
        $tracking = is_string($query['OrderTrackingId'] ?? null) ? trim((string) $query['OrderTrackingId']) : '';
        $type = is_string($query['OrderNotificationType'] ?? null) && preg_match('/^[A-Z]{3,20}$/', (string) $query['OrderNotificationType']) === 1 ? (string) $query['OrderNotificationType'] : 'IPNCHANGE';
        $ack = static fn(int $http): array => ['http' => $http, 'body' => [
            'orderNotificationType'  => $type,
            'orderTrackingId'        => PesapalClient::validId($tracking) ? $tracking : '',
            'orderMerchantReference' => self::validRef($ref) ? $ref : '',
            'status'                 => $http,
        ]];
        $row = self::byRef($ref);
        if ($row === null && PesapalClient::validId($tracking)) {
            $row = Database::one('SELECT * FROM academy_payments WHERE tracking_id = ? LIMIT 1', [$tracking]);
        }
        if ($row === null) {
            return $ack(200);                // not ours (or an invented reference): nothing to do, and no reason to make Pesapal retry
        }
        if ($row['tracking_id'] === null && PesapalClient::validId($tracking)) {
            Database::update('academy_payments', ['tracking_id' => $tracking], 'id = ? AND tracking_id IS NULL', [(int) $row['id']]);
            $row['tracking_id'] = $tracking;
        }
        $state = self::recheck($row, 'ipn');
        return $ack($state === 'verify_failed' ? 500 : 200);
    }

    /**
     * Ask Pesapal about one row and apply the answer. Safe to call any number of times.
     * @return string paid | pending | failed | review | verify_failed
     */
    public static function recheck(array $row, string $source = 'check'): string
    {
        if (in_array($row['status'], ['paid', 'waived'], true)) {
            return 'paid';
        }
        if ($row['status'] === 'review') {
            return 'review';
        }
        $id = (int) $row['id'];
        if ($row['tracking_id'] === null) {
            // the order never got an id: our request died before Pesapal replied. A short grace, then close it.
            if (strtotime((string) $row['created_at']) < time() - 900) {
                self::close($id, 'failed', 'The payment page was never opened.');
                return 'failed';
            }
            return 'pending';
        }
        $st = PesapalClient::status((string) $row['tracking_id']);
        Database::update('academy_payments', ['checked_at' => date('Y-m-d H:i:s')], 'id = ?', [$id]);
        if (!$st['ok']) {
            return 'verify_failed';
        }
        return self::settle($row, $st, $source);
    }

    /**
     * Apply Pesapal's verified answer. @param array{ok:bool,status:string,amount:?float,currency:string,merchant_ref:string,method:string,confirmation:string,description:string} $st
     * @return string paid | pending | failed | review
     */
    public static function settle(array $row, array $st, string $source): string
    {
        $id = (int) $row['id'];
        $became = null;
        $outcome = Database::transaction(static function () use ($id, $st, &$became): string {
            $r = Database::one('SELECT * FROM academy_payments WHERE id = ? FOR UPDATE', [$id]);
            if ($r === null) {
                return 'failed';
            }
            if (in_array($r['status'], ['paid', 'waived'], true)) {
                return 'paid';
            }
            if ($r['status'] === 'review') {
                return 'review';
            }
            $now = date('Y-m-d H:i:s');
            if ($st['status'] === 'completed') {
                $why = null;
                if (!hash_equals((string) $r['merchant_ref'], $st['merchant_ref'])) {
                    $why = 'Pesapal reported another order reference than the one we sent.';
                } elseif ($st['currency'] !== (string) $r['currency']) {
                    $why = 'Currency mismatch: we asked for ' . $r['currency'] . ', Pesapal received ' . ($st['currency'] ?: '?') . '.';
                } elseif ($st['amount'] === null || $st['amount'] + 0.005 < (float) $r['amount']) {
                    $why = 'Amount mismatch: we asked for ' . number_format((float) $r['amount'], 2) . ', Pesapal received ' . ($st['amount'] === null ? '?' : number_format($st['amount'], 2)) . '.';
                } elseif ($r['learner_id'] !== null && $r['course_id'] !== null && (int) Database::value("SELECT COUNT(*) FROM academy_payments WHERE learner_id = ? AND course_id = ? AND status IN ('paid','waived') AND id <> ?", [(int) $r['learner_id'], (int) $r['course_id'], $id], 0) > 0) {
                    $why = 'Duplicate payment: this course was already paid for. Refund it in Pesapal or keep it as a credit.';
                }
                if ($why !== null) {
                    Database::update('academy_payments', ['status' => 'review', 'note' => mb_substr($why, 0, 255), 'method' => $st['method'] ?: null, 'confirmation_code' => $st['confirmation'] ?: null, 'paid_at' => $now], 'id = ?', [$id]);
                    return 'review';
                }
                Database::update('academy_payments', ['status' => 'paid', 'paid_at' => $now, 'method' => $st['method'] ?: null, 'confirmation_code' => $st['confirmation'] ?: null, 'note' => null], 'id = ?', [$id]);
                $became = 'paid';
                return 'paid';
            }
            if ($st['status'] === 'failed') {
                Database::update('academy_payments', ['status' => 'failed', 'note' => 'Declined or reversed at Pesapal' . ($st['method'] !== '' ? ' (' . $st['method'] . ')' : '') . '.', 'method' => $st['method'] ?: null], 'id = ?', [$id]);
                return 'failed';
            }
            if (strtotime((string) $r['created_at']) < time() - self::STALE_HOURS * 3600) {
                Database::update('academy_payments', ['status' => 'failed', 'note' => 'Never confirmed by Pesapal (abandoned).'], 'id = ?', [$id]);
                return 'failed';
            }
            return 'pending';
        });
        if ($became === 'paid') {
            self::afterPaid((int) $id, $source);
        }
        return (string) $outcome;
    }

    /** Once, after money is confirmed: file the certificate request, e-mail the receipt, tell the team. Never undoes the payment if anything here fails. */
    private static function afterPaid(int $id, string $source): void
    {
        try {
            $p = self::find($id);
            if ($p === null) {
                return;
            }
            $learner = $p['learner_id'] !== null ? Database::one('SELECT * FROM academy_learners WHERE id = ?', [(int) $p['learner_id']]) : null;
            $course = $p['course_id'] !== null ? CourseService::find((int) $p['course_id']) : null;
            $waits = true;
            if ($learner !== null && $course !== null) {
                $res = CertificateService::request($learner, $course);
                $waits = (bool) ($res['waiting'] ?? true);          // false = approval is off: the certificate was issued and e-mailed at once
            }
            if ($learner !== null && $p['receipt_emailed_at'] === null && AcademyMail::paymentReceipt($p, $learner, $waits)) {
                Database::update('academy_payments', ['receipt_emailed_at' => date('Y-m-d H:i:s')], 'id = ?', [$id]);
            }
            Mailer::notify('Certificate payment received: ' . mb_substr((string) $p['course_title'], 0, 80),
                $p['learner_name'] . ' (' . $p['learner_email'] . ') paid ' . number_format((float) $p['amount'], 0) . ' ' . $p['currency'] . ' for the certificate of "' . $p['course_title'] . "\".\nReference " . $p['merchant_ref'] . ".\n\nSee it here: " . abs_url('admin/academy/payments'), (string) $p['learner_email']);
        } catch (\Throwable $e) {
            Log::error('Academy payment follow-up failed', ['payment' => $id, 'source' => $source, 'error' => $e::class]);
        }
    }

    // ------------------------------------------------------------------------------------------------ staff

    /** An administrator lets a learner have the certificate without paying (a scholarship, a cash payment taken elsewhere...). */
    public static function waive(array $learner, array $course, string $note, ?int $adminId): int
    {
        $id = Database::insert('academy_payments', [
            'learner_id'    => (int) $learner['id'],
            'course_id'     => (int) $course['id'],
            'learner_name'  => mb_substr((string) $learner['name'], 0, 120),
            'learner_email' => mb_substr((string) $learner['email'], 0, 190),
            'course_title'  => mb_substr((string) $course['title'], 0, 190),
            'merchant_ref'  => 'LAF-' . strtoupper(bin2hex(random_bytes(6))),
            'amount'        => 0,
            'currency'      => self::CURRENCY,
            'status'        => 'waived',
            'source'        => 'manual',
            'note'          => mb_substr(trim($note) !== '' ? trim($note) : 'Waived by an administrator' . ($adminId !== null ? ' (user ' . $adminId . ')' : '') . '.', 0, 255),
            'paid_at'       => date('Y-m-d H:i:s'),
        ]);
        return $id;
    }

    /** A `review` row the staff decided is fine (for example the learner overpaid, or the duplicate is kept as a credit): it counts as paid. */
    public static function accept(int $id, ?int $adminId): bool
    {
        $p = self::find($id);
        if ($p === null || $p['status'] !== 'review') {
            return false;
        }
        Database::update('academy_payments', ['status' => 'paid', 'note' => mb_substr('Accepted by an administrator' . ($adminId !== null ? ' (user ' . $adminId . ')' : '') . '. ' . (string) $p['note'], 0, 255)], 'id = ?', [$id]);
        self::afterPaid($id, 'admin');
        return true;
    }

    /** Re-ask Pesapal about every payment still pending (a cron job: bin/academy-payments-recheck.php). @return int how many rows were looked at */
    public static function recheckPending(int $limit = 100): int
    {
        if (!self::ready() || !self::available()) {
            return 0;
        }
        $n = 0;
        foreach (Database::all("SELECT * FROM academy_payments WHERE status = 'pending' AND created_at > ? ORDER BY id DESC LIMIT " . max(1, min(500, $limit)), [date('Y-m-d H:i:s', time() - 14 * 86400)]) as $row) {
            self::recheck($row, 'cron');
            $n++;
        }
        return $n;
    }

    private static function close(int $id, string $status, string $note): void
    {
        Database::update('academy_payments', ['status' => $status, 'note' => mb_substr($note, 0, 255)], 'id = ? AND status = ?', [$id, 'pending']);
    }
}
