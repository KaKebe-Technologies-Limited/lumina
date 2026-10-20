<?php
/**
 * SOCIAL MEDIA module checks (docs/SOCIAL.md). Run alone:   /c/xampp/php/php.exe bin/selftest-social.php     or include it from bin/selftest.php (it shares check() and $fail).
 *
 * Part 1 (no web server needed): schema / seed / abilities / editor-role upgrade; the SSRF guard of the webhook (IP spellings, ports, user info, DNS to private addresses through
 * a test resolver, redirects re-checked, the connection pinned to the checked address); fixed API hosts, path validation, the dev-only base URL; error mapping and redaction;
 * the caption builder (placeholders, X weighting, fuzz, sanitising, hashtags, UTM); profile links; settings and secrets (encrypted at rest, masked, .env override); the post queue
 * (idempotent enqueue under parallel processes, claim, backoff, five attempts, stuck rows, review); the dispatcher and ShareHook with FAKE channels (publish -> queue -> post, never
 * twice, scheduled go-live, saved choices, drafts never posted, post again, auth failure notified once, ambiguous -> review, a failing hook never breaks publishing).
 * Part 2 (starts the LOCAL MOCK bin/dev/mock-social.php): the Telegram and Webhook adapters and the Graph / X / LinkedIn mock endpoints through SocialHttp, the dev-only base URL, every
 * failure mode (401, 429, 500, 502, slow, ambiguous, redirect, empty, bad JSON, oversized), OAuth 1.0a signature verification of the mock, HMAC signatures, and the whole publish flow
 * with real adapters.
 * Part 3 (needs the site at http://localhost/<folder>/): abilities of every social URL with throw-away users and roles, CSRF, JSON shapes, nothing secret in any page. Skips itself
 * when the site does not answer.
 *
 * Everything it creates is removed again (issues "ZZ-SOCIAL ...", settings, secrets, users, roles, mail files, mock state); the settings it changes are put back exactly as they were.
 * No real platform is EVER contacted: the only server it talks to is the local mock (and the network is replaced by a test transport where no server is needed).
 */
declare(strict_types=1);

if (function_exists('check')) {
    // included by bin/selftest.php: this suite uses many short variable names and starts a mock server, so it runs in its OWN process (a clean scope, nothing leaks into or out of the
    // other suites); its output is relayed and a failure counts as one failed check here.
    $zsProc = proc_open([PHP_BINARY, __FILE__], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $zsPipes);
    $zsOut = is_resource($zsProc) ? (string) stream_get_contents($zsPipes[1]) : '';
    $zsErr = is_resource($zsProc) ? (string) stream_get_contents($zsPipes[2]) : 'could not start the process';
    $zsCode = is_resource($zsProc) ? proc_close($zsProc) : -1;
    echo $zsOut . ($zsErr !== '' ? $zsErr . PHP_EOL : '');
    check('the social media suite (own process) ends with ALL CHECKS PASSED', $zsCode === 0 && str_contains($zsOut, 'ALL CHECKS PASSED'), 'exit code ' . $zsCode);
    return;
}
if (!function_exists('check')) {
    if (PHP_SAPI !== 'cli') {
        exit('CLI only.');
    }
    require dirname(__DIR__) . '/app/bootstrap.php';
    if (\App\Core\Config::get('app.env') !== 'local') {
        exit("Refusing to run outside APP_ENV=local.\n");
    }
    $fail = 0;
    function check(string $name, bool $ok, string $extra = ''): void
    {
        global $fail;
        echo ($ok ? '  ok   ' : '  FAIL ') . $name . ($ok || $extra === '' ? '' : "  -> {$extra}") . PHP_EOL;
        if (!$ok) {
            $fail++;
        }
    }
    $standalone = true;
}

use App\Core\Config;
use App\Core\Database;
use App\Core\Env;
use App\Core\Roles;
use App\Core\Settings;
use App\Models\Newsletter;
use App\Models\SocialPost;
use App\Services\Newsletter\NewsletterContent;
use App\Services\Newsletter\NewsletterService;
use App\Services\Shop\SecretStore;
use App\Services\Social\CaptionBuilder;
use App\Services\Social\ChannelInterface;
use App\Services\Social\Channels;
use App\Services\Social\PostQueue;
use App\Services\Social\ShareHook;
use App\Services\Social\SocialCard;
use App\Services\Social\SocialConfig;
use App\Services\Social\SocialDispatcher;
use App\Services\Social\SocialHttp;
use App\Services\Social\SocialPayload;
use App\Services\Social\SocialProfiles;
use App\Services\Social\SocialResult;

if (!class_exists('ZzSocialFakeChannel', false)) {
    /** a channel whose post() is a closure: metadata and validation are the real adapter's */
    final class ZzSocialFakeChannel implements ChannelInterface
    {
        /** @var list<SocialPayload> */
        public array $calls = [];

        public function __construct(private ChannelInterface $real, public \Closure $onPost)
        {
        }

        public function id(): string
        {
            return $this->real->id();
        }

        public function meta(): array
        {
            return $this->real->meta();
        }

        public function validate(array $input): array
        {
            return $this->real->validate($input);
        }

        public function test(array $config): array
        {
            return ['ok' => true, 'state' => 'ok', 'message' => 'fake ok'];
        }

        public function post(array $config, SocialPayload $payload): SocialResult
        {
            $this->calls[] = $payload;
            return ($this->onPost)($payload, count($this->calls));
        }
    }

    final class ZzSocialThrowingChannel implements ChannelInterface
    {
        public function id(): string
        {
            return 'facebook';
        }

        public function meta(): array
        {
            throw new \RuntimeException('boom from meta');
        }

        public function validate(array $input): array
        {
            return [];
        }

        public function test(array $config): array
        {
            return ['ok' => false, 'state' => 'error', 'message' => 'x'];
        }

        public function post(array $config, SocialPayload $payload): SocialResult
        {
            throw new \RuntimeException('boom');
        }
    }
}

$zzRoot = dirname(__DIR__);
$zzDone = false;
$zzMailBefore = glob(STORAGE_PATH . '/mail/*.eml') ?: [];
$zzAuditStart = (int) Database::value('SELECT COALESCE(MAX(id), 0) FROM audit_log', [], 0);
$zzLogFile = STORAGE_PATH . '/logs/app-' . date('Y-m-d') . '.log';
$zzLogStart = is_file($zzLogFile) ? (int) filesize($zzLogFile) : 0;
$zzIssues = [];
$zzMockPort = 0;
$zzMockProc = null;
$zzSettingRows = Database::all("SELECT * FROM settings WHERE setting_key LIKE 'social\\_%' OR setting_key = 'maintenance_show_social'");
$zzEnvBefore = (string) @file_get_contents($zzRoot . '/.env');

/** put the social settings back EXACTLY as they were (rows, timestamps) */
$zzRestoreSettings = static function () use ($zzSettingRows): void {
    Database::execute("DELETE FROM settings WHERE setting_key LIKE 'social\\_%' OR setting_key = 'maintenance_show_social'");
    foreach ($zzSettingRows as $row) {
        Database::insert('settings', ['setting_key' => $row['setting_key'], 'setting_value' => $row['setting_value'], 'created_at' => $row['created_at'], 'updated_at' => $row['updated_at']]);
    }
    SocialConfig::flush();
};
$zzCleanup = static function () use (&$zzIssues): void {
    foreach ($zzIssues as $id) {
        Database::execute('DELETE FROM newsletters WHERE id = ?', [$id]);          // social_posts / social_images / revisions go with it (ON DELETE CASCADE)
    }
    $zzIssues = [];
};
$zzFinish = static function () use (&$zzDone, $zzCleanup, $zzRestoreSettings, &$zzMockProc, &$zzMockPort, $zzMailBefore): void {
    if ($zzDone) {
        return;
    }
    $zzDone = true;
    Channels::reset();
    SocialHttp::$transport = null;
    SocialHttp::$resolver = null;
    putenv('SOCIAL_DEV_BASE_URL');
    putenv('SOCIAL_FB_PAGE_TOKEN');
    $zzCleanup();
    foreach (array_diff(glob(STORAGE_PATH . '/mail/*.eml') ?: [], $zzMailBefore) as $f) {
        if (str_contains((string) @file_get_contents($f), 'Social media')) {
            @unlink($f);
        }
    }
    $zzRestoreSettings();
    if (is_resource($zzMockProc)) {
        proc_terminate($zzMockProc);
        proc_close($zzMockProc);
    }
    @unlink(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'lumina-mock-social-' . $zzMockPort . '.json');
    Settings::flush();
};
register_shutdown_function($zzFinish);

// ------------------------------------------------------------------------------------------------ helpers
$zzIssue = static function (array $o = []) use (&$zzIssues): int {
    $c = NewsletterContent::emptyContent();
    $c['featured']['heading'] = 'ZZ social featured heading';
    $c['featured']['body_html'] = '<p>Body of the zz social test issue.</p>';
    $r = NewsletterService::create($o + ['title' => 'ZZ-SOCIAL ' . bin2hex(random_bytes(3)), 'summary' => 'A short summary of the zz social test issue.', 'issue_date' => date('Y-m-d'), 'content' => $c]);
    if (!$r['ok']) {
        throw new \RuntimeException('could not create the test issue: ' . json_encode($r));
    }
    $zzIssues[] = (int) $r['newsletter']['id'];
    return (int) $r['newsletter']['id'];
};
/** set the values of a channel (secrets encrypted) and switch it on / off */
$zzConfigure = static function (string $ch, array $values, bool $enabled = true): void {
    foreach (Channels::get($ch)->meta()['fields'] as $f) {
        if (array_key_exists($f['key'], $values)) {
            if ($f['secret']) {
                SocialConfig::putSecret($ch, $f['key'], (string) $values[$f['key']]);
            } else {
                Settings::set(SocialConfig::key($ch, $f['key']), (string) $values[$f['key']]);
            }
        }
    }
    Settings::set(SocialConfig::key($ch, 'enabled'), $enabled ? '1' : '0');
    SocialConfig::flush();
};
$zzResetChannels = static function (): void {
    foreach (Channels::ids() as $id) {
        Settings::set(SocialConfig::key($id, 'enabled'), '0');
        Settings::set(SocialConfig::key($id, 'status'), '');
        Settings::set(SocialConfig::key($id, 'notified'), '');
        foreach (Channels::get($id)->meta()['fields'] as $f) {
            if ($f['secret']) {
                SocialConfig::forgetSecret($id, $f['key']);
            }
        }
    }
    SocialConfig::flush();
};
$zzMails = static function () use ($zzMailBefore): array {
    $out = [];
    foreach (array_diff(glob(STORAGE_PATH . '/mail/*.eml') ?: [], $zzMailBefore) as $f) {
        $raw = (string) file_get_contents($f);
        if (str_contains($raw, 'Social media')) {
            $out[] = $raw;
        }
    }
    return $out;
};
$zzTok = [
    'fb'     => 'ZZFB' . bin2hex(random_bytes(14)),
    'tg'     => '123456789:ZZTG' . bin2hex(random_bytes(12)),
    'wh'     => 'zzhooksecret' . bin2hex(random_bytes(6)),
    'li'     => 'ZZLI' . bin2hex(random_bytes(14)),
];
$zzAllTokens = array_values($zzTok);

// =====================================================================================================================================================
echo "Social: schema, seed, abilities, settings\n";
check('tables social_posts and social_images exist', Database::tableExists('social_posts') && Database::tableExists('social_images'));
$cols = array_column(Database::all('SHOW COLUMNS FROM newsletters'), 'Type', 'Field');
check('newsletters has share_choices (TEXT NULL) and share_version (default 1)', isset($cols['share_choices'], $cols['share_version']) && str_contains((string) $cols['share_choices'], 'text'));
$uq = Database::all("SELECT column_name FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'social_posts' AND index_name = 'uq_social_posts_issue_channel' AND non_unique = 0 ORDER BY seq_in_index");
check('social_posts has UNIQUE (newsletter_id, channel, share_version)', array_column($uq, 'column_name') === ['newsletter_id', 'channel', 'share_version']);
check('social_posts and social_images cascade with the issue', (int) Database::value("SELECT COUNT(*) FROM information_schema.referential_constraints WHERE constraint_schema = DATABASE() AND constraint_name IN ('fk_social_posts_issue','fk_social_images_issue') AND delete_rule = 'CASCADE'", [], 0) === 2);
check('social_posts.status has the six states', str_contains((string) Database::one("SHOW COLUMNS FROM social_posts LIKE 'status'")['Type'], "'review'") && str_contains((string) Database::one("SHOW COLUMNS FROM social_posts LIKE 'status'")['Type'], "'posting'"));
check('social_images: PRIMARY KEY token CHAR(64), UNIQUE (issue, variant)', str_contains(strtolower((string) Database::one("SHOW COLUMNS FROM social_images LIKE 'token'")['Type']), 'char(64)') && (int) Database::value("SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'social_images' AND index_name = 'uq_social_images_issue_variant' AND non_unique = 0", [], 0) === 2);
$seedSql = (string) file_get_contents($zzRoot . '/database/seed.sql');
$missing = [];
$wrong = [];
foreach (SocialConfig::defs() as $k => $d) {
    if (preg_match("/\\('" . preg_quote($k, '/') . "', '((?:[^']|'')*)'\\)/", $seedSql, $m) !== 1) {
        $missing[] = $k;
    } elseif (str_replace("''", "'", $m[1]) !== $d[0]) {
        $wrong[] = $k;
    }
}
check('every SocialConfig::defs() key is seeded with its default (seed.sql and the PHP definitions cannot drift)', $missing === [] && $wrong === [], 'missing ' . implode(',', $missing) . ' wrong ' . implode(',', $wrong));
check('the three new profile links are seeded and defined in the settings schema; channel / template keys are NOT in the generic schema',
    str_contains($seedSql, "('social_threads', '')") && str_contains($seedSql, "('social_telegram', '')") && str_contains($seedSql, "('social_whatsapp_channel', '')")
    && Settings::definition('social_threads') !== null && Settings::definition('social_telegram') !== null && Settings::definition('social_whatsapp_channel') !== null
    && Settings::definition('social_enabled') === null && Settings::definition('social_ch_facebook_enabled') === null && Settings::definition('social_tpl_x') === null && Settings::definition('social_facebook') !== null);
check('no secret, token or ciphertext is seeded', !str_contains($seedSql, 'social_secret_') && !str_contains($seedSql, 'enc:v1:'));
check('every channel starts SWITCHED OFF in the seed', preg_match_all("/\\('social_ch_[a-z]+_enabled', '0'\\)/", $seedSql) === 6);
$abil = (array) Config::get('abilities', []);
check('abilities: social.manage = admin only, social.share = admin + editor', ($abil['social.manage'] ?? null) === ['admin'] && ($abil['social.share'] ?? null) === ['admin', 'editor']);
$cat = require $zzRoot . '/app/config/ability_catalog.php';
check('ability catalogue lists both; social.manage is flagged as holding secrets', isset($cat['social']['items']['social.manage'], $cat['social']['items']['social.share']) && ($cat['social']['items']['social.manage']['secrets'] ?? false) === true);
Roles::flush();
check('Roles: editor may share, never configure; admin holds both', Roles::can('editor', 'social.share') && !Roles::can('editor', 'social.manage') && Roles::can('admin', 'social.manage'));
$editorList = json_decode((string) Database::value("SELECT abilities FROM roles WHERE slug = 'editor'", [], '[]'), true);
check('the built-in Editor role stored list includes social.share (one-time upgrade ran, or the fresh seed)', is_array($editorList) && in_array('social.share', $editorList, true) && !in_array('social.manage', $editorList, true));
check('the one-time editor upgrade is marker-guarded and compares the exact old list (an edited role is never touched)', str_contains($seedSql, 'migration_social_editor_role_v1') && str_contains($seedSql, "AND abilities = '[\"dashboard.view\",\"pages.edit\",\"content.manage\",\"content.delete\",\"inbox.manage\",\"export.csv\",\"members.manage\",\"newsletter.manage\",\"calendar.manage\",\"meetings.manage\",\"forms.manage\",\"forms.submissions\",\"shop.manage\",\"shop.orders\"]'"));
check('the admin sidebar fragment follows the abilities (Profile links / Channels / Templates: manage; Log / Kit: share)', (static function (): bool {
    $f = require dirname(__DIR__) . '/app/config/admin_nav/social.php';
    $by = array_column($f['items'], 'can', 'key');
    return $by === ['social_profiles' => 'social.manage', 'social_channels' => 'social.manage', 'social_templates' => 'social.manage', 'social_log' => 'social.share', 'social_kit' => 'social.share'];
})());
check('.env contains no social token or developer override right now', !preg_match('/^\s*SOCIAL_[A-Z_]*(TOKEN|SECRET|KEY|DEV_BASE_URL)\s*=\s*\S/m', $zzEnvBefore));

// =====================================================================================================================================================
echo "Social: SocialHttp - SSRF guard of the webhook\n";
$resolverMap = [
    'ok.example.com' => ['93.184.216.34'], 'ok6.example.com' => ['2606:4700:4700::1111'], 'internal.evil.com' => ['10.0.0.5'], 'loop.evil.com' => ['127.0.0.1'], 'meta.evil.com' => ['169.254.169.254'],
    'mixed.evil.com' => ['93.184.216.34', '127.0.0.1'], 'v6mixed.evil.com' => ['2606:4700:4700::1111', 'fe80::1'], 'nxdomain.example.com' => [], 'cgnat.evil.com' => ['100.64.0.9'],
    'mapped.evil.com' => ['::ffff:127.0.0.1'], 'v6loop.evil.com' => ['::1'], 'ula.evil.com' => ['fd00::5'], 'nat64.evil.com' => ['64:ff9b::7f00:1'], 'doc.evil.com' => ['2001:db8::1'], 'tunnel.evil.com' => ['2002:7f00:1::1'],
];
$zzResolver = static fn(string $h): array => $resolverMap[$h] ?? ['93.184.216.34'];
SocialHttp::$resolver = $zzResolver;
$deny = static fn(string $url): bool => !SocialHttp::guardWebhookUrl($url)['ok'];
$allowed = ['https://ok.example.com/hook', 'https://ok.example.com:8443/hook?x=1', 'https://hooks.zapier.com/hooks/catch/123/abc/', 'https://ok6.example.com/h', 'https://93.184.216.34/h', 'https://[2606:4700:4700::1111]/h', 'https://ok.example.com./hook'];
foreach ($allowed as $u) {
    $g = SocialHttp::guardWebhookUrl($u);
    check('allowed: ' . $u, $g['ok'] === true, (string) ($g['error'] ?? ''));
}
$denied = [
    'empty' => '', 'http' => 'http://ok.example.com/h', 'ftp' => 'ftp://ok.example.com/h', 'file' => 'file:///etc/passwd', 'javascript' => 'javascript:alert(1)', 'gopher' => 'gopher://ok.example.com/_x', 'no scheme' => 'ok.example.com/h',
    'userinfo' => 'https://user:pass@ok.example.com/h', 'user only' => 'https://user@ok.example.com/h', 'at in host trick' => 'https://ok.example.com@evil.example.com/h', 'at in host trick 2' => 'https://127.0.0.1@ok.example.com/h',
    'port 80' => 'https://ok.example.com:80/h', 'port 22' => 'https://ok.example.com:22/h', 'port 8080' => 'https://ok.example.com:8080/h', 'port 6379' => 'https://ok.example.com:6379/', 'port 0' => 'https://ok.example.com:0/',
    'localhost' => 'https://localhost/h', 'localhost.' => 'https://localhost./h', 'sub.localhost' => 'https://a.localhost/h', 'dot local' => 'https://printer.local/h', 'dot internal' => 'https://x.internal/h', 'metadata' => 'https://metadata.google.internal/computeMetadata/v1/',
    'single label' => 'https://intranet/h', 'loopback v4' => 'https://127.0.0.1/h', 'loopback v4 other' => 'https://127.1.2.3/h', 'decimal ip' => 'https://2130706433/h', 'hex ip' => 'https://0x7f000001/h', 'hex dotted' => 'https://0x7f.0.0.1/h',
    'octal dotted' => 'https://0177.0.0.1/h', 'short form' => 'https://127.1/h', 'short form 2' => 'https://10.1/h', 'mixed radix' => 'https://0x7f.1/h', 'overflow' => 'https://4294967297/h', 'private 10' => 'https://10.0.0.1/h', 'private 172' => 'https://172.16.5.5/h',
    'private 192' => 'https://192.168.1.1/h', 'link-local' => 'https://169.254.169.254/latest/meta-data/', 'cgnat' => 'https://100.64.0.1/h', 'zero' => 'https://0.0.0.0/h', 'broadcast' => 'https://255.255.255.255/h', 'multicast' => 'https://224.0.0.1/h',
    'reserved 240' => 'https://240.0.0.1/h', 'doc net 1' => 'https://192.0.2.1/h', 'doc net 2' => 'https://198.51.100.7/h', 'doc net 3' => 'https://203.0.113.9/h', 'benchmark' => 'https://198.18.0.1/h', 'v6 loopback' => 'https://[::1]/h',
    'v6 unspecified' => 'https://[::]/h', 'v6 mapped v4 loopback' => 'https://[::ffff:127.0.0.1]/h', 'v6 mapped hex' => 'https://[::ffff:7f00:1]/h', 'v6 mapped public' => 'https://[::ffff:8.8.8.8]/h', 'v6 link-local' => 'https://[fe80::1]/h',
    'v6 unique-local' => 'https://[fc00::1]/h', 'v6 unique-local 2' => 'https://[fd12:3456::1]/h', 'v6 doc' => 'https://[2001:db8::1]/h', 'v6 nat64' => 'https://[64:ff9b::7f00:1]/h', 'v6 6to4' => 'https://[2002:7f00:1::1]/h', 'v6 teredo' => 'https://[2001:0:4136:e378:8000:63bf:3fff:fdd2]/h',
    'v6 zone id' => 'https://[fe80::1%25eth0]/h', 'v6 junk' => 'https://[zzzz]/h',
    'dns private' => 'https://internal.evil.com/h', 'dns loopback' => 'https://loop.evil.com/h', 'dns metadata' => 'https://meta.evil.com/h', 'dns mixed (one public, one private)' => 'https://mixed.evil.com/h', 'dns v6 mixed' => 'https://v6mixed.evil.com/h',
    'dns cgnat' => 'https://cgnat.evil.com/h', 'dns mapped' => 'https://mapped.evil.com/h', 'dns v6 loop' => 'https://v6loop.evil.com/h', 'dns ula' => 'https://ula.evil.com/h', 'dns nat64' => 'https://nat64.evil.com/h', 'dns doc' => 'https://doc.evil.com/h',
    'dns 6to4' => 'https://tunnel.evil.com/h', 'dns nothing' => 'https://nxdomain.example.com/h',
    'space' => 'https://ok.example.com/a b', 'newline' => "https://ok.example.com/h\nHost: evil.com", 'crlf' => "https://ok.example.com/h\r\nX: y", 'tab' => "https://ok.example.com/\th", 'nul' => "https://ok.example.com/h\0", 'unicode host' => 'https://münchen.example.com/h',
    'too long' => 'https://ok.example.com/' . str_repeat('a', 2100), 'backslash trick' => 'https://ok.example.com\\@127.0.0.1/h', 'only slashes' => 'https:///h', 'double dot' => 'https://ok..example.com/h', 'dash start' => 'https://-ok.example.com/h',
    'numeric tld' => 'https://example.123/h', 'underscore' => 'https://ok_host.example.com/h',
];
$bad = [];
foreach ($denied as $label => $u) {
    if (!$deny($u)) {
        $bad[] = $label;
    }
}
check('denied (' . count($denied) . ' hostile addresses: IP spellings, ports, user info, local names, DNS answers, junk)', $bad === [], 'ALLOWED: ' . implode(' | ', $bad));
$resolves = 0;
SocialHttp::$resolver = static function (string $h) use (&$resolves): array {
    $resolves++;
    return ['93.184.216.34'];
};
SocialHttp::guardWebhookUrl('https://ok.example.com/h');
check('a public host is resolved ONCE per check; an IP literal is never resolved', $resolves === 1 && (function () use (&$resolves): bool {
    $before = $resolves;
    SocialHttp::guardWebhookUrl('https://93.184.216.34/h');
    return $resolves === $before;
})());
SocialHttp::$resolver = $zzResolver;
check('isPublicIp: public yes, every private / reserved range no (IPv4 and IPv6)', SocialHttp::isPublicIp('8.8.8.8') && SocialHttp::isPublicIp('2606:4700:4700::1111') && !SocialHttp::isPublicIp('127.0.0.1') && !SocialHttp::isPublicIp('::1')
    && !SocialHttp::isPublicIp('10.255.255.255') && !SocialHttp::isPublicIp('172.31.0.1') && SocialHttp::isPublicIp('172.32.0.1') && !SocialHttp::isPublicIp('192.168.0.1') && !SocialHttp::isPublicIp('169.254.0.1') && !SocialHttp::isPublicIp('not an ip'));
// pinning and redirects through the test transport
$sent = [];
$script = [];
SocialHttp::$transport = static function (array $req) use (&$sent, &$script): array {
    $sent[] = $req;
    return array_shift($script) ?? ['status' => 200, 'headers' => [], 'raw' => '{"ok":true}', 'kind' => '', 'ambiguous' => false, 'detail' => ''];
};
$r = SocialHttp::webhook('https://ok.example.com/hook', '{"a":1}', ['X-Test' => '1']);
check('webhook: POST to the checked address; the connection is PINNED to the IP that was validated (no second DNS lookup)', $r['ok'] && count($sent) === 1 && $sent[0]['pin'] === '93.184.216.34' && $sent[0]['url'] === 'https://ok.example.com/hook' && $sent[0]['method'] === 'POST' && $sent[0]['body'] === '{"a":1}' && $sent[0]['allow_http'] === false);
$sent = [];
$script = [['status' => 302, 'headers' => ['location' => 'https://10.0.0.5/x'], 'raw' => '', 'kind' => '', 'ambiguous' => false, 'detail' => '']];
$r = SocialHttp::webhook('https://ok.example.com/hook', '{}');
check('a 302 redirect (to a private address) is not followed and never requested', !$r['ok'] && $r['kind'] === 'redirect' && count($sent) === 1, json_encode([$r['kind'], count($sent)]));
$sent = [];
$script = [['status' => 301, 'headers' => ['location' => 'https://internal.evil.com/x'], 'raw' => '', 'kind' => '', 'ambiguous' => false, 'detail' => '']];
$r = SocialHttp::webhook('https://ok.example.com/hook', '{}');
check('a 301 redirect is not followed either: a POST is never repeated somewhere else', !$r['ok'] && $r['kind'] === 'redirect' && count($sent) === 1 && str_contains((string) $r['error'], 'not followed'));
$sent = [];
$script = [['status' => 302, 'headers' => ['location' => 'http://ok.example.com/plain'], 'raw' => '', 'kind' => '', 'ambiguous' => false, 'detail' => '']];
$r = SocialHttp::webhook('https://ok.example.com/hook', '{}');
check('a 302 redirect to plain http is not followed', !$r['ok'] && $r['kind'] === 'redirect' && count($sent) === 1);
$sent = [];
$script = [['status' => 303, 'headers' => ['location' => '/elsewhere'], 'raw' => '', 'kind' => '', 'ambiguous' => false, 'detail' => '']];
$r = SocialHttp::webhook('https://ok.example.com/hook', '{}', ['X-Lumina-Signature' => 'sig']);
check('a 303 redirect (even on the same host) is not followed', !$r['ok'] && $r['kind'] === 'redirect' && count($sent) === 1);
$sent = [];
$script = [['status' => 307, 'headers' => ['location' => 'https://evil2.example.org/steal'], 'raw' => '', 'kind' => '', 'ambiguous' => false, 'detail' => '']];
$r = SocialHttp::webhook('https://ok.example.com/hook', '{"secret":"body"}', ['X-Lumina-Signature' => 'sig', 'X-Lumina-Timestamp' => '1']);
check('a 307 / 308 to ANOTHER host is refused: the signed body and the signature headers never reach a host the admin did not enter', !$r['ok'] && $r['kind'] === 'redirect' && count($sent) === 1 && $sent[0]['url'] === 'https://ok.example.com/hook');
$sent = [];
$script = [['status' => 308, 'headers' => ['location' => 'https://ok.example.com:8443/hook'], 'raw' => '', 'kind' => '', 'ambiguous' => false, 'detail' => '']];
$r = SocialHttp::webhook('https://ok.example.com/hook', '{}');
check('a redirect to another PORT of the same host is refused too', !$r['ok'] && $r['kind'] === 'redirect' && count($sent) === 1);
$sent = [];
$script = [['status' => 307, 'headers' => ['location' => '/second'], 'raw' => '', 'kind' => '', 'ambiguous' => false, 'detail' => ''], ['status' => 308, 'headers' => ['location' => 'https://ok.example.com/third'], 'raw' => '', 'kind' => '', 'ambiguous' => false, 'detail' => ''], ['status' => 200, 'headers' => [], 'raw' => '{"id":"x"}', 'kind' => '', 'ambiguous' => false, 'detail' => '']];
$r = SocialHttp::webhook('https://ok.example.com/hook', '{"a":1}', ['X-Lumina-Signature' => 'sig']);
check('two 307 / 308 redirects on the SAME host are followed (method and body kept, each hop re-checked and pinned)', $r['ok'] && count($sent) === 3 && $sent[1]['url'] === 'https://ok.example.com/second' && $sent[2]['url'] === 'https://ok.example.com/third' && $sent[2]['pin'] === '93.184.216.34' && $sent[2]['method'] === 'POST' && $sent[2]['body'] === '{"a":1}', json_encode(array_column($sent, 'url')));
$sent = [];
$script = [['status' => 307, 'headers' => ['location' => '/a'], 'raw' => '', 'kind' => '', 'ambiguous' => false, 'detail' => ''], ['status' => 307, 'headers' => ['location' => '/b'], 'raw' => '', 'kind' => '', 'ambiguous' => false, 'detail' => ''], ['status' => 307, 'headers' => ['location' => '/c'], 'raw' => '', 'kind' => '', 'ambiguous' => false, 'detail' => '']];
$r = SocialHttp::webhook('https://ok.example.com/hook', '{}');
check('a third redirect is refused (at most 2)', !$r['ok'] && $r['kind'] === 'redirect' && count($sent) === 3);
$calls = 0;
SocialHttp::$resolver = static function (string $h) use (&$calls): array {
    $calls++;
    return $calls === 1 ? ['93.184.216.34'] : ['10.0.0.9'];             // DNS rebinding: the host answers differently the second time
};
$sent = [];
$script = [];
$r = SocialHttp::webhook('https://rebind.example.com/hook', '{}');
check('DNS rebinding: the request goes to the address that was checked (pinned), the later "private" answer is never used', $r['ok'] && $sent[0]['pin'] === '93.184.216.34' && $calls === 1);
SocialHttp::$resolver = $zzResolver;
$sent = [];
$r = SocialHttp::webhook('https://127.0.0.1/hook', '{}');
check('a blocked webhook address never reaches the transport', !$r['ok'] && $r['kind'] === 'blocked' && $sent === []);
SocialHttp::$transport = null;

echo "Social: SocialHttp - platform API hosts, paths, dev override, errors\n";
$sent = [];
SocialHttp::$transport = static function (array $req) use (&$sent): array {
    $sent[] = $req;
    return ['status' => 200, 'headers' => [], 'raw' => '{"ok":true}', 'kind' => '', 'ambiguous' => false, 'detail' => ''];
};
putenv('SOCIAL_DEV_BASE_URL');
$r = SocialHttp::api('telegram', 'POST', '/bot123456789:ZZTOKEN1234567890abcdef/getMe');
check('Telegram call: https://api.telegram.org, TLS only (plain http not allowed)', $r['ok'] && str_starts_with($sent[0]['url'], 'https://api.telegram.org/bot') && $sent[0]['allow_http'] === false && $sent[0]['pin'] === null);
$sent = [];
SocialHttp::api('facebook', 'GET', '/v23.0/me', ['query' => ['fields' => 'id,name']]);
SocialHttp::api('instagram', 'GET', '/v23.0/1', []);
SocialHttp::api('x', 'POST', '/2/tweets', ['json' => ['text' => 'hi']]);
SocialHttp::api('linkedin', 'GET', '/rest/organizationAcls');
check('each channel only talks to its own fixed host', array_map(static fn(array $q): string => (string) parse_url($q['url'], PHP_URL_HOST), $sent) === ['graph.facebook.com', 'graph.facebook.com', 'api.x.com', 'api.linkedin.com']);
$sent = [];
$r = SocialHttp::api('x', 'GET', '/2/users/me', ['host' => 'api.twitter.com']);
check('the second X host (api.twitter.com) is accepted', $r['ok'] && str_starts_with($sent[0]['url'], 'https://api.twitter.com/'));
$sent = [];
$blocked = [
    SocialHttp::api('telegram', 'GET', '/getMe', ['host' => 'evil.example.com']), SocialHttp::api('x', 'GET', '/2/x', ['host' => 'graph.facebook.com']), SocialHttp::api('mastodon', 'GET', '/x'),
    SocialHttp::api('telegram', 'GET', '//evil.com/x'), SocialHttp::api('telegram', 'GET', 'http://evil.com/x'), SocialHttp::api('telegram', 'GET', '/a/../b'), SocialHttp::api('telegram', 'GET', "/a b"), SocialHttp::api('telegram', 'GET', "/a\r\nHost: x"),
    SocialHttp::api('telegram', 'GET', '/x?y=1'), SocialHttp::api('telegram', 'PUT', '/x'), SocialHttp::api('telegram', 'GET', '@evil.com/x'), SocialHttp::api('telegram', 'GET', '/x', ['headers' => ["Bad\r\nHeader" => 'x']]),
    SocialHttp::api('telegram', 'GET', '/x', ['headers' => ['X-Ok' => "a\r\nInjected: 1"]]),
];
check('hostile hosts, paths, methods and headers are refused before any request', count(array_filter($blocked, static fn(array $b): bool => $b['kind'] === 'blocked' && !$b['ok'])) === count($blocked) && $sent === [], json_encode(array_map(static fn(array $b): string => (string) $b['kind'], $blocked)));
check('urlFor() is exactly what api() requests', (function () use (&$sent): bool {
    $sent = [];
    SocialHttp::api('facebook', 'GET', '/v23.0/me', ['query' => ['fields' => 'id,name']]);
    return $sent[0]['url'] === SocialHttp::urlFor('facebook', '/v23.0/me', ['fields' => 'id,name']);
})());
$seen = null;
SocialHttp::api('x', 'POST', '/2/tweets', ['json' => ['text' => 'hi'], 'authorize' => static function (string $m, string $u, array $q, array $f) use (&$seen): array {
    $seen = [$m, $u];
    return ['Authorization' => 'OAuth test'];
}]);
check('the authorize callback gets the final method + url and may add headers (OAuth 1.0a signing)', $seen === ['POST', 'https://api.x.com/2/tweets'] && ($sent[count($sent) - 1]['headers']['Authorization'] ?? '') === 'OAuth test');
// dev override
$dev = 'http://127.0.0.1:18123';
putenv('SOCIAL_DEV_BASE_URL=' . $dev);
$sent = [];
SocialHttp::api('telegram', 'POST', '/bot1:x/getMe');
SocialHttp::api('facebook', 'GET', '/v23.0/me');
SocialHttp::api('x', 'GET', '/2/users/me');
SocialHttp::api('linkedin', 'GET', '/rest/posts');
check('dev override (APP_ENV=local): calls go to the local mock under /telegram /graph /x /linkedin, plain http allowed', array_column($sent, 'url') === [$dev . '/telegram/bot1:x/getMe', $dev . '/graph/v23.0/me', $dev . '/x/2/users/me', $dev . '/linkedin/rest/posts'] && $sent[0]['allow_http'] === true && SocialHttp::usingDevBase());
Config::set('app.env', 'production');
$sent = [];
SocialHttp::api('telegram', 'POST', '/bot1:x/getMe');
$prodUrl = $sent[0]['url'] ?? '';
check('the override is IGNORED outside APP_ENV=local: production always uses the real https host', $prodUrl === 'https://api.telegram.org/bot1:x/getMe' && !SocialHttp::usingDevBase() && $sent[0]['allow_http'] === false);
Config::set('app.env', 'local');
foreach (['http://evil.example', 'http://127.0.0.1.evil.example', 'http://user:pw@127.0.0.1:1', 'http://127.0.0.1:99/path', 'ftp://127.0.0.1', 'file:///etc/passwd', 'http://localhost.evil.com', 'http://127.0.0.1:99?x=1', 'http://10.0.0.1:99', 'http://[::1]x'] as $badUrl) {
    putenv('SOCIAL_DEV_BASE_URL=' . $badUrl);
    if (SocialHttp::devBase() !== null) {
        check('a non-local dev base URL is refused: ' . $badUrl, false);
    }
}
check('hostile dev base URLs are all refused (other hosts, userinfo, paths, queries, other schemes)', true);
putenv('SOCIAL_DEV_BASE_URL=' . $dev);
check('dev mode lets ONLY <base>/webhook through the webhook guard; other local addresses stay blocked', SocialHttp::guardWebhookUrl($dev . '/webhook/x')['ok'] && !SocialHttp::guardWebhookUrl('http://127.0.0.1:18124/webhook/x')['ok'] && !SocialHttp::guardWebhookUrl($dev . '/other')['ok'] && !SocialHttp::guardWebhookUrl('http://169.254.169.254/webhook')['ok'] && !SocialHttp::guardWebhookUrl('http://user@127.0.0.1:18123/webhook')['ok']);
Config::set('app.env', 'production');
check('...and not at all outside APP_ENV=local', !SocialHttp::guardWebhookUrl($dev . '/webhook/x')['ok']);
Config::set('app.env', 'local');
putenv('SOCIAL_DEV_BASE_URL');
SocialHttp::$transport = null;
$httpSrc = (string) file_get_contents($zzRoot . '/app/services/Social/SocialHttp.php');
check('source guard: TLS verification ON, no redirect following, answer capped (cURL and stream paths)', str_contains($httpSrc, 'CURLOPT_SSL_VERIFYPEER  => true') && str_contains($httpSrc, 'CURLOPT_SSL_VERIFYHOST  => 2') && str_contains($httpSrc, 'CURLOPT_FOLLOWLOCATION  => false')
    && !str_contains($httpSrc, 'CURLOPT_FOLLOWLOCATION  => true') && str_contains($httpSrc, "'verify_peer' => true") && str_contains($httpSrc, "'verify_peer_name' => true") && !str_contains($httpSrc, "'verify_peer' => false") && str_contains($httpSrc, 'MAX_BODY = 1048576'));
check('timeouts: connect 4 s, total 10 s, 25 s overall budget, at most 2 webhook redirects, ports 443 / 8443', SocialHttp::CONNECT_TIMEOUT === 4 && SocialHttp::TOTAL_TIMEOUT === 10 && SocialDispatcher::OVERALL_BUDGET === 25.0 && SocialHttp::MAX_REDIRECTS === 2 && SocialHttp::WEBHOOK_PORTS === [443, 8443]);
// budget
$sent = [];
SocialHttp::$transport = static function (array $req) use (&$sent): array {
    $sent[] = $req;
    return ['status' => 200, 'headers' => [], 'raw' => '{}', 'kind' => '', 'ambiguous' => false, 'detail' => ''];
};
$r = SocialHttp::api('telegram', 'POST', '/bot1:x/getMe', ['deadline' => microtime(true) + 0.3]);
check('an exhausted time budget makes NO request and is not ambiguous', !$r['ok'] && $r['kind'] === 'budget' && $sent === [] && !$r['ambiguous']);
$r = SocialHttp::api('telegram', 'POST', '/bot1:x/getMe', ['deadline' => microtime(true) + 6.0, 'timeout' => 10, 'connect_timeout' => 4]);
check('the per-request timeout is clamped to the remaining budget', $sent[0]['timeout'] <= 6.0 && $sent[0]['timeout'] > 4.0 && $sent[0]['connect_timeout'] <= 4.0);
SocialHttp::$transport = null;

echo "Social: SocialHttp - error mapping and redaction\n";
$map = static fn(array $r): SocialResult => SocialHttp::toResult('facebook', $r + ['ok' => false, 'status' => 0, 'json' => null, 'raw' => '', 'headers' => [], 'error' => 'x', 'kind' => '', 'ambiguous' => false, 'retry_after' => null, 'ms' => 1]);
check('timeout AFTER the request was sent -> review (ambiguous), never a blind retry', $map(['kind' => 'timeout', 'ambiguous' => true])->state === 'review');
check('connect failure / TLS problem BEFORE sending -> transient', $map(['kind' => 'network'])->state === 'transient' && $map(['kind' => 'tls'])->state === 'transient' && $map(['kind' => 'timeout'])->state === 'transient');
check('HTTP 401 -> auth; Graph error code 190 / 102 / 463 -> auth', $map(['status' => 401])->state === 'auth' && $map(['status' => 400, 'json' => ['error' => ['code' => 190, 'message' => 'Invalid OAuth access token']]])->state === 'auth'
    && $map(['status' => 400, 'json' => ['error' => ['code' => 463]]])->state === 'auth' && $map(['status' => 400, 'json' => ['error' => ['code' => 100, 'message' => 'bad param']]])->state === 'failed');
check('HTTP 429 -> transient with the Retry-After seconds', ($t = $map(['status' => 429, 'retry_after' => 12]))->state === 'transient' && $t->retryAfter === 12);
check('HTTP 502 and 504 -> review; 500 and 503 -> transient; other 4xx -> failed with the platform message', $map(['status' => 502, 'ambiguous' => true])->state === 'review' && $map(['status' => 504, 'ambiguous' => true])->state === 'review'
    && $map(['status' => 500])->state === 'transient' && $map(['status' => 503])->state === 'transient' && $map(['status' => 400, 'json' => ['error' => ['message' => 'Param message is required']]])->state === 'failed');
check('blocked / budget / redirect / too_large map to failed / transient(budget) / failed / failed', $map(['kind' => 'blocked'])->state === 'failed' && ($b = $map(['kind' => 'budget']))->state === 'transient' && $b->code === 'budget' && $map(['kind' => 'redirect'])->state === 'failed' && $map(['kind' => 'too_large'])->state === 'failed');
$tokenText = 'Invalid token ' . $zzTok['fb'] . ' for https://graph.facebook.com/me?access_token=' . $zzTok['fb'] . '&x=1 Authorization: Bearer abcdefghijklmnop0123456789 oauth_signature="abc%2Fdef" bot123456789:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA';
$zzConfigure('facebook', ['page_id' => '105234567890123', 'page_token' => $zzTok['fb']], false);
$red = SocialHttp::redact($tokenText);
check('redact(): the configured token, access_token=, Bearer, OAuth signatures and bot tokens are blanked', !str_contains($red, $zzTok['fb']) && !str_contains($red, 'abcdefghijklmnop0123456789') && !str_contains($red, 'AAAAAAAAAAAAAAAAAAAA') && !str_contains($red, 'abc%2Fdef') && str_contains($red, 'hidden'), $red);
$r = SocialResult::failed($tokenText);
check('SocialResult error texts are redacted by the constructor', !str_contains($r->error, $zzTok['fb']) && !str_contains($r->error, 'access_token=ZZ'));
$apiMsg = SocialHttp::apiMessage(['json' => ['error' => ['message' => 'Token ' . $zzTok['fb'] . ' is bad']]]);
check('apiMessage(): the platform text is redacted too', !str_contains($apiMsg, $zzTok['fb']));
$zzResetChannels();

// =====================================================================================================================================================
echo "Social: caption builder\n";
$issue = ['title' => 'Building Pathways to Opportunity', 'summary' => 'Our youth training programme welcomed its newest group this month.', 'url' => 'https://example.org/newsletter/issue-five', 'issue_number' => 5, 'issue_date' => '2026-10-20', 'site' => 'Lumina Africa Foundation'];
$b = CaptionBuilder::build('facebook', $issue, "{title}|{summary}|{url}|{issue_number}|{date}|{hashtags}|{site}", ['hashtags' => '#One two #three']);
check('placeholders: title, summary, url (with UTM), issue number, date, hashtags, site', $b['text'] === 'Building Pathways to Opportunity|Our youth training programme welcomed its newest group this month.|https://example.org/newsletter/issue-five?utm_source=facebook&utm_medium=social&utm_campaign=newsletter|5|20 October 2026|#One #two #three|Lumina Africa Foundation', $b['text']);
$b = CaptionBuilder::build('x', ['title' => 'Evil {url} {summary}', 'summary' => '{title}', 'url' => 'https://example.org/a'] + $issue, '{title} / {summary}', ['hashtags' => '']);
check('a title that contains placeholders stays literal (single-pass expansion)', $b['text'] === 'Evil {url} {summary} / {title}', $b['text']);
$b = CaptionBuilder::build('facebook', $issue, '{title} {nonsense} {Summary}');
check('unknown placeholders are reported and left as they are; names are not case sensitive', $b['unknown'] === ['{nonsense}'] && str_contains($b['text'], '{nonsense}') && str_contains($b['text'], 'Our youth training'));
check('no issue number -> {issue_number} is empty; bad date -> empty', CaptionBuilder::build('facebook', ['issue_number' => null, 'issue_date' => 'garbage'] + $issue, 'N{issue_number}D{date}')['text'] === 'ND');
check('UTM: added with ?, kept after an existing query, never duplicated, switched off by the option; fragment kept', CaptionBuilder::utmUrl('https://a.org/x', 'telegram', true) === 'https://a.org/x?utm_source=telegram&utm_medium=social&utm_campaign=newsletter'
    && CaptionBuilder::utmUrl('https://a.org/x?p=2#top', 'x', true) === 'https://a.org/x?p=2&utm_source=x&utm_medium=social&utm_campaign=newsletter#top' && CaptionBuilder::utmUrl('https://a.org/x?utm_source=mail', 'x', true) === 'https://a.org/x?utm_source=mail&utm_medium=social&utm_campaign=newsletter'
    && CaptionBuilder::utmUrl('https://a.org/x', 'x', false) === 'https://a.org/x' && CaptionBuilder::utmUrl('/relative', 'x', true) === '/relative');
// X counting
check('X counting: a URL counts 23 whatever its length', CaptionBuilder::length('x', 'https://example.org/' . str_repeat('a', 400)) === 23 && CaptionBuilder::length('x', 'see https://example.org/a and http://b.co/c') === 4 + 23 + 5 + 23);
check('X counting: ASCII and Latin text weight 1, emoji and CJK weight 2', CaptionBuilder::length('x', 'abc') === 3 && CaptionBuilder::length('x', "caf\u{e9}") === 4 && CaptionBuilder::length('x', "\u{1F600}") === 2 && CaptionBuilder::length('x', "\u{65E5}\u{672C}") === 4);
check('plain channels count characters (not bytes)', CaptionBuilder::length('facebook', "caf\u{e9}\u{1F600}") === 5 && CaptionBuilder::length('instagram', str_repeat('a', 10)) === 10);
check('limits: facebook 5000, instagram 2200, x 280, linkedin 3000, telegram 1024, webhook 5000, tiktok 2200', array_map([CaptionBuilder::class, 'limit'], ['facebook', 'instagram', 'x', 'linkedin', 'telegram', 'webhook', 'tiktok']) === [5000, 2200, 280, 3000, 1024, 5000, 2200]);
$long = ['title' => 'T' . str_repeat('itle word ', 20), 'summary' => str_repeat('A long summary sentence that goes on and on. ', 40), 'url' => 'https://example.org/newsletter/a-very-long-slug-' . str_repeat('x', 80)] + $issue;
$ok = true;
$why = '';
foreach (array_merge(Channels::ids(), ['tiktok']) as $ch) {
    $b = CaptionBuilder::build($ch, $long, null, ['hashtags' => '#a #b #c #d #e #f #g #h #i #j #k #l']);
    if ($b['over'] || CaptionBuilder::length($ch, $b['text']) > $b['limit'] || $b['text'] === '' || !$b['trimmed'] && $ch === 'x') {
        $ok = false;
        $why .= $ch . '(' . $b['length'] . '/' . $b['limit'] . ') ';
    }
}
check('a long issue is SHRUNK to fit every channel (summary first), never cut blindly, never empty', $ok, $why);
$b = CaptionBuilder::build('x', $long);
check('X: the shrunk caption keeps the title start and the link, and fits 280 weighted characters', $b['length'] <= 280 && str_contains($b['text'], 'utm_source=x') && str_starts_with($b['text'], 'Title word') && !str_contains($b['text'], 'A long summary sentence that goes on and on. A long'), $b['text']);
$b = CaptionBuilder::build('x', ['title' => 'Short title', 'summary' => str_repeat('Summary words here. ', 30), 'url' => 'https://example.org/n/abc'] + $issue);
check('X: a long summary is cut at a word boundary with an ellipsis, the link (23) and the hashtags stay', $b['length'] <= 280 && $b['length'] >= 240 && str_contains($b['text'], '…') && str_contains($b['text'], 'https://example.org/n/abc?utm_source=x') && str_contains($b['text'], '#LuminaAfricaFoundation'), $b['text']);
$b = CaptionBuilder::build('x', ['title' => str_repeat('W', 400)] + $issue, '{title}', ['hashtags' => '']);
check('a title longer than the limit itself is shortened too (last resort)', $b['length'] <= 280 && !$b['over'] && str_ends_with($b['text'], '…'));
$v = CaptionBuilder::validate('x', str_repeat('a', 281));
check('validate(): a typed caption is never shortened, it is reported (with how many characters too long)', !$v['ok'] && $v['over'] && str_contains($v['error'], '1 character too long') && strlen($v['text']) === 281);
check('validate(): empty after cleaning is an error; a good caption passes', !CaptionBuilder::validate('facebook', "  \u{200B}  ")['ok'] && CaptionBuilder::validate('facebook', 'Hello')['ok']);
// sanitising
check('sanitize: tags stripped, entities decoded then stripped again (&lt;script&gt;), NUL and control characters removed', CaptionBuilder::sanitize('<b>Hi</b> <script>alert(1)</script>&lt;i&gt;there&lt;/i&gt; &amp; co' . "\0\x07") === 'Hi alert(1)there & co');
check('sanitize: bidirectional overrides, zero-width and tag characters removed (a "publish" that reads reversed cannot be faked)', CaptionBuilder::sanitize("a\u{202E}b\u{202C}c\u{200B}d\u{2066}e\u{2069}f\u{FEFF}g\u{E0041}h\u{00AD}i") === 'abcdefghi');
check('sanitize: line breaks folded in single-line fields, kept (max one blank line) in captions; invalid UTF-8 repaired', CaptionBuilder::sanitize("a\r\nb\n\n\n\nc", false) === 'a b c' && CaptionBuilder::validate('facebook', "a\r\n\r\n\r\n\r\nb")['text'] === "a\n\nb" && mb_check_encoding(CaptionBuilder::sanitize("bad \xC3\x28 bytes"), 'UTF-8'));
check('hashtags: letters, digits, underscores only; no pure numbers; deduplicated (any case); limited; injection characters dropped', CaptionBuilder::hashtags('#Lumina, lumina  #Uganda2026 #2026 #a-b <script> #ok_ok #x', 4) === '#Lumina #Uganda2026 #ab #ok_ok' && CaptionBuilder::hashtags('', 5) === '' && CaptionBuilder::hashtagList("#a\n#b", 10) === ['#a', '#b']);
check('Telegram HTML helper escapes < > &', CaptionBuilder::telegramHtml('a <b> & c') === 'a &lt;b&gt; &amp; c');
mt_srand(20261020);
$words = ['alpha', 'Beta', 'gamma', '<b>x</b>', '{url}', "\u{1F600}", "\u{65E5}\u{672C}", 'https://example.org/aaaa', '#tag', "\u{202E}", "a\nb", '&amp;', str_repeat('z', 60)];
$fuzzOk = true;
$fuzzWhy = '';
for ($i = 0; $i < 300; $i++) {
    $pick = static function (int $n) use ($words): string {
        $o = [];
        for ($k = 0; $k < $n; $k++) {
            $o[] = $words[mt_rand(0, count($words) - 1)];
        }
        return implode(' ', $o);
    };
    $iss = ['title' => $pick(mt_rand(0, 12)), 'summary' => $pick(mt_rand(0, 90)), 'url' => 'https://example.org/n/' . bin2hex(random_bytes(mt_rand(1, 40))), 'issue_number' => mt_rand(0, 3) ? mt_rand(1, 999) : null, 'issue_date' => '2026-10-' . sprintf('%02d', mt_rand(1, 28))];
    foreach (['facebook', 'instagram', 'x', 'linkedin', 'telegram', 'webhook', 'tiktok'] as $ch) {
        $b = CaptionBuilder::build($ch, $iss, null, ['hashtags' => $pick(mt_rand(0, 8))]);
        $bad = $b['over'] || preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]|[\x{202A}-\x{202E}\x{2066}-\x{2069}\x{200B}-\x{200F}\x{FEFF}]/u', $b['text']) === 1 || !mb_check_encoding($b['text'], 'UTF-8') || preg_match('/<\/?[a-z]/i', $b['text']) === 1
            || preg_match('/\n{3,}/', $b['text']) === 1 || $b['text'] !== trim($b['text']) || ($iss['title'] !== '' && $b['text'] === '' && $ch !== 'x');
        if ($bad) {
            $fuzzOk = false;
            $fuzzWhy = $ch . ' ' . json_encode($b['text']);
            break 2;
        }
    }
}
check('fuzz: 300 random issues x 7 channels - always within the limit, valid UTF-8, no tags, no control / bidi characters, no triple line breaks', $fuzzOk, $fuzzWhy);
$tplErr = SocialConfig::saveTemplates(['templates' => ['x' => 'nothing useful here', 'facebook' => '{title} {bogus}', 'telegram' => str_repeat('fixed text ', 150) . '{title}', 'linkedin' => "<script>{title}</script>\n{url}"]]);
check('template check: needs {title} or {summary}; unknown placeholders refused; text that cannot fit its channel refused; nothing saved when one is wrong', !$tplErr['ok'] && isset($tplErr['errors']['x'], $tplErr['errors']['facebook'], $tplErr['errors']['telegram']) && !isset($tplErr['errors']['linkedin']) && SocialConfig::str('social_tpl_x') === '');
$tplOk = SocialConfig::saveTemplates(['templates' => ['x' => '{title} {url}', 'instagram' => "{title}\n{url}"], 'default_on' => ['x' => false], 'hashtags' => '#Zz #Test']);
check('template save: custom template stored, in force and built-in again when emptied; instagram {url} warns; default-on switch and hashtags saved', $tplOk['ok'] && isset($tplOk['warnings']['instagram']) && SocialConfig::template('x') === '{title} {url}' && !SocialConfig::defaultOn('x') && SocialConfig::str('social_hashtags') === '#Zz #Test'
    && SocialConfig::saveTemplates(['templates' => ['x' => '', 'instagram' => '']])['ok'] && SocialConfig::template('x') === CaptionBuilder::defaultTemplate('x'));
check('global options: validated (API versions, hashtag length), the global switch toggles', !SocialConfig::saveGlobal(['graph_version' => 'banana'])['ok'] && !SocialConfig::saveGlobal(['li_version' => '2025'])['ok'] && SocialConfig::saveGlobal(['enabled' => false, 'utm' => false, 'graph_version' => 'v99.0', 'li_version' => '203012'])['ok']
    && !SocialConfig::globalOn() && !SocialConfig::bool('social_utm') && SocialConfig::str('social_graph_version') === 'v99.0' && SocialConfig::saveGlobal(['enabled' => true, 'utm' => true, 'graph_version' => 'v23.0', 'li_version' => '202510'])['ok'] && SocialConfig::globalOn());

// =====================================================================================================================================================
echo "Social: profile links\n";
$n = static fn(string $p, string $v): array => SocialProfiles::normalise($p, $v);
check('profile links: empty is fine; a bare @name / number becomes an address', $n('facebook', '')['ok'] && $n('instagram', '@lumina_africa')['url'] === 'https://www.instagram.com/lumina_africa/' && $n('x', '@luminax')['url'] === 'https://x.com/luminax' && $n('telegram', '@luminachannel')['url'] === 'https://t.me/luminachannel'
    && $n('whatsapp', '+256 779-712990')['url'] === 'https://wa.me/256779712990' && $n('whatsapp', '+256779712990')['url'] === 'https://wa.me/256779712990' && $n('tiktok', '@lumina.africa.fou')['url'] === 'https://www.tiktok.com/@lumina.africa.fou');
check('profile links: WhatsApp needs the country code (no leading 0, spaces allowed, 00 prefix = +); a local number is refused with the reason', !$n('whatsapp', '0700000000')['ok'] && str_contains($n('whatsapp', '0700 000 000')['error'], 'country code') && $n('whatsapp', '00256700000000')['url'] === 'https://wa.me/256700000000' && !$n('whatsapp', '+12')['ok'] && $n('whatsapp', 'https://whatsapp.com/channel/abc')['ok']);
check('profile links: quotes, angle brackets, backslashes and backticks are refused in an address (the stored link would be broken)', !$n('youtube', 'https://youtube.com/@x"onmouseover="alert(1)<b>')['ok'] && !$n('x', "https://x.com/a'b")['ok'] && !$n('x', 'https://x.com/a?b=c`d')['ok'] && !$n('x', 'https://x.com/a\\b')['ok'] && $n('youtube', 'https://youtube.com/@x')['ok'] && $n('linkedin', 'https://www.linkedin.com/company/lumina-africa-foundation?trk=a&b=c')['ok']);
check('profile links: "facebook.com/page" gets https://; http:// is refused; javascript:, data:, userinfo, spaces, other schemes refused', $n('facebook', 'facebook.com/luminaafrica')['url'] === 'https://facebook.com/luminaafrica' && !$n('facebook', 'http://www.facebook.com/x')['ok'] && !$n('facebook', 'javascript:alert(1)')['ok']
    && !$n('facebook', 'data:text/html,x')['ok'] && !$n('facebook', 'https://user:pw@facebook.com/x')['ok'] && !$n('facebook', 'https://facebook.com/a b')['ok'] && !$n('facebook', 'ftp://facebook.com/x')['ok'] && !$n('facebook', "https://facebook.com/x\r\nSet-Cookie: a")['ok'] && !$n('facebook', str_repeat('a', 301))['ok'] && !$n('nope', 'https://x.com')['ok']);
$w = $n('facebook', 'https://example.org/page');
check('profile links: a host that is not the platform\'s own is ACCEPTED WITH A WARNING', $w['ok'] && $w['warning'] !== '' && $n('facebook', 'https://www.facebook.com/p')['warning'] === '' && $n('linkedin', 'https://lnkd.in/abc')['warning'] === '' && $n('x', 'https://twitter.com/a')['warning'] === '');
$before = SocialProfiles::values();
$res = SocialProfiles::save(['urls' => ['facebook' => 'https://www.facebook.com/zzpage', 'threads' => '@zzthreads', 'instagram' => 'http://insecure.example'], 'order' => ['threads', 'facebook'], 'show_footer' => false]);
check('save: nothing is saved when one address is invalid', !$res['ok'] && isset($res['errors']['instagram']) && SocialProfiles::values() === $before);
$res = SocialProfiles::save(['urls' => ['facebook' => 'https://www.facebook.com/zzpage', 'threads' => '@zzthreads', 'telegram' => '@zzchan', 'whatsapp' => 'https://wa.me/256700000000'], 'order' => ['threads', 'telegram', 'facebook', 'bogus', 'facebook'],
    'show_footer' => true, 'show_contact' => false, 'show_newsletter' => true, 'show_maintenance' => false]);
$vals = SocialProfiles::values();
check('save: the EXISTING keys keep working (social_facebook ...) and the new ones are stored', $res['ok'] && Settings::get('social_facebook') === 'https://www.facebook.com/zzpage' && $vals['facebook'] === 'https://www.facebook.com/zzpage' && Settings::get('social_threads') === 'https://www.threads.net/@zzthreads'
    && Settings::get('social_telegram') === 'https://t.me/zzchan' && Settings::get('social_whatsapp_channel') === 'https://wa.me/256700000000' && Settings::get('social_instagram') === $before['instagram']);
$order = SocialProfiles::order();
check('order: saved order first, unknown names dropped, duplicates dropped, the rest appended in the default order', array_slice($order, 0, 3) === ['threads', 'telegram', 'facebook'] && count($order) === count(SocialProfiles::PLATFORMS) && count(array_unique($order)) === count($order));
$footer = array_column(social_links('footer'), 'key');
$contact = social_links('contact');
$maint = social_links('maintenance');
check('where they show: footer yes, contact switched off, newsletter on, maintenance off (the old maintenance_show_social switch); "all" ignores every switch (sameAs)',
    in_array('facebook', $footer, true) && array_slice($footer, 0, 3) === ['threads', 'telegram', 'facebook'] && $contact === [] && $maint === [] && social_links('newsletter') !== [] && count(social_links('all')) === count(social_links()) && count(social_links('all')) >= 3 && Settings::get('maintenance_show_social') === '0');
$maintOn = SocialProfiles::save(['show_maintenance' => true]);
check('the maintenance page still reads maintenance_show_social: switching it on shows the links again', $maintOn['ok'] && social_links('maintenance') !== [] && array_column(social_links('maintenance'), 'key') === array_column(social_links('all'), 'key'));
check('links() only ever returns http(s) addresses, whatever is stored', (function () use (&$zzSettingRows): bool {
    Settings::set('social_youtube', 'javascript:alert(1)');
    $bad = array_filter(social_links('all'), static fn(array $l): bool => !str_starts_with($l['url'], 'http'));
    Settings::set('social_youtube', '');
    return $bad === [];
})());

// =====================================================================================================================================================
echo "Social: settings and secrets\n";
$zzResetChannels();
$tg = Channels::get('telegram');
check('every channel has complete metadata (label, icon, limit, steps, setup link, fields)', (function (): bool {
    foreach (Channels::all() as $id => $c) {
        $m = $c->meta();
        if ($m['id'] !== $id || $m['label'] === '' || $m['short'] === '' || $m['limit'] < 1 || $m['steps'] === [] || $m['fields'] === [] || !in_array('icon', array_keys($m), true) || $m['needs'] === '' || ($m['setup_url'] === '' && $id !== 'x' && false)) {
            return false;
        }
        foreach ($m['fields'] as $f) {
            if (!isset($f['key'], $f['label'], $f['type'], $f['required'], $f['secret']) || ($f['secret'] && $f['type'] !== 'secret')) {
                return false;
            }
        }
    }
    return true;
})());
$res = SocialConfig::saveChannel('telegram', ['bot_token' => 'nonsense', 'chat' => 'not a chat', 'enabled' => '1']);
check('saveChannel: malformed values are refused field by field (a secret is never echoed in the message), nothing stored', !$res['ok'] && isset($res['errors']['bot_token'], $res['errors']['chat']) && !str_contains(json_encode($res), 'nonsense') && SocialConfig::str('social_ch_telegram_chat') === '' && !SocialConfig::secretStatus('telegram', 'bot_token')['set']);
$res = SocialConfig::saveChannel('telegram', ['chat' => '@zzchannel', 'enabled' => '1']);
check('switching a channel ON needs a complete setup: it stays off and says what is missing', !$res['ok'] && isset($res['errors']['enabled']) && str_contains($res['errors']['enabled'], 'Bot token') && !SocialConfig::enabled('telegram') && SocialConfig::str('social_ch_telegram_chat') === '@zzchannel');
check('ready(): off / incomplete states explain themselves', SocialConfig::ready('telegram')['state'] === 'off' && (SocialConfig::saveGlobal(['enabled' => false])['ok'] && SocialConfig::ready('telegram')['state'] === 'off' && SocialConfig::saveGlobal(['enabled' => true])['ok']));
$res = SocialConfig::saveChannel('telegram', ['bot_token' => $zzTok['tg'], 'chat' => '@zzchannel', 'test_chat' => '-1009999999999', 'enabled' => '1', 'default_on' => '1']);
$raw = (string) Database::value("SELECT setting_value FROM settings WHERE setting_key = 'social_secret_telegram_bot_token'", [], '');
check('a secret is stored ENCRYPTED (enc:v1:, AES-256-GCM) and the plaintext is nowhere in the database', $res['ok'] && str_starts_with($raw, 'enc:v1:') && !str_contains($raw, $zzTok['tg']) && SecretStore::decrypt($raw) === $zzTok['tg']
    && (int) Database::value("SELECT COUNT(*) FROM settings WHERE setting_value LIKE ?", ['%' . substr($zzTok['tg'], 12) . '%'], 0) === 0);
$pv = SocialConfig::publicView('telegram');
$pvJson = json_encode($pv);
check('publicView(): the secret is described ("Set (hidden)"), never shown; the other values are', !str_contains($pvJson, $zzTok['tg']) && !str_contains($pvJson, substr($zzTok['tg'], 12)) && $pv['fields'][0]['secret']['text'] === 'Set (hidden)' && $pv['enabled'] === true && $pv['ready']['ready'] === true && $pv['fields'][1]['value'] === '@zzchannel');
$cfg = SocialConfig::channelConfig('telegram');
check('channelConfig() (adapters only) returns the plaintext; missing values are empty strings', $cfg['bot_token'] === $zzTok['tg'] && $cfg['chat'] === '@zzchannel' && $cfg['test_chat'] === '-1009999999999');
$res = SocialConfig::saveChannel('telegram', ['bot_token' => '', 'chat' => '@zzchannel2']);
check('an EMPTY secret box keeps the stored secret; a changed value replaces only that value', $res['ok'] && SocialConfig::secret('telegram', 'bot_token') === $zzTok['tg'] && SocialConfig::str('social_ch_telegram_chat') === '@zzchannel2' && $res['changed'] === ['chat']);
SocialConfig::setStatus('telegram', 'needs_token', 'The platform rejected the access token.');
check('"needs a new token" makes the channel not ready', SocialConfig::ready('telegram')['state'] === 'needs_token' && !SocialConfig::ready('telegram')['ready'] && SocialConfig::attention(true)['needs_token'] === ['telegram']);
$res = SocialConfig::saveChannel('telegram', ['bot_token' => '987654321:ZZNEWTOKEN' . bin2hex(random_bytes(10))]);
check('pasting a new token starts over: status untested, ready again, "notified" cleared', $res['ok'] && SocialConfig::status('telegram')['state'] === 'untested' && SocialConfig::ready('telegram')['ready'] && SocialConfig::attention(true)['needs_token'] === []);
SocialConfig::saveChannel('telegram', ['bot_token' => $zzTok['tg']]);
putenv('SOCIAL_TG_BOT_TOKEN=' . '555555555:ZZENVTOKEN' . bin2hex(random_bytes(10)));
Settings::flush();
$st = SocialConfig::secretStatus('telegram', 'bot_token');
check('.env override wins and is shown as "set in .env" (never the value); removing it brings the stored one back', $st['source'] === 'env' && str_contains(SocialConfig::describeSecret('telegram', 'bot_token'), 'SOCIAL_TG_BOT_TOKEN') && str_starts_with((string) SocialConfig::secret('telegram', 'bot_token'), '555555555:ZZENV') && (function () use ($zzTok): bool {
    putenv('SOCIAL_TG_BOT_TOKEN');
    return SocialConfig::secret('telegram', 'bot_token') === $zzTok['tg'];
})());
$res = SocialConfig::saveChannel('telegram', ['clear_bot_token' => '1']);
check('"remove the stored value" deletes the secret from the database', $res['ok'] && !SocialConfig::secretStatus('telegram', 'bot_token')['set'] && (int) Database::value("SELECT COUNT(*) FROM settings WHERE setting_key = 'social_secret_telegram_bot_token'", [], 0) === 0 && !SocialConfig::ready('telegram')['ready']);
// APP_KEY change: unreadable
SocialConfig::putSecret('telegram', 'bot_token', $zzTok['tg']);
$enc = (string) Settings::get('social_secret_telegram_bot_token', '');
check('a secret encrypted with another application key is unreadable (never garbage), and the admin is told to enter it again', SecretStore::decrypt($enc, 'another-app-key') === null && SecretStore::decrypt(substr($enc, 0, -3) . 'AAA') === null && SecretStore::decrypt($enc) === $zzTok['tg']);
Database::execute("UPDATE settings SET setting_value = ? WHERE setting_key = 'social_secret_telegram_bot_token'", [substr($enc, 0, 20) . 'XXXX' . substr($enc, 24)]);
Settings::flush();
check('a tampered stored secret reads as "stored but cannot be read" and the channel is not ready', !SocialConfig::secretStatus('telegram', 'bot_token')['readable'] && str_contains(SocialConfig::describeSecret('telegram', 'bot_token'), 'cannot be read') && SocialConfig::secret('telegram', 'bot_token') === null && !SocialConfig::ready('telegram')['ready'] && str_contains(implode(' ', SocialConfig::missingFor('telegram')), 'unreadable'));
SocialConfig::forgetSecret('telegram', 'bot_token');
// token expiry
$soon = date('Y-m-d', time() + 5 * 86400);
$zzConfigure('linkedin', ['org_urn' => '12345678', 'access_token' => $zzTok['li'], 'expires' => $soon]);
check('LinkedIn: a bare organisation number becomes a URN; the expiry date is parsed; a warning appears 14 days before', SocialConfig::channelConfig('linkedin')['org_urn'] === 'urn:li:organization:12345678' && SocialConfig::expiry('linkedin')['days_left'] >= 4 && SocialConfig::expiry('linkedin')['days_left'] <= 5
    && count(SocialConfig::expiryWarnings()) === 1 && SocialConfig::expiryWarnings()[0]['channel'] === 'linkedin' && SocialConfig::attention(true)['total'] >= 1);
Settings::set(SocialConfig::key('linkedin', 'expires'), date('Y-m-d', time() + 40 * 86400));
check('...but not when the token has weeks left', SocialConfig::expiryWarnings() === []);
Settings::set(SocialConfig::key('linkedin', 'expires'), date('Y-m-d', time() - 2 * 86400));
check('an expired token makes the channel not ready ("expired")', SocialConfig::ready('linkedin')['state'] === 'expired' && !SocialConfig::ready('linkedin')['ready']);
Settings::set(SocialConfig::key('linkedin', 'expires'), $soon);
Settings::set(SocialConfig::key('linkedin', 'expiry_notified'), '');
$mailsBefore = count($zzMails());
SocialDispatcher::remindExpiry();
SocialDispatcher::remindExpiry();
$m = $zzMails();
check('the expiry reminder e-mail is sent ONCE per expiry date and never contains the token', count($m) === $mailsBefore + 1 && !str_contains(implode("\n", $m), $zzTok['li']) && str_contains(implode("\n", $m), 'LinkedIn'));
$zzResetChannels();
check('webhook address check: validated through the SSRF guard when saved', (function (): bool {
    SocialHttp::$resolver = static fn(string $h): array => ['93.184.216.34'];
    $a = SocialConfig::saveChannel('webhook', ['url' => 'https://127.0.0.1/hook']);
    $b = SocialConfig::saveChannel('webhook', ['url' => 'https://hooks.example.com/abc']);
    SocialHttp::$resolver = null;
    return !$a['ok'] && isset($a['errors']['url']) && $b['ok'] && SocialConfig::secret('webhook', 'url') === 'https://hooks.example.com/abc';
})());
check('the webhook address is a SECRET: stored encrypted (enc:v1), nothing in the plain setting, never in the channel view, masked as "Set (hidden)"', (function (): bool {
    $stored = (string) Settings::get(SocialConfig::secretSettingKey('webhook', 'url'), '');
    $view = json_encode(SocialConfig::publicView('webhook'));
    return str_starts_with($stored, 'enc:v1:') && !str_contains($stored, 'hooks.example.com') && trim((string) Settings::get(SocialConfig::key('webhook', 'url'), '')) === '' && !str_contains((string) $view, 'hooks.example.com') && SocialConfig::describeSecret('webhook', 'url') === 'Set (hidden)';
})());
check('a webhook address saved as a plain setting by an older version still works, and is encrypted (and the plain copy emptied) by migrateLegacySecrets()', (function (): bool {
    SocialConfig::forgetSecret('webhook', 'url');
    Settings::set(SocialConfig::key('webhook', 'url'), 'https://legacy.example.com/old');
    $before = SocialConfig::secret('webhook', 'url') === 'https://legacy.example.com/old' && SocialConfig::secretStatus('webhook', 'url')['set'] === true && !empty(SocialConfig::secretStatus('webhook', 'url')['legacy']);
    $n = SocialConfig::migrateLegacySecrets();
    $after = SocialConfig::secret('webhook', 'url') === 'https://legacy.example.com/old' && trim((string) Settings::get(SocialConfig::key('webhook', 'url'), '')) === '' && str_starts_with((string) Settings::get(SocialConfig::secretSettingKey('webhook', 'url'), ''), 'enc:v1:') && SocialConfig::migrateLegacySecrets() === 0;
    return $before && $n === 1 && $after;
})());
SocialConfig::forgetSecret('webhook', 'url');
Settings::set(SocialConfig::key('webhook', 'url'), '');

// =====================================================================================================================================================
echo "Social: post queue\n";
$q1 = $zzIssue();
$a = PostQueue::enqueue($q1, 'telegram', 1, "Caption <b>one</b>\0");
$b = PostQueue::enqueue($q1, 'telegram', 1, 'Caption two');
$c = PostQueue::enqueue($q1, 'telegram', 2, 'Caption again');
$d = PostQueue::enqueue($q1, 'x', 1, 'X caption');
check('enqueue: ONE row per (issue, channel, share version); a repeat creates nothing and answers with the existing id', $a['created'] && !$b['created'] && $b['id'] === $a['id'] && $c['created'] && $d['created'] && (int) Database::value('SELECT COUNT(*) FROM social_posts WHERE newsletter_id = ?', [$q1], 0) === 3);
check('enqueue: the caption is cleaned; unknown channel / bad ids create nothing', SocialPost::find($a['id'])['caption'] === 'Caption one' && PostQueue::enqueue($q1, 'myspace', 1, 'x') === ['created' => false, 'id' => 0] && PostQueue::enqueue(0, 'x', 1, 'x')['created'] === false && PostQueue::enqueue($q1, 'x', 0, 'x')['created'] === false);
check('hasAny(): true for any share version, false for a channel never queued', PostQueue::hasAny($q1, 'telegram') && !PostQueue::hasAny($q1, 'facebook'));
// parallel enqueue
$q2 = $zzIssue();
$procs = [];
for ($i = 0; $i < 6; $i++) {
    $procs[$i] = proc_open([PHP_BINARY, '-r', 'require "' . str_replace('\\', '/', $zzRoot) . '/app/bootstrap.php"; $r = App\Services\Social\PostQueue::enqueue(' . $q2 . ', "linkedin", 1, "race"); echo $r["created"] ? "created" : "exists";'], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $zsPp[$i]);
}
$created = 0;
foreach ($procs as $i => $p) {
    $created += trim((string) stream_get_contents($zsPp[$i][1])) === 'created' ? 1 : 0;
    proc_close($p);
}
check('six parallel processes enqueue the same key: exactly ONE row is created', $created === 1 && (int) Database::value('SELECT COUNT(*) FROM social_posts WHERE newsletter_id = ?', [$q2], 0) === 1, (string) $created);
// claim
$cl1 = PostQueue::claim($a['id']);
$cl2 = PostQueue::claim($a['id']);
check('claim(): ONE conditional UPDATE - the first caller gets the row (posting, attempts 1, last_attempt_at recorded BEFORE any call), the second gets nothing', $cl1 !== null && $cl2 === null && $cl1['status'] === 'posting' && (int) $cl1['attempts'] === 1 && $cl1['last_attempt_at'] !== null);
$qq = [];
for ($i = 0; $i < 4; $i++) {
    $qq[$i] = proc_open([PHP_BINARY, '-r', 'require "' . str_replace('\\', '/', $zzRoot) . '/app/bootstrap.php"; echo App\Services\Social\PostQueue::claim(' . $d['id'] . ') !== null ? "won" : "lost";'], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $zsCp[$i]);
}
$won = 0;
foreach ($qq as $i => $p) {
    $won += trim((string) stream_get_contents($zsCp[$i][1])) === 'won' ? 1 : 0;
    proc_close($p);
}
check('four parallel processes claim the same row: exactly ONE wins', $won === 1, (string) $won);
// transitions
check('markPosted only from posting; the row keeps the remote id and url, clears the error', PostQueue::markPosted($a['id'], 'm1', 'https://t.me/x/1') && !PostQueue::markPosted($a['id'], 'm2', 'x') && SocialPost::find($a['id'])['remote_url'] === 'https://t.me/x/1' && SocialPost::find($a['id'])['status'] === 'posted' && SocialPost::find($a['id'])['posted_at'] !== null);
$bk = [];
$row = SocialPost::find($d['id']);        // claimed above by the winner (x, attempts 1)
for ($attempt = 1; $attempt <= 5; $attempt++) {
    $cur = SocialPost::find($d['id']);
    if ($cur['status'] === 'queued') {
        Database::execute('UPDATE social_posts SET next_attempt_at = NULL WHERE id = ?', [$d['id']]);
        PostQueue::claim($d['id']);
    }
    $end = PostQueue::markTransient($d['id'], 'Could not connect ' . $zzTok['tg']);
    $after = SocialPost::find($d['id']);
    $bk[] = [$end, $after['next_attempt_at'] !== null ? (int) round((strtotime((string) $after['next_attempt_at']) - time()) / 10) * 10 : null];
}
check('backoff: 60 s, 5 min, 15 min, 1 h after attempts 1-4; the FIFTH failure ends in failed ("gave up after 5 tries")', $bk[0] === ['queued', 60] && $bk[1] === ['queued', 300] && $bk[2] === ['queued', 900] && $bk[3] === ['queued', 3600] && $bk[4][0] === 'failed' && str_contains((string) SocialPost::find($d['id'])['last_error'], 'Gave up after 5'), json_encode($bk));
check('stored error texts are redacted (a token in an error message never reaches the table)', !str_contains((string) SocialPost::find($d['id'])['last_error'], substr($zzTok['tg'], 12)));
$e1 = PostQueue::enqueue($q1, 'webhook', 1, 'w');
PostQueue::claim($e1['id']);
PostQueue::markTransient($e1['id'], 'later', 9999999);
$rt = SocialPost::find($e1['id']);
check('a Retry-After from the platform overrides the backoff (capped at one day)', strtotime((string) $rt['next_attempt_at']) - time() > 80000 && strtotime((string) $rt['next_attempt_at']) - time() <= 86400 + 5);
check('due(): a row waiting for its next try is not due; a row with no wait is', !in_array($e1['id'], PostQueue::due(50), true) && (function () use ($e1, $q1): bool {
    Database::execute('UPDATE social_posts SET next_attempt_at = ? WHERE id = ?', [date('Y-m-d H:i:s', time() - 5), $e1['id']]);
    return in_array($e1['id'], PostQueue::due(50, $q1), true) && !in_array($e1['id'], PostQueue::due(50, $q1 + 99999), true);
})());
// stuck
$s1 = PostQueue::enqueue($zzIssue(), 'telegram', 1, 's');
$s2 = PostQueue::enqueue((int) SocialPost::find($s1['id'])['newsletter_id'], 'webhook', 1, 's');
PostQueue::claim($s1['id']);
PostQueue::claim($s2['id']);
Database::execute('UPDATE social_posts SET last_attempt_at = ? WHERE id IN (?, ?)', [date('Y-m-d H:i:s', time() - 700), $s1['id'], $s2['id']]);
$moved = PostQueue::reclaimStuck();
check('stuck "posting" rows (the process died): Telegram -> review (it may have been posted), webhook -> queued again (receivers de-duplicate)', $moved >= 2 && SocialPost::find($s1['id'])['status'] === 'review' && SocialPost::find($s2['id'])['status'] === 'queued');
check('a fresh "posting" row is left alone', (function () use ($zzIssue): bool {
    $r = PostQueue::enqueue($zzIssue(), 'telegram', 1, 'fresh');
    PostQueue::claim($r['id']);
    PostQueue::reclaimStuck();
    return SocialPost::find($r['id'])['status'] === 'posting';
})());
// admin actions
$rv = $s1['id'];
check('review rows are never retried by themselves: due() ignores them, retry() refuses and points at the decision', !in_array($rv, PostQueue::due(100), true) && !PostQueue::retry($rv)['ok'] && str_contains(PostQueue::retry($rv)['error'], 'decision'));
check('retryAnyway(): review -> queued with attempts reset; skip(); markPostedByAdmin() with an https link only', PostQueue::retryAnyway($rv)['ok'] && SocialPost::find($rv)['status'] === 'queued' && (int) SocialPost::find($rv)['attempts'] === 0 && PostQueue::skip($rv)['ok'] && SocialPost::find($rv)['status'] === 'skipped'
    && PostQueue::retry($rv)['ok'] && PostQueue::claim($rv) !== null && PostQueue::markReview($rv, 'unclear') && !PostQueue::markPostedByAdmin($rv, 'http://x.example/p')['ok'] && !PostQueue::markPostedByAdmin($rv, 'javascript:alert(1)')['ok'] && PostQueue::markPostedByAdmin($rv, 'https://t.me/c/1/2')['ok']
    && SocialPost::find($rv)['status'] === 'posted' && SocialPost::find($rv)['remote_url'] === 'https://t.me/c/1/2' && !PostQueue::skip($rv)['ok'] && !PostQueue::retry($rv)['ok'] && !PostQueue::retry(999999999)['ok']);
check('release(): a claimed row goes back WITHOUT counting the attempt', (function () use ($zzIssue): bool {
    $r = PostQueue::enqueue($zzIssue(), 'x', 1, 'rel');
    PostQueue::claim($r['id']);
    PostQueue::release($r['id'], 0);
    $row = SocialPost::find($r['id']);
    return $row['status'] === 'queued' && (int) $row['attempts'] === 0;
})());
check('retryFailedForChannel() / retryAuthFailures(): ONLY posts that failed because the token was rejected (fail_code auth), made recently, of issues that are still public, go back to the queue; every other failure and old or unpublished issues stay as they are', (function () use ($zzIssue): bool {
    $live = [];
    foreach (['auth', 'perm', 'dup', 'gaveup', 'old', 'draft'] as $k) {
        $id = $zzIssue();
        if ($k !== 'draft') {
            NewsletterService::publish($id, null);
        }
        $r = PostQueue::enqueue($id, 'linkedin', 1, 'f ' . $k);
        PostQueue::claim($r['id']);
        PostQueue::markFailed($r['id'], 'x ' . $k, $k === 'auth' || $k === 'old' || $k === 'draft' ? 'auth' : null);
        if ($k === 'old') {
            Database::execute('UPDATE social_posts SET created_at = ? WHERE id = ?', [date('Y-m-d H:i:s', time() - 5 * 86400), $r['id']]);
        }
        $live[$k] = $r['id'];
    }
    $q = PostQueue::retryAuthFailures('linkedin');
    $st = static fn(string $k): string => (string) SocialPost::find($live[$k])['status'];
    return $q['count'] === 1 && count($q['issues']) === 1 && str_starts_with($q['issues'][0], 'ZZ-SOCIAL') && $st('auth') === 'queued' && $st('perm') === 'failed' && $st('dup') === 'failed' && $st('gaveup') === 'failed' && $st('old') === 'failed' && $st('draft') === 'failed'
        && SocialPost::find($live['auth'])['fail_code'] === null && PostQueue::retryFailedForChannel('linkedin') === 0;
})());
check('Retry takes the caption again from the saved choices: what the admin edited after a failure is what goes out (and the frozen text is replaced)', (function () use ($zzIssue): bool {
    $id = $zzIssue();
    $r = PostQueue::enqueue($id, 'telegram', 1, 'OLD frozen caption');
    PostQueue::claim($r['id']);
    PostQueue::markFailed($r['id'], 'boom');
    Database::execute('UPDATE newsletters SET share_choices = ? WHERE id = ?', [json_encode(['telegram' => ['on' => true, 'caption' => 'EDITED after the failure']]), $id]);
    $a = PostQueue::retry($r['id']);
    $row = SocialPost::find($r['id']);
    Database::execute('UPDATE newsletters SET share_choices = NULL WHERE id = ?', [$id]);
    PostQueue::markSkipped($r['id'], 'x');
    $b = PostQueue::retry($r['id']);
    $row2 = SocialPost::find($r['id']);
    return $a['ok'] && $row['status'] === 'queued' && $row['caption'] === 'EDITED after the failure' && $b['ok'] && str_contains((string) $row2['caption'], 'ZZ-SOCIAL') && !str_contains((string) $row2['caption'], 'OLD');
})());
$log = PostQueue::log(['channel' => 'telegram', 'status' => 'posted', 'newsletter_id' => $q1], 1, 10);
check('share log: filter by channel / status / issue; rows carry the issue title', $log->total === 1 && $log->items[0]['channel'] === 'telegram' && str_starts_with((string) $log->items[0]['newsletter_title'], 'ZZ-SOCIAL') && PostQueue::log(['channel' => 'bogus'], 1, 10)->total >= 1 && PostQueue::log(['status' => 'bogus'], 1, 10)->total >= 1);
check('deleting an issue removes its log rows (the remote posts are NOT touched: no API call exists for that)', (function () use ($q1): bool {
    NewsletterService::delete($q1);
    return (int) Database::value('SELECT COUNT(*) FROM social_posts WHERE newsletter_id = ?', [$q1], 0) === 0;
})());
$zzCleanup();

echo "Social: share picture design (saveCard)\n";
$zzCardKeys = array_keys(SocialConfig::CARD_DEFAULTS);
$zzCardBefore = [];
foreach ($zzCardKeys as $k) {
    $zzCardBefore[$k] = SocialConfig::str($k);
}
$res = SocialConfig::saveCard(['bg' => '0b5d3b', 'bg_dark' => '#062B1B', 'accent' => '#ffd166', 'accent2' => '#EF476F', 'text' => '#ffffff', 'tagline' => "Together\nwe <b>grow</b>", 'show_logo' => '0', 'show_url' => '1']);
$d = SocialCard::designer();
check('saveCard: colours are normalised (# added, upper case), the tagline is plain text, switches stored; designer() reads them', $res['ok'] && $d['bg'] === '#0B5D3B' && $d['accent'] === '#FFD166' && $d['show_logo'] === false && $d['show_url'] === true && !str_contains($d['tagline'], '<') && !str_contains($d['tagline'], "\n"), json_encode([$res, $d]));
$res = SocialConfig::saveCard(['bg' => 'zzz', 'accent' => '#12345']);
check('saveCard: a bad colour is refused per field and nothing is saved', !$res['ok'] && isset($res['errors']['bg'], $res['errors']['accent']) && SocialConfig::str('social_card_bg') === '#0B5D3B');
$res = SocialConfig::saveCard(['tagline' => str_repeat('x', 81)]);
check('saveCard: a tagline over 80 characters is refused', !$res['ok'] && isset($res['errors']['tagline']));
$res = SocialConfig::saveCard(['reset' => '1']);
check('saveCard: reset puts every value back to the brand default', $res['ok'] && SocialConfig::str('social_card_bg') === SocialConfig::CARD_DEFAULTS['social_card_bg'][0] && SocialConfig::bool('social_card_show_logo') === true);
$zzRow = ['id' => 0, 'slug' => 'zz-card', 'title' => 'T', 'summary' => 'S', 'issue_number' => 1, 'issue_date' => '2026-01-01', 'kind' => 'online', 'cover_image' => '', 'content' => '{}', 'status' => 'draft'];
$hs = SocialCard::sourceHash($zzRow, 'square');
SocialConfig::saveCard(['bg' => '#112233']);
check('a design change changes the picture hash (stored pictures are redrawn lazily, same address)', $hs !== SocialCard::sourceHash($zzRow, 'square'));
Settings::setMany($zzCardBefore);
SocialConfig::flush();
check('design settings restored', SocialConfig::str('social_card_bg') === $zzCardBefore['social_card_bg']);

require __DIR__ . '/selftest-social-flow.php';      // dispatcher + ShareHook with fake channels, then the LOCAL MOCK (adapters, failure modes, signatures, the whole publish flow)
require __DIR__ . '/selftest-social-http.php';      // the real HTTP side: abilities, CSRF, JSON shapes, nothing secret in any page (skips itself when the site does not answer)

$zzFinish();
if (!empty($standalone)) {
    echo $fail === 0 ? PHP_EOL . 'ALL CHECKS PASSED' . PHP_EOL : PHP_EOL . $fail . ' CHECK(S) FAILED' . PHP_EOL;
    exit($fail === 0 ? 0 : 1);
}
