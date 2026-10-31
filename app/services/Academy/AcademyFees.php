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

    /** Learners whose sign-up fee is still due (active accounts only), oldest first. @return list<array<string,mixed>> */
    public static function unpaidLearners(): array
    {
        if (!self::ready()) {
            return [];
        }
        return Database::all("SELECT id, name, email, phone, country, whatsapp, reg_no, fee_status FROM academy_learners WHERE fee_status = 'due' AND is_active = 1 ORDER BY id");
    }

    /** The reminder the Academy team sends about an unpaid sign-up fee (the same words in the portal message, the e-mail and WhatsApp). @param array<string,mixed> $learner */
    public static function reminderText(array $learner): string
    {
        $fee = self::configured('signup');
        $first = trim((string) strtok(trim((string) $learner['name']), ' ')) ?: 'there';
        $text = 'Hello ' . $first . ', this is the LAF Academy team. Your sign-up fee' . ($fee !== null ? ' of ' . $fee['display'] : '') . ' is not paid yet, so your account cannot be approved. ';
        $ways = [];
        $m = self::manual();
        if ($m !== null) {
            $ways[] = trim(($m['mobile'] !== '' ? 'mobile money: ' . preg_replace('/\s*\R\s*/', ', ', $m['mobile']) : '') . ($m['bank'] !== '' ? ($m['mobile'] !== '' ? '; ' : '') . 'bank: ' . preg_replace('/\s*\R\s*/', ', ', $m['bank']) : ''));
        }
        if (\App\Services\Pesapal\PesapalPayments::ready() || \App\Services\Daraza::enabled('signup')) {
            $ways[] = 'online on the waiting page';
        }
        if ($ways !== []) {
            $text .= 'You can pay by ' . implode(', or ', $ways) . '. ';
        }
        $text .= 'After paying, sign in at ' . abs_url('academy/login') . ' and use "I have paid" to send us the transaction ID. Your account is approved automatically as soon as the payment is confirmed.';
        return $text;
    }

    /** The first words of every payment acknowledgement: also how the site knows a learner was already welcomed (no extra column). */
    public const WELCOME_MARK = 'Payment received - welcome to the LAF Academy';

    /** Learners who paid the sign-up fee (Pesapal or confirmed by an administrator), with whether the welcome message was sent. @return list<array<string,mixed>> */
    public static function paidLearners(bool $onlyNotWelcomed = false): array
    {
        if (!self::ready() || !\App\Services\Academy\LearnerMessages::ready()) {
            return [];
        }
        $rows = Database::all("SELECT l.id, l.name, l.email, l.phone, l.country, l.whatsapp, l.reg_no, l.fee_paid_at,
                (SELECT COUNT(*) FROM academy_messages m WHERE m.learner_id = l.id AND m.sender = 'admin' AND m.body LIKE ?) AS welcomed
            FROM academy_learners l WHERE l.fee_status = 'paid' AND l.is_active = 1 ORDER BY l.id", [self::WELCOME_MARK . '%']);
        return $onlyNotWelcomed ? array_values(array_filter($rows, static fn(array $r): bool => (int) $r['welcomed'] === 0)) : $rows;
    }

    /** The message that thanks the learner for paying, welcomes them to the class and says where to ask for help. @param array<string,mixed> $learner */
    public static function welcomeText(array $learner): string
    {
        $fee = self::configured('signup');
        $first = trim((string) strtok(trim((string) $learner['name']), ' ')) ?: 'there';
        $sw = \App\Services\Academy\LearnerAuth::supportWhatsapp();
        return self::WELCOME_MARK . "!\n\nHello " . $first . ', thank you: we have received your sign-up fee' . ($fee !== null ? ' of ' . $fee['display'] : '') . ' and your account is active. Welcome to the class! '
            . 'Sign in at ' . abs_url('academy/login') . ', pick a course and start with the first lesson. Join the virtual classes too: they are listed at ' . abs_url('academy/classes') . '.'
            . "\n\nIf you have any question or need any support, just ask: reply to this message in your portal (Messages)" . ($sw !== null ? ' or WhatsApp us on ' . $sw['display'] : '') . '. We are happy to help.';
    }

    /** @param array<string,mixed> $learner */
    public static function welcomeWhatsAppUrl(array $learner): ?string
    {
        $n = (string) preg_replace('/\D+/', '', (string) ($learner['whatsapp'] ?? ''));
        if ($n === '') {
            $n = (string) \App\Services\Academy\WhatsAppSender::normalise((string) ($learner['phone'] ?? ''));
        }
        return strlen($n) >= 8 ? 'https://wa.me/' . $n . '?text=' . rawurlencode(self::welcomeText($learner)) : null;
    }

    /** Send the acknowledgement to one learner who paid (portal message, and an e-mail when asked). @return bool false when the learner has not paid or it could not be sent */
    public static function acknowledge(int $learnerId, ?int $adminId, bool $email): bool
    {
        $l = Database::one('SELECT id, name, email, phone, whatsapp, fee_status FROM academy_learners WHERE id = ?', [$learnerId]);
        if ($l === null || $l['fee_status'] !== 'paid' || !self::ready()) {
            return false;
        }
        $text = self::welcomeText($l);
        if (!\App\Services\Academy\LearnerMessages::send($learnerId, 'admin', $text, $adminId)['ok']) {
            return false;
        }
        if ($email) {
            \App\Services\Academy\AcademyMail::teamMessage($l, $text);
        }
        return true;
    }

    /** The acknowledgement to every learner who paid and has not been welcomed yet. @return int how many learners were written to */
    public static function acknowledgeAll(?int $adminId, bool $email): int
    {
        $n = 0;
        foreach (self::paidLearners(true) as $row) {
            $n += self::acknowledge((int) $row['id'], $adminId, $email) ? 1 : 0;
        }
        return $n;
    }
    /** Certificates held until the certificate fee is paid, with the learner's contact details. @return list<array<string,mixed>> */
    public static function unpaidCerts(): array
    {
        if (!self::ready()) {
            return [];
        }
        return Database::all("SELECT c.id, c.learner_id, c.learner_name AS name, c.course_title, l.email, l.phone, l.country, l.whatsapp, l.reg_no FROM academy_certificates c JOIN academy_learners l ON l.id = c.learner_id WHERE c.fee_status = 'due' AND c.revoked_at IS NULL AND l.is_active = 1 ORDER BY c.id");
    }

    /** The reminder about an unpaid certificate fee (portal message, e-mail and WhatsApp). @param array<string,mixed> $row a row of unpaidCerts() */
    public static function certReminderText(array $row): string
    {
        $fee = self::configured('cert');
        $first = trim((string) strtok(trim((string) $row['name']), ' ')) ?: 'there';
        return 'Hello ' . $first . ', this is the LAF Academy team. Congratulations on passing "' . $row['course_title'] . '". Your certificate is ready and is released as soon as the certificate fee' . ($fee !== null ? ' of ' . $fee['display'] : '') . ' is paid. Sign in at ' . abs_url('academy/login') . ', open My certificates and pay online: the certificate is available the moment the payment is confirmed.';
    }

    /** @param array<string,mixed> $row a row of unpaidCerts() */
    public static function certReminderWhatsAppUrl(array $row): ?string
    {
        $n = (string) preg_replace('/\D+/', '', (string) ($row['whatsapp'] ?? ''));
        if ($n === '') {
            $n = (string) \App\Services\Academy\WhatsAppSender::normalise((string) ($row['phone'] ?? ''));
        }
        return strlen($n) >= 8 ? 'https://wa.me/' . $n . '?text=' . rawurlencode(self::certReminderText($row)) : null;
    }

    /** Portal message (and e-mail when asked) to every learner whose certificate waits for its fee. @return int how many learners were written to */
    public static function remindAllCerts(?int $adminId, bool $email): int
    {
        $sent = 0;
        foreach (self::unpaidCerts() as $row) {
            $text = self::certReminderText($row);
            if (!\App\Services\Academy\LearnerMessages::send((int) $row['learner_id'], 'admin', $text, $adminId)['ok']) {
                continue;
            }
            if ($email) {
                \App\Services\Academy\AcademyMail::teamMessage($row, $text);
            }
            $sent++;
        }
        return $sent;
    }

    /** WhatsApp template reminder to every learner whose certificate waits for its fee ({{1}} first name, {{2}} course, {{3}} fee, {{4}} sign-in link). @return array{sent:int,failed:int,nonumber:int,error:?string} */
    public static function remindAllCertsWhatsApp(): array
    {
        $out = ['sent' => 0, 'failed' => 0, 'nonumber' => 0, 'error' => null];
        $fee = self::configured('cert');
        foreach (self::unpaidCerts() as $l) {
            $n = (string) preg_replace('/\D+/', '', (string) ($l['whatsapp'] ?? ''));
            $n = $n !== '' ? (string) \App\Services\Academy\WhatsAppSender::normalise('+' . $n) : (string) \App\Services\Academy\WhatsAppSender::normalise((string) ($l['phone'] ?? ''));
            if ($n === '') {
                $out['nonumber']++;
                continue;
            }
            $first = trim((string) strtok(trim((string) $l['name']), ' ')) ?: 'there';
            $r = \App\Services\Academy\WhatsAppSender::sendReminder($n, [$first, (string) $l['course_title'], $fee !== null ? (string) $fee['display'] : 'the certificate fee', abs_url('academy/login')], 'WHATSAPP_CERT_REMINDER_TEMPLATE');
            if ($r['ok']) {
                $out['sent']++;
            } else {
                $out['failed']++;
                $out['error'] ??= $r['error'];
            }
        }
        return $out;
    }
    /**
     * Send the fee reminder on WhatsApp to every learner who has not paid, straight from the site (needs the WhatsApp Business API and an approved template, see
     * WhatsAppSender::reminderEnabled()). The template body is filled with {{1}} first name, {{2}} the fee, {{3}} the sign-in link.
     * @return array{sent:int,failed:int,nonumber:int,error:?string}
     */
    public static function remindAllWhatsApp(): array
    {
        $out = ['sent' => 0, 'failed' => 0, 'nonumber' => 0, 'error' => null];
        $fee = self::configured('signup');
        foreach (self::unpaidLearners() as $l) {
            $n = (string) preg_replace('/\D+/', '', (string) ($l['whatsapp'] ?? ''));
            $n = $n !== '' ? (string) \App\Services\Academy\WhatsAppSender::normalise('+' . $n) : (string) \App\Services\Academy\WhatsAppSender::normalise((string) ($l['phone'] ?? ''));
            if ($n === '') {
                $out['nonumber']++;
                continue;
            }
            $first = trim((string) strtok(trim((string) $l['name']), ' ')) ?: 'there';
            $r = \App\Services\Academy\WhatsAppSender::sendReminder($n, [$first, $fee !== null ? (string) $fee['display'] : 'the sign-up fee', abs_url('academy/login')]);
            if ($r['ok']) {
                $out['sent']++;
            } else {
                $out['failed']++;
                $out['error'] ??= $r['error'];
            }
        }
        return $out;
    }

    /** A click-to-chat WhatsApp link with the reminder already typed, or null when the learner has no usable number. @param array<string,mixed> $learner */
    public static function reminderWhatsAppUrl(array $learner): ?string
    {
        $n = (string) preg_replace('/\D+/', '', (string) ($learner['whatsapp'] ?? ''));
        if ($n === '') {
            $n = (string) \App\Services\Academy\WhatsAppSender::normalise((string) ($learner['phone'] ?? ''));
        }
        return strlen($n) >= 8 ? 'https://wa.me/' . $n . '?text=' . rawurlencode(self::reminderText($learner)) : null;
    }

    /** Send the reminder into the learner's portal messages (and e-mail it when asked). @return bool false when it could not be sent */
    public static function remind(int $learnerId, ?int $adminId, bool $email): bool
    {
        $l = Database::one("SELECT id, name, email, phone, whatsapp, fee_status FROM academy_learners WHERE id = ?", [$learnerId]);
        if ($l === null || !self::signupDue($l) || !self::ready()) {
            return false;
        }
        $text = self::reminderText($l);
        if (!\App\Services\Academy\LearnerMessages::send($learnerId, 'admin', $text, $adminId)['ok']) {
            return false;
        }
        if ($email) {
            \App\Services\Academy\AcademyMail::teamMessage($l, $text);
        }
        return true;
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

    /** How many certificates are free at the moment (no fee asked yet, not revoked): the ones "Hold all" would put behind the certificate fee. */
    public static function certificatesWithoutFee(): int
    {
        return self::ready() ? (int) Database::value("SELECT COUNT(*) FROM academy_certificates WHERE fee_status = 'none' AND revoked_at IS NULL", [], 0) : 0;
    }

    /**
     * Hold EVERY certificate in the system (also the ones already given out and the ones an administrator issued) until its certificate fee is paid: each gets the fee
     * DUE and waits again (nobody can open or check it). A payment with Pesapal releases it at once; an administrator can release it, issue another by hand or waive the fee.
     * Does nothing while the certificate fee is not set up. @return int how many certificates were put on hold
     */
    public static function holdAllCertificates(): int
    {
        if (self::cert() === null || !CertificateService::approvalReady()) {
            return 0;
        }
        $set = "fee_status = 'due', fee_paid_at = NULL, approved_at = NULL, approved_by = NULL" . (CertificateService::hasEmailedColumn() ? ', emailed_at = NULL' : '');
        return Database::execute("UPDATE academy_certificates SET " . $set . " WHERE fee_status = 'none' AND revoked_at IS NULL");
    }

    /**
     * Pesapal confirmed the sign-up fee: approve the account at once (Settings > LAF Academy can switch this off). Never for a rejected or switched-off account.
     * @return bool true when the account was approved by this call
     */
    public static function autoApproveAfterPayment(int $learnerId): bool
    {
        if (!(bool) (int) \App\Core\Settings::get('academy_fee_auto_approve', '1')) {
            return false;
        }
        $l = Database::one('SELECT * FROM academy_learners WHERE id = ?', [$learnerId]);
        if ($l === null || (int) $l['is_active'] !== 1 || !empty($l['rejected_at']) || $l['email_verified_at'] !== null || self::signupDue($l)) {
            return false;
        }
        if (!\App\Services\Academy\LearnerRegistry::approve($learnerId, null)) {
            return false;
        }
        \App\Core\AuditLog::record('academy.learner.auto_approved', 'academy_learner', (string) $learnerId, 'Approved automatically after the sign-up fee was paid with Pesapal (' . $l['email'] . ')');
        \App\Services\Academy\AcademyMail::approved($l);        // best effort
        return true;
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
        if ($c['approved_at'] === null && (!CertificateService::approvalRequired() || (bool) (int) \App\Core\Settings::get('academy_cert_auto_release', '1'))) {
            CertificateService::approve($certId, null, true);
            \App\Core\AuditLog::record('academy.certificate.auto_released', 'academy_certificate', (string) $certId, 'Certificate ' . $c['code'] . ' released automatically after the fee was paid with Pesapal');
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
