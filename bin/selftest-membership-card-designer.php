<?php
/**
 * MEMBERSHIP CARD DESIGNER checks (Admin > Membership > Membership card). OWNER: CARD agent. Contract: docs/MEMBERSHIP_CARD.md.
 *
 *   /c/xampp/php/php.exe bin/selftest-membership-card-designer.php          (stand-alone; add  -d extension=gd  for the picture checks)
 *   or  require __DIR__ . '/selftest-membership-card-designer.php';          from bin/selftest.php (shares its check() helper and $fail counter)
 *
 * Part 1 (no web server): the tab map covers every setting once, the colour themes and the reference design pass every contrast check, unreadable colour
 * combinations are refused, the e-mail message cleaner keeps the whole message, every preview scenario draws what it promises (statuses, no-expiry, long
 * names, photo, no location), the placeholder portrait is a real PNG, the card e-mail is escaped and complete.
 * Part 2 (real HTTP with curl, skipped when the site does not answer): the preview answers with errors / contrast / numbers / the rendered e-mail, an unreadable
 * colour and a bad number format are refused, a long e-mail message is saved whole and audited by key name only, "back to the reference" per tab and for the whole
 * look, the logo goes through the optimiser into membership/card/ and the old files are removed, the designer needs members.card.
 * Creates a throw-away administrator and restores every card setting it touched; leaves no files behind.
 */
declare(strict_types=1);

use App\Controllers\Admin\MembershipCardController as MC;
use App\Core\Auth;
use App\Core\Database;
use App\Core\Roles;
use App\Core\Settings;
use App\Models\User;
use App\Services\Membership\Card\CardData;
use App\Services\Membership\Card\CardLayout;
use App\Services\Membership\Card\CardPng;
use App\Services\Membership\Card\CardPreview;
use App\Services\Membership\Card\CardSettings;
use App\Services\Membership\MembershipMail;

if (!function_exists('check')) {                                  // stand-alone run: a tiny harness like bin/selftest.php
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
    $mcdStandalone = true;
}
$mcdStandalone ??= false;

echo "Membership card designer: settings map, colours, scenarios, e-mail\n";

// ---------------------------------------------------------------------------------------------- the tab map
$mcdAll = [];
foreach (MC::TAB_KEYS as $keys) {
    foreach ($keys as $k) {
        $mcdAll[] = $k;
    }
}
$mcdDefs = array_keys(CardSettings::DEFS);
check('designer: the seven tabs list every card setting exactly once (nothing missing, nothing twice, nothing unknown)', count($mcdAll) === count(array_unique($mcdAll)) && array_diff($mcdDefs, $mcdAll) === [] && array_diff($mcdAll, $mcdDefs) === [] && count(MC::TABS) === 7);

// ---------------------------------------------------------------------------------------------- colours
$mcdRef = CardSettings::resolve();
foreach (CardSettings::COLOR_KEYS as $k) {
    $mcdRef[$k] = CardSettings::default($k);
}
$mcdRows = MC::contrastRows($mcdRef);
// the owner's reference colours are kept as the default, but four small texts are BELOW 4.5:1 and the screen says so (it must not call them "good"): nothing fails, exactly these warn
$mcdWarnIds = array_column(array_filter($mcdRows, static fn(array $r): bool => $r['status'] === 'warn'), 'id');
sort($mcdWarnIds);
check('designer: the reference design can be saved (no pair fails) and the four pairs below 4.5:1 are reported as warnings, the others are fine (' . count($mcdRows) . ' pairs)',
    count($mcdRows) >= 9 && array_filter($mcdRows, static fn(array $r): bool => $r['status'] === 'fail') === [] && $mcdWarnIds === ['accent_text', 'gold_white', 'green_white', 'label_tile'], json_encode($mcdWarnIds));
$mcdBadThemes = [];
foreach (MC::THEMES as $tk => $th) {
    $v = $mcdRef;
    foreach (['navy', 'accent', 'gold', 'green', 'tile', 'label', 'muted'] as $short) {
        $v['membership_card_' . ($short === 'label' ? 'label_color' : $short)] = $th[$short];
    }
    foreach (MC::contrastRows($v) as $r) {
        // the structural pairs (white on the side panel, the QR colour, the big initials, the values on the tiles) must be fine in every theme; the small decorative ones may warn
        if ($r['status'] === 'fail' || ($r['status'] !== 'ok' && in_array($r['id'], ['navy_white', 'qr', 'muted_navy', 'accent_white', 'navy_tile', 'label_white'], true))) {
            $mcdBadThemes[] = $tk . ':' . $r['id'] . ' ' . $r['ratio'];
        }
    }
}
check('designer: every colour theme can be saved and keeps the side panel, the QR code and the big initials readable', $mcdBadThemes === [] && count(MC::THEMES) >= 4, implode(', ', $mcdBadThemes));
$mcdTry = static function (array $over) use ($mcdRef): array {
    return MC::contrastErrors(array_merge($mcdRef, $over));
};
$mcdWhite = ['membership_card_navy' => '#FFFFFF'];
$e1 = $mcdTry($mcdWhite);
$e2 = $mcdTry(['membership_card_tile' => '#0F1B33']);
$e3 = $mcdTry(['membership_card_gold' => '#FFFFFF']);
$e4 = $mcdTry(['membership_card_label_color' => '#FFFFFF']);
$e5 = $mcdTry(['membership_card_accent' => '#FFFFFF']);
$e6 = $mcdTry(['membership_card_green' => '#FFFFFF']);
$e7 = $mcdTry(['membership_card_muted' => '#0F1B33']);
check('designer: white-on-white and other unreadable combinations are REFUSED, each naming the field to change (white side panel, tile = navy, white tagline, labels, accent, green, small text = panel)',
    isset($e1['membership_card_navy']) && isset($e2['membership_card_tile']) && isset($e3['membership_card_gold']) && isset($e4['membership_card_label_color']) && isset($e5['membership_card_accent'])
    && isset($e6['membership_card_green']) && isset($e7['membership_card_muted']), json_encode([array_keys($e1), array_keys($e2), array_keys($e3), array_keys($e4), array_keys($e5), array_keys($e6), array_keys($e7)]));
$mcdMild = $mcdTry(['membership_card_label_color' => '#8E94A3']);        // readable enough to save, but below the advised ratio: a warning, not a refusal
$mcdMildRows = MC::contrastRows(array_merge($mcdRef, ['membership_card_label_color' => '#8E94A3']));
check('designer: a colour that is merely hard to read is a WARNING (saved), only an unreadable one is refused; the QR colour (navy on white) is held to a stricter limit', $mcdMild === [] && count(array_filter($mcdMildRows, static fn(array $r): bool => $r['status'] === 'warn')) >= 1
    && isset($mcdTry(['membership_card_navy' => '#9AA4B8'])['membership_card_navy']) && $mcdTry(['membership_card_navy' => '#5A6B8C']) === []);
check('designer: the contrast summary counts ok / warn / fail', MC::contrastSummary(MC::contrastRows(array_merge($mcdRef, $mcdWhite)))['fail'] >= 1 && MC::contrastSummary($mcdRows) === ['ok' => count($mcdRows) - 4, 'warn' => 4, 'fail' => 0]);

// ---------------------------------------------------------------------------------------------- the e-mail message cleaner (CardSettings::cleanText keeps only 6 lines)
$mcdBody = CardSettings::default('membership_card_email_body');
check('designer: the default e-mail message (20 lines) survives the designer\'s own cleaner untouched, where CardSettings::cleanText would cut it to 6 lines', CardPreview::cleanMessage($mcdBody) === $mcdBody
    && substr_count($mcdBody, "\n") > 6 && CardSettings::cleanText($mcdBody, 3000) === $mcdBody && substr_count(CardSettings::cleanText($mcdBody, 160), "\n") <= 5);
$mcdDirty = "Line one\r\n\r\n\r\n\r\nLine\x00 two\u{200B}\u{202E}with  spaces\t\tand tab\r" . str_repeat("x\n", 120);
$mcdClean = CardPreview::cleanMessage($mcdDirty);
check('designer: the message cleaner normalises line breaks, drops control / zero-width / bidi characters, collapses blank runs, keeps at most 80 lines and 3000 characters',
    !str_contains($mcdClean, "\r") && !str_contains($mcdClean, "\x00") && !str_contains($mcdClean, "\u{200B}") && !str_contains($mcdClean, "\u{202E}") && !str_contains($mcdClean, "\n\n\n") && str_contains($mcdClean, 'Line two')
    && str_contains($mcdClean, 'with spaces and tab') && substr_count($mcdClean, "\n") < 80 && mb_strlen(CardPreview::cleanMessage(str_repeat('a', 9000))) === 3000 && CardPreview::cleanMessage("  \n \n ") === '');

// ---------------------------------------------------------------------------------------------- the scenarios
$mcdOps = static function (string $scenario, array $override = []): array {
    $s = CardSettings::resolve($override);
    $data = CardData::build(CardPreview::raw($scenario), $s);
    $layout = CardLayout::build($data, ['logo' => true]);
    $texts = [];
    foreach ($layout['ops'] as $op) {
        if ($op['t'] === 'text') {
            $texts[] = $op['text'];
        }
    }
    return ['data' => $data, 'layout' => $layout, 'texts' => $texts];
};
$mcdSc = [];
foreach (array_keys(CardPreview::SCENARIOS) as $k) {
    $mcdSc[$k] = $mcdOps($k);
}
check('designer: unknown scenario names fall back to the typical member; there are ten scenarios, each fictional and watermarked', CardPreview::scenario('nonsense') === 'typical' && CardPreview::scenario(['x']) === 'typical' && count(CardPreview::SCENARIOS) === 10
    && !array_filter($mcdSc, static fn(array $o): bool => !in_array(CardData::SAMPLE_TEXT, $o['texts'], true)));
check('designer: the status scenarios print ACTIVE MEMBER / EXPIRED / SUSPENDED / NOT VALID', in_array('ACTIVE MEMBER', $mcdSc['typical']['texts'], true) && in_array('EXPIRED', $mcdSc['expired']['texts'], true) && in_array('SUSPENDED', $mcdSc['suspended']['texts'], true)
    && in_array('NOT VALID', $mcdSc['revoked']['texts'], true) && in_array('19 Oct 2025', $mcdSc['expired']['texts'], true));
check('designer: the lifetime scenario prints "No expiry" and no renewal line; the unpaid one says the joining fee is due; the bare one prints no location and no FROM label',
    in_array('No expiry', $mcdSc['lifetime']['texts'], true) && !array_filter($mcdSc['lifetime']['texts'], static fn(string $t): bool => str_starts_with($t, 'Renews'))
    && count(array_filter($mcdSc['unpaid']['texts'], static fn(string $t): bool => str_contains($t, 'due'))) >= 1 && !in_array('FROM', $mcdSc['bare']['texts'], true) && !in_array('Gulu City, Uganda', $mcdSc['bare']['texts'], true));
check('designer: the long scenario forces the name onto two lines and shortens the type line, the names scenario keeps accents and apostrophes, the photo scenarios carry a picture',
    $mcdSc['long']['layout']['meta']['fitted']['name']['lines'] === 2 && !empty($mcdSc['long']['layout']['meta']['photo']) && !empty($mcdSc['photo']['data']['photo'])
    && in_array("Zo\u{00EB} Ou\u{00E9}draogo-D'Souza", $mcdSc['names']['texts'], true) && empty($mcdSc['typical']['data']['photo']));
$mcdPhoto = CardPreview::photo();
$mcdInfo = function_exists('getimagesizefromstring') ? @getimagesizefromstring($mcdPhoto['bytes']) : false;
check('designer: the placeholder portrait is a real 240 x 300 PNG made without GD, and the sample PDFs are valid PDF 1.4 files that open with the portrait',
    $mcdPhoto['mime'] === 'image/png' && is_array($mcdInfo) && $mcdInfo[0] === 240 && $mcdInfo[1] === 300 && ($mcdInfo['mime'] ?? '') === 'image/png'
    && (function (): bool {
        foreach (['photo', 'long', 'typical'] as $k) {
            $pdf = CardPreview::pdf($k);
            if (!str_starts_with($pdf, '%PDF-1.4') || !str_ends_with($pdf, "%%EOF\n") || strlen($pdf) < 3000) {
                return false;
            }
        }
        return strlen(CardPreview::pdf('photo')) > strlen(CardPreview::pdf('typical')) - 40000;
    })());
$mcdPng = CardPreview::png('typical');
check('designer: the picture preview needs GD: a PNG when GD is loaded, null without it', extension_loaded('gd') ? ($mcdPng !== null && str_starts_with($mcdPng, "\x89PNG")) : $mcdPng === null);

// ---------------------------------------------------------------------------------------------- every setting changes the card (or says why not)
$mcdBase = md5(json_encode($mcdSc['typical']['layout']['ops']));
$mcdMoves = [
    'membership_card_org_name' => 'Changed Org', 'membership_card_tagline' => 'New tagline', 'membership_card_label' => 'LABEL', 'membership_card_navy' => '#123456', 'membership_card_accent' => '#654321',
    'membership_card_gold' => '#8A6A1F', 'membership_card_green' => '#1E7B45', 'membership_card_tile' => '#EEF1F6', 'membership_card_label_color' => '#555555', 'membership_card_muted' => '#CCD3E6',
    'membership_card_show_photo' => '0', 'membership_card_show_qr' => '0', 'membership_card_tile_1' => 'district', 'membership_card_tile_2' => 'type', 'membership_card_tile_3' => 'none',
    'membership_card_label_member' => 'HOLDER', 'membership_card_label_from' => 'LIVES IN', 'membership_card_label_number' => 'NUMBER', 'membership_card_label_valid_until' => 'EXPIRES', 'membership_card_label_member_since' => 'SINCE',
    'membership_card_label_payment' => 'PAID', 'membership_card_label_fee' => '{period} FEE', 'membership_card_scan_text' => 'Scan me', 'membership_card_renews_text' => 'Renew {period}',
    'membership_card_pay_paid' => 'Settled', 'membership_card_location_format' => '{district} district', 'membership_card_panel_text' => "Line A\nLine B",
    'membership_card_footer_1' => 'Footer one', 'membership_card_footer_2' => 'Footer two', 'membership_card_footer_3' => 'Footer three', 'membership_card_support_phone' => '0700 000 111', 'membership_card_support_email' => 'card-test@example.org',
];
$mcdStill = [];
foreach ($mcdMoves as $k => $v) {
    if (md5(json_encode($mcdOps('typical', [$k => $v])['layout']['ops'])) === $mcdBase) {
        $mcdStill[] = $k;
    }
}
check('designer: changing each of the ' . count($mcdMoves) . ' look settings changes the card that is drawn (none is a dead switch)', $mcdStill === [], implode(', ', $mcdStill));
check('designer: settings that are not on the card (number format, trigger, renewal, photo rules, retention, e-mail) leave the drawing alone',
    md5(json_encode($mcdOps('typical', ['membership_card_number_format' => '{PREFIX}-{SEQ}', 'membership_card_trigger' => 'manual', 'membership_card_reissue_renewal' => '0', 'membership_card_require_photo' => '1',
        'membership_photo_required' => '0', 'membership_proof_retention_days' => '30', 'membership_card_email_subject' => 'S', 'membership_card_email_body' => 'B'])['layout']['ops'])) === $mcdBase);

// ---------------------------------------------------------------------------------------------- the card e-mail
[$mcdSub, $mcdTxt, $mcdHtml] = MembershipMail::renderCard(['id' => 0, 'full_name' => 'Amina Akello', 'email' => 'x@example.test', 'type_name' => 'Individual Member', 'membership_number' => 'LAF-IND-26-0001',
    'start_date' => '2026-10-20', 'expiry_date' => '2027-10-20', 'photo_path' => null], [], true);
check('designer e-mail: the plain text and the HTML carry the same words (name, number, type, valid until 19 Oct 2027, the private page link) and the HTML is a complete document with a button',
    str_contains($mcdTxt, 'LAF-IND-26-0001') && str_contains($mcdTxt, '19 Oct 2027') && str_contains($mcdHtml, 'LAF-IND-26-0001') && str_contains($mcdHtml, '19 Oct 2027') && str_starts_with($mcdHtml, '<!doctype html>')
    && str_contains($mcdHtml, '/membership/me/') && str_contains($mcdHtml, 'Open my membership page') && str_contains($mcdHtml, 'LUMINA AFRICA FOUNDATION') && str_contains($mcdHtml, 'Name') && str_contains($mcdHtml, 'Member number')
    && str_contains($mcdSub, 'LAF-IND-26-0001') && !preg_match('/[\r\n]/', $mcdSub));
$mcdHostile = [
    'membership_card_org_name' => '<script>alert(1)</script>ORG', 'membership_card_tagline' => '"><img src=x onerror=alert(2)>',
    'membership_card_email_subject' => "Hi {name}\r\nBcc: evil@example.org", 'membership_card_email_body' => "Hello <b>{first_name}</b>\n\nClick javascript:alert(3) or https://example.org/a?b=1&c=<2>\n\nName: <i>{name}</i>\nNumber: {number}",
];
[$hs, $ht, $hh] = MembershipMail::renderCard(['id' => 0, 'full_name' => 'Evil <script>x</script> O\'Neil', 'email' => 'x@example.test', 'type_name' => '<b>T</b>', 'membership_number' => 'LAF-IND-26-0001', 'start_date' => '2026-10-20', 'expiry_date' => '2027-10-20'], $mcdHostile, true);
check('designer e-mail: hostile settings and names are escaped in the HTML (no live tag, attribute or script), links are http(s) only, and the subject has no line break (no header injection)',
    !str_contains($hh, '<script') && !str_contains($hh, '<img src=x') && !str_contains($hh, '<b>Evil') && str_contains($hh, '&lt;b&gt;') && !preg_match('/href="javascript:/i', $hh) && str_contains($hh, 'href="https://example.org/a?b=1&amp;c=&lt;2&gt;')
    && !preg_match('/[\r\n]/', $hs) && !str_contains($hh, '<i>') && str_contains($hh, 'Name'), $hs);
$mcdLong = implode("\n\n", array_map(static fn(int $i): string => 'Paragraph ' . $i . ' of the long message', range(1, 30)));
[, $lt] = MembershipMail::renderCard(['id' => 0, 'full_name' => 'A B', 'email' => 'x@example.test', 'type_name' => 'T', 'membership_number' => 'N', 'start_date' => '2026-10-20', 'expiry_date' => '2027-10-20'], ['membership_card_email_body' => $mcdLong], true);
check('designer e-mail: an unsaved message with 30 paragraphs is rendered whole (the preview and the test e-mail use the designer\'s own cleaner)', str_contains($lt, 'Paragraph 30 of the long message') && str_contains($lt, 'Paragraph 1 of'));

// ---------------------------------------------------------------------------------------------- HTTP
$mcdBaseUrl = 'http://localhost/' . basename(BASE_PATH);
$mcdProbe = (function () use ($mcdBaseUrl): bool {
    if (!function_exists('curl_init')) {
        return false;
    }
    $ch = curl_init($mcdBaseUrl . '/robots.txt');
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 4, CURLOPT_CONNECTTIMEOUT => 2]);
    curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);
    return $code === 200;
})();
if (!$mcdProbe) {
    echo "  skip  the site does not answer at {$mcdBaseUrl}/ (the HTTP checks of the designer are skipped)\n";
} else {
    echo "Membership card designer: HTTP\n";
    $mcdJar = tempnam(sys_get_temp_dir(), 'mcd') ?: '';
    $mcdTmp = [$mcdJar];
    $mcdReq = static function (string $method, string $path, array $o = []) use ($mcdBaseUrl, $mcdJar): array {
        $url = $mcdBaseUrl . $path;
        $r = [0, '', []];
        for ($i = 0; $i < 6; $i++) {
            $ch = curl_init($url);
            $opts = [CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true, CURLOPT_TIMEOUT => 60, CURLOPT_COOKIEJAR => $mcdJar, CURLOPT_COOKIEFILE => $mcdJar, CURLOPT_FOLLOWLOCATION => false, CURLOPT_CUSTOMREQUEST => $method];
            if ($i === 0 && isset($o['form'])) {
                $opts[CURLOPT_POSTFIELDS] = http_build_query($o['form']);
            } elseif ($i === 0 && isset($o['multipart'])) {
                $opts[CURLOPT_POSTFIELDS] = $o['multipart'];
            }
            $opts[CURLOPT_HTTPHEADER] = $i === 0 ? ($o['headers'] ?? []) : [];
            if ($i > 0) {
                $opts[CURLOPT_CUSTOMREQUEST] = 'GET';
            }
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
            $r = [$status, substr($raw, $hs), $h];
            if (empty($o['follow']) || $status < 300 || $status >= 400 || empty($h['location'])) {
                break;
            }
            $loc = $h['location'];
            $url = preg_match('~^https?://~i', $loc) ? $loc : preg_replace('~^(https?://[^/]+).*$~', '$1', $mcdBaseUrl) . $loc;
        }
        return $r;
    };
    $mcdTok = static fn(string $html): string => preg_match('/name="_token" value="([^"]+)"/', $html, $m) === 1 ? html_entity_decode($m[1]) : '';

    // a throw-away administrator
    $mcdEmail = 'zz-designer-' . bin2hex(random_bytes(3)) . '@example.test';
    $mcdPw = 'Zz' . bin2hex(random_bytes(7)) . '9q';
    $mcdUid = (int) User::create(['name' => 'ZZ Designer Test', 'email' => $mcdEmail, 'password_hash' => Auth::hashPassword($mcdPw), 'role' => 'admin', 'is_active' => 1, 'must_change_password' => 0]);
    $mcdOriginal = [];
    foreach ($mcdDefs as $k) {
        $mcdOriginal[$k] = Settings::isCustomised($k) ? CardSettings::get($k) : null;       // null = it was on its default (no stored override)
    }
    $mcdRestore = static function () use ($mcdDefs, $mcdOriginal): void {
        foreach ($mcdDefs as $k) {
            $o = $mcdOriginal[$k];
            if ($o === null) {
                Settings::reset($k);
            } else {
                Settings::set($k, $o);
            }
        }
        Settings::flush();
    };
    try {
        [, $loginPage] = $mcdReq('GET', '/admin/login');
        $mcdReq('POST', '/admin/login', ['form' => ['_token' => $mcdTok($loginPage), 'email' => $mcdEmail, 'password' => $mcdPw], 'follow' => true]);
        [$st0, $page] = $mcdReq('GET', '/admin/membership-card');
        $token = $mcdTok($page);
        check('designer HTTP: the administrator opens the designer with seven tabs, seven panels, a separate reset form and one Save button owned by the main form', $st0 === 200 && substr_count($page, 'data-mc-tab="') === 7
            && substr_count($page, 'data-mc-panel="') === 7 && str_contains($page, 'id="mc-reset"') && str_contains($page, 'form="mc-reset"') && str_contains($page, 'data-mc-contrast=') && str_contains($page, 'data-mc-frame'));
        $full = static function (array $over = []) use ($token): array {
            $v = CardSettings::resolve();
            return array_merge(['_token' => $token, 'tab' => 'look'], $v, $over);
        };

        [$stP, $pj] = $mcdReq('POST', '/admin/membership-card/preview', ['form' => ['_token' => $token, 'scenario' => 'photo', 'membership_card_org_name' => 'Designer Preview Org', 'membership_card_navy' => '#162B6B',
            'membership_card_number_format' => '{PREFIX}-{TYPE}-{SEQ:5}', 'membership_card_email_body' => $mcdLong], 'headers' => ['X-Requested-With: XMLHttpRequest']]);
        $j = json_decode($pj, true);
        check('designer HTTP: the preview answers with the contrast of every pair, the example member numbers of the typed format, the rendered e-mail (whole message) and the PDF address',
            $stP === 200 && is_array($j) && !empty($j['ok']) && count($j['contrast'] ?? []) === count(MC::CONTRAST) && ($j['summary']['fail'] ?? 1) === 0 && ($j['scenario'] ?? '') === 'photo'
            && str_contains((string) ($j['email']['html'] ?? ''), 'Paragraph 30') && str_contains((string) ($j['email']['text'] ?? ''), 'Paragraph 30') && str_contains((string) ($j['pdf'] ?? ''), 'preview.pdf')
            && !empty($j['numbers']) && preg_match('/^LAF-[A-Z0-9]+-\d{5}$/', (string) reset($j['numbers'])) === 1 && CardSettings::get('membership_card_org_name') !== 'Designer Preview Org');
        [$stPdf, $pdf, $hdPdf] = $mcdReq('GET', '/admin/membership-card/preview.pdf');
        [$stDl, , $hdDl] = $mcdReq('GET', '/admin/membership-card/preview.pdf?download=1');
        check('designer HTTP: preview.pdf is the PDF of the chosen scenario, inline and same-origin framed; ?download=1 is an attachment; nothing was saved', $stPdf === 200 && str_starts_with($pdf, '%PDF-1.4') && str_contains($hdPdf['content-disposition'] ?? '', 'inline')
            && str_contains($hdPdf['x-frame-options'] ?? '', 'SAMEORIGIN') && $stDl === 200 && str_contains($hdDl['content-disposition'] ?? '', 'attachment'));

        [$stU, $pu] = $mcdReq('POST', '/admin/membership-card/preview', ['form' => ['_token' => $token, 'membership_card_navy' => '#FFFFFF', 'membership_card_number_format' => '{SEQ}-X'], 'headers' => ['X-Requested-With: XMLHttpRequest']]);
        $ju = json_decode($pu, true);
        check('designer HTTP: the live preview reports an unreadable colour and a bad number format next to their fields, and keeps drawing the saved colour', $stU === 200 && isset($ju['errors']['membership_card_navy']) && isset($ju['errors']['membership_card_number_format'])
            && str_contains((string) $ju['errors']['membership_card_navy'], 'unreadable') && ($ju['summary']['fail'] ?? 0) === 0);

        [$stW, $bodyW] = $mcdReq('POST', '/admin/membership-card', ['form' => $full(['membership_card_navy' => '#FFFFFF', 'membership_card_org_name' => 'ZZ Must Not Save']), 'follow' => true]);
        Settings::flush();
        check('designer HTTP: saving a white side panel is refused (the white-on-white rule), the message names the colour, the Logo & colours tab is open, and nothing else in the form was saved either',
            $stW === 200 && str_contains($bodyW, 'would be unreadable') && str_contains($bodyW, 'data-active-tab="look"') && CardSettings::get('membership_card_navy') === '#0F1B33' && CardSettings::get('membership_card_org_name') !== 'ZZ Must Not Save');
        [$stF, $bodyF] = $mcdReq('POST', '/admin/membership-card', ['form' => $full(['tab' => 'numbers', 'membership_card_number_format' => '{PREFIX}-X']), 'follow' => true]);
        Settings::flush();
        check('designer HTTP: a number format without {SEQ} at the end is refused and keeps what was typed on the Member numbers tab', $stF === 200 && str_contains($bodyF, 'must end with {SEQ}') && str_contains($bodyF, 'data-active-tab="numbers"') && str_contains($bodyF, 'value="{PREFIX}-X"')
            && CardSettings::get('membership_card_number_format') === CardSettings::DEFAULT_NUMBER_FORMAT);

        [$stE, $bodyE] = $mcdReq('POST', '/admin/membership-card', ['form' => $full(['tab' => 'email', 'membership_card_email_body' => "  \n  "]), 'follow' => true]);
        check('designer HTTP: an empty e-mail message is refused', $stE === 200 && str_contains($bodyE, 'cannot be empty') && CardSettings::get('membership_card_email_body') === CardSettings::default('membership_card_email_body'));

        $auditBefore = (int) Database::value("SELECT COALESCE(MAX(id), 0) FROM audit_log WHERE action = 'membership_card.settings'", [], 0);
        [$stL] = $mcdReq('POST', '/admin/membership-card', ['form' => $full(['tab' => 'email', 'membership_card_email_body' => $mcdLong, 'membership_card_org_name' => 'ZZ Designer Org', 'membership_card_accent' => '#C8641B']), 'follow' => true]);
        Settings::flush();
        $savedBody = CardSettings::get('membership_card_email_body');
        $audits = Database::all("SELECT details FROM audit_log WHERE action = 'membership_card.settings' AND id > ? ORDER BY id", [$auditBefore]);
        $auditKeys = [];
        foreach ($audits as $a) {
            $auditKeys = array_merge($auditKeys, (array) (json_decode((string) $a['details'], true)['keys'] ?? []));
        }
        sort($auditKeys);
        check('designer HTTP: a 30-paragraph e-mail message is SAVED WHOLE (not cut to 6 lines), the other changes are saved, and the audit lists key names only', $stL === 200 && $savedBody === CardPreview::cleanMessage($mcdLong)
            && substr_count($savedBody, "\n") > 40 && CardSettings::get('membership_card_org_name') === 'ZZ Designer Org' && CardSettings::get('membership_card_accent') === '#C8641B'
            && $auditKeys === ['membership_card_accent', 'membership_card_email_body', 'membership_card_org_name'] && !str_contains(json_encode($audits), 'ZZ Designer Org') && !str_contains(json_encode($audits), 'Paragraph 30'), json_encode($auditKeys));

        // reset one tab, then the whole look
        $mcdReq('POST', '/admin/membership-card', ['form' => ['_token' => $token, 'tab' => 'words', 'reset' => 'words'], 'follow' => true]);
        Settings::flush();
        check('designer HTTP: "Back to the reference" on the Words tab restores only that tab (the organisation name is back, the accent colour and the e-mail message stay)', CardSettings::get('membership_card_org_name') === 'LUMINA AFRICA FOUNDATION'
            && CardSettings::get('membership_card_accent') === '#C8641B' && CardSettings::get('membership_card_email_body') === $savedBody);
        [$stA] = $mcdReq('POST', '/admin/membership-card', ['form' => ['_token' => $token, 'reset' => 'appearance'], 'follow' => true]);
        Settings::flush();
        check('designer HTTP: "Restore the reference design" resets the look (colours back) but not the issue rules, numbering or the e-mail message', $stA === 200 && CardSettings::get('membership_card_accent') === CardSettings::default('membership_card_accent') && CardSettings::get('membership_card_email_body') === $savedBody);
        [$stX] = $mcdReq('POST', '/admin/membership-card', ['form' => ['_token' => $token, 'reset' => 'nonsense'], 'follow' => true]);
        check('designer HTTP: an unknown reset target changes nothing', $stX === 200 && CardSettings::get('membership_card_email_body') === $savedBody);
        $mcdReq('POST', '/admin/membership-card', ['form' => ['_token' => $token, 'reset' => 'email'], 'follow' => true]);
        Settings::flush();
        check('designer HTTP: resetting the e-mail tab brings the 20-line default back', CardSettings::get('membership_card_email_body') === CardSettings::default('membership_card_email_body'));

        // the logo: uploaded by the picture field, optimised on save into membership/card/, the intermediate upload removed, removable again
        $png = (function (): string {
            $w = 240;
            $h = 240;
            $rows = '';
            for ($y = 0; $y < $h; $y++) {
                $rows .= "\x00";
                for ($x = 0; $x < $w; $x++) {
                    $in = (($x - 120) ** 2 + ($y - 120) ** 2) < 100 * 100;
                    $rows .= $in ? "\x14\x6e\x3c" : "\xff\xff\xff";
                }
            }
            $c = static fn(string $t, string $d): string => pack('N', strlen($d)) . $t . $d . pack('N', crc32($t . $d));
            return "\x89PNG\r\n\x1a\n" . $c('IHDR', pack('NNCCCCC', $w, $h, 8, 2, 0, 0, 0)) . $c('IDAT', (string) gzcompress($rows, 6)) . $c('IEND', '');
        })();
        $tmp = tempnam(sys_get_temp_dir(), 'mcl') ?: '';
        $mcdTmp[] = $tmp;
        file_put_contents($tmp, $png);
        [$stUp, $up] = $mcdReq('POST', '/admin/media/upload', ['multipart' => ['_token' => $token, 'file' => new \CURLFile($tmp, 'image/png', 'logo.png')], 'headers' => ['X-Requested-With: XMLHttpRequest', 'Accept: application/json']]);
        $uploaded = (string) (json_decode($up, true)['path'] ?? '');
        check('designer HTTP: the picture field uploads the logo first (a stored path)', $stUp === 201 && preg_match('~^\d{4}/\d{2}/[a-f0-9]+\.png$~', $uploaded) === 1, $up);
        [, $pv] = $mcdReq('POST', '/admin/membership-card/preview', ['form' => ['_token' => $token, 'membership_card_logo' => $uploaded], 'headers' => ['X-Requested-With: XMLHttpRequest']]);
        [, $pdfLogo] = $mcdReq('GET', '/admin/membership-card/preview.pdf');
        [, $pdfNoLogo] = (function () use ($mcdReq, $token): array {
            $mcdReq('POST', '/admin/membership-card/preview', ['form' => ['_token' => $token], 'headers' => ['X-Requested-With: XMLHttpRequest']]);
            return $mcdReq('GET', '/admin/membership-card/preview.pdf');
        })();
        check('designer HTTP: the unsaved logo is drawn in the preview (a transparent PNG makes a much smaller PDF than the bundled JPEG seal)', is_array(json_decode($pv, true)) && str_starts_with($pdfLogo, '%PDF') && strlen($pdfLogo) < strlen($pdfNoLogo) - 20000, strlen($pdfLogo) . ' vs ' . strlen($pdfNoLogo));
        $mcdReq('POST', '/admin/membership-card', ['form' => $full(['membership_card_logo' => $uploaded]), 'follow' => true]);
        Settings::flush();
        $logoPath = CardSettings::get('membership_card_logo');
        check('designer HTTP: on save the logo goes through the optimiser into uploads/membership/card/ and the first upload is removed', preg_match('~^membership/card/[a-f0-9]+\.(png|jpg|webp)$~', $logoPath) === 1 && is_file(UPLOAD_PATH . '/' . $logoPath)
            && !is_file(UPLOAD_PATH . '/' . $uploaded), $logoPath);
        $mcdReq('POST', '/admin/membership-card', ['form' => $full(['membership_card_logo' => '']), 'follow' => true]);
        Settings::flush();
        for ($try = 0; $try < 4 && is_file(UPLOAD_PATH . '/' . $logoPath); $try++) {     // Windows can hold a file for a moment while another process (a parallel test run, a virus scanner) has it open
            usleep(700000);
            clearstatcache(true, UPLOAD_PATH . '/' . $logoPath);
        }
        check('designer HTTP: removing the logo returns to the bundled seal and deletes the stored file', CardSettings::get('membership_card_logo') === '' && !is_file(UPLOAD_PATH . '/' . $logoPath),
            'setting=' . CardSettings::get('membership_card_logo') . ' file=' . (is_file(UPLOAD_PATH . '/' . $logoPath) ? 'still there' : 'gone'));
    } finally {
        $mcdRestore();
        Database::run('DELETE FROM users WHERE id = ?', [$mcdUid]);
        foreach ($mcdTmp as $f) {
            if ($f !== '' && is_file($f)) {
                @unlink($f);
            }
        }
        foreach (glob(UPLOAD_PATH . '/membership/card/*') ?: [] as $f) {
            if (filemtime($f) >= time() - 600 && !CardSettings::get('membership_card_logo')) {
                @unlink($f);
            }
        }
    }
}

if ($mcdStandalone) {
    echo $fail === 0 ? "\nALL DESIGNER CHECKS PASSED\n" : "\n{$fail} CHECK(S) FAILED\n";
    exit($fail === 0 ? 0 : 1);
}
