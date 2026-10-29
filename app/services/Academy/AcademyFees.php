<?php
declare(strict_types=1);

namespace App\Services\Academy;

use App\Core\Database;
use App\Core\Mailer;
use App\Core\Settings;
use App\Services\Pesapal\PesapalPayments;
use App\Services\Shop\Money;

/**
 * The two fees of the LAF Academy, paid through Pesapal. The administrator sets the amounts (Admin > Pesapal payments, or Settings > LAF Academy); 0 or empty = free.
 *
 *   SIGN-UP FEE       a new learner pays after creating the account. Until it is paid the account cannot sign in (it stays "due"); an administrator can WAIVE it.
 *                     After the payment the usual approval rules apply (an administrator approves, or the account is open at once, depending on the sign-up mode).
 *   CERTIFICATE FEE   a certificate earned by passing a final exam is issued with the fee "due": it does not become available until the fee is paid AND (when the
 *                     setting asks for it) an administrator approves it. An administrator can waive the fee of one certificate.
 *
 * A fee is only CHARGED when its amount is above 0, Pesapal is connected (keys in .env) and the database has the fee columns. Otherwise nobody is blocked: the Academy
 * behaves as it did before, and the admin page says why the fee is not active. Accounts and certificates that exist already are never charged.
 */
final class AcademyFees
{
    public const CURRENCIES = ['UGX', 'USD', 'EUR', 'GBP', 'KES', 'TZS', 'RWF'];

    /** Do the fee columns exist (database update imported)? */
    public static function ready(): bool
    {
        static $ok = null;
        if ($ok === null) {
            try {
                $ok = (int) Database::value("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND ((table_name = 'academy_learners' AND column_name = 'fee_status') OR (table_name = 'academy_certificates' AND column_name = 'fee_status'))", [], 0) === 2;
            } catch (\Throwable) {
                $ok = false;
            }
        }
        return $ok;
    }

    /** @return array{minor:int,currency:string,display:string}|null the amount set by the administrator (regardless of whether it can be charged yet) */
    public static function configured(string $kind): ?array
    {
        $key = $kind === 'cert' ? 'academy_cert_fee' : 'academy_signup_fee';
        $minor = Money::parse(trim((string) Settings::get($key, '0')));
        if ($minor === null || $minor <= 0) {
            return null;
        }
        $cur = strtoupper((string) Settings::get($key . '_currency', 'UGX'));
        $cur = in_array($cur, self::CURRENCIES, true) && Money::validCurrency($cur) ? $cur : 'UGX';
        return ['minor' => $minor, 'currency' => $cur, 'display' => Money::format($minor, $cur)];
    }

    /** The fee that is actually charged now, or null. @return array{minor:int,currency:string,display:string}|null */
    public static function active(string $kind): ?array
    {
        return self::ready() && (PesapalPayments::ready() || \App\Services\Daraza::enabled($kind === 'cert' ? 'cert' : 'signup') || self::manual() !== null) ? self::configured($kind) : null;
    }

    /** The mobile-money and bank details learners may pay to (Settings > LAF Academy), or null when the administrator gave none. @return array{mobile:string,bank:string}|null */
    public static function manual(): ?array
    {
        $m = trim((string) \App\Core\Settings::get('academy_pay_mobile_money', ''));
        $b = trim((string) \App\Core\Settings::get('academy_pay_bank_details', ''));
        return $m !== '' || $b !== '' ? ['mobile' => $m, 'bank' => $b] : null;
    }

    /** Do the columns that hold a reported payment exist (2026-10-academy-fee-proof.sql imported)? */
    public static function reportReady(): bool
    {
        static $ok = null;
        if ($ok === null) {
            try {
                $ok = (int) Database::value("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'academy_learners' AND column_name IN ('fee_reference','fee_proof_path','fee_reported_at')", [], 0) === 3;
            } catch (\Throwable) {
                $ok = false;
            }
        }
        return $ok;
    }

    /**
     * The applicant says they paid (mobile money, bank or Daraza): the transaction ID, and optionally the receipt (image or PDF, kept PRIVATE). Nothing is approved by this:
     * an administrator looks at the money and presses "Mark the fee as paid".
     * @param ?array<string,mixed> $file one entry of $_FILES / Request::file()
     * @return array{ok:bool,error?:string}
     */
    public static function report(int $learnerId, string $reference, ?array $file): array
    {
        if (!self::reportReady()) {
            return ['ok' => false, 'error' => 'unavailable'];
        }
        $reference = trim((string) preg_replace('/\s+/u', ' ', $reference));
        if (preg_match('/^[A-Za-z0-9][A-Za-z0-9 ._\/-]{3,59}$/', $reference) !== 1) {
            return ['ok' => false, 'error' => 'Please type the transaction ID or receipt number exactly as it appears on your payment message (4 to 60 letters or digits).'];
        }
        $l = Database::one('SELECT id, name, email, reg_no, fee_status, fee_proof_path FROM academy_learners WHERE id = ?', [$learnerId]);
        if ($l === null || $l['fee_status'] !== 'due') {
            return ['ok' => false, 'error' => 'There is no unpaid sign-up fee on this account.'];
        }
        $path = null;
        if ($file !== null && (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $path = self::storeProof($file);
            if ($path === null) {
                return ['ok' => false, 'error' => 'The receipt could not be saved. Please attach a JPG, PNG, WebP or PDF of at most 5 MB, or leave it out and just send the transaction ID.'];
            }
        }
        $set = ['fee_reference' => mb_substr($reference, 0, 120), 'fee_reported_at' => date('Y-m-d H:i:s')];
        if ($path !== null) {
            $set['fee_proof_path'] = $path;
            if ((string) ($l['fee_proof_path'] ?? '') !== '') {
                \App\Core\Uploader::deletePrivate((string) $l['fee_proof_path']);
            }
        }
        Database::update('academy_learners', $set, 'id = ?', [$learnerId]);
        Mailer::notify('Academy sign-up payment reported', (string) $l['name'] . ' (' . (string) $l['email'] . ', ' . (string) ($l['reg_no'] ?? '-') . ") says the sign-up fee was paid.\n\nTransaction ID: " . $reference . ($path !== null ? "\nA receipt is attached to the account." : '') . "\n\nCheck the money, then press \"Mark the fee as paid\": " . abs_url('admin/academy/learners/' . $learnerId) . "\n", null);
        return ['ok' => true];
    }

    /** The "Pay with Daraza" button of a fee ('signup' | 'cert'): ['url' => ..., 'label' => ...] or null. Daraza payments are confirmed by an administrator (Mark as paid). */
    public static function daraza(string $kind): ?array
    {
        $place = $kind === 'cert' ? 'cert' : 'signup';
        return \App\Services\Daraza::enabled($place) ? ['url' => \App\Services\Daraza::url($place), 'label' => \App\Services\Daraza::label()] : null;
    }

    public static function signup(): ?array
    {
        return self::active('signup');
    }

    public static function cert(): ?array
    {
        return self::active('cert');
    }

    /** Does this learner still owe the sign-up fee? (also true while the fee is switched off again: an unpaid learner stays blocked until waived or paid) */
    public static function signupDue(?array $learner): bool
    {
        return $learner !== null && ($learner['fee_status'] ?? 'none') === 'due';
    }

    public static function certDue(?array $cert): bool
    {
        return $cert !== null && ($cert['fee_status'] ?? 'none') === 'due';
    }

    // ------------------------------------------------------------------------------------------------------------- paying

    /** @return array{ok:bool,link?:string,error?:string} */
    public static function beginSignup(int $learnerId): array
    {
        $l = Database::one('SELECT id, name, email, phone, fee_status FROM academy_learners WHERE id = ?', [$learnerId]);
        $fee = self::configured('signup');
        if ($l === null || $fee === null || !PesapalPayments::ready()) {
            return ['ok' => false, 'error' => 'unavailable'];
        }
        if ($l['fee_status'] !== 'due') {
            return ['ok' => false, 'error' => 'not_due'];
        }
        return PesapalPayments::begin('academy_signup', $learnerId, $fee['minor'], $fee['currency'], 'LAF Academy sign-up fee', ['name' => (string) $l['name'], 'email' => (string) $l['email'], 'phone' => (string) ($l['phone'] ?? '')]);
    }

    /** @return array{ok:bool,link?:string,error?:string} */
    public static function beginCert(int $certId, int $learnerId): array
    {
        $c = Database::one('SELECT id, learner_id, course_title, fee_status, revoked_at FROM academy_certificates WHERE id = ?', [$certId]);
        $l = Database::one('SELECT id, name, email, phone FROM academy_learners WHERE id = ?', [$learnerId]);
        $fee = self::configured('cert');
        if ($c === null || $l === null || (int) $c['learner_id'] !== $learnerId || $fee === null || !PesapalPayments::ready()) {
            return ['ok' => false, 'error' => 'unavailable'];
        }
        if ($c['fee_status'] !== 'due' || $c['revoked_at'] !== null) {
            return ['ok' => false, 'error' => 'not_due'];
        }
        return PesapalPayments::begin('academy_cert', $certId, $fee['minor'], $fee['currency'], 'LAF Academy certificate fee', ['name' => (string) $l['name'], 'email' => (string) $l['email'], 'phone' => (string) ($l['phone'] ?? '')]);
    }

    public const PROOF_DIR = 'academy/proofs';
    public const PROOF_MAX_BYTES = 5 * 1024 * 1024;

    /**
     * Keep a receipt PRIVATELY (storage/private/academy/proofs): a PDF is validated and kept, a picture is re-encoded without metadata. Returns the path below
     * storage/private, or null when the file is not acceptable.
     * @param array<string,mixed> $file one entry of $_FILES
     */
    private static function storeProof(array $file): ?string
    {
        $tmp = (string) ($file['tmp_name'] ?? '');
        if ((int) ($file['error'] ?? 0) !== UPLOAD_ERR_OK || $tmp === '' || (!is_uploaded_file($tmp) && empty($file['_selftest'])) || (int) ($file['size'] ?? 0) > self::PROOF_MAX_BYTES) {
            return null;
        }
        $dirAbs = STORAGE_PATH . '/private/' . self::PROOF_DIR;
        if (!is_dir($dirAbs) && !@mkdir($dirAbs, 0775, true) && !is_dir($dirAbs)) {
            return null;
        }
        try {
            if (str_starts_with((string) file_get_contents($tmp, false, null, 0, 8), '%PDF-')) {
                \App\Core\PdfValidator::inspect($tmp, self::PROOF_MAX_BYTES, true);
                $rel = self::PROOF_DIR . '/' . bin2hex(random_bytes(16)) . '.pdf';
                if (!@copy($tmp, STORAGE_PATH . '/private/' . $rel)) {
                    return null;
                }
                @chmod(STORAGE_PATH . '/private/' . $rel, 0640);
                return $rel;
            }
            $first = self::PROOF_DIR . '/' . bin2hex(random_bytes(16)) . '.jpg';
            $res = \App\Core\ImageOptimizer::evidence($tmp, STORAGE_PATH . '/private/' . $first, ['max_bytes' => self::PROOF_MAX_BYTES]);
            if ($res['ext'] === 'jpg') {
                return $first;
            }
            $rel = self::PROOF_DIR . '/' . bin2hex(random_bytes(16)) . '.' . $res['ext'];
            if (!@rename(STORAGE_PATH . '/private/' . $first, STORAGE_PATH . '/private/' . $rel)) {
                @unlink(STORAGE_PATH . '/private/' . $first);
                return null;
            }
            return $rel;
        } catch (\Throwable) {
            return null;
        }
    }

    /** An administrator checked the money (Daraza dashboard, cash, ...) and marks the fee as paid. @return bool false when the fee was not due */
    public static function markPaidByAdmin(string $kind, int $id): bool
    {
        $ref = 'confirmed by an administrator';
        if ($kind === 'cert') {
            $due = AcademyFees::certDue(Database::one('SELECT fee_status FROM academy_certificates WHERE id = ?', [$id]));
            if ($due) {
                self::markCertPaid($id, $ref);
            }
            return $due;
        }
        $due = self::signupDue(Database::one('SELECT fee_status FROM academy_learners WHERE id = ?', [$id]));
        if ($due) {
            self::markSignupPaid($id, $ref);
        }
        return $due;
    }

    /** Called by PesapalPayments when Pesapal confirms the sign-up fee (once). */
    public static function markSignupPaid(int $learnerId, string $reference): void
    {
        $n = Database::execute("UPDATE academy_learners SET fee_status = 'paid', fee_paid_at = ? WHERE id = ? AND fee_status = 'due'", [date('Y-m-d H:i:s'), $learnerId]);
        if ($n > 0) {
            $l = Database::one('SELECT name, email, reg_no FROM academy_learners WHERE id = ?', [$learnerId]);
            Mailer::notify('Academy sign-up fee paid', 'A new learner paid the LAF Academy sign-up fee through Pesapal.' . "\n\n" . 'Name: ' . ($l['name'] ?? '') . "\nE-mail: " . ($l['email'] ?? '')
                . "\nRegistration number: " . ($l['reg_no'] ?? '-') . "\nReference: " . $reference . "\n\nManage the account: " . abs_url('admin/academy/learners/' . $learnerId) . "\n", null);
        }
    }

    /**
     * Called by PesapalPayments when Pesapal confirms a certificate fee (once). The certificate becomes available straight away when administrator approval is not
     * required; otherwise it waits for the administrator, who now sees it as paid.
     */
    public static function markCertPaid(int $certId, string $reference): void
    {
        $n = Database::execute("UPDATE academy_certificates SET fee_status = 'paid', fee_paid_at = ? WHERE id = ? AND fee_status = 'due'", [date('Y-m-d H:i:s'), $certId]);
        if ($n === 0) {
            return;
        }
        $c = Database::one('SELECT * FROM academy_certificates WHERE id = ?', [$certId]);
        if ($c === null) {
            return;
        }
        if (!CertificateService::approvalRequired() && $c['approved_at'] === null) {
            CertificateService::approve($certId, null, true);
        } else {
            Mailer::notify('Academy certificate fee paid', 'The certificate fee was paid through Pesapal and the certificate now waits for your approval.' . "\n\nLearner: " . $c['learner_name'] . "\nCourse: "
                . $c['course_title'] . "\nCertificate: " . $c['code'] . "\nReference: " . $reference . "\n\nApprove it: " . abs_url('admin/academy/certificates') . "\n", null);
        }
    }

    // ------------------------------------------------------------------------------------------------------------- waiving (administrators)

    public static function waiveSignup(int $learnerId): bool
    {
        return Database::execute("UPDATE academy_learners SET fee_status = 'waived' WHERE id = ? AND fee_status = 'due'", [$learnerId]) > 0;
    }

    public static function waiveCert(int $certId): bool
    {
        return Database::execute("UPDATE academy_certificates SET fee_status = 'waived' WHERE id = ? AND fee_status = 'due'", [$certId]) > 0;
    }

    /** Short wording for lists: "Fee unpaid" | "Fee paid" | "Fee waived" | '' (no fee). */
    public static function label(?string $status): string
    {
        return match ($status) {
            'due' => 'Fee unpaid', 'paid' => 'Fee paid', 'waived' => 'Fee waived', default => '',
        };
    }
}
