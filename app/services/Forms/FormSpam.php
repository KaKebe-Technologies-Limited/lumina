<?php
declare(strict_types=1);

namespace App\Services\Forms;

use App\Core\Config;
use App\Core\RateLimit;
use App\Core\Request;

/**
 * SPAM PROTECTION of the public submit handler (no CAPTCHA). Contract: docs/FORMS.md section 8.
 *
 *   $v = FormSpam::check($request, $form);     // ['ok'=>bool, 'reason'=>?string, 'silent'=>bool, 'status'=>int]
 *
 * The form markup must contain <?= honeypot_field() ?> (a hidden input that humans never fill + a signed timestamp `_hp_ts`).
 * Levels (settings.spam): light (2 s minimum fill time, flood limit x2), standard (3 s), strict (6 s, flood limit /2, more than 3 web links in the answers).
 *   - a filled honeypot, a forged / missing timestamp or a link flood: silent = true -> pretend success (a bot learns nothing);
 *   - a too-fast submit: reason `too_fast`, HTTP 422, a visible "please press send again" (never a fake success: a person who corrects one field right after an
 *     error page would lose the whole form; the re-rendered form keeps the ORIGINAL timestamp, see Honeypot::field($keep));
 *   - an old timestamp (> 7 h) : not silent, reason `expired` (ask the person to reload the page);
 *   - the per-visitor flood limit (Site settings > Forms, per 10 minutes) and the optional per-form limit: reason `throttled`, status 429.
 * A throttled or silent result must NOT store anything. Nothing here logs the visitor's address (RateLimit keeps a keyed hash only).
 */
final class FormSpam
{
    public const MIN_SECONDS = ['light' => 2, 'standard' => 3, 'strict' => 6];

    /** @return array{ok:bool,reason:?string,silent:bool,status:int} */
    public static function check(Request $request, array $form): array
    {
        $level = (string) ($form['cfg']['spam'] ?? 'standard');
        $level = isset(self::MIN_SECONDS[$level]) ? $level : 'standard';
        $deny = static fn(string $reason, bool $silent, int $status = 200): array => ['ok' => false, 'reason' => $reason, 'silent' => $silent, 'status' => $status];

        $hpName = (string) Config::get('forms.honeypot_field', 'website');
        $hp = $request->post($hpName, '');
        if ((is_array($hp) && $hp !== []) || (is_string($hp) && trim($hp) !== '')) {
            return $deny('honeypot', true);
        }
        $raw = $request->post('_hp_ts', '');
        if (!is_string($raw) || !str_contains($raw, '.')) {
            return $deny('token', true);
        }
        [$ts, $sig] = explode('.', $raw, 2);
        if (!ctype_digit($ts) || !hash_equals(hash_hmac('sha256', $ts, Config::appKey()), $sig)) {
            return $deny('token', true);
        }
        $age = time() - (int) $ts;
        if ($age < self::MIN_SECONDS[$level]) {
            return $deny('too_fast', false, 422);          // visible: a real person who fixed one field quickly must not lose the form
        }
        if ($age > 25200) {
            return $deny('expired', false, 422);
        }
        if ($level === 'strict') {
            $links = 0;
            foreach ($request->all() as $k => $v) {
                if (is_string($k) && str_starts_with($k, 'f_')) {
                    foreach ((array) $v as $x) {
                        if (is_string($x)) {
                            $links += (int) preg_match_all('~https?://|www\.~i', $x);
                        }
                    }
                }
            }
            if ($links > 3) {
                return $deny('links', true);
            }
        }

        $limit = 10;
        try {
            $limit = max(2, (int) setting('forms_submit_limit', '10'));
        } catch (\Throwable) {
        }
        $limit = match ($level) { 'light' => $limit * 2, 'strict' => max(2, intdiv($limit, 2)), default => $limit };
        if (!RateLimit::attempt('forms:ip:' . $request->ip(), $limit, 600)) {
            return $deny('throttled', false, 429);
        }
        $perForm = (int) ($form['cfg']['throttle_form'] ?? 0);
        if ($perForm > 0 && !RateLimit::attempt('forms:form:' . (int) $form['id'], $perForm, 600)) {
            return $deny('throttled', false, 429);
        }
        return ['ok' => true, 'reason' => null, 'silent' => false, 'status' => 200];
    }
}
