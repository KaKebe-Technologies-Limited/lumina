<?php
declare(strict_types=1);

namespace App\Services\Security;

use App\Core\Config;
use App\Core\Database;
use App\Core\Request;

/**
 * Admin > Security: what is safe and what needs attention, computed from the live site (nothing is changed here).
 * Each check is [key, status ok|warn|bad, title, detail, fix]. The checks are advice for the owner; none of them blocks anything.
 */
final class SecurityStatus
{
    /** @return list<array{status:string,title:string,detail:string,fix:string}> */
    public static function checks(?Request $request): array
    {
        $out = [];
        $local = (string) Config::get('app.env', 'production') === 'local';
        $secure = $request !== null && $request->isSecure();
        $out[] = $local || $secure
            ? ['status' => 'ok', 'title' => 'The admin is served over HTTPS', 'detail' => $local ? 'This is a local test site, so HTTPS is not needed here.' : 'Passwords and sessions travel encrypted.', 'fix' => '']
            : ['status' => 'bad', 'title' => 'The admin is NOT served over HTTPS', 'detail' => 'Passwords can be read on the way. Never sign in on this address.', 'fix' => 'Turn on the SSL certificate for the domain in hPanel and set APP_URL to the https address.'];

        $debug = (bool) Config::get('app.debug', false);
        $out[] = !$debug || $local
            ? ['status' => 'ok', 'title' => 'Technical error details are hidden from visitors', 'detail' => 'Errors show a short friendly message; the details go only to the error log.', 'fix' => '']
            : ['status' => 'bad', 'title' => 'Debug mode is on', 'detail' => 'Visitors could be shown file names and code.', 'fix' => 'Set APP_DEBUG=0 in the server .env file.'];

        $on = TwoFactor::enabled();
        $out[] = $on
            ? ['status' => 'ok', 'title' => 'Two-step sign-in by e-mail is on', 'detail' => 'A stolen password is not enough to open the admin.', 'fix' => '']
            : ['status' => 'bad', 'title' => 'Two-step sign-in is off', 'detail' => 'Anyone who learns a password can sign in.', 'fix' => 'Turn it on below (you will be asked for a test code first).'];

        $min = AdminLock::minutes();
        $out[] = $min > 0
            ? ['status' => 'ok', 'title' => 'The screen locks after ' . $min . ' idle minutes', 'detail' => 'An unattended computer does not leave the admin open.', 'fix' => '']
            : ['status' => 'warn', 'title' => 'The screen lock is off', 'detail' => 'An unattended computer stays signed in for hours.', 'fix' => 'Choose a lock time below (15 minutes is a good start).'];

        $creds = defined('STORAGE_PATH') && is_file(STORAGE_PATH . '/dev-admin-credentials.txt');
        $out[] = !$creds
            ? ['status' => 'ok', 'title' => 'No leftover set-up password file', 'detail' => '', 'fix' => '']
            : ['status' => $local ? 'warn' : 'bad', 'title' => 'A set-up password file is still on the server', 'detail' => 'storage/dev-admin-credentials.txt holds the first admin password in plain text.', 'fix' => 'Delete that file in the hosting file manager.'];

        if (!$local && $request !== null) {
            foreach (['.env' => 'the settings file (.env)', 'database/schema.sql' => 'the database files', 'storage/app.key' => 'the secret key file'] as $path => $label) {
                $code = self::probe($request, $path);
                if ($code === 200) {
                    $out[] = ['status' => 'bad', 'title' => 'Visitors can download ' . $label, 'detail' => '/' . $path . ' answers with its contents.', 'fix' => 'Check that the .htaccess file is uploaded and the hosting honours it. Change every password and key afterwards.'];
                }
            }
        }

        $active = (int) Database::value("SELECT COUNT(*) FROM users WHERE is_active = 1", [], 0);
        $mustChange = (int) Database::value("SELECT COUNT(*) FROM users WHERE is_active = 1 AND must_change_password = 1", [], 0);
        $out[] = $mustChange === 0
            ? ['status' => 'ok', 'title' => $active . ' active admin account' . ($active === 1 ? '' : 's') . ', none waiting to change a temporary password', 'detail' => '', 'fix' => '']
            : ['status' => 'warn', 'title' => $mustChange . ' account' . ($mustChange === 1 ? ' still has' : 's still have') . ' a temporary password', 'detail' => 'They will be asked to change it at their next sign-in.', 'fix' => 'Remind them, or remove accounts nobody uses (Users).'];
        return $out;
    }

    /** The HTTP status of a file that must never be downloadable (null when the check could not run). */
    private static function probe(Request $request, string $path): ?int
    {
        $url = rtrim((string) abs_url(''), '/') . '/' . $path;
        if (!function_exists('curl_init') || !str_starts_with($url, 'https://')) {
            return null;
        }
        $ch = curl_init($url);
        curl_setopt_array($ch, [CURLOPT_NOBODY => true, CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 4, CURLOPT_CONNECTTIMEOUT => 3, CURLOPT_FOLLOWLOCATION => false, CURLOPT_SSL_VERIFYPEER => true]);
        curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);
        return $code > 0 ? $code : null;
    }

    /** @return array{failed:int,ok:int,locked:int} sign-in numbers of the last 24 hours */
    public static function activity(): array
    {
        $since = date('Y-m-d H:i:s', time() - 86400);
        return [
            'failed' => (int) Database::value("SELECT COUNT(*) FROM audit_log WHERE action = 'auth.login_failed' AND created_at >= ?", [$since], 0),
            'ok' => (int) Database::value("SELECT COUNT(*) FROM audit_log WHERE action = 'auth.login' AND created_at >= ?", [$since], 0),
            'locked' => (int) Database::value("SELECT COUNT(*) FROM audit_log WHERE action IN ('auth.2fa_failed','auth.unlock_blocked') AND created_at >= ?", [$since], 0),
        ];
    }

    /** The latest sign-in related lines of the activity log. @return list<array<string,mixed>> */
    public static function recent(int $limit = 15): array
    {
        return Database::all("SELECT created_at, user_name, action, summary, ip FROM audit_log WHERE action LIKE 'auth.%' ORDER BY id DESC LIMIT " . max(1, min(50, $limit)));
    }
}
