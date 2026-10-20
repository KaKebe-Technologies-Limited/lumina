<?php
declare(strict_types=1);

namespace App\Services\Social;

use App\Core\AuditLog;
use App\Core\Database;
use App\Core\Env;
use App\Core\Log;
use App\Core\Settings;
use App\Services\Shop\SecretStore;

/**
 * Everything the social-media module reads from settings, the encrypted secrets and the .env file (docs/SOCIAL.md section 3).
 *
 * SETTINGS (table `settings`, group "Social media"; defs() is the single source of truth: database/seed.sql seeds exactly these keys and a selftest compares them;
 * they are deliberately NOT in config/settings_schema.php except the profile URLs, so the generic Site settings screen never saves them by accident):
 *   social_enabled (global switch)  social_hashtags  social_utm  social_graph_version  social_li_version
 *   social_ch_<channel>_enabled  social_ch_<channel>_default (share by default when an issue goes live)  social_ch_<channel>_<field> (non-secret channel fields)
 *   social_tpl_<channel|tiktok>  (empty = built-in template)
 *   social_order  social_show_footer|contact|newsletter   (the maintenance page keeps its old switch maintenance_show_social)
 *   social_card_*  (share image designer)
 *   runtime (written by the app, never seeded): social_ch_<channel>_status (JSON), social_ch_<channel>_notified, social_ch_<channel>_expiry_notified
 * SECRETS: settings key social_secret_<channel>_<field> = "enc:v1:..." (SecretStore: AES-256-GCM keyed from APP_KEY). The .env override of each secret (names in
 * the channel's field spec, e.g. SOCIAL_FB_PAGE_TOKEN) wins and the admin screen then says "Set in the .env file". A secret is NEVER returned by any method
 * except secret() / channelConfig(), which only adapters call; publicView() / secretStatus() / describe() tell only "set / not set / source".
 */
final class SocialConfig
{
    public const SECRET_PREFIX = 'social_secret_';
    /** the share-image designer keys (read by the images agent through SocialConfig::str / bool) */
    public const CARD_DEFAULTS = [
        'social_card_bg'        => ['#2C3950', 'str', 'Share image: background colour'],
        'social_card_bg_dark'   => ['#172036', 'str', 'Share image: dark gradient colour'],
        'social_card_accent'    => ['#D6A151', 'str', 'Share image: accent colour (gold)'],
        'social_card_accent2'   => ['#CC651A', 'str', 'Share image: second accent colour (orange)'],
        'social_card_text'      => ['#FFFFFF', 'str', 'Share image: text colour'],
        'social_card_tagline'   => ['', 'str', 'Share image: tagline (empty = the site tagline)'],
        'social_card_show_logo' => ['1', 'bool', 'Share image: show the logo'],
        'social_card_show_url'  => ['1', 'bool', 'Share image: show the website address'],
    ];
    public const STATES = ['untested', 'ok', 'error', 'needs_token'];

    /** @var ?array<string,array{0:string,1:string,2:string}> */
    private static ?array $defs = null;
    /** @var ?list<string> */
    private static ?array $secretCache = null;
    /** @var ?array<string,mixed> */
    private static ?array $attentionMemo = null;

    // ------------------------------------------------------------------------------------------------ definitions

    /**
     * key => [default, type (bool|str|text), label] of every setting the module owns (seeded).
     * @return array<string,array{0:string,1:string,2:string}>
     */
    public static function defs(): array
    {
        if (self::$defs !== null) {
            return self::$defs;
        }
        $d = [
            'social_enabled'        => ['1', 'bool', 'Auto-share is on (global switch)'],
            'social_hashtags'       => ['#LuminaAfricaFoundation #Uganda #CommunityDevelopment', 'str', 'Default hashtags'],
            'social_utm'            => ['1', 'bool', 'Add UTM tags to the shared link'],
            'social_graph_version'  => ['v23.0', 'str', 'Meta Graph API version'],
            'social_li_version'     => ['202510', 'str', 'LinkedIn API version (YYYYMM)'],
            'social_order'          => ['facebook,instagram,linkedin,x,tiktok,youtube,whatsapp,telegram,threads', 'str', 'Order of the profile links'],
            'social_show_footer'    => ['1', 'bool', 'Show the profile links in the website footer'],
            'social_show_contact'   => ['1', 'bool', 'Show the profile links on the Contact page'],
            'social_show_newsletter' => ['1', 'bool', 'Show the profile links in newsletter e-mails'],
        ];
        foreach (Channels::ids() as $id) {
            $meta = Channels::get($id)->meta();
            $d['social_ch_' . $id . '_enabled'] = ['0', 'bool', $meta['label'] . ': switched on'];
            $d['social_ch_' . $id . '_default'] = ['1', 'bool', $meta['label'] . ': share new issues here by default'];
            foreach ($meta['fields'] as $f) {
                if ($f['secret']) {
                    continue;
                }
                $d['social_ch_' . $id . '_' . $f['key']] = [(string) $f['default'], 'str', $meta['label'] . ': ' . $f['label']];
            }
        }
        foreach (array_merge(Channels::ids(), Channels::KIT_ONLY) as $id) {
            $d['social_tpl_' . $id] = ['', 'text', Channels::label($id) . ': post template (empty = built-in)'];
        }
        self::$defs = $d + self::CARD_DEFAULTS;
        return self::$defs;
    }

    /** Forget cached definitions and secrets (after a settings change in the same request, and in tests). */
    public static function flush(): void
    {
        self::$secretCache = null;
        self::$attentionMemo = null;
        Settings::flush();
    }

    public static function str(string $key): string
    {
        $def = self::defs()[$key] ?? null;
        $v = Settings::get($key, $def[0] ?? '');
        return is_string($v) ? $v : (string) $v;
    }

    public static function bool(string $key): bool
    {
        return in_array(strtolower(trim(self::str($key))), ['1', 'true', 'yes', 'on'], true);
    }

    public static function globalOn(): bool
    {
        return self::bool('social_enabled');
    }

    // ------------------------------------------------------------------------------------------------ channel settings

    public static function key(string $channel, string $field): string
    {
        return 'social_ch_' . $channel . '_' . $field;
    }

    public static function enabled(string $channel): bool
    {
        return self::bool(self::key($channel, 'enabled'));
    }

    /** Share new issues on this channel by default (the share panel starts with it ticked). */
    public static function defaultOn(string $channel): bool
    {
        return self::bool(self::key($channel, 'default'));
    }

    /** The template in force for a channel: the admin's own, else the built-in one. */
    public static function template(string $channel): string
    {
        $own = trim(str_replace("\r\n", "\n", self::str('social_tpl_' . $channel)));
        return $own !== '' ? $own : CaptionBuilder::defaultTemplate($channel);
    }

    /** @return ?array<string,mixed> */
    private static function fieldSpec(string $channel, string $field): ?array
    {
        foreach (Channels::get($channel)->meta()['fields'] as $f) {
            if ($f['key'] === $field) {
                return $f;
            }
        }
        return null;
    }

    // ------------------------------------------------------------------------------------------------ secrets

    private static function secretKey(string $channel, string $field): string
    {
        if (preg_match('/^[a-z0-9_]+$/', $channel . $field) !== 1) {
            throw new \InvalidArgumentException('Bad secret name.');
        }
        return self::SECRET_PREFIX . $channel . '_' . $field;
    }

    /** The settings key under which a secret is stored encrypted (for tests and diagnostics; never the value). */
    public static function secretSettingKey(string $channel, string $field): string
    {
        return self::secretKey($channel, $field);
    }

    /** Name of the .env variable that overrides a secret ('' = none). */
    public static function envName(string $channel, string $field): string
    {
        return (string) (self::fieldSpec($channel, $field)['env'] ?? '');
    }

    /** The plaintext of a secret: .env first, then the decrypted database value; null when unset or unreadable. ADAPTERS ONLY. */
    public static function secret(string $channel, string $field): ?string
    {
        $env = self::envName($channel, $field);
        if ($env !== '') {
            $v = trim((string) Env::get($env, ''));
            if ($v !== '') {
                return $v;
            }
        }
        $stored = trim((string) Settings::get(self::secretKey($channel, $field), ''));
        if ($stored === '') {
            return self::legacyPlain($channel, $field);
        }
        $plain = SecretStore::decrypt($stored);
        return $plain !== null && $plain !== '' ? $plain : null;
    }

    /**
     * A field that used to be a plain setting and became a secret (the webhook address: Zapier / Make / IFTTT hook addresses ARE the credential) keeps working from the
     * old plain value until it is encrypted (migrateLegacySecrets(), run when the channels screen opens or a channel is saved). Spec flag: `legacy` => true.
     */
    private static function legacyPlain(string $channel, string $field): ?string
    {
        if (empty(self::fieldSpec($channel, $field)['legacy'])) {
            return null;
        }
        $v = trim((string) Settings::get(self::key($channel, $field), ''));
        return $v !== '' ? $v : null;
    }

    private static function dropLegacy(string $channel, string $field): void
    {
        if (!empty(self::fieldSpec($channel, $field)['legacy']) && trim((string) Settings::get(self::key($channel, $field), '')) !== '') {
            Settings::set(self::key($channel, $field), '');
        }
    }

    /**
     * Encrypt the values of fields that were plain settings before they became secrets (so the webhook address leaves the plain settings table and every backup of it).
     * Idempotent; does nothing when the server cannot encrypt. @return int values moved
     */
    public static function migrateLegacySecrets(): int
    {
        $n = 0;
        try {
            if (!SecretStore::available()) {
                return 0;
            }
            foreach (Channels::ids() as $id) {
                foreach (Channels::get($id)->meta()['fields'] as $f) {
                    if ($f['secret'] && !empty($f['legacy'])) {
                        $k = (string) $f['key'];
                        $stored = trim((string) Settings::get(self::secretKey($id, $k), ''));
                        $plain = self::legacyPlain($id, $k);
                        if ($stored === '' && $plain !== null && self::putSecret($id, $k, $plain)) {
                            $n++;
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            self::note('legacy secret migration failed (' . (new \ReflectionClass($e))->getShortName() . ')');
        }
        return $n;
    }

    /** @return array{set:bool,source:string,readable:bool,env:string,legacy?:bool} source: env | database | none (legacy: still the old plain setting, not encrypted yet) */
    public static function secretStatus(string $channel, string $field): array
    {
        $env = self::envName($channel, $field);
        if ($env !== '' && trim((string) Env::get($env, '')) !== '') {
            return ['set' => true, 'source' => 'env', 'readable' => true, 'env' => $env];
        }
        $stored = trim((string) Settings::get(self::secretKey($channel, $field), ''));
        if ($stored === '') {
            if (self::legacyPlain($channel, $field) !== null) {
                return ['set' => true, 'source' => 'database', 'readable' => true, 'env' => $env, 'legacy' => true];
            }
            return ['set' => false, 'source' => 'none', 'readable' => true, 'env' => $env];
        }
        return ['set' => true, 'source' => 'database', 'readable' => SecretStore::decrypt($stored) !== null, 'env' => $env];
    }

    /** "Set in the .env file", "Set (hidden)", "Not set", "Stored but cannot be read ...". */
    public static function describeSecret(string $channel, string $field): string
    {
        $s = self::secretStatus($channel, $field);
        return match (true) {
            $s['source'] === 'env' => 'Set in the .env file (' . $s['env'] . ')',
            !$s['set']             => 'Not set',
            !$s['readable']        => 'Stored but cannot be read (the application key changed): enter it again',
            default                => 'Set (hidden)',
        };
    }

    public static function putSecret(string $channel, string $field, string $plain): bool
    {
        $key = self::secretKey($channel, $field);
        $plain = trim($plain);
        if ($plain === '') {
            self::forgetSecret($channel, $field);
            return true;
        }
        $enc = SecretStore::encrypt($plain);
        if ($enc === null) {
            return false;
        }
        Settings::set($key, $enc);
        self::dropLegacy($channel, $field);
        self::$secretCache = null;
        return true;
    }

    public static function forgetSecret(string $channel, string $field): void
    {
        Database::execute('DELETE FROM settings WHERE setting_key = ?', [self::secretKey($channel, $field)]);
        self::dropLegacy($channel, $field);
        Settings::flush();
        self::$secretCache = null;
    }

    /**
     * A secret that is stored but cannot be decrypted (the APP_KEY changed, or the value was tampered with) and that matters for this channel (its `when` condition holds).
     * Required or optional makes no difference: an optional signing secret that silently drops out would make the channel send UNSIGNED messages.
     * @return list<string> field labels
     */
    public static function unreadableSecrets(string $channel): array
    {
        $meta = Channels::get($channel)->meta();
        $cfg = self::channelConfig($channel);
        $bad = [];
        foreach ($meta['fields'] as $f) {
            if (!$f['secret']) {
                continue;
            }
            if (is_array($f['when']) && $f['when'] !== []) {
                foreach ($f['when'] as $wk => $wv) {
                    if (($cfg[$wk] ?? '') !== $wv) {
                        continue 2;
                    }
                }
            }
            $st = self::secretStatus($channel, (string) $f['key']);
            if ($st['set'] && !$st['readable']) {
                $bad[] = (string) $f['label'];
            }
        }
        return $bad;
    }

    /**
     * Channels that borrow a secret of another channel when their own box is empty (Instagram uses the Facebook Page token): channel => secret field => [dependent => its own override field].
     */
    private const INHERITED = ['facebook' => ['page_token' => ['instagram' => 'token']]];

    /**
     * The channels that currently use $field of $channel because their own override is empty (replacing it changes what they send).
     * @return list<string>
     */
    public static function dependents(string $channel, string $field): array
    {
        $out = [];
        foreach (self::INHERITED[$channel][$field] ?? [] as $dep => $ownField) {
            if (Channels::has($dep) && !self::secretStatus($dep, $ownField)['set']) {
                $out[] = $dep;
            }
        }
        return $out;
    }

    /** @return list<string> every secret currently configured in plaintext (for SocialHttp::redact; cached per request) */
    public static function secretValues(): array
    {
        if (self::$secretCache !== null) {
            return self::$secretCache;
        }
        $out = [];
        foreach (Channels::ids() as $id) {
            foreach (Channels::get($id)->meta()['fields'] as $f) {
                if ($f['secret']) {
                    $v = self::secret($id, (string) $f['key']);
                    if ($v !== null && strlen($v) >= 6) {
                        $out[] = $v;
                    }
                }
            }
        }
        return self::$secretCache = $out;
    }

    // ------------------------------------------------------------------------------------------------ channel config

    /**
     * Every field of a channel as the adapter needs it: non-secret values from settings, secrets decrypted (env first), '' for anything missing, then the adapter's own
     * normaliseConfig(). FOR ADAPTERS ONLY: never log, return or store the result.
     * @return array<string,string>
     */
    public static function channelConfig(string $channel): array
    {
        $ch = Channels::get($channel);
        $cfg = [];
        foreach ($ch->meta()['fields'] as $f) {
            $k = (string) $f['key'];
            if ($f['secret']) {
                $cfg[$k] = (string) (self::secret($channel, $k) ?? '');
            } else {
                $v = self::str(self::key($channel, $k));
                $cfg[$k] = $v !== '' ? $v : (string) $f['default'];
            }
        }
        return $ch instanceof AbstractChannel ? $ch->normaliseConfig($cfg) : $cfg;
    }

    /**
     * What the admin screens may see of a channel: non-secret values, and for secrets only whether they are set and where. NEVER a secret.
     * @return array{id:string,enabled:bool,default_on:bool,fields:list<array<string,mixed>>,status:array<string,mixed>,ready:array<string,mixed>}
     */
    public static function publicView(string $channel): array
    {
        $meta = Channels::get($channel)->meta();
        $fields = [];
        foreach ($meta['fields'] as $f) {
            $row = ['key' => $f['key'], 'label' => $f['label'], 'type' => $f['type'], 'required' => $f['required'], 'when' => $f['when'], 'help' => $f['help'], 'placeholder' => $f['placeholder'], 'options' => $f['options'], 'maxlength' => $f['maxlength'], 'env' => $f['env']];
            if ($f['secret']) {
                $row['secret'] = self::secretStatus($channel, (string) $f['key']) + ['text' => self::describeSecret($channel, (string) $f['key'])];
                $row['value'] = '';
            } else {
                $v = self::str(self::key($channel, (string) $f['key']));
                $row['value'] = $v !== '' ? $v : (string) $f['default'];
            }
            $fields[] = $row;
        }
        return ['id' => $channel, 'enabled' => self::enabled($channel), 'default_on' => self::defaultOn($channel), 'fields' => $fields, 'status' => self::status($channel), 'ready' => self::ready($channel)];
    }

    /**
     * Save what an admin typed for one channel. Non-secret fields: the posted value (a missing key is left alone). Secret fields: an empty value KEEPS the stored one, a
     * value replaces it, `clear_<key>` = 1 removes it. `enabled` / `default_on` are booleans. Switching a channel ON requires a complete setup.
     *
     * @param array<string,mixed> $input
     * @return array{ok:bool,errors:array<string,string>,changed:list<string>,reset:list<string>}  changed = field NAMES only (for the audit trail); reset = channels (this one and the
     *         ones that borrow its secret, see dependents()) that were "needs a new token" and are now a fresh start: their posts that failed because of the old credentials may be queued again
     */
    public static function saveChannel(string $channel, array $input, ?int $userId = null): array
    {
        $ch = Channels::get($channel);
        $meta = $ch->meta();
        $cand = [];
        $changed = [];
        $secretsIn = [];
        $clear = [];
        foreach ($meta['fields'] as $f) {
            $k = (string) $f['key'];
            if ($f['secret']) {
                if (!empty($input['clear_' . $k])) {
                    $clear[] = $k;
                }
                $v = $input[$k] ?? '';
                if (is_string($v) && trim($v) !== '') {
                    $secretsIn[$k] = trim($v);
                    $cand[$k] = trim($v);
                }
                continue;
            }
            if (!array_key_exists($k, $input)) {
                continue;
            }
            $v = $input[$k];
            if (is_array($v)) {
                $v = implode(',', array_filter(array_map(static fn($x): string => is_scalar($x) ? trim((string) $x) : '', $v)));
            }
            $cand[$k] = is_scalar($v) ? trim((string) $v) : '';
        }
        $errors = $ch->validate($cand);
        if ($errors !== []) {
            return ['ok' => false, 'errors' => $errors, 'changed' => [], 'reset' => []];
        }
        if ($secretsIn !== [] && !SecretStore::available()) {
            return ['ok' => false, 'errors' => ['_' => 'Secrets cannot be stored safely on this server (the OpenSSL extension is missing). Put them in the .env file instead.'], 'changed' => [], 'reset' => []];
        }
        $values = [];
        foreach ($meta['fields'] as $f) {
            $k = (string) $f['key'];
            if (!$f['secret'] && isset($cand[$k]) && $cand[$k] !== self::str(self::key($channel, $k))) {
                $values[self::key($channel, $k)] = $cand[$k];
                $changed[] = $k;
            }
        }
        $wasNeedsToken = self::status($channel)['state'] === 'needs_token';
        $borrowers = [];                                                       // channels that use a secret of this one (Instagram <- the Facebook Page token)
        foreach (array_keys($secretsIn) as $k) {
            foreach (self::dependents($channel, $k) as $dep) {
                $borrowers[$dep] = self::status($dep)['state'] === 'needs_token';
            }
        }
        $wasEnabled = self::enabled($channel);
        $wantEnabled = array_key_exists('enabled', $input) ? self::truthy($input['enabled']) : $wasEnabled;
        if (array_key_exists('default_on', $input)) {
            $d = self::truthy($input['default_on']);
            if ($d !== self::defaultOn($channel)) {
                $values[self::key($channel, 'default')] = $d ? '1' : '0';
                $changed[] = 'default_on';
            }
        }
        foreach ($secretsIn as $k => $v) {
            self::putSecret($channel, $k, $v);
            $changed[] = $k;
        }
        foreach ($clear as $k) {
            if (!isset($secretsIn[$k]) && self::secretStatus($channel, $k)['source'] === 'database') {
                self::forgetSecret($channel, $k);
                $changed[] = $k;
            }
        }
        if ($values !== []) {
            Settings::setMany($values);
        }
        self::flush();
        // a new token / address is a fresh start: forget "needs a new token" and let the next problem notify again
        $reset = [];
        if ($secretsIn !== [] || (isset($meta['token_expiry']) && isset($values[self::key($channel, (string) $meta['token_expiry'])]))) {
            self::setStatus($channel, 'untested', '');
            Settings::set(self::key($channel, 'notified'), '');
            Settings::set(self::key($channel, 'expiry_notified'), '');
            if ($wasNeedsToken) {
                $reset[] = $channel;
            }
            foreach ($borrowers as $dep => $needed) {                           // the channels that were using the replaced secret start over too
                self::setStatus($dep, 'untested', '');
                Settings::set(self::key($dep, 'notified'), '');
                Settings::set(self::key($dep, 'expiry_notified'), '');
                if ($needed) {
                    $reset[] = $dep;
                }
            }
        }
        $errors = [];
        if ($wantEnabled && !$wasEnabled) {
            $why = self::missingFor($channel);
            if ($why !== []) {
                $errors['enabled'] = 'Finish the setup before switching this channel on. Missing: ' . implode(', ', $why) . '.';
                $wantEnabled = false;
            }
        }
        if ($wantEnabled !== $wasEnabled) {
            Settings::set(self::key($channel, 'enabled'), $wantEnabled ? '1' : '0');
            $changed[] = 'enabled';
        }
        self::flush();
        if ($changed !== []) {
            AuditLog::record('social.channel.update', 'social_channel', $channel, 'Changed social channel settings', ['channel' => $channel, 'fields' => array_values(array_unique($changed))]);
        }
        return ['ok' => $errors === [], 'errors' => $errors, 'changed' => array_values(array_unique($changed)), 'reset' => $reset];
    }

    private static function truthy(mixed $v): bool
    {
        return in_array(is_string($v) ? strtolower(trim($v)) : $v, [true, 1, '1', 'true', 'on', 'yes'], true);
    }

    // ------------------------------------------------------------------------------------------------ readiness and status

    /** Labels of the required fields that have no value yet (respecting `when`). @return list<string> */
    public static function missingFor(string $channel): array
    {
        $meta = Channels::get($channel)->meta();
        $cfg = self::channelConfig($channel);
        $missing = [];
        $unreadable = self::unreadableSecrets($channel);
        foreach ($meta['fields'] as $f) {
            if (!$f['required']) {
                if ($f['secret'] && in_array((string) $f['label'], $unreadable, true)) {
                    $missing[] = (string) $f['label'] . ' (stored but unreadable)';          // never fall back to "not set": a signature / token that silently drops out is worse than a stopped channel
                }
                continue;
            }
            if (is_array($f['when']) && $f['when'] !== []) {
                $skip = false;
                foreach ($f['when'] as $wk => $wv) {
                    if (($cfg[$wk] ?? '') !== $wv) {
                        $skip = true;
                    }
                }
                if ($skip) {
                    continue;
                }
            }
            if (trim((string) ($cfg[(string) $f['key']] ?? '')) === '') {
                $st = $f['secret'] ? self::secretStatus($channel, (string) $f['key']) : null;
                $missing[] = (string) $f['label'] . ($st !== null && $st['set'] && !$st['readable'] ? ' (stored but unreadable)' : '');
            }
        }
        return $missing;
    }

    /**
     * Can this channel post right now?  state: off (global switch or channel switch) | incomplete | needs_token | expired | ready
     * @return array{ready:bool,state:string,reasons:list<string>}
     */
    public static function ready(string $channel): array
    {
        $reasons = [];
        $state = 'ready';
        if (!self::globalOn()) {
            return ['ready' => false, 'state' => 'off', 'reasons' => ['Auto-share is switched off for the whole site (Admin > Social media > Channels).']];
        }
        if (!self::enabled($channel)) {
            return ['ready' => false, 'state' => 'off', 'reasons' => ['This channel is switched off.']];
        }
        $unreadable = self::unreadableSecrets($channel);
        if ($unreadable !== []) {
            return ['ready' => false, 'state' => 'needs_token', 'reasons' => [implode(', ', $unreadable) . ' is stored but cannot be read (the application key changed): enter it again in Admin > Social media > Channels.']];
        }
        $missing = self::missingFor($channel);
        if ($missing !== []) {
            return ['ready' => false, 'state' => 'incomplete', 'reasons' => ['Not finished: ' . implode(', ', $missing) . '.']];
        }
        $st = self::status($channel);
        if ($st['state'] === 'needs_token') {
            return ['ready' => false, 'state' => 'needs_token', 'reasons' => ['The platform rejected the access token. Paste a new one in Admin > Social media > Channels.']];
        }
        $exp = self::expiry($channel);
        if ($exp !== null && $exp['days_left'] < 0) {
            return ['ready' => false, 'state' => 'expired', 'reasons' => ['The access token expired on ' . $exp['date'] . '. Paste a new one in Admin > Social media > Channels.']];
        }
        return ['ready' => true, 'state' => $state, 'reasons' => $reasons];
    }

    /** Channels that may be offered and posted to now, in display order. @return list<string> */
    public static function availableIds(): array
    {
        $out = [];
        foreach (Channels::ids() as $id) {
            if (self::ready($id)['ready']) {
                $out[] = $id;
            }
        }
        return $out;
    }

    /** @return array{state:string,message:string,at:string} last known health of the channel (test connection or a post) */
    public static function status(string $channel): array
    {
        $raw = (string) Settings::get(self::key($channel, 'status'), '');
        $j = $raw !== '' ? json_decode($raw, true) : null;
        if (!is_array($j) || !in_array($j['state'] ?? '', self::STATES, true)) {
            return ['state' => 'untested', 'message' => '', 'at' => ''];
        }
        return ['state' => (string) $j['state'], 'message' => mb_substr((string) ($j['message'] ?? ''), 0, 240), 'at' => (string) ($j['at'] ?? '')];
    }

    /** Record the health of a channel. The message is redacted again here (a token can never be stored by accident). */
    public static function setStatus(string $channel, string $state, string $message): void
    {
        if (!in_array($state, self::STATES, true)) {
            return;
        }
        $json = $state === 'untested' && $message === '' ? '' : (string) json_encode(['state' => $state, 'message' => mb_substr(SocialHttp::redact($message), 0, 240), 'at' => date('Y-m-d H:i:s')], JSON_UNESCAPED_UNICODE);
        Settings::set(self::key($channel, 'status'), $json);
    }

    /**
     * The access-token expiry date of a channel (LinkedIn), if the admin entered one.
     * @return ?array{date:string,days_left:int}
     */
    public static function expiry(string $channel): ?array
    {
        $field = Channels::get($channel)->meta()['token_expiry'] ?? null;
        if (!is_string($field)) {
            return null;
        }
        $date = self::str(self::key($channel, $field));
        $d = $date !== '' ? \DateTimeImmutable::createFromFormat('!Y-m-d', $date) : false;
        if ($d === false || $d->format('Y-m-d') !== $date) {
            return null;
        }
        $today = new \DateTimeImmutable('today');
        return ['date' => $date, 'days_left' => (int) $today->diff($d)->format('%r%a')];
    }

    /**
     * Channels whose token runs out within 14 days (or already did).
     * @return list<array{channel:string,label:string,date:string,days_left:int}>
     */
    public static function expiryWarnings(int $withinDays = 14): array
    {
        $out = [];
        foreach (Channels::ids() as $id) {
            if (!self::enabled($id)) {
                continue;
            }
            $e = self::expiry($id);
            if ($e !== null && $e['days_left'] <= $withinDays) {
                $out[] = ['channel' => $id, 'label' => Channels::label($id), 'date' => $e['date'], 'days_left' => $e['days_left']];
            }
        }
        return $out;
    }

    /**
     * What needs a person: posts that failed or need review, channels that need a new token, tokens about to expire. One cheap query; never throws.
     * @return array{failed:int,review:int,needs_token:list<string>,expiring:list<array<string,mixed>>,total:int}
     */
    public static function attention(bool $fresh = false): array
    {
        if (self::$attentionMemo !== null && !$fresh) {
            return self::$attentionMemo;
        }
        $out = ['failed' => 0, 'review' => 0, 'needs_token' => [], 'expiring' => [], 'total' => 0];
        try {
            foreach (Database::all("SELECT status, COUNT(*) AS c FROM social_posts WHERE status IN ('failed','review') AND created_at >= ? GROUP BY status", [date('Y-m-d H:i:s', time() - 30 * 86400)]) as $r) {
                $out[(string) $r['status']] = (int) $r['c'];
            }
            foreach (Channels::ids() as $id) {
                if (self::enabled($id) && (self::status($id)['state'] === 'needs_token' || self::unreadableSecrets($id) !== [])) {
                    $out['needs_token'][] = $id;
                }
            }
            $out['expiring'] = self::expiryWarnings();
        } catch (\Throwable) {
            // tables not imported yet
        }
        $out['total'] = $out['failed'] + $out['review'] + count($out['needs_token']) + count($out['expiring']);
        return self::$attentionMemo = $out;
    }

    // ------------------------------------------------------------------------------------------------ global options and templates

    /**
     * Save the global options: `enabled` (the global switch), `utm`, `hashtags`, `graph_version`, `li_version`. Only the keys present change; nothing is saved when one is invalid.
     * @param array<string,mixed> $input
     * @return array{ok:bool,errors:array<string,string>}
     */
    public static function saveGlobal(array $input, ?int $userId = null): array
    {
        $set = [];
        $errors = [];
        foreach (['enabled' => 'social_enabled', 'utm' => 'social_utm'] as $field => $key) {
            if (array_key_exists($field, $input)) {
                $set[$key] = self::truthy($input[$field]) ? '1' : '0';
            }
        }
        if (array_key_exists('hashtags', $input)) {
            $raw = is_scalar($input['hashtags']) ? (string) $input['hashtags'] : '';
            if (mb_strlen($raw) > 600) {
                $errors['hashtags'] = 'The hashtag list is too long (600 characters at most).';
            } else {
                $set['social_hashtags'] = CaptionBuilder::hashtags($raw, 30);
            }
        }
        if (array_key_exists('graph_version', $input)) {
            $v = trim((string) $input['graph_version']);
            if (preg_match('/^v\d{1,2}\.\d{1,2}$/', $v) !== 1) {
                $errors['graph_version'] = 'The Graph API version looks like v23.0.';
            } else {
                $set['social_graph_version'] = $v;
            }
        }
        if (array_key_exists('li_version', $input)) {
            $v = trim((string) $input['li_version']);
            if (preg_match('/^20\d{2}(0[1-9]|1[0-2])$/', $v) !== 1) {
                $errors['li_version'] = 'The LinkedIn API version is a year and month like 202510.';
            } else {
                $set['social_li_version'] = $v;
            }
        }
        if ($errors !== []) {
            return ['ok' => false, 'errors' => $errors];
        }
        if ($set !== []) {
            Settings::setMany($set);
            self::flush();
            AuditLog::record('social.settings.update', 'social_settings', null, 'Changed the social media options', ['fields' => array_keys($set)]);
        }
        return ['ok' => true, 'errors' => []];
    }

    /**
     * Save the share-picture design (colours, tagline, logo / address switches) read by SocialCard::designer(). `reset` puts every value back to the brand default.
     * Colours are #RRGGBB (a leading # is added when missing), the tagline is plain text up to 80 characters. Nothing is saved when one value is wrong.
     * Pictures already stored are redrawn lazily: SocialCard::sourceHash() contains the design, so a changed design re-renders them on the next request (token and address stay).
     *
     * @param array<string,mixed> $input bg, bg_dark, accent, accent2, text, tagline, show_logo, show_url, reset
     * @return array{ok:bool,errors:array<string,string>}
     */
    public static function saveCard(array $input, ?int $userId = null): array
    {
        $set = [];
        $errors = [];
        if (self::truthy($input['reset'] ?? false)) {
            foreach (self::CARD_DEFAULTS as $key => $def) {
                $set[$key] = $def[0];
            }
        } else {
            $labels = ['bg' => 'Background colour', 'bg_dark' => 'Dark gradient colour', 'accent' => 'Accent colour', 'accent2' => 'Second accent colour', 'text' => 'Text colour'];
            foreach ($labels as $field => $label) {
                if (!array_key_exists($field, $input)) {
                    continue;
                }
                $v = strtoupper(trim(is_scalar($input[$field]) ? (string) $input[$field] : ''));
                if ($v !== '' && $v[0] !== '#') {
                    $v = '#' . $v;
                }
                if (preg_match('/^#[0-9A-F]{6}$/', $v) !== 1) {
                    $errors[$field] = $label . ' must be a colour like #2C3950.';
                    continue;
                }
                $set['social_card_' . $field] = $v;
            }
            if (array_key_exists('tagline', $input)) {
                $t = CaptionBuilder::sanitize(is_scalar($input['tagline']) ? (string) $input['tagline'] : '', false);
                if (mb_strlen($t) > 80) {
                    $errors['tagline'] = 'The tagline is too long (80 characters at most).';
                } else {
                    $set['social_card_tagline'] = $t;
                }
            }
            foreach (['show_logo', 'show_url'] as $field) {
                if (array_key_exists($field, $input)) {
                    $set['social_card_' . $field] = self::truthy($input[$field]) ? '1' : '0';
                }
            }
        }
        if ($errors !== []) {
            return ['ok' => false, 'errors' => $errors];
        }
        if ($set !== []) {
            Settings::setMany($set);
            self::flush();
            AuditLog::record('social.card.update', 'social_settings', null, 'Changed the share picture design', ['fields' => array_keys($set)]);
        }
        return ['ok' => true, 'errors' => []];
    }

    /**
     * Save the post templates and the per-channel "share by default" switches (and, when posted too, the global options of saveGlobal()).
     * `templates[<channel|tiktok>]` = text (empty = back to the built-in one), `default_on[<channel>]` = boolean. Every template is test-built with a sample issue: it must give a
     * non-empty caption, use only the known placeholders and fit its channel's limit after shortening. Nothing is saved when one is invalid.
     *
     * @param array<string,mixed> $input
     * @return array{ok:bool,errors:array<string,string>,warnings:array<string,string>,samples:array<string,array<string,mixed>>}
     */
    public static function saveTemplates(array $input, ?int $userId = null): array
    {
        $errors = [];
        $warnings = [];
        $samples = [];
        $set = [];
        $tpls = is_array($input['templates'] ?? null) ? $input['templates'] : [];
        foreach (array_merge(Channels::ids(), Channels::KIT_ONLY) as $ch) {
            if (!array_key_exists($ch, $tpls)) {
                continue;
            }
            $t = trim(str_replace(["\r\n", "\r"], "\n", is_scalar($tpls[$ch]) ? (string) $tpls[$ch] : ''));
            $label = Channels::label($ch);
            if ($t === '' || $t === CaptionBuilder::defaultTemplate($ch)) {
                $set[self::SETTING_TPL . $ch] = '';
                continue;
            }
            if (mb_strlen($t) > 2000 || preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $t) === 1) {
                $errors[$ch] = $label . ': the template is too long (2000 characters at most) or contains illegal characters.';
                continue;
            }
            $b = CaptionBuilder::build($ch, CaptionBuilder::sampleIssue(), $t);
            $samples[$ch] = $b;
            if ($b['unknown'] !== []) {
                $errors[$ch] = $label . ': unknown placeholder ' . implode(', ', $b['unknown']) . '. Use ' . implode(' ', array_map(static fn(string $p): string => '{' . $p . '}', CaptionBuilder::PLACEHOLDERS)) . '.';
                continue;
            }
            if ($b['text'] === '' || !str_contains($t, '{title}') && !str_contains($t, '{summary}')) {
                $errors[$ch] = $label . ': the template must contain {title} or {summary}.';
                continue;
            }
            $fixed = CaptionBuilder::fixedLength($ch, $t);
            if ($b['over'] || $fixed > $b['limit'] - 20) {
                $errors[$ch] = $label . ': the fixed words of this template already take ' . $fixed . ' of the ' . $b['limit'] . ' characters this platform allows, which leaves no room for the title. Make the fixed text shorter.';
                continue;
            }
            if ($ch === 'instagram' && str_contains($t, '{url}')) {
                $warnings[$ch] = 'Instagram does not make links in a caption clickable: "link in bio" works better than {url}.';
            }
            $set[self::SETTING_TPL . $ch] = $t;
        }
        $defaults = is_array($input['default_on'] ?? null) ? $input['default_on'] : [];
        foreach (Channels::ids() as $ch) {
            if (array_key_exists($ch, $defaults)) {
                $set[self::key($ch, 'default')] = self::truthy($defaults[$ch]) ? '1' : '0';
            }
        }
        if ($errors !== []) {
            return ['ok' => false, 'errors' => $errors, 'warnings' => $warnings, 'samples' => $samples];
        }
        $glob = array_intersect_key($input, array_flip(['utm', 'hashtags']));
        if ($glob !== []) {
            $g = self::saveGlobal($glob, $userId);
            if (!$g['ok']) {
                return ['ok' => false, 'errors' => $g['errors'], 'warnings' => $warnings, 'samples' => $samples];
            }
        }
        if ($set !== []) {
            Settings::setMany($set);
            self::flush();
            AuditLog::record('social.templates.update', 'social_settings', null, 'Changed the social media post templates', ['fields' => array_keys($set)]);
        }
        return ['ok' => true, 'errors' => [], 'warnings' => $warnings, 'samples' => $samples];
    }

    private const SETTING_TPL = 'social_tpl_';

    /** Drop a stored value so the default applies again (tests, "reset"). */
    public static function reset(string $key): void
    {
        Settings::reset($key);
        self::$secretCache = null;
    }

    /** Log a configuration problem without any value in it. */
    public static function note(string $message, array $context = []): void
    {
        Log::warning('Social: ' . $message, $context);
    }
}
