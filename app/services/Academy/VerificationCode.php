<?php
declare(strict_types=1);

namespace App\Services\Academy;

use App\Core\Config;
use App\Core\Database;
use App\Core\RateLimit;

/**
 * 6-digit confirmation code for a new LAF Academy account. The same code goes in the confirmation e-mail and, when the learner gave a WhatsApp number,
 * in a WhatsApp message. Only a keyed hash is stored; a code works for 24 hours and allows 5 wrong tries, then a new one must be requested.
 * Answers never reveal whether an address has an account. Every function degrades to "not available" when the columns are missing.
 */
final class VerificationCode
{
    public const HOURS = 24;
    public const MAX_TRIES = 5;

    private static ?bool $ready = null;

    public static function ready(): bool
    {
        if (self::$ready === null) {
            try {
                self::$ready = (int) Database::value("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'academy_learners' AND column_name IN ('code_hash', 'code_expires_at', 'code_attempts', 'whatsapp')", [], 0) === 4;
            } catch (\Throwable) {
                self::$ready = false;
            }
        }
        return self::$ready;
    }

    private static function hash(int $learnerId, string $code): string
    {
        return hash_hmac('sha256', 'academy-code|' . $learnerId . '|' . $code, Config::appKey());
    }

    /** Make a new code for the learner (replaces the old one). Returns it, or null when codes are not available yet. */
    public static function issue(int $learnerId): ?string
    {
        if (!self::ready()) {
            return null;
        }
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        Database::update('academy_learners', [
            'code_hash' => self::hash($learnerId, $code),
            'code_expires_at' => date('Y-m-d H:i:s', time() + self::HOURS * 3600),
            'code_attempts' => 0,
        ], 'id = ?', [$learnerId]);
        return $code;
    }

    /**
     * The learner types the code. On success the address is confirmed (like opening the link).
     * @return array{ok:bool,error:?string,learner:?array<string,mixed>}
     */
    public static function check(string $email, string $code, string $ip): array
    {
        $generic = 'That code is not right or has expired. Check it, or ask for a new one.';
        $email = LearnerAuth::normaliseEmail($email);
        $code = (string) preg_replace('/\D+/', '', $code);
        if (!self::ready() || $email === '' || strlen($code) !== 6) {
            return ['ok' => false, 'error' => $generic, 'learner' => null];
        }
        if (!RateLimit::attempt('academy-code-ip:' . $ip, 30, 3600) || !RateLimit::attempt('academy-code:' . sha1($email), 15, 3600)) {
            return ['ok' => false, 'error' => 'Too many tries. Please wait a little and try again.', 'learner' => null];
        }
        $l = Database::one('SELECT * FROM academy_learners WHERE email = ? AND is_active = 1 LIMIT 1', [$email]);
        if ($l === null || $l['code_hash'] === null || $l['code_expires_at'] === null || strtotime((string) $l['code_expires_at']) < time()) {
            return ['ok' => false, 'error' => $generic, 'learner' => null];
        }
        if ((int) $l['code_attempts'] >= self::MAX_TRIES) {
            return ['ok' => false, 'error' => 'Too many wrong tries for this code. Please ask for a new one.', 'learner' => null];
        }
        if (!hash_equals((string) $l['code_hash'], self::hash((int) $l['id'], $code))) {
            Database::execute('UPDATE academy_learners SET code_attempts = code_attempts + 1 WHERE id = ?', [(int) $l['id']]);
            return ['ok' => false, 'error' => $generic, 'learner' => null];
        }
        $now = date('Y-m-d H:i:s');
        Database::update('academy_learners', [
            'email_verified_at' => $l['email_verified_at'] ?? $now,
            'code_hash' => null, 'code_expires_at' => null, 'code_attempts' => 0,
            'token_hash' => null, 'token_kind' => null, 'token_expires_at' => null,
        ], 'id = ?', [(int) $l['id']]);
        $wasNew = $l['email_verified_at'] === null;
        $l['email_verified_at'] = $l['email_verified_at'] ?? $now;
        if ($wasNew) {
            AcademyMail::welcome($l);
        }
        return ['ok' => true, 'error' => null, 'learner' => $l];
    }

    /** Save the learner's WhatsApp number (already normalised). */
    public static function saveNumber(int $learnerId, ?string $digits): void
    {
        if (self::ready()) {
            Database::update('academy_learners', ['whatsapp' => $digits], 'id = ?', [$learnerId]);
        }
    }

    /**
     * Send a code by WhatsApp to the number the learner typed. Learner side (public page): limited, and the answer is the same for unknown addresses.
     * @return array{ok:bool,error:?string,shown:string} shown = the message for the learner
     */
    public static function sendWhatsApp(string $email, string $numberInput, string $ip): array
    {
        $same = 'If this address is waiting to be confirmed, we sent a code to that WhatsApp number. It can take a minute.';
        if (!self::ready() || !WhatsAppSender::enabled()) {
            return ['ok' => false, 'error' => 'not available', 'shown' => 'WhatsApp codes are not available right now. Please use the e-mail we sent you.'];
        }
        $number = WhatsAppSender::normalise($numberInput);
        if ($number === null) {
            return ['ok' => false, 'error' => 'number', 'shown' => 'Please enter your WhatsApp number with the country code, for example +256 772 123 456.'];
        }
        $email = LearnerAuth::normaliseEmail($email);
        if (!RateLimit::attempt('academy-wa-ip:' . $ip, 8, 3600) || ($email !== '' && !RateLimit::attempt('academy-wa:' . sha1($email), 3, 3600))) {
            return ['ok' => false, 'error' => 'limit', 'shown' => 'You asked for several codes already. Please wait an hour or use the e-mail we sent you.'];
        }
        $l = $email !== '' ? Database::one('SELECT * FROM academy_learners WHERE email = ? AND is_active = 1 AND email_verified_at IS NULL LIMIT 1', [$email]) : null;
        if ($l === null) {
            return ['ok' => true, 'error' => null, 'shown' => $same];
        }
        self::saveNumber((int) $l['id'], $number);
        $res = self::deliver((int) $l['id'], $number);
        return ['ok' => $res['ok'], 'error' => $res['error'], 'shown' => $res['ok'] ? $same : 'We could not send the WhatsApp message. Please use the e-mail we sent you, or ask the Academy team for help.'];
    }

    /** Issue a fresh code and send it to the number. Records nothing about the number in logs. @return array{ok:bool,error:?string} */
    public static function deliver(int $learnerId, string $number, ?string $code = null): array
    {
        $code ??= self::issue($learnerId);
        if ($code === null) {
            return ['ok' => false, 'error' => 'Codes are not available until the database update is imported.'];
        }
        return WhatsAppSender::sendCode($number, $code);
    }
}
