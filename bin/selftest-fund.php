<?php
declare(strict_types=1);

/**
 * Fund a project: the project data is consistent (figures, bars, USD at UGX 3,750), the pledge arithmetic is calculated on the server, the pages open and a pledge is recorded in the donations
 * with the project and the contribution. Included by bin/selftest.php or run alone:   /c/xampp/php/php.exe bin/selftest-fund.php
 * Needs the local web server. Removes its rows (donor e-mails zz-fund-*).
 */

use App\Core\Database;
use App\Services\Fund\FundProjects;

if (!function_exists('check')) {
    if (PHP_SAPI !== 'cli') {
        exit('CLI only.');
    }
    require dirname(__DIR__) . '/app/bootstrap.php';
    if (\App\Core\Config::get('app.env') !== 'local') {
        exit('Refusing to run outside APP_ENV=local.' . PHP_EOL);
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

echo "-- fund a project --\n";
$root = dirname(__DIR__);
$all = FundProjects::all();
$ready = array_values(array_filter($all, static fn(array $p): bool => $p['status'] === 'proposal'));
check('five projects: four with a full proposal and one still in preparation', count($all) === 5 && count($ready) === 4);

$problems = [];
foreach ($all as $p) {
    foreach (['slug', 'cover', 'title', 'short', 'tagline', 'summary'] as $k) {
        if (trim((string) ($p[$k] ?? '')) === '') {
            $problems[] = $p['slug'] . ' lacks ' . $k;
        }
    }
    foreach (['', '-card'] as $suffix) {
        if (!is_file($root . '/assets/img/projects/' . $p['cover'] . $suffix . '.jpg')) {
            $problems[] = $p['slug'] . ' cover ' . $suffix . ' missing';
        }
    }
}
check('every project has its slug, texts and both cover pictures', $problems === [], implode('; ', $problems));

$bad = [];
foreach ($ready as $p) {
    if (count($p['figures']) !== 6 || count($p['packages']) !== 3 || count($p['items']) < 9 || count($p['deliver']) < 4 || count($p['why']) < 4 || count($p['faq']) < 4) {
        $bad[] = $p['slug'] . ' is incomplete';
    }
    $sum = array_sum(array_column($p['budget']['bars'], 1));
    if (abs($sum - 100.0) > 0.3) {
        $bad[] = $p['slug'] . ' budget bars add up to ' . $sum;
    }
    if (abs($p['budget']['ugx'] / 3750 - $p['budget']['usd']) > 1.5) {
        $bad[] = $p['slug'] . ' total USD does not match UGX';
    }
    foreach ($p['packages'] as $k) {
        if (isset($k['ugx']) && abs($k['ugx'] / 3750 - $k['usd']) > 1.5) {
            $bad[] = $p['slug'] . ' package ' . $k['key'] . ' USD does not match UGX';
        }
    }
    $keys = [];
    foreach ($p['items'] as $i) {
        if (abs($i['ugx'] / 3750 - $i['usd']) > 1.5) {
            $bad[] = $p['slug'] . ' item ' . $i['key'] . ' USD ' . $i['usd'] . ' vs UGX ' . $i['ugx'];
        }
        $keys[] = $i['key'];
    }
    if (count($keys) !== count(array_unique($keys))) {
        $bad[] = $p['slug'] . ' has a repeated item key';
    }
}
check('prices are consistent: USD = UGX / 3,750 for every package and contribution, budget bars add up to 100%, nothing is missing', $bad === [], implode('; ', array_slice($bad, 0, 5)));
$di = FundProjects::find('digital-inclusion');
check('the figures of the proposals are right (digital inclusion total UGX 1,096,147,200; Year 1 USD 106,146)', $di['budget']['ugx'] === 1096147200 && $di['packages'][1]['usd'] === 106146 && FundProjects::find('books-brains-brighter-futures')['budget']['usd'] === 277832 && FundProjects::find('green-horizons')['budget']['ugx'] === 762936300 && FundProjects::find('healthy-futures')['packages'][1]['ugx'] === 348073800);

// ---- the arithmetic
$c = FundProjects::parseChoice($di, 'item:laptop', '3');
check('three laptops cost 3 x UGX 2,100,000 = UGX 6,300,000, or USD 1,680', $c !== null && FundProjects::fixedAmount($c, 'UGX') === 6300000.0 && FundProjects::fixedAmount($c, 'USD') === 1680.0);
check('a fixed contribution has no price in EUR or GBP', FundProjects::fixedAmount($c, 'EUR') === null && FundProjects::fixedAmount($c, 'GBP') === null);
check('a wrong item, quantity 0, quantity 501 and rubbish are refused', FundProjects::parseChoice($di, 'item:nope', '1') === null && FundProjects::parseChoice($di, 'item:laptop', '0') === null && FundProjects::parseChoice($di, 'item:laptop', '501') === null && FundProjects::parseChoice($di, 'zzz', '1') === null && FundProjects::parseChoice($di, 'package:nope', '1') === null);
$pk = FundProjects::parseChoice($di, 'package:year1');
check('the Year 1 package is a fixed price; a co-funding share and "any amount" are typed by the donor', $pk['fixed'] === true && FundProjects::fixedAmount($pk, 'USD') === 106146.0 && FundProjects::parseChoice($di, 'package:cofund')['fixed'] === false && FundProjects::parseChoice($di, 'package:cofund')['min_usd'] === 25000 && FundProjects::parseChoice($di, 'any')['fixed'] === false);
$soon = FundProjects::find('voices-of-change');
check('a project still in preparation takes "any amount" only', FundProjects::parseChoice($soon, 'any') !== null && FundProjects::parseChoice($soon, 'package:full') === null && FundProjects::parseChoice($soon, 'item:x') === null);
check('short money: UGX 1,096,147,200 -> "UGX 1.10 billion", 398,046,000 -> "UGX 398 million"', FundProjects::short(1096147200) === 'UGX 1.10 billion' && FundProjects::short(398046000) === 'UGX 398 million');

// ---- the pages
$base = 'http://localhost/' . basename($root);
$jar = tempnam(sys_get_temp_dir(), 'fnj');
$req = static function (string $method, string $path, array $form = []) use ($base, $jar): array {
    $ch = curl_init($base . $path);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true, CURLOPT_TIMEOUT => 60, CURLOPT_COOKIEJAR => $jar, CURLOPT_COOKIEFILE => $jar, CURLOPT_FOLLOWLOCATION => false]);
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($form));
    }
    $raw = (string) curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $hs = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);
    $head = substr($raw, 0, $hs);
    return ['code' => $code, 'body' => substr($raw, $hs), 'loc' => preg_match('/^Location:\s*(.+)$/mi', $head, $m) === 1 ? trim($m[1]) : ''];
};
$tok = static fn(string $html): string => preg_match('/name="_token" value="([^"]+)"/', $html, $m) === 1 ? $m[1] : '';
$hp = static function (): string {
    $ts = (string) (time() - 30);
    return $ts . '.' . hash_hmac('sha256', $ts, \App\Core\Config::appKey());
};
Database::execute("DELETE FROM donations WHERE donor_email LIKE 'zz-fund-%'");
Database::execute('DELETE FROM rate_limits');

$hub = $req('GET', '/fund-a-project');
$allTitles = true;
foreach ($all as $p) {
    $allTitles = $allTitles && str_contains($hub['body'], e($p['short'])) && str_contains($hub['body'], 'img/projects/' . $p['cover'] . '-card.jpg');
}
check('the hub opens (200) with every project card and cover', $hub['code'] === 200 && $allTitles && str_contains($hub['body'], 'Fund a project that changes'), (string) $hub['code']);
$pagesOk = true;
$why = '';
foreach ($all as $p) {
    $r = $req('GET', '/fund-a-project/' . $p['slug']);
    $needles = $p['status'] === 'proposal' ? ['Choose how to give', 'name="choice"', 'Or sponsor one contribution', 'Where the money goes', 'Questions funders often ask'] : ['Register your interest', 'The full proposal is being prepared'];
    foreach ($needles as $n) {
        if ($r['code'] !== 200 || !str_contains($r['body'], $n)) {
            $pagesOk = false;
            $why .= $p['slug'] . ' lacks "' . $n . '" (' . $r['code'] . '); ';
        }
    }
}
check('every project page opens with its budget, price list, pledge form and questions (or the "in preparation" notice)', $pagesOk, $why);
check('an unknown project is a 404', $req('GET', '/fund-a-project/nope')['code'] === 404);
$hits = [];
foreach (['' => 'home', 'donate' => 'donate', 'projects' => 'projects'] as $path => $name) {
    if (!str_contains($req('GET', '/' . $path)['body'], '/fund-a-project')) {
        $hits[] = $name;
    }
}
check('the Fund a project band is on the home, Donate and Projects pages', $hits === [], implode(',', $hits));
$home = $req('GET', '/')['body'];
$posFund = strpos($home, 'id="fund"');
$posNext = strpos($home, 'home-focus');
check('on the home page the spotlight comes right after the hero and the quick links, before everything else', $posFund !== false && $posNext !== false && $posFund < $posNext && substr_count($home, 'fund-band__grid') === 1, json_encode([$posFund, $posNext]));
$fh = FundProjects::highlights();
check('the highlights: 4 projects, the smallest gift is USD 5, the total is about USD 1.05 million, one card each', $fh['projects'] === 4 && count($fh['cards']) === 4 && $fh['min_gift_usd'] === 5.0 && $fh['total_usd'] === 1052985.0 && count($fh['soon']) === 1);
check('the home spotlight shows the numbers (USD 1.05M, From USD 5) and each project\'s launch package and smallest gift', str_contains($home, 'USD 1.05M') && str_contains($home, 'From USD 5') && str_contains($home, 'Launch USD 106K') && str_contains($home, 'Give from USD 75') && str_contains($home, 'Coming next'));
check('quick links to Fund a project: the top bar, the header and the mobile menu', preg_match_all('~href="[^"]*/fund-a-project"~', $home) >= 4 && str_contains($home, 'topbar__item--fund') && str_contains($home, 'site-header__fund') && str_contains($home, 'site-nav__fund'));
check('usdShort: 1,052,985 -> USD 1.05M, 292,306 -> USD 292K, 75 -> USD 75', FundProjects::usdShort(1052985) === 'USD 1.05M' && FundProjects::usdShort(292306) === 'USD 292K' && FundProjects::usdShort(75) === 'USD 75');
check('the sitemap lists the hub and the project pages', str_contains(\App\Services\Web\SitemapBuilder::xml(), 'fund-a-project/green-horizons'));

// ---- pledges
$post = static function (string $slug, array $form) use ($req, $tok, $hp): array {
    $page = $req('GET', '/fund-a-project/' . $slug);
    return $req('POST', '/fund-a-project/' . $slug . '/pledge', $form + ['_token' => $tok($page['body']), '_hp_ts' => $hp()]);
};
$who = static fn(string $tag): array => ['donor_name' => 'Zed Fund ' . $tag, 'donor_email' => 'zz-fund-' . $tag . '@example.test', 'method' => 'bank', 'message' => 'Happy to help'];
$last = static fn(string $tag): ?array => Database::one("SELECT * FROM donations WHERE donor_email = ? ORDER BY id DESC LIMIT 1", ['zz-fund-' . $tag . '@example.test']);

$r = $post('digital-inclusion', $who('item') + ['choice' => 'item:laptop', 'qty' => '3', 'currency' => 'UGX', 'amount' => '1']);
$d = $last('item');
check('pledging three laptops records UGX 6,300,000 (the amount typed in the form is ignored), as a pledge with the project and the contribution', $r['code'] === 302 && str_contains($r['loc'], 'fund-a-project/digital-inclusion/thanks') && $d !== null && (float) $d['amount'] === 6300000.0 && $d['currency'] === 'UGX' && $d['status'] === 'pledged' && str_starts_with((string) $d['reference'], 'PROJECT:digital-inclusion|item:laptopx3') && str_contains((string) $d['message'], 'PROJECT PLEDGE: Digital Inclusion') && str_contains((string) $d['message'], 'Quantity: 3') && str_contains((string) $d['message'], 'Happy to help'), $r['code'] . ' ' . $r['loc'] . ' ' . json_encode($d));
$th = $req('GET', '/fund-a-project/digital-inclusion/thanks');
check('the thank-you page shows the contribution, the amount and what happens next', $th['code'] === 200 && str_contains($th['body'], 'Thank you, Zed') && str_contains($th['body'], 'Laptop for a learner workstation') && str_contains($th['body'], '6,300,000.00') && str_contains($th['body'], 'What happens next'), (string) $th['code']);
check('the thank-you page of another project (or without a pledge) goes back to the project', $req('GET', '/fund-a-project/green-horizons/thanks')['code'] === 302);

$post('healthy-futures', $who('usd') + ['choice' => 'package:year1', 'currency' => 'USD']);
$d = $last('usd');
check('the Year 1 package in USD records USD 92,820 for Healthy Futures', $d !== null && (float) $d['amount'] === 92820.0 && $d['currency'] === 'USD' && str_contains((string) $d['reference'], 'PROJECT:healthy-futures|package:year1'));
$r = $post('healthy-futures', $who('eur') + ['choice' => 'item:hygiene', 'qty' => '10', 'currency' => 'EUR']);
check('a fixed contribution cannot be pledged in EUR (no price): refused, nothing recorded', $r['code'] === 302 && $last('eur') === null);
$r = $post('green-horizons', $who('low') + ['choice' => 'package:cofund', 'currency' => 'USD', 'amount' => '5000']);
check('a co-funding share below USD 15,000 is refused, nothing recorded', $last('low') === null);
$post('green-horizons', $who('co') + ['choice' => 'package:cofund', 'currency' => 'USD', 'amount' => '20,000']);
$d = $last('co');
check('a co-funding share of USD 20,000 is recorded as typed', $d !== null && (float) $d['amount'] === 20000.0 && str_contains((string) $d['reference'], 'package:cofund'));
$post('books-brains-brighter-futures', $who('any') + ['choice' => 'any', 'currency' => 'GBP', 'amount' => '75.50', 'is_anonymous' => '1']);
$d = $last('any');
check('any amount in GBP is recorded, and "anonymous" is kept', $d !== null && (float) $d['amount'] === 75.5 && $d['currency'] === 'GBP' && (int) $d['is_anonymous'] === 1);
$r = $post('voices-of-change', $who('soon') + ['choice' => 'item:x', 'qty' => '1', 'currency' => 'UGX']);
check('a project in preparation refuses a priced contribution', $last('soon') === null);
$post('voices-of-change', $who('soon2') + ['choice' => 'any', 'currency' => 'UGX', 'amount' => '100000']);
check('...and takes "any amount"', $last('soon2') !== null);
$r = $post('digital-inclusion', ['donor_name' => '', 'donor_email' => 'not-an-email', 'method' => 'bank', 'choice' => 'item:laptop', 'qty' => '1', 'currency' => 'UGX']);
check('a missing name and a bad e-mail are refused', $r['code'] === 302 && Database::value("SELECT COUNT(*) FROM donations WHERE donor_email = 'not-an-email'", [], 0) === 0);
$r = $post('digital-inclusion', $who('nochoice') + ['currency' => 'UGX']);
check('no choice at all is refused', $last('nochoice') === null);

$rep = \App\Services\Reports\ReportCatalog::all()['donations-project-pledges'] ?? null;
$res = $rep !== null ? \App\Services\Reports\ReportCatalog::run($rep, ['from' => null, 'to' => null], 200) : ['error' => 'missing', 'rows' => []];
$row = null;
foreach ($res['rows'] as $x) {
    if ($x['donor_email'] === 'zz-fund-item@example.test') {
        $row = $x;
    }
}
check('the report "Project pledges" lists the pledge with its project and contribution', $res['error'] === null && $row !== null && $row['project'] === 'digital-inclusion' && str_contains((string) $row['contribution'], 'Laptop'), json_encode($row));


// ---- "Request the full proposal": a real form, the proposal PDF e-mailed at once
echo "-- full proposal request --\n";
$mailDir = STORAGE_PATH . '/mail';
$mailsBefore = glob($mailDir . '/*.eml') ?: [];
$page = $req('GET', '/fund-a-project/digital-inclusion');
check('proposal: the button leads to a form on the page (no mailto: link any more) and the form asks for name and e-mail', str_contains($page['body'], 'href="#proposal"') && str_contains($page['body'], 'id="proposal"') && str_contains($page['body'], 'name="pr_email"') && !str_contains($page['body'], 'href="mailto:' . (string) setting('email_info', setting('email_primary', '')) . '?subject=Full'));
$soon = array_values(array_filter($all, static fn(array $p): bool => $p['status'] !== 'proposal'))[0] ?? null;
if ($soon !== null) {
    $sp = $req('GET', '/fund-a-project/' . $soon['slug']);
    check('proposal: a project still in preparation has no proposal form', !str_contains($sp['body'], 'id="proposal"'));
}
$doc = \App\Services\Fund\ProposalDocument::pdf(FundProjects::find('digital-inclusion'), 'Amina Test', 'Test Org', ['compress' => false]);
check('proposal PDF: a real PDF on the letterhead with the project title, the budget, the ways to give and the questions', str_starts_with($doc, '%PDF-') && str_contains($doc, 'Digital Inclusion for Post-Conflict') && str_contains($doc, 'The budget') && str_contains($doc, 'UGX 1,096,147,200') && str_contains($doc, 'Ways to give') && str_contains($doc, 'Questions funders often ask') && str_contains($doc, 'Dear Amina,'));
check('proposal PDF: every proposal project builds a multi-page document of reasonable size', (static function () use ($ready): bool {
    foreach ($ready as $p) {
        $b = \App\Services\Fund\ProposalDocument::pdf($p, 'X', '');
        if (!str_starts_with($b, '%PDF-') || strlen($b) < 8000 || strlen($b) > 600000 || preg_match('/[^\x00-\x7F]/', \App\Services\Fund\ProposalDocument::body($p)) === 1) {
            return false;
        }
    }
    return true;
})());

$prop = static function (string $slug, array $form) use ($req, $tok, $hp): array {
    $pg = $req('GET', '/fund-a-project/' . $slug);
    return $req('POST', '/fund-a-project/' . $slug . '/proposal', $form + ['_token' => $tok($pg['body']), '_hp_ts' => $hp()]);
};
$mine = static function () use ($mailDir, $mailsBefore): array {
    $out = [];
    foreach (array_diff(glob($mailDir . '/*.eml') ?: [], $mailsBefore) as $f) {
        $raw = (string) file_get_contents($f);
        if (str_contains($raw, 'zz-fund-prop@example.test') && str_contains($raw, 'application/pdf')) {
            $out[] = $raw;
        }
    }
    return $out;
};
$r = $prop('digital-inclusion', ['pr_name' => 'Amina Prop', 'pr_email' => 'zz-fund-prop@example.test', 'pr_org' => 'Test Org', 'pr_phone' => '', 'pr_note' => 'Please send it']);
$m = Database::one("SELECT * FROM contact_messages WHERE email = 'zz-fund-prop@example.test' ORDER BY id DESC LIMIT 1");
check('proposal: a valid request is saved in Admin > Messages with the project in the subject and redirects back to the form', in_array($r['code'], [302, 303], true) && str_contains($r['loc'], 'fund-a-project/digital-inclusion') && $m !== null && $m['subject'] === 'Full proposal request: Digital Inclusion' && str_contains((string) $m['message'], 'Test Org'));
$sentMails = $mine();
$pdfOk = false;
$nameOk = false;
if (count($sentMails) === 1 && preg_match('/Content-Type: application\/pdf;[^\n]*\n(?:[^\n]*\n)*?\r?\n([A-Za-z0-9+\/=\r\n]+)/', $sentMails[0], $mm) === 1) {
    $bytes = (string) base64_decode(preg_replace('/\s+/', '', $mm[1]) ?? '');
    $pdfOk = str_starts_with($bytes, '%PDF-') && strlen($bytes) > 8000;
    $nameOk = str_contains($sentMails[0], 'Proposal-Digital-Inclusion.pdf');
}
check('proposal: the visitor gets ONE e-mail with the proposal attached as a real PDF named after the project', count($sentMails) === 1 && $pdfOk && $nameOk, 'mails=' . count($sentMails));
$r2 = $prop('digital-inclusion', ['pr_name' => 'Amina Prop', 'pr_email' => 'zz-fund-prop@example.test']);
$r3 = $prop('digital-inclusion', ['pr_name' => 'Amina Prop', 'pr_email' => 'zz-fund-prop@example.test']);
check('proposal: the same address gets at most two copies a day (no mail bombing through the form)', count($mine()) === 2 && in_array($r3['code'], [302, 303], true));
$bad = $prop('digital-inclusion', ['pr_name' => '', 'pr_email' => 'not-an-email']);
check('proposal: a missing name and a bad e-mail address are refused and nothing is sent', in_array($bad['code'], [302, 303], true) && count($mine()) === 2 && (int) Database::value("SELECT COUNT(*) FROM contact_messages WHERE email = 'not-an-email'", [], 0) === 0);
$rawPost = static function (array $form) use ($req, $tok): array {
    $pg = $req('GET', '/fund-a-project/digital-inclusion');
    return $req('POST', '/fund-a-project/digital-inclusion/proposal', $form + ['_token' => $tok($pg['body'])]);
};
$before = count($mine());
$rawPost(['pr_name' => 'Bot', 'pr_email' => 'zz-fund-prop@example.test']);
check('proposal: a post without the signed honeypot timestamp (a bot) sends nothing', count($mine()) === $before);
check('proposal: a project in preparation refuses a proposal request (404)', $soon === null || $prop($soon['slug'], ['pr_name' => 'A B', 'pr_email' => 'zz-fund-p2@example.test'])['code'] === 404);
Database::execute("DELETE FROM contact_messages WHERE email LIKE 'zz-fund-%'");
foreach (array_diff(glob($mailDir . '/*.eml') ?: [], $mailsBefore) as $f) {
    if (str_contains((string) file_get_contents($f), 'zz-fund-')) {
        @unlink($f);
    }
}
Database::execute('DELETE FROM rate_limits');

@unlink($jar);
Database::execute("DELETE FROM donations WHERE donor_email LIKE 'zz-fund-%'");

if (!empty($standalone)) {
    echo PHP_EOL . ($fail === 0 ? 'ALL OK' : $fail . ' CHECK(S) FAILED') . PHP_EOL;
    exit($fail === 0 ? 0 : 1);
}
