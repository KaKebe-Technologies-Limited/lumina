<?php
/*
 * Global helper functions (loaded by app/bootstrap.php). FOUNDATION-OWNED - do not edit; add your own helpers
 * in a class inside your own files instead. Exact signatures are documented in docs/ARCHITECTURE.md.
 */
declare(strict_types=1);

use App\Core\Auth;
use App\Core\Config;
use App\Core\Content;
use App\Core\Csrf;
use App\Core\Flash;
use App\Core\FormState;
use App\Core\Honeypot;
use App\Core\HttpException;
use App\Core\Icons;
use App\Core\Nav;
use App\Core\Request;
use App\Core\Response;
use App\Core\Router;
use App\Core\Sanitizer;
use App\Core\Settings;
use App\Core\SitePages;
use App\Core\View;

// ------------------------------------------------------------------------------------------------ escaping / output

/** Escape for HTML text and attribute values. ALWAYS use on dynamic output. */
function e(mixed $value): string
{
    if ($value === null || is_array($value) || is_object($value) && !method_exists($value, '__toString')) {
        return '';
    }
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Sanitised rich text for OUTPUT (DB bodies, richtext fields): Sanitizer::html() + base-path expansion. Never echo raw HTML otherwise. */
function rich(?string $html): string
{
    return Sanitizer::rich($html);
}

/** Escape + convert blank-line separated text into <p> paragraphs (single newlines become <br>). For textarea content. */
function nl2p(?string $text): string
{
    $text = trim(str_replace(["\r\n", "\r"], "\n", (string) $text));
    if ($text === '') {
        return '';
    }
    $out = '';
    foreach (preg_split("/\n{2,}/", $text) ?: [] as $para) {
        $out .= '<p>' . nl2br(e(trim($para)), false) . '</p>';
    }
    return $out;
}

/** Visitor-facing name of a job's type of work (editable: Pages > Careers > Names of the types of work). */
function job_type_label(?string $type): string
{
    $type = (string) $type;
    $v = trim((string) content('careers', 'types.' . $type));
    return $v !== '' ? $v : label_from($type);
}

/** Escaped e-mail address that may break after the @ sign on narrow screens (never in the middle of the name). */
function email_html(?string $email): string
{
    return str_replace('@', '@<wbr>', e($email));
}

/** Plain-text excerpt of (possibly HTML) text, cut at a word boundary with an ellipsis. */
function excerpt(?string $text, int $length = 160): string
{
    $t = html_entity_decode(strip_tags((string) $text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $t = trim((string) preg_replace('/\s+/u', ' ', $t));
    if (mb_strlen($t) <= $length) {
        return $t;
    }
    $cut = mb_substr($t, 0, $length);
    $space = mb_strrpos($cut, ' ');
    if ($space !== false && $space > $length * 0.6) {
        $cut = mb_substr($cut, 0, $space);
    }
    return rtrim($cut, " \t\n\r,.;:-") . '…';
}

/** Build an HTML attribute string from an array: attrs(['class' => 'a b', 'disabled' => true, 'data-x' => null]) -> ' class="a b" disabled'. */
function attrs(array $attributes): string
{
    $out = '';
    foreach ($attributes as $k => $v) {
        if ($v === null || $v === false) {
            continue;
        }
        $out .= $v === true ? ' ' . e($k) : ' ' . e($k) . '="' . e($v) . '"';
    }
    return $out;
}

function icon(string $name, string $class = '', ?string $label = null): string
{
    return Icons::svg($name, $class, $label);
}

/** Render a view partial and RETURN the HTML (echo it: <?= partial('partials/flash') ?>). */
function partial(string $name, array $data = []): string
{
    return View::partial($name, $data);
}

// ------------------------------------------------------------------------------------------------ URLs

function request(): ?Request
{
    return Request::current();
}

/** Public URL prefix: '' at a domain root, '/lumina' under XAMPP. */
function base_path(): string
{
    $req = Request::current();
    if ($req !== null) {
        return $req->basePath();
    }
    return rtrim((string) (parse_url((string) Config::get('app.url', ''), PHP_URL_PATH) ?? ''), '/');
}

/**
 * URL for a path inside the site, prefixed with the runtime base path. url('about') -> '/lumina/about', url('') -> '/lumina/'.
 * Absolute (http/https), mailto:, tel: and #anchor values are returned unchanged. $query is appended (null/'' values skipped).
 */
function url(string $path = '', array $query = [], bool $absolute = false): string
{
    if (preg_match('~^(https?:)?//|^(mailto|tel|sms):|^#~i', $path)) {
        return $path;
    }
    $path = ltrim($path, '/');
    $u = base_path() . '/' . $path;
    if ($query !== []) {
        $q = http_build_query(array_filter($query, static fn($v) => $v !== null && $v !== ''));
        if ($q !== '') {
            $u .= (str_contains($u, '?') ? '&' : '?') . $q;
        }
    }
    return $absolute ? origin() . $u : $u;
}

/** Scheme + host (no base path, no trailing slash). Honours APP_URL when set. */
function origin(): string
{
    $configured = (string) Config::get('app.url', '');
    if ($configured !== '') {
        $p = parse_url($configured);
        if (!empty($p['scheme']) && !empty($p['host'])) {
            return $p['scheme'] . '://' . $p['host'] . (isset($p['port']) ? ':' . $p['port'] : '');
        }
    }
    $req = Request::current();
    return $req ? $req->origin() : 'http://localhost';
}

/** Absolute URL: abs_url('about') -> 'https://host/lumina/about'. */
function abs_url(string $path = '', array $query = []): string
{
    return url($path, $query, true);
}

/** Static asset URL with a cache-busting ?v=<mtime>: asset('css/main.min.css'). */
function asset(string $path): string
{
    $path = ltrim($path, '/');
    $file = ASSET_PATH . '/' . $path;
    $v = is_file($file) ? '?v=' . filemtime($file) : '';
    return url('assets/' . $path) . $v;
}

/** URL of an uploaded file given the path stored in the DB ("2026/10/abc.jpg"). */
function upload_url(?string $path): string
{
    return $path === null || $path === '' ? '' : url('uploads/' . ltrim($path, '/'));
}

/**
 * URL for any stored image path: '' -> '', 'https://...' as is, 'assets/img/x.jpg' -> bundled asset, otherwise an upload
 * ("2026/10/x.jpg" -> /uploads/2026/10/x.jpg). Use this for every image column and image content field.
 */
function img_url(?string $path): string
{
    $path = trim((string) $path);
    if ($path === '') {
        return '';
    }
    if (preg_match('~^https?://~i', $path)) {
        return $path;
    }
    if (str_starts_with($path, 'assets/')) {
        return asset(substr($path, 7));
    }
    return upload_url($path);
}

/** Absolute URL of a stored image path (for og:image / JSON-LD). */
function abs_url_for_image(?string $path): string
{
    $u = img_url($path);
    if ($u === '' || preg_match('~^https?://~i', $u)) {
        return $u;
    }
    return origin() . $u;
}

/**
 * Logo for small UI spots (header, footer, admin sidebar, receipts). The bundled default logo has light copies (64/128/192/512 px
 * in assets/img/brand/), so a 60 px header logo no longer downloads the 512 px original; a logo the owner uploaded in the
 * settings is used as it is. $px = the CSS size the logo is shown at (the next copy up is picked for sharp retina screens).
 */
function logo_url(int $px = 64): string
{
    $path = trim((string) setting('logo_path', 'assets/img/brand/logo-512.png'));
    if ($path === '' || str_starts_with($path, 'assets/img/brand/logo-')) {
        $want = $px * 2;
        $size = $want <= 64 ? 64 : ($want <= 128 ? 128 : ($want <= 192 ? 192 : 512));
        return asset('img/brand/logo-' . $size . '.png');
    }
    return img_url($path);
}

/** Turn a STORED link (url content fields, settings) into a href: '/about' -> '/lumina/about'; http(s)/mailto/tel/# unchanged. */
function link_url(?string $stored): string
{
    $s = trim((string) $stored);
    if ($s === '') {
        return '';
    }
    if (preg_match('~^(https?:|mailto:|tel:|sms:|#)~i', $s)) {
        return $s;
    }
    return url($s);
}

/** True when a stored link points off-site (so templates can add target/rel). */
function is_external(?string $stored): bool
{
    return (bool) preg_match('~^https?://~i', trim((string) $stored)) && !str_starts_with(trim((string) $stored), origin());
}

/** URL for a named route (->name('blog.show')): route('blog.show', ['slug' => 'x']). */
function route(string $name, array $params = [], array $query = []): string
{
    return url(ltrim(Router::path($name, $params), '/'), $query);
}

function current_path(): string
{
    return Request::current()?->path() ?? '/';
}

/** Is the current request at/below $path ('blog' or '/blog')? */
function is_path(string $path): bool
{
    $p = '/' . trim($path, '/');
    $cur = current_path();
    return $p === '/' ? $cur === '/' : ($cur === $p || str_starts_with($cur, $p . '/'));
}

function redirect(string $path, int $status = 302): Response
{
    return Response::redirect(preg_match('~^https?://~i', $path) ? $path : url($path), $status);
}

function abort(int $status, string $message = ''): never
{
    throw new HttpException($status, $message);
}

// ------------------------------------------------------------------------------------------------ forms / flash

function csrf_token(): string
{
    return Csrf::token();
}

/** Hidden CSRF input. REQUIRED in every <form method="post">. */
function csrf_field(): string
{
    return Csrf::field();
}

/** Hidden _method input to spoof PUT/PATCH/DELETE from an HTML form (the form itself must still be method="post"). */
function method_field(string $method): string
{
    return '<input type="hidden" name="_method" value="' . e(strtoupper($method)) . '">';
}

/** Spam-trap fields for PUBLIC forms (hidden input + signed timestamp). */
function honeypot_field(): string
{
    return Honeypot::field();
}

/** Old input after a failed validation redirect (ESCAPE it: <?= e(old('email')) ?>). */
function old(string $key, mixed $default = ''): mixed
{
    $old = FormState::old();
    return array_key_exists($key, $old) ? $old[$key] : $default;
}

/** First validation error for a field, or null. */
function error(string $key): ?string
{
    $e = FormState::errors();
    return isset($e[$key]) ? (string) $e[$key] : null;
}

function has_error(string $key): bool
{
    return error($key) !== null;
}

/** @return array<string,string> */
function errors(): array
{
    return FormState::errors();
}

/**
 * flash($type, $message) queues a message for the next page view; flash() with no arguments returns and clears the
 * queued messages as [['type' => 'success|error|info|warning', 'message' => '...'], ...] (partials/flash.php does this).
 */
function flash(?string $type = null, ?string $message = null): array
{
    if ($type !== null && $message !== null) {
        Flash::add($type, $message);
        return [];
    }
    return Flash::pull();
}

// ------------------------------------------------------------------------------------------------ settings & auth

function config(string $key, mixed $default = null): mixed
{
    return Config::get($key, $default);
}

/** Organisation-wide setting (DB value, else app/config/settings_schema.php default, else $default). */
function setting(string $key, mixed $default = null): mixed
{
    return Settings::get($key, $default);
}

/**
 * The Google Analytics measurement ID to use on THIS request: '' when none is set, and always '' on the personal meeting links (/meeting/{token}...): their
 * address holds the invitee's only credential and must never reach a third party. Decided by PATH on the server, so it also covers the error pages
 * (404, 419, 429, 500) of such an address, and the layout, the CSP header and the page script all agree.
 */
function analytics_id(): string
{
    $id = trim((string) setting('analytics_id', ''));
    if ($id === '') {
        return '';
    }
    $path = (string) (\App\Core\Request::current()?->path() ?? '');
    return preg_match('~^/?meeting/~i', $path) === 1 ? '' : $id;
}

function current_user(): ?array
{
    return Auth::user();
}

/** Does the logged-in user hold the ability? (abilities and roles: config app.abilities) */
function can(string $ability): bool
{
    $map = (array) Config::get('abilities', []);
    if (!isset($map[$ability])) {
        if (Config::get('app.debug')) {
            throw new InvalidArgumentException("Unknown ability: {$ability}");
        }
        return false;
    }
    return \App\Core\Roles::can(Auth::role(), $ability);      // roles table (custom roles) with the built-in map as fallback
}

/** [['key','label','url','icon'], ...] of the social profiles that have a URL in settings (footer, JSON-LD sameAs). */
function social_links(): array
{
    $defs = [
        'facebook' => ['Facebook', 'facebook'], 'instagram' => ['Instagram', 'instagram'], 'linkedin' => ['LinkedIn', 'linkedin'],
        'x' => ['X', 'twitter'], 'tiktok' => ['TikTok', 'tiktok'], 'youtube' => ['YouTube', 'youtube'],
    ];
    $out = [];
    foreach ($defs as $key => [$label, $icon]) {
        $u = trim((string) setting('social_' . $key, ''));
        if ($u !== '') {
            $out[] = ['key' => $key, 'label' => $label, 'url' => $u, 'icon' => $icon];
        }
    }
    return $out;
}

/** 'tel:' href from a phone number: phone_href('+256-779-712-990') -> 'tel:+256779712990'. */
function phone_href(?string $phone): string
{
    $d = preg_replace('/[^0-9+]/', '', (string) $phone) ?? '';
    return $d === '' ? '' : 'tel:' . $d;
}

/** WhatsApp link from the 'whatsapp' setting (a wa.me URL or a number). */
function whatsapp_url(): string
{
    $w = trim((string) setting('whatsapp', ''));
    if ($w === '') {
        return '';
    }
    if (preg_match('~^https?://~i', $w)) {
        return $w;
    }
    return 'https://wa.me/' . preg_replace('/[^0-9]/', '', $w);
}

// ------------------------------------------------------------------------------------------------ CMS content (page registry)

/**
 * Editable copy. content('home', 'hero.title') -> typed value (string; bool for boolean; int|float for number; array for repeater).
 * Returns the owner's saved text or the registry default. NOT escaped: <?= e(content('home', 'hero.title')) ?>.
 */
function content(string $page, string $key, mixed $default = null): mixed
{
    return Content::get($page, $key, $default);
}

/** Sanitised rich-text block, ready to echo (it is HTML): <?= content_html('about', 'story.body') ?> */
function content_html(string $page, string $key): string
{
    return Sanitizer::rich((string) Content::get($page, $key, ''));
}

/**
 * Image block: ['src' => url or '', 'alt' => string, 'path' => stored path, 'has' => bool].
 *   <?php $img = content_img('home', 'hero.image'); if ($img['has']): ?><img src="<?= e($img['src']) ?>" alt="<?= e($img['alt']) ?>"><?php endif; ?>
 */
function content_img(string $page, string $key): array
{
    return Content::image($page, $key);
}

/** Repeater rows (each row has every sub-field key; image sub-fields also have "<key>_alt"). Use img_url($row['image']). */
function content_list(string $page, string $key): array
{
    return Content::list($page, $key);
}

/**
 * Button = two fields "<key>_label" and "<key>_url". content_btn('home', 'hero.cta') ->
 * ['label' => string, 'url' => resolved href, 'has' => bool (label AND url non-empty), 'external' => bool].
 */
function content_btn(string $page, string $key): array
{
    $label = trim((string) Content::get($page, $key . '_label', ''));
    $stored = trim((string) Content::get($page, $key . '_url', ''));
    return ['label' => $label, 'url' => link_url($stored), 'has' => $label !== '' && $stored !== '', 'external' => is_external($stored)];
}

/** Is the (toggle-able) section visible? Sections without 'toggle' => true are always on. */
function section_on(string $page, string $sectionKey): bool
{
    return Content::sectionOn($page, $sectionKey);
}

/**
 * Section wrapper that respects section_on(): everything between section_begin() and section_end() is printed inside
 * <section id="key" class="section {class}"><div class="container {container}"> ... </div></section> ONLY when the section is
 * switched on; otherwise it is discarded. opts: class, container (extra container class, e.g. 'container--narrow'),
 * id (default = key), bleed (true = no .container wrapper), attrs (array of extra attributes), labelledby (id of the heading).
 *   <?php section_begin('home', 'challenge', ['class' => 'section--tint']); ?> ...markup... <?php section_end(); ?>
 */
function section_begin(string $page, string $sectionKey, array $opts = []): void
{
    $GLOBALS['__lumina_sections'][] = ['on' => section_on($page, $sectionKey), 'key' => $sectionKey, 'opts' => $opts];
    ob_start();
}

function section_end(): void
{
    $html = (string) ob_get_clean();
    $s = array_pop($GLOBALS['__lumina_sections']);
    if ($s === null || !$s['on']) {
        return;
    }
    echo partial('partials/section', ['key' => $s['key'], 'opts' => $s['opts'], 'inner' => $html]);
}

/**
 * SEO/meta array for a page: title, full_title, description, og_image (absolute), og_type, robots, canonical (absolute), site_name.
 * $override may set title, description, og_image, og_type, robots, canonical (for detail pages).
 */
function page_meta(string $slug, array $override = []): array
{
    return SitePages::meta($slug, $override);
}

/** Is the nav item for page $slug (or a child) the current page? */
function is_active_nav(string $slug): bool
{
    foreach (array_merge(Nav::header(), Nav::buttons(), Nav::footer()) as $item) {
        if ($item['slug'] === $slug) {
            return (bool) $item['active'];
        }
        foreach ($item['children'] as $child) {
            if ($child['slug'] === $slug) {
                return (bool) $child['active'];
            }
        }
    }
    return false;
}

// ------------------------------------------------------------------------------------------------ formatting

/** Format a date string / DateTimeInterface / timestamp: format_date('2026-03-01') -> '1 March 2026'. Returns '' for empty/invalid. */
function format_date(mixed $date, string $format = 'j F Y'): string
{
    $dt = to_datetime($date);
    return $dt ? $dt->format($format) : '';
}

function format_datetime(mixed $date, string $format = 'j F Y, H:i'): string
{
    return format_date($date, $format);
}

/** Convert string|int|DateTimeInterface|null to DateTimeImmutable|null. */
function to_datetime(mixed $date): ?DateTimeImmutable
{
    if ($date === null || $date === '' || $date === '0000-00-00' || $date === '0000-00-00 00:00:00') {
        return null;
    }
    try {
        if ($date instanceof DateTimeInterface) {
            return DateTimeImmutable::createFromInterface($date);
        }
        if (is_int($date) || (is_string($date) && ctype_digit($date) && strlen($date) >= 9)) {
            return (new DateTimeImmutable('@' . $date))->setTimezone(new DateTimeZone(date_default_timezone_get()));
        }
        return new DateTimeImmutable((string) $date);
    } catch (Throwable) {
        return null;
    }
}

/** 1536 -> '1.5 KB'. */
function human_filesize(int|float|null $bytes): string
{
    $b = (float) $bytes;
    foreach (['B', 'KB', 'MB', 'GB'] as $i => $unit) {
        if ($b < 1024 || $unit === 'GB') {
            return ($i === 0 ? (string) (int) $b : rtrim(rtrim(number_format($b, 1), '0'), '.')) . ' ' . $unit;
        }
        $b /= 1024;
    }
    return '';
}

/** Human label from a snake_case value: label_from('annual_report') -> 'Annual report'. */
function label_from(?string $value): string
{
    return ucfirst(str_replace('_', ' ', (string) $value));
}

// Design-system helpers (highlight(), youtube_id(), photo_tag(), icon_options() ...): docs/DESIGN.md
require_once __DIR__ . '/helpers_design.php';
