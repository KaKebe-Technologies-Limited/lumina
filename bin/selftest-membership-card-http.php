<?php
/**
 * MEMBERSHIP CARD checks, part 3: HTTP (required by bin/selftest-membership-card-flow.php, which provides $mcNew, $mcPay, $mcRow, $mcMails, $mcCards, $mcTmpFile, $typeA, $typeB ...).
 * Real requests to http://localhost/<folder>/ with curl: the verification page (what it shows and what it must never show), the member's private page (headers, bad
 * tokens and their rate limit, uploads with and without consent / CSRF, streaming, the card download, e-mail again), and the admin side (designer abilities and preview,
 * member photo / proof / card actions for an editor, the same screens refused to anonymous visitors). Skips itself when the site does not answer.
 * NOTE: the web server's PHP may differ from the CLI (for example no GD), so these checks only assert what holds for both.
 */
declare(strict_types=1);

use App\Core\Auth;
use App\Core\Database;
use App\Core\RateLimit;
use App\Core\Roles;
use App\Core\Settings;
use App\Models\User;
use App\Services\Membership\Card\CardData;
use App\Services\Membership\Card\CardSettings;
use App\Services\Membership\CardService;
use App\Services\Membership\MemberFiles;
use App\Services\Membership\MembershipService as MS;

echo "Membership card: HTTP (verification page, private page, admin)\n";

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

/** A browser-like client with its own cookie jar. Redirects are followed by hand with GET (like a browser after a POST). @return callable(string,string,array=):array{0:int,1:string,2:array<string,string>} status, body, lower-case headers */
$hClient = static function () use ($hBase, &$mcTmp): callable {
    $jar = tempnam(sys_get_temp_dir(), 'mcj') ?: '';
    $mcTmp[] = $jar;
    $one = static function (string $method, string $url, array $o) use ($jar): array {
        $ch = curl_init($url);
        $opts = [CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true, CURLOPT_TIMEOUT => 40, CURLOPT_COOKIEJAR => $jar, CURLOPT_COOKIEFILE => $jar, CURLOPT_FOLLOWLOCATION => false, CURLOPT_CUSTOMREQUEST => $method];
        if (isset($o['form'])) {
            $opts[CURLOPT_POSTFIELDS] = http_build_query($o['form']);
        } elseif (isset($o['multipart'])) {
            $opts[CURLOPT_POSTFIELDS] = $o['multipart'];
        }
        $opts[CURLOPT_HTTPHEADER] = $o['headers'] ?? [];
        curl_setopt_array($ch, $opts);
        $raw = (string) curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $hs = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);
        $h = [];
        foreach (explode("\r\n", substr($raw, 0, $hs)) as $line) {
            if (str_contains($line, ':')) {
                [$k, $v] = explode(':', $line, 2);
                $h[strtolower(trim($k))] = trim($v);
            }
        }
        return [$status, substr($raw, $hs), $h];
    };
    return static function (string $method, string $path, array $o = []) use ($hBase, $one): array {
        $r = $one($method, $hBase . $path, $o);
        for ($i = 0; $i < 5 && !empty($o['follow']) && $r[0] >= 300 && $r[0] < 400 && !empty($r[2]['location']); $i++) {
            $loc = $r[2]['location'];
            $r = $one('GET', preg_match('~^https?://~i', $loc) ? $loc : preg_replace('~^(https?://[^/]+).*$~', '$1', $hBase) . $loc, []);
        }
        return $r;
    };
};
$hAttr = static fn(string $html, string $re): string => preg_match($re, $html, $m) === 1 ? html_entity_decode($m[1]) : '';
$hTok = static fn(string $html): string => $hAttr($html, '/name="_token" value="([^"]+)"/');
$hFile = static function (string $bytes, string $name, string $mime) use (&$mcTmp): \CURLFile {
    $p = tempnam(sys_get_temp_dir(), 'mcu') ?: '';
    file_put_contents($p, $bytes);
    $mcTmp[] = $p;
    return new \CURLFile($p, $mime, $name);
};
$hRandPw = static fn(): string => 'Zz' . bin2hex(random_bytes(7)) . '9q';
$okPngH = mc_png(330, 330, 2);
$pdfH = "%PDF-1.4\n1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 200 200] >>\nendobj\ntrailer\n<< /Root 1 0 R >>\n%%EOF\n";
foreach (['127.0.0.1', '::1'] as $ip) {
    foreach (['member-me-bad:', 'member-verify:', 'member-verify-bad:'] as $pre) {
        RateLimit::clear($pre . $ip);
    }
}

// ---------------------------------------------------------------------------------------------- the people
$h1 = $mcNew($typeA, 'h1', ['full_name' => 'Hanna Verify', 'phone' => '+256700123456', 'city' => 'Pece-Laroo', 'region' => 'Gulu', 'address' => 'Plot 7 Secret Lane']);
$hid1 = (int) $h1['id'];
MemberFiles::storePhoto($hid1, $mcTmpFile(mc_png(310, 310, 2), 'h.png'), ['consent' => '1', 'source' => 'dashboard', 'skip_rate' => true]);
MS::changeStatus($hid1, 'approved');
$mcPay($hid1, '60000');
$h1 = $mcRow($hid1);
$visitor = $hClient();
$tokenMe = (string) $h1['dashboard_token'];
$tokenV = (string) $h1['card_verify_token'];

// ---------------------------------------------------------------------------------------------- verification page
[$st, $body, $hd] = $visitor('GET', '/membership/verify/' . $tokenV);
$secrets = ['h1' . $mcDomain, '+256700123456', 'LAF-APP-', 'Plot 7 Secret Lane', 'Pece-Laroo', 'members/photos', 'storage/private', (string) $h1['dashboard_token'], (string) $h1['application_ref'], '1990-05-20', 'Em Contact'];
$leaked = array_filter($secrets, static fn(string $s): bool => str_contains($body, $s));
check('verify page: a valid card shows VALID, the name, type, number, member since and valid until (200)', $st === 200 && str_contains($body, 'Hanna Verify') && str_contains($body, (string) $h1['membership_number']) && str_contains($body, 'VALID')
    && str_contains($body, 'ZZ MC Annual') && str_contains($body, CardData::date(MS::lastValidDay((string) $h1['expiry_date']))) && str_contains($body, CardData::date($h1['start_date'])));
check('verify page: NOTHING else - no e-mail, phone, address, location, birth date, application reference, photo path, private-page token or emergency contact', $leaked === [] && !preg_match('/<img[^>]+(photo|members)/i', $body), implode(' | ', $leaked));
check('verify page: noindex (header and meta), never cached, no referrer, plain 200', str_contains($hd['x-robots-tag'] ?? '', 'noindex') && str_contains($body, 'noindex') && str_contains($hd['cache-control'] ?? '', 'no-store') && ($hd['referrer-policy'] ?? '') === 'no-referrer');
[$st2, $body2] = $visitor('GET', '/membership/verify/' . str_repeat('a', 32));
check('verify page: an unknown token says NOT VALID with HTTP 404 and no member data', $st2 === 404 && str_contains($body2, 'NOT VALID') && !str_contains($body2, 'Hanna') && !str_contains($body2, 'VALID MEMBER'));
$st3 = 404;                                                          // (the router ignores a trailing line break in the URL; CardService::validToken() is strict, see the flow checks)
[$st4] = $visitor('GET', '/membership/verify/' . strtoupper($tokenV));
[$st5, $body5] = $visitor('GET', '/membership/verify/' . $tokenMe);
check('verify page: a token in capitals, or the private-page token, do not verify (404, NOT VALID)', $st3 === 404 && $st4 === 404 && $st5 === 404 && str_contains($body5, 'NOT VALID') && !str_contains($body5, 'Hanna'));
CardService::revoke($hid1, 'selftest', null);
[$st6, $body6] = $visitor('GET', '/membership/verify/' . $tokenV);
CardService::reissue($hid1, ['force' => true, 'source' => 'manual', 'send' => false]);
$h1 = $mcRow($hid1);
$tokenV = (string) $h1['card_verify_token'];
[$st7, $body7] = $visitor('GET', '/membership/verify/' . $tokenV);
check('verify page: a revoked card is NOT VALID without data; after a re-issue the new link is VALID', $st6 === 404 && !str_contains($body6, 'Hanna') && $st7 === 200 && str_contains($body7, 'Hanna Verify'));
foreach (['127.0.0.1', '::1'] as $ip) {
    RateLimit::clear('member-verify-bad:' . $ip);
    RateLimit::clear('member-verify:' . $ip);
}
$nBad = 0;
$lastStatus = 0;
for ($i = 0; $i < 25; $i++) {
    [$lastStatus] = $visitor('GET', '/membership/verify/' . bin2hex(random_bytes(16)));
    $nBad++;
    if ($lastStatus === 429) {
        break;
    }
}
check('verify page: guessing tokens is rate limited (HTTP 429 after 20 failed lookups from one address), yet a genuine card still verifies from that address', $lastStatus === 429 && $nBad === 21 && $visitor('GET', '/membership/verify/' . $tokenV)[0] === 200, "$lastStatus after $nBad");
foreach (['127.0.0.1', '::1'] as $ip) {
    RateLimit::clear('member-verify-bad:' . $ip);
    RateLimit::clear('member-verify:' . $ip);
}

// ---------------------------------------------------------------------------------------------- credential pages: analytics off, and independent of the marketing page
$gaWas = [Settings::isCustomised('analytics_id'), (string) Settings::get('analytics_id', '')];
$pubWas = Database::value("SELECT is_published FROM pages WHERE slug = 'membership'");
try {
    Settings::set('analytics_id', 'G-ZZTEST0001');
    [, $gaReg] = $visitor('GET', '/membership/register');
    [, $gaVerify, $gaVerifyHd] = $visitor('GET', '/membership/verify/' . $tokenV);
    [, $gaMe, $gaMeHd] = $visitor('GET', '/membership/me/' . $tokenMe);
    [, $gaMe404, $gaMe404Hd] = $visitor('GET', '/membership/me/' . str_repeat('c', 64) . '/card.pdf');
    [, $gaConf] = $visitor('GET', '/membership/confirmation/LAF-APP-0000?t=1.abcdef');
    [, $gaRobots] = $visitor('GET', '/robots.txt');
    check('analytics: the Google tag is switched off ON THE SERVER for the verification page, the private page (and its error pages) and the confirmation page, while the registration page keeps it',
        str_contains($gaReg, 'data-ga-id="G-ZZTEST0001"') && !str_contains($gaVerify, 'G-ZZTEST0001') && !str_contains($gaMe, 'G-ZZTEST0001') && !str_contains($gaMe404, 'G-ZZTEST0001') && !str_contains($gaConf, 'G-ZZTEST0001')
        && !str_contains(implode(' ', [$gaVerifyHd['content-security-policy'] ?? '', $gaMeHd['content-security-policy'] ?? '', $gaMe404Hd['content-security-policy'] ?? '']), 'googletagmanager'));
    check('analytics: robots.txt keeps crawlers away from the private page, the verification page and the confirmation page', preg_match('~^Disallow: \S*/membership/me/\s*$~m', $gaRobots) === 1 && preg_match('~^Disallow: \S*/membership/verify/\s*$~m', $gaRobots) === 1 && preg_match('~^Disallow: \S*/membership/confirmation/\s*$~m', $gaRobots) === 1, substr($gaRobots, 0, 400));
} finally {
    $gaWas[0] ? Settings::set('analytics_id', $gaWas[1]) : Settings::reset('analytics_id');
}
try {
    Database::execute("UPDATE pages SET is_published = 0 WHERE slug = 'membership'");
    \App\Core\SitePages::flush();
    [$unVerify, $unVerifyBody] = $visitor('GET', '/membership/verify/' . $tokenV);
    [$unMe] = $visitor('GET', '/membership/me/' . $tokenMe);
    [$unPdf] = $visitor('GET', '/membership/me/' . $tokenMe . '/card.pdf');
    [$unReg] = $visitor('GET', '/membership/register');
    check('unpublished "Membership" page: the QR verification and the private page keep working (200), the marketing/registration page is hidden (404)', $unVerify === 200 && str_contains($unVerifyBody, 'Hanna Verify') && $unMe === 200 && $unPdf === 200 && $unReg === 404, "$unVerify $unMe $unPdf $unReg");
} finally {
    Database::execute('UPDATE pages SET is_published = ? WHERE slug = ?', [(int) $pubWas, 'membership']);
    \App\Core\SitePages::flush();
}

// ---------------------------------------------------------------------------------------------- the member's private page
[$st, $page, $hd] = $visitor('GET', '/membership/me/' . $tokenMe);
check('private page: 200 with the application reference, member number, the four steps, the card download and the details of THIS member', $st === 200 && str_contains($page, (string) $h1['application_ref']) && str_contains($page, (string) $h1['membership_number'])
    && str_contains($page, 'Card issued') && str_contains($page, '/membership/me/' . $tokenMe . '/card.pdf') && str_contains($page, 'Hanna Verify') && str_contains($page, 'Download my card'));
check('private page: noindex (header and meta), no-store, no referrer; no file path, no other member\'s data', str_contains($hd['x-robots-tag'] ?? '', 'noindex') && str_contains($page, 'content="noindex') && str_contains($hd['cache-control'] ?? '', 'no-store')
    && ($hd['referrer-policy'] ?? '') === 'no-referrer' && !str_contains($page, 'storage/private') && !str_contains($page, 'members/photos') && !str_contains($page, 'h2' . $mcDomain), '');
[$stBad, $bodyBad] = $visitor('GET', '/membership/me/' . str_repeat('b', 64));
check('private page: a wrong token gets the friendly "this link is not valid" page (404), nothing about any member', $stBad === 404 && str_contains($bodyBad, 'This link is not valid') && !str_contains($bodyBad, 'Hanna'));
$last = 0;
$count = 0;
for ($i = 0; $i < 25; $i++) {
    [$last] = $visitor('GET', '/membership/me/' . bin2hex(random_bytes(32)));
    $count++;
    if ($last === 429) {
        break;
    }
}
check('private page: failed token lookups are counted per address and answered 429 after 15, but a CORRECT token still opens its page (a shared address must not lock the holder out)', $last === 429 && $count <= 17 && $visitor('GET', '/membership/me/' . $tokenMe)[0] === 200, "$last after $count");
foreach (['127.0.0.1', '::1'] as $ip) {
    RateLimit::clear('member-me-bad:' . $ip);
}
// card download
[$stPdf, $pdfBody, $hdPdf] = $visitor('GET', '/membership/me/' . $tokenMe . '/card.pdf');
check('private page: the card downloads as a PDF attachment named Membership-card-<number>.pdf, private and never cached; the PDF is valid', $stPdf === 200 && str_starts_with($pdfBody, '%PDF-1.4') && mc_pdf_problems($pdfBody) === []
    && str_contains($hdPdf['content-disposition'] ?? '', 'Membership-card-' . $h1['membership_number'] . '.pdf') && str_contains($hdPdf['cache-control'] ?? '', 'no-store') && ($hdPdf['content-type'] ?? '') === 'application/pdf'
    && in_array('Hanna Verify', mc_pdf_strings($pdfBody), true));
[$stPng, , $hdPng] = $visitor('GET', '/membership/me/' . $tokenMe . '/card.png');
check('private page: the PNG preview is an image when the server has GD, a plain 404 when it does not', ($stPng === 200 && ($hdPng['content-type'] ?? '') === 'image/png') || $stPng === 404);
$h0 = $mcNew($typeA, 'h0');
[$stNoCard] = $visitor('GET', '/membership/me/' . $h0['dashboard_token'] . '/card.pdf');
check('private page: no card yet -> no card download (404)', $stNoCard === 404);
// without CSRF
[$stCsrf] = $visitor('POST', '/membership/me/' . $tokenMe . '/resend', ['form' => []]);
check('private page: a POST without a CSRF token is refused', in_array($stCsrf, [403, 419], true), (string) $stCsrf);
// resend by e-mail
$cardsBefore = count($mcCards('h1' . $mcDomain));
RateLimit::clear('member-card-resend:' . $hid1);
[, $pg] = $visitor('GET', '/membership/me/' . $tokenMe);
[$stRe, $reBody] = $visitor('POST', '/membership/me/' . $tokenMe . '/resend', ['form' => ['_token' => $hTok($pg)], 'follow' => true]);
check('private page: "send it to my e-mail again" mails the card (flash message shown)', $stRe === 200 && count($mcCards('h1' . $mcDomain)) === $cardsBefore + 1 && str_contains($reBody, 'We sent your card'));
RateLimit::clear('member-card-resend:' . $hid1);
// photo upload by the member
$h2 = $mcNew($typeB, 'h2');
$hid2 = (int) $h2['id'];
$visitor2 = $hClient();
$tokenMe2 = (string) $h2['dashboard_token'];
[, $pg2] = $visitor2('GET', '/membership/me/' . $tokenMe2);
$csrf2 = $hTok($pg2);
[$stNoC, $bNoC] = $visitor2('POST', '/membership/me/' . $tokenMe2 . '/photo', ['multipart' => ['_token' => $csrf2, 'photo' => $hFile($okPngH, 'face.png', 'image/png')], 'follow' => true]);
check('private page: a photo without the consent tick is refused with the editable message and nothing is stored', $stNoC === 200 && str_contains($bNoC, 'tick the box') && $mcRow($hid2)['photo_path'] === null);
[$stBadF, $bBadF] = $visitor2('POST', '/membership/me/' . $tokenMe2 . '/photo', ['multipart' => ['_token' => $csrf2, 'photo_consent' => '1', 'photo' => $hFile('<html><script>alert(1)</script></html>', 'face.png', 'image/png')], 'follow' => true]);
check('private page: an HTML file named face.png is refused ("choose a JPG, PNG or WebP"), nothing stored', $stBadF === 200 && str_contains($bBadF, 'JPG, PNG or WebP') && $mcRow($hid2)['photo_path'] === null);
[$stOk, $bOk] = $visitor2('POST', '/membership/me/' . $tokenMe2 . '/photo', ['multipart' => ['_token' => $csrf2, 'photo_consent' => '1', 'photo' => $hFile($okPngH, 'face.png', 'image/png')], 'follow' => true]);
$r2 = $mcRow($hid2);
check('private page: a photo with consent is stored (private path, consent time) and shown back on the page through the controller (no file path in the HTML)', $stOk === 200 && str_contains($bOk, 'Your photo was saved') && !empty($r2['photo_path'])
    && !empty($r2['photo_consent_at']) && str_contains($bOk, '/membership/me/' . $tokenMe2 . '/photo') && !str_contains($bOk, (string) $r2['photo_path']));
[$stPh, $phBody, $hdPh] = $visitor2('GET', '/membership/me/' . $tokenMe2 . '/photo');
check('private page: the photo streams back as an image to its owner, private and nosniff', $stPh === 200 && str_starts_with($hdPh['content-type'] ?? '', 'image/') && ($hdPh['x-content-type-options'] ?? '') === 'nosniff' && str_contains($hdPh['cache-control'] ?? '', 'no-store') && strlen($phBody) > 100);
[$stOther, $otherBody] = $visitor('GET', '/membership/me/' . $tokenMe . '/photo');
check('private page: another member\'s token gets their OWN photo (or 404), never this one', $stOther === 200 ? $otherBody !== $phBody : $stOther === 404);
[$stPr, $bPr] = $visitor2('POST', '/membership/me/' . $tokenMe2 . '/proof', ['multipart' => ['_token' => $csrf2, 'proof' => $hFile($pdfH, 'receipt.pdf', 'application/pdf')], 'follow' => true]);
$r2 = $mcRow($hid2);
check('private page: a PDF proof of payment is stored and shown as "Received - waiting for staff to verify"', $stPr === 200 && $r2['proof_status'] === 'received' && str_contains($bPr, 'waiting for staff to verify') && str_contains($bPr, 'Thank you. We received'));
[$stDl, $dlBody, $hdDl] = $visitor2('GET', '/membership/me/' . $tokenMe2 . '/proof');
check('private page: the owner can open their proof (a PDF always downloads as an attachment)', $stDl === 200 && str_starts_with($dlBody, '%PDF') && str_starts_with($hdDl['content-disposition'] ?? '', 'attachment'));
MemberFiles::setProofStatus($hid2, 'verified', null);
[, $pg3] = $visitor2('GET', '/membership/me/' . $tokenMe2);
[$stClosed, $bClosed] = $visitor2('POST', '/membership/me/' . $tokenMe2 . '/proof', ['multipart' => ['_token' => $hTok($pg3), 'proof' => $hFile($pdfH, 'again.pdf', 'application/pdf')], 'follow' => true]);
check('private page: once the proof is verified the form is gone and a direct upload is refused', !str_contains($pg3, 'name="proof"') && str_contains($bClosed, 'can no longer be uploaded'));

// ---------------------------------------------------------------------------------------------- admin
$anon = $hClient();
[$stA1] = $anon('GET', '/admin/membership-card');
[$stA2] = $anon('GET', '/admin/members/' . $hid2 . '/photo');
[$stA3] = $anon('GET', '/admin/members/' . $hid2 . '/proof');
[$stA4] = $anon('GET', '/admin/members/' . $hid1 . '/card.pdf');
[$stA5] = $anon('POST', '/admin/members/' . $hid1 . '/card/issue', ['form' => []]);
check('admin: an anonymous visitor cannot reach the designer, a member\'s photo, proof or card, nor press an action (redirected to the login, or refused)', array_filter([$stA1, $stA2, $stA3, $stA4, $stA5], static fn(int $s): bool => !in_array($s, [302, 403, 419], true)) === [],
    json_encode([$stA1, $stA2, $stA3, $stA4, $stA5]));
$pwAdmin = $hRandPw();
$pwEditor = $hRandPw();
$pwNone = $hRandPw();
Database::execute("INSERT INTO roles (slug, name, description, abilities, is_system) VALUES ('zz-mc-card-only', 'ZZ MC card only', '', ?, 0)", [json_encode(['dashboard.view', 'members.card'])]);
Roles::flush();
foreach ([['zz-mc-admin@example.test', 'admin', $pwAdmin], ['zz-mc-editor@example.test', 'editor', $pwEditor], ['zz-mc-cardonly@example.test', 'zz-mc-card-only', $pwNone]] as [$email, $role, $pw]) {
    User::create(['name' => 'ZZ MC ' . $role, 'email' => $email, 'password_hash' => Auth::hashPassword($pw), 'role' => $role, 'is_active' => 1, 'must_change_password' => 0]);
}
$hLogin = static function (callable $c, string $email, string $pw) use ($hTok): bool {
    [, $loginPage] = $c('GET', '/admin/login');
    $c('POST', '/admin/login', ['form' => ['_token' => $hTok($loginPage), 'email' => $email, 'password' => $pw]]);
    return $c('GET', '/admin')[0] === 200;
};
$admin = $hClient();
$editor = $hClient();
$cardOnly = $hClient();
if (!$hLogin($admin, 'zz-mc-admin@example.test', $pwAdmin) || !$hLogin($editor, 'zz-mc-editor@example.test', $pwEditor) || !$hLogin($cardOnly, 'zz-mc-cardonly@example.test', $pwNone)) {
    echo "  skip  the throw-away administrator, editor and card-only users could not sign in; the remaining admin checks are skipped\n";
    return;
}
[$stD1] = $editor('GET', '/admin/membership-card');
[$stD2] = $editor('POST', '/admin/membership-card', ['form' => ['membership_card_org_name' => 'Hacked']]);
[$stD3] = $cardOnly('GET', '/admin/membership-card');
[$stD4] = $cardOnly('GET', '/admin/members/' . $hid2);
[$stD5] = $cardOnly('GET', '/admin/members/' . $hid2 . '/photo');
check('admin: an editor (members.manage) cannot open or change the designer (403); a role with only members.card opens the designer but cannot see members or their photos (403)', $stD1 === 403 && $stD2 === 403 && $stD3 === 200 && $stD4 === 403 && $stD5 === 403,
    json_encode([$stD1, $stD2, $stD3, $stD4, $stD5]));
[$stD6, $designer] = $admin('GET', '/admin/membership-card');
check('admin: the administrator opens the designer: fields for the words, colours, blocks, number format, trigger and e-mail, a PDF preview frame and the test e-mail button', $stD6 === 200 && str_contains($designer, 'name="membership_card_org_name"')
    && str_contains($designer, 'name="membership_card_navy"') && str_contains($designer, 'name="membership_card_number_format"') && str_contains($designer, 'name="membership_card_trigger"') && str_contains($designer, 'name="membership_card_email_body"')
    && str_contains($designer, 'preview.pdf') && str_contains($designer, 'Send a test card to my e-mail') && str_contains($designer, 'LAF-ZA-') === true);
$tokenAdmin = $hTok($designer);
[$stP, $pj] = $admin('POST', '/admin/membership-card/preview', ['form' => ['_token' => $tokenAdmin, 'membership_card_org_name' => 'Preview Org Unsaved', 'membership_card_navy' => '#223355'], 'headers' => ['X-Requested-With: XMLHttpRequest']]);
$j = json_decode($pj, true);
[$stPdfP, $pdfP, $hdP] = $admin('GET', '/admin/membership-card/preview.pdf');
check('admin: the preview takes the UNSAVED form values and renders the real card as a same-origin PDF with the sample member and the watermark; nothing is saved', $stP === 200 && is_array($j) && !empty($j['ok']) && $stPdfP === 200
    && str_starts_with($pdfP, '%PDF-1.4') && mc_pdf_problems($pdfP) === [] && in_array('Preview Org Unsaved', mc_pdf_strings($pdfP), true) && count(array_filter(mc_pdf_strings($pdfP), static fn(string $x): bool => str_contains($x, 'SAMPLE CARD'))) === 1
    && ($hdP['content-type'] ?? '') === 'application/pdf' && str_contains($hdP['content-disposition'] ?? '', 'inline') && CardSettings::get('membership_card_org_name') === 'LUMINA AFRICA FOUNDATION' && str_contains($hdP['x-frame-options'] ?? '', 'SAMEORIGIN'));
[$stPng2, , $hdPng2] = $admin('GET', '/admin/membership-card/preview.png');
check('admin: the PNG preview is an image when the server has GD, a 404 when it does not', ($stPng2 === 200 && ($hdPng2['content-type'] ?? '') === 'image/png') || $stPng2 === 404);
$mailsBeforeTest = count($mcMails('zz-mc-admin@example.test'));
[$stT] = $admin('POST', '/admin/membership-card/test-email', ['form' => ['_token' => $tokenAdmin], 'follow' => true]);
$testMail = $mcMails('zz-mc-admin@example.test');
check('admin: "send a test card to my e-mail" goes to the signed-in administrator only, marked [TEST], with the sample card attached', $stT === 200 && count($testMail) === $mailsBeforeTest + 1 && str_contains($testMail[count($testMail) - 1]['subject'], '[TEST]') && $testMail[count($testMail) - 1]['pdf'] !== null);
[$stBadSave, $bodyBadSave] = $admin('POST', '/admin/membership-card', ['form' => ['_token' => $tokenAdmin, 'membership_card_navy' => 'javascript:alert(1)', 'membership_card_number_format' => '{PREFIX}/{SEQ}', 'membership_card_org_name' => 'X'], 'follow' => true]);
check('admin: hostile or invalid designer input is refused with messages next to the fields and nothing is saved', $stBadSave === 200 && str_contains($bodyBadSave, 'use a colour') && str_contains($bodyBadSave, 'capital letters, digits') && CardSettings::get('membership_card_org_name') === 'LUMINA AFRICA FOUNDATION');
[$stSave] = $admin('POST', '/admin/membership-card', ['form' => [
    '_token' => $tokenAdmin, 'membership_card_org_name' => 'ZZ Saved Org', 'membership_card_show_photo' => '1', 'membership_card_show_qr' => '1', 'membership_card_reissue_renewal' => '1', 'membership_photo_required' => '1',
    'membership_card_trigger' => 'approved', 'membership_card_number_format' => '{PREFIX}-{TYPE}-{YY}-{SEQ:5}'], 'follow' => true]);
Settings::flush();
$auditDetails = (string) Database::value("SELECT details FROM audit_log WHERE action = 'membership_card.settings' ORDER BY id DESC LIMIT 1", [], '');
$comp = [
    'status' => $stSave === 200, 'org' => CardSettings::get('membership_card_org_name') === 'ZZ Saved Org', 'trigger' => CardSettings::get('membership_card_trigger') === 'approved',
    'format' => CardSettings::get('membership_card_number_format') === '{PREFIX}-{TYPE}-{YY}-{SEQ:5}', 'navy unchanged' => CardSettings::get('membership_card_navy') === '#0F1B33',
    'require photo off' => CardSettings::get('membership_card_require_photo') === '0', 'only the 3 changed keys audited' => (function () use ($auditDetails): bool { $k = (json_decode($auditDetails, true)['keys'] ?? []); sort($k); return $k === ['membership_card_number_format', 'membership_card_org_name', 'membership_card_trigger']; })(),
    'audit has key names' => $auditDetails !== '' && str_contains($auditDetails, 'membership_card_org_name'), 'audit has no values' => !str_contains($auditDetails, 'ZZ Saved Org'),
];
check('admin: saving stores only what differs from the default (organisation name, trigger, number format), unticked boxes become off (require photo), and the change is audited by key names only', !in_array(false, $comp, true), json_encode(array_keys(array_filter($comp, static fn(bool $v): bool => !$v))) . ' ' . $auditDetails);
foreach (['membership_card_org_name', 'membership_card_trigger', 'membership_card_number_format'] as $k) {
    Settings::reset($k);
}
Settings::flush();

// the member screen for an editor: photo + proof + card panels, file streaming
[$stM, $screen] = $editor('GET', '/admin/members/' . $hid2);
check('admin: the member screen has the "Photo and proof of payment" and "Membership card" panels with the actions (editors may use them)', $stM === 200 && str_contains($screen, 'Photo and proof of payment') && str_contains($screen, 'Membership card')
    && str_contains($screen, '/admin/members/' . $hid2 . '/photo') && str_contains($screen, 'Mark as verified') && str_contains($screen, 'Issue card now'));
[$stSp, $spBody, $hdSp] = $editor('GET', '/admin/members/' . $hid2 . '/photo');
[$stSr, $srBody, $hdSr] = $editor('GET', '/admin/members/' . $hid2 . '/proof');
check('admin: staff stream the photo (image) and the PDF proof (attachment) through the controller, private and nosniff; a member without a proof is a 404', $stSp === 200 && str_starts_with($hdSp['content-type'] ?? '', 'image/') && $stSr === 200 && str_starts_with($srBody, '%PDF')
    && str_starts_with($hdSr['content-disposition'] ?? '', 'attachment') && str_contains($hdSp['cache-control'] ?? '', 'no-store') && ($hdSp['x-content-type-options'] ?? '') === 'nosniff' && $editor('GET', '/admin/members/' . (int) $h0['id'] . '/proof')[0] === 404);
$csrfE = $hTok($screen);
[, $afterReject] = $editor('POST', '/admin/members/' . $hid2 . '/proof/status', ['form' => ['_token' => $csrfE, 'status' => 'rejected'], 'follow' => true]);
$rejected = array_values(array_filter($mcMails('h2' . $mcDomain), static fn(array $m): bool => str_contains($m['subject'], 'clearer proof')));
check('admin: "needs a better copy" sets the status, e-mails the applicant once and tells the editor', $mcRow($hid2)['proof_status'] === 'rejected' && count($rejected) === 1 && str_contains($afterReject, 'asked for a clearer copy'));
[, $afterPhoto] = $editor('POST', '/admin/members/' . $hid2 . '/photo', ['multipart' => ['_token' => $csrfE, 'photo' => $hFile(mc_png(350, 350, 2), 'staff.png', 'image/png')], 'follow' => true]);
$rS = $mcRow($hid2);
check('admin: staff replace the photo without a consent tick (the applicant\'s consent time stays)', str_contains($afterPhoto, 'The photo was saved') && !empty($rS['photo_path']) && $rS['photo_path'] !== $r2['photo_path'] && !empty($rS['photo_consent_at']));
$editor('POST', '/admin/members/' . $hid2 . '/photo/remove', ['form' => ['_token' => $csrfE], 'follow' => true]);
check('admin: staff remove the photo (the file is deleted and the card would show the initials)', $mcRow($hid2)['photo_path'] === null && !is_file(STORAGE_PATH . '/private/' . $rS['photo_path']));
// card actions by an editor on a member whose card waits
Settings::set('membership_card_trigger', 'manual');
$h3 = $mcNew($typeB, 'h3');
$hid3 = (int) $h3['id'];
MS::changeStatus($hid3, 'approved');
[, $scr3] = $editor('GET', '/admin/members/' . $hid3);
$csrf3 = $hTok($scr3);
$cardsBefore3 = count($mcCards('h3' . $mcDomain));
[, $afterIssue] = $editor('POST', '/admin/members/' . $hid3 . '/card/issue', ['form' => ['_token' => $csrf3], 'follow' => true]);
$r3 = $mcRow($hid3);
check('admin: "Issue card now" by an editor issues version 1 (payment rule skipped), e-mails it and says so', $r3['card_status'] === 'issued' && (int) $r3['card_version'] === 1 && count($mcCards('h3' . $mcDomain)) === $cardsBefore3 + 1 && str_contains($afterIssue, 'issued'));
[$stCp, $cpBody, $hdCp] = $editor('GET', '/admin/members/' . $hid3 . '/card.pdf');
check('admin: staff download the issued card (PDF attachment, valid, snapshot of the member)', $stCp === 200 && mc_pdf_problems($cpBody) === [] && str_contains($hdCp['content-disposition'] ?? '', 'attachment') && in_array('MC Person h3', mc_pdf_strings($cpBody), true));
$editor('POST', '/admin/members/' . $hid3 . '/card/resend', ['form' => ['_token' => $csrf3], 'follow' => true]);
check('admin: "Re-send card e-mail" mails the card again', count($mcCards('h3' . $mcDomain)) === $cardsBefore3 + 2);
$oldTokenV = (string) $r3['card_verify_token'];
$editor('POST', '/admin/members/' . $hid3 . '/card/reissue', ['form' => ['_token' => $csrf3], 'follow' => true]);
$r3b = $mcRow($hid3);
check('admin: "Re-issue" makes version 2 with a new verification link; the old QR link stops verifying; the new one works', (int) $r3b['card_version'] === 2 && $r3b['card_verify_token'] !== $oldTokenV && $visitor('GET', '/membership/verify/' . $oldTokenV)[0] === 404
    && $visitor('GET', '/membership/verify/' . $r3b['card_verify_token'])[0] === 200);
$editor('POST', '/admin/members/' . $hid3 . '/card/revoke', ['form' => ['_token' => $csrf3, 'reason' => 'selftest'], 'follow' => true]);
check('admin: "Revoke" makes the QR code say NOT VALID at once', $mcRow($hid3)['card_status'] === 'revoked' && $visitor('GET', '/membership/verify/' . $r3b['card_verify_token'])[0] === 404);
$h4 = $mcNew($typeB, 'h4');
$h5 = $mcNew($typeB, 'h5');
MS::changeStatus((int) $h4['id'], 'approved');
$mcPay((int) $h4['id'], '20000');
[, $bulk] = $editor('POST', '/admin/members/card-bulk', ['form' => ['_token' => $csrf3, 'ids' => [(int) $h4['id'], (int) $h5['id'], 999999]], 'follow' => true]);
check('admin: bulk issue applies the NORMAL rules per member (approved and paid: issued; pending: skipped with the reason; unknown id: skipped), one summary', $mcRow((int) $h4['id'])['card_status'] === 'issued' && $mcRow((int) $h5['id'])['card_status'] === 'none'
    && str_contains($bulk, '1 card issued') && str_contains($bulk, 'Waiting for the application to be approved'));
Settings::reset('membership_card_trigger');
check('admin: the sidebar shows the card designer to the administrator only, and "Needs proof check" with its counter to staff', str_contains($designer, 'admin/membership-card') && !str_contains($screen, 'admin/membership-card') && str_contains($screen, 'proof=received'));
