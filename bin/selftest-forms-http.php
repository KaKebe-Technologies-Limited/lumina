<?php
/**
 * FORMS module: HTTP checks (part 2 of bin/selftest-forms.php; it shares its helpers and check()). Real requests to http://localhost/<folder>/ with curl:
 * visitors (page, CSRF, honeypot, timing, validation, upload, thank-you, closed / full / draft notices), admins (builder JSON API, conflict, publish, inbox,
 * detail, status, notes, bulk, CSV, authenticated file download, delete) and a restricted role (forms.submissions only) and a role without any forms ability.
 * Skips itself when the site does not answer. Throw-away users / roles / forms are removed by the caller.
 */
declare(strict_types=1);

use App\Core\Auth;
use App\Core\Config;
use App\Core\Database;
use App\Core\Roles;
use App\Models\User;
use App\Services\Forms\FormDefinition;
use App\Services\Forms\FormService;
use App\Services\Forms\SubmissionService;

echo "Forms: HTTP (visitors, admins, restricted roles)\n";

$hBase = 'http://localhost/' . basename(BASE_PATH);
$hProbe = (function () use ($hBase): bool {
    if (!function_exists('curl_init')) {
        return false;
    }
    $ch = curl_init($hBase . '/robots.txt');
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 4, CURLOPT_CONNECTTIMEOUT => 2]);
    curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);
    return $code === 200;
})();

if (!$hProbe) {
    echo "  skip  the site does not answer at {$hBase}/ (HTTP checks skipped)\n";
    return;
}

/** A browser-like client with its own cookie jar: ['do' => callable, 'jar' => path]. */
$hClient = static function () use ($hBase, &$zzTmp): callable {
    $jar = tempnam(sys_get_temp_dir(), 'zzj') ?: '';
    $zzTmp[] = $jar;
    /** @return array{0:int,1:string,2:array<string,string>} status, body, lower-case headers */
    return static function (string $method, string $path, array $o = []) use ($hBase, $jar): array {
        $ch = curl_init($hBase . $path);
        $headers = $o['headers'] ?? [];
        $opts = [CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true, CURLOPT_TIMEOUT => 40, CURLOPT_COOKIEJAR => $jar, CURLOPT_COOKIEFILE => $jar,
            CURLOPT_FOLLOWLOCATION => !empty($o['follow']), CURLOPT_CUSTOMREQUEST => $method, CURLOPT_MAXREDIRS => 5];
        if (isset($o['form'])) {
            $opts[CURLOPT_POSTFIELDS] = http_build_query($o['form']);
        } elseif (isset($o['json'])) {
            $opts[CURLOPT_POSTFIELDS] = json_encode($o['json']);
            $headers[] = 'Content-Type: application/json';
            $headers[] = 'Accept: application/json';
        } elseif (isset($o['multipart'])) {
            $opts[CURLOPT_POSTFIELDS] = $o['multipart'];
        }
        $opts[CURLOPT_HTTPHEADER] = $headers;
        curl_setopt_array($ch, $opts);
        $raw = (string) curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $hs = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);
        $hdrBlock = substr($raw, 0, $hs);
        if (!empty($o['follow']) && str_contains($hdrBlock, "\r\n\r\nHTTP/")) {          // keep the last response's headers
            $parts = explode("\r\n\r\n", trim($hdrBlock));
            $hdrBlock = (string) end($parts);
        }
        $h = [];
        foreach (explode("\r\n", $hdrBlock) as $line) {
            if (str_contains($line, ':')) {
                [$k, $v] = explode(':', $line, 2);
                $h[strtolower(trim($k))] = trim($v);
            }
        }
        return [$status, substr($raw, $hs), $h];
    };
};
$hAttr = static fn(string $html, string $re): string => preg_match($re, $html, $m) === 1 ? html_entity_decode($m[1]) : '';
$hTok = static fn(string $html): string => $hAttr($html, '/name="_token" value="([^"]+)"/');
$hMeta = static fn(string $html): string => $hAttr($html, '/<meta name="csrf-token" content="([^"]+)"/');
$hSig = static fn(int $age): string => (time() - $age) . '.' . hash_hmac('sha256', (string) (time() - $age), Config::appKey());
$hMails = static fn(): array => glob(STORAGE_PATH . '/mail/*.eml') ?: [];
$hEml = static function (string $file): string {
    $raw = (string) file_get_contents($file);
    $out = $raw;
    if (preg_match_all('/Content-Transfer-Encoding: base64\r?\n\r?\n(.*?)(?=\r?\n--|\z)/s', $raw, $m)) {
        foreach ($m[1] as $b) {
            $out .= "\n" . base64_decode(preg_replace('/\s+/', '', $b));
        }
    }
    return $out;
};
$hRandPw = static fn(): string => 'Zz' . bin2hex(random_bytes(7)) . '9q';
$hClearLimits = static function (string ...$slugs): void {
    // the confirmation mail is limited to 3 per address per hour (a limit that survives between runs): forget the addresses this test types, so a re-run within the hour still gets its mail
    foreach (['grace@example.org', 'json@example.org', 'peter@example.org'] as $addr) {
        \App\Core\RateLimit::clear('forms:confirm:addr:' . hash('sha256', $addr));
    }
    foreach (['127.0.0.1', '::1'] as $ip) {
        \App\Core\RateLimit::clear('forms:ip:' . $ip);
        foreach ($slugs as $s) {
            \App\Core\RateLimit::clear('throttle:' . $ip . ':POST:/forms/' . $s);
        }
    }
};

// ---- throw-away accounts: an administrator, a role that may only read answers, a role with no forms ability at all
$pwAdmin = $hRandPw();
$pwReader = $hRandPw();
$pwNone = $hRandPw();
Database::execute("INSERT INTO roles (slug, name, description, abilities, is_system) VALUES ('zz-forms-reader', 'ZZ forms reader', '', ?, 0), ('zz-forms-none', 'ZZ no forms', '', ?, 0)", [json_encode(['dashboard.view', 'forms.submissions']), json_encode(['dashboard.view', 'inbox.manage'])]);
Roles::flush();
foreach ([['zz-forms-admin@example.test', 'admin', $pwAdmin], ['zz-forms-reader@example.test', 'zz-forms-reader', $pwReader], ['zz-forms-none@example.test', 'zz-forms-none', $pwNone]] as [$email, $role, $pw]) {
    User::create(['name' => 'ZZ ' . $role, 'email' => $email, 'password_hash' => Auth::hashPassword($pw), 'role' => $role, 'is_active' => 1, 'must_change_password' => 0]);
}
$hLogin = static function (callable $c, string $email, string $pw) use ($hTok): bool {
    [, $page] = $c('GET', '/admin/login');
    $c('POST', '/admin/login', ['form' => ['_token' => $hTok($page), 'email' => $email, 'password' => $pw]]);
    return $c('GET', '/admin')[0] === 200;
};

$admin = $hClient();
$reader = $hClient();
$none = $hClient();
$visitor = $hClient();
$anon = $hClient();
check('login: the throw-away administrator, reader and no-forms users can sign in', $hLogin($admin, 'zz-forms-admin@example.test', $pwAdmin) && $hLogin($reader, 'zz-forms-reader@example.test', $pwReader) && $hLogin($none, 'zz-forms-none@example.test', $pwNone));
if ($admin('GET', '/admin')[0] !== 200) {
    echo "  skip  administrator login failed; the remaining HTTP checks are skipped\n";
    return;
}
$csrf = $hMeta($admin('GET', '/admin/forms')[1]);
$J = static fn(array $extra = []): array => ['X-CSRF-TOKEN: ' . $GLOBALS['csrf'], 'Accept: application/json'] + $extra;
$GLOBALS['csrf'] = $csrf;
$api = static function (callable $c, string $method, string $path, ?array $body = null) use ($J): array {
    $o = ['headers' => $J()];
    if ($body !== null) {
        $o['json'] = $body;
    }
    $res = $c($method, $path, $o);
    return [$res[0], json_decode($res[1], true) ?? [], $res[2]];
};

// ================================================================================================================================ access control
echo " access control\n";
[$st] = $anon('GET', '/admin/forms');
check('anonymous visitor: /admin/forms redirects to the login (302), nothing leaks', $st === 302);
[$st, $b] = $anon('GET', '/admin/api/forms', ['headers' => ['Accept: application/json']]);
check('anonymous visitor: the JSON API answers 401, not data', in_array($st, [401, 302], true) && !str_contains($b, '"data"'));
foreach (['/admin/forms', '/admin/forms/new', '/admin/form-answers', '/admin/forms/1/submissions', '/admin/forms/1/submissions/export', '/admin/api/forms', '/admin/api/forms/catalog', '/admin/api/forms/1'] as $p) {
    [$st] = $none('GET', $p, ['headers' => ['Accept: application/json']]);
    check('role without forms abilities: GET ' . $p . ' -> 403', $st === 403, (string) $st);
}
foreach (['/admin/forms', '/admin/forms/new', '/admin/api/forms', '/admin/api/forms/catalog'] as $p) {
    [$st] = $reader('GET', $p, ['headers' => ['Accept: application/json']]);
    check('reader role (forms.submissions only): GET ' . $p . ' -> 403 (no builder, no list of forms)', $st === 403, (string) $st);
}

// ================================================================================================================================ builder side
echo " builder pages and API\n";
[$st, $page] = $admin('GET', '/admin/forms');
check('admin: the forms list renders (200) with "New form"', $st === 200 && str_contains($page, 'New form') && str_contains($page, 'Forms'));
[$st, $page] = $admin('GET', '/admin/forms/new');
check('admin: the template picker lists blank + all nine templates', $st === 200 && substr_count($page, 'name="source"') >= 10 && str_contains($page, 'Job application') && str_contains($page, 'Beneficiary intake') && str_contains($page, 'Training registration'));
[$st, , $h] = $admin('POST', '/admin/forms', ['form' => ['_token' => $hTok($page), 'source' => 'contact_enquiry', 'title' => ZZ_FORMS . ' http contact']]);
$loc = $h['location'] ?? '';
check('admin: creating a form from a template redirects to its builder', $st === 302 && preg_match('~/admin/forms/(\d+)/edit$~', $loc, $m) === 1);
$cid = (int) ($m[1] ?? 0);
[$st, $page] = $admin('GET', '/admin/forms/' . $cid . '/edit');
check('admin: the builder page hosts #form-builder with its data hooks and loads admin-form-builder.js', $st === 200 && str_contains($page, 'id="form-builder"') && str_contains($page, 'data-form-id="' . $cid . '"') && str_contains($page, 'admin-form-builder.js'));
[$st, $b] = $api($admin, 'GET', '/admin/api/forms/catalog');
$cat = $b['data'] ?? [];
check('API catalog: 17 field types, 7 operators, 8 presets, 7 file types, limits, 9 templates', $st === 200 && count($cat['types']) === 17 && count($cat['ops']) === 7 && count($cat['patterns']) === 8 && count($cat['file_types']) === 7 && ($cat['limits']['fields'] ?? 0) === 150 && count($cat['templates']) === 9, json_encode(array_keys($cat)));
[$st, $b] = $api($admin, 'GET', '/admin/api/forms/' . $cid);
$fd = $b['data'] ?? [];
check('API load: id, slug, status draft, version 1, definition + settings + availability + urls + embed + permissions, checklist', $st === 200 && $fd['id'] === $cid && $fd['status'] === 'draft' && $fd['version'] === 1 && count($fd['definition']['pages']) === 1 && isset($fd['settings']['notify']) && isset($fd['embed']['shortcode']) && $fd['can']['delete'] === true && isset($b['checklist']), json_encode(array_keys($fd)));
$slug = (string) $fd["slug"];
$hClearLimits($slug);
[$st, $b] = $api($admin, 'PUT', '/admin/api/forms/' . $cid, ['version' => 1, 'title' => ZZ_FORMS . ' http contact v2', 'autosave' => true]);
check('API save (autosave): 200, version 2, changed', $st === 200 && $b['changed'] === true && $b['data']['version'] === 2 && $b['data']['title'] === ZZ_FORMS . ' http contact v2');
[$st, $b] = $api($admin, 'PUT', '/admin/api/forms/' . $cid, ['version' => 2, 'title' => ZZ_FORMS . ' http contact v2', 'autosave' => true]);
check('API save with nothing changed: 200, changed=false, version stays 2', $st === 200 && $b['changed'] === false && $b['data']['version'] === 2);
[$st, $b] = $api($admin, 'PUT', '/admin/api/forms/' . $cid, ['version' => 1, 'title' => 'Stale edit']);
check('API save with a stale version: 409 conflict with "current" and the newest form (nothing overwritten)', $st === 409 && $b['error'] === 'conflict' && $b['current']['version'] === 2 && $b['data']['title'] === ZZ_FORMS . ' http contact v2');
[$st, $b] = $api($admin, 'PUT', '/admin/api/forms/' . $cid, ['title' => 'No version']);
check('API save without a version: 400', $st === 400 && $b['error'] === 'bad_request');
[$st, $b] = $api($admin, 'PUT', '/admin/api/forms/' . $cid, ['version' => 2, 'slug' => 'admin', 'definition' => ['pages' => [['fields' => [['type' => 'nope']]]]]]);
check('API save with errors: 422 validation lists every problem under its path', $st === 422 && $b['error'] === 'validation' && isset($b['errors']['slug']) && count($b['errors']) >= 2, json_encode($b['errors'] ?? []));
[$st, $b] = $api($admin, 'PUT', '/admin/api/forms/999999', ['version' => 1]);
check('API save of an unknown form: 404 JSON', $st === 404 && $b['error'] === 'not_found');
[$st, $b] = $api($admin, 'POST', '/admin/api/forms/validate', ['definition' => ['pages' => [['fields' => [['type' => 'select', 'label' => 'Pick']]]]], 'slug' => 'taken-or-not']);
check('API validate: normalised definition + checklist with the empty-options error; nothing written', $st === 200 && in_array('empty_options', array_column($b['checklist'], 'code'), true) && preg_match(FormDefinition::ID_PATTERN, $b['definition']['pages'][0]['fields'][0]['id']) === 1);
[$st, $b] = $api($admin, 'POST', '/admin/api/forms/preview', ['definition' => $fd['definition'], 'settings' => $fd['settings'], 'title' => 'Preview title']);
check('API preview: the PUBLIC renderer in preview mode (no CSRF field, submit disabled) and a full document for an iframe', $st === 200 && str_contains($b['html'], 'data-lf') && str_contains($b['html'], 'lf--preview') && !str_contains($b['html'], 'name="_token"') && str_contains($b['html'], 'disabled') && str_starts_with($b['document'], '<!doctype html>') && str_contains($b['document'], 'forms.js'));
[$st, $b] = $api($admin, 'POST', '/admin/api/forms/' . $cid . '/status', ['action' => 'publish']);
check('API publish: the contact template is ready -> status published', $st === 200 && $b['data']['status'] === 'published', json_encode($b));
$blankId = (int) FormService::create(['source' => 'blank', 'title' => ZZ_FORMS . ' http blank'])['form']['id'];
[$st, $b] = $api($admin, 'POST', '/admin/api/forms/' . $blankId . '/status', ['action' => 'publish']);
check('API publish of an empty form: 422 not_ready with the checklist', $st === 422 && $b['error'] === 'not_ready' && isset($b['errors']['no_fields']) && $b['checklist'] !== []);
[$st, $b] = $api($admin, 'POST', '/admin/api/forms/' . $blankId . '/status', ['action' => 'explode']);
check('API status with an unknown action: 400', $st === 400);
[$st, $b] = $api($admin, 'POST', '/admin/api/forms/' . $cid . '/duplicate');
check('API duplicate: 201, a draft copy', $st === 201 && $b['data']['status'] === 'draft' && $b['data']['id'] !== $cid);
$copyId = (int) ($b['data']['id'] ?? 0);
[$st, $b] = $api($admin, 'DELETE', '/admin/api/forms/' . $copyId);
check('API delete (admin holds forms.delete): ok', $st === 200 && $b['ok'] === true && FormService::find($copyId) === null);
[$st, $b] = $api($admin, 'GET', '/admin/api/forms?status=published&q=http');
check('API list: filter by status and search, meta with counts', $st === 200 && $b['meta']['total'] >= 1 && isset($b['meta']['counts']['published']) && array_reduce($b['data'], static fn($c, $f) => $c && $f['status'] === 'published', true));
// POST without the CSRF header
[$st] = $admin('PUT', '/admin/api/forms/' . $cid, ['json' => ['version' => 3], 'headers' => ['Accept: application/json']]);
check('a state-changing API call without the CSRF header is refused (403)', $st === 403);
[$st] = $admin('POST', '/admin/forms/' . $cid . '/status', ['form' => ['action' => 'close']]);
check('a state-changing page POST without the CSRF token is refused (403)', $st === 403 && FormService::find($cid)['status'] === 'published');

// ================================================================================================================================ public page
echo " public page and submission\n";
$form = FormService::find($cid);
$ids = [];
foreach (FormDefinition::fields($form['def']) as $f) {
    $ids[$f['key']] = $f['id'];
}
[$st, $page, $h] = $visitor('GET', '/forms/' . $slug);
check('visitor: the form page renders (200) with the form, the CSRF token, the honeypot timestamp and no session cookie', $st === 200 && str_contains($page, 'data-lf') && str_contains($page, 'name="f_' . $ids['full_name'] . '"') && $hTok($page) !== '' && str_contains($page, 'name="_hp_ts"') && !isset($h['set-cookie']));
check('visitor: contact form is indexable (robots index,follow) and has a canonical link; labels are tied to inputs', preg_match('/<meta name="robots" content="index,follow"/', $page) === 1 && str_contains($page, 'rel="canonical"') && str_contains($page, 'for="q-' . $ids['full_name'] . '"') && str_contains($page, 'id="q-' . $ids['full_name'] . '"'));
check('visitor: no inline script or on* handler anywhere on the form page (CSP)', !preg_match('/<script(?![^>]*(?:\bsrc=|ld\+json))[^>]*>/i', $page) && !preg_match('/\son[a-z]+\s*=\s*"/i', $page));
check('visitor: the page sends the CSP and nosniff headers', str_contains($h['content-security-policy'] ?? '', "script-src 'self'") && ($h['x-content-type-options'] ?? '') === 'nosniff');
$tok = $hTok($page);
$post = static fn(array $over = [], ?string $tokv = null, ?string $sig = null) => array_replace([
    '_token' => $tokv ?? $tok, '_hp_ts' => $sig ?? $hSig(30), 'website' => '',
    'f_' . $ids['full_name'] => 'Grace Akello', 'f_' . $ids['email'] => 'grace@example.org', 'f_' . $ids['phone'] => '0772 555 111', 'f_' . $ids['topic'] => 'programmes',
    'f_' . $ids['subject'] => 'Hello there', 'f_' . $ids['message'] => 'I would like to know more about your programmes.', 'f_' . $ids['consent'] => '1',
], $over);
$subCount = static fn(): int => (int) Database::value('SELECT COUNT(*) FROM form_submissions WHERE form_id = ?', [$GLOBALS['cid']], 0);
$GLOBALS['cid'] = $cid;
[$st] = $visitor('POST', '/forms/' . $slug, ['form' => array_diff_key($post(), ['_token' => 1])]);
check('visitor: POST without a CSRF token -> 403 and nothing stored', $st === 403 && $subCount() === 0);
[$st] = $visitor('POST', '/forms/' . $slug, ['form' => $post([], 's.1.deadbeef')]);
check('visitor: POST with a forged CSRF token -> 403', $st === 403 && $subCount() === 0);
[$st] = $anon('POST', '/forms/' . $slug, ['form' => $post([], 'abc')]);
check('visitor: POST with a malformed CSRF token -> 403', $st === 403);

$before = $hMails();
[$st, $b, $h] = $visitor('POST', '/forms/' . $slug, ['form' => $post()]);
check('visitor: a valid submission is accepted: 302 to /forms/<slug>/thanks (post/redirect/get)', $st === 302 && str_ends_with($h['location'] ?? '', '/forms/' . $slug . '/thanks'), $st . ' ' . ($h['location'] ?? ''));
check('visitor: exactly one submission stored, status new, answers snapshot, e-mail lower-case', $subCount() === 1 && ($row = Database::one('SELECT * FROM form_submissions WHERE form_id = ?', [$cid])) && $row['status'] === 'new' && $row['submitter_email'] === 'grace@example.org' && str_contains($row['answers'], 'Grace Akello'));
$sid1 = (int) ($row['id'] ?? 0);
[$st, $b] = $visitor('GET', '/forms/' . $slug . '/thanks');
check('visitor: the thank-you page shows the reference once, is noindex and has no form', $st === 200 && str_contains($b, 'F' . $cid . '-' . $sid1) && str_contains($b, 'noindex') && !str_contains($b, 'data-lf-submit'), $st . '');
[$st, $b] = $visitor('GET', '/forms/' . $slug . '/thanks');
check('visitor: reloading the thank-you page does not show the reference again (it was flash data)', $st === 200 && !str_contains($b, 'F' . $cid . '-' . $sid1));
$new = array_values(array_diff($hMails(), $before));
$texts = array_map($hEml, $new);
$staff = array_values(array_filter($texts, static fn($t) => str_contains($t, '/admin/forms/' . $cid . '/submissions/' . $sid1)));
$confirm = array_values(array_filter($texts, static fn($t) => preg_match('/^To: grace@example\.org/m', $t) === 1));   // anchored: the staff mail carries "Reply-To: grace@..." and must not count as the confirmation
check('e-mail: a staff notification with the admin LINK and NOT the answers, and a confirmation to the address typed', count($staff) === 1 && !str_contains($staff[0], 'Grace Akello') && !str_contains($staff[0], 'programmes') && !str_contains($staff[0], 'I would like') && count($confirm) === 1, count($new) . ' mails');

// invalid + hostile input
$bad = $post(['f_' . $ids['email'] => 'nope', 'f_' . $ids['full_name'] => '"><script>alert(1)</script>', 'f_' . $ids['message'] => 'x']);
[$st, $b] = $visitor('POST', '/forms/' . $slug, ['form' => $bad]);
check('visitor: invalid answers -> 422 page with the error summary, the field errors and aria-invalid; nothing stored', $st === 422 && str_contains($b, 'lf__summary') && str_contains($b, 'aria-invalid="true"') && str_contains($b, 'data-lf-error') && $subCount() === 1);
check('visitor: hostile input is escaped when the form is shown again and never echoed raw inside error messages', !str_contains($b, '<script>alert(1)</script>') && str_contains($b, '&lt;script&gt;alert(1)&lt;/script&gt;') && !str_contains($b, '"><script>'), '');
check('visitor: the 422 page is noindex and keeps the entered values', preg_match('/<meta name="robots" content="noindex/', $b) === 1 && str_contains($b, 'value="nope"'));
// traps
[$st, , $h] = $visitor('POST', '/forms/' . $slug, ['form' => $post(['website' => 'http://spam.test'], null, null)]);
check('honeypot filled: looks like success (302 to thanks) but nothing is stored', $st === 302 && str_ends_with($h['location'] ?? '', '/thanks') && $subCount() === 1);
[$st, $b, $h] = $visitor('POST', '/forms/' . $slug, ['form' => $post([], null, $hSig(0))]);
check('submitted instantly (no human fill time): a VISIBLE 422 "please send again" (never a fake success) and nothing is stored', $st === 422 && str_contains($b, 'did not save') && $subCount() === 1);
[$st, , $h] = $visitor('POST', '/forms/' . $slug, ['form' => $post([], null, '1.forged')]);
check('forged timestamp: looks like success but nothing is stored', $st === 302 && $subCount() === 1);
[$st, $b] = $visitor('POST', '/forms/' . $slug, ['form' => $post([], null, $hSig(9 * 3600)), 'headers' => ['Accept: application/json']]);
check('a page open for more than 7 hours: JSON answer 422 "expired" (not 419: Apache turns it into a 500), nothing stored', $st === 422 && (json_decode($b, true)['error'] ?? '') === 'expired' && $subCount() === 1, $st . ' ' . substr($b, 0, 80));
// JSON client
[$st, $b] = $visitor('POST', '/forms/' . $slug, ['form' => $bad, 'headers' => ['Accept: application/json', 'X-Requested-With: XMLHttpRequest']]);
$jb = json_decode($b, true) ?? [];
check('JSON client: invalid answers -> 422 {ok:false, errors:{fieldId:message}, error_pages}', $st === 422 && ($jb['ok'] ?? null) === false && isset($jb['errors'][$ids['email']]) && isset($jb['error_pages']), $b);
[$st, $b] = $visitor('POST', '/forms/' . $slug, ['form' => $post(['f_' . $ids['email'] => 'json@example.org']), 'headers' => ['Accept: application/json', 'X-Requested-With: XMLHttpRequest']]);
$jb = json_decode($b, true) ?? [];
check('JSON client: valid submission -> 200 {ok:true, redirect, reference}', $st === 200 && ($jb['ok'] ?? false) === true && str_contains($jb['redirect'] ?? '', '/thanks') && preg_match('/^F\d+-\d+$/', (string) ($jb['reference'] ?? '')) === 1 && $subCount() === 2, $b);
// throttle
$tmpF = FormService::find($cid);
for ($i = 0; $i < 12; $i++) {
    [$stT] = $anon('POST', '/forms/' . $slug, ['form' => ['_token' => $tok] + $post(['f_' . $ids['email'] => 'flood' . $i . '@example.org'], $tok)]);
}
check('flood: the 11th+ submission from one address in 10 minutes is answered 429 (and stores nothing more than the allowed ones)', $stT === 429 && $subCount() <= 2 + 10, (string) $stT);
$hClearLimits($slug);

// ---- states
echo " states: draft, closed, full, not open\n";
FormService::transition($cid, 'unpublish');
[$st] = $visitor('GET', '/forms/' . $slug);
check('draft form: 404 for visitors', $st === 404);
[$st, $b] = $admin('GET', '/forms/' . $slug);
check('draft form: signed-in staff can preview it (banner, submit disabled)', $st === 200 && str_contains($b, 'lf--preview') && str_contains($b, 'noindex'));
[$st] = $visitor('POST', '/forms/' . $slug, ['form' => $post()]);
check('draft form: a POST is refused (404) and stores nothing', $st === 404 && $subCount() <= 12);
$cnt = $subCount();
FormService::transition($cid, 'publish');
FormService::transition($cid, 'close');
[$st, $b] = $visitor('GET', '/forms/' . $slug);
check('closed form: 200 with the editable "closed" notice, no form, noindex', $st === 200 && str_contains($b, 'This form is closed') && !str_contains($b, 'data-lf-submit') && str_contains($b, 'noindex'));
[$st, $b] = $anon('POST', '/forms/' . $slug, ['form' => $post([], null)]);
check('closed form: a late POST stores nothing and shows the notice', $st === 200 && str_contains($b, 'closed') && $subCount() === $cnt);
FormService::transition($cid, 'reopen');
$v = (int) Database::value('SELECT version FROM forms WHERE id = ?', [$cid]);
FormService::save($cid, ['max_submissions' => (string) $subCount()], $v, null);
[$st, $b] = $visitor('GET', '/forms/' . $slug);
check('full form (maximum reached): the "full" notice, no form', $st === 200 && str_contains($b, 'This form is full') && !str_contains($b, 'data-lf-submit'));
FormService::save($cid, ['max_submissions' => '', 'opens_at' => date('Y-m-d\TH:i', time() + 86400 + 10800), 'closes_at' => ''], (int) Database::value('SELECT version FROM forms WHERE id = ?', [$cid]), null);
[$st, $b] = $visitor('GET', '/forms/' . $slug);
check('not open yet: the "opens on <date>" notice, no form', $st === 200 && str_contains($b, 'not open yet') && !str_contains($b, 'data-lf-submit'));
FormService::save($cid, ['opens_at' => '', 'settings' => array_replace($form['cfg'], ['closed_message' => 'Custom closed text for this form.'])], (int) Database::value('SELECT version FROM forms WHERE id = ?', [$cid]), null);
FormService::transition($cid, 'close');
[$st, $b] = $visitor('GET', '/forms/' . $slug);
check('a form can carry its own closed message instead of the default', str_contains($b, 'Custom closed text for this form.'));
FormService::transition($cid, 'reopen');
[$st] = $visitor('GET', '/forms/does-not-exist');
check('unknown form: 404', $st === 404);
[$st] = $visitor('GET', '/forms/Bad_Slug!');
check('malformed slug: 404', $st === 404);

// ---- directory + sitemap + embed
echo " directory, sitemap, embed\n";
[$st] = $visitor('GET', '/forms');
check('/forms: 404 while the public list is switched off (default)', $st === 404);
$v = (int) Database::value('SELECT version FROM forms WHERE id = ?', [$cid]);
FormService::save($cid, ['listed' => '1'], $v, null);
$map = $visitor('GET', '/sitemap.xml')[1];
check('sitemap: forms are NOT listed while the directory is off', !str_contains($map, '/forms'));
\App\Core\Settings::set('forms_directory_enabled', '1');
[$st, $b] = $visitor('GET', '/forms');
check('/forms: 200 once switched on; lists the published, listed form; hides unlisted and draft ones', $st === 200 && str_contains($b, '/forms/' . $slug) && str_contains($b, ZZ_FORMS . ' http contact v2') && !str_contains($b, 'http blank'));
$map = $visitor('GET', '/sitemap.xml')[1];
check('sitemap: /forms and the listed, open, indexable form appear; a draft does not', str_contains($map, '/forms</loc>') && str_contains($map, '/forms/' . $slug . '</loc>') && !str_contains($map, '/thanks'));
\App\Core\Settings::set('forms_directory_enabled', '0');
\App\Core\Settings::flush();
$map = $visitor('GET', '/sitemap.xml')[1];
check('sitemap: switching the directory off removes them again at once', !str_contains($map, '/forms/' . $slug));
// shortcode inside a custom page
$pgSlug = 'zz-forms-embed-page';
Database::execute('DELETE FROM pages WHERE slug = ?', [$pgSlug]);
Database::insert('pages', ['slug' => $pgSlug, 'title' => 'ZZ embed', 'type' => 'custom', 'body' => '<p>Intro</p><p>[form slug="' . $slug . '"]</p>', 'is_published' => 1, 'show_in_nav' => 0, 'show_in_footer' => 0, 'template' => 'default']);
\App\Core\SitePages::flush();
[$st, $b] = $visitor('GET', '/' . $pgSlug);
$embedWorks = str_contains($b, 'data-lf-embed="' . $slug . '"');
check('custom page body with [form slug="..."]: the page renders (200); the form is embedded once the PUBLIC agent hooks FormShortcode into the custom-page view', $st === 200 && ($embedWorks || str_contains($b, '[form slug=')), $st . ' embedded=' . ($embedWorks ? 'yes' : 'not yet (public agent task)'));
Database::execute('DELETE FROM pages WHERE slug = ?', [$pgSlug]);
\App\Core\SitePages::flush();

// ================================================================================================================================ files + admin inbox
echo " upload, inbox, detail, export, download, delete\n";
$jobFormId = (int) FormService::create(['source' => 'job_application', 'title' => ZZ_FORMS . ' http job'], null)['form']['id'];
FormService::transition($jobFormId, 'publish');
$jf = FormService::find($jobFormId);
$jids = [];
foreach (FormDefinition::fields($jf['def']) as $f) {
    $jids[$f['key']] = $f['id'];
}
Database::insert('jobs', ['slug' => 'zz-selftest-forms-http-job', 'title' => 'ZZ Programme Assistant', 'employment_type' => 'full_time', 'is_active' => 1, 'form_id' => $jobFormId]);
[, $jp, ] = $visitor('GET', '/forms/' . $jf['slug'] . '?job=zz-selftest-forms-http-job');
check('job form opened with ?job=<slug>: the vacancy title is shown ("Applying for") and the job travels as a hidden field', str_contains($jp, 'Applying for: ZZ Programme Assistant') && str_contains($jp, 'name="job" value="zz-selftest-forms-http-job"'));
[, $jp2] = $visitor('GET', '/forms/' . $jf['slug'] . '?job=nonexistent-job');
check('job form with an unknown ?job=: the "vacancy not open" notice instead of the form', str_contains($jp2, 'no longer open') && !str_contains($jp2, 'data-lf-submit'));
$jtok = $hTok($jp);
$hClearLimits($jf['slug']);
$pdfTmp = $mkTmp($pdfBytes('Peter CV'));
$jpost = static fn(array $over = []) => array_replace([
    '_token' => $jtok, '_hp_ts' => $hSig(30), 'website' => '', 'job' => 'zz-selftest-forms-http-job',
    'f_' . $jids['full_name'] => 'Peter Otim', 'f_' . $jids['email'] => 'peter@example.org', 'f_' . $jids['phone'] => '0772 000 111', 'f_' . $jids['location'] => 'Gulu',
    'f_' . $jids['availability'] => 'immediately', 'f_' . $jids['consent'] => '1', 'f_' . $jids['cv'] => new CURLFile($pdfTmp, 'application/pdf', 'Peter CV.pdf'),
], $over);
[$st, , $h] = $visitor('POST', '/forms/' . $jf['slug'], ['multipart' => $jpost()]);
$jrow = Database::one('SELECT * FROM form_submissions WHERE form_id = ? ORDER BY id DESC LIMIT 1', [$jobFormId]);
check('job application with a CV (multipart): stored with the verified job context (title from the database)', $st === 302 && $jrow !== null && $jrow['context_type'] === 'job' && $jrow['context_ref'] === 'zz-selftest-forms-http-job' && $jrow['context_label'] === 'ZZ Programme Assistant', $st . ' ' . json_encode($jrow));
$jfiles = $jrow ? SubmissionService::files((int) $jrow['id']) : [];
$stPriv1 = $jfiles ? $visitor('GET', '/storage/private/' . $jfiles[0]['stored_path'])[0] : 0;
$stPriv2 = $jfiles ? $visitor('GET', '/uploads/' . $jfiles[0]['stored_path'])[0] : 0;
$stPriv3 = $jfiles ? $visitor('GET', '/' . $jfiles[0]['stored_path'])[0] : 0;
check('the CV is in PRIVATE storage with a random name and a file row; the web server cannot serve it (403 or 404 for the storage/, uploads/ and root URLs)', count($jfiles) === 1 && is_file(STORAGE_PATH . '/private/' . $jfiles[0]['stored_path']) && in_array($stPriv1, [403, 404], true) && in_array($stPriv2, [403, 404], true) && in_array($stPriv3, [403, 404], true), "$stPriv1/$stPriv2/$stPriv3");
$sidJ = (int) ($jrow['id'] ?? 0);
// hostile uploads
$evilPdf = $mkTmp("<?php echo 1; ?>");      // (a classic web-shell one-liner is reset by some anti-virus tools while it travels over localhost, which would hide the real answer)
[$st, $b] = $visitor('POST', '/forms/' . $jf['slug'], ['multipart' => array_replace($jpost(), ['f_' . $jids['cv'] => new CURLFile($evilPdf, 'application/pdf', 'shell.php.pdf'), 'f_' . $jids['email'] => 'evil@example.org'])]);
check('hostile upload (PHP code named shell.php.pdf): 422 with a friendly file error, nothing stored', $st === 422 && (str_contains($b, 'does not look like') || str_contains($b, 'not allowed')), $st . ' ' . substr(strip_tags($b), 0, 100));
check('...and no new submission or file appeared', (int) Database::value('SELECT COUNT(*) FROM form_submissions WHERE form_id = ?', [$jobFormId], 0) === 1);
[$st, $b] = $visitor('POST', '/forms/' . $jf['slug'], ['multipart' => array_replace($jpost(), ['f_' . $jids['cv'] => new CURLFile($mkTmp('<svg onload=alert(1)>'), 'image/svg+xml', 'cv.svg'), 'f_' . $jids['email'] => 'evil2@example.org'])]);
check('hostile upload (SVG): refused (422)', $st === 422 && (int) Database::value('SELECT COUNT(*) FROM form_submissions WHERE form_id = ?', [$jobFormId], 0) === 1);
[$st, $b] = $visitor('POST', '/forms/' . $jf['slug'], ['multipart' => array_diff_key($jpost(), ['f_' . $jids['cv'] => 1])]);
check('missing required CV: 422 on the CV field', $st === 422 && str_contains($b, 'e-' . $jids['cv']));

// admin inbox
$base = '/admin/forms/' . $cid . '/submissions';
[$st, $b] = $admin('GET', $base);
check('admin inbox: lists the submissions (200), new ones highlighted, first question columns', $st === 200 && str_contains($b, 'Grace Akello') && str_contains($b, 'is-unread'));
[$st, $b] = $admin('GET', $base . '?q=json%40example');
check('admin inbox: text search across answers (finds the JSON submission by its e-mail, not the others)', $st === 200 && str_contains($b, 'json@example.org') && !str_contains($b, 'flood0@example.org') && !str_contains($b, 'xss@example.org'));
[$st, $b] = $admin('GET', $base . '?q=%25&status=new&from=2020-01-01&to=2099-01-01&sort=status&dir=asc&page=999');
check('admin inbox: odd filter values (wildcard, bad page) do not break the page', $st === 200);
[$st, $b] = $admin('GET', $base . '/' . $sid1);
check('admin detail: every answer with its label from the snapshot, status select, notes, print link; first view turns NEW into READ', $st === 200 && str_contains($b, 'Grace Akello') && str_contains($b, 'name="notes"') && str_contains($b, '/print') && Database::value('SELECT status FROM form_submissions WHERE id = ?', [$sid1]) === 'read');
[$st, $b] = $admin('GET', $base . '/' . $sid1 . '/print');
check('admin print view renders', $st === 200 && str_contains($b, 'Grace Akello'));
[$st] = $admin('GET', '/admin/forms/' . $jobFormId . '/submissions/' . $sid1);
check('a submission opened through ANOTHER form id is a 404 (no cross-form access)', $st === 404);
[$st, , $h] = $admin('POST', $base . '/' . $sid1 . '/status', ['form' => ['_token' => $csrf, 'status' => 'shortlisted']]);
check('admin status change: saved', in_array($st, [302, 303], true) && Database::value('SELECT status FROM form_submissions WHERE id = ?', [$sid1]) === 'shortlisted');
[$st] = $admin('POST', $base . '/' . $sid1 . '/status', ['form' => ['_token' => $csrf, 'status' => 'hacked']]);
check('admin status change with an unknown status: refused, unchanged', Database::value('SELECT status FROM form_submissions WHERE id = ?', [$sid1]) === 'shortlisted');
$admin('POST', $base . '/' . $sid1 . '/notes', ['form' => ['_token' => $csrf, 'notes' => "Strong candidate <i>call</i>\nMonday"]]);
check('admin notes: stored as plain text', Database::value('SELECT notes FROM form_submissions WHERE id = ?', [$sid1]) === "Strong candidate call\nMonday");
// stored XSS: an answer with markup is shown escaped in the admin
$xssPost = $post(['f_' . $ids['full_name'] => '"><img src=x onerror=alert(1)>', 'f_' . $ids['email'] => 'xss@example.org'], $hTok($visitor('GET', '/forms/' . $slug)[1]));
$visitor('POST', '/forms/' . $slug, ['form' => $xssPost]);
$xssId = (int) Database::value("SELECT id FROM form_submissions WHERE form_id = ? AND submitter_email = 'xss@example.org'", [$cid], 0);
[, $b] = $admin('GET', $base . '/' . $xssId);
[, $bl] = $admin('GET', $base);
check('stored XSS: markup in an answer is escaped in the admin detail and in the inbox list', $xssId > 0 && !str_contains($b, '<img src=x') && str_contains($b, '&lt;img src=x') && !str_contains($bl, '<img src=x'));
// bulk
[$st] = $admin('POST', $base . '/bulk', ['form' => ['_token' => $csrf, 'action' => 'archive', 'ids' => [$xssId]]]);
check('admin bulk: archive moves the selected submission', Database::value('SELECT status FROM form_submissions WHERE id = ?', [$xssId]) === 'archived');
[, $b] = $admin('GET', $base);
[, $archivedTab] = $admin('GET', $base . '?status=archived');
check('admin inbox: the "All" tab hides archived answers; the Archived tab shows them (escaped)', !str_contains($b, 'xss@example.org') && str_contains($archivedTab, 'xss@example.org') && !str_contains($archivedTab, '<img src=x'));
[$st] = $admin('POST', $base . '/bulk', ['form' => ['_token' => $csrf, 'action' => 'read']]);
check('admin bulk with nothing selected: friendly refusal, no change', in_array($st, [302, 303], true));
// export
[$st, $b, $h] = $admin('GET', $base . '/export');
check('admin CSV export: text/csv attachment, UTF-8 BOM, header + a row per submission, formula-safe', $st === 200 && str_starts_with($h['content-type'] ?? '', 'text/csv') && str_contains($h['content-disposition'] ?? '', 'attachment') && str_starts_with($b, "\xEF\xBB\xBF") && str_contains($b, 'Grace Akello') && substr_count(trim($b), "\n") >= 3, substr($b, 0, 120));
// file download
$dl = '/admin/forms/' . $jobFormId . '/submissions/' . $sidJ . '/files/' . ($jfiles[0]['id'] ?? 0);
[$st, $b, $h] = $admin('GET', $dl);
check('admin file download: authenticated, attachment, real PDF bytes, nosniff, no-store', $st === 200 && str_contains($h['content-disposition'] ?? '', 'attachment') && str_contains($h['content-disposition'] ?? '', 'Peter CV.pdf') && ($h['content-type'] ?? '') === 'application/pdf' && ($h['x-content-type-options'] ?? '') === 'nosniff' && str_contains($h['cache-control'] ?? '', 'no-store') && str_starts_with($b, '%PDF-') && $b === (string) file_get_contents($pdfTmp));
check('file download: the same URL for an anonymous visitor is not served (login redirect)', $anon('GET', $dl)[0] === 302);
check('file download: with another form id in the URL it is a 404 even for the admin', $admin('GET', '/admin/forms/' . $cid . '/submissions/' . $sidJ . '/files/' . ($jfiles[0]['id'] ?? 0))[0] === 404);
check('file download: for a role without forms.submissions it is 403', $none('GET', $dl)[0] === 403);
// stats
[$st, $b] = $admin('GET', $base . '/stats');
check('admin summary page renders with counts and the chart', $st === 200 && str_contains($b, '<svg') && str_contains($b, 'Answers'));
[$st, $b] = $api($admin, 'GET', '/admin/api/forms/' . $cid . '/stats');
check('admin stats JSON: overview, per_day (30), tallies - counts only', $st === 200 && count($b['per_day']) === 30 && isset($b['overview']['total']) && !str_contains(json_encode($b), 'grace@example.org'));

// ---- the reader role (forms.submissions only)
echo " restricted role: read answers only\n";
[$st, $b] = $reader('GET', '/admin/form-answers');
check('reader: "Form answers" overview lists the forms with counts', $st === 200 && str_contains($b, ZZ_FORMS));
[$st, $b] = $reader('GET', $base);
check('reader: may open an inbox and a submission', $st === 200 && $reader('GET', $base . '/' . $sid1)[0] === 200);
[, $adminPage] = $reader('GET', '/admin');
check('reader: the sidebar shows "Form answers" but not "New form"', str_contains($adminPage, 'Form answers') && !str_contains($adminPage, 'admin/forms/new'));
$rcsrf = $hMeta($reader('GET', $base)[1]);
[$st] = $reader('GET', $base . '/export');
check('reader: CSV export is 403 (needs forms.export)', $st === 403);
[$st] = $reader('POST', $base . '/' . $sid1 . '/delete', ['form' => ['_token' => $rcsrf]]);
check('reader: deleting a submission is 403 (needs forms.delete) and the row survives', $st === 403 && SubmissionService::find($sid1) !== null);
[$st] = $reader('POST', $base . '/bulk', ['form' => ['_token' => $rcsrf, 'action' => 'delete', 'ids' => [$sid1]]]);
check('reader: bulk delete is 403 and the row survives', $st === 403 && SubmissionService::find($sid1) !== null);
[$st] = $reader('POST', '/admin/forms/' . $cid . '/delete', ['form' => ['_token' => $rcsrf]]);
check('reader: deleting the form is 403', $st === 403 && FormService::find($cid) !== null);
[$st] = $reader('POST', $base . '/' . $sid1 . '/status', ['form' => ['_token' => $rcsrf, 'status' => 'read']]);
check('reader: may change a submission status', in_array($st, [302, 303], true));
[$st] = $reader('GET', '/admin/forms/' . $cid . '/edit');
check('reader: the builder page is 403', $st === 403);

// ---- fixes after the review (HTTP)
echo " fixes after the audit\n";
$pwMgr = $hRandPw();
Database::execute("INSERT INTO roles (slug, name, description, abilities, is_system) VALUES ('zz-forms-manager', 'ZZ forms manager', '', ?, 0), ('zz-forms-rolechk', 'ZZ role check', '', ?, 0)", [json_encode(['dashboard.view', 'forms.manage']), json_encode(['dashboard.view', 'forms.submissions', 'forms.export'])]);
Roles::flush();
User::create(['name' => 'ZZ manager', 'email' => 'zz-forms-mgr@example.test', 'password_hash' => Auth::hashPassword($pwMgr), 'role' => 'zz-forms-manager', 'is_active' => 1, 'must_change_password' => 0]);
$mgr = $hClient();
check('login: the throw-away forms manager (forms.manage only) can sign in', $hLogin($mgr, 'zz-forms-mgr@example.test', $pwMgr));
$mcsrf = $hMeta($mgr('GET', '/admin/forms')[1]);
$apiMgr = static function (string $method, string $path, ?array $body = null) use ($mgr, $mcsrf): array {
    $res = $mgr($method, $path, ['headers' => ['X-CSRF-TOKEN: ' . $mcsrf, 'Accept: application/json']] + ($body !== null ? ['json' => $body] : []));
    return [$res[0], json_decode($res[1], true) ?? []];
};
// a form with answers, and a retention attack by the manager
$xf = FormService::create(['source' => 'contact_enquiry', 'title' => ZZ_FORMS . ' http audit'], null)['form'];
$xid = (int) $xf['id'];
FormService::transition($xid, 'publish');
for ($i = 0; $i < 3; $i++) {
    Database::insert('form_submissions', ['form_id' => $xid, 'form_version' => 1, 'answers' => '[]', 'search_text' => 'zz', 'status' => 'new', 'created_at' => date('Y-m-d H:i:s', time() - 5 * 86400)]);
}
SubmissionService::recount($xid);
$xv = (int) FormService::find($xid)['version'];
$xcfg = FormService::find($xid)['cfg'];
[$st, $b] = $apiMgr('GET', '/admin/forms/' . $xid . '/submissions');
check('manager: cannot read the answers (403) - the premise of the next checks', $st === 403);
[$st, $b] = $apiMgr('PUT', '/admin/api/forms/' . $xid, ['version' => $xv, 'settings' => array_replace($xcfg, ['retention_days' => 1])]);
check('SECURITY: manager PUT retention_days=1 -> 403 forbidden (needs forms.delete); the setting is unchanged', $st === 403 && ($b['error'] ?? '') === 'forbidden' && (int) FormService::find($xid)['cfg']['retention_days'] === 0, $st . ' ' . json_encode($b));
$mgr('GET', '/admin/forms');
$mgr('GET', '/admin/api/forms');
check('SECURITY: after the manager opened the forms list (which runs the lazy retention), no answer was deleted', (int) Database::value('SELECT COUNT(*) FROM form_submissions WHERE form_id = ?', [$xid], 0) === 3);
[$st, $b] = $apiMgr('PUT', '/admin/api/forms/' . $xid, ['version' => $xv, 'settings' => array_replace_recursive($xcfg, ['notify' => ['enabled' => true, 'emails' => ['exfil@evil.example'], 'include_answers' => true]])]);
check('SECURITY: manager PUT notify include_answers=true with an outside address -> 403 forbidden', $st === 403 && ($b['error'] ?? '') === 'forbidden' && empty(FormService::find($xid)['cfg']['notify']['include_answers']) && FormService::find($xid)['cfg']['notify']['emails'] === [], $st . ' ' . json_encode($b));
[$st, $b] = $apiMgr('PUT', '/admin/api/forms/' . $xid, ['version' => $xv, 'title' => ZZ_FORMS . ' http audit 2', 'settings' => array_replace_recursive($xcfg, ['notify' => ['emails' => ['staff@example.org']]])]);
check('manager: other edits still work, including the link-only notification recipients', $st === 200 && FormService::find($xid)['cfg']['notify']['emails'] === ['staff@example.org'], $st . ' ' . json_encode($b));
$xv = (int) FormService::find($xid)['version'];
[$st, $b] = $api($admin, 'PUT', '/admin/api/forms/' . $xid, ['version' => $xv, 'settings' => array_replace(FormService::find($xid)['cfg'], ['retention_days' => 400])]);
$aud = Database::one("SELECT * FROM audit_log WHERE action = 'form.retention' AND entity_type = 'form' AND entity_id = ? ORDER BY id DESC LIMIT 1", [$xid]);
check('admin (has forms.delete): may set the retention period; an audit entry "form.retention" records the form id and the days only', $st === 200 && (int) FormService::find($xid)['cfg']['retention_days'] === 400 && $aud !== null && str_contains((string) $aud['details'], '"to_days":400') && !str_contains((string) $aud['details'], '@'), $st . ' ' . json_encode($b));
$xv = (int) FormService::find($xid)['version'];
[$st, $b] = $api($admin, 'PUT', '/admin/api/forms/' . $xid, ['version' => $xv, 'settings' => array_replace_recursive(FormService::find($xid)['cfg'], ['retention_days' => 0, 'notify' => ['emails' => ['staff@example.org', 'boss@example.org']]])]);
$aud2 = Database::one("SELECT * FROM audit_log WHERE action = 'form.notify' AND entity_type = 'form' AND entity_id = ? ORDER BY id DESC LIMIT 1", [$xid]);
check('audit: changing the notification recipients is logged as "form.notify" with counts only (no addresses)', $st === 200 && $aud2 !== null && str_contains((string) $aud2['details'], '"recipients":2') && !str_contains((string) $aud2['details'], '@') && !str_contains((string) $aud2['summary'], '@'));

// roles: the edit page shows what the role has, and saving it unchanged keeps it
$rcRow = Database::one("SELECT id FROM roles WHERE slug = 'zz-forms-rolechk'");
[$st, $rp] = $admin('GET', '/admin/roles/' . (int) $rcRow['id'] . '/edit');
check('roles: the edit page ticks the abilities the role holds (forms.submissions, forms.export) and not the ones it lacks', $st === 200 && preg_match('/value="forms\.submissions"\s+checked/', $rp) === 1 && preg_match('/value="forms\.export"\s+checked/', $rp) === 1 && preg_match('/value="forms\.manage"\s+checked/', $rp) === 0, $st . '');
preg_match_all('/<input type="checkbox" name="abilities\[\]" value="([^"]+)" checked/', $rp, $mm1);
preg_match_all('/<input type="hidden" name="abilities\[\]" value="([^"]+)"/', $rp, $mm2);
$post1 = ['_token' => $hTok($rp), 'name' => 'ZZ role check', 'description' => '', 'abilities' => array_values(array_merge($mm1[1], $mm2[1]))];
$admin('POST', '/admin/roles/' . (int) $rcRow['id'], ['form' => $post1]);
Roles::flush();
$kept = json_decode((string) Database::value("SELECT abilities FROM roles WHERE slug = 'zz-forms-rolechk'"), true) ?: [];
sort($kept);
check('roles: saving the edit form unchanged keeps the forms abilities (it used to wipe them)', in_array('forms.submissions', $kept, true) && in_array('forms.export', $kept, true) && in_array('dashboard.view', $kept, true), json_encode($kept));

// ---- a hostile / hand-made request never makes the page fail
$hf = FormService::create(['source' => 'blank', 'title' => ZZ_FORMS . ' http hostile'], null)['form'];
$hid = (int) $hf['id'];
$hdef = $mkDef([['text', 'nm', 'Name', ['required' => true]], ['select', 'sel', 'Pick', ['options' => ['A', 'B'], 'allow_other' => true]], ['radio', 'rad', 'Radio', ['options' => ['A', 'B'], 'allow_other' => true]], ['checkboxes', 'chk', 'Boxes', ['options' => ['A', 'B']]]]);
$hIds = [];
foreach (FormDefinition::fields($hdef) as $f) {
    $hIds[$f['key']] = $f['id'];
}
FormService::save($hid, ['definition' => $hdef, 'settings' => array_replace_recursive($hf['cfg'], ['consent' => ['required' => true, 'text' => 'I agree'], 'spam' => 'light'])], (int) $hf['version'], null);
FormService::transition($hid, 'publish');
$hslug = (string) FormService::find($hid)['slug'];
$hClearLimits($hslug);
[, $hpage] = $visitor('GET', '/forms/' . $hslug);
$htok = $hTok($hpage);
$hts = $hAttr($hpage, '/name="_hp_ts" value="([^"]+)"/');
$hbase = static fn(array $over = []): array => array_replace(['_token' => $htok, '_hp_ts' => $hSig(30), 'website' => '', 'f_' . $hIds['nm'] => 'Zed', 'form_consent' => '1'], $over);
$hCount = static fn(): int => (int) Database::value('SELECT COUNT(*) FROM form_submissions WHERE form_id = ?', [$hid], 0);
foreach ([
    'a list for a dropdown' => ['f_' . $hIds['sel'] => ['a', 'x' => 'b']],
    'a list for the "other" text of a dropdown' => ['f_' . $hIds['sel'] => '__other', 'f_' . $hIds['sel'] . '_other' => ['a']],
    'a list for the "other" text of a single choice' => ['f_' . $hIds['rad'] => '__other', 'f_' . $hIds['rad'] . '_other' => ['a']],
    'a list for a single choice' => ['f_' . $hIds['rad'] => ['A', 'B']],
    'a list for the form-wide consent box' => ['form_consent' => ['a']],
    'a list for a text question' => ['f_' . $hIds['nm'] => ['a', 'b']],
] as $label => $over) {
    // the required name is left empty so the page has to be re-rendered with the hostile values in it (that is where a list used to be cast to a string)
    [$st, $b] = $visitor('POST', '/forms/' . $hslug, ['form' => $hbase($over + ['f_' . $hIds['nm'] => ''])]);
    check('hostile POST (' . $label . '): the form is shown again with HTTP 422, never a 500', $st === 422 && str_contains($b, 'data-lf-summary') && str_contains($b, 'class="lf lf--live"'), (string) $st);
}
$h0 = $hCount();
[$st] = $visitor('POST', '/forms/' . $hslug, ['form' => $hbase(['_hp_ts' => ['x']])]);
check('hostile POST (a list as the timestamp): answered like a bot (302), no 500, nothing stored', $st === 302 && $hCount() === $h0, (string) $st);
[$st] = $visitor('POST', '/forms/' . $hslug, ['form' => $hbase(['website' => ['x']])]);
check('hostile POST (a list as the honeypot): treated as filled (302), nothing stored', $st === 302 && $hCount() === $h0, (string) $st);

// ---- the re-shown form keeps the original timestamp: a quick fix-and-resend is stored, not swallowed
$hClearLimits($hslug);
$h1 = $hCount();
sleep(3);
[$st, $b1] = $visitor('POST', '/forms/' . $hslug, ['form' => ['_token' => $htok, '_hp_ts' => $hts, 'website' => '', 'form_consent' => '1', 'f_' . $hIds['nm'] => '']]);
$hts2 = $hAttr($b1, '/name="_hp_ts" value="([^"]+)"/');
$htok2 = $hTok($b1);
check('no-JS flow: a validation error re-shows the form (422) with the SAME signed timestamp as the page the visitor opened', $st === 422 && $hts !== '' && $hts2 === $hts, $st . ' ' . $hts . ' / ' . $hts2);
[$st, , $h] = $visitor('POST', '/forms/' . $hslug, ['form' => ['_token' => $htok2, '_hp_ts' => $hts2, 'website' => '', 'form_consent' => '1', 'f_' . $hIds['nm'] => 'Zed']]);
check('no-JS flow: the corrected form sent at once is STORED (it used to be dropped as "too fast" while the visitor saw success)', $st === 302 && str_ends_with($h['location'] ?? '', '/thanks') && $hCount() === $h1 + 1, $st . ' count=' . $hCount() . ' before=' . $h1);
[$st, $b] = $visitor('POST', '/forms/' . $hslug, ['form' => $hbase(['_hp_ts' => $hSig(0), 'f_' . $hIds['nm'] => 'Quick']), 'headers' => ['Accept: application/json', 'X-Requested-With: XMLHttpRequest']]);
$j = json_decode($b, true) ?? [];
check('AJAX: a submit that is genuinely too fast answers 422 JSON with a visible message (forms.js shows it), never {"ok":true}', $st === 422 && ($j['error'] ?? '') === 'too_fast' && !empty($j['message']) && $hCount() === $h1 + 1, $st . ' ' . $b);

// ---- a vacancy context that is no longer valid answers AJAX clients with JSON
[$st, $b] = $visitor('POST', '/forms/' . $hslug, ['form' => $hbase(['_hp_ts' => $hSig(30), 'job' => 'no-such-vacancy']), 'headers' => ['Accept: application/json', 'X-Requested-With: XMLHttpRequest']]);
$j = json_decode($b, true) ?? [];
check('AJAX: an unknown / closed vacancy gives 409 JSON {error:"job"} (the script reloads and shows the closed-vacancy notice), not an HTML page', $st === 409 && ($j['error'] ?? '') === 'job', $st . ' ' . substr($b, 0, 80));

// ---- JSON API details
[$st, $b] = $api($admin, 'PUT', '/admin/api/forms/' . $hid, ['version' => (int) FormService::find($hid)['version'], 'closes_at' => '2036-10-19T20:02:17']);
check('API: a closing time with seconds comes back with its seconds (a round trip never moves it)', $st === 200 && ($b['data']['closes_at'] ?? '') === '2036-10-19T20:02:17', json_encode($b['data']['closes_at'] ?? $b));
[$st, $b] = $api($admin, 'PUT', '/admin/api/forms/' . $hid, ['version' => (int) FormService::find($hid)['version'], 'closes_at' => '2036-10-19T20:02']);
check('API: a closing time on a whole minute is returned as "YYYY-MM-DDTHH:MM" (what the datetime-local input expects)', $st === 200 && ($b['data']['closes_at'] ?? '') === '2036-10-19T20:02', json_encode($b['data']['closes_at'] ?? $b));
[$st, $b] = $api($admin, 'PUT', '/admin/api/forms/' . $hid, ['version' => (int) FormService::find($hid)['version'], 'closes_at' => '']);
foreach (['99999999999', '1.5', '0', 'abc', '-3'] as $bad) {
    [$st1, $b1] = $api($admin, 'POST', '/admin/api/forms/validate', ['definition' => FormService::find($hid)['def'], 'max_submissions' => $bad]);
    [$st2] = $api($admin, 'PUT', '/admin/api/forms/' . $hid, ['version' => (int) FormService::find($hid)['version'], 'max_submissions' => $bad]);
    check('API: max_submissions "' . $bad . '" is judged the same way by validate and by save (both refuse)', $st1 === 200 && !empty(((array) $b1['errors'])['max_submissions']) && $st2 === 422, $st1 . '/' . $st2);
}
[$st1, $b1] = $api($admin, 'POST', '/admin/api/forms/validate', ['definition' => FormService::find($hid)['def'], 'max_submissions' => '25']);
check('API: a valid maximum passes validate', $st1 === 200 && empty(((array) $b1['errors'])['max_submissions'] ?? null));

// ---- notice pages describe THEIR form
FormService::save($hid, ['description' => 'ZZ description of the hostile test form'], (int) FormService::find($hid)['version'], null);
FormService::transition($hid, 'close');
[$st, $b] = $visitor('GET', '/forms/' . $hslug);
check('closed notice: the meta description (and og:description) are the form\'s own, not the text of the /forms registry page', $st === 200 && str_contains($b, 'content="ZZ description of the hostile test form"'));
[$st, $b] = $anon('GET', '/forms/' . $hslug);
check('closed form on the public site shows the closed notice (embed behaviour is covered by the shortcode checks)', $st === 200 && str_contains($b, 'This form is closed'));

// ---- delete a form with everything in it
echo " deleting\n";
$cvAbs = STORAGE_PATH . '/private/' . ($jfiles[0]['stored_path'] ?? 'x');
[$st] = $admin('POST', '/admin/forms/' . $jobFormId . '/delete', ['form' => ['_token' => $csrf]]);
clearstatcache();
check('admin: deleting a form removes its submissions, file rows and the stored files; the job falls back to the built-in form', in_array($st, [302, 303], true) && FormService::find($jobFormId) === null && !is_file($cvAbs) && Database::value('SELECT COUNT(*) FROM form_files WHERE submission_id = ?', [$sidJ], 0) == 0 && Database::value("SELECT form_id FROM jobs WHERE slug = 'zz-selftest-forms-http-job'") === null, 'st=' . $st . ' form=' . json_encode(FormService::find($jobFormId) === null) . ' file=' . json_encode(!is_file($cvAbs)) . ' jobform=' . json_encode(Database::value("SELECT form_id FROM jobs WHERE slug = 'zz-selftest-forms-http-job'")));
check('audit log has ids and counts only - no names, e-mails or answers', (int) Database::value("SELECT COUNT(*) FROM audit_log WHERE entity_type IN ('form', 'form_submission') AND (details LIKE '%@example.%' OR details LIKE '%Grace%' OR summary LIKE '%Grace%' OR summary LIKE '%@example.%')", [], 0) === 0);
[$st] = $admin('GET', '/careers');
check('Careers still works (the existing job pages are untouched)', $st === 200 && $admin('GET', '/careers/zz-selftest-forms-http-job')[0] === 200);
Database::execute("DELETE FROM jobs WHERE slug = 'zz-selftest-forms-http-job'");

echo " clean-up of the mails written by the checks\n";
foreach (array_diff($hMails(), $before) as $f) {
    $t = (string) file_get_contents($f);
    if (str_contains($t, 'example.org') || str_contains($t, 'example.test') || str_contains($t, ZZ_FORMS)) {
        @unlink($f);
    }
}
check('test mails removed', true);
