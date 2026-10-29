<?php
declare(strict_types=1);

namespace App\Services\Academy;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Log;
use App\Core\Mailer;
use App\Core\RateLimit;
use App\Core\Session;

/**
 * Accounts of LEARNERS of the LAF Academy (table academy_learners), separate from the staff accounts of the admin (users): own session key,
 * own sign-in page, no access to the admin whatever the password. E-mail + password; the address is confirmed with a one-time link
 * (setting academy_require_verification); passwords are reset with a one-time link. Links hold a random 256-bit token; only its SHA-256 is stored.
 *
 * Privacy / safety: the sign-in, sign-up and reset answers never reveal whether an address has an account (a person who already has one is e-mailed instead),
 * sign-in is throttled per address+IP and per IP, a deactivated learner is signed out on the next request, and the session id is renewed on sign-in.
 */
final class LearnerAuth
{
    private const SESSION_KEY = 'academy_learner_id';
    public const VERIFY_HOURS = 48;
    public const RESET_MINUTES = 60;

    /** The confirmation code made by the last sendVerification() (so the same code can also go to WhatsApp). */
    public static ?string $lastCode = null;

    private static ?array $current = null;
    private static bool $loaded = false;

    // ------------------------------------------------------------------------------------------------------------- who is signed in

    /** The signed-in learner (row without the password hash), or null. */
    public static function current(): ?array
    {
        if (self::$loaded) {
            return self::$current;
        }
        self::$loaded = true;
        $id = (int) Session::get(self::SESSION_KEY, 0);
        if ($id <= 0) {
            return self::$current = null;
        }
        try {
            $wa = (VerificationCode::ready() ? ', whatsapp' : '') . (LearnerRegistry::ready() ? ', reg_no' : '') . (self::bioReady() ? ', bio' : '');
            $row = Database::one('SELECT id, name, email, phone, country' . $wa . ', email_verified_at, is_active, created_at FROM academy_learners WHERE id = ? LIMIT 1', [$id]);
        } catch (\Throwable) {
            $row = null;
        }
        if ($row === null || (int) $row['is_active'] !== 1) {
            Session::forget(self::SESSION_KEY);
            return self::$current = null;
        }
        return self::$current = $row;
    }

    public static function id(): ?int
    {
        $c = self::current();
        return $c === null ? null : (int) $c['id'];
    }

    public static function check(): bool
    {
        return self::current() !== null;
    }

    public static function flush(): void
    {
        self::$current = null;
        self::$loaded = false;
    }

    /** How new learners get in: "approval" (an administrator approves, nothing is e-mailed: the default), "email" (confirm by link or code, or be approved) or "open". */
    public static function signupMode(): string
    {
        $m = (string) setting('academy_signup_mode', 'approval');
        return in_array($m, ['approval', 'email', 'open'], true) ? $m : 'approval';
    }

    /** True when a new learner cannot sign in until the e-mail is confirmed or an administrator approves the account. */
    public static function requireVerification(): bool
    {
        return self::signupMode() !== 'open';
    }

    /** True only in "email" mode: confirmation link / 6-digit code are e-mailed (and sent on WhatsApp). */
    public static function emailVerificationOn(): bool
    {
        return self::signupMode() === 'email';
    }

    /** The approval time shown to learners, in hours. */
    public static function approvalHours(): int
    {
        return max(1, min(48, (int) setting('academy_approval_hours', '1')));
    }

    /** "1 hour" / "3 hours". */
    public static function approvalText(): string
    {
        $h = self::approvalHours();
        return $h . ' hour' . ($h === 1 ? '' : 's');
    }

    public static function registrationOpen(): bool
    {
        return (string) setting('academy_registration_open', '1') === '1';
    }

    // ------------------------------------------------------------------------------------------------------------- sign up

    public static function normaliseEmail(string $email): string
    {
        return strtolower(trim($email));
    }

    public static function passwordProblem(string $password, string $email = ''): ?string
    {
        if (mb_strlen($password) < 8) {
            return 'Use at least 8 characters.';
        }
        if (strlen($password) > 72) {
            return 'Use at most 72 characters.';
        }
        if (preg_match('/^(.)\1+$/u', $password) === 1 || preg_match('/^\d+$/', $password) === 1) {
            return 'Choose something harder to guess: add letters and a mix of characters.';
        }
        if ($email !== '' && strtolower($password) === strtolower($email)) {
            return 'The password must not be your e-mail address.';
        }
        return null;
    }

    /**
     * The contact details every learner must give (they let the Academy support them): phone, country and WhatsApp number.
     * @return array{errors:array<string,string>,phone:string,country:string,whatsapp:?string} whatsapp = digits with the country code
     */
    public static function contact(string $phoneIn, string $countryIn, string $whatsappIn): array
    {
        $errors = [];
        $phone = trim((string) preg_replace('/[^0-9+()\- ]/', '', $phoneIn));
        $digits = (string) preg_replace('/\D+/', '', $phone);
        if (strlen($digits) < 7 || strlen($phone) > 40) {
            $errors['phone'] = 'Please enter your phone number.';
        }
        $country = trim((string) preg_replace('/\s+/u', ' ', strip_tags($countryIn)));
        if (mb_strlen($country) < 2 || mb_strlen($country) > 80) {
            $errors['country'] = 'Please enter your country.';
        }
        $wa = self::normaliseWhatsApp($whatsappIn);
        if ($wa === null) {
            $errors['whatsapp'] = 'Please enter your WhatsApp number with the country code, for example +256 772 123 456.';
        }
        return ['errors' => $errors, 'phone' => $phone, 'country' => $country, 'whatsapp' => $wa];
    }

    /** International digits of a WhatsApp number. A local number starting with 0 gets the default country code (WHATSAPP_COUNTRY_CODE, else 256 = Uganda). */
    public static function normaliseWhatsApp(string $input): ?string
    {
        $n = WhatsAppSender::normalise($input);
        if ($n === null && str_starts_with((string) preg_replace('/\D+/', '', $input), '0') && !str_starts_with((string) preg_replace('/\D+/', '', $input), '00')) {
            $d = (string) preg_replace('/\D+/', '', $input);
            $n = '256' . ltrim($d, '0');
            $n = strlen($n) >= 8 && strlen($n) <= 15 ? $n : null;
        }
        return $n;
    }

    /** True when the learner still has to give the phone, country or WhatsApp number. */
    public static function missingContact(array $learner): bool
    {
        return trim((string) ($learner['phone'] ?? '')) === '' || trim((string) ($learner['country'] ?? '')) === ''
            || (VerificationCode::ready() && trim((string) ($learner['whatsapp'] ?? '')) === '');
    }

    /** The Academy support WhatsApp line: ['display' => '+256 779 795 662', 'digits' => '256779795662', 'url' => 'https://wa.me/...'] or null when switched off. */
    public static function supportWhatsapp(): ?array
    {
        $raw = trim((string) setting('academy_support_whatsapp', '+256779795662'));
        $digits = (string) preg_replace('/\D+/', '', $raw);
        if ($digits === '') {
            return null;
        }
        return ['display' => $raw, 'digits' => $digits, 'url' => 'https://wa.me/' . $digits];
    }

    /**
     * @param array{name?:string,email?:string,password?:string,phone?:string,country?:string,whatsapp?:string} $in
     * @return array{ok:bool,errors:array<string,string>,learner?:array<string,mixed>,verify:bool}
     */
    public static function register(array $in, string $ip): array
    {
        $name = trim((string) preg_replace('/\s+/u', ' ', strip_tags((string) ($in['name'] ?? ''))));
        $email = self::normaliseEmail((string) ($in['email'] ?? ''));
        $password = (string) ($in['password'] ?? '');
        $c = self::contact((string) ($in['phone'] ?? ''), (string) ($in['country'] ?? ''), (string) ($in['whatsapp'] ?? ''));
        $phone = $c['phone'];
        $country = $c['country'];
        $wa = $c['whatsapp'];
        $errors = $c['errors'];
        if ($name === '' || mb_strlen($name) < 2 || mb_strlen($name) > 120) {
            $errors['name'] = 'Please enter your full name (it is printed on your certificates).';
        }
        if (!Mailer::validEmail($email) || strlen($email) > 190) {
            $errors['email'] = 'Please enter a valid e-mail address.';
        }
        $pp = self::passwordProblem($password, $email);
        if ($pp !== null) {
            $errors['password'] = $pp;
        }
        if ($errors !== []) {
            return ['ok' => false, 'errors' => $errors, 'verify' => false];
        }
        if (!RateLimit::attempt('academy-register:' . $ip, 6, 3600)) {
            return ['ok' => false, 'errors' => ['_' => 'Too many sign-ups from this connection. Please try again in an hour.'], 'verify' => false];
        }
        $existing = Database::one('SELECT id, name, is_active FROM academy_learners WHERE email = ? LIMIT 1', [$email]);
        if ($existing !== null) {
            // never reveal that the address has an account: tell its owner by e-mail, show the same screen
            AcademyMail::alreadyRegistered($email, (string) $existing['name']);
            return ['ok' => true, 'errors' => [], 'verify' => self::requireVerification()];
        }
        $verify = self::requireVerification();
        $id = Database::insert('academy_learners', [
            'name' => mb_substr($name, 0, 120),
            'email' => $email,
            'password_hash' => Auth::hashPassword($password),
            'phone' => $phone !== '' ? $phone : null,
            'country' => $country !== '' ? mb_substr($country, 0, 80) : null,
            'email_verified_at' => $verify ? null : date('Y-m-d H:i:s'),
            'is_active' => 1,
        ]);
        LearnerRegistry::assign($id);
        VerificationCode::saveNumber($id, $wa);                                                  // the WhatsApp number is kept for support
        $learner = (array) Database::one('SELECT * FROM academy_learners WHERE id = ?', [$id]);
        if ($verify && self::emailVerificationOn()) {
            self::$lastCode = null;
            self::sendVerification($learner);
            if ($wa !== null && self::$lastCode !== null && WhatsAppSender::enabled()) {
                VerificationCode::deliver($id, $wa, self::$lastCode);        // the same code, on WhatsApp too
            }
        } elseif (!$verify) {
            AcademyMail::welcome($learner);
        }
        self::rememberPortal();
        LearnerRegistry::announce($id, 'the sign-up form');        // the activity log (dashboard) + an e-mail to the organisation
        return ['ok' => true, 'errors' => [], 'learner' => $learner, 'verify' => $verify];
    }

    // ------------------------------------------------------------------------------------------------------------- tokens

    /** Create a one-time link token of $kind ('verify' | 'reset'); returns the plain token (only its hash is stored). */
    private static function issueToken(int $learnerId, string $kind, int $minutes): string
    {
        $token = bin2hex(random_bytes(32));
        Database::update('academy_learners', [
            'token_hash' => hash('sha256', $token),
            'token_kind' => $kind,
            'token_expires_at' => date('Y-m-d H:i:s', time() + $minutes * 60),
        ], 'id = ?', [$learnerId]);
        return $token;
    }

    /** @return array<string,mixed>|null the learner a still-valid token of this kind belongs to */
    private static function byToken(string $kind, string $token): ?array
    {
        if (preg_match('/^[a-f0-9]{64}$/', $token) !== 1) {
            return null;
        }
        $row = Database::one(
            'SELECT * FROM academy_learners WHERE token_hash = ? AND token_kind = ? AND token_expires_at > NOW() LIMIT 1',
            [hash('sha256', $token), $kind]
        );
        return $row;
    }

    private static function clearToken(int $learnerId): void
    {
        Database::update('academy_learners', ['token_hash' => null, 'token_kind' => null, 'token_expires_at' => null], 'id = ?', [$learnerId]);
    }

    /** $hours: how long the link works (default VERIFY_HOURS); $limit false for an administrator who presses the button. */
    public static function sendVerification(array $learner, ?int $hours = null, bool $limit = true): bool
    {
        if ($limit && !RateLimit::attempt('academy-verify-mail:' . sha1((string) $learner['email']), 5, 3600)) {
            return false;
        }
        $hours ??= self::VERIFY_HOURS;
        $token = self::issueToken((int) $learner['id'], 'verify', $hours * 60);
        $code = VerificationCode::issue((int) $learner['id']);
        self::$lastCode = $code;
        $ok = AcademyMail::verification($learner, abs_url('academy/verify/' . $token), $hours, $code);
        self::recordMail((int) $learner['id'], $ok);
        return $ok;
    }

    /** True when the delivery-record columns exist (the code can be uploaded before the database update). */
    public static function mailColumns(): bool
    {
        static $ok = null;
        if ($ok === null) {
            try {
                $ok = (int) Database::value("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'academy_learners' AND column_name IN ('mail_sent_at', 'mail_error')", [], 0) === 2;
            } catch (\Throwable) {
                $ok = false;
            }
        }
        return $ok;
    }

    /** Remember whether the last e-mail to this learner was accepted for delivery (and why not). Never breaks the caller. */
    public static function recordMail(int $learnerId, bool $ok): void
    {
        if (!self::mailColumns()) {
            return;
        }
        try {
            Database::update('academy_learners', [
                'mail_sent_at' => $ok ? date('Y-m-d H:i:s') : null,
                'mail_error' => $ok ? null : mb_substr((string) (AcademyMail::lastError() ?? 'The e-mail could not be sent.'), 0, 190),
            ], 'id = ?', [$learnerId]);
        } catch (\Throwable) {
        }
    }

    /**
     * Administrator button: send a fresh confirmation link (valid 7 days) to one learner. No per-address rate limit.
     * @return array{ok:bool,error:?string}
     */
    public static function sendConfirmation(int $learnerId): array
    {
        $l = Database::one('SELECT * FROM academy_learners WHERE id = ? AND is_active = 1 AND email_verified_at IS NULL LIMIT 1', [$learnerId]);
        if ($l === null) {
            return ['ok' => false, 'error' => 'This learner is already confirmed or the account is switched off.'];
        }
        $ok = self::sendVerification($l, 168, false);
        return ['ok' => $ok, 'error' => $ok ? null : (AcademyMail::lastError() ?? 'The e-mail could not be sent.')];
    }

    /** Re-send the confirmation link (the screen says the same thing whether or not the address exists). */
    public static function resendVerification(string $email): void
    {
        $l = Database::one('SELECT * FROM academy_learners WHERE email = ? AND is_active = 1 AND email_verified_at IS NULL LIMIT 1', [self::normaliseEmail($email)]);
        if ($l !== null) {
            self::sendVerification($l);
        }
    }

    /** Open a confirmation link. Signs the learner in. @return array<string,mixed>|null the learner, or null when the link is wrong or expired */
    public static function verify(string $token): ?array
    {
        $l = self::byToken('verify', $token);
        if ($l === null || (int) $l['is_active'] !== 1) {
            return null;
        }
        Database::update('academy_learners', ['email_verified_at' => date('Y-m-d H:i:s'), 'token_hash' => null, 'token_kind' => null, 'token_expires_at' => null], 'id = ?', [(int) $l['id']]);
        $l['email_verified_at'] = date('Y-m-d H:i:s');
        AcademyMail::welcome($l);
        return $l;
    }

    // ------------------------------------------------------------------------------------------------------------- sign in / out

    /** @return array{ok:bool,error:?string,unverified:bool,learner:?array<string,mixed>} */
    public static function login(string $email, string $password, string $ip): array
    {
        $email = self::normaliseEmail($email);
        $key = 'academy-login:' . sha1($email . '|' . $ip);
        if (RateLimit::tooMany($key, 8) || RateLimit::tooMany('academy-login-ip:' . $ip, 40)) {
            return ['ok' => false, 'error' => 'Too many attempts. Please wait a few minutes and try again.', 'unverified' => false, 'learner' => null];
        }
        $l = $email !== '' ? Database::one('SELECT * FROM academy_learners WHERE email = ? LIMIT 1', [$email]) : null;
        // always verify against a hash so the answer takes the same time whether or not the address exists
        $hash = $l['password_hash'] ?? '$2y$10$abcdefghijklmnopqrstuuJ0zH2o9B5uD2o1cE8o8R7oR0b5eI1xW';
        $ok = Auth::verifyPassword($password, (string) $hash) && $l !== null && (int) $l['is_active'] === 1;
        if (!$ok && $l !== null && (int) $l['is_active'] !== 1 && ($l['rejected_at'] ?? null) !== null && Auth::verifyPassword($password, (string) $l['password_hash'])) {
            return ['ok' => false, 'error' => 'Your application was not approved' . (trim((string) ($l['reject_reason'] ?? '')) !== '' ? ': ' . trim((string) $l['reject_reason']) : '.')
                . ' If you think this is a mistake, please message the Academy team and we will look at it again.', 'unverified' => false, 'learner' => null, 'rejected' => true];
        }
        if (!$ok) {
            RateLimit::hit($key, 900);
            RateLimit::hit('academy-login-ip:' . $ip, 900);
            return ['ok' => false, 'error' => 'That e-mail address and password do not match.', 'unverified' => false, 'learner' => null];
        }
        if (self::requireVerification() && $l['email_verified_at'] === null) {
            return ['ok' => false, 'error' => self::emailVerificationOn()
                ? 'Your account is waiting for approval. Confirm your e-mail address (open the link or type the code we sent you), or wait for the Academy team to approve it.'
                : 'Your account is waiting for approval. Approval takes about ' . self::approvalText() . ': please sign in after that.' . (($sw = self::supportWhatsapp()) !== null ? ' For quick inquiries about your approval, WhatsApp ' . $sw['display'] . '.' : ''), 'unverified' => true, 'learner' => $l];
        }
        RateLimit::clear($key);
        self::signIn($l);
        return ['ok' => true, 'error' => null, 'unverified' => false, 'learner' => $l];
    }

    public static function bioReady(): bool
    {
        static $ok = null;
        if ($ok === null) {
            try {
                $ok = (int) Database::value("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'academy_learners' AND column_name = 'bio'", [], 0) === 1;
            } catch (\Throwable) {
                $ok = false;
            }
        }
        return $ok;
    }

    /** How complete the learner's profile is, and what is missing. @return array{pct:int,missing:list<string>} */
    public static function completeness(array $learner, bool $hasPhoto): array
    {
        $checks = [
            'your full name' => trim((string) ($learner['name'] ?? '')) !== '',
            'your phone number' => trim((string) ($learner['phone'] ?? '')) !== '',
            'your country' => trim((string) ($learner['country'] ?? '')) !== '',
            'your WhatsApp number' => !VerificationCode::ready() || trim((string) ($learner['whatsapp'] ?? '')) !== '',
            'a short bio' => !self::bioReady() || trim((string) ($learner['bio'] ?? '')) !== '',
            'your picture' => $hasPhoto || !LearnerPortrait::ready(),
        ];
        $missing = array_keys(array_filter($checks, static fn(bool $ok): bool => !$ok));
        return ['pct' => (int) round(100 * (count($checks) - count($missing)) / count($checks)), 'missing' => $missing];
    }

    /** A small marker cookie (no personal data) that tells the website this browser has a learner account, so every page can offer "Go to my portal". */
    public static function rememberPortal(): void
    {
        if (PHP_SAPI === 'cli' || headers_sent()) {
            return;
        }
        $path = rtrim(base_path(), '/') . '/';
        setcookie('laf_portal', '1', ['expires' => time() + 400 * 86400, 'path' => $path === '' ? '/' : $path, 'secure' => \App\Core\Request::current()?->isSecure() ?? false, 'httponly' => true, 'samesite' => 'Lax']);
    }

    /** True when this browser has (had) a learner account. */
    public static function knowsPortal(): bool
    {
        return (string) (\App\Core\Request::current()?->cookie('laf_portal') ?? '') === '1';
    }

    public static function signIn(array $learner): void
    {
        self::rememberPortal();
        Session::regenerate();
        Session::put(self::SESSION_KEY, (int) $learner['id']);
        Database::update('academy_learners', ['last_login_at' => date('Y-m-d H:i:s')], 'id = ?', [(int) $learner['id']]);
        self::flush();
    }

    public static function logout(): void
    {
        Session::forget(self::SESSION_KEY);
        Session::regenerate();
        self::flush();
    }

    // ------------------------------------------------------------------------------------------------------------- password reset / profile

    public static function requestReset(string $email, string $ip): void
    {
        $email = self::normaliseEmail($email);
        if (!Mailer::validEmail($email) || !RateLimit::attempt('academy-reset-ip:' . $ip, 6, 3600) || !RateLimit::attempt('academy-reset:' . sha1($email), 3, 3600)) {
            return;
        }
        $l = Database::one('SELECT * FROM academy_learners WHERE email = ? AND is_active = 1 LIMIT 1', [$email]);
        if ($l === null) {
            return;
        }
        $token = self::issueToken((int) $l['id'], 'reset', self::RESET_MINUTES);
        self::recordMail((int) $l['id'], AcademyMail::passwordReset($l, abs_url('academy/reset/' . $token)));
    }

    public static function resetTokenValid(string $token): bool
    {
        return self::byToken('reset', $token) !== null;
    }

    /** @return array{ok:bool,error:?string} */
    public static function resetPassword(string $token, string $password): array
    {
        $l = self::byToken('reset', $token);
        if ($l === null) {
            return ['ok' => false, 'error' => 'This link has expired or was already used. Please ask for a new one.'];
        }
        $pp = self::passwordProblem($password, (string) $l['email']);
        if ($pp !== null) {
            return ['ok' => false, 'error' => $pp];
        }
        Database::update('academy_learners', [
            'password_hash' => Auth::hashPassword($password),
            'token_hash' => null, 'token_kind' => null, 'token_expires_at' => null,
            // opening a link that was e-mailed to the address proves it is theirs
            'email_verified_at' => $l['email_verified_at'] ?? date('Y-m-d H:i:s'),
        ], 'id = ?', [(int) $l['id']]);
        Log::info('Academy learner password reset', ['learner' => (int) $l['id']]);
        return ['ok' => true, 'error' => null];
    }

    /** @return array{ok:bool,error:?string} */
    public static function changePassword(int $learnerId, string $current, string $new): array
    {
        $l = Database::one('SELECT * FROM academy_learners WHERE id = ?', [$learnerId]);
        if ($l === null || !Auth::verifyPassword($current, (string) $l['password_hash'])) {
            return ['ok' => false, 'error' => 'The current password is not correct.'];
        }
        $pp = self::passwordProblem($new, (string) $l['email']);
        if ($pp !== null) {
            return ['ok' => false, 'error' => $pp];
        }
        Database::update('academy_learners', ['password_hash' => Auth::hashPassword($new)], 'id = ?', [$learnerId]);
        return ['ok' => true, 'error' => null];
    }

    /** Delete the account and everything tied to it (progress, attempts, enrolments). Certificates stay valid (they keep the name as printed). */
    public static function deleteAccount(int $learnerId): void
    {
        Database::execute('DELETE FROM academy_learners WHERE id = ?', [$learnerId]);
        self::logout();
    }
}
