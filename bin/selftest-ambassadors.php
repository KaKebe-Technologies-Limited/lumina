<?php
/**
 * AMBASSADORS (page /involvement/ambassadors), included by bin/selftest.php (shares check() and $fail) or run alone:
 *   /c/xampp/php/php.exe bin/selftest-ambassadors.php
 * Covers: the regions and countries data, the public page and its menu entry, every validation rule of the application form (region and country must match, money needs an
 * amount and a currency, in-kind needs items, other needs text, consent), storing and e-mailing an application, the duplicate guard, the public list of approved ambassadors
 * (only those who agreed), the admin list / detail / decision with the optional e-mail / CSV export / delete, and the access rules. Removes its own rows (zz-amb-*).
 */
declare(strict_types=1);

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

use App\Core\Auth;
use App\Core\Database;
use App\Models\AmbassadorApplication as Amb;
use App\Models\User;
use App\Services\Web\Countries;

echo "-- ambassadors --\n";
// ---- data ------------------------------------------------------------------------------------------------------------------
$all = [];
foreach (Countries::byRegion() as $list) {
    foreach ($list as $c) {
        $all[] = $c;
    }
}
check('countries: eight regions, every one with countries, at least 230 countries and territories, none twice', count(Countries::REGIONS) === 8 && count(Countries::byRegion()) === 8
    && count($all) >= 230 && count($all) === count(array_unique($all)) && !array_filter(Countries::byRegion(), static fn(array $l): bool => $l === []));
check('countries: a country matches only its own region', Countries::valid('africa', 'Uganda') && Countries::valid('europe', 'Iceland') && Countries::valid('south_america', 'Brazil') && Countries::valid('oceania', 'Fiji')
    && !Countries::valid('africa', 'France') && !Countries::valid('mars', 'Uganda') && !Countries::valid('asia', 'Atlantis'));
check('currencies: the usual ones are offered', isset(Countries::CURRENCIES['USD'], Countries::CURRENCIES['UGX'], Countries::CURRENCIES['EUR']));

// ---- http helpers -------------------------------------------------------------------------------------------------------------
$base = 'http://localhost/' . basename(dirname(__DIR__));
$req = static function (string $method, string $path, array $form = [], ?string &$jar = null) use ($base): array {
    $jar ??= tempnam(sys_get_temp_dir(), 'ambj');
    $ch = curl_init($base . $path);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true, CURLOPT_TIMEOUT => 40, CURLOPT_COOKIEJAR => $jar, CURLOPT_COOKIEFILE => $jar, CURLOPT_FOLLOWLOCATION => false]);
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($form));
    }
    $raw = (string) curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $hs = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);
    $head = substr($raw, 0, $hs);
    return ['code' => $code, 'body' => substr($raw, $hs), 'head' => $head, 'loc' => preg_match('/^location:\s*(\S+)/mi', $head, $m) === 1 ? $m[1] : ''];
};
$tok = static fn(string $html): string => preg_match('/name="_token" value="([^"]+)"/', $html, $m) === 1 ? $m[1] : '';
$post = static function (string $path, array $form, ?string &$jar, string $from, bool $hp = false) use ($req, $tok): array {
    $page = $req('GET', $from, [], $jar);
    $extra = ['_token' => $tok($page['body'])];
    if ($hp) {
        $ts = (string) (time() - 30);
        $extra['_hp_ts'] = $ts . '.' . hash_hmac('sha256', $ts, \App\Core\Config::appKey());
    }
    return $req('POST', $path, $form + $extra, $jar);
};
$login = static function (string $email, string $pw, ?string &$jar) use ($req, $tok): bool {
    $page = $req('GET', '/admin/login', [], $jar);
    return in_array($req('POST', '/admin/login', ['_token' => $tok($page['body']), 'email' => $email, 'password' => $pw], $jar)['code'], [302, 303], true);
};
$clean = static function (): void {
    Database::execute("DELETE FROM ambassador_applications WHERE email LIKE 'zz-amb-%@example.test'");
    Database::execute("DELETE FROM users WHERE email LIKE 'zz-amb-%@example.test'");
    Database::execute('DELETE FROM rate_limits');
};
$clean();
$mailsBefore = glob(STORAGE_PATH . '/mail/*.eml') ?: [];
$mailText = static function (string $to) use ($mailsBefore): string {
    $out = '';
    foreach (array_diff(glob(STORAGE_PATH . '/mail/*.eml') ?: [], $mailsBefore) as $f) {
        $c = (string) file_get_contents($f);
        if (preg_match('/^To:.*' . preg_quote($to, '/') . '/mi', $c) === 1) {
            if (preg_match_all('/Content-Transfer-Encoding: base64\r?\n\r?\n(.*?)(?=\r?\n--)/s', $c, $parts) > 0) {
                foreach ($parts[1] as $b) {
                    $out .= (string) base64_decode((string) preg_replace('/\s+/', '', $b)) . "\n";
                }
            } else {
                $body = preg_split('/?
?
/', $c, 2)[1] ?? '';
                $out .= stripos($c, 'Content-Transfer-Encoding: base64') !== false ? (string) base64_decode((string) preg_replace('/\s+/', '', $body)) : quoted_printable_decode($body);
            }
        }
    }
    return $out;
};

// ---- public page ---------------------------------------------------------------------------------------------------------------
$page = $req('GET', '/involvement/ambassadors');
check('page: opens with the banner, the pledge types, the steps and the form', $page['code'] === 200 && str_contains($page['body'], 'Become a Lumina') && str_contains($page['body'], 'name="pledge_types[]"')
    && str_contains($page['body'], 'name="annual_amount"') && str_contains($page['body'], 'name="inkind_items[]"') && str_contains($page['body'], 'name="other_text"'));
check('page: the region list and the whole country list are in the form', str_contains($page['body'], 'Central America and the Caribbean') && str_contains($page['body'], 'value="Zimbabwe"')
    && str_contains($page['body'], 'value="Iceland"') && str_contains($page['body'], 'value="Fiji"') && substr_count($page['body'], '<option value="') > 270);
$home = $req('GET', '/involvement');
check('menu: the Involvement page links to the Ambassadors page (child page)', str_contains($home['body'], 'involvement/ambassadors'), (string) $home['code'] . ' careers=' . substr_count($home['body'], 'careers') . ' amb=' . substr_count($home['body'], 'mbassador') . ' membership=' . substr_count($home['body'], 'membership'));
check('page: a visitor who is not signed in sees no admin data and no cookie is needed to read it', $page['code'] === 200 && !str_contains($page['body'], 'zz-amb'));

// ---- validation ------------------------------------------------------------------------------------------------------------------
$good = ['name' => 'ZZ Amb Ada Lovelace', 'email' => 'zz-amb-ada@example.test', 'phone' => '+44 20 7946 0000', 'region' => 'europe', 'country' => 'United Kingdom', 'city' => 'London',
    'occupation' => 'Engineer', 'organisation' => 'Analytical Ltd', 'profile_url' => 'https://example.org/ada', 'roles' => ['fundraise', 'lobby'], 'pledge_types' => ['money', 'inkind', 'other'],
    'annual_amount' => '5,000', 'currency' => 'GBP', 'inkind_items' => ['computers', 'books'], 'inkind_notes' => '20 laptops in London, about GBP 4,000.', 'other_text' => 'Free software licences',
    'network' => 'My professional body', 'motivation' => 'I believe every young person deserves digital skills.', 'availability' => 'weekly', 'consent' => '1', 'public_ok' => '1'];
$count = static fn(): int => (int) Database::value("SELECT COUNT(*) FROM ambassador_applications WHERE email LIKE 'zz-amb-%@example.test'", [], 0);
$try = static function (array $over, ?string &$jar = null) use ($post, $good): array {
    Database::execute("DELETE FROM rate_limits");
    return $post('/involvement/ambassadors', $over + $good, $jar, '/involvement/ambassadors', true);
};
$j = null;
$try(['country' => 'Uganda'], $j);
check('form: a country that is not in the chosen region is refused', $count() === 0);
$try(['region' => 'atlantis'], $j);
check('form: an unknown region is refused', $count() === 0);
$try(['annual_amount' => ''], $j);
check('form: a money pledge needs an amount', $count() === 0);
$try(['annual_amount' => '-5'], $j);
check('form: a negative amount is refused', $count() === 0);
$try(['currency' => 'XYZ'], $j);
check('form: a money pledge needs a known currency', $count() === 0);
$try(['inkind_items' => [], 'inkind_notes' => ''], $j);
check('form: an in-kind pledge needs items or a description', $count() === 0);
$try(['other_text' => ''], $j);
check('form: "something else" needs text', $count() === 0);
$try(['pledge_types' => []], $j);
check('form: at least one pledge kind is required', $count() === 0);
$try(['roles' => []], $j);
check('form: at least one way to help is required', $count() === 0);
$try(['consent' => ''], $j);
check('form: consent is required', $count() === 0);
$try(['email' => 'not-an-email'], $j);
check('form: a bad e-mail address is refused', $count() === 0);
$try(['motivation' => ''], $j);
check('form: the motivation is required', $count() === 0);
$noHp = $post('/involvement/ambassadors', $good, $j, '/involvement/ambassadors', false);
check('form: a submission without the honeypot time stamp is turned away', $count() === 0 && $noHp['code'] !== 200);

$ok = $try([], $j);
$row = Database::one("SELECT * FROM ambassador_applications WHERE email = 'zz-amb-ada@example.test'");
check('form: a complete application is stored as new', $ok['code'] === 302 && $row !== null && $row['status'] === 'new' && $row['country'] === 'United Kingdom' && $row['region'] === 'europe', (string) $ok['code'] . ' ' . $ok['loc']);
check('form: the yearly pledge is stored (amount without separators, currency, pledge kinds, items, notes, other)', $row !== null && (float) $row['annual_amount'] === 5000.0 && $row['currency'] === 'GBP'
    && $row['pledge_types'] === 'money,inkind,other' && Amb::jsonList($row['inkind_items']) === ['computers', 'books'] && str_contains((string) $row['inkind_notes'], 'laptops') && $row['other_text'] === 'Free software licences');
check('form: the ways to help, availability and consent are stored', $row !== null && Amb::jsonList($row['roles']) === ['fundraise', 'lobby'] && $row['availability'] === 'weekly' && (int) $row['public_ok'] === 1 && $row['consent_at'] !== null);
check('form: the applicant is e-mailed a confirmation with the pledge, and the success message is shown', str_contains($mailText('zz-amb-ada@example.test'), 'Money: 5,000.00 GBP per year')
    && str_contains($req('GET', '/involvement/ambassadors', [], $j)['body'], 'received your application'));
$try(['name' => 'ZZ Amb Again'], $j);
check('form: a second application from the same address while one is open is refused', $count() === 1);

// money-only and in-kind-only variants
$try(['email' => 'zz-amb-bob@example.test', 'name' => 'ZZ Amb Bob', 'region' => 'africa', 'country' => 'Uganda', 'pledge_types' => ['inkind'], 'annual_amount' => '', 'currency' => '', 'other_text' => '', 'public_ok' => ''], $j);
$bob = Database::one("SELECT * FROM ambassador_applications WHERE email = 'zz-amb-bob@example.test'");
check('form: an in-kind-only application stores no amount', $bob !== null && $bob['annual_amount'] === null && $bob['pledge_types'] === 'inkind' && (int) $bob['public_ok'] === 0);
$try(['email' => 'zz-amb-cleo@example.test', 'name' => 'ZZ Amb Cleo', 'region' => 'asia', 'country' => 'Japan', 'pledge_types' => ['other'], 'annual_amount' => '', 'currency' => '', 'inkind_items' => [], 'inkind_notes' => '', 'other_text' => 'A venue for training days'], $j);
check('form: an "other" only application is accepted', Database::value("SELECT COUNT(*) FROM ambassador_applications WHERE email = 'zz-amb-cleo@example.test'", [], 0) === 1);

// ---- directory: only approved and agreed ---------------------------------------------------------------------------------------------
check('directory: nobody is listed while applications are only new', !str_contains($req('GET', '/involvement/ambassadors')['body'], 'ZZ Amb Ada'));

// ---- admin ------------------------------------------------------------------------------------------------------------------------------
$mk = static fn(string $name, string $email, string $role, string $pw): int => (int) User::create(['name' => $name, 'email' => $email, 'password_hash' => Auth::hashPassword($pw), 'role' => $role, 'is_active' => 1, 'must_change_password' => 0]);
$mk('ZZ Amb Admin', 'zz-amb-admin@example.test', 'admin', 'Admin test pass 51');
$mk('ZZ Amb Teacher', 'zz-amb-teacher@example.test', 'facilitator', 'Teacher test pass 52');
$jA = $jT = null;
check('admin and a teaching-only user can sign in', $login('zz-amb-admin@example.test', 'Admin test pass 51', $jA) && $login('zz-amb-teacher@example.test', 'Teacher test pass 52', $jT));
$list = $req('GET', '/admin/ambassadors', [], $jA);
check('admin: the list shows the applications with country, pledge and status', $list['code'] === 200 && str_contains($list['body'], 'ZZ Amb Ada Lovelace') && str_contains($list['body'], 'United Kingdom') && str_contains($list['body'], '5,000'));
check('admin: filters work (region, pledge kind, search)', str_contains($req('GET', '/admin/ambassadors?region=africa', [], $jA)['body'], 'ZZ Amb Bob') && !str_contains($req('GET', '/admin/ambassadors?region=africa', [], $jA)['body'], 'ZZ Amb Ada')
    && str_contains($req('GET', '/admin/ambassadors?pledge=money&q=zz-amb', [], $jA)['body'], 'ZZ Amb Ada') && !str_contains($req('GET', '/admin/ambassadors?pledge=money&q=zz-amb', [], $jA)['body'], 'ZZ Amb Bob'));
$aid = (int) $row['id'];
$show = $req('GET', '/admin/ambassadors/' . $aid, [], $jA);
check('admin: the detail page shows the whole pledge and the answers', $show['code'] === 200 && str_contains($show['body'], 'Money: 5,000.00 GBP per year') && str_contains($show['body'], 'Computers and laptops') && str_contains($show['body'], 'Free software licences')
    && str_contains($show['body'], 'My professional body'));
check('access: a teaching-only user and an anonymous visitor cannot open the Ambassadors admin', $req('GET', '/admin/ambassadors', [], $jT)['code'] === 403 && $req('GET', '/admin/ambassadors/' . $aid, [], $jT)['code'] === 403
    && $req('GET', '/admin/ambassadors')['code'] !== 200);
$post('/admin/ambassadors/' . $aid, ['status' => 'approved', 'admin_notes' => 'Spoke on Monday', 'email_applicant' => '1', 'applicant_note' => 'Welcome aboard, Ada.'], $jA, '/admin/ambassadors/' . $aid);
$app = Database::one('SELECT * FROM ambassador_applications WHERE id = ?', [$aid]);
check('admin: approving saves the status, the note and who decided when', $app['status'] === 'approved' && $app['admin_notes'] === 'Spoke on Monday' && $app['reviewed_at'] !== null && (int) $app['reviewed_by'] > 0);
check('admin: the applicant is e-mailed the welcome with the team message', str_contains($mailText('zz-amb-ada@example.test'), 'welcome you as an ambassador') && str_contains($mailText('zz-amb-ada@example.test'), 'Welcome aboard, Ada.'));
$dir = $req('GET', '/involvement/ambassadors');
check('directory: an approved ambassador who agreed is listed with the country, and the number of countries is shown', str_contains($dir['body'], 'ZZ Amb Ada Lovelace') && str_contains($dir['body'], 'United Kingdom') && str_contains($dir['body'], 'amb-world__count'));
$bobId = (int) $bob['id'];
$post('/admin/ambassadors/' . $bobId, ['status' => 'approved', 'admin_notes' => ''], $jA, '/admin/ambassadors/' . $bobId);
check('directory: an approved ambassador who did NOT agree stays off the page', !str_contains($req('GET', '/involvement/ambassadors')['body'], 'ZZ Amb Bob'));
$post('/admin/ambassadors/' . $aid, ['status' => 'declined', 'admin_notes' => '', 'email_applicant' => '1'], $jA, '/admin/ambassadors/' . $aid);
check('directory: a declined application disappears from the page', !str_contains($req('GET', '/involvement/ambassadors')['body'], 'ZZ Amb Ada'));
$post('/admin/ambassadors/' . $aid, ['status' => 'bogus', 'admin_notes' => ''], $jA, '/admin/ambassadors/' . $aid);
check('admin: an unknown status is refused', Database::value('SELECT status FROM ambassador_applications WHERE id = ?', [$aid]) === 'declined');
$csv = $req('GET', '/admin/ambassadors/export?q=zz-amb', [], $jA);
check('admin: the CSV export has the pledge columns and the rows', $csv['code'] === 200 && str_contains($csv['head'], 'text/csv') && str_contains($csv['body'], 'Amount per year') && str_contains($csv['body'], 'ZZ Amb Ada Lovelace') && str_contains($csv['body'], 'GBP'));
$post('/admin/ambassadors/' . $aid . '/delete', [], $jA, '/admin/ambassadors/' . $aid);
check('admin: an application can be deleted', Database::value('SELECT COUNT(*) FROM ambassador_applications WHERE id = ?', [$aid], 0) === 0);

// ---- cleanup -------------------------------------------------------------------------------------------------------------------------------
foreach (array_diff(glob(STORAGE_PATH . '/mail/*.eml') ?: [], $mailsBefore) as $mf) {
    @unlink($mf);
}
Database::execute("DELETE FROM audit_log WHERE summary LIKE '%ZZ Amb%'");
$clean();
check('cleanup: no zz-amb rows are left', $count() === 0 && Database::value("SELECT COUNT(*) FROM users WHERE email LIKE 'zz-amb-%'", [], 0) === 0);

if (!empty($standalone)) {
    echo $fail === 0 ? "\nALL CHECKS PASSED\n" : "\n{$fail} CHECK(S) FAILED\n";
    exit($fail === 0 ? 0 : 1);
}
