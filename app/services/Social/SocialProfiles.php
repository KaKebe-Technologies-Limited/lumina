<?php
declare(strict_types=1);

namespace App\Services\Social;

use App\Core\AuditLog;
use App\Core\Settings;

/**
 * The organisation's PUBLIC social-media profile links (docs/SOCIAL.md section 2): Facebook, Instagram, X, LinkedIn, TikTok, YouTube, WhatsApp, Telegram, Threads.
 * They live in the existing `social_*` settings (every old key keeps working: social_facebook ... social_youtube) plus social_threads, social_telegram,
 * social_whatsapp_channel, the order (social_order) and where they show (social_show_footer | contact | newsletter, and maintenance_show_social for the maintenance page).
 *
 *   SocialProfiles::links('footer')   -> [['key','label','url','icon'], ...] in the admin's order; only platforms that have an address, only where the toggle allows
 *   SocialProfiles::normalise('instagram', '@luminaafrica') -> ['ok'=>true,'url'=>'https://www.instagram.com/luminaafrica/','warning'=>'','error'=>'']
 * The global helper social_links($context = 'all') (app/helpers.php) is a thin wrapper around links().
 * Addresses are https only; a host that is not the platform's own is ACCEPTED WITH A WARNING (short links and custom domains exist). Never javascript:, data:, http:.
 */
final class SocialProfiles
{
    public const CONTEXTS = ['all', 'footer', 'contact', 'newsletter', 'maintenance'];

    /** platform => label, short label, icon, setting key, accepted hosts, example, how a bare handle becomes an address */
    public const PLATFORMS = [
        'facebook'  => ['name' => 'Facebook', 'label' => 'Facebook', 'icon' => 'facebook', 'setting' => 'social_facebook', 'hosts' => ['facebook.com', 'fb.com', 'fb.me'], 'example' => 'https://www.facebook.com/yourpage', 'handle' => 'https://www.facebook.com/%s'],
        'instagram' => ['name' => 'Instagram', 'label' => 'Instagram', 'icon' => 'instagram', 'setting' => 'social_instagram', 'hosts' => ['instagram.com'], 'example' => 'https://www.instagram.com/yourname/', 'handle' => 'https://www.instagram.com/%s/'],
        'x'         => ['name' => 'X (Twitter)', 'label' => 'X', 'icon' => 'twitter', 'setting' => 'social_x', 'hosts' => ['x.com', 'twitter.com'], 'example' => 'https://x.com/yourname', 'handle' => 'https://x.com/%s'],
        'linkedin'  => ['name' => 'LinkedIn', 'label' => 'LinkedIn', 'icon' => 'linkedin', 'setting' => 'social_linkedin', 'hosts' => ['linkedin.com', 'lnkd.in'], 'example' => 'https://www.linkedin.com/company/your-page', 'handle' => ''],
        'tiktok'    => ['name' => 'TikTok', 'label' => 'TikTok', 'icon' => 'tiktok', 'setting' => 'social_tiktok', 'hosts' => ['tiktok.com'], 'example' => 'https://www.tiktok.com/@yourname', 'handle' => 'https://www.tiktok.com/@%s'],
        'youtube'   => ['name' => 'YouTube', 'label' => 'YouTube', 'icon' => 'youtube', 'setting' => 'social_youtube', 'hosts' => ['youtube.com', 'youtu.be'], 'example' => 'https://www.youtube.com/@yourchannel', 'handle' => 'https://www.youtube.com/@%s'],
        'whatsapp'  => ['name' => 'WhatsApp (channel or number)', 'label' => 'WhatsApp', 'icon' => 'whatsapp', 'setting' => 'social_whatsapp_channel', 'hosts' => ['wa.me', 'whatsapp.com'], 'example' => 'https://whatsapp.com/channel/... or https://wa.me/256700000000', 'handle' => ''],
        'telegram'  => ['name' => 'Telegram', 'label' => 'Telegram', 'icon' => 'telegram', 'setting' => 'social_telegram', 'hosts' => ['t.me', 'telegram.me', 'telegram.dog'], 'example' => 'https://t.me/yourchannel', 'handle' => 'https://t.me/%s'],
        'threads'   => ['name' => 'Threads', 'label' => 'Threads', 'icon' => 'threads', 'setting' => 'social_threads', 'hosts' => ['threads.net', 'threads.com'], 'example' => 'https://www.threads.net/@yourname', 'handle' => 'https://www.threads.net/@%s'],
    ];
    /** where a toggle lives: context => setting key */
    public const TOGGLES = ['footer' => 'social_show_footer', 'contact' => 'social_show_contact', 'newsletter' => 'social_show_newsletter', 'maintenance' => 'maintenance_show_social'];

    /** @return array<string,array<string,mixed>> */
    public static function platforms(): array
    {
        return self::PLATFORMS;
    }

    /**
     * Validate one address typed by the admin and turn a bare handle into an address.
     * @return array{ok:bool,url:string,error:string,warning:string}
     */
    public static function normalise(string $platform, string $input): array
    {
        $p = self::PLATFORMS[$platform] ?? null;
        $input = trim($input);
        $res = static fn(bool $ok, string $url = '', string $error = '', string $warning = ''): array => ['ok' => $ok, 'url' => $url, 'error' => $error, 'warning' => $warning];
        if ($p === null) {
            return $res(false, '', 'Unknown platform.');
        }
        if ($input === '') {
            return $res(true);
        }
        // a WhatsApp number may be typed with spaces, dashes or brackets ("+256 700 000 000"): it needs the country code and no leading 0 (wa.me takes digits only)
        if ($platform === 'whatsapp' && preg_match('/^\+?[\d\s().-]{7,30}$/', $input) === 1 && preg_match('/\d/', $input) === 1) {
            $digits = (string) preg_replace('/\D/', '', $input);
            if (str_starts_with($digits, '00')) {
                $digits = substr($digits, 2);                                // 00256... is the international prefix
            }
            if ($digits === '' || $digits[0] === '0') {
                return $res(false, '', $p['name'] . ': write the number with its country code and without a leading 0, for example 256700000000 (Uganda). A local number such as 0700 000 000 does not work on wa.me.');
            }
            if (strlen($digits) < 8 || strlen($digits) > 15) {
                return $res(false, '', $p['name'] . ': a phone number with its country code has 8 to 15 digits, for example 256700000000.');
            }
            return $res(true, 'https://wa.me/' . $digits);
        }
        if (strlen($input) > 300 || preg_match('/[\x00-\x20\x7f]/', $input) === 1) {
            return $res(false, '', $p['name'] . ': the address must be one line without spaces (at most 300 characters).');
        }
        if (preg_match('/["\'<>`\\\\]/', $input) === 1) {
            return $res(false, '', $p['name'] . ': the address contains a character that cannot be in a web address (a quote, < or >, a backslash or a backtick). Copy it again from the browser.');
        }
        if (preg_match('~^[a-z][a-z0-9+.-]*:~i', $input) !== 1) {
            $handle = ltrim($input, '@');
            if ($p['handle'] !== '' && preg_match('/^[A-Za-z0-9._]{2,60}$/', $handle) === 1) {
                return $res(true, sprintf($p['handle'], $handle));
            }
            if (preg_match('~^(www\.)?[a-z0-9-]+(\.[a-z0-9-]+)+(/\S*)?$~i', $input) === 1) {
                $input = 'https://' . $input;                              // "facebook.com/yourpage" -> https://
            } else {
                return $res(false, '', $p['name'] . ': write the full address, for example ' . $p['example'] . '.');
            }
        }
        $u = parse_url($input);
        if ($u === false || strtolower((string) ($u['scheme'] ?? '')) !== 'https') {
            return $res(false, '', $p['name'] . ': the address must start with https://');
        }
        if (isset($u['user']) || isset($u['pass'])) {
            return $res(false, '', $p['name'] . ': an address with a user name or password is not allowed.');
        }
        $host = strtolower((string) ($u['host'] ?? ''));
        if ($host === '' || preg_match('/^(?=.{4,253}$)([a-z0-9-]+\.)+[a-z]{2,}$/', $host) !== 1) {
            return $res(false, '', $p['name'] . ': that is not a valid web address.');
        }
        $warning = '';
        $match = false;
        foreach ($p['hosts'] as $h) {
            if ($host === $h || str_ends_with($host, '.' . $h)) {
                $match = true;
            }
        }
        if (!$match) {
            $warning = $p['name'] . ': this address is not on ' . $p['hosts'][0] . '. It was accepted: check that it is right.';
        }
        return $res(true, 'https://' . $host . (isset($u['port']) && (int) $u['port'] !== 443 ? ':' . (int) $u['port'] : '') . ($u['path'] ?? '') . (isset($u['query']) ? '?' . $u['query'] : ''), '', $warning);
    }

    /** @return array<string,string> platform => stored address ('' when none) */
    public static function values(): array
    {
        $out = [];
        foreach (self::PLATFORMS as $key => $p) {
            $out[$key] = trim((string) Settings::get($p['setting'], ''));
        }
        return $out;
    }

    /** Platform keys in the admin's order (unknown names dropped, missing ones appended). @return list<string> */
    public static function order(): array
    {
        $saved = array_values(array_filter(array_map('trim', explode(',', (string) Settings::get('social_order', ''))), static fn(string $k): bool => isset(self::PLATFORMS[$k])));
        $saved = array_values(array_unique($saved));
        foreach (array_keys(self::PLATFORMS) as $k) {
            if (!in_array($k, $saved, true)) {
                $saved[] = $k;
            }
        }
        return $saved;
    }

    /** May the links show in this place? */
    public static function showIn(string $context): bool
    {
        $key = self::TOGGLES[$context] ?? null;
        if ($key === null) {
            return true;
        }
        $v = Settings::get($key, $key === 'maintenance_show_social' ? '1' : (SocialConfig::defs()[$key][0] ?? '1'));
        return (string) $v !== '0' && (string) $v !== '';
    }

    /**
     * The links to print, in order. $context: all (every address, ignoring the toggles: JSON-LD sameAs) | footer | contact | newsletter | maintenance.
     * Only https addresses reach a page, whatever is stored.
     * @return list<array{key:string,label:string,url:string,icon:string}>
     */
    public static function links(string $context = 'all'): array
    {
        if ($context !== 'all' && !self::showIn($context)) {
            return [];
        }
        $vals = self::values();
        $out = [];
        foreach (self::order() as $key) {
            $url = $vals[$key] ?? '';
            if ($url === '' || preg_match('~^https?://~i', $url) !== 1) {
                continue;
            }
            $p = self::PLATFORMS[$key];
            $out[] = ['key' => $key, 'label' => $p['label'], 'url' => $url, 'icon' => $p['icon']];
        }
        return $out;
    }

    /**
     * Save the profile form. $input: urls (platform => text), order (list of platform keys), show_footer / show_contact / show_newsletter / show_maintenance (booleans).
     * Nothing is saved when any address is invalid.
     *
     * @param array<string,mixed> $input
     * @return array{ok:bool,errors:array<string,string>,warnings:array<string,string>,urls:array<string,string>}
     */
    public static function save(array $input, ?int $userId = null): array
    {
        $errors = [];
        $warnings = [];
        $urls = [];
        $in = is_array($input['urls'] ?? null) ? $input['urls'] : [];
        foreach (self::PLATFORMS as $key => $p) {
            if (!array_key_exists($key, $in)) {
                continue;
            }
            $r = self::normalise($key, is_scalar($in[$key]) ? (string) $in[$key] : '');
            if (!$r['ok']) {
                $errors[$key] = $r['error'];
                continue;
            }
            $urls[$key] = $r['url'];
            if ($r['warning'] !== '') {
                $warnings[$key] = $r['warning'];
            }
        }
        if ($errors !== []) {
            return ['ok' => false, 'errors' => $errors, 'warnings' => $warnings, 'urls' => $urls];
        }
        $set = [];
        foreach ($urls as $key => $url) {
            $set[self::PLATFORMS[$key]['setting']] = $url;
        }
        if (isset($input['order'])) {
            $list = is_array($input['order']) ? $input['order'] : explode(',', (string) $input['order']);
            $clean = [];
            foreach ($list as $k) {
                if (is_string($k) && isset(self::PLATFORMS[trim($k)]) && !in_array(trim($k), $clean, true)) {
                    $clean[] = trim($k);
                }
            }
            $set['social_order'] = implode(',', $clean);
        }
        foreach (['show_footer' => 'social_show_footer', 'show_contact' => 'social_show_contact', 'show_newsletter' => 'social_show_newsletter', 'show_maintenance' => 'maintenance_show_social'] as $field => $key) {
            if (array_key_exists($field, $input)) {
                $set[$key] = in_array($input[$field], [true, 1, '1', 'on', 'true', 'yes'], true) ? '1' : '0';
            }
        }
        if ($set !== []) {
            Settings::setMany($set);
            AuditLog::record('social.profiles.update', 'social_profiles', null, 'Changed the social media profile links', ['fields' => array_keys($set)]);
        }
        return ['ok' => true, 'errors' => [], 'warnings' => $warnings, 'urls' => $urls];
    }
}
