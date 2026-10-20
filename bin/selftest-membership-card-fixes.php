<?php
/**
 * MEMBERSHIP CARD, regression checks for the findings of the security / functional / accessibility / editability review (docs/MEMBERSHIP_CARD.md section 15).
 *
 *   /c/xampp/php/php.exe bin/selftest-membership-card-fixes.php        (stand-alone; the HTTP-side fixes - analytics, unpublished page, rate limits - are in
 *   bin/selftest-membership-card-http.php, the registry coverage in -flow.php)
 *   or  require __DIR__ . '/selftest-membership-card-fixes.php';        from bin/selftest.php (shares its check() helper and $fail counter)
 *
 * Covers: the strict PDF scan (object streams whose dictionary hides /ObjStm), the PNG decompression bomb in the no-GD card writer and the order "render, then
 * claim the e-mail", the retention purge and erasure (card photo copy, snapshot, tokens, status reason), parallel renewal events (one new version), the transliteration
 * of African and decomposed letters, names the card cannot draw, bidi controls, the revoked dashboard state, the words of the card in the designer, a waived joining fee,
 * the number-format separator rule, a fee change flagging a re-issue, the proof file of a deleted payment row, the seed-safe settings rows, the contrast report and the
 * accessible e-mail colours. Creates members @mcfix.invalid and types zz-mf-*, removes them and their files afterwards.
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
    function check(string $name, bool $ok, string $detail = ''): void
    {
        global $fail;
        if (!$ok) {
            $fail++;
        }
        echo ($ok ? 'ok    ' : 'FAIL  ') . $name . (!$ok && $detail !== '' ? '  [' . $detail . ']' : '') . "\n";
    }
}

use App\Core\Database;
use App\Core\ImageOptimizer;
use App\Core\PdfValidator;
use App\Core\Settings;
use App\Core\UploadException;
use App\Core\Uploader;
use App\Models\Member;
use App\Services\Membership\Card\CardData;
use App\Services\Membership\Card\CardSettings;
use App\Services\Membership\Card\PdfWriter;
use App\Services\Membership\CardService;
use App\Services\Membership\MemberFiles;
use App\Services\Membership\MembershipApplication;
use App\Services\Membership\MembershipService as MS;

echo "Membership card: review fixes\n";

$mfStart = time() - 2;
$mfDomain = '@mcfix.invalid';
$mfTmp = [];
$mfSaved = [];
foreach (['membership_open', 'membership_card_trigger', 'membership_card_tagline', 'membership_card_number_format', 'membership_proof_retention_days'] as $k) {
    $mfSaved[$k] = [Settings::isCustomised($k), (string) Settings::get($k, '')];
}
$mfTmpFile = static function (string $bytes, string $name = 'x.bin') use (&$mfTmp): array {
    $p = tempnam(sys_get_temp_dir(), 'mfx') ?: '';
    file_put_contents($p, $bytes);
    $mfTmp[] = $p;
    return ['name' => $name, 'type' => 'application/octet-stream', 'tmp_name' => $p, 'error' => UPLOAD_ERR_OK, 'size' => strlen($bytes)];
};
$mfErased = [];
$mfClean = static function () use (&$mfSaved, &$mfTmp, &$mfErased, $mfDomain, $mfStart): void {
    $extra = $mfErased === [] ? '0' : implode(',', array_map('intval', $mfErased));
    foreach (Database::all("SELECT * FROM members WHERE email LIKE ? OR id IN ($extra)", ['%' . $mfDomain]) as $m) {
        MemberFiles::deleteAll($m);
        if (!empty($m['card_photo_path'])) {
            Uploader::deletePrivate((string) $m['card_photo_path']);
        }
    }
    Database::execute("DELETE FROM member_payments WHERE member_id IN (SELECT id FROM members WHERE email LIKE ? OR id IN ($extra))", ['%' . $mfDomain]);
    Database::execute("DELETE FROM members WHERE email LIKE ? OR id IN ($extra)", ['%' . $mfDomain]);
    Database::execute("DELETE FROM membership_types WHERE slug LIKE 'zz-mf-%'");
    Database::execute('DELETE FROM subscribers WHERE email LIKE ?', ['%' . $mfDomain]);
    Database::execute("DELETE FROM audit_log WHERE created_at >= ? AND (action LIKE 'member.%' OR action LIKE 'membership_%') AND user_name = 'system'", [date('Y-m-d H:i:s', $mfStart)]);
    foreach (glob(STORAGE_PATH . '/mail/*.eml') ?: [] as $f) {
        if (filemtime($f) >= $mfStart && str_contains((string) file_get_contents($f), $mfDomain)) {
            @unlink($f);
        }
    }
    foreach ($mfSaved as $k => [$custom, $val]) {
        $custom ? Settings::set($k, $val) : Settings::reset($k);
    }
    foreach ($mfTmp as $p) {
        @unlink($p);
    }
    MS::flush();
    Settings::flush();
};
$mfClean();
Settings::set('membership_open', '1');
Settings::reset('membership_card_trigger');
Settings::reset('membership_card_number_format');

// ------------------------------------------------------------------------------------------------ helpers
$mfPng = static function (int $w, int $h, int $type, string $rawPixels): string {
    $chunk = static fn(string $t, string $d): string => pack('N', strlen($d)) . $t . $d . pack('N', crc32($t . $d));
    return "\x89PNG\r\n\x1a\n" . $chunk('IHDR', pack('NNCCCCC', $w, $h, 8, $type, 0, 0, 0)) . $chunk('IDAT', gzcompress($rawPixels)) . $chunk('IEND', '');
};
$mfSolid = static function (int $w, int $h, int $type): string {          // an opaque 8-bit PNG of one grey, row filter 0
    $bpp = [0 => 1, 2 => 3, 4 => 2, 6 => 4][$type];
    $row = "\x00" . str_repeat(str_repeat("\x80", $bpp), $w);
    return str_repeat($row, $h);
};
$mfType = ['name' => 'ZZ MF Annual', 'slug' => 'zz-mf-annual', 'code' => 'ZMF', 'fee_amount' => '50000', 'joining_fee' => '10000', 'currency' => 'UGX', 'period' => 'annual', 'is_active' => '1', 'sort_order' => 9201];
$mfT = MS::saveType(null, $mfType);
$mfTypeId = (int) $mfT['id'];
$mfBase = static fn(string $tag, array $over = []): array => $over + [
    'type_id' => $mfTypeId, 'full_name' => 'MF Person ' . $tag, 'gender' => 'female', 'date_of_birth' => '1990-05-20', 'nationality' => 'Ugandan',
    'email' => $tag . $mfDomain, 'phone' => '+256700000001', 'country' => 'Uganda', 'region' => 'Gulu', 'city' => 'Laroo',
    'occupation_status' => 'employed', 'education_level' => 'diploma', 'availability' => 'weekends', 'heard_about' => 'website',
    'motivation' => 'I would like to volunteer and support young people.', 'emergency_name' => 'Em Contact', 'emergency_phone' => '+256700000002',
    'emergency_relationship' => 'Sister', 'payment_method' => 'mobile_money', 'consent_terms' => '1', 'consent_privacy' => '1', 'declaration' => '1',
];
$mfNew = static function (string $tag, array $over = []) use ($mfBase): array {
    $r = MembershipApplication::process($mfBase($tag, $over), ['ip' => '203.0.113.9', 'user_agent' => 'selftest']);
    if (!$r['ok'] || empty($r['member'])) {
        throw new \RuntimeException('could not create member ' . $tag . ': ' . json_encode($r['errors'] ?? $r));
    }
    return (array) $r['member'];
};
$mfMails = static function (string $email) use ($mfStart): int {
    $n = 0;
    foreach (glob(STORAGE_PATH . '/mail/*.eml') ?: [] as $f) {
        if (filemtime($f) >= $mfStart && preg_match('/^To: ' . preg_quote($email, '/') . '\r?$/mi', (string) file_get_contents($f)) && str_contains((string) file_get_contents($f), 'Membership-card')) {
            $n++;
        }
    }
    return $n;
};
$mfPaid = static function (int $id, string $amount = '60000'): void {
    MS::recordPayment($id, ['amount' => $amount, 'status' => 'received', 'purpose' => 'membership', 'method' => 'mobile_money']);
};
$mfPdfDir = sys_get_temp_dir();

try {
    // ---------------------------------------------------------------------------------------------- 1. strict PDF scan: object streams
    $mfObjPdf = static function (string $dictExtra, string $payload, bool $fakeEnd = false, bool $damaged = false): string {
        $z = gzcompress($payload);
        if ($damaged) {
            $z = substr($z, 0, -4) . "    ";            // wrong checksum: PHP's gzuncompress() gives up, a lenient viewer still reads the stream
        }
        if ($fakeEnd) {                        // the compressed bytes carry the text "endstream" (a stored block), which cuts a naive scan short
            $z = "\x78\x01" . "\x00" . pack('v', 9) . pack('v', ~9 & 0xFFFF) . 'endstream' . "\x01" . pack('v', strlen($payload)) . pack('v', ~strlen($payload) & 0xFFFF) . $payload . pack('N', hexdec(hash('adler32', 'endstream' . $payload)));
        }
        return "%PDF-1.5\n1 0 obj\n<< /Type /Catalog /Pages 2 0 R /OpenAction 5 0 R >>\nendobj\n2 0 obj\n<< /Type /Pages /Kids [] /Count 0 >>\nendobj\n4 0 obj\n<< /Type /ObjStm /N 1 /First 4 /Filter /FlateDecode "
            . $dictExtra . ' /Length ' . strlen($z) . " >>\nstream\n" . $z . "\nendstream\nendobj\ntrailer\n<< /Root 1 0 R >>\n%%EOF\n";
    };
    $js = '5 0 << /S /JavaScript /JS (app.alert(1)) >>';
    $variants = [
        'plain' => $mfObjPdf('', $js), 'extra key containing "obj"' => $mfObjPdf('/Aobj null', $js), 'comment containing "obj"' => $mfObjPdf("\n%obj\n", $js),
        '2600 bytes of padding' => $mfObjPdf("\n%" . str_repeat(' ', 2600) . "\n", $js), 'fake "endstream" inside the compressed bytes' => $mfObjPdf('', $js, true), 'damaged checksum' => $mfObjPdf('', $js, false, true),
        'a filter chain (zlib inside zlib)' => $mfObjPdf('', gzcompress($js)), 'a predictor on the object stream' => $mfObjPdf('/DecodeParms << /Predictor 12 /Columns 4 >>', '5 0 << /Foo /Bar >>'),
        'a "stream" word in a comment before the dictionary' => str_replace('4 0 obj', "%stream
4 0 obj", $mfObjPdf('', $js)),
        'remote GoToR action' => $mfObjPdf("\n%" . str_repeat(' ', 2600) . "\n", '5 0 << /S /GoToR /F (\\\\\\\\evil\\\\share\\\\x.pdf) >>'),
    ];
    $accepted = [];
    foreach ($variants as $name => $bytes) {
        $f = $mfPdfDir . '/mfx-' . md5($name) . '.pdf';
        file_put_contents($f, $bytes);
        $mfTmp[] = $f;
        try {
            PdfValidator::inspect($f, 1000000, true);
            $accepted[] = $name;
        } catch (UploadException) {
        }
    }
    check('PDF strict scan: JavaScript / GoToR in an object stream is refused however the stream dictionary is padded or disguised', $accepted === [], implode(' | ', $accepted));
    $f = $mfPdfDir . '/mfx-benign.pdf';
    file_put_contents($f, $mfObjPdf('', '5 0 << /Foo /Bar >>'));
    $mfTmp[] = $f;
    $benign = true;
    try {
        PdfValidator::inspect($f, 1000000, true);
    } catch (UploadException) {
        $benign = false;
    }
    check('PDF strict scan: a harmless object stream is still accepted', $benign);

    // ---------------------------------------------------------------------------------------------- 2. PNG bomb in the pure-PHP card writer
    $deflate = deflate_init(ZLIB_ENCODING_DEFLATE);
    $bomb = '';
    for ($i = 0; $i < 120; $i++) {
        $bomb .= deflate_add($deflate, str_repeat("\0", 1048576), ZLIB_NO_FLUSH);
    }
    $bomb .= deflate_add($deflate, '', ZLIB_FINISH);                     // 120 MB of zeros in about 120 KB
    $chunk = static fn(string $t, string $d): string => pack('N', strlen($d)) . $t . $d . pack('N', crc32($t . $d));
    $bombPng = "\x89PNG\r\n\x1a\n" . $chunk('IHDR', pack('NNCCCCC', 1000, 1000, 8, 6, 0, 0, 0)) . $chunk('IDAT', $bomb) . $chunk('IEND', '');
    $before = memory_get_peak_usage(true);
    $threw = false;
    try {
        (new PdfWriter())->addPng($bombPng);
    } catch (\InvalidArgumentException) {
        $threw = true;
    }
    check('PDF writer: a PNG whose pixel data inflates far beyond its header is refused at once (no 100 MB allocation)', $threw && (memory_get_peak_usage(true) - $before) < 48 * 1048576, (string) ((memory_get_peak_usage(true) - $before) / 1048576));
    $mfBig = $mfPng(1500, 1500, 6, '');                                   // header only matters for imageInfo()
    check('PDF writer: a large PNG with transparency is not embedded (decoding it in PHP would take seconds); a small one is', PdfWriter::imageInfo($mfBig) === null
        && PdfWriter::imageInfo($mfPng(300, 300, 6, $mfSolid(300, 300, 6))) !== null && PdfWriter::imageInfo($mfPng(1500, 1500, 2, '')) !== null);
    $caps = ImageOptimizer::capabilities();
    if (!$caps['gd']) {
        $up = MemberFiles::inspectUpload($mfTmpFile($mfPng(1500, 1500, 6, $mfSolid(1500, 1500, 6)), 'big.png'), 'photo');
        check('photo upload without GD: a big PNG with transparency is refused up front with its own message (the card could not print it)', $up['ok'] === false && $up['code'] === 'unsupported', json_encode($up));
    } else {
        check('photo upload with GD: the big transparent PNG is accepted (it is re-encoded as a small JPEG)', MemberFiles::inspectUpload($mfTmpFile($mfPng(1500, 1500, 6, $mfSolid(1500, 1500, 6)), 'big.png'), 'photo')['ok'] === true);
    }

    // ---------------------------------------------------------------------------------------------- 3. the card e-mail: render first, then claim
    $m1 = $mfNew('deliver');
    $id1 = (int) $m1['id'];
    $rel = MemberFiles::PHOTO_DIR . '/' . bin2hex(random_bytes(16)) . '.png';
    @mkdir(STORAGE_PATH . '/private/' . MemberFiles::PHOTO_DIR, 0775, true);
    file_put_contents(STORAGE_PATH . '/private/' . $rel, $bombPng);        // a hostile photo that got into storage (no GD at upload time)
    Database::update('members', ['photo_path' => $rel, 'photo_consent_at' => date('Y-m-d H:i:s')], 'id = ?', [$id1]);
    MS::changeStatus($id1, 'approved');
    $t0 = microtime(true);
    $mfPaid($id1);
    $took = microtime(true) - $t0;
    $r1 = Member::find($id1);
    check('card e-mail: even with a hostile photo in storage the card is issued, drawn without the picture, and e-mailed exactly once, quickly', $r1['card_status'] === 'issued' && (int) $r1['card_emailed_version'] === 1 && $mfMails($m1['email']) === 1 && $took < 20, 'mails=' . $mfMails($m1['email']) . ' ' . round($took, 1) . 's');
    $order = (string) file_get_contents(dirname(__DIR__) . '/app/services/Membership/CardService.php');
    $deliverSrc = substr($order, (int) strpos($order, 'private static function deliver('), 2200);
    check('card e-mail: deliver() renders the PDF BEFORE it claims card_emailed_version (a fatal while drawing can no longer leave "e-mailed" recorded)', strpos($deliverSrc, 'self::pdf(') !== false && strpos($deliverSrc, 'self::pdf(') < strpos($deliverSrc, 'UPDATE members SET card_emailed_version'));

    // ---------------------------------------------------------------------------------------------- 4. retention, erasure
    $m2 = $mfNew('retain');
    $id2 = (int) $m2['id'];
    MS::changeStatus($id2, 'approved');
    $photoBytes = $mfPng(400, 400, 2, $mfSolid(400, 400, 2));
    $rel2 = MemberFiles::PHOTO_DIR . '/' . bin2hex(random_bytes(16)) . '.png';
    file_put_contents(STORAGE_PATH . '/private/' . $rel2, $photoBytes);
    Database::update('members', ['photo_path' => $rel2, 'photo_consent_at' => date('Y-m-d H:i:s')], 'id = ?', [$id2]);
    $mfPaid($id2);
    $r2 = Member::find($id2);
    $cardPhoto = (string) ($r2['card_photo_path'] ?? '');
    check('retention setup: an issued card with its own photo copy, a snapshot and both tokens', $r2['card_status'] === 'issued' && $cardPhoto !== '' && is_file(STORAGE_PATH . '/private/' . $cardPhoto) && !empty($r2['card_snapshot']) && !empty($r2['card_verify_token']) && !empty($r2['dashboard_token']));
    MS::changeStatus($id2, 'cancelled', ['reason' => 'zz test']);
    Database::execute('UPDATE members SET status_changed_at = ? WHERE id = ?', [date('Y-m-d H:i:s', time() - 200 * 86400), $id2]);
    check('cancelled member: the card cannot be downloaded any more and a re-send is refused', CardService::pdf((array) Member::find($id2)) === null && CardService::resend($id2)['ok'] === false);
    $purge = MemberFiles::purgeStale(90);
    $r2 = (array) Member::find($id2);
    check('retention purge: the card\'s photo copy and file, the snapshot and both tokens go too, the card is revoked, the private page and the QR link stop working',
        $purge['members'] >= 1 && $r2['photo_path'] === null && $r2['card_photo_path'] === null && !is_file(STORAGE_PATH . '/private/' . $cardPhoto) && $r2['card_snapshot'] === null
        && $r2['dashboard_token'] === null && $r2['card_verify_token'] === null && $r2['card_status'] === 'revoked' && MS::memberByDashboardToken((string) Member::find($id2)['dashboard_token']) === null
        && CardService::pdf($r2) === null, json_encode(array_intersect_key($r2, array_flip(['photo_path', 'card_photo_path', 'card_status', 'dashboard_token']))));
    $m3 = $mfNew('erase');
    $id3 = (int) $m3['id'];
    MS::changeStatus($id3, 'rejected', ['reason' => 'Cancelled at request of Brenda Atim (phone 0771234567) after dispute']);
    $er = MembershipApplication::anonymise($id3, null);
    $mfErased[] = $id3;
    $r3 = (array) Member::find($id3);
    check('erasure: the free-text reason typed by staff (it may name people) is erased with the rest', $er['ok'] && ($r3['status_reason'] ?? null) === null && $r3['full_name'] === 'Erased on request', json_encode($er));

    // ---------------------------------------------------------------------------------------------- 5. parallel renewal events: one new version
    $m4 = $mfNew('renew');
    $id4 = (int) $m4['id'];
    MS::changeStatus($id4, 'approved');
    $mfPaid($id4);
    Database::execute('UPDATE members SET card_valid_until = DATE_SUB(expiry_date, INTERVAL 1 YEAR) WHERE id = ?', [$id4]);      // the card says the previous term: a renewal is due
    $php = PHP_BINARY;
    $script = 'require ' . var_export(dirname(__DIR__) . '/app/bootstrap.php', true) . '; $s = microtime(true) + 0.6; while (microtime(true) < $s) { usleep(300); } echo json_encode(\App\Services\Membership\CardService::event(' . $id4 . ', "payment")["version"] ?? null);';
    $procs = [];
    for ($i = 0; $i < 4; $i++) {
        $procs[] = proc_open([$php, '-r', $script], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
        $procs[$i] = [$procs[$i], $pipes];
    }
    $outs = [];
    foreach ($procs as [$p, $pipes]) {
        $outs[] = trim((string) stream_get_contents($pipes[1]));
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($p);
    }
    $r4 = (array) Member::find($id4);
    check('renewal race: four events at the same instant make ONE new card version, one new e-mail and one valid QR link', (int) $r4['card_version'] === 2 && (int) $r4['card_emailed_version'] === 2 && $mfMails($m4['email']) === 2, 'version ' . $r4['card_version'] . ' mails ' . $mfMails($m4['email']) . ' ' . json_encode($outs));
    $again = CardService::reissue($id4, ['source' => 'renewal', 'only_if_renewal_due' => true, 'send' => false]);
    $forced = CardService::reissue($id4, ['source' => 'manual', 'send' => false]);
    check('renewal race: an automatic re-issue does nothing when the card is already current, a staff re-issue still makes a new version', $again['ok'] && $again['already'] && !$again['issued'] && $forced['issued'] && (int) $forced['version'] === 3);

    // ---------------------------------------------------------------------------------------------- 6. letters
    $w = static fn(string $s): string => PdfWriter::toWinAnsi($s);
    check('letters: open O, open E, schwa, eng and the hooked letters print as the nearest plain letter (Lugbara, Madi, Alur, Hausa)', $w('Ɔdɔŋ Ɛsther ǝ Ɗ ɗ ƙ Ɨ') === 'Odon Esther e D d k I', bin2hex($w('Ɔdɔŋ Ɛsther ǝ Ɗ ɗ ƙ Ɨ')));
    check('letters: Yoruba dotted letters, Vietnamese letters and other Latin Extended letters are transliterated, not turned into "?"', $w('Ṣọlá Ọkọ') === "Sol\xE1 Oko" && $w('Nguyễn Văn Đình') === "Nguyen Van D\xECnh" && $w('Łukasz Ħ') === 'Lukasz H');
    check('letters: decomposed (NFD) names compose to the WinAnsi letter, other marks vanish, and unknown scripts still give "?"', $w("Jose\u{301} Mun\u{303}oz") === "Jos\xE9 Mu\xF1oz" && $w("n\u{301}") === 'n' && $w('محمد') === '????');
    check('letters: bidi overrides and isolates never reach the card', $w("Evil\u{202E}Name\u{2066}x") === 'EvilNamex');
    check('names the card cannot draw: detected for Arabic / Chinese / emoji, not for accents or African letters; the initials use the printable words', CardData::hasUnprintable('محمد علي') && CardData::hasUnprintable('Amina 😀')
        && !CardData::hasUnprintable('José Ɔdɔŋ Ṣọlá') && CardData::initials('王小明 Wang') === 'WA' && CardData::initials('Иван Петров') === 'M' && CardData::initials('Amina Akello') === 'AA');
    $m5 = $mfNew('arabic', ['full_name' => 'محمد علي']);
    check('names the card cannot draw: the staff card summary says so', CardService::summary((array) Member::find((int) $m5['id']))['unprintable'] === true && CardService::summary((array) Member::find($id1))['unprintable'] === false);
    $m6 = $mfNew('bidi', ['full_name' => "Evil\u{202E}Name Sihtkcab"]);
    check('bidi controls typed in a name are removed when the application is saved, and the verification data uses the same cleaner', !str_contains((string) Member::find((int) $m6['id'])['full_name'], "\u{202E}") && MembershipApplication::line("A\u{200F}B\u{2067}C") === 'ABC');

    // ---------------------------------------------------------------------------------------------- 7. dashboard state of a revoked card
    $r1 = (array) Member::find($id1);
    CardService::revoke($id1, 'zz');
    $r1 = (array) Member::find($id1);
    $tl = CardService::timeline($r1);
    check('revoked card: the private page does not say "being prepared" and the card step is not in progress', CardService::dashboardState($r1) === 'revoked' && $tl['card'] === 'todo' && PageRegistryHas('dashboard.state_revoked'));

    // ---------------------------------------------------------------------------------------------- 8. the words of the card
    $base = CardSettings::defaults();
    $sw = $base + [];
    foreach (['pay_free' => 'Bure', 'pay_partial' => 'Kidogo', 'pay_tbd' => 'Haijawekwa', 'fee_free' => 'Bila malipo', 'fee_tbd' => 'Itatangazwa', 'fee_per_year' => 'kwa mwaka', 'fee_per_month' => 'kwa mwezi', 'fee_for_life' => '(maisha)',
        'period_annual' => 'KILA MWAKA', 'adverb_annual' => 'kila mwaka', 'joining_paid' => 'Ada ya kujiunga {amount} imelipwa', 'joining_due' => 'Ada ya kujiunga {amount} inadaiwa', 'joining_waived' => 'Ada ya kujiunga {amount} imesamehewa'] as $k => $v) {
        $sw['membership_card_' . $k] = $v;
    }
    $sw['membership_card_tile_1'] = 'payment';
    $sw['membership_card_tile_2'] = 'fee';
    $sw['membership_card_tile_3'] = 'fee';
    $raw = CardData::sampleRaw();
    $tiles = static fn(array $r, array $s): array => array_column(CardData::build($r, $s)['tiles'], 'value');
    $d = CardData::build($raw + [], $sw);
    $feeWord = $tiles(['pay_state' => 'paid'] + $raw, $sw);
    check('card words: the fee suffix, period word, renewal word and joining-fee line come from the settings', in_array('UGX 50,000 kwa mwaka', $feeWord, true) && $d['renews_text'] === 'Renews kila mwaka' && $d['footer'][0] === 'Ada ya kujiunga UGX 50,000 imelipwa · Issued 20 Oct 2026'
        && CardData::build(['fee_amount' => '0'] + $raw, $sw)['tiles'][1]['value'] === 'Bila malipo' && CardData::build(['fee_amount' => null] + $raw, $sw)['tiles'][1]['value'] === 'Itatangazwa'
        && CardData::build(['pay_state' => 'partial'] + $raw, $sw)['tiles'][0]['value'] === 'Kidogo' && CardData::build(['pay_state' => 'free'] + $raw, $sw)['tiles'][0]['value'] === 'Bure' && CardData::build(['pay_state' => 'tbd'] + $raw, $sw)['tiles'][0]['value'] === 'Haijawekwa',
        json_encode([$feeWord, $d['renews_text'], $d['footer']]));
    check('card words: a waived joining fee never reads "paid", a pending one reads "due", and the defaults are the reference words',
        str_contains(CardData::build(['pay_state' => 'waived'] + $raw, $base)['footer'][0], 'Joining fee UGX 50,000 waived') && str_contains(CardData::build(['pay_state' => 'pending'] + $raw, $base)['footer'][0], 'due')
        && CardData::build($raw, $base)['footer'][0] === 'Joining fee UGX 50,000 paid · Issued 20 Oct 2026' && CardData::build(['period' => 'monthly'] + $raw, $base)['tiles'][2]['value'] === 'UGX 50,000 / month');
    $keysInTabs = [];
    foreach (\App\Controllers\Admin\MembershipCardController::TAB_KEYS as $keys) {
        $keysInTabs = array_merge($keysInTabs, $keys);
    }
    check('card words: every designer setting, including the 21 new wording settings, is on exactly one designer tab and seeded', count($keysInTabs) === count(CardSettings::DEFS) && array_diff(array_keys(CardSettings::DEFS), $keysInTabs) === [] && count($keysInTabs) === count(array_unique($keysInTabs))
        && (int) Database::value("SELECT COUNT(*) FROM settings WHERE setting_key LIKE 'membership_card_%' AND setting_key IN ('membership_card_pay_free','membership_card_fee_one_off','membership_card_joining_waived','membership_card_adverb_quarterly')") === 4);

    // ---------------------------------------------------------------------------------------------- 9. number format, fee edit, payment proof
    check('number format: {TYPE}, {YY} or {YYYY} straight before {SEQ} is refused (the series of one type could swallow another\'s); a separator is fine',
        CardSettings::numberFormatError('{TYPE}{SEQ}') !== null && CardSettings::numberFormatError('{PREFIX}-{YY}{SEQ}') !== null && CardSettings::numberFormatError('{PREFIX}-{TYPE}-{YY}-{SEQ}') === null && CardSettings::numberFormatError('{PREFIX}{SEQ:5}') === null);
    $m7 = $mfNew('fee');
    $id7 = (int) $m7['id'];
    MS::changeStatus($id7, 'approved');
    $mfPaid($id7);
    $before = (array) Member::find($id7);
    $up = MembershipApplication::update($id7, $mfBase('fee') + ['fee_amount' => '99000', 'fee_period' => 'monthly'], null, true);
    $after = (array) Member::find($id7);
    check('fee edit after the card was issued: the card is flagged "needs a new version" (reason details)', $before['card_status'] === 'issued' && (int) $before['card_needs_reissue'] === 0 && $up['ok'] && (int) $after['card_needs_reissue'] === 1 && $after['card_reissue_reason'] === 'details', json_encode($up));
    $proofRel = MemberFiles::PROOF_DIR . '/' . bin2hex(random_bytes(16)) . '.pdf';
    @mkdir(STORAGE_PATH . '/private/' . MemberFiles::PROOF_DIR, 0775, true);
    file_put_contents(STORAGE_PATH . '/private/' . $proofRel, "%PDF-1.4\n%%EOF\n");
    $pay = MS::recordPayment($id7, ['amount' => '1000', 'status' => 'pending', 'purpose' => 'other', 'method' => 'mobile_money']);
    Database::update('member_payments', ['proof_path' => $proofRel, 'proof_name' => 'slip.pdf', 'proof_mime' => 'application/pdf'], 'id = ?', [(int) $pay['payment']['id']]);
    $del = MS::deletePayment((int) $pay['payment']['id']);
    check('deleting a payment row also deletes the proof file attached to it', $del['ok'] && !is_file(STORAGE_PATH . '/private/' . $proofRel), json_encode($del));
    @unlink(STORAGE_PATH . '/private/' . $proofRel);

    // ---------------------------------------------------------------------------------------------- 10. settings rows and the seed
    Settings::reset('membership_card_tagline');
    Settings::set('membership_card_tagline', 'Owner tagline B');
    $pristine = (int) Database::value("SELECT created_at = updated_at FROM settings WHERE setting_key = 'membership_card_tagline'");
    check('settings: a row created by the application after a reset does NOT look pristine, so the next import of lumina-live-import.sql keeps the owner\'s edit', $pristine === 0);

    // ---------------------------------------------------------------------------------------------- 11. contrast honesty and the e-mail colours
    $mail = \App\Services\Membership\MembershipMail::renderCard(['full_name' => 'A B', 'email' => 'a@mcfix.invalid', 'membership_number' => 'LAF-IND-26-0001', 'type_name' => 'T', 'expiry_date' => '2027-10-20', 'start_date' => '2026-10-20', 'id' => 0], [], true);
    $html = $mail[2];
    preg_match_all('/background:(#[0-9A-F]{6})"><a href="[^"]+" style="display:inline-block[^>]*color:#ffffff/i', $html, $btn);
    check('card e-mail: the white button text and the links reach 4.5:1 even with the reference orange (3.5:1) as the card accent', ($btn[1] ?? []) !== [] && CardSettings::contrast('#FFFFFF', $btn[1][0]) >= 4.5 && !str_contains($html, 'color:#E0642C') && !str_contains($html, 'background:#E0642C"><a'), json_encode($btn[1] ?? []));
} finally {
    $mfClean();
}

function PageRegistryHas(string $key): bool
{
    return \App\Core\PageRegistry::field('membership', $key) !== null;
}

if (isset($argv[0]) && realpath($argv[0]) === __FILE__) {
    echo $fail === 0 ? "\nALL CHECKS PASSED\n" : "\n{$fail} CHECK(S) FAILED\n";
    exit($fail === 0 ? 0 : 1);
}
