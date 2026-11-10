<?php
declare(strict_types=1);

namespace App\Services\DarazaPay;

use App\Core\AuditLog;
use App\Core\Database;
use App\Core\Log;
use App\Core\Mailer;
use App\Core\Settings;
use App\Services\Shop\Money;

/**
 * MOBILE-MONEY payments through the Daraza API (MTN and Airtel), in one ledger table `daraza_payments`. Collections ("in") are what payers pay; payouts ("out") are money the
 * administrators send (refunds, prizes) with the remittance call. Purposes: academy_signup (ref_id = academy_learners.id), academy_cert (academy_certificates.id), donation (donations.id).
 *
 *   begin()     a row (pending, unique reference) and the address of the payment page /pay/mobile-money/{reference}
 *   collect()   the payer typed the phone number: ask Daraza to collect. The call is held open until the payer approves on the phone (up to 5 minutes). Success = paid and applied once.
 *               A call that never comes back is "unknown": nothing is assumed; check() asks Daraza again with the request id.
 * Amounts are charged in UGX. A fee set in USD is converted with the rate in Settings (daraza_api_usd_rate, UGX per 1 USD), rounded UP to the next 100 UGX; other currencies are not offered.
 */
final class DarazaPayments
{
    public const PURPOSES = ['donation' => 'Donations', 'project' => 'Gifts to a project', 'shop' => 'Shop orders', 'academy_signup' => 'Academy sign-up fee', 'academy_cert' => 'Academy certificate fee', 'membership' => 'Membership payments'];
    private const BUSY_SECONDS = 400;

    private static ?bool $ready = null;

    public static function install(): bool
    {
        if (self::$ready !== null) {
            return self::$ready;
        }
        try {
            Database::execute("CREATE TABLE IF NOT EXISTS `daraza_payments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT, `reference` VARCHAR(40) NOT NULL, `direction` VARCHAR(3) NOT NULL DEFAULT 'in', `purpose` VARCHAR(20) NOT NULL, `ref_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `amount_minor` BIGINT UNSIGNED NOT NULL DEFAULT 0, `currency` CHAR(3) NOT NULL DEFAULT 'UGX', `amount_ugx` INT UNSIGNED NOT NULL, `phone` VARCHAR(20) NULL, `method` TINYINT UNSIGNED NULL,
  `status` VARCHAR(10) NOT NULL DEFAULT 'pending', `request_id` VARCHAR(80) NULL, `message` VARCHAR(255) NULL, `mode` VARCHAR(10) NOT NULL DEFAULT 'sandbox', `payer_name` VARCHAR(150) NOT NULL DEFAULT '',
  `payer_email` VARCHAR(190) NOT NULL DEFAULT '', `note` VARCHAR(150) NULL, `created_by` INT UNSIGNED NULL, `paid_at` DATETIME NULL, `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, PRIMARY KEY (`id`), UNIQUE KEY `uq_daraza_reference` (`reference`), KEY `ix_daraza_target` (`purpose`, `ref_id`),
  KEY `ix_daraza_status` (`status`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            return self::$ready = true;
        } catch (\Throwable) {
            return self::$ready = false;
        }
    }

    // ------------------------------------------------------------------------------------------------------------- settings

    public static function rate(): int
    {
        return max(0, (int) Settings::get('daraza_api_usd_rate', '0'));
    }

    /** The places that offer mobile money: the switch is on, the key is in .env and the amount can be charged in UGX. */
    public static function on(string $purpose): bool
    {
        return isset(self::PURPOSES[$purpose]) && (string) Settings::get('daraza_api_' . $purpose . '_on', '0') === '1';
    }

    public static function enabled(string $purpose): bool
    {
        return self::on($purpose) && DarazaApi::hasKey() && self::install();
    }

    public const MIN_UGX = 500;
    public const MAX_UGX = 5000000;

    /** Minor units + currency -> whole UGX (rounded up to 100 for dollars), or null when it cannot be turned into UGX. No limits applied. */
    public static function rawUgx(int $minor, string $currency): ?int
    {
        $currency = strtoupper($currency);
        $major = $minor / Money::SCALE;
        if ($currency === 'UGX') {
            return (int) ceil($major);
        }
        if ($currency === 'USD' && self::rate() > 0) {
            return (int) (ceil($major * self::rate() / 100) * 100);
        }
        return null;
    }

    /** Minor units + currency -> whole UGX (rounded up to 100), or null when it cannot be charged in UGX or is out of range (500 to 5,000,000). */
    public static function ugx(int $minor, string $currency): ?int
    {
        $u = self::rawUgx($minor, $currency);
        return $u !== null && $u >= self::MIN_UGX && $u <= self::MAX_UGX ? $u : null;
    }

    // ------------------------------------------------------------------------------------------------------------- collect

    /**
     * @param array{name?:string,email?:string,phone?:string} $payer
     * @return array{ok:bool,reference?:string,url?:string,error?:string}
     */
    public static function begin(string $purpose, int $refId, int $amountMinor, string $currency, array $payer): array
    {
        $ugx = self::ugx($amountMinor, $currency);
        if (!self::enabled($purpose) || $refId < 1 || $ugx === null) {
            return ['ok' => false, 'error' => 'unavailable'];
        }
        // the same payer coming back: keep the open attempt instead of piling up rows
        $open = Database::one("SELECT * FROM daraza_payments WHERE direction = 'in' AND purpose = ? AND ref_id = ? AND status IN ('pending','failed') AND amount_ugx = ? AND created_at > (NOW() - INTERVAL 2 HOUR) ORDER BY id DESC LIMIT 1", [$purpose, $refId, $ugx]);
        if ($open !== null) {
            return ['ok' => true, 'reference' => (string) $open['reference'], 'url' => abs_url('pay/mobile-money/' . $open['reference'])];
        }
        $reference = 'DZ' . strtoupper(substr($purpose, 0, 1)) . $refId . '-' . strtoupper(bin2hex(random_bytes(6)));
        Database::insert('daraza_payments', [
            'reference' => $reference, 'direction' => 'in', 'purpose' => $purpose, 'ref_id' => $refId, 'amount_minor' => $amountMinor, 'currency' => strtoupper($currency), 'amount_ugx' => $ugx,
            'status' => 'pending', 'mode' => DarazaApi::mode(), 'payer_name' => mb_substr(trim((string) ($payer['name'] ?? '')), 0, 150), 'payer_email' => mb_substr(trim((string) ($payer['email'] ?? '')), 0, 190),
            'phone' => (string) ($payer['phone'] ?? '') !== '' ? mb_substr((string) $payer['phone'], 0, 20) : null,
        ]);
        return ['ok' => true, 'reference' => $reference, 'url' => abs_url('pay/mobile-money/' . $reference)];
    }

    /**
     * Start (or restart) the mobile-money payment of a SHOP ORDER. The amount is the order total, read from the order, never from the browser.
     * @param array<string,mixed> $order a shop_orders row
     * @return array{ok:bool,url?:string,error?:string}  errors: unavailable | not_payable | already_paid
     */
    public static function beginShop(array $order): array
    {
        $fresh = \App\Services\Shop\OrderService::find((int) $order['id']);
        if ($fresh === null || !self::enabled('shop')) {
            return ['ok' => false, 'error' => 'unavailable'];
        }
        if ($fresh['payment_status'] === 'paid') {
            return ['ok' => false, 'error' => 'already_paid'];
        }
        $deposit = \App\Services\Shop\CodDeposit::requiredFor($fresh);                    // pay on delivery: the DEPOSIT is paid now, the balance on delivery
        if (!($fresh['payment_method'] === 'mobile' || $deposit) || $fresh['status'] !== 'pending' || !in_array($fresh['payment_status'], ['unpaid', 'pending', 'failed'], true)) {
            return ['ok' => false, 'error' => $deposit && (int) \App\Services\Shop\PaymentService::paidMinor((int) $fresh['id']) > 0 ? 'already_paid' : 'not_payable'];
        }
        $total = Money::fromDb($fresh['total']);
        $amount = $deposit ? \App\Services\Shop\CodDeposit::amountMinor($total, (string) $fresh['currency']) : $total;
        $r = self::begin('shop', (int) $fresh['id'], $amount, (string) $fresh['currency'], ['name' => (string) $fresh['customer_name'], 'email' => (string) $fresh['customer_email'], 'phone' => (string) ($fresh['customer_phone'] ?? '')]);
        if ($r['ok']) {
            Database::execute("UPDATE shop_orders SET payment_status = 'pending', hold_until = ? WHERE id = ? AND payment_status IN ('unpaid','failed')", [\App\Services\Shop\OrderService::holdUntil($deposit ? 'cod' : 'mobile'), (int) $fresh['id']]);
        }
        return $r;
    }

    /** @return ?array<string,mixed> */
    public static function byReference(string $reference): ?array
    {
        return preg_match('/^[A-Za-z0-9-]{6,40}$/', $reference) === 1 ? Database::one('SELECT * FROM daraza_payments WHERE reference = ? LIMIT 1', [$reference]) : null;
    }

    /** @return ?array<string,mixed> */
    private static function fresh(int $id): ?array
    {
        return Database::one('SELECT * FROM daraza_payments WHERE id = ?', [$id]);
    }

    /**
     * Ask Daraza to collect from this phone, and wait for the answer (up to 5 minutes). Idempotent for a paid row; refuses a second call while one is open.
     * @return array{state:string,message:string,row:?array<string,mixed>} state: paid | failed | unknown | busy | invalid | unavailable
     */
    public static function collect(string $reference, string $phone, int $method): array
    {
        $row = self::byReference($reference);
        if ($row === null || $row['direction'] !== 'in') {
            return ['state' => 'invalid', 'message' => 'This payment was not found.', 'row' => null];
        }
        if ($row['status'] === 'paid') {
            return ['state' => 'paid', 'message' => 'This payment is already confirmed.', 'row' => $row];
        }
        $network = DarazaApi::networkOf($phone);
        if (DarazaApi::local($phone) === null) {
            return ['state' => 'invalid', 'message' => 'Please type a Ugandan mobile-money number, for example 0772 123 456.', 'row' => $row];
        }
        if (!in_array($method, [1, 2], true)) {
            $method = (int) $network;
        }
        if (!in_array($method, [1, 2], true)) {
            return ['state' => 'invalid', 'message' => 'Choose MTN Mobile Money or Airtel Money.', 'row' => $row];
        }
        if ($row['status'] === 'collecting' && strtotime((string) $row['updated_at']) > time() - self::BUSY_SECONDS) {
            return ['state' => 'busy', 'message' => 'A payment request is already waiting for your approval on the phone.', 'row' => $row];
        }
        $claimed = Database::execute("UPDATE daraza_payments SET status = 'collecting', phone = ?, method = ?, message = NULL WHERE id = ? AND status <> 'paid' AND (status <> 'collecting' OR updated_at < (NOW() - INTERVAL " . self::BUSY_SECONDS . " SECOND))", [DarazaApi::e164($phone), $method, (int) $row['id']]);
        if ($claimed < 1) {
            return ['state' => 'busy', 'message' => 'A payment request is already waiting for your approval on the phone.', 'row' => self::fresh((int) $row['id'])];
        }
        ignore_user_abort(true);                          // the payer may close the page: the answer is still written down
        $r = DarazaApi::requestToPay($method, (int) $row['amount_ugx'], (string) DarazaApi::e164($phone), self::note($row));
        if ($r['ok']) {
            return self::pay((int) $row['id'], $r['request_id'], $r['message']);
        }
        if ($r['state'] === 'unknown') {
            Database::update('daraza_payments', ['status' => 'unknown', 'message' => mb_substr($r['message'], 0, 250)], 'id = ?', [(int) $row['id']]);
            return ['state' => 'unknown', 'message' => $r['message'], 'row' => self::fresh((int) $row['id'])];
        }
        Database::update('daraza_payments', ['status' => 'failed', 'request_id' => $r['request_id'] !== '' ? $r['request_id'] : null, 'message' => mb_substr($r['message'], 0, 250)], 'id = ?', [(int) $row['id']]);
        if ($r['state'] === 'auth') {
            Log::warning('Daraza: the key was refused');
        }
        return ['state' => $r['state'] === 'auth' ? 'unavailable' : 'failed', 'message' => $r['state'] === 'auth' ? 'Mobile-money payment is not available right now. Please try another way to pay.' : $r['message'], 'row' => self::fresh((int) $row['id'])];
    }

    private static function note(array $row): string
    {
        return mb_substr('LAF ' . (self::PURPOSES[$row['purpose']] ?? 'payment') . ' ' . $row['reference'], 0, 150);
    }

    /** Mark paid ONCE and apply it. @return array{state:string,message:string,row:?array<string,mixed>} */
    private static function pay(int $id, string $requestId, string $message): array
    {
        $claimed = Database::transaction(static function () use ($id, $requestId, $message): bool {
            $r = Database::one('SELECT status FROM daraza_payments WHERE id = ? FOR UPDATE', [$id]);
            if ($r === null || $r['status'] === 'paid') {
                return false;
            }
            Database::update('daraza_payments', ['status' => 'paid', 'paid_at' => date('Y-m-d H:i:s'), 'request_id' => $requestId !== '' ? $requestId : null, 'message' => mb_substr($message, 0, 250)], 'id = ?', [$id]);
            return true;
        });
        $row = self::fresh($id);
        if ($claimed && $row !== null) {
            self::apply($row);
        }
        return ['state' => 'paid', 'message' => 'Payment received. Thank you!', 'row' => $row];
    }

    /** Ask Daraza again about a payment whose answer never came back (or is still pending). @return array{state:string,message:string,row:?array<string,mixed>} */
    public static function check(int $id): array
    {
        $row = self::fresh($id);
        if ($row === null) {
            return ['state' => 'invalid', 'message' => 'Not found.', 'row' => null];
        }
        if ($row['status'] === 'paid') {
            return ['state' => 'paid', 'message' => 'Already confirmed.', 'row' => $row];
        }
        if ((string) $row['request_id'] === '') {
            return ['state' => 'unknown', 'message' => 'Daraza never gave a request number for this attempt, so it cannot be checked. Look for it in the Daraza dashboard.', 'row' => $row];
        }
        $s = DarazaApi::status((string) $row['request_id'], $row['direction'] === 'out' ? 'Remittance' : 'Request_to_Pay');
        if (!$s['ok']) {
            return ['state' => 'unknown', 'message' => $s['message'], 'row' => $row];
        }
        if ($s['successful'] === true) {
            if ($row['direction'] === 'out') {
                Database::update('daraza_payments', ['status' => 'paid', 'paid_at' => date('Y-m-d H:i:s')], 'id = ?', [$id]);
                return ['state' => 'paid', 'message' => 'The payout went through.', 'row' => self::fresh($id)];
            }
            return self::pay($id, (string) $row['request_id'], 'Confirmed by Daraza');
        }
        Database::update('daraza_payments', ['status' => 'failed', 'message' => 'Daraza says it was not successful'], 'id = ? AND status <> ?', [$id, 'paid']);
        return ['state' => 'failed', 'message' => 'Daraza says it was not successful.', 'row' => self::fresh($id)];
    }

    /** @param array<string,mixed> $row */
    private static function apply(array $row): void
    {
        try {
            $ref = (string) $row['reference'];
            if ($row['purpose'] === 'academy_signup') {
                \App\Services\Academy\AcademyFees::markSignupPaid((int) $row['ref_id'], $ref);
                \App\Services\Academy\AcademyFees::autoApproveAfterPayment((int) $row['ref_id']);
            } elseif ($row['purpose'] === 'academy_cert') {
                \App\Services\Academy\AcademyFees::markCertPaid((int) $row['ref_id'], $ref);
            } elseif ($row['purpose'] === 'shop') {
                $codOrder = (string) Database::value('SELECT payment_method FROM shop_orders WHERE id = ?', [(int) $row['ref_id']], '') === 'cod';
                \App\Services\Shop\PaymentService::markPaid((int) $row['ref_id'], [
                    'method' => 'mobile', 'deposit' => $codOrder, 'reference' => $ref, 'amount' => (int) $row['amount_minor'], 'currency' => (string) $row['currency'], 'source' => 'gateway',
                    'gateway_id' => (string) ($row['request_id'] ?? ''), 'meta' => 'Daraza mobile money ' . ((int) ($row['method'] ?? 0) === 2 ? 'Airtel' : 'MTN'),
                ]);
            } elseif ($row['purpose'] === 'membership') {
                \App\Services\Membership\MembershipService::recordPayment((int) $row['ref_id'], [
                    'amount' => number_format((int) $row['amount_minor'] / Money::SCALE, 2, '.', ''), 'currency' => (string) $row['currency'], 'status' => 'received', 'method' => 'mobile_money',
                    'reference' => $ref, 'purpose' => 'membership', 'paid_at' => date('Y-m-d H:i'), 'notes' => 'Paid by mobile money through Daraza',
                ]);
            } elseif ($row['purpose'] === 'donation' || $row['purpose'] === 'project') {
                for ($try = 0; $try < 5; $try++) {
                    try {
                        Database::execute("UPDATE donations SET status = 'received', method = 'mobile_money', reference = ?, received_at = ?, receipt_no = COALESCE(receipt_no, ?) WHERE id = ? AND status <> 'received'",
                            [(string) ($row['request_id'] ?: $ref), date('Y-m-d H:i:s'), \App\Services\Pesapal\PesapalPayments::nextReceiptNo(), (int) $row['ref_id']]);
                        break;
                    } catch (\PDOException $e) {
                        if ((string) $e->getCode() !== '23000' || $try === 4) {
                            throw $e;
                        }
                    }
                }
                $d = Database::one('SELECT donor_name, donor_email, is_anonymous FROM donations WHERE id = ?', [(int) $row['ref_id']]);
                Mailer::notify('Mobile-money donation received', "A donation was paid by mobile money through Daraza.\n\nDonor: " . ((int) ($d['is_anonymous'] ?? 0) === 1 ? '(anonymous) ' : '') . ($d['donor_name'] ?? '') . ' <' . ($d['donor_email'] ?? '') . ">\nAmount: UGX "
                    . number_format((int) $row['amount_ugx']) . "\nReference: " . $ref . "\n\nManage it: " . abs_url('admin/donations') . "\n", (string) ($d['donor_email'] ?? ''));
            }
            AuditLog::record('daraza.payment', 'daraza_payment', (string) $row['id'], 'Mobile-money payment received (' . (self::PURPOSES[$row['purpose']] ?? $row['purpose']) . ', UGX ' . number_format((int) $row['amount_ugx']) . ')');
        } catch (\Throwable $e) {
            Log::error('Daraza: a paid payment could not be applied', ['ref' => $row['reference'], 'error' => $e::class]);
        }
    }

    // ------------------------------------------------------------------------------------------------------------- payouts

    /**
     * Send money to a phone (remittance). Written to the ledger whatever happens. @return array{ok:bool,message:string,row:?array<string,mixed>}
     */
    public static function payout(?int $adminId, string $phone, int $method, int $amountUgx, string $note): array
    {
        if (!self::install()) {
            return ['ok' => false, 'message' => 'The payments table could not be created.', 'row' => null];
        }
        $network = DarazaApi::networkOf($phone);
        $method = in_array($method, [1, 2], true) ? $method : (int) $network;
        if (DarazaApi::local($phone) === null || !in_array($method, [1, 2], true) || $amountUgx < 500 || $amountUgx > 5000000) {
            return ['ok' => false, 'message' => 'Check the phone number (Ugandan MTN or Airtel), the network and the amount (UGX 500 to 5,000,000).', 'row' => null];
        }
        $reference = 'DZO-' . strtoupper(bin2hex(random_bytes(6)));
        $id = (int) Database::insert('daraza_payments', ['reference' => $reference, 'direction' => 'out', 'purpose' => 'payout', 'ref_id' => 0, 'amount_ugx' => $amountUgx, 'currency' => 'UGX', 'phone' => DarazaApi::e164($phone),
            'method' => $method, 'status' => 'collecting', 'mode' => DarazaApi::mode(), 'note' => mb_substr(trim($note), 0, 150), 'created_by' => $adminId]);
        ignore_user_abort(true);
        $r = DarazaApi::remit($method, $amountUgx, (string) DarazaApi::e164($phone), $note !== '' ? $note : 'LAF payout ' . $reference);
        $status = $r['ok'] ? 'paid' : ($r['state'] === 'unknown' ? 'unknown' : 'failed');
        Database::update('daraza_payments', ['status' => $status, 'request_id' => $r['request_id'] !== '' ? $r['request_id'] : null, 'message' => mb_substr($r['message'], 0, 250), 'paid_at' => $r['ok'] ? date('Y-m-d H:i:s') : null], 'id = ?', [$id]);
        AuditLog::record('daraza.payout', 'daraza_payment', (string) $id, 'Sent UGX ' . number_format($amountUgx) . ' to ' . substr((string) DarazaApi::e164($phone), 0, 8) . '*** (' . $status . ')');
        return ['ok' => $r['ok'], 'message' => $r['ok'] ? 'The money was sent.' : $r['message'], 'row' => self::fresh($id)];
    }

    /** @return list<array<string,mixed>> */
    public static function recent(int $limit = 40): array
    {
        return self::install() ? Database::all('SELECT * FROM daraza_payments ORDER BY id DESC LIMIT ' . max(1, min(200, $limit))) : [];
    }
}
