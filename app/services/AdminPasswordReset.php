<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\AuditLog;
use App\Core\Auth;
use App\Core\Config;
use App\Core\Database;
use App\Core\Log;
use App\Core\Mailer;
use App\Core\RateLimit;
use App\Models\User;

/**
 * "Forgot password" for the admin area. The link e-mailed to the account is a signed token, so no table or column is needed:
 *
 *   token = <user id>_<expires at (unix)>_<HMAC-SHA256 over id | expires | the CURRENT password hash, keyed with the app key>
 *
 * Because the password hash is part of the signature, a link stops working the moment the password changes (it can be used once),
 * and it expires after RESET_MINUTES. Requests never reveal whether an address belongs to an account.
 */
final class AdminPasswordReset
{
    public const RESET_MINUTES = 60;

    /** Always returns quietly: the screen shows the same message whether or not the address exists. */
    public static function request(string $email, string $ip): void
    {
        $email = strtolower(trim($email));
        if (!Mailer::validEmail($email) || !RateLimit::attempt('admin-reset-ip:' . $ip, 6, 3600) || !RateLimit::attempt('admin-reset:' . sha1($email), 3, 3600)) {
            return;
        }
        $user = User::findBy('email', $email);
        if ($user === null || (int) $user['is_active'] !== 1) {
            return;
        }
        $sent = self::mail($user, abs_url('admin/reset-password/' . self::token($user)));
        AuditLog::record('auth.reset_requested', 'user', (int) $user['id'], $sent ? 'Password reset link sent' : 'Password reset link could not be sent');
    }

    /** @return array<string,mixed>|null the user a still-valid link belongs to */
    public static function userForToken(string $token): ?array
    {
        $parts = explode('_', $token);
        if (count($parts) !== 3 || !ctype_digit($parts[0]) || !ctype_digit($parts[1]) || (int) $parts[1] < time()) {
            return null;
        }
        $user = User::find((int) $parts[0]);
        if ($user === null || (int) $user['is_active'] !== 1) {
            return null;
        }
        return hash_equals(self::sign((int) $user['id'], (int) $parts[1], (string) $user['password_hash']), $parts[2]) ? $user : null;
    }

    /** @return array{ok:bool,error:?string} */
    public static function reset(string $token, string $password): array
    {
        $user = self::userForToken($token);
        if ($user === null) {
            return ['ok' => false, 'error' => 'This link has expired or was already used. Please ask for a new one.'];
        }
        if (($problem = Auth::passwordProblem($password)) !== null) {
            return ['ok' => false, 'error' => $problem];
        }
        Auth::changePassword((int) $user['id'], $password, false);
        // a locked-out owner can sign in straight away with the new password
        try {
            Database::execute('DELETE FROM login_attempts WHERE email = ?', [strtolower((string) $user['email'])]);
        } catch (\Throwable) {
        }
        AuditLog::record('auth.reset_done', 'user', (int) $user['id'], 'Password reset with an e-mailed link');
        return ['ok' => true, 'error' => null];
    }

    /** @param array<string,mixed> $user */
    public static function token(array $user, ?int $expires = null): string
    {
        $expires ??= time() + self::RESET_MINUTES * 60;
        return $user['id'] . '_' . $expires . '_' . self::sign((int) $user['id'], $expires, (string) $user['password_hash']);
    }

    private static function sign(int $id, int $expires, string $passwordHash): string
    {
        return hash_hmac('sha256', 'admin-reset|' . $id . '|' . $expires . '|' . $passwordHash, Config::appKey());
    }

    /** @param array<string,mixed> $user */
    private static function mail(array $user, string $url): bool
    {
        $site = trim((string) setting('site_name', 'Lumina Africa Foundation')) ?: 'Lumina Africa Foundation';
        $first = trim(explode(' ', trim((string) preg_replace('/[^\p{L}\p{M}\' \-]/u', '', (string) $user['name'])))[0] ?? '');
        $hello = 'Hello ' . ($first !== '' ? mb_substr($first, 0, 40) : 'there') . ',';
        $p1 = 'We received a request to reset the password of your ' . $site . ' admin account. The link below works for ' . self::RESET_MINUTES . ' minutes and only once.';
        $p2 = 'If you did not ask for this, ignore this message: your password stays as it is.';
        $text = $hello . "\n\n" . $p1 . "\n\nChoose a new password:\n" . $url . "\n\n" . $p2 . "\n\n" . $site;
        $h = static fn(string $s): string => htmlspecialchars($s, ENT_QUOTES);
        $html = '<!doctype html><html><body style="margin:0;padding:24px;background:#f4f1ea;font-family:Arial,Helvetica,sans-serif;color:#1d1e20">'
            . '<div style="max-width:560px;margin:0 auto;background:#ffffff;border-radius:14px;padding:28px">'
            . '<p style="margin:0 0 18px;font-size:13px;letter-spacing:.12em;text-transform:uppercase;color:#85560a;font-weight:700">' . $h($site) . ' - admin</p>'
            . '<p style="margin:0 0 16px;line-height:1.6;font-size:16px">' . $h($hello) . '</p>'
            . '<p style="margin:0 0 16px;line-height:1.6;font-size:16px">' . $h($p1) . '</p>'
            . '<p style="margin:22px 0"><a href="' . $h($url) . '" style="background:#d6a151;color:#172036;text-decoration:none;font-weight:700;padding:13px 24px;border-radius:999px;display:inline-block">Choose a new password</a></p>'
            . '<p style="margin:0 0 16px;font-size:13px;color:#5a6470;word-break:break-all">If the button does not work, copy this address into your browser:<br>' . $h($url) . '</p>'
            . '<p style="margin:0;line-height:1.6;font-size:14px;color:#5a6470">' . $h($p2) . '</p></div></body></html>';
        try {
            return Mailer::send((string) $user['email'], 'Reset your ' . $site . ' admin password', $text, ['html' => $html]);
        } catch (\Throwable $e) {
            Log::error('Admin password reset e-mail failed: ' . $e->getMessage());
            return false;
        }
    }
}
