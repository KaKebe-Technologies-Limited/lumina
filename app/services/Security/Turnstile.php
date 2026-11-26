<?php
declare(strict_types=1);

namespace App\Services\Security;

use App\Core\Config;
use App\Core\Env;
use App\Core\Log;
use App\Core\Request;

/**
 * Optional Cloudflare Turnstile bot check (docs/CLOUDFLARE.md section 10). OFF unless TURNSTILE_SITE_KEY and TURNSTILE_SECRET_KEY are both in the
 * server .env (and TURNSTILE_ENABLED is not 0). The honeypot, the signed timestamp and the throttles stay in place either way.
 *
 *   widget()            HTML for the form (empty when off) and a flag that lets Response add challenges.cloudflare.com to the CSP of THAT page only
 *   verify($request)    server-side check of the token; true = let the submission through
 *
 * The secret is read straight from the environment when a check is made: it is never put in config, a view, a log line, a flash message or an
 * exception text. A network failure follows TURNSTILE_FAIL (open = the form still works and the failure is logged; closed = refused).
 * Tests inject $transport (array request => array answer) so no network is needed.
 */
final class Turnstile
{
    public const VERIFY_URL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
    public const FIELD = 'cf-turnstile-response';
    public const SCRIPT = 'https://challenges.cloudflare.com/turnstile/v0/api.js';

    /** @var ?\Closure(array<string,mixed>):array<string,mixed> test hook: replaces the network. Receives ['url','fields','timeout']; returns ['status'=>int,'body'=>string] or ['error'=>'timeout'|'network'] */
    public static ?\Closure $transport = null;

    /** set by widget(): this response embeds Turnstile, so its CSP must allow Cloudflare's script and frame */
    private static bool $rendered = false;

    public static function siteKey(): string
    {
        $k = (string) Config::get('turnstile.site_key', '');
        return preg_match('/^[0-9A-Za-z_\-]{4,100}$/', $k) === 1 ? $k : '';
    }

    private static function secret(): string
    {
        return trim((string) Env::get('TURNSTILE_SECRET_KEY', ''));
    }

    public static function enabled(): bool
    {
        return (bool) Config::get('turnstile.enabled', true) && self::siteKey() !== '' && self::secret() !== '';
    }

    /** 'open' (a failed check of the service lets the form through) or 'closed'. */
    public static function failMode(): string
    {
        return (string) Config::get('turnstile.fail', 'open') === 'closed' ? 'closed' : 'open';
    }

    /** Safe description for Admin > Security: no key, no part of a key. @return array{enabled:bool,site_key_set:bool,secret_set:bool,fail:string} */
    public static function status(): array
    {
        return ['enabled' => self::enabled(), 'site_key_set' => self::siteKey() !== '', 'secret_set' => self::secret() !== '', 'fail' => self::failMode()];
    }

    /** True when the CSP of the current response must allow Cloudflare's challenge (a widget was rendered). */
    public static function rendered(): bool
    {
        return self::$rendered;
    }

    public static function resetRendered(): void
    {
        self::$rendered = false;
    }

    /** The widget markup. Empty string when Turnstile is off. Put it inside the form, next to honeypot_field(). */
    public static function widget(string $action = ''): string
    {
        if (!self::enabled()) {
            return '';
        }
        self::$rendered = true;
        $act = preg_match('/^[A-Za-z0-9_\-]{1,32}$/', $action) === 1 ? ' data-action="' . $action . '"' : '';
        return '<div class="cf-turnstile" data-sitekey="' . htmlspecialchars(self::siteKey(), ENT_QUOTES, 'UTF-8') . '" data-theme="light" data-appearance="interaction-only"' . $act . '></div>'
            . '<script src="' . self::SCRIPT . '" async defer></script>';
    }

    /**
     * Server-side check. Returns true when Turnstile is off, when the token is valid, or when Cloudflare cannot be asked and TURNSTILE_FAIL=open.
     * A missing or invalid token is always false. Never throws; never logs the secret or the token.
     */
    public static function verify(Request $request): bool
    {
        if (!self::enabled()) {
            return true;
        }
        $token = $request->post(self::FIELD, '');
        $token = is_string($token) ? trim($token) : '';
        if ($token === '' || strlen($token) > 2048) {
            return false;
        }
        $fields = ['secret' => self::secret(), 'response' => $token, 'remoteip' => $request->ip()];
        $res = self::send(['url' => self::VERIFY_URL, 'fields' => $fields, 'timeout' => 5]);
        if (isset($res['error']) || (int) ($res['status'] ?? 0) < 200 || (int) ($res['status'] ?? 0) >= 300) {
            $why = isset($res['error']) ? (string) $res['error'] : 'http ' . (int) ($res['status'] ?? 0);
            Log::warning('Turnstile could not be reached (' . self::failMode() . ')', ['reason' => $why, 'path' => $request->path()]);
            return self::failMode() === 'open';
        }
        $json = json_decode((string) ($res['body'] ?? ''), true);
        if (!is_array($json)) {
            Log::warning('Turnstile gave an unreadable answer (' . self::failMode() . ')', ['path' => $request->path()]);
            return self::failMode() === 'open';
        }
        if (!empty($json['success'])) {
            return true;
        }
        $codes = array_map('strval', array_slice((array) ($json['error-codes'] ?? []), 0, 5));
        // "internal-error" is Cloudflare's own fault, not the visitor's: treat it like an unreachable service. Every other code means a bad token.
        if ($codes === ['internal-error']) {
            Log::warning('Turnstile internal error (' . self::failMode() . ')', ['path' => $request->path()]);
            return self::failMode() === 'open';
        }
        Log::info('Turnstile refused a submission', ['path' => $request->path(), 'codes' => implode(',', array_map(static fn($c) => preg_replace('/[^a-z0-9\-]/', '', $c), $codes))]);
        return false;
    }

    /** @param array{url:string,fields:array<string,string>,timeout:int} $req @return array<string,mixed> */
    private static function send(array $req): array
    {
        if (self::$transport !== null) {
            try {
                return (array) (self::$transport)($req);
            } catch (\Throwable) {
                return ['error' => 'network'];
            }
        }
        if (!function_exists('curl_init')) {
            return ['error' => 'network'];
        }
        $ch = curl_init($req['url']);
        curl_setopt_array($ch, [
            CURLOPT_POST => true, CURLOPT_POSTFIELDS => http_build_query($req['fields']), CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 3, CURLOPT_TIMEOUT => $req['timeout'], CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_SSL_VERIFYPEER => true, CURLOPT_SSL_VERIFYHOST => 2, CURLOPT_PROTOCOLS => CURLPROTO_HTTPS,
            CURLOPT_MAXFILESIZE => 65536, CURLOPT_HTTPHEADER => ['Accept: application/json'],
        ]);
        $body = curl_exec($ch);
        $errno = curl_errno($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);
        if ($body === false) {
            return ['error' => $errno === 28 ? 'timeout' : 'network'];
        }
        return ['status' => $status, 'body' => substr((string) $body, 0, 65536)];
    }
}
