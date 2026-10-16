<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Spam trap for PUBLIC forms (no CAPTCHA). Put <?= honeypot_field() ?> inside every public <form method="post">
 * and add the 'honeypot' middleware (and 'throttle:N,S') to the POST route.
 *
 * The field renders (a) a visually hidden text input that humans never fill, and (b) a signed timestamp.
 * A submission is rejected when the hidden input is filled, the timestamp is missing/forged, or the form
 * was submitted faster than forms.min_seconds or older than forms.max_seconds.
 *
 *   if (!Honeypot::passes($request)) { ...pretend success... }     // controllers can call it directly too
 */
final class Honeypot
{
    private const TS = '_hp_ts';

    public static function field(): string
    {
        $name = (string) Config::get('forms.honeypot_field', 'website');
        $ts = (string) time();
        $sig = hash_hmac('sha256', $ts, Config::appKey());
        return '<div class="hp-field" aria-hidden="true" style="position:absolute;left:-9999px;width:1px;height:1px;overflow:hidden">'
            . '<label>Leave this field empty<input type="text" name="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '" value="" tabindex="-1" autocomplete="off"></label>'
            . '<input type="hidden" name="' . self::TS . '" value="' . $ts . '.' . $sig . '">'
            . '</div>';
    }

    public static function passes(Request $request): bool
    {
        $name = (string) Config::get('forms.honeypot_field', 'website');
        if ($request->string($name) !== '') {
            return false;
        }
        $raw = (string) $request->post(self::TS, '');
        if (!str_contains($raw, '.')) {
            return false;
        }
        [$ts, $sig] = explode('.', $raw, 2);
        if (!ctype_digit($ts) || !hash_equals(hash_hmac('sha256', $ts, Config::appKey()), $sig)) {
            return false;
        }
        $age = time() - (int) $ts;
        return $age >= (int) Config::get('forms.min_seconds', 3) && $age <= (int) Config::get('forms.max_seconds', 7200);
    }

    /** Same-origin referer path (or the home page) used by the middleware to bounce a rejected bot. */
    public static function safeBackUrl(Request $request): string
    {
        $ref = $request->referer();
        if ($ref !== null) {
            $host = (string) parse_url($ref, PHP_URL_HOST);
            if ($host !== '' && strtolower($host) === strtolower(explode(':', $request->host())[0])) {
                return $ref;
            }
        }
        return url('');
    }
}
