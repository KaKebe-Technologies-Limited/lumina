<?php
declare(strict_types=1);

namespace App\Services\Inbox;

/**
 * Small pure helpers shared by the admin inbox controllers and views (B5 "admin-inbox").
 */
final class Support
{
    /** '%term%' for a LIKE clause with the wildcard characters of the term escaped (MySQL default escape is the backslash). */
    public static function like(string $term): string
    {
        $term = trim(mb_substr($term, 0, 100));
        if ($term === '') {
            return '';
        }
        $bs = chr(92);
        return '%' . str_replace([$bs, '%', '_'], [$bs . $bs, $bs . '%', $bs . '_'], $term) . '%';
    }

    /** A real calendar date in Y-m-d, else null. */
    public static function date(?string $value): ?string
    {
        $value = trim((string) $value);
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return null;
        }
        $d = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        return $d !== false && $d->format('Y-m-d') === $value ? $value : null;
    }

    /** 1500000 UGX -> "UGX 1,500,000"; 12.5 USD -> "USD 12.50"; null -> "-". */
    public static function money(mixed $amount, ?string $currency = null): string
    {
        if ($amount === null || $amount === '') {
            return '-';
        }
        $n = (float) $amount;
        $decimals = abs($n - round($n)) < 0.005 ? 0 : 2;
        $cur = strtoupper(trim((string) $currency));
        return ($cur !== '' ? $cur . ' ' : '') . number_format($n, $decimals);
    }

    /** Safe mailto: link for a stored address ('' when the address is not a clean single email). */
    public static function mailto(?string $email, string $subject = '', string $body = ''): string
    {
        $email = trim((string) $email);
        if ($email === '' || !\App\Core\Mailer::validEmail($email)) {
            return '';
        }
        $q = [];
        if ($subject !== '') {
            $q[] = 'subject=' . rawurlencode($subject);
        }
        if ($body !== '') {
            $q[] = 'body=' . rawurlencode($body);
        }
        return 'mailto:' . $email . ($q ? '?' . implode('&', $q) : '');
    }

    /** Link target for a stored http(s) URL, else ''. */
    public static function webUrl(?string $url): string
    {
        $url = trim((string) $url);
        return preg_match('~^https?://[^\s<>"\']+$~i', $url) === 1 ? $url : '';
    }

    /** Short "Browser on OS" label from a user-agent string (best effort, display only). */
    public static function agent(?string $ua): string
    {
        $ua = (string) $ua;
        if ($ua === '') {
            return '';
        }
        $browser = 'Browser';
        foreach (['Edg' => 'Edge', 'OPR' => 'Opera', 'Firefox' => 'Firefox', 'Chrome' => 'Chrome', 'Safari' => 'Safari'] as $needle => $label) {
            if (str_contains($ua, $needle)) {
                $browser = $label;
                break;
            }
        }
        $os = '';
        foreach (['Windows' => 'Windows', 'Android' => 'Android', 'iPhone' => 'iOS', 'iPad' => 'iOS', 'Mac OS' => 'macOS', 'Linux' => 'Linux'] as $needle => $label) {
            if (str_contains($ua, $needle)) {
                $os = $label;
                break;
            }
        }
        return $browser . ($os !== '' ? ' on ' . $os : '');
    }
}
