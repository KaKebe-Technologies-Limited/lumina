<?php
declare(strict_types=1);

namespace App\Services\Pesapal;

use App\Core\AuditLog;
use App\Core\Database;
use App\Core\Log;
use App\Core\Mailer;
use App\Services\Shop\Money;
use App\Services\Shop\OrderService;
use App\Services\Shop\PaymentService;

/**
 * Every PESAPAL payment of the site (donations now; the shop checkout too), in one ledger table `pesapal_payments`.
 *
 *   begin()   a row (status pending, unique merchant reference) -> SubmitOrderRequest -> the hosted page to send the customer to
 *   settle()  the ONLY place money is decided: asks Pesapal server-to-server (GetTransactionStatus); a browser URL or an IPN body is never believed.
 *             completed + same reference + same currency + amount >= expected  -> paid (idempotent, row locked); anything else odd -> review
 *   The customer's return page and the IPN call both just call settle(), so whichever arrives first wins and the other is a harmless repeat.
 *
 * purpose = donation (ref_id = donations.id: marked received) | shop (ref_id = shop_orders.id: PaymentService::markPaid, method "pesapal") |
 *           academy_signup (ref_id = academy_learners.id) | academy_cert (ref_id = academy_certificates.id): see App\Services\Academy\AcademyFees.
 */
final class PesapalPayments
{
    public const PURPOSES = ['donation', 'shop', 'academy_signup', 'academy_cert'];

    /**
     * @param array{name?:string,email?:string,phone?:string} $payer
     * @return array{ok:bool,link?:string,reference?:string,error?:string}
     */
    public static function begin(string $purpose, int $refId, int $amountMinor, string $currency, string $description, array $payer): array
    {
        $currency = strtoupper($currency);
        if (!in_array($purpose, self::PURPOSES, true) || $refId < 1 || $amountMinor < 1 || !Money::validCurrency($currency)) {
            return ['ok' => false, 'error' => 'rejected'];
        }
        $reference = 'LAF' . ['donation' => 'D', 'shop' => 'S', 'academy_signup' => 'A', 'academy_cert' => 'C'][$purpose] . $refId . '-' . strtoupper(bin2hex(random_bytes(4)));
        $id = Database::insert('pesapal_payments', [
            'merchant_reference' => $reference,
            'purpose'            => $purpose,
            'ref_id'             => $refId,
            'amount_minor'       => $amountMinor,
            'currency'           => $currency,
            'status'             => 'pending',
            'payer_name'         => mb_substr(trim((string) ($payer['name'] ?? '')), 0, 150),
            'payer_email'        => mb_substr(trim((string) ($payer['email'] ?? '')), 0, 190),
            'payer_phone'        => mb_substr(trim((string) ($payer['phone'] ?? '')), 0, 40),
            'mode'               => PesapalClient::mode(),
        ]);
        $name = trim((string) ($payer['name'] ?? ''));
        $parts = $name === '' ? [] : preg_split('/\s+/', $name, 2);
        $r = PesapalClient::submitOrder([
            'reference'    => $reference,
            'amount'       => (string) Money::gateway($amountMinor, $currency),
            'currency'     => $currency,
            'description'  => $description,
            'callback_url' => abs_url('pay/pesapal/return'),
            'ipn_url'      => abs_url('pay/pesapal/ipn'),
            'email'        => (string) ($payer['email'] ?? ''),
            'phone'        => (string) ($payer['phone'] ?? ''),
            'first_name'   => (string) ($parts[0] ?? ''),
            'last_name'    => (string) ($parts[1] ?? ''),
        ]);
        if (!$r['ok']) {
            Database::update('pesapal_payments', ['status' => 'failed', 'last_error' => (string) $r['error']], 'id = ?', [$id]);
            Log::warning('Pesapal: the payment page could not be opened', ['purpose' => $purpose, 'ref' => $refId, 'error' => (string) $r['error']]);
            return ['ok' => false, 'error' => (string) $r['error']];
        }
        Database::update('pesapal_payments', ['order_tracking_id' => (string) $r['tracking_id']], 'id = ?', [$id]);
        return ['ok' => true, 'link' => (string) $r['redirect_url'], 'reference' => $reference];
    }

    /** @return ?array<string,mixed> */
    public static function byTracking(string $trackingId): ?array
    {
        return preg_match('/^[A-Za-z0-9-]{8,80}$/', $trackingId) === 1
            ? Database::one('SELECT * FROM pesapal_payments WHERE order_tracking_id = ? LIMIT 1', [$trackingId])
            : null;
    }

    /** @return ?array<string,mixed> */
    public static function byReference(string $reference): ?array
    {
        return preg_match('/^[A-Za-z0-9-]{4,60}$/', $reference) === 1
            ? Database::one('SELECT * FROM pesapal_payments WHERE merchant_reference = ? LIMIT 1', [$reference])
            : null;
    }

    /**
     * Ask Pesapal and apply the answer.
     * @return array{state:string,row:?array<string,mixed>}  state: paid | pending | failed | reversed | review | unknown | verify_failed
     */
    public static function settle(string $trackingId): array
    {
        $row = self::byTracking($trackingId);
        if ($row === null) {
            return ['state' => 'unknown', 'row' => null];
        }
        if ($row['status'] === 'paid') {
            return ['state' => 'paid', 'row' => $row];
        }
        $v = PesapalClient::status($trackingId);
        if (!$v['ok']) {
            return ['state' => 'verify_failed', 'row' => $row];
        }
        if ($v['reference'] !== '' && !hash_equals((string) $row['merchant_reference'], $v['reference'])) {
            self::flagReview($row, $v, 'Pesapal answered for a different reference');
            return ['state' => 'review', 'row' => self::fresh((int) $row['id'])];
        }
        if ($v['code'] === 1) {
            $amount = $v['amount'] === null ? null : Money::fromGateway($v['amount']);
            if ($amount === null || $v['currency'] !== $row['currency'] || $amount < (int) $row['amount_minor']) {
                self::flagReview($row, $v, 'Amount or currency does not match what was asked');
                return ['state' => 'review', 'row' => self::fresh((int) $row['id'])];
            }
            return self::pay((int) $row['id'], $v, $amount);
        }
        if ($v['code'] === 2) {
            self::mark((int) $row['id'], 'failed', $v, '');
            self::applyFailure(self::fresh((int) $row['id']) ?? $row);
            return ['state' => 'failed', 'row' => self::fresh((int) $row['id'])];
        }
        if ($v['code'] === 3) {
            self::mark((int) $row['id'], 'reversed', $v, 'Pesapal reports the payment as reversed');
            Mailer::notify('Pesapal payment reversed', 'Pesapal reports a reversal for ' . $row['merchant_reference'] . ' (' . $row['purpose'] . ' #' . $row['ref_id'] . "). Please check it in your Pesapal dashboard.\n", null);
            return ['state' => 'reversed', 'row' => self::fresh((int) $row['id'])];
        }
        return ['state' => 'pending', 'row' => $row];
    }

    /** @param array<string,mixed> $v @return array{state:string,row:?array<string,mixed>} */
    private static function pay(int $id, array $v, int $amount): array
    {
        $claimed = Database::transaction(static function () use ($id, $v): bool {
            $r = Database::one('SELECT status FROM pesapal_payments WHERE id = ? FOR UPDATE', [$id]);
            if ($r === null || $r['status'] === 'paid') {
                return false;
            }
            Database::update('pesapal_payments', [
                'status'       => 'paid',
                'paid_at'      => date('Y-m-d H:i:s'),
                'confirmation' => (string) $v['confirmation'],
                'pay_method'   => (string) $v['method'],
                'last_error'   => null,
            ], 'id = ?', [$id]);
            return true;
        });
        $row = self::fresh($id);
        if ($claimed && $row !== null) {
            self::applySuccess($row, $v, $amount);
        }
        return ['state' => 'paid', 'row' => $row];
    }

    /** @param array<string,mixed> $row @param array<string,mixed> $v */
    private static function applySuccess(array $row, array $v, int $amount): void
    {
        try {
            if ($row['purpose'] === 'donation') {
                for ($try = 0; $try < 5; $try++) {                      // two payments settling at the same moment may pick the same receipt number: the unique key says so, take the next
                    try {
                        Database::execute(
                            "UPDATE donations SET status = 'received', method = 'pesapal', reference = ?, received_at = ?, receipt_no = COALESCE(receipt_no, ?) WHERE id = ? AND status <> 'received'",
                            [(string) ($v['confirmation'] !== '' ? $v['confirmation'] : $row['merchant_reference']), date('Y-m-d H:i:s'), self::nextReceiptNo(), (int) $row['ref_id']]
                        );
                        break;
                    } catch (\PDOException $e) {
                        if ((string) $e->getCode() !== '23000' || $try === 4) {
                            throw $e;
                        }
                    }
                }
                AuditLog::record('donation.pesapal', 'donation', (string) $row['ref_id'], 'Online donation received through Pesapal (' . $row['merchant_reference'] . ')');
                $d = Database::one('SELECT donor_name, donor_email, amount, currency, is_anonymous FROM donations WHERE id = ?', [(int) $row['ref_id']]);
                if ($d !== null) {
                    Mailer::notify('Online donation received', "An online donation was paid through Pesapal.\n\nDonor: " . ((int) $d['is_anonymous'] === 1 ? '(anonymous) ' : '') . $d['donor_name'] . ' <' . $d['donor_email'] . ">\nAmount: "
                        . Money::format($amount, (string) $row['currency']) . "\nReference: " . $row['merchant_reference'] . "\n\nManage it in the admin area: " . abs_url('admin/donations') . "\n", (string) $d['donor_email']);
                }
            } elseif ($row['purpose'] === 'academy_signup') {
                \App\Services\Academy\AcademyFees::markSignupPaid((int) $row['ref_id'], (string) $row['merchant_reference']);
                \App\Services\Academy\AcademyFees::autoApproveAfterPayment((int) $row['ref_id']);
            } elseif ($row['purpose'] === 'academy_cert') {
                \App\Services\Academy\AcademyFees::markCertPaid((int) $row['ref_id'], (string) $row['merchant_reference']);
            } elseif ($row['purpose'] === 'shop') {
                PaymentService::markPaid((int) $row['ref_id'], [
                    'method'     => 'pesapal',
                    'reference'  => (string) $row['merchant_reference'],
                    'amount'     => $amount,
                    'currency'   => (string) $row['currency'],
                    'source'     => 'gateway',
                    'gateway_id' => (string) $row['order_tracking_id'],
                    'meta'       => 'Pesapal ' . $v['method'] . ' ' . $v['confirmation'],
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Pesapal: a paid payment could not be applied', ['ref' => $row['merchant_reference'], 'error' => $e::class]);
        }
    }

    /** "RCT-2026-0007": the next free number of the current year (same series as the admin's manual receipts). */
    private static function nextReceiptNo(): string
    {
        $prefix = 'RCT-' . date('Y') . '-';
        $max = (int) Database::value('SELECT MAX(CAST(SUBSTRING(receipt_no, ?) AS UNSIGNED)) FROM donations WHERE receipt_no LIKE ?', [strlen($prefix) + 1, $prefix . '%'], 0);
        return $prefix . str_pad((string) ($max + 1), 4, '0', STR_PAD_LEFT);
    }

    /** @param array<string,mixed> $row */
    private static function applyFailure(array $row): void
    {
        if ($row['purpose'] === 'shop') {
            try {
                PaymentService::markFailed((int) $row['ref_id'], (string) $row['merchant_reference'], 'The Pesapal payment did not go through.', 'gateway');
            } catch (\Throwable $e) {
                Log::error('Pesapal: a failed payment could not be applied', ['ref' => $row['merchant_reference'], 'error' => $e::class]);
            }
        } elseif ($row['purpose'] === 'donation') {
            Database::execute("UPDATE donations SET status = 'cancelled' WHERE id = ? AND status = 'pledged' AND source = 'online'", [(int) $row['ref_id']]);
        }
    }

    /** Pesapal says something we cannot accept as payment: keep the row for a person to look at, and flag the shop order so staff see it. @param array<string,mixed> $row @param array<string,mixed> $v */
    private static function flagReview(array $row, array $v, string $note): void
    {
        self::mark((int) $row['id'], 'review', $v, $note);
        if ($row['purpose'] === 'shop') {
            try {
                PaymentService::markReview((int) $row['ref_id'], (string) $row['merchant_reference'], $note . ' (Pesapal ' . $row['merchant_reference'] . ')');
            } catch (\Throwable $e) {
                Log::error('Pesapal: a payment could not be flagged for review', ['ref' => $row['merchant_reference'], 'error' => $e::class]);
            }
        }
    }

    /** @param array<string,mixed> $v */
    private static function mark(int $id, string $status, array $v, string $note): void
    {
        Database::update('pesapal_payments', [
            'status'     => $status,
            'pay_method' => (string) $v['method'],
            'last_error' => $note !== '' ? $note : null,
        ], 'id = ? AND status <> ?', [$id, 'paid']);
    }

    /** @return ?array<string,mixed> */
    private static function fresh(int $id): ?array
    {
        return Database::one('SELECT * FROM pesapal_payments WHERE id = ?', [$id]);
    }

    /** Pending payments older than five minutes, newest first (for "check again"). @return list<array<string,mixed>> */
    public static function recent(int $limit = 50): array
    {
        return Database::all('SELECT * FROM pesapal_payments ORDER BY id DESC LIMIT ' . max(1, min(200, $limit)));
    }

    /** Is the gateway usable right now? (keys present) */
    public static function ready(): bool
    {
        return PesapalClient::hasKeys();
    }

    /** The owner's choice (Settings > Donations > "How online donations are taken", or the tick box on Admin > Pesapal payments): both | pesapal | link. */
    public static function donateMethod(): string
    {
        $m = (string) \App\Core\Settings::get('donate_method', 'both');
        return in_array($m, ['both', 'pesapal', 'link'], true) ? $m : 'both';
    }

    /** Is the Donate page's "Give online" form switched on (any choice but "link only")? */
    public static function donationsSwitch(): bool
    {
        return self::donateMethod() !== 'link';
    }

    /** Does Pesapal REPLACE the online donation link (choice "pesapal" and the gateway is ready)? Then the donate buttons open the form of this site. */
    public static function donationsOnSite(): bool
    {
        return self::donateMethod() === 'pesapal' && self::ready();
    }

    /** Is online giving available to visitors? keys + the switch. */
    public static function donationsEnabled(): bool
    {
        return self::ready() && self::donationsSwitch();
    }

    /** The shop order of a row, or null. @return ?array<string,mixed> */
    public static function shopOrder(array $row): ?array
    {
        return $row['purpose'] === 'shop' ? OrderService::find((int) $row['ref_id']) : null;
    }
}
