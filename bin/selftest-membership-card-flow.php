<?php
/**
 * MEMBERSHIP CARD checks, part 2 (required by bin/selftest-membership-card.php: check(), mc_png(), mc_pdf_problems() and the bootstrap are loaded).
 * Member numbers (per type and year, format changes, legacy continuity, parallel requests), the issue trigger matrix (approved / paid / waived / partial / free /
 * renewal / manual / photo required), idempotence (also with parallel processes), tokens, revoke and re-issue, the verification data, files (photo and proof
 * validation, polyglots, PDFs, rate limit, retention), erasure, e-mail, abilities, registry coverage, a scratch-database migration, and the HTTP flows of the
 * member page, the verification page and the admin screens (real requests with curl; skipped when the site does not answer). Removes everything it creates.
 */
declare(strict_types=1);

use App\Core\Auth;
use App\Core\Config;
use App\Core\Database;
use App\Core\PageRegistry;
use App\Core\RateLimit;
use App\Core\Roles;
use App\Core\Settings;
use App\Models\Member;
use App\Models\MemberPayment;
use App\Models\MembershipType;
use App\Models\User;
use App\Services\Membership\Card\CardData;
use App\Services\Membership\Card\CardSettings;
use App\Services\Membership\CardService;
use App\Services\Membership\MemberFiles;
use App\Services\Membership\MembershipApplication;
use App\Services\Membership\MembershipMail;
use App\Services\Membership\MembershipService as MS;

echo "Membership card: numbers, triggers, tokens, files, privacy, e-mail\n";

$mcStart = time() - 2;
$mcDomain = '@mcselftest.invalid';
$mcTmp = [];
$mcWatched = array_merge(['membership_open'], array_keys(CardSettings::DEFS));
$mcSaved = [];
foreach ($mcWatched as $k) {
    $mcSaved[$k] = [Settings::isCustomised($k), (string) Settings::get($k, '')];
}
$mcErased = [];
$mcTmpFile = static function (string $bytes, string $name = 'upload.bin') use (&$mcTmp): array {
    $p = tempnam(sys_get_temp_dir(), 'mcs') ?: '';
    file_put_contents($p, $bytes);
    $mcTmp[] = $p;
    return ['name' => $name, 'type' => 'application/octet-stream', 'tmp_name' => $p, 'error' => UPLOAD_ERR_OK, 'size' => strlen($bytes)];
};
$mcCleanup = static function () use (&$mcSaved, &$mcTmp, &$mcErased, $mcDomain, $mcStart): void {
    $extra = $mcErased === [] ? '0' : implode(',', array_map('intval', $mcErased));
    $rows = Database::all("SELECT * FROM members WHERE email LIKE ? OR id IN ($extra)", ['%' . $mcDomain]);
    foreach ($rows as $m) {
        MemberFiles::deleteAll($m);
        if (!empty($m['card_photo_path'])) {
            \App\Core\Uploader::deletePrivate((string) $m['card_photo_path']);
        }
    }
    Database::execute("DELETE FROM member_payments WHERE member_id IN (SELECT id FROM members WHERE email LIKE ? OR id IN ($extra))", ['%' . $mcDomain]);
    Database::execute("DELETE FROM members WHERE email LIKE ? OR id IN ($extra)", ['%' . $mcDomain]);
    Database::execute("DELETE FROM membership_types WHERE slug LIKE 'zz-mc-%'");
    Database::execute("DELETE FROM subscribers WHERE email LIKE ?", ['%' . $mcDomain]);
    Database::execute("DELETE FROM users WHERE email LIKE 'zz-mc-%@example.test'");
    Database::execute("DELETE FROM roles WHERE slug LIKE 'zz-mc-%'");
    Database::execute("DELETE FROM audit_log WHERE created_at >= ? AND (action LIKE 'member.%' OR action LIKE 'membership_%') AND (user_name = 'system' OR user_name LIKE 'ZZ MC%')", [date('Y-m-d H:i:s', $mcStart)]);
    foreach (glob(STORAGE_PATH . '/mail/*.eml') ?: [] as $f) {
        if (filemtime($f) >= $mcStart && (str_contains((string) file_get_contents($f), $mcDomain) || str_contains((string) file_get_contents($f), 'zz-mc-'))) {
            @unlink($f);
        }
    }
    foreach ($mcSaved as $k => [$custom, $val]) {
        $custom ? Settings::set($k, $val) : Settings::reset($k);
    }
    foreach ($mcTmp as $p) {
        @unlink($p);
    }
    foreach (['127.0.0.1', '::1'] as $ip) {
        foreach (['member-me-bad:', 'member-verify:', 'member-verify-bad:', 'member-upload-ip:'] as $pre) {
            RateLimit::clear($pre . $ip);
        }
    }
    MS::flush();
    Settings::flush();
    Roles::flush();
};
$mcCleanup();                                                     // leftovers of an aborted earlier run
Settings::set('membership_open', '1');
foreach (['membership_card_trigger', 'membership_card_require_photo', 'membership_card_reissue_renewal', 'membership_card_number_format', 'membership_photo_required'] as $k) {
    Settings::reset($k);
}

$mcBase = static fn(int $type, string $tag, array $over = []): array => $over + [
    'type_id' => $type, 'full_name' => 'MC Person ' . $tag, 'gender' => 'female', 'date_of_birth' => '1990-05-20', 'nationality' => 'Ugandan',
    'email' => $tag . $mcDomain, 'phone' => '+256700000001', 'country' => 'Uganda', 'region' => 'Gulu', 'city' => 'Laroo',
    'occupation_status' => 'employed', 'education_level' => 'diploma', 'availability' => 'weekends', 'heard_about' => 'website',
    'motivation' => 'I would like to volunteer and support young people.', 'emergency_name' => 'Em Contact', 'emergency_phone' => '+256700000002',
    'emergency_relationship' => 'Sister', 'payment_method' => 'mobile_money', 'consent_terms' => '1', 'consent_privacy' => '1', 'declaration' => '1',
];
$mcMeta = ['ip' => '203.0.113.9', 'user_agent' => 'selftest'];
$mcNew = static function (int $type, string $tag, array $over = []) use ($mcBase, $mcMeta): array {
    $r = MembershipApplication::process($mcBase($type, $tag, $over), $mcMeta);
    if (!$r['ok'] || empty($r['member'])) {
        throw new \RuntimeException('could not create member ' . $tag . ': ' . json_encode($r['errors'] ?? $r));
    }
    return (array) $r['member'];
};
$mcRow = static fn(int $id): array => (array) Member::find($id);
$mcMails = static function (string $email) use ($mcStart): array {
    $out = [];
    foreach (glob(STORAGE_PATH . '/mail/*.eml') ?: [] as $f) {
        if (filemtime($f) < $mcStart) {
            continue;
        }
        $raw = (string) file_get_contents($f);
        if (!preg_match('/^To: ' . preg_quote($email, '/') . '\r?$/mi', $raw)) {
            continue;
        }
        $subject = preg_match('/^Subject: (.*)\r?$/mi', $raw, $m) ? trim($m[1]) : '';
        $pdf = null;
        if (preg_match_all('/Content-Type: application\/pdf; name="([^"]+)"\r?\nContent-Transfer-Encoding: base64\r?\n(?:[^\r\n]+\r?\n)?\r?\n(.*?)(?=\r?\n--|\z)/s', $raw, $mm, PREG_SET_ORDER)) {
            $pdf = ['name' => $mm[0][1], 'bytes' => base64_decode(preg_replace('/\s+/', '', $mm[0][2]))];
        }
        $out[] = ['file' => $f, 'subject' => $subject, 'raw' => $raw, 'pdf' => $pdf, 'time' => filemtime($f)];
    }
    return $out;
};
$mcPay = static fn(int $id, string $amount, string $status = 'received', array $over = []): array => MS::recordPayment($id, $over + ['amount' => $amount, 'status' => $status, 'purpose' => 'membership', 'method' => 'mobile_money']);
$mcCards = static fn(string $email): array => array_values(array_filter($mcMails($email), static fn(array $m): bool => $m['pdf'] !== null && str_contains($m['pdf']['name'], 'Membership-card')));

try {
    // ---------------------------------------------------------------------------------------------- schema, abilities, registry
    $cols = array_column(Database::all('SHOW COLUMNS FROM members'), 'Field');
    $need = ['photo_path', 'photo_updated_at', 'photo_consent_at', 'photo_original', 'proof_path', 'proof_name', 'proof_mime', 'proof_uploaded_at', 'proof_status', 'proof_verified_by', 'proof_verified_at',
        'dashboard_token', 'card_status', 'card_version', 'card_issued_at', 'card_emailed_at', 'card_emailed_version', 'card_valid_until', 'card_verify_token', 'card_needs_reissue', 'card_reissue_reason', 'card_revoked_at',
        'card_snapshot', 'card_photo_path'];
    check('schema: members has every membership-card column; member_payments has the proof columns; membership_types has code', array_diff($need, $cols) === []
        && array_diff(['proof_path', 'proof_name', 'proof_mime'], array_column(Database::all('SHOW COLUMNS FROM member_payments'), 'Field')) === []
        && in_array('code', array_column(Database::all('SHOW COLUMNS FROM membership_types'), 'Field'), true), implode(',', array_diff($need, $cols)));
    $idx = array_column(Database::all('SHOW INDEX FROM members'), 'Key_name');
    check('schema: the two tokens are UNIQUE (private page, verification link)', in_array('uq_members_dashboard_token', $idx, true) && in_array('uq_members_card_token', $idx, true));
    Roles::flush();
    $catalog = require dirname(__DIR__) . '/app/config/ability_catalog.php';
    check('abilities: members.card exists, is admin-only by default, can be given to a custom role, is in the role catalogue; editors keep members.manage',
        Roles::can('admin', 'members.card') && !Roles::can('editor', 'members.card') && Roles::can('editor', 'members.manage') && in_array('members.card', Roles::assignable(), true)
        && isset($catalog['membership']['items']['members.card']) && Config::get('abilities')['members.card'] === ['admin']);
    $codeBad = [];
    foreach (['<?php', 'x', 'ABCDEFGHIJ', 'ab c', "A\nB"] as $c) {
        $r = MS::saveType(null, ['name' => 'ZZ code test', 'slug' => 'zz-mc-code-test', 'period' => 'annual', 'fee_amount' => '1', 'code' => $c]);
        if ($r['ok']) {
            $codeBad[] = $c;
            MembershipType::delete((int) $r['id']);
        }
    }
    $rc = MS::saveType(null, ['name' => 'ZZ MC coded', 'slug' => 'zz-mc-coded', 'period' => 'annual', 'fee_amount' => '1', 'code' => 'abc1']);
    check('types: the code of a type is 2 to 8 letters or digits, stored in capitals; junk is refused', $codeBad === [] && $rc['ok'] && $rc['type']['code'] === 'ABC1', json_encode($codeBad));
    check('types: without a saved code the initials of the name are used (Women\'s Group -> WG, Individual Member -> IND, The Friends of Lumina -> FL, Corporate Sponsors Club -> CSC, "A" -> MEM)',
        MS::typeCode(['name' => "Women's Group"]) === 'WG' && MS::typeCode(['name' => 'Individual Member']) === 'IND' && MS::typeCode(['name' => 'The Friends of Lumina']) === 'FL'
        && MS::typeCode(['name' => 'Corporate Sponsors Club']) === 'CSC' && MS::typeCode(['name' => 'A']) === 'MEM' && MS::typeCode(['name' => 'X', 'code' => 'yth']) === 'YTH' && MS::typeCode([]) === 'MEM');
    $seeded = Database::all("SELECT slug, code FROM membership_types WHERE slug IN ('individual-member','youth-student-member','organisation-partner-member') ORDER BY slug");
    check('types: the three starter types carry the codes IND / YTH / ORG', count($seeded) === 0 || array_column($seeded, 'code', 'slug') === ['individual-member' => 'IND', 'organisation-partner-member' => 'ORG', 'youth-student-member' => 'YTH'], json_encode($seeded));
    $missingKeys = [];
    foreach (['uploads', 'dashboard', 'verify'] as $sec) {
        $found = false;
        foreach (PageRegistry::get('membership')['sections'] as $section) {
            $found = $found || $section['key'] === $sec;
        }
        if (!$found) {
            $missingKeys[] = $sec;
        }
    }
    $used = [];
    $scan = array_merge(glob(dirname(__DIR__) . '/app/views/web/membership/me/*.php') ?: [], glob(dirname(__DIR__) . '/app/views/web/membership/verify/*.php') ?: [],
        [dirname(__DIR__) . '/app/controllers/Web/MembershipDashboardController.php', dirname(__DIR__) . '/app/controllers/Web/MembershipVerifyController.php']);
    foreach ($scan as $file) {
        $src = (string) file_get_contents($file);
        if (preg_match_all('/(?:content\(\s*\'membership\',\s*|\$c\(\s*)\'((?:dashboard|uploads|verify|emails|confirmation)\.[a-z_]+)\'/', $src, $mm)) {
            foreach ($mm[1] as $k) {
                $used[$k] = true;
            }
        }
        if (preg_match_all('/(?:content\(\s*\'membership\',\s*|\$c\(\s*)\'((?:dashboard|uploads|verify)\.[a-z_]+)\'/', $src, $mm)) {
            foreach ($mm[1] as $k) {
                $used[$k] = true;
            }
        }
        // MembershipService::copy('uploads.remove_label', 'Remove') reads the registry too, with an English fallback: every such key must exist or the owner cannot edit the word
        if (preg_match_all('/MembershipService::copy\(\s*\'((?:dashboard|uploads|verify|emails)\.[a-z_]+)\'/', $src, $mm)) {
            foreach ($mm[1] as $k) {
                $used[$k] = true;
            }
        }
    }
    // the registration form reads the uploads section through $u('key', 'fallback')
    foreach (['/app/views/web/membership/register.php', '/app/views/web/membership/_upload.php'] as $rel) {
        if (preg_match_all('/\$u\(\s*\'([a-z_]+)\'/', (string) file_get_contents(dirname(__DIR__) . $rel), $mm)) {
            foreach ($mm[1] as $k) {
                $used['uploads.' . $k] = true;
            }
        }
    }
    foreach (['pending', 'approved', 'rejected', 'suspended', 'expired', 'cancelled'] as $st) {
        $used['dashboard.status_value_' . $st] = true;
    }
    $used['dashboard.state_revoked'] = true;
    foreach (array_keys(\App\Services\Membership\MemberFiles::class === '' ? [] : (new \ReflectionClass(MemberFiles::class))->getReflectionConstant('DEFAULT_MESSAGES')->getValue()) as $k) {
        $used['uploads.err_' . $k] = true;
    }
    foreach (array_merge(['dashboard.state_pending', 'dashboard.state_approved_unpaid', 'dashboard.state_approved_photo', 'dashboard.state_approved_ready', 'dashboard.state_issued', 'dashboard.state_rejected', 'dashboard.state_cancelled',
        'dashboard.state_suspended', 'dashboard.state_expired', 'verify.label_valid', 'verify.label_expired', 'verify.label_suspended', 'verify.label_invalid', 'verify.text_valid', 'verify.text_expired', 'verify.text_suspended', 'verify.text_invalid',
        'uploads.photo_saved', 'uploads.proof_saved', 'emails.proof_subject', 'emails.proof_body', 'emails.card_button', 'emails.card_photo_note', 'dashboard.step_done', 'dashboard.step_current', 'dashboard.step_todo', 'dashboard.step_received', 'dashboard.step_payment', 'dashboard.step_approved', 'dashboard.step_card',
        'dashboard.proof_received', 'dashboard.proof_verified', 'dashboard.proof_rejected', 'dashboard.card_waiting_approval', 'dashboard.card_waiting_payment', 'dashboard.card_waiting_photo', 'dashboard.renewal_soon', 'dashboard.renewal_expired'], []) as $k) {
        $used[$k] = true;
    }
    $absent = [];
    foreach (array_keys($used) as $k) {
        if (!str_ends_with($k, '_') && PageRegistry::field('membership', $k) === null) {          // keys that end in "_" are prefixes of dynamic keys: listed one by one above and below
            $absent[] = $k;
        }
    }
    check('registry: the dashboard, verification and upload copy exists, and every key the views, the controllers and MemberFiles use is a registry field (' . count($used) . ' keys)', $missingKeys === [] && $absent === [], implode(',', array_merge($missingKeys, $absent)));

    // ---------------------------------------------------------------------------------------------- types for the tests
    $tA = MS::saveType(null, ['name' => 'ZZ MC Annual', 'slug' => 'zz-mc-annual', 'code' => 'ZA', 'fee_amount' => '50000', 'joining_fee' => '10000', 'currency' => 'UGX', 'period' => 'annual', 'is_active' => '1', 'sort_order' => 9101]);
    $tB = MS::saveType(null, ['name' => 'ZZ MC Second', 'slug' => 'zz-mc-second', 'code' => 'ZB', 'fee_amount' => '20000', 'currency' => 'UGX', 'period' => 'annual', 'is_active' => '1', 'sort_order' => 9102]);
    $tF = MS::saveType(null, ['name' => 'ZZ MC Free Lifetime', 'slug' => 'zz-mc-free', 'code' => 'ZF', 'fee_amount' => '0', 'period' => 'lifetime', 'is_active' => '1', 'sort_order' => 9103]);
    [$typeA, $typeB, $typeF] = [(int) $tA['id'], (int) $tB['id'], (int) $tF['id']];
    $yy = date('y');

    // ---------------------------------------------------------------------------------------------- member numbers
    Settings::set('membership_card_trigger', 'manual');          // numbering first: no cards while we approve
    $n1 = $mcNew($typeA, 'n1');
    $n2 = $mcNew($typeA, 'n2');
    $n3 = $mcNew($typeB, 'n3');
    $a1 = MS::changeStatus((int) $n1['id'], 'approved')['member'];
    $a2 = MS::changeStatus((int) $n2['id'], 'approved')['member'];
    $a3 = MS::changeStatus((int) $n3['id'], 'approved')['member'];
    $baseA = (int) Database::value("SELECT COALESCE(MAX(CAST(SUBSTRING(membership_number, ?) AS UNSIGNED)), 0) FROM members WHERE membership_number REGEXP ? AND id NOT IN (?, ?)", [strlen("LAF-ZA-{$yy}-") + 1, "^LAF-ZA-{$yy}-[0-9]+$", $n1['id'], $n2['id']], 0);
    check('numbers: the default format is LAF-<type code>-<YY>-<4 digits>, counted up PER TYPE (ZA 0001, 0002; ZB 0001)', $a1['membership_number'] === "LAF-ZA-{$yy}-0001" && $a2['membership_number'] === "LAF-ZA-{$yy}-0002"
        && $a3['membership_number'] === "LAF-ZB-{$yy}-0001" && $baseA === 0, json_encode([$a1['membership_number'], $a2['membership_number'], $a3['membership_number']]));
    check('numbers: assigning again returns the same number (an issued number never changes)', MS::assignMembershipNumber((int) $n1['id']) === $a1['membership_number']);
    $y1 = $mcNew($typeA, 'y1');
    $y2 = $mcNew($typeA, 'y2');
    $y3 = $mcNew($typeA, 'y3');
    $ny = [MS::assignMembershipNumber((int) $y1['id'], 2041), MS::assignMembershipNumber((int) $y2['id'], 2041), MS::assignMembershipNumber((int) $y3['id'], 2042)];
    check('numbers: the series restarts every year (…-41-0001, …-41-0002, …-42-0001)', $ny === ['LAF-ZA-41-0001', 'LAF-ZA-41-0002', 'LAF-ZA-42-0001'], json_encode($ny));
    Settings::set('membership_card_number_format', '{PREFIX}-{YYYY}-{SEQ:5}');
    $f1 = $mcNew($typeA, 'f1');
    $fn1 = MS::assignMembershipNumber((int) $f1['id'], 2043);
    $fn2 = MS::assignMembershipNumber((int) $mcNew($typeB, 'f2')['id'], 2043);
    check('numbers: changing the format affects only FUTURE numbers (older ones untouched) and types share one series when the format has no {TYPE}', $fn1 === 'LAF-2043-00001' && $fn2 === 'LAF-2043-00002'
        && $mcRow((int) $n1['id'])['membership_number'] === $a1['membership_number'], "$fn1 $fn2");
    Settings::set('membership_card_number_format', '{PREFIX}-{YYYY}-{SEQ}');
    $legacy = $mcNew($typeA, 'legacy');
    Database::update('members', ['membership_number' => 'LAF-2044-0007'], 'id = ?', [(int) $legacy['id']]);
    $next = MS::assignMembershipNumber((int) $mcNew($typeA, 'legacy2')['id'], 2044);
    check('numbers: a legacy number LAF-YYYY-NNNN is continued by the equivalent format (…-0007 then …-0008) and a longer number of another series never confuses the counter', $next === 'LAF-2044-0008', $next);
    Settings::set('membership_card_number_format', 'bad format');
    $fallback = MS::assignMembershipNumber((int) $mcNew($typeB, 'fallback')['id'], 2045);
    check('numbers: an invalid stored format falls back to the default instead of failing', $fallback === 'LAF-ZB-45-0001', $fallback);
    Settings::reset('membership_card_number_format');
    // parallel requests for the same series
    $par = [];
    for ($i = 1; $i <= 6; $i++) {
        $par[] = (int) $mcNew($typeF, 'par' . $i)['id'];
    }
    $startAt = microtime(true) + 3.0;
    $procs = [];
    foreach ($par as $pid) {
        $proc = proc_open([PHP_BINARY, __DIR__ . '/selftest-membership-card-child.php', 'number', (string) $pid, sprintf('%.4f', $startAt)], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, dirname(__DIR__));
        $procs[] = [$proc, $pipes];
    }
    $res = [];
    foreach ($procs as [$proc, $pipes]) {
        $out = trim((string) stream_get_contents($pipes[1]));
        stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($proc);
        $res[] = json_decode($out, true) ?: ['ok' => false, 'error' => 'no output ' . $out];
    }
    $nums = array_column($res, 'number');
    sort($nums);
    check('numbers: 6 requests at the same instant get 6 different numbers, in order (…-31-0001 to …-31-0006), no failure', count(array_filter(array_column($res, 'ok'))) === 6
        && $nums === array_map(static fn(int $i): string => 'LAF-ZF-31-000' . $i, range(1, 6)), json_encode($res));
    check('numbers: the preview for the designer shows the next number of a type', MS::numberPreview(['name' => 'X', 'code' => 'ZA'], 2026) === 'LAF-ZA-26-0001');
    Settings::reset('membership_card_trigger');

    // ---------------------------------------------------------------------------------------------- tokens
    $tk = $mcNew($typeA, 'tok1');
    $tk2 = $mcNew($typeA, 'tok2');
    check('tokens: a new application gets a 64-hex-character (256 bit) private-page token at once; two applications never share one',
        preg_match('/^[a-f0-9]{64}$/D', (string) $tk['dashboard_token']) === 1 && $tk['dashboard_token'] !== $tk2['dashboard_token'] && MS::memberByDashboardToken((string) $tk['dashboard_token'])['id'] === $tk['id']);
    Database::update('members', ['dashboard_token' => null], 'id = ?', [(int) $tk2['id']]);
    $lazy = MS::dashboardToken((int) $tk2['id']);
    check('tokens: an existing member without one gets a token lazily, and the same one afterwards', preg_match('/^[a-f0-9]{64}$/D', $lazy) === 1 && MS::dashboardToken((int) $tk2['id']) === $lazy
        && MS::dashboardToken($mcRow((int) $tk2['id'])) === $lazy);
    $badTokens = [$lazy . "\n", strtoupper($lazy), substr($lazy, 0, 63), $lazy . 'a', '', str_repeat('g', 64), 'sample'];
    check('tokens: malformed tokens (trailing newline, capitals, wrong length, non-hex) never find a member', array_filter($badTokens, static fn(string $t): bool => MS::memberByDashboardToken($t) !== null) === []);
    $regen = MS::regenerateDashboardToken((int) $tk2['id']);
    check('tokens: regenerating replaces the token and the old link stops working', $regen !== $lazy && MS::memberByDashboardToken($lazy) === null && MS::memberByDashboardToken($regen) !== null);
    check('tokens: the link helpers point at /membership/me/<token>', str_ends_with(MS::dashboardUrl((int) $tk['id']), '/membership/me/' . $tk['dashboard_token']) && str_contains(MS::dashboardPath((int) $tk['id']), 'membership/me/'));

    // ---------------------------------------------------------------------------------------------- the issue trigger matrix
    echo "Membership card: when the card is issued\n";
    // 1. paid_approved (default): approved, unpaid -> nothing; partial -> nothing; cleared -> issued and e-mailed once
    $m1 = $mcNew($typeA, 'trg1');
    $id1 = (int) $m1['id'];
    MS::changeStatus($id1, 'approved');
    $r = $mcRow($id1);
    check('trigger paid_approved: approved but nothing paid -> no card (balance 60,000 = 50,000 + 10,000 joining fee)', $r['card_status'] === 'none' && $mcCards('trg1' . $mcDomain) === [] && CardService::summary($r)['waiting'] === ['unpaid']);
    $mcPay($id1, '20000');
    check('trigger paid_approved: a PARTIAL payment does not issue the card', $mcRow($id1)['card_status'] === 'none');
    $mcPay($id1, '40000');
    $r = $mcRow($id1);
    $mail = $mcCards('trg1' . $mcDomain);
    check('trigger paid_approved: the payment that clears the balance issues card version 1 and e-mails it once, with the PDF attached', $r['card_status'] === 'issued' && (int) $r['card_version'] === 1 && count($mail) === 1
        && $mail[0]['pdf'] !== null && str_starts_with((string) $mail[0]['pdf']['bytes'], '%PDF-1.4') && mc_pdf_problems((string) $mail[0]['pdf']['bytes']) === [] && (int) $r['card_emailed_version'] === 1, json_encode([$r['card_status'], count($mail)]));
    check('card: the verification token is 32 hex characters (128 bits), different from the private-page token; the snapshot and the valid-until date are stored', preg_match('/^[a-f0-9]{32}$/D', (string) $r['card_verify_token']) === 1
        && $r['card_verify_token'] !== $r['dashboard_token'] && str_starts_with((string) $r['card_valid_until'], (string) $r['expiry_date']) && json_decode((string) $r['card_snapshot'], true)['number'] === $r['membership_number']);
    $mcPay($id1, '5000');
    CardService::event($id1, 'payment');
    CardService::event($id1, 'approved');
    check('idempotence: more payments and repeated events do not issue or e-mail a second time', (int) $mcRow($id1)['card_version'] === 1 && count($mcCards('trg1' . $mcDomain)) === 1);
    $again = CardService::issue($id1, ['source' => 'manual']);
    check('idempotence: issue() on an issued card says "already" and sends nothing', $again['ok'] && $again['already'] && !$again['issued'] && count($mcCards('trg1' . $mcDomain)) === 1);
    // 2. pay first, approve afterwards
    $m2 = $mcNew($typeA, 'trg2');
    $id2 = (int) $m2['id'];
    $mcPay($id2, '60000');
    check('trigger: paying BEFORE approval issues nothing (the member is not approved yet)', $mcRow($id2)['card_status'] === 'none');
    MS::changeStatus($id2, 'approved');
    check('trigger: ...the approval that follows a cleared balance issues the card (whichever happens last)', $mcRow($id2)['card_status'] === 'issued' && count($mcCards('trg2' . $mcDomain)) === 1);
    // 3. waived
    $m3 = $mcNew($typeA, 'trg3');
    MS::changeStatus((int) $m3['id'], 'approved');
    $mcPay((int) $m3['id'], '60000', 'waived');
    check('trigger: a waived fee clears the balance like a payment', $mcRow((int) $m3['id'])['card_status'] === 'issued');
    // 4. free type
    $m4 = $mcNew($typeF, 'trg4');
    MS::changeStatus((int) $m4['id'], 'approved');
    $r4 = $mcRow((int) $m4['id']);
    check('trigger: a free lifetime membership is issued at approval and the card says "No expiry"', $r4['card_status'] === 'issued' && $r4['expiry_date'] === null && $r4['card_valid_until'] === null
        && CardData::rawFromSnapshot($r4)['valid_until'] === null && in_array('No expiry', \mc_pdf_strings((string) CardService::pdf($r4)), true));
    // 5. approved trigger
    Settings::set('membership_card_trigger', 'approved');
    $m5 = $mcNew($typeA, 'trg5');
    MS::changeStatus((int) $m5['id'], 'approved');
    check('trigger "approved": the card is issued as soon as the member is approved, even unpaid', $mcRow((int) $m5['id'])['card_status'] === 'issued' && count($mcCards('trg5' . $mcDomain)) === 1);
    // 6. manual
    Settings::set('membership_card_trigger', 'manual');
    $m6 = $mcNew($typeA, 'trg6');
    $id6 = (int) $m6['id'];
    MS::changeStatus($id6, 'approved');
    $mcPay($id6, '60000');
    check('trigger "manual only": nothing is issued automatically, not even when approved and paid', $mcRow($id6)['card_status'] === 'none' && $mcCards('trg6' . $mcDomain) === []);
    $manual = CardService::issue($id6, ['user_id' => null, 'source' => 'manual', 'force' => true]);
    check('trigger "manual only": staff can issue the card (and it is e-mailed)', $manual['ok'] && $manual['issued'] && $manual['emailed'] === true && count($mcCards('trg6' . $mcDomain)) === 1);
    // staff override of the payment rule, approval still required
    Settings::reset('membership_card_trigger');
    $m7 = $mcNew($typeA, 'trg7');
    $notApproved = CardService::issue((int) $m7['id'], ['force' => true, 'source' => 'manual']);
    MS::changeStatus((int) $m7['id'], 'approved');
    $unpaidNoForce = CardService::issue((int) $m7['id'], ['source' => 'auto']);
    $unpaidForce = CardService::issue((int) $m7['id'], ['force' => true, 'source' => 'manual', 'send' => false]);
    check('staff: "Issue card now" needs an approved member; the payment rule can be skipped by staff but not by the automatic path; send=false issues without e-mail',
        !$notApproved['ok'] && $notApproved['error'] === 'not_approved' && !$unpaidNoForce['ok'] && $unpaidNoForce['error'] === 'unpaid' && $unpaidForce['ok'] && $unpaidForce['issued'] && $unpaidForce['emailed'] === null
        && $mcCards('trg7' . $mcDomain) === []);
    // 7. photo required
    Settings::set('membership_card_require_photo', '1');
    $m8 = $mcNew($typeA, 'trg8');
    $id8 = (int) $m8['id'];
    MS::changeStatus($id8, 'approved');
    $mcPay($id8, '60000');
    $noPhoto = CardService::issue($id8, ['force' => true, 'source' => 'manual']);
    check('photo required: approved and paid, but no photo -> not issued (staff and applicant are told why: photo_missing)', $mcRow($id8)['card_status'] === 'none' && !$noPhoto['ok'] && $noPhoto['error'] === 'photo_missing'
        && CardService::summary($mcRow($id8))['waiting'] === ['photo_missing'] && CardService::dashboardState($mcRow($id8)) === 'approved_photo');
    $png = mc_png(320, 320, 2);
    $noConsent = MemberFiles::storePhoto($id8, $mcTmpFile($png, 'me.png'), ['consent' => '', 'source' => 'dashboard', 'ip' => '203.0.113.9']);
    check('photo: without the consent tick the upload is refused (code consent) and nothing is stored', !$noConsent['ok'] && $noConsent['code'] === 'consent' && $mcRow($id8)['photo_path'] === null);
    $withPhoto = MemberFiles::storePhoto($id8, $mcTmpFile($png, 'me.png'), ['consent' => '1', 'source' => 'dashboard', 'ip' => '203.0.113.9']);
    $r8 = $mcRow($id8);
    check('photo required: adding the photo (with consent) issues the card at once, e-mailed once', $withPhoto['ok'] && $r8['card_status'] === 'issued' && count($mcCards('trg8' . $mcDomain)) === 1 && !empty($r8['photo_consent_at']) && !empty($r8['card_photo_path']));
    check('photo: stored privately under storage/private/members/photos with a random 32-hex name (never in the web root); the card keeps its own copy', preg_match('~^members/photos/[a-f0-9]{32}\.(jpg|png)$~D', (string) $r8['photo_path']) === 1
        && is_file(STORAGE_PATH . '/private/' . $r8['photo_path']) && $r8['card_photo_path'] !== $r8['photo_path'] && is_file(STORAGE_PATH . '/private/' . $r8['card_photo_path']) && !is_file(BASE_PATH . '/' . $r8['photo_path']));
    Settings::reset('membership_card_require_photo');
    // replacing the photo after the card was issued FLAGS a re-issue and does not change the card
    $cardPhotoBefore = $r8['card_photo_path'];
    $snapBefore = $r8['card_snapshot'];
    $replace = MemberFiles::storePhoto($id8, $mcTmpFile(mc_png(340, 340, 2), 'me2.png'), ['consent' => '1', 'source' => 'dashboard', 'ip' => '203.0.113.9']);
    $r8b = $mcRow($id8);
    check('photo replaced after issue: the card is NOT changed (same snapshot and card photo, same version) but a re-issue is flagged for staff; the old file is deleted', $replace['ok'] && (int) $r8b['card_needs_reissue'] === 1
        && $r8b['card_reissue_reason'] === 'photo' && $r8b['card_photo_path'] === $cardPhotoBefore && $r8b['card_snapshot'] === $snapBefore && (int) $r8b['card_version'] === 1 && $r8b['photo_path'] !== $r8['photo_path']
        && !is_file(STORAGE_PATH . '/private/' . $r8['photo_path']) && CardService::summary($r8b)['needs_reissue'] === true);
    $re8 = CardService::reissue($id8, ['force' => true, 'source' => 'manual', 'user_id' => null]);
    $r8c = $mcRow($id8);
    check('re-issue: version 2, a NEW verification token (the old card\'s link stops verifying), the flag is cleared, the new photo is on the card, e-mailed as a second message',
        $re8['ok'] && $re8['issued'] && (int) $r8c['card_version'] === 2 && $r8c['card_verify_token'] !== $r8['card_verify_token'] && (int) $r8c['card_needs_reissue'] === 0 && $r8c['card_photo_path'] !== $cardPhotoBefore
        && !is_file(STORAGE_PATH . '/private/' . $cardPhotoBefore) && count($mcCards('trg8' . $mcDomain)) === 2
        && CardService::verification((string) $r8['card_verify_token']) === ['state' => 'invalid'] && CardService::verification((string) $r8c['card_verify_token'])['state'] === 'valid');
    // 8. renewal
    $beforeRenew = (string) $mcRow($id1)['card_valid_until'];
    $ren = MS::renew($id1, ['user_id' => null]);
    $afterRenew = $mcRow($id1);
    check('renewal: a new term with money still to pay does NOT re-issue the card yet (version 1, old valid-until)', $ren['ok'] && (int) $afterRenew['card_version'] === 1 && substr((string) $afterRenew['card_valid_until'], 0, 10) === substr($beforeRenew, 0, 10)
        && $afterRenew['expiry_date'] > $beforeRenew && CardService::summary($afterRenew)['renewal_due'] === true && count($mcCards('trg1' . $mcDomain)) === 1);
    $mcPay($id1, '50000');
    $paidRenew = $mcRow($id1);
    $cards1 = $mcCards('trg1' . $mcDomain);
    check('renewal: when the renewal is paid the card is re-issued with the NEW valid-until date and e-mailed (version 2, token changed, old token dead)', (int) $paidRenew['card_version'] === 2 && count($cards1) === 2
        && substr((string) $paidRenew['card_valid_until'], 0, 10) === substr((string) $paidRenew['expiry_date'], 0, 10) && $paidRenew['card_verify_token'] !== $r['card_verify_token']
        && CardService::verification((string) $r['card_verify_token'])['state'] === 'invalid' && CardService::verification((string) $paidRenew['card_verify_token'])['state'] === 'valid'
        && in_array(CardData::date(MS::lastValidDay((string) $paidRenew['expiry_date'])), mc_pdf_strings((string) CardService::pdf($paidRenew)), true));
    Settings::set('membership_card_reissue_renewal', '0');
    $ren2 = MS::renew($id2, ['user_id' => null]);
    $mcPay($id2, '50000');
    check('renewal: with the setting off a paid renewal does not re-issue (the card still shows the old date; staff may re-issue)', $ren2['ok'] && (int) $mcRow($id2)['card_version'] === 1 && CardService::summary($mcRow($id2))['renewal_due'] === true);
    Settings::reset('membership_card_reissue_renewal');
    // 9. revoke
    $rv = CardService::revoke($id1, 'selftest', null);
    $rvRow = $mcRow($id1);
    check('revoke: the card is revoked at once, its QR link says NOT VALID with no member data, later payments / events do not bring it back, only an explicit re-issue does',
        $rv['ok'] && $rvRow['card_status'] === 'revoked' && CardService::verification((string) $rvRow['card_verify_token']) === ['state' => 'invalid'] && CardService::event($id1, 'payment') === null && $mcRow($id1)['card_status'] === 'revoked'
        && CardService::summary($rvRow)['state'] === 'revoked' && CardService::pdf($rvRow) === null && !CardService::revoke($id1, '', null)['ok']);
    $back = CardService::reissue($id1, ['force' => true, 'source' => 'manual']);
    check('revoke: issuing again after a revocation gives a new version and a new working link', $back['ok'] && (int) $mcRow($id1)['card_version'] === 3 && CardService::verification((string) $mcRow($id1)['card_verify_token'])['state'] === 'valid');
    // 10. parallel issue
    Settings::set('membership_card_trigger', 'manual');
    $m9 = $mcNew($typeA, 'trg9');
    $id9 = (int) $m9['id'];
    MS::changeStatus($id9, 'approved');
    $mcPay($id9, '60000');
    $startAt = microtime(true) + 3.0;
    $procs = [];
    for ($i = 0; $i < 5; $i++) {
        $proc = proc_open([PHP_BINARY, __DIR__ . '/selftest-membership-card-child.php', 'issue', (string) $id9, sprintf('%.4f', $startAt)], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, dirname(__DIR__));
        $procs[] = [$proc, $pipes];
    }
    $res = [];
    foreach ($procs as [$proc, $pipes]) {
        $out = trim((string) stream_get_contents($pipes[1]));
        stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($proc);
        $res[] = json_decode($out, true) ?: ['ok' => false, 'error' => 'no output ' . $out];
    }
    $r9 = $mcRow($id9);
    check('idempotence: 5 processes issuing the same card at the same instant -> one issue (version 1), one e-mail, no failure', count(array_filter(array_column($res, 'ok'))) === 5 && count(array_filter(array_column($res, 'issued'))) === 1
        && (int) $r9['card_version'] === 1 && count($mcCards('trg9' . $mcDomain)) === 1, json_encode($res));
    Settings::reset('membership_card_trigger');
    // 11. live status on the verification data
    $ver = CardService::verification((string) $mcRow($id2)['card_verify_token']);
    check('verification: a valid card returns exactly state, name, type, number, since, valid_until, no_expiry - nothing else', array_keys($ver) === ['state', 'name', 'type', 'number', 'since', 'valid_until', 'no_expiry'] && $ver['state'] === 'valid'
        && $ver['name'] === 'MC Person trg2' && $ver['number'] === $mcRow($id2)['membership_number'] && !str_contains(json_encode($ver), $mcDomain) && !str_contains(json_encode($ver), '+2567'));
    MS::changeStatus($id2, 'suspended', ['reason' => 'selftest']);
    $susp = CardService::verification((string) $mcRow($id2)['card_verify_token'])['state'];
    MS::changeStatus($id2, 'approved');
    Database::update('members', ['expiry_date' => date('Y-m-d', strtotime('-1 day'))], 'id = ?', [$id2]);
    $expd = CardService::verification((string) $mcRow($id2)['card_verify_token']);
    Database::update('members', ['expiry_date' => date('Y-m-d', strtotime('+200 days'))], 'id = ?', [$id2]);
    MS::changeStatus($id2, 'cancelled', ['reason' => 'selftest']);
    $canc = CardService::verification((string) $mcRow($id2)['card_verify_token']);
    check('verification: suspended -> SUSPENDED (with data), past expiry -> EXPIRED (with data), cancelled -> NOT VALID with no data (the page is the live truth, the PDF a snapshot)', $susp === 'suspended' && $expd['state'] === 'expired'
        && $expd['name'] === 'MC Person trg2' && $canc === ['state' => 'invalid']);
    check('verification: tokens that are not exactly 32 lower-case hex characters (trailing newline, capitals, short, long, empty, the private-page token) give NOT VALID and no data',
        (function () use ($mcRow, $id1): bool {
            $t = (string) $mcRow($id1)['card_verify_token'];
            foreach ([$t . "\n", strtoupper($t), substr($t, 0, 31), $t . '0', '', (string) $mcRow($id1)['dashboard_token'], CardData::SAMPLE_TOKEN, "' OR 1=1 --"] as $bad) {
                if (CardService::verification($bad) !== ['state' => 'invalid']) {
                    return false;
                }
            }
            return true;
        })());
    $allTok = Database::all("SELECT dashboard_token, card_verify_token FROM members WHERE email LIKE ?", ['%' . $mcDomain]);
    $dt = array_filter(array_column($allTok, 'dashboard_token'));
    $vt = array_filter(array_column($allTok, 'card_verify_token'));
    check('tokens: across ' . count($dt) . ' private-page tokens and ' . count($vt) . ' verification tokens none repeats, none is shared, all have the right format and look random (all 16 hex digits appear)',
        count(array_unique($dt)) === count($dt) && count(array_unique($vt)) === count($vt) && array_intersect($dt, $vt) === [] && count(array_filter($dt, static fn($t): bool => preg_match('/^[a-f0-9]{64}$/D', (string) $t) === 1)) === count($dt)
        && count(array_filter($vt, static fn($t): bool => preg_match('/^[a-f0-9]{32}$/D', (string) $t) === 1)) === count($vt) && count(array_unique(str_split(implode('', $dt) . implode('', $vt)))) === 16);

    // ---------------------------------------------------------------------------------------------- audit and privacy of the log
    $aud = Database::all("SELECT summary, details FROM audit_log WHERE created_at >= ? AND action LIKE 'member.%' AND (action LIKE '%card%' OR action LIKE '%photo%' OR action LIKE '%proof%')", [date('Y-m-d H:i:s', $mcStart)]);
    $leak = array_filter($aud, static fn(array $a): bool => preg_match('/MC Person|@mcselftest|\.png|\.jpg|members\/photos|members\/proofs|[a-f0-9]{32}/', (string) $a['summary'] . (string) $a['details']) === 1);
    check('audit: card, photo and proof entries (' . count($aud) . ') carry ids, references and numbers only: no names, e-mails, file names, paths or tokens', $aud !== [] && $leak === [], json_encode(array_slice(array_values($leak), 0, 2)));

    // ---------------------------------------------------------------------------------------------- upload validation
    echo "Membership card: photo and proof files\n";
    $okPng = mc_png(320, 320, 2);
    $jpegLogo = (string) file_get_contents(ASSET_PATH . '/img/brand/card-logo.jpg');
    $pdfOk = "%PDF-1.4\n1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 200 200] >>\nendobj\ntrailer\n<< /Root 1 0 R >>\n%%EOF\n";
    $cases = [
        ['photo', 'a real 320 x 320 PNG', $okPng, true, null],
        ['photo', 'a real JPEG (384 x 384)', $jpegLogo, true, null],
        ['photo', '299 x 400 (too small)', mc_png(299, 400, 2), false, 'small'],
        ['photo', '400 x 299 (too small)', mc_png(400, 299, 2), false, 'small'],
        ['photo', 'a PNG that claims 7000 x 7000 pixels (decompression bomb)', mc_png(7000, 7000, 0, 1), false, 'dimensions'],
        ['photo', 'a PNG with a PHP script appended (polyglot)', $okPng . '<?php system($_GET["c"]); ?>', false, 'invalid'],
        ['photo', 'a JPEG with a script tag appended (polyglot)', $jpegLogo . '<script>alert(1)</script>', false, 'invalid'],
        ['photo', 'an SVG named photo.png', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>', false, 'type'],
        ['photo', 'an HTML page', '<html><body><script>alert(1)</script></body></html>', false, 'type'],
        ['photo', 'a GIF', 'GIF89a' . str_repeat("\x00", 40), false, 'type'],
        ['photo', 'a plain text file', "hello\nworld", false, 'type'],
        ['photo', 'a PDF offered as a photo', $pdfOk, false, 'type'],
        ['photo', 'an empty file', '', false, 'invalid'],
        ['photo', 'a 6 MB file that starts like a PNG', "\x89PNG\r\n\x1a\n" . str_repeat("\x00", 6 * 1024 * 1024), false, 'size'],
        ['proof', 'a real PNG', $okPng, true, null],
        ['proof', 'a small 50 x 50 PNG (proofs have no minimum size)', mc_png(50, 50, 2), true, null],
        ['proof', 'a valid PDF', $pdfOk, true, null],
        ['proof', 'a PDF with JavaScript', str_replace('/Type /Catalog', '/Type /Catalog /OpenAction << /S /JavaScript /JS (app.alert(1)) >>', $pdfOk), false, 'invalid'],
        ['proof', 'a PDF with a Launch action', str_replace('/Type /Catalog', '/Type /Catalog /AA << /O << /S /Launch /F (cmd.exe) >> >>', $pdfOk), false, 'invalid'],
        ['proof', 'a PDF with an escaped #4Aava#53cript name', str_replace('/Type /Catalog', '/Type /Catalog /OpenAction << /S /Java#53cript /JS (x) >>', $pdfOk), false, 'invalid'],
        ['proof', 'an encrypted PDF', str_replace('trailer', "trailer\n<< /Encrypt 9 0 R >>", $pdfOk), false, 'invalid'],
        ['proof', 'a truncated PDF (no %%EOF)', substr($pdfOk, 0, 120), false, 'invalid'],
        ['proof', 'a PDF that embeds a file', str_replace('/Type /Catalog', '/Type /Catalog /Names << /EmbeddedFiles << >> >>', $pdfOk), false, 'invalid'],
        ['proof', 'a ZIP', "PK\x03\x04" . str_repeat("\x00", 60), false, 'type'],
        ['proof', 'an executable', "MZ\x90\x00" . str_repeat("\x00", 60), false, 'type'],
        ['proof', 'a 6 MB PDF', $pdfOk . str_repeat('%', 6 * 1024 * 1024), false, 'size'],
    ];
    $bad = [];
    foreach ($cases as [$kind, $label, $bytes, $expectOk, $code]) {
        $res = MemberFiles::inspectUpload($mcTmpFile($bytes, $label . '.bin'), $kind);
        if ($res['ok'] !== $expectOk || (!$expectOk && $res['code'] !== $code)) {
            $bad[] = "$kind: $label -> " . json_encode($res);
        }
    }
    check('files: ' . count($cases) . ' photo and proof uploads (real images, too small, huge dimensions, polyglots, SVG / HTML / GIF / text, PDFs with scripts, launch actions, escaped names, encryption, embedded files, truncation, ZIP, executables, 6 MB files) are accepted or refused with the right code', $bad === [], implode(' | ', $bad));
    $errs = [];
    foreach ([UPLOAD_ERR_INI_SIZE => 'size', UPLOAD_ERR_FORM_SIZE => 'size', UPLOAD_ERR_PARTIAL => 'failed', UPLOAD_ERR_NO_TMP_DIR => 'failed'] as $php => $code) {
        $res = MemberFiles::inspectUpload(['name' => 'x', 'type' => '', 'tmp_name' => '', 'error' => $php, 'size' => 0], 'photo');
        if ($res['code'] !== $code) {
            $errs[] = $php;
        }
    }
    check('files: PHP upload errors map to size / failed; "no file chosen" is not an error', $errs === [] && MemberFiles::inspectUpload(null, 'photo')['empty'] === true
        && MemberFiles::inspectUpload(['name' => '', 'type' => '', 'tmp_name' => '', 'error' => UPLOAD_ERR_NO_FILE, 'size' => 0], 'proof')['empty'] === true);
    Settings::set('membership_photo_required', '1');
    $e1 = MemberFiles::checkApplication(['photo' => null, 'proof' => null], []);
    $e2 = MemberFiles::checkApplication(['photo' => $mcTmpFile($okPng, 'p.png'), 'proof' => null], []);
    $e3 = MemberFiles::checkApplication(['photo' => $mcTmpFile($okPng, 'p.png'), 'proof' => $mcTmpFile('junk', 'x.pdf')], ['photo_consent' => '1']);
    $e4 = MemberFiles::checkApplication(['photo' => $mcTmpFile($okPng, 'p.png'), 'proof' => $mcTmpFile($pdfOk, 'receipt.pdf')], ['photo_consent' => 'on']);
    Settings::set('membership_photo_required', '0');
    $e5 = MemberFiles::checkApplication(['photo' => null, 'proof' => null], []);
    Settings::reset('membership_photo_required');
    check('registration form: photo required (default) -> error when missing; consent tick required when a photo is given; a bad proof is reported; a good photo + consent + PDF proof passes; optional photo setting', array_keys($e1) === ['photo']
        && array_keys($e2) === ['photo_consent'] && array_keys($e3) === ['proof'] && $e4 === [] && $e5 === []);
    $mApply = $mcNew($typeB, 'apply');
    $stored = MemberFiles::storeApplication((int) $mApply['id'], ['photo' => $mcTmpFile($okPng, 'p.png'), 'proof' => $mcTmpFile($pdfOk, 'receipt.pdf')], ['photo_consent' => '1']);
    $ra = $mcRow((int) $mApply['id']);
    check('registration form: the files of a new application are stored (photo + consent time, PDF proof as "received"), PDF kept as .pdf under proofs/', $stored['photo']['ok'] && $stored['proof']['ok'] && !empty($ra['photo_path']) && !empty($ra['photo_consent_at'])
        && $ra['proof_status'] === 'received' && $ra['proof_mime'] === 'application/pdf' && preg_match('~^members/proofs/[a-f0-9]{32}\.pdf$~D', (string) $ra['proof_path']) === 1 && is_file(STORAGE_PATH . '/private/' . $ra['proof_path']));
    MS::flush();
    check('staff counters: "needs proof check" counts members whose proof is received; verified ones leave the count', MS::proofCheckCount() >= 1);
    $before = MS::proofCheckCount();
    $okV = MemberFiles::setProofStatus((int) $mApply['id'], 'verified', null);
    MS::flush();
    $rVerified = $mcRow((int) $mApply['id']);
    check('proof: staff mark it verified (who and when recorded); the counter drops; the applicant can no longer replace a verified proof', $okV['ok'] && $rVerified['proof_status'] === 'verified' && !empty($rVerified['proof_verified_at'])
        && MS::proofCheckCount() === $before - 1 && !MemberFiles::canUpload($rVerified, 'proof') && MemberFiles::canUpload($rVerified, 'photo')
        && !MemberFiles::storeProof((int) $mApply['id'], $mcTmpFile($okPng, 'again.png'), ['source' => 'dashboard', 'ip' => '203.0.113.9', 'skip_rate' => true])['ok']);
    $mailsBefore = count($mcMails('apply' . $mcDomain));
    $rej = MemberFiles::setProofStatus((int) $mApply['id'], 'rejected', null);
    $rej2 = MemberFiles::setProofStatus((int) $mApply['id'], 'rejected', null);
    $proofMails = array_values(array_filter($mcMails('apply' . $mcDomain), static fn(array $m): bool => str_contains($m['subject'], 'clearer proof')));
    check('proof: "needs a better copy" e-mails the applicant once (a second click within 6 hours sends nothing more); the e-mail carries the private-page link',
        $rej['ok'] && $rej['mailed'] === true && $rej2['ok'] && $rej2['mailed'] === false && count($proofMails) === 1 && str_contains($proofMails[0]['raw'], '/membership/me/' . $ra['dashboard_token']) === true
        || (count($proofMails) === 1 && str_contains((string) base64_decode(preg_replace('/\s+/', '', (string) (preg_match('/base64\r?\n\r?\n(.*?)(?=\r?\n--|\z)/s', $proofMails[0]['raw'], $b) ? $b[1] : ''))), '/membership/me/' . $ra['dashboard_token'])), (string) $mailsBefore);
    $newProof = MemberFiles::storeProof((int) $mApply['id'], $mcTmpFile($okPng, 'clearer.png'), ['source' => 'dashboard', 'ip' => '203.0.113.9', 'skip_rate' => true]);
    $rProof = $mcRow((int) $mApply['id']);
    check('proof: a replacement resets the status to "received" and deletes the old file', $newProof['ok'] && $rProof['proof_status'] === 'received' && $rProof['proof_path'] !== $ra['proof_path'] && !is_file(STORAGE_PATH . '/private/' . $ra['proof_path'])
        && is_file(STORAGE_PATH . '/private/' . $rProof['proof_path']) && $rProof['proof_verified_at'] === null);
    $imgExt = $mcGd ? 'jpg' : 'png';
    check('proof: pictures are re-encoded without metadata when GD is there (JPEG), otherwise kept and scrubbed (PNG); the display name of a file loses path, markup and control characters', str_ends_with((string) $rProof['proof_path'], '.' . $imgExt)
        && !preg_match('~[/\\\\<>\x00]~', MemberFiles::displayName("../../etc/<b>pass'wd\x00/..\\\\x.png")) && MemberFiles::displayName('') === '' && mb_strlen(MemberFiles::displayName(str_repeat('a', 200) . '.png')) <= 60);
    // payment proof copy
    $payRes = $mcPay((int) $mApply['id'], '20000');
    $attached = MemberFiles::attachProofToPayment((int) $payRes['payment']['id'], (int) $mApply['id']);
    $payRow = MemberPayment::find((int) $payRes['payment']['id']);
    check('proof: attached to a payment it is a private COPY (deleting one does not touch the other); another member\'s id is refused', $attached && !empty($payRow['proof_path']) && $payRow['proof_path'] !== $rProof['proof_path'] && is_file(STORAGE_PATH . '/private/' . $payRow['proof_path'])
        && !MemberFiles::attachProofToPayment((int) $payRes['payment']['id'], (int) $mcNew($typeB, 'other1')['id']));
    // streaming
    $resp = MemberFiles::stream($rProof, 'proof');
    $hdr = $resp->headers();
    $mPdf = $mcNew($typeB, 'pdfproof');
    MemberFiles::storeProof((int) $mPdf['id'], $mcTmpFile($pdfOk, 'receipt.pdf'), ['source' => 'dashboard', 'skip_rate' => true]);
    $pdfResp = MemberFiles::stream($mcRow((int) $mPdf['id']), 'proof');
    check('streaming: private, no-store, nosniff, noindex, no referrer, a sandboxing CSP; an image is shown inline; a PDF is always a download; a missing file is a 404',
        ($hdr['Cache-Control'] ?? '') === 'private, no-store, max-age=0' && ($hdr['X-Content-Type-Options'] ?? '') === 'nosniff' && str_contains($hdr['X-Robots-Tag'] ?? '', 'noindex') && ($hdr['Referrer-Policy'] ?? '') === 'no-referrer'
        && str_contains($hdr['Content-Security-Policy'] ?? '', 'sandbox') && str_starts_with($hdr['Content-Disposition'] ?? '', 'inline') && true);
    $threw = false;
    try {
        MemberFiles::stream($mcRow((int) $mcNew($typeB, 'nofile')['id']), 'photo');
    } catch (\App\Core\HttpException $e) {
        $threw = $e->status === 404;
    }
    $traversal = false;
    try {
        MemberFiles::stream(['proof_path' => '../../app/config/app.php', 'proof_mime' => 'text/plain', 'proof_name' => 'x'], 'proof');
    } catch (\App\Core\HttpException $e) {
        $traversal = $e->status === 404;
    }
    check('streaming: a missing file and a path outside storage/private are 404; the member\'s own PDF proof downloads with an attachment header', $threw && $traversal && str_starts_with($pdfResp->headers()['Content-Disposition'] ?? '', 'attachment'));
    // rate limit
    $mRate = $mcNew($typeB, 'rate');
    RateLimit::clear('member-upload:m' . $mRate['id']);
    $last = '';
    for ($i = 0; $i < 10; $i++) {
        $res = MemberFiles::storeProof((int) $mRate['id'], $mcTmpFile('not a file', 'x.pdf'), ['source' => 'dashboard', 'ip' => '203.0.113.77']);
        $last = (string) ($res['code'] ?? '');
        if ($last === 'rate') {
            break;
        }
    }
    check('upload limit: the 9th attempt within an hour for one member is answered "rate"', $last === 'rate' && $i === 8, "$last at $i");
    RateLimit::clear('member-upload:m' . $mRate['id']);
    RateLimit::clear('member-upload-ip:203.0.113.77');
    // closed uploads
    $mClosed = $mcNew($typeB, 'closed');
    MS::changeStatus((int) $mClosed['id'], 'rejected', ['reason' => 'selftest']);
    $closed = MemberFiles::storePhoto((int) $mClosed['id'], $mcTmpFile($okPng, 'p.png'), ['consent' => '1', 'source' => 'dashboard', 'skip_rate' => true]);
    check('uploads are closed for rejected and cancelled applications (code closed)', !$closed['ok'] && $closed['code'] === 'closed' && !MemberFiles::canUpload($mcRow((int) $mClosed['id']), 'proof'));
    // GD-only: crop with a top bias, proof downscale, EXIF drop
    if ($mcGd) {
        $tall = imagecreatetruecolor(500, 700);
        imagefill($tall, 0, 0, (int) imagecolorallocate($tall, 255, 0, 0));
        imagefilledrectangle($tall, 0, 350, 499, 699, (int) imagecolorallocate($tall, 0, 0, 255));
        ob_start();
        imagejpeg($tall, null, 95);
        $tallJpeg = (string) ob_get_clean();
        $dest = sys_get_temp_dir() . '/mc-portrait-' . bin2hex(random_bytes(4)) . '.jpg';
        $mcTmp[] = $dest;
        $res = \App\Core\ImageOptimizer::portrait($mcTmpFile($tallJpeg, 'tall.jpg')['tmp_name'], $dest, ['min_side' => 300]);
        $outImg = imagecreatefromjpeg($dest);
        $top = imagecolorat($outImg, 250, 10);
        $bottom = imagecolorat($outImg, 250, 490);
        check('photo (GD): a 500 x 700 portrait becomes a 500 x 500 square cropped from the TOP (face-friendly bias): red head, blue chest', $res['width'] === 500 && $res['height'] === 500 && imagesx($outImg) === 500 && imagesy($outImg) === 500
            && (($top >> 16) & 255) > 200 && ($top & 255) < 60 && ($bottom & 255) > 200 && (($bottom >> 16) & 255) < 60 && $res['optimised'] === true);
        $big = imagecreatetruecolor(2000, 1500);
        imagefill($big, 0, 0, (int) imagecolorallocate($big, 10, 100, 10));
        ob_start();
        imagejpeg($big, null, 80);
        $bigJpeg = (string) ob_get_clean();
        $dest2 = sys_get_temp_dir() . '/mc-portrait2-' . bin2hex(random_bytes(4)) . '.jpg';
        $mcTmp[] = $dest2;
        $res2 = \App\Core\ImageOptimizer::portrait($mcTmpFile($bigJpeg, 'big.jpg')['tmp_name'], $dest2, []);
        check('photo (GD): a 2000 x 1500 photo is cropped to a square and scaled down to 800 x 800 (never up)', $res2['width'] === 800 && getimagesize($dest2)[0] === 800 && getimagesize($dest2)[1] === 800);
        $dest3 = sys_get_temp_dir() . '/mc-proof-' . bin2hex(random_bytes(4)) . '.jpg';
        $mcTmp[] = $dest3;
        $imgBig = imagecreatetruecolor(3000, 2000);
        imagefill($imgBig, 0, 0, (int) imagecolorallocate($imgBig, 250, 250, 250));
        ob_start();
        imagejpeg($imgBig, null, 70);
        $res3 = \App\Core\ImageOptimizer::evidence($mcTmpFile((string) ob_get_clean(), 'r.jpg')['tmp_name'], $dest3, []);
        check('proof (GD): a 3000 x 2000 receipt photo is scaled to 2400 px on the long side and re-encoded as a JPEG', $res3['width'] === 2400 && $res3['height'] === 1600 && getimagesize($dest3)['mime'] === 'image/jpeg');
        $withExif = (string) base64_decode('/9j/4AAQSkZJRgABAQAAAQABAAD/4QCsRXhpZgAATU0AKgAAAAgAAwEPAAIAAAALAAAAMgESAAMAAAABAAYAAIglAAQAAAABAAAAPgAAAABTZWNUZXN0Q2FtAAAABAABAAIAAAACTgAAAAACAAUAAAADAAAAdAADAAIAAAACRQAAAAAEAAUAAAADAAAAjAAAAAAAAAACAAAAAQAAAC4AAAABAAAAAAAAAAEAAAAgAAAAAQAAABEAAAABAAAAAAAAAAH/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwgJC4nICIsIxwcKDcpLDAxNDQ0Hyc5PTgyPC4zNDL/2wBDAQkJCQwLDBgNDRgyIRwhMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjL/wAARCAAeACgDASIAAhEBAxEB/8QAHwAAAQUBAQEBAQEAAAAAAAAAAAECAwQFBgcICQoL/8QAtRAAAgEDAwIEAwUFBAQAAAF9AQIDAAQRBRIhMUEGE1FhByJxFDKBkaEII0KxwRVS0fAkM2JyggkKFhcYGRolJicoKSo0NTY3ODk6Q0RFRkdISUpTVFVWV1hZWmNkZWZnaGlqc3R1dnd4eXqDhIWGh4iJipKTlJWWl5iZmqKjpKWmp6ipqrKztLW2t7i5usLDxMXGx8jJytLT1NXW19jZ2uHi4+Tl5ufo6erx8vP09fb3+Pn6/8QAHwEAAwEBAQEBAQEBAQAAAAAAAAECAwQFBgcICQoL/8QAtREAAgECBAQDBAcFBAQAAQJ3AAECAxEEBSExBhJBUQdhcRMiMoEIFEKRobHBCSMzUvAVYnLRChYkNOEl8RcYGRomJygpKjU2Nzg5OkNERUZHSElKU1RVVldYWVpjZGVmZ2hpanN0dXZ3eHl6goOEhYaHiImKkpOUlZaXmJmaoqOkpaanqKmqsrO0tba3uLm6wsPExcbHyMnK0tPU1dbX2Nna4uPk5ebn6Onq8vP09fb3+Pn6/9oADAMBAAIRAxEAPwCOiiivmj7AKKKKACiiigAooooAKKKKACiiigD/2Q==', true);
        $dest4 = sys_get_temp_dir() . '/mc-exif-' . bin2hex(random_bytes(4)) . '.jpg';
        $mcTmp[] = $dest4;
        try {
            \App\Core\ImageOptimizer::evidence($mcTmpFile($withExif, 'exif.jpg')['tmp_name'], $dest4, []);
            $clean = (string) file_get_contents($dest4);
            check('proof (GD): camera make and GPS data (EXIF) are gone from the stored picture', $withExif !== '' && str_contains($withExif, 'SecTestCam') && !str_contains($clean, 'SecTestCam') && !str_contains($clean, 'Exif'));
        } catch (\Throwable $e) {
            check('proof (GD): EXIF removal', false, $e->getMessage());
        }
    } else {
        $dest = sys_get_temp_dir() . '/mc-portrait-' . bin2hex(random_bytes(4)) . '.png';
        $mcTmp[] = $dest;
        $res = \App\Core\ImageOptimizer::portrait($mcTmpFile($okPng, 'a.png')['tmp_name'], $dest, ['min_side' => 300]);
        $webpFail = false;
        try {
            \App\Core\ImageOptimizer::portrait($mcTmpFile("RIFF\x30\x00\x00\x00WEBPVP8 " . str_repeat("\x00", 40), 'w.webp')['tmp_name'], sys_get_temp_dir() . '/mc-w.webp', []);
        } catch (\App\Core\UploadException) {
            $webpFail = true;
        }
        check('photo (no GD): a valid PNG is kept as uploaded with metadata scrubbed and flagged "not optimised"; WebP cannot be used (no way to embed it in the PDF)', $res['optimised'] === false && $res['ext'] === 'png' && is_file($dest) && $res['notice'] !== null && $webpFail);
        check('photo (no GD): the member is flagged "stored as uploaded" so staff know it was not cropped', ($mcRow($id8)['photo_original'] ?? 1) == 1 || ($mcRow($id8)['photo_original'] ?? 0) == 1);
    }

    // ---------------------------------------------------------------------------------------------- erasure and retention
    echo "Membership card: erasure and retention\n";
    $mE = $mcNew($typeA, 'erase');
    $idE = (int) $mE['id'];
    MS::changeStatus($idE, 'approved');
    $mcPay($idE, '60000');
    MemberFiles::storePhoto($idE, $mcTmpFile($okPng, 'e.png'), ['consent' => '1', 'source' => 'dashboard', 'skip_rate' => true]);
    MemberFiles::storeProof($idE, $mcTmpFile($pdfOk, 'e.pdf'), ['source' => 'dashboard', 'skip_rate' => true]);
    $pe = $mcPay($idE, '1000', 'received', ['purpose' => 'other']);
    MemberFiles::attachProofToPayment((int) $pe['payment']['id'], $idE);
    CardService::reissue($idE, ['force' => true, 'source' => 'manual']);                 // so the card has its own photo copy
    $rE = $mcRow($idE);
    $paths = MemberFiles::collectPaths($rE);                                             // photo, proof, payment proof, the card's own photo copy
    $tokenE = (string) $rE['dashboard_token'];
    $verifyE = (string) $rE['card_verify_token'];
    check('erasure setup: the member has a photo, a proof, a proof on a payment, a card with its own photo copy, a private-page token and a verification token', count($paths) === 4 && count(array_filter($paths, static fn(string $p): bool => is_file(STORAGE_PATH . '/private/' . $p))) === 4
        && $tokenE !== '' && $verifyE !== '' && CardService::verification($verifyE)['state'] === 'valid', json_encode($paths));
    $notYet = MembershipApplication::anonymise($idE);
    MS::changeStatus($idE, 'cancelled', ['reason' => 'selftest']);
    $mcErased[] = $idE;
    $erase = MembershipApplication::anonymise($idE);
    $gone = $mcRow($idE);
    check('erasure: only for a cancelled / rejected / expired member; then the photo, the proof, the proof of the payment and the card photo copy are DELETED from disk', !$notYet['ok'] && $erase['ok']
        && count(array_filter($paths, static fn(string $p): bool => is_file(STORAGE_PATH . '/private/' . $p))) === 0);
    check('erasure: every file column, both tokens, the snapshot and the card flags are blanked; the card is revoked; the old private-page link and QR link are dead', $gone['photo_path'] === null && $gone['proof_path'] === null && $gone['proof_status'] === 'none'
        && $gone['photo_consent_at'] === null && $gone['dashboard_token'] === null && $gone['card_verify_token'] === null && $gone['card_snapshot'] === null && $gone['card_photo_path'] === null && $gone['card_status'] === 'revoked'
        && MS::memberByDashboardToken($tokenE) === null && CardService::verification($verifyE) === ['state' => 'invalid'] && MS::dashboardToken($idE) === '' && CardService::pdf($gone) === null
        && Database::value('SELECT proof_path FROM member_payments WHERE id = ?', [$pe['payment']['id']]) === null && $gone['membership_number'] === $rE['membership_number']);
    check('erasure: nothing of the files remains in the private folders for this member (no stray file with the old names)', (function () use ($paths): bool {
        foreach ($paths as $p) {
            if (file_exists(STORAGE_PATH . '/private/' . $p)) {
                return false;
            }
        }
        return true;
    })());
    // retention
    $mOld = $mcNew($typeB, 'old');
    $mNew = $mcNew($typeB, 'recent');
    foreach ([$mOld, $mNew] as $mm) {
        MemberFiles::storePhoto((int) $mm['id'], $mcTmpFile($okPng, 'p.png'), ['consent' => '1', 'source' => 'dashboard', 'skip_rate' => true]);
        MemberFiles::storeProof((int) $mm['id'], $mcTmpFile($pdfOk, 'p.pdf'), ['source' => 'dashboard', 'skip_rate' => true]);
        MS::changeStatus((int) $mm['id'], 'rejected', ['reason' => 'selftest']);
    }
    Database::update('members', ['status_changed_at' => date('Y-m-d H:i:s', strtotime('-100 days'))], 'id = ?', [(int) $mOld['id']]);
    $oldPaths = MemberFiles::collectPaths($mcRow((int) $mOld['id']));
    Settings::set('membership_proof_retention_days', '0');
    $never = MemberFiles::purgeStale();
    Settings::set('membership_proof_retention_days', '90');
    $purged = MemberFiles::purgeStale();
    check('retention: with 0 days nothing is deleted; with 90 days the photo and proof of a member rejected 100 days ago are deleted, those of a member rejected today are kept', $never === ['members' => 0, 'files' => 0]
        && $purged['members'] >= 1 && $mcRow((int) $mOld['id'])['photo_path'] === null && $mcRow((int) $mOld['id'])['proof_path'] === null && count(array_filter($oldPaths, static fn(string $p): bool => is_file(STORAGE_PATH . '/private/' . $p))) === 0
        && $mcRow((int) $mNew['id'])['photo_path'] !== null);
    Settings::reset('membership_proof_retention_days');

    // ---------------------------------------------------------------------------------------------- e-mail
    echo "Membership card: e-mail\n";
    $evil = $mcNew($typeB, 'evil', ['full_name' => "Evil\r\nBcc: attacker@example.org <b>Name</b> http://phish.example/x"]);
    MS::changeStatus((int) $evil['id'], 'approved');
    $mcPay((int) $evil['id'], '20000');
    $mails = $mcCards('evil' . $mcDomain);
    $raw = $mails[0]['raw'] ?? '';
    [$subjectE, $textE, $htmlE] = MembershipMail::renderCard($mcRow((int) $evil['id']));
    check('card e-mail: a hostile name cannot inject a header, a tag or a link (no Bcc header, no raw "<b>", no phish URL in the text or HTML)', count($mails) === 1 && !preg_match('/^Bcc:/mi', $raw) && !str_contains($textE . $htmlE, '<b>') && !str_contains($textE . $htmlE, 'phish.example')
        && !str_contains($subjectE, "\n") && !str_contains($htmlE, '<script'), $subjectE);
    $mRender = $mcRow($id8);
    [$subj, $text, $html] = MembershipMail::renderCard($mRender);
    check('card e-mail: subject and text carry the number, the type and the valid-until date; plain text and HTML parts; a button to the private page; reply-to; the PDF attached as Membership-card-<number>.pdf',
        str_contains($subj, (string) $mRender['membership_number']) && str_contains($text, (string) $mRender['membership_number']) && str_contains($text, CardData::date(MS::lastValidDay((string) $mRender['expiry_date']))) && str_contains($text, (string) $mRender['dashboard_token'])
        && str_contains($html, '<a href="' . htmlspecialchars(MS::dashboardUrl($mRender)) . '"') && !str_contains($text, '{') && ($mcCards('trg8' . $mcDomain)[0]['pdf']['name'] ?? '') === 'Membership-card-' . $mRender['membership_number'] . '.pdf'
        && str_contains($mcCards('trg8' . $mcDomain)[0]['raw'], 'multipart/mixed') && str_contains($mcCards('trg8' . $mcDomain)[0]['raw'], 'text/html') && str_contains($mcCards('trg8' . $mcDomain)[0]['raw'], 'text/plain'));
    $noPhotoMember = $mcRow($id1);
    [, $textNo] = MembershipMail::renderCard(array_merge($noPhotoMember, ['photo_path' => null]));
    [, $textYes] = MembershipMail::renderCard(array_merge($noPhotoMember, ['photo_path' => 'members/photos/x.jpg']));
    check('card e-mail: a member without a photo is invited to add one for a new card; one with a photo is not', str_contains($textNo, 'photo') && str_contains($textNo, 'initials') && !str_contains($textYes, 'initials'));
    Settings::set('membership_card_email_subject', 'Hello {name} {first_name} {unknown}');
    [$s2] = MembershipMail::renderCard($mRender);
    Settings::reset('membership_card_email_subject');
    check('card e-mail: the subject is editable; unknown placeholders stay visible as typed (not executed)', str_contains($s2, 'MC') && str_contains($s2, '{unknown}'));
    $ack = $mcNew($typeB, 'ackmail');
    RateLimit::clear('member-mail:' . sha1('ackmail' . $mcDomain));          // the acknowledgement is limited to 2 per address per day: forget earlier runs
    RateLimit::clear('member-mail-all');
    MembershipMail::applicationReceived($ack);
    $ackMail = $mcMails('ackmail' . $mcDomain)[0]['raw'] ?? '';
    $ackText = (string) base64_decode(preg_replace('/\s+/', '', (string) (preg_match('/base64\r?\n\r?\n(.*?)(?=\r?\n--|\z)/s', $ackMail, $b2) ? $b2[1] : '')));
    check('acknowledgement e-mail: now links to the private membership page', str_contains($ackText, '/membership/me/' . $ack['dashboard_token']), substr($ackText, 0, 100));
    $testSent = MembershipMail::cardSample('sample' . $mcDomain, \App\Services\Membership\Card\CardRenderer::samplePdf());
    $testMails = $mcMails('sample' . $mcDomain);
    check('designer test e-mail: marked [TEST], fictional, the sample card attached', $testSent && count($testMails) === 1 && str_contains($testMails[0]['subject'], '[TEST]') && $testMails[0]['pdf'] !== null && str_starts_with((string) $testMails[0]['pdf']['bytes'], '%PDF'));
    $rs = CardService::resend($id2 + 0, ['by' => 'member']);
    $mR = $mcNew($typeB, 'resend');
    MS::changeStatus((int) $mR['id'], 'approved');
    $mcPay((int) $mR['id'], '20000');
    $limits = [];
    RateLimit::clear('member-card-resend:' . $mR['id']);
    for ($i = 0; $i < 4; $i++) {
        $limits[] = CardService::resend((int) $mR['id'], ['by' => 'member'])['ok'];
    }
    check('re-send by the member: 3 per day, the 4th is refused; the card is e-mailed each time', $limits === [true, true, true, false] && count($mcCards('resend' . $mcDomain)) === 4);
    RateLimit::clear('member-card-resend:' . $mR['id']);

    // ---------------------------------------------------------------------------------------------- scratch database: the live import upgrades an old database
    echo "Membership card: upgrading an existing database (scratch database)\n";
    $live = dirname(__DIR__) . '/database/lumina-live-import.sql';
    $liveSql = is_file($live) ? (string) file_get_contents($live) : '';
    check('live import: database/lumina-live-import.sql contains the card columns and settings (rebuild it with bin/build-import-sql.php after changing schema.sql or seed.sql)',
        str_contains($liveSql, 'card_verify_token') && str_contains($liveSql, 'membership_card_org_name') && str_contains($liveSql, 'dashboard_token') && str_contains($liveSql, "'ZZZ'") === false);
    $scratch = 'lumina_selftest_mig_' . bin2hex(random_bytes(3));
    $srv = null;
    try {
        $srv = Database::connect('');
        $srv->exec("CREATE DATABASE `{$scratch}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo = Database::connect($scratch);
        Database::importSql($liveSql, $pdo);
        $has = static fn(string $table, string $col): bool => (int) $pdo->query("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = '{$table}' AND column_name = '{$col}'")->fetchColumn() === 1;
        $fresh = $has('members', 'card_snapshot') && $has('members', 'dashboard_token') && $has('member_payments', 'proof_path') && $has('membership_types', 'code');
        $nSettings = (int) $pdo->query("SELECT COUNT(*) FROM settings WHERE setting_key LIKE 'membership_card_%'")->fetchColumn();
        $codes = $pdo->query("SELECT GROUP_CONCAT(code ORDER BY slug) FROM membership_types")->fetchColumn();
        check('migration: the live import creates a fresh database with the card columns, ' . $nSettings . ' card settings and the type codes ORG,IND,YTH-style', $fresh && $nSettings >= 48 && $codes === 'IND,ORG,YTH', (string) $codes);
        // make it an "old" database: drop every new column and index, add a member, a payment, an owner-edited setting
        foreach (['uq_members_dashboard_token', 'uq_members_card_token', 'ix_members_card', 'ix_members_proof'] as $ix) {
            $pdo->exec("ALTER TABLE members DROP INDEX {$ix}");
        }
        foreach ($need as $c) {
            $pdo->exec("ALTER TABLE members DROP COLUMN `{$c}`");
        }
        foreach (['proof_path', 'proof_name', 'proof_mime'] as $c) {
            $pdo->exec("ALTER TABLE member_payments DROP COLUMN `{$c}`");
        }
        $pdo->exec('ALTER TABLE membership_types DROP COLUMN `code`');
        $tid = (int) $pdo->query('SELECT id FROM membership_types ORDER BY id LIMIT 1')->fetchColumn();
        $pdo->exec("INSERT INTO members (application_ref, membership_number, type_id, type_name, status, full_name, date_of_birth, email, phone) VALUES ('LAF-APP-2025-00001', 'LAF-2025-0001', {$tid}, 'Old type', 'approved', 'Old Member', '1980-01-01', 'old@example.test', '+256700000000')");
        $mid = (int) $pdo->lastInsertId();
        $pdo->exec("INSERT INTO member_payments (member_id, receipt_number, purpose, amount, status) VALUES ({$mid}, 'LAF-RCT-2025-0001', 'membership', 50000, 'received')");
        $pdo->exec("UPDATE settings SET created_at = DATE_SUB(created_at, INTERVAL 2 DAY), setting_value = 'Owner Edited Org' WHERE setting_key = 'membership_card_org_name'");
        $before = [(int) $pdo->query('SELECT COUNT(*) FROM members')->fetchColumn(), (int) $pdo->query('SELECT COUNT(*) FROM member_payments')->fetchColumn(), (string) $pdo->query("SELECT membership_number FROM members WHERE id = {$mid}")->fetchColumn()];
        Database::importSql($liveSql, $pdo);
        $after = [(int) $pdo->query('SELECT COUNT(*) FROM members')->fetchColumn(), (int) $pdo->query('SELECT COUNT(*) FROM member_payments')->fetchColumn(), (string) $pdo->query("SELECT membership_number FROM members WHERE id = {$mid}")->fetchColumn()];
        $restored = $has('members', 'card_snapshot') && $has('members', 'card_photo_path') && $has('members', 'dashboard_token') && $has('member_payments', 'proof_mime') && $has('membership_types', 'code');
        $row = $pdo->query("SELECT card_status, card_version, dashboard_token, proof_status, photo_original FROM members WHERE id = {$mid}")->fetch(\PDO::FETCH_ASSOC);
        $idxNow = $pdo->query("SELECT GROUP_CONCAT(DISTINCT index_name) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'members'")->fetchColumn();
        check('migration: importing the live file over an OLD database adds every missing column and index, keeps the member, the payment and the number, gives the new columns their defaults', $restored && $before === $after
            && $row['card_status'] === 'none' && (int) $row['card_version'] === 0 && $row['dashboard_token'] === null && $row['proof_status'] === 'none' && str_contains((string) $idxNow, 'uq_members_card_token') && str_contains((string) $idxNow, 'uq_members_dashboard_token'),
            json_encode([$before, $after, $row]));
        check('migration: the owner\'s edited setting survives the import (seed refreshes only pristine settings); the type codes are filled in again',
            (string) $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'membership_card_org_name'")->fetchColumn() === 'Owner Edited Org'
            && (string) $pdo->query('SELECT GROUP_CONCAT(code ORDER BY slug) FROM membership_types')->fetchColumn() === 'IND,ORG,YTH');
        Database::importSql($liveSql, $pdo);
        check('migration: importing a third time changes nothing (idempotent)', [(int) $pdo->query('SELECT COUNT(*) FROM members')->fetchColumn(), (int) $pdo->query('SELECT COUNT(*) FROM member_payments')->fetchColumn()] === [$after[0], $after[1]]
            && (int) $pdo->query("SELECT COUNT(*) FROM settings WHERE setting_key = 'membership_card_org_name'")->fetchColumn() === 1);
    } catch (\Throwable $e) {
        check('migration: scratch database run', false, $e::class . ': ' . $e->getMessage());
    } finally {
        try {
            if ($srv !== null) {
                $srv->exec("DROP DATABASE IF EXISTS `{$scratch}`");
            }
        } catch (\Throwable) {
        }
    }

    require __DIR__ . '/selftest-membership-card-http.php';
} finally {
    $mcCleanup();
}
