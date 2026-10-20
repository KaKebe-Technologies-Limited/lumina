<?php
/*
 * DESIGN-SYSTEM helpers for the public site (loaded at the end of app/helpers.php). Documented in docs/DESIGN.md.
 *
 *   highlight($text)         heading text with ONE phrase wrapped in the hand-drawn accent stroke:  "Together, we [[change lives]]"
 *   strip_highlight($text)   the same text without the markers (for <title>, aria-label, alt text)
 *   youtube_id($url)         11-character YouTube id from any youtube.com / youtu.be / nocookie URL, '' when it is not one
 *   photo_tag($img, ...)     <img> from a content_img() result (returns '' when there is no image)
 *   icon_options()           [icon name => label] for registry 'select' fields (feature icons)
 *   safe_icon($name)         $name when it is a known icon, else the fallback
 *   donate_prefill()         ['amount' => '', 'currency' => ''] read (and validated) from ?amount=&currency=
 *
 * Every function here escapes its own output: the return value of highlight() and photo_tag() is HTML that is safe to echo.
 */
declare(strict_types=1);

use App\Core\Icons;

/**
 * Accent-stroke markup for CMS text fields. The text is HTML-escaped FIRST; only afterwards the markers are turned into a span,
 * so an editor can never inject markup. Markers: [[phrase]] (preferred) or *phrase* (no space just inside the stars).
 * Unbalanced markers are left as typed. Returns HTML.
 *
 *   <h1><?= highlight(content('home', 'hero.title')) ?></h1>
 */
function highlight(?string $text, string $class = 'hl'): string
{
    $html = e((string) $text);
    $class = preg_replace('/[^a-z0-9_\- ]/i', '', $class) ?? 'hl';
    $wrap = static fn(array $m): string => '<span class="' . $class . '">' . $m[1] . '</span>';
    $count = 0;
    $html = preg_replace_callback('/\[\[(.+?)\]\]/su', $wrap, $html, -1, $count) ?? $html;
    if ($count > 0) {
        return $html;               // [[ ]] wins: never nest two strokes
    }
    // one-line *phrase*: the star must touch the text and must not sit next to another star or a word character on the outside
    $html = preg_replace_callback('/(?<![\w*])\*(?=\S)([^*\r\n]+?)(?<=\S)\*(?![\w*])/u', $wrap, $html) ?? $html;
    return $html;
}

/** The same text without highlight markers, as PLAIN text (not escaped): e(strip_highlight($t)) for attributes / <title>. */
function strip_highlight(?string $text): string
{
    $t = (string) $text;
    $t = preg_replace('/\[\[(.+?)\]\]/su', '$1', $t) ?? $t;
    return preg_replace('/(?<![\w*])\*(?=\S)([^*\r\n]+?)(?<=\S)\*(?![\w*])/u', '$1', $t) ?? $t;
}

/** YouTube video id (11 chars) from a watch / embed / shorts / youtu.be / nocookie URL or a bare id. '' when none. */
function youtube_id(?string $url): string
{
    $u = trim((string) $url);
    if ($u === '') {
        return '';
    }
    if (preg_match('/^[A-Za-z0-9_-]{11}$/', $u)) {
        return $u;
    }
    $parts = parse_url($u);
    if ($parts === false || empty($parts['host']) || !in_array(strtolower((string) ($parts['scheme'] ?? '')), ['http', 'https'], true)) {
        return '';
    }
    $host = strtolower((string) $parts['host']);
    $host = preg_replace('/^(www|m)\./', '', $host) ?? $host;
    $id = '';
    if ($host === 'youtu.be') {
        $id = trim((string) ($parts['path'] ?? ''), '/');
    } elseif (in_array($host, ['youtube.com', 'youtube-nocookie.com'], true)) {
        $path = (string) ($parts['path'] ?? '');
        if (preg_match('~^/(?:embed|shorts|live|v)/([A-Za-z0-9_-]{11})~', $path, $m)) {
            $id = $m[1];
        } elseif ($path === '/watch') {
            parse_str((string) ($parts['query'] ?? ''), $q);
            $id = is_string($q['v'] ?? null) ? $q['v'] : '';
        }
    }
    return preg_match('/^[A-Za-z0-9_-]{11}$/', $id) ? $id : '';
}

/**
 * <img> for a content_img() result: ['src','alt','has'] (+ optional 'path'). Extra attributes override the defaults
 * (loading="lazy" decoding="async"); pass 'loading' => 'eager' + 'fetchpriority' => 'high' for the hero.
 *   <?= photo_tag(content_img('home', 'who.image'), 'figure-round__img') ?>
 */
function photo_tag(array $img, string $class = '', array $attrs = []): string
{
    if (empty($img['has']) || empty($img['src'])) {
        return '';
    }
    $base = ['class' => $class !== '' ? $class : null, 'src' => (string) $img['src'], 'alt' => (string) ($img['alt'] ?? ''), 'loading' => 'lazy', 'decoding' => 'async'];
    return '<img' . attrs($attrs + $base) . '>';
}

/** Curated icons an editor can pick for feature cards (registry 'select' options). Names are keys of App\Core\Icons. */
function icon_options(): array
{
    $all = [
        'laptop' => 'Laptop', 'monitor' => 'Computer screen', 'smartphone' => 'Mobile phone', 'wifi' => 'Internet / Wi-Fi', 'code' => 'Coding',
        'graduation-cap' => 'Graduation / training', 'book-open' => 'Book / learning', 'lightbulb' => 'Idea / light', 'flame' => 'Flame / spark', 'sprout' => 'Growth',
        'handshake' => 'Handshake / partnership', 'users' => 'People / community', 'user' => 'Person', 'heart' => 'Heart / care', 'hand-heart' => 'Giving hand',
        'briefcase' => 'Briefcase / jobs', 'target' => 'Target / goal', 'rocket' => 'Rocket / launch', 'trending-up' => 'Growth chart', 'award' => 'Award',
        'globe' => 'Globe', 'map-pin' => 'Location', 'calendar' => 'Calendar', 'mail' => 'Email', 'phone' => 'Phone', 'shield' => 'Shield / safety',
        'star' => 'Star', 'check-circle' => 'Check mark', 'banknote' => 'Money / income', 'home' => 'Home', 'compass' => 'Compass / direction', 'mic' => 'Microphone / voice',
        'shopping-bag' => 'Shopping bag', 'package' => 'Package / delivery',
    ];
    $known = array_flip(Icons::names());
    return array_intersect_key($all, $known);
}

/** $name when it is a known icon, else $fallback (so an old or mistyped value never renders the placeholder circle). */
function safe_icon(?string $name, string $fallback = 'check-circle'): string
{
    $n = trim((string) $name);
    return $n !== '' && in_array($n, Icons::names(), true) ? $n : $fallback;
}

/**
 * Amount / currency that the home-page donation panel passes to /donate (?amount=50&currency=USD). Both are validated here, so
 * the result is always safe to echo into a form field: amount = digits with at most one decimal point (max 12 chars), else ''.
 * currency = one of $allowed (default UGX USD EUR GBP), else ''.
 *   $pre = donate_prefill();  value="<?= e(old('amount', $pre['amount'])) ?>"
 */
function donate_prefill(array $allowed = ['UGX', 'USD', 'EUR', 'GBP']): array
{
    $req = request();
    $amount = $req !== null ? $req->query('amount') : null;
    $cur = $req !== null ? $req->query('currency') : null;
    $amount = is_scalar($amount) ? trim((string) $amount) : '';
    $cur = is_scalar($cur) ? strtoupper(trim((string) $cur)) : '';
    if (!preg_match('/^\d{1,9}(\.\d{1,2})?$/', $amount)) {
        $amount = '';
    }
    return ['amount' => $amount, 'currency' => in_array($cur, $allowed, true) ? $cur : ''];
}
