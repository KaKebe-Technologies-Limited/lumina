<?php
/**
 * Part 3 of bin/selftest-social.php: the REAL HTTP side against http://localhost/<folder>/ (skips itself when the site does not answer). Included by bin/selftest-social.php:
 * it shares its variables ($zzIssue, $zzTok, $zzConfigure, $zzRoot, check() ...). Throw-away users and roles ("zz-social-*@example.test") get random passwords that are never printed
 * and are deleted again. NO real platform is contacted: the only channel actions called over HTTP are those that answer before any network call (unconfigured channel, missing confirmation).
 */
declare(strict_types=1);

use App\Core\Auth;
use App\Core\Database;
use App\Core\Roles;
use App\Models\SocialPost;
use App\Models\User;
use App\Services\Social\Channels;
use App\Services\Social\PostQueue;
use App\Services\Social\SocialConfig;

echo "Social: HTTP (abilities, CSRF, JSON shapes, no secret in any page)\n";
$zsBase = 'http://localhost/' . basename($zzRoot) . '/';
$zsJars = [];
/** @return array{code:int,headers:array<string,list<string>>,body:string} */
$zsHttp = static function (string $method, string $path, array $o = []) use ($zsBase, &$zsJars): array {
    $jar = (string) ($o['jar'] ?? '');
    if ($jar !== '' && !in_array($jar, $zsJars, true)) {
        $zsJars[] = $jar;
    }
    $headers = [];
    $ch = curl_init(str_starts_with($path, 'http') ? $path : $zsBase . ltrim($path, '/'));
    $post = $o['post'] ?? null;
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST => $method, CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => false, CURLOPT_TIMEOUT => 40, CURLOPT_CONNECTTIMEOUT => 3, CURLOPT_HTTPHEADER => $o['headers'] ?? [],
        CURLOPT_HEADERFUNCTION => static function ($c, string $line) use (&$headers): int {
            if (str_contains($line, ':')) {
                [$k, $v] = explode(':', $line, 2);
                $headers[strtolower(trim($k))][] = trim($v);
            }
            return strlen($line);
        },
    ]);
    if ($jar !== '') {
        curl_setopt($ch, CURLOPT_COOKIEJAR, $jar);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $jar);
    }
    if ($post !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($post) ? http_build_query($post) : $post);
    }
    $body = (string) curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);
    return ['code' => $code, 'headers' => $headers, 'body' => $body];
};
$zsField = static fn(string $html, string $name): string => preg_match('/name="' . preg_quote($name, '/') . '" value="([^"]*)"/', $html, $m) === 1 ? html_entity_decode($m[1]) : '';
$zsMeta = static fn(string $html): string => preg_match('/<meta name="csrf-token" content="([^"]*)"/', $html, $m) === 1 ? html_entity_decode($m[1]) : '';
$zsProbe = $zsHttp('GET', 'admin/login');
if ($zsProbe['code'] === 0) {
    echo "  info the site does not answer at {$zsBase}: the HTTP checks were skipped\n";
    return;
}
$zsTmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'zzsocial-' . bin2hex(random_bytes(3));
$zsUsers = [];
$zsRoles = [];
$zsPw = [];
$zsAccounts = [
    'admin'  => ['admin', null],
    'editor' => ['editor', null],
    'share'  => ['zz-social-share', ['dashboard.view', 'social.share']],
    'manage' => ['zz-social-manage', ['dashboard.view', 'social.manage']],
    'none'   => ['zz-social-none', ['dashboard.view', 'newsletter.manage']],
];
foreach ($zsAccounts as $k => [$role, $abilities]) {
    if ($abilities !== null) {
        Database::insert('roles', ['slug' => $role, 'name' => 'ZZ ' . $k, 'description' => '', 'abilities' => json_encode($abilities), 'is_system' => 0]);
        $zsRoles[] = $role;
    }
    $pw = bin2hex(random_bytes(9)) . 'Aa1';
    $zsPw[$k] = $pw;
    $zsUsers[] = User::create(['name' => 'ZZ ' . $k, 'email' => 'zz-social-' . $k . '@example.test', 'password_hash' => Auth::hashPassword($pw), 'role' => $role, 'is_active' => 1, 'must_change_password' => 0]);
}
Roles::flush();
$zsCleanup = static function () use (&$zsUsers, &$zsRoles, &$zsJars): void {
    foreach ($zsUsers as $id) {
        Database::execute('DELETE FROM users WHERE id = ?', [$id]);
    }
    foreach ($zsRoles as $slug) {
        Database::execute('DELETE FROM roles WHERE slug = ?', [$slug]);
    }
    foreach ($zsJars as $j) {
        @unlink($j);
    }
    $zsUsers = [];
    $zsRoles = [];
    Roles::flush();
};
register_shutdown_function($zsCleanup);
Database::execute('DELETE FROM rate_limits');
$zsJar = [];
foreach (array_keys($zsAccounts) as $k) {
    $jar = $zsTmp . '-' . $k . '.txt';
    $lp = $zsHttp('GET', 'admin/login', ['jar' => $jar]);
    $zsHttp('POST', 'admin/login', ['jar' => $jar, 'post' => ['_token' => $zsField($lp['body'], '_token'), 'email' => 'zz-social-' . $k . '@example.test', 'password' => $zsPw[$k]]]);
    $zsJar[$k] = $jar;
}
$code = static fn(string $k, string $path, array $h = []): int => $zsHttp('GET', $path, ['jar' => $zsJar[$k], 'headers' => $h])['code'];
$json = ['Accept: application/json'];
$etok = $zsMeta($zsHttp('GET', 'admin/social/log', ['jar' => $zsJar['editor']])['body']);

$issueId = $zzIssue();
\App\Services\Newsletter\NewsletterService::publish($issueId, (int) Database::value('SELECT id FROM users ORDER BY id LIMIT 1', [], 0));
$draftId = $zzIssue();
$zzConfigure('telegram', ['bot_token' => $zzTok['tg'], 'chat' => '@zzchannel'], false);          // configured but SWITCHED OFF: nothing can reach the network from these tests
$zsBodies = [];

$urls = ['admin/social', 'admin/social/channels', 'admin/social/templates', 'admin/social/log', 'admin/social/kit', 'admin/social/kit/' . $issueId, 'admin/api/newsletters/' . $issueId . '/share', 'admin/api/newsletters/' . $issueId . '/share/status'];
$anon = array_map(static fn(string $u): int => $zsHttp('GET', $u)['code'], $urls);
check('not signed in: every social admin URL goes to the login page', $anon === array_fill(0, count($urls), 302) && str_contains($zsHttp('GET', 'admin/social/channels')['headers']['location'][0] ?? '', 'admin/login'), json_encode($anon));
check('not signed in, JSON client: 401 with a JSON error', $zsHttp('GET', 'admin/api/newsletters/' . $issueId . '/share', ['headers' => $json])['code'] === 401);
$r = $zsHttp('GET', 'admin/social', ['jar' => $zsJar['admin']]);
$zsBodies[] = $r['body'];
check('admin: the profile links page opens, lists every platform with its address box', $r['code'] === 200 && str_contains($r['body'], 'Social media profile links') && str_contains($r['body'], 'name="url_facebook"') && str_contains($r['body'], 'name="url_telegram"') && str_contains($r['body'], 'name="show_footer"'));
$r = $zsHttp('GET', 'admin/social/channels', ['jar' => $zsJar['admin']]);
$zsBodies[] = $r['body'];
check('admin: the channels page shows a card per channel with set-up steps, test buttons and masked secrets', $r['code'] === 200 && substr_count($r['body'], 'class="card asoc-channel"') === 6 && str_contains($r['body'], 'Test connection') && str_contains($r['body'], 'Send a test post') && str_contains($r['body'], 'Set (hidden)') && str_contains($r['body'], 'SOCIAL_TG_BOT_TOKEN'));
check('admin: the channels page never contains a secret value (the stored one, its tail, or any part)', !str_contains($r['body'], $zzTok['tg']) && !str_contains($r['body'], substr($zzTok['tg'], 12)) && preg_match('/type="password"[^>]*value="[^"]+"/', $r['body']) !== 1);
foreach (['admin/social/templates', 'admin/social/log', 'admin/social/kit/' . $issueId] as $u) {
    $r = $zsHttp('GET', $u, ['jar' => $zsJar['admin']]);
    $zsBodies[] = $r['body'];
    check("admin: GET /{$u} opens", $r['code'] === 200);
}
$r = $zsHttp('GET', 'admin/social/kit', ['jar' => $zsJar['admin']]);
check('the Share kit entry redirects to the kit of the newest public issue', $r['code'] === 302 && str_contains($r['headers']['location'][0] ?? '', 'admin/social/kit/'));
$matrix = [
    // key => [profile, channels, templates, log, kit, panel json]
    'admin'  => [200, 200, 200, 200, 200, 200],
    'editor' => [403, 403, 403, 200, 200, 200],
    'share'  => [403, 403, 403, 200, 200, 200],
    'manage' => [200, 200, 200, 403, 403, 403],
    'none'   => [403, 403, 403, 403, 403, 403],
];
$paths = ['admin/social', 'admin/social/channels', 'admin/social/templates', 'admin/social/log', 'admin/social/kit/' . $issueId, 'admin/api/newsletters/' . $issueId . '/share'];
foreach ($matrix as $k => $want) {
    $got = array_map(static fn(string $p): int => $code($k, $p, $json), $paths);
    check("role matrix ({$k}): profile / channels / templates / log / kit / share panel = " . implode(' ', $want), $got === $want, implode(' ', $got));
}
$nav = static function (string $k) use ($zsHttp, $zsJar): string {
    $r = $zsHttp('GET', 'admin/social/log', ['jar' => $zsJar[$k]]);
    return preg_match('~<nav[^>]*admin-nav.*?</nav>~s', $r['body'], $m) === 1 ? $m[0] : $r['body'];
};
$navAdmin = $nav('admin');
$navEditor = $nav('editor');
check('the sidebar follows the abilities: admin sees all five social entries, the editor only Share log and Share kit', str_contains($navAdmin, 'admin/social/channels') && str_contains($navAdmin, 'admin/social/templates') && str_contains($navAdmin, 'admin/social"') && str_contains($navEditor, 'admin/social/log') && str_contains($navEditor, 'admin/social/kit') && !str_contains($navEditor, 'admin/social/channels') && !str_contains($navEditor, 'admin/social/templates'));
// CSRF and state changes
$page = $zsHttp('GET', 'admin/social', ['jar' => $zsJar['admin']]);
$tok = $zsMeta($page['body']);
check('POST without a CSRF token: 403; with a forged token: 403', $zsHttp('POST', 'admin/social', ['jar' => $zsJar['admin'], 'post' => ['url_facebook' => 'https://www.facebook.com/zz']])['code'] === 403 && $zsHttp('POST', 'admin/social/channels/global', ['jar' => $zsJar['admin'], 'post' => ['_token' => 'forged', 'enabled' => '1']])['code'] === 403);
$r = $zsHttp('POST', 'admin/social', ['jar' => $zsJar['admin'], 'post' => ['_token' => $tok, 'url_facebook' => 'http://insecure.example/zz', 'url_telegram' => '@zzviahttp']]);
check('saving an invalid profile address: redirected back with an error; NOTHING was saved (not even the valid ones)', $r['code'] === 302 && SocialProfiles_values_telegram() !== 'https://t.me/zzviahttp');
$r = $zsHttp('POST', 'admin/social', ['jar' => $zsJar['admin'], 'post' => ['_token' => $tok, 'url_facebook' => 'https://www.facebook.com/zzhttp', 'url_telegram' => '@zzviahttp', 'order_telegram' => '1', 'order_facebook' => '2', 'show_footer' => '1', 'show_newsletter' => '1']]);
\App\Core\Settings::flush();
check('saving valid profile addresses: saved, the existing setting keys updated, order and switches applied', $r['code'] === 302 && \App\Core\Settings::get('social_facebook') === 'https://www.facebook.com/zzhttp' && SocialProfiles_values_telegram() === 'https://t.me/zzviahttp' && array_slice(\App\Services\Social\SocialProfiles::order(), 0, 2) === ['telegram', 'facebook']
    && !\App\Services\Social\SocialProfiles::showIn('contact') && \App\Services\Social\SocialProfiles::showIn('footer'));
$home = $zsHttp('GET', '');
check('the public footer shows the saved links in the saved order (Telegram before Facebook)', $home['code'] === 200 && str_contains($home['body'], 'https://t.me/zzviahttp') && str_contains($home['body'], 'https://www.facebook.com/zzhttp') && strpos($home['body'], 'https://t.me/zzviahttp') < strpos($home['body'], 'https://www.facebook.com/zzhttp'));
$secret = 'zzhttp' . bin2hex(random_bytes(6));
$tgNew = '987654321:ZZHTTP' . bin2hex(random_bytes(12));
$r = $zsHttp('POST', 'admin/social/channels/telegram', ['jar' => $zsJar['admin'], 'post' => ['_token' => $tok, 'bot_token' => $tgNew, 'chat' => '@zzchannel', 'enabled' => '', 'default_on' => '1']]);
$stored = (string) Database::value("SELECT setting_value FROM settings WHERE setting_key = 'social_secret_telegram_bot_token'", [], '');
check('saving a channel over HTTP: the token is stored ENCRYPTED, the page after the redirect does not show it', $r['code'] === 302 && str_starts_with($stored, 'enc:v1:') && !str_contains($stored, $tgNew) && \App\Services\Shop\SecretStore::decrypt($stored) === $tgNew);
$chan = $zsHttp('GET', 'admin/social/channels', ['jar' => $zsJar['admin']]);
check('...flash message and page contain no token', !str_contains($chan['body'], $tgNew) && !str_contains($chan['body'], substr($tgNew, 12)));
$r = $zsHttp('POST', 'admin/social/channels/telegram', ['jar' => $zsJar['admin'], 'post' => ['_token' => $tok, 'chat' => 'not a chat', 'bot_token' => $secret . 'x']]);
$after = $zsHttp('GET', 'admin/social/channels', ['jar' => $zsJar['admin']]);
check('a refused channel form: redirect back, the error is shown, and the secret typed in the box is NOT given back in the page', $r['code'] === 302 && !str_contains($after['body'], $secret) && str_contains($after['body'], 'does not look right'));
$r = $zsHttp('POST', 'admin/social/channels/telegram/clear-secret', ['jar' => $zsJar['admin'], 'post' => ['_token' => $tok, 'name' => 'bot_token']]);
\App\Core\Settings::flush();
check('"Remove the stored value" deletes the secret', $r['code'] === 302 && !SocialConfig::secretStatus('telegram', 'bot_token')['set']);
$r = $zsHttp('POST', 'admin/social/channels/telegram/clear-secret', ['jar' => $zsJar['admin'], 'post' => ['_token' => $tok, 'name' => 'chat']]);
\App\Core\Settings::flush();
check('clear-secret refuses a field that is not a secret', $r['code'] === 302 && SocialConfig::str('social_ch_telegram_chat') === '@zzchannel');
check('an unknown channel name is a 404 (the route only knows the six)', $zsHttp('POST', 'admin/social/channels/myspace', ['jar' => $zsJar['admin'], 'post' => ['_token' => $tok]])['code'] === 404);
$r = $zsHttp('POST', 'admin/social/channels/instagram/test', ['jar' => $zsJar['admin'], 'headers' => array_merge($json, ['X-CSRF-TOKEN: ' . $tok])]);
$j = json_decode($r['body'], true);
check('TEST CONNECTION of a channel that is not set up: JSON {ok:false, message: "Finish the setup first. Missing: ..."} - answered BEFORE any network call', $r['code'] === 200 && ($j['ok'] ?? null) === false && str_contains((string) ($j['message'] ?? ''), 'Missing') && isset($j['at']), $r['body']);
$r = $zsHttp('POST', 'admin/social/channels/instagram/send-test', ['jar' => $zsJar['admin'], 'headers' => array_merge($json, ['X-CSRF-TOKEN: ' . $tok])]);
$j = json_decode($r['body'], true);
check('SEND A TEST POST without the confirmation: 422 confirm_required, nothing is sent', $r['code'] === 422 && ($j['error'] ?? '') === 'confirm_required');
$r = $zsHttp('POST', 'admin/social/channels/instagram/send-test', ['jar' => $zsJar['admin'], 'headers' => array_merge($json, ['X-CSRF-TOKEN: ' . $tok]), 'post' => ['confirm' => '1']]);
$j = json_decode($r['body'], true);
check('...with it, an unconfigured channel still answers "Finish the setup first" (no network)', ($j['ok'] ?? null) === false && str_contains((string) ($j['message'] ?? ''), 'Missing'));
check('the test endpoints need social.manage: the editor gets 403', $zsHttp('POST', 'admin/social/channels/instagram/test', ['jar' => $zsJar['editor'], 'headers' => array_merge($json, ['X-CSRF-TOKEN: ' . $etok])])['code'] === 403);
$r = $zsHttp('POST', 'admin/social/templates', ['jar' => $zsJar['admin'], 'post' => ['_token' => $tok, 'tpl_x' => '{title} {nonsense}', 'hashtags' => '#Zz']]);
check('a template with an unknown placeholder is refused over HTTP and nothing is saved', $r['code'] === 302 && SocialConfig::str('social_tpl_x') === '');
$r = $zsHttp('POST', 'admin/social/templates/preview', ['jar' => $zsJar['admin'], 'headers' => array_merge($json, ['X-CSRF-TOKEN: ' . $tok, 'Content-Type: application/json']), 'post' => json_encode(['channel' => 'x', 'template' => '{title}', 'newsletter_id' => $issueId])]);
$j = json_decode($r['body'], true);
check('template preview (JSON): the caption for a real issue with its length and limit', $r['code'] === 200 && str_starts_with((string) ($j['text'] ?? ''), 'ZZ-SOCIAL') && $j['limit'] === 280 && $j['counting'] === 'x' && $j['over'] === false, $r['body']);
// the share panel API
$jh = array_merge($json, ['Content-Type: application/json']);
$r = $zsHttp('GET', 'admin/api/newsletters/' . $issueId . '/share', ['jar' => $zsJar['editor'], 'headers' => $json]);
$j = json_decode($r['body'], true);
check('GET share panel (editor): JSON {ok, issue{is_live:true}, channels[], hidden[6], image, warnings, note, kit_url}', $r['code'] === 200 && $j['ok'] === true && $j['issue']['is_live'] === true && $j['channels'] === [] && count($j['hidden']) === 6 && isset($j['image']['thumb_url'], $j['note'], $j['kit_url']) && str_contains((string) ($r['headers']['cache-control'][0] ?? ''), 'no-store'), $r['body']);
check('the panel of a missing issue is a 404 JSON error', $zsHttp('GET', 'admin/api/newsletters/999999999/share', ['jar' => $zsJar['editor'], 'headers' => $json])['code'] === 404);
$r = $zsHttp('PUT', 'admin/api/newsletters/' . $issueId . '/share', ['jar' => $zsJar['editor'], 'headers' => array_merge($jh, ['X-CSRF-TOKEN: ' . $etok]), 'post' => json_encode(['channels' => ['telegram' => ['on' => true]]])]);
$j = json_decode($r['body'], true);
check('PUT choices: ticking a channel that is switched off is a 422 validation error', $r['code'] === 422 && ($j['error'] ?? '') === 'validation' && isset($j['errors']['telegram']), $r['body']);
check('PUT without the CSRF header: 403', $zsHttp('PUT', 'admin/api/newsletters/' . $issueId . '/share', ['jar' => $zsJar['editor'], 'headers' => $jh, 'post' => json_encode(['channels' => []])])['code'] === 403);
Database::execute('UPDATE settings SET setting_value = ? WHERE setting_key = ?', ['1', SocialConfig::key('telegram', 'enabled')]);
\App\Core\Settings::flush();
SocialConfig::putSecret('telegram', 'bot_token', $zzTok['tg']);
$r = $zsHttp('PUT', 'admin/api/newsletters/' . $draftId . '/share', ['jar' => $zsJar['editor'], 'headers' => array_merge($jh, ['X-CSRF-TOKEN: ' . $etok]), 'post' => json_encode(['channels' => ['telegram' => ['on' => false, 'caption' => 'A caption typed in the panel']]])]);
$j = json_decode($r['body'], true);
check('PUT choices with a ready channel: saved with the issue, the panel is returned (the caption is kept; the issue is a draft: not live)', $r['code'] === 200 && $j['ok'] === true && $j['panel']['issue']['is_live'] === false && $j['panel']['channels'][0]['id'] === 'telegram' && $j['panel']['channels'][0]['caption'] === 'A caption typed in the panel' && $j['panel']['channels'][0]['edited'] === true);
Database::execute('UPDATE settings SET setting_value = ? WHERE setting_key = ?', ['0', SocialConfig::key('telegram', 'enabled')]);
\App\Core\Settings::flush();
$r = $zsHttp('POST', 'admin/api/newsletters/' . $draftId . '/share/count', ['jar' => $zsJar['editor'], 'headers' => array_merge($jh, ['X-CSRF-TOKEN: ' . $etok]), 'post' => json_encode(['channel' => 'x', 'text' => "Link https://example.org/" . str_repeat('a', 200) . " \u{1F600}"])]);
$j = json_decode($r['body'], true);
check('POST count: the live counter as the platform counts it (X: a link is 23, an emoji 2)', $r['code'] === 200 && $j['length'] === 5 + 23 + 1 + 2 && $j['limit'] === 280 && $j['over'] === false && $j['counting'] === 'x', $r['body']);
$r = $zsHttp('POST', 'admin/api/newsletters/' . $draftId . '/share/now', ['jar' => $zsJar['editor'], 'headers' => array_merge($jh, ['X-CSRF-TOKEN: ' . $etok]), 'post' => json_encode(['channels' => ['telegram']])]);
check('POST share/now on an issue that is not public yet: 422 (it is shared when it goes live)', $r['code'] === 422 && str_contains((string) json_decode($r['body'], true)['message'], 'not public'));
$r = $zsHttp('POST', 'admin/api/newsletters/' . $issueId . '/share/again', ['jar' => $zsJar['editor'], 'headers' => array_merge($jh, ['X-CSRF-TOKEN: ' . $etok]), 'post' => json_encode(['channels' => ['telegram']])]);
check('POST share/again without confirm: 422 confirm_required', $r['code'] === 422 && (json_decode($r['body'], true)['error'] ?? '') === 'confirm_required');
$r = $zsHttp('POST', 'admin/api/newsletters/' . $issueId . '/share/dispatch', ['jar' => $zsJar['editor'], 'headers' => array_merge($jh, ['X-CSRF-TOKEN: ' . $etok]), 'post' => '{}']);
$j = json_decode($r['body'], true);
check('POST share/dispatch with nothing due: summary of zeros and the statuses', $r['code'] === 200 && $j['summary']['attempted'] === 0 && isset($j['statuses']));
$r = $zsHttp('GET', 'admin/api/newsletters/' . $issueId . '/share/status', ['jar' => $zsJar['editor'], 'headers' => $json]);
check('GET share/status: {statuses, queued}', $r['code'] === 200 && isset(json_decode($r['body'], true)['statuses']) && json_decode($r['body'], true)['queued'] === 0);
// the share log buttons
$row = PostQueue::enqueue($issueId, 'linkedin', 1, 'log test caption');
PostQueue::claim($row['id']);
PostQueue::markFailed($row['id'], 'The platform refused the post');
$logPage = $zsHttp('GET', 'admin/social/log?channel=linkedin&status=failed', ['jar' => $zsJar['editor']]);
check('the share log shows the failed post with its short error and a Retry button; filters work', $logPage['code'] === 200 && str_contains($logPage['body'], 'The platform refused the post') && str_contains($logPage['body'], '/retry') && str_contains($logPage['body'], 'Skip'));
$r = $zsHttp('POST', 'admin/social/log/' . $row['id'] . '/retry', ['jar' => $zsJar['editor'], 'post' => ['_token' => $zsMeta($logPage['body'])]]);
$st = SocialPost::find($row['id'])['status'];
check('Retry (editor, ability social.share): the post is queued again (LinkedIn is not set up, so a later dispatch only skips it; nothing leaves the server)', $r['code'] === 302 && in_array($st, ['queued', 'skipped'], true), $st);
$row = PostQueue::enqueue($issueId, 'linkedin', 3, 'json skip test caption');
PostQueue::claim($row['id']);
PostQueue::markFailed($row['id'], 'The platform refused the post');
$r = $zsHttp('POST', 'admin/api/social/posts/' . $row['id'] . '/skip', ['jar' => $zsJar['editor'], 'headers' => array_merge($json, ['X-CSRF-TOKEN: ' . $etok])]);
$j = json_decode($r['body'], true);
check('JSON skip: {ok:true,status:{state:"skipped"}}; a second skip is a 409 conflict', $r['code'] === 200 && ($j['status']['state'] ?? '') === 'skipped' && $zsHttp('POST', 'admin/api/social/posts/' . $row['id'] . '/skip', ['jar' => $zsJar['editor'], 'headers' => array_merge($json, ['X-CSRF-TOKEN: ' . $etok])])['code'] === 409);
check('the log actions are refused for a role without social.share (manage-only): 403', $zsHttp('POST', 'admin/social/log/' . $row['id'] . '/retry', ['jar' => $zsJar['manage'], 'post' => ['_token' => $zsMeta($zsHttp('GET', 'admin/social', ['jar' => $zsJar['manage']])['body'])]])['code'] === 403);
// social.share WITHOUT newsletter.manage: public issues only; a draft is a 404 everywhere (no title, summary, picture, no write of captions that would auto-post later)
$stok = $zsMeta($zsHttp('GET', 'admin/social/log', ['jar' => $zsJar['share']])['body']);
$shareDraft = [
    'GET panel'   => $zsHttp('GET', 'admin/api/newsletters/' . $draftId . '/share', ['jar' => $zsJar['share'], 'headers' => $json]),
    'GET status'  => $zsHttp('GET', 'admin/api/newsletters/' . $draftId . '/share/status', ['jar' => $zsJar['share'], 'headers' => $json]),
    'GET kit'     => $zsHttp('GET', 'admin/social/kit/' . $draftId, ['jar' => $zsJar['share']]),
    'GET preview' => $zsHttp('GET', 'admin/social/preview/' . $draftId . '/square', ['jar' => $zsJar['share']]),
    'GET dl'      => $zsHttp('GET', 'admin/social/kit/' . $draftId . '/download/portrait', ['jar' => $zsJar['share']]),
    'POST now'    => $zsHttp('POST', 'admin/api/newsletters/' . $draftId . '/share/now', ['jar' => $zsJar['share'], 'headers' => array_merge($jh, ['X-CSRF-TOKEN: ' . $stok]), 'post' => json_encode(['channels' => ['telegram']])]),
];
$before = (string) Database::value('SELECT COALESCE(share_choices, "") FROM newsletters WHERE id = ?', [$draftId], '');
$shareDraft['PUT save'] = $zsHttp('PUT', 'admin/api/newsletters/' . $draftId . '/share', ['jar' => $zsJar['share'], 'headers' => array_merge($jh, ['X-CSRF-TOKEN: ' . $stok]), 'post' => json_encode(['channels' => ['telegram' => ['on' => false, 'caption' => 'csrf probe SECRET-DRAFT']]])]);
$after = (string) Database::value('SELECT COALESCE(share_choices, "") FROM newsletters WHERE id = ?', [$draftId], '');
$leak = false;
foreach ($shareDraft as $resp) {
    $leak = $leak || str_contains($resp['body'], (string) Database::value('SELECT title FROM newsletters WHERE id = ?', [$draftId], 'x')) || str_contains($resp['body'], 'SECRET-DRAFT');
}
check('social.share without newsletter.manage: a DRAFT issue is a 404 for the panel, the status, the kit, the picture preview and download, share/now and the save; nothing is written and no title leaks', array_map(static fn(array $r): int => $r['code'], $shareDraft) === array_fill_keys(array_keys($shareDraft), 404) && $before === $after && !$leak, json_encode(array_map(static fn(array $r): int => $r['code'], $shareDraft)));
check('...the same role still works on a PUBLIC issue (panel, kit, picture preview), and the editor (both abilities) on a draft', $code('share', 'admin/api/newsletters/' . $issueId . '/share', $json) === 200 && $code('share', 'admin/social/kit/' . $issueId) === 200 && $code('share', 'admin/social/preview/' . $issueId . '/square') === 200 && $code('editor', 'admin/social/kit/' . $draftId) === 200 && $code('editor', 'admin/social/preview/' . $draftId . '/square') === 200);
$failedRow = PostQueue::enqueue($issueId, 'linkedin', 7, 'dashboard probe');
PostQueue::claim($failedRow['id']);
PostQueue::markFailed($failedRow['id'], 'The platform refused the post');
$dashShare = $zsHttp('GET', 'admin', ['jar' => $zsJar['share']]);
$dashManage = $zsHttp('GET', 'admin', ['jar' => $zsJar['manage']]);
$tileHref = static fn(string $html): string => preg_match('~<a[^>]*stat-tile--alert[^>]*href="([^"]*)"|<a[^>]*href="([^"]*)"[^>]*stat-tile--alert~', $html, $m) === 1 ? ($m[1] !== '' ? $m[1] : ($m[2] ?? '')) : '';
check('the dashboard tile "Social media needs attention" links to a page the person may open: social.share-only -> the share log, social.manage-only -> the channels screen (never a 403)', str_contains($tileHref($dashShare['body']), 'admin/social/log') && str_contains($tileHref($dashManage['body']), 'admin/social/channels') && $code('manage', 'admin/social/channels') === 200, json_encode([$tileHref($dashShare['body']), $tileHref($dashManage['body'])]));
// the live counter never works on unbounded text
$r = $zsHttp('POST', 'admin/api/newsletters/' . $issueId . '/share/count', ['jar' => $zsJar['editor'], 'headers' => array_merge($jh, ['X-CSRF-TOKEN: ' . $etok]), 'post' => json_encode(['channel' => 'x', 'text' => str_repeat('A https://example.org/x ', 2500)])]);
$j = json_decode($r['body'], true);
$r2 = $zsHttp('POST', 'admin/api/newsletters/' . $issueId . '/share/count', ['jar' => $zsJar['editor'], 'headers' => array_merge($jh, ['X-CSRF-TOKEN: ' . $etok]), 'post' => json_encode(['channel' => 'facebook', 'text' => str_repeat('word ', 1500)])]);
check('POST count: text far beyond any limit (over 10,000 characters) is refused with a 422 before it is cleaned or counted (still answers "over"); a long but sane text is counted normally', $r['code'] === 422 && ($j['over'] ?? null) === true && $r2['code'] === 200 && json_decode($r2['body'], true)['length'] === 7499 && json_decode($r2['body'], true)['over'] === true);
// the public image route
$tokenImg = bin2hex(random_bytes(32));
$r = $zsHttp('GET', 'social/image/' . $tokenImg . '.jpg');
check('PUBLIC share image: an unknown token is a plain 404 with no cookie (the stub answers 404 until the renderer exists)', $r['code'] === 404 && empty($r['headers']['set-cookie']));
check('...a malformed token never even reaches the controller (404)', $zsHttp('GET', 'social/image/short.jpg')['code'] === 404 && $zsHttp('GET', 'social/image/' . strtoupper($tokenImg) . '.jpg')['code'] === 404 && $zsHttp('GET', 'social/image/' . $tokenImg)['code'] === 404 && $zsHttp('GET', 'social/image/' . str_repeat('a', 63) . '.jpg')['code'] === 404 && $zsHttp('GET', 'social/image/' . str_repeat('a', 65) . '.jpg')['code'] === 404);
check('...and the route does not list directories', $zsHttp('GET', 'social/image/')['code'] === 404 && $zsHttp('GET', 'social/')['code'] === 404);
// admin pages: nothing secret anywhere
$hits = [];
foreach ($zsBodies as $i => $b) {
    foreach ([$zzTok['tg'], substr($zzTok['tg'], 12), $tgNew, substr($tgNew, 12), $secret] as $n) {
        if (str_contains($b, $n)) {
            $hits[] = 'page ' . $i;
        }
    }
}
check('no admin page fetched in this section contains any token or secret', $hits === [], implode(',', $hits));
$zsCleanup();
$zzCleanup();

/** the stored Telegram profile address (a tiny helper so the checks above stay one line each) */
function SocialProfiles_values_telegram(): string
{
    \App\Core\Settings::flush();
    return (string) \App\Core\Settings::get('social_telegram', '');
}
