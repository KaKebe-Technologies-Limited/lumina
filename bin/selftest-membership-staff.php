<?php
/**
 * MEMBERSHIP CARD, STAFF side (docs/MEMBERSHIP_CARD.md sections 7 and 10): the member screen's proof of payment / photo / card panels and their actions, the payment
 * form with a proof attached, the automatic issue rule at approval, payment and renewal (all three trigger settings; one card e-mail per version), bulk issue, the
 * list filters and the sidebar counter, the abilities (editor, a custom role with ONLY members.manage / members.card / members.delete), the erasure wording and
 * what is deleted, and the audit log (ids only). Real requests to http://localhost/<folder>/ with curl; skipped when the site does not answer.
 *
 * Run alone:   /c/xampp/php/php.exe bin/selftest-membership-staff.php      (or from bin/selftest.php, which shares check() and $fail)
 * Creates its own types (zz-ms-*), members (@msstaff.invalid), users and roles (zz-ms-*) and removes them, their files and their e-mails afterwards; the settings it
 * changes are restored. The web server's PHP may differ from the CLI (for example no GD): only what holds for both is asserted.
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
    $msStandalone = true;
}

use App\Core\Auth;
use App\Core\Database;
use App\Core\Roles;
use App\Core\Settings;
use App\Core\Uploader;
use App\Models\Member;
use App\Models\User;
use App\Services\Membership\Card\CardSettings;
use App\Services\Membership\MemberFiles;
use App\Services\Membership\MembershipApplication;
use App\Services\Membership\MembershipService as MS;

echo "Membership card, staff side: proof, photo, card panels and actions, triggers, bulk, abilities, erasure, audit\n";

$msBase = 'http://localhost/' . basename(BASE_PATH);
$msProbe = (function () use ($msBase): bool {
    if (!function_exists('curl_init')) {
        return false;
    }
    $ch = curl_init($msBase . '/robots.txt');
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 4, CURLOPT_CONNECTTIMEOUT => 2]);
    curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);
    return $code === 200;
})();
if (!$msProbe) {
    echo "  skip  the site does not answer at {$msBase}/ (staff-side checks skipped)\n";
    return;
}

$msStart = time() - 2;
$msDomain = '@msstaff.invalid';
$msTmp = [];
$msWatched = ['membership_open', 'membership_card_trigger', 'membership_card_require_photo', 'membership_photo_required', 'membership_card_reissue_renewal'];
$msSaved = [];
foreach ($msWatched as $k) {
    $msSaved[$k] = [Settings::isCustomised($k), (string) Settings::get($k, '')];
}
$msCleanup = static function () use (&$msTmp, &$msSaved, $msDomain, $msStart): void {
    // an ERASED member has lost its e-mail address, so the members of the test types are found by their type
    $rows = Database::all("SELECT * FROM members WHERE email LIKE ? OR type_id IN (SELECT id FROM membership_types WHERE slug LIKE 'zz-ms-%')", ['%' . $msDomain]);
    $ids = array_map(static fn(array $r): int => (int) $r['id'], $rows);
    foreach ($rows as $m) {
        $paths = MemberFiles::collectPaths($m);
        if (!empty($m['card_photo_path'])) {
            $paths[] = (string) $m['card_photo_path'];
        }
        MemberFiles::deletePaths($paths);
    }
    $in = $ids === [] ? '0' : implode(',', $ids);
    Database::execute("DELETE FROM member_payments WHERE member_id IN ($in)");
    Database::execute("DELETE FROM members WHERE id IN ($in)");
    Database::execute('DELETE FROM subscribers WHERE email LIKE ?', ['%' . $msDomain]);
    $typeIds = array_map(static fn(array $r): int => (int) $r['id'], Database::all("SELECT id FROM membership_types WHERE slug LIKE 'zz-ms-%'"));
    $tin = $typeIds === [] ? '0' : implode(',', $typeIds);
    Database::execute("DELETE FROM audit_log WHERE (entity_type = 'member' AND entity_id IN ($in)) OR user_name LIKE 'ZZ MS%' OR (entity_type = 'membership_type' AND entity_id IN ($tin))");
    Database::execute("DELETE FROM membership_types WHERE slug LIKE 'zz-ms-%'");
    Database::execute("DELETE FROM users WHERE email LIKE 'zz-ms-%@example.test'");
    Database::execute("DELETE FROM roles WHERE slug LIKE 'zz-ms-%'");
    foreach (glob(STORAGE_PATH . '/mail/*.eml') ?: [] as $f) {
        if (filemtime($f) >= $msStart && str_contains((string) file_get_contents($f), $msDomain)) {
            @unlink($f);
        }
    }
    foreach ($msSaved as $k => [$custom, $val]) {
        $custom ? Settings::set($k, $val) : Settings::reset($k);
    }
    foreach ($msTmp as $p) {
        @unlink($p);
    }
    MS::flush();
    Settings::flush();
    Roles::flush();
};
$msCleanup();                                                     // leftovers of an aborted earlier run
register_shutdown_function($msCleanup);                           // also after a fatal error halfway
Settings::set('membership_open', '1');
foreach (['membership_card_trigger', 'membership_card_require_photo', 'membership_photo_required', 'membership_card_reissue_renewal'] as $k) {
    Settings::reset($k);
}

// ---------------------------------------------------------------------------------------------- helpers
$msPng = static function (int $w, int $h): string {
    $raw = '';
    for ($y = 0; $y < $h; $y++) {
        $raw .= "\x00" . str_repeat(chr(($y * 5 + 40) & 0xFF) . "\x80\xC0", $w);
    }
    $chunk = static fn(string $t, string $d): string => pack('N', strlen($d)) . $t . $d . pack('N', crc32($t . $d));
    return "\x89PNG\r\n\x1a\n" . $chunk('IHDR', pack('NNCCCCC', $w, $h, 8, 2, 0, 0, 0)) . $chunk('IDAT', (string) gzcompress($raw, 6)) . $chunk('IEND', '');
};
$msFile = static function (string $bytes, string $name = 'upload.png') use (&$msTmp): array {
    $p = tempnam(sys_get_temp_dir(), 'msf') ?: '';
    file_put_contents($p, $bytes);
    $msTmp[] = $p;
    return ['name' => $name, 'type' => 'application/octet-stream', 'tmp_name' => $p, 'error' => UPLOAD_ERR_OK, 'size' => strlen($bytes)];
};
$msCurl = static function (string $bytes, string $name, string $mime) use (&$msTmp): \CURLFile {
    $p = tempnam(sys_get_temp_dir(), 'msu') ?: '';
    file_put_contents($p, $bytes);
    $msTmp[] = $p;
    return new \CURLFile($p, $mime, $name);
};
$okPng = $msPng(330, 330);
$tinyPng = $msPng(200, 200);
$msType = static function (string $slug, string $name, string $code, string $fee, string $period): int {
    $r = MS::saveType(null, ['name' => $name, 'slug' => $slug, 'code' => $code, 'fee_amount' => $fee, 'joining_fee' => '0', 'currency' => 'UGX', 'period' => $period, 'is_active' => '1', 'sort_order' => 9301]);
    return (int) ($r['id'] ?? 0);
};
$typeA = $msType('zz-ms-annual', 'ZZ MS Annual', 'ZQ', '50000', 'annual');
$typeF = $msType('zz-ms-free', 'ZZ MS Free', 'ZR', '0', 'lifetime');
$msNew = static function (int $type, string $tag, array $over = []) use ($msDomain): array {
    Settings::set('membership_open', '1');                          // (other test runs restore this setting when they finish)
    $r = MembershipApplication::process($over + [
        'type_id' => $type, 'full_name' => 'MS Person ' . $tag, 'gender' => 'female', 'date_of_birth' => '1990-05-20', 'nationality' => 'Ugandan',
        'email' => $tag . $msDomain, 'phone' => '+256700000001', 'country' => 'Uganda', 'region' => 'Gulu', 'city' => 'Laroo',
        'occupation_status' => 'employed', 'education_level' => 'diploma', 'availability' => 'weekends', 'heard_about' => 'website',
        'motivation' => 'I would like to volunteer and support young people.', 'emergency_name' => 'Em Contact', 'emergency_phone' => '+256700000002',
        'emergency_relationship' => 'Sister', 'payment_method' => 'mobile_money', 'consent_terms' => '1', 'consent_privacy' => '1', 'declaration' => '1',
    ], ['ip' => '203.0.113.9', 'user_agent' => 'selftest']);
    if (!$r['ok'] || empty($r['member'])) {
        throw new \RuntimeException('could not create member ' . $tag . ': ' . json_encode($r['errors'] ?? $r));
    }
    return (array) $r['member'];
};
/** A pending member, optionally with a photo and a proof of payment (stored like the member's own upload). */
$msMember = static function (string $tag, int $type, bool $photo = true, bool $proof = true) use ($msNew, $msFile, $okPng): int {
    $id = (int) $msNew($type, $tag)['id'];
    if ($photo) {
        MemberFiles::storePhoto($id, $msFile($okPng, 'me.png'), ['consent' => '1', 'source' => 'dashboard', 'skip_rate' => true]);
    }
    if ($proof) {
        MemberFiles::storeProof($id, $msFile($okPng, 'receipt.png'), ['source' => 'dashboard', 'skip_rate' => true]);
    }
    return $id;
};
$msRow = static fn(int $id): array => (array) Member::find($id);
$msMails = static function (string $email) use ($msStart): array {
    $out = [];
    foreach (glob(STORAGE_PATH . '/mail/*.eml') ?: [] as $f) {
        if (filemtime($f) < $msStart) {
            continue;
        }
        $raw = (string) file_get_contents($f);
        if (!preg_match('/^To: .*' . preg_quote($email, '/') . '/mi', $raw)) {
            continue;
        }
        $body = preg_match('/Content-Transfer-Encoding: base64\r?\n\r?\n(.*?)(?:\r?\n--|\z)/s', $raw, $m) ? (string) base64_decode(preg_replace('/\s+/', '', $m[1])) : $raw;
        $out[] = ['subject' => preg_match('/^Subject: (.*)\r?$/mi', $raw, $s) ? trim($s[1]) : '', 'pdf' => str_contains($raw, 'application/pdf'), 'time' => filemtime($f), 'body' => $body];
    }
    usort($out, static fn(array $a, array $b): int => $a['time'] <=> $b['time']);
    return $out;
};
$msCards = static fn(string $email): int => count(array_filter($msMails($email), static fn(array $m): bool => $m['pdf']));

/** A browser-like client with its own cookie jar. Redirects are followed by hand with GET (like a browser after a POST). */
$msClient = static function () use ($msBase, &$msTmp): callable {
    $jar = tempnam(sys_get_temp_dir(), 'msj') ?: '';
    $msTmp[] = $jar;
    $one = static function (string $method, string $url, array $o) use ($jar): array {
        $ch = curl_init($url);
        $opts = [CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true, CURLOPT_TIMEOUT => 60, CURLOPT_COOKIEJAR => $jar, CURLOPT_COOKIEFILE => $jar, CURLOPT_FOLLOWLOCATION => false, CURLOPT_CUSTOMREQUEST => $method];
        if (isset($o['form'])) {
            $opts[CURLOPT_POSTFIELDS] = http_build_query($o['form']);
        } elseif (isset($o['multipart'])) {
            $opts[CURLOPT_POSTFIELDS] = $o['multipart'];
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
        return [$status, substr($raw, $hs), $h];
    };
    return static function (string $method, string $path, array $o = []) use ($msBase, $one): array {
        $r = $one($method, $msBase . $path, $o);
        for ($i = 0; $i < 5 && !empty($o['follow']) && $r[0] >= 300 && $r[0] < 400 && !empty($r[2]['location']); $i++) {
            $loc = $r[2]['location'];
            $r = $one('GET', preg_match('~^https?://~i', $loc) ? $loc : preg_replace('~^(https?://[^/]+).*$~', '$1', $msBase) . $loc, []);
        }
        return $r;
    };
};
$msAttr = static fn(string $html, string $re): string => preg_match($re, $html, $m) === 1 ? html_entity_decode($m[1]) : '';
$msGet = static function (callable $c, string $path) use ($msAttr): array {
    $r = $c('GET', $path, ['follow' => true]);
    $r[3] = $msAttr($r[1], '/name="_token" value="([^"]+)"/') ?: $msAttr($r[1], '/name="csrf-token" content="([^"]+)"/');
    return $r;
};
$msPost = static fn(callable $c, string $path, string $token, array $fields = []): array => $c('POST', $path, ['form' => ['_token' => $token] + $fields, 'follow' => true]);
$msFlash = static function (string $html): string {
    $out = [];
    if (preg_match_all('/class="alert__body">(.*?)<\/div>/s', $html, $m)) {
        foreach ($m[1] as $x) {
            $out[] = trim(html_entity_decode(strip_tags($x)));
        }
    }
    return implode(' | ', $out);
};

// ---------------------------------------------------------------------------------------------- the staff
$msPw = static fn(): string => 'Zz' . bin2hex(random_bytes(8)) . '9q';
Database::execute("INSERT INTO roles (slug, name, description, abilities, is_system) VALUES ('zz-ms-manage', 'ZZ MS manage', '', ?, 0), ('zz-ms-card', 'ZZ MS card', '', ?, 0), ('zz-ms-delete', 'ZZ MS delete', '', ?, 0)",
    [json_encode(['dashboard.view', 'members.manage']), json_encode(['dashboard.view', 'members.card']), json_encode(['dashboard.view', 'members.delete'])]);
Roles::flush();
$msCreds = [];
foreach (['admin' => 'admin', 'editor' => 'editor', 'manage' => 'zz-ms-manage', 'card' => 'zz-ms-card', 'delete' => 'zz-ms-delete'] as $key => $role) {
    $msCreds[$key] = ['email' => 'zz-ms-' . $key . '@example.test', 'password' => $msPw()];
    User::create(['name' => 'ZZ MS ' . ucfirst($key), 'email' => $msCreds[$key]['email'], 'password_hash' => Auth::hashPassword($msCreds[$key]['password']), 'role' => $role, 'is_active' => 1, 'must_change_password' => 0]);
}
$msLogin = static function (string $who) use ($msClient, $msCreds, $msAttr): ?callable {
    foreach (['127.0.0.1', '::1'] as $ip) {
        \App\Core\RateLimit::clear('throttle:' . $ip . ':POST:/admin/login');
        \App\Core\RateLimit::clear('throttle:' . $ip . ':POST:/admin/members/card-bulk');           // 10 per 10 minutes: every role is tested
    }
    $c = $msClient();
    [, $html] = $c('GET', '/admin/login');
    [$st, , $h] = $c('POST', '/admin/login', ['form' => ['_token' => $msAttr($html, '/name="_token" value="([^"]+)"/'), 'email' => $msCreds[$who]['email'], 'password' => $msCreds[$who]['password']]]);
    return $st >= 300 && $st < 400 && str_contains((string) ($h['location'] ?? ''), 'admin') ? $c : null;
};
$admin = $msLogin('admin');
check('the throwaway administrator signs in', $admin !== null);
if ($admin === null) {
    return;
}
$adminId = (int) Database::value('SELECT id FROM users WHERE email = ?', [$msCreds['admin']['email']], 0);
$tokenOf = static fn(callable $c, int $id): string => $msGet($c, '/admin/members/' . $id)[3];
$approve = static fn(callable $c, int $id): array => $msPost($c, '/admin/members/' . $id . '/status', $tokenOf($c, $id), ['st' => ['to' => 'approved', 'notify' => '1']]);
$pay = static function (callable $c, int $id, array $pay, array $extra = []) use ($msGet, $msAttr): array {
    [, $html, , $tok] = $msGet($c, '/admin/members/' . $id);
    $fields = ['_token' => $tok, 'pay[_once]' => $msAttr($html, '/name="pay\[_once\]" value="([^"]+)"/')];
    foreach ($pay as $k => $v) {
        $fields['pay[' . $k . ']'] = $v;
    }
    return $c('POST', '/admin/members/' . $id . '/payments', ['multipart' => $fields + $extra, 'follow' => true]);
};
$received = ['amount' => '50000', 'status' => 'received', 'purpose' => 'membership', 'method' => 'mobile_money'];

// ================================================================================================ A. default rule: approved AND the balance cleared
$a = $msMember('a', $typeA);
$emailA = 'a' . $msDomain;
[, $html] = $approve($admin, $a);
$m = $msRow($a);
check('approve while unpaid: approved, no card yet, the screen says the card follows the payment',
    $m['status'] === 'approved' && $m['card_status'] === 'none' && $msCards($emailA) === 0 && str_contains($msFlash($html), 'as soon as the balance'), $m['status'] . '/' . $m['card_status'] . ' ' . $msFlash($html));
[, $html] = $pay($admin, $a, $received + ['attach_proof' => '1']);
$m = $msRow($a);
$pays = Database::all('SELECT * FROM member_payments WHERE member_id = ?', [$a]);
check('payment with the proof attached: card v1 issued and e-mailed ONCE (PDF attached), and the screen says so',
    $m['card_status'] === 'issued' && (int) $m['card_version'] === 1 && $msCards($emailA) === 1 && str_contains($msFlash($html), 'Membership card version 1 was issued and e-mailed'), $msFlash($html));
check('the payment row holds a PRIVATE COPY of the proof; the member proof is verified with who and when',
    count($pays) === 1 && str_starts_with((string) $pays[0]['proof_path'], 'members/proofs/') && $pays[0]['proof_path'] !== $m['proof_path']
    && $m['proof_status'] === 'verified' && (int) $m['proof_verified_by'] === $adminId && !empty($m['proof_verified_at']));
[$st, , $h] = $admin('GET', '/admin/members/' . $a . '/payments/' . $pays[0]['id'] . '/proof');
check('the proof streams to staff (image, no-store); a payment id that is not this member\'s answers 404',
    $st === 200 && str_starts_with($h['content-type'] ?? '', 'image/') && str_contains($h['cache-control'] ?? '', 'no-store') && $admin('GET', '/admin/members/' . $a . '/payments/999999/proof')[0] === 404);
$pay($admin, $a, ['amount' => '1000', 'status' => 'received', 'purpose' => 'other', 'method' => 'mobile_money']);
[$st, $html] = $msPost($admin, '/admin/members/' . $a . '/card/issue', $tokenOf($admin, $a));
check('a second payment and "Issue card now" on a member who has a card send nothing more (idempotent)', (int) $msRow($a)['card_version'] === 1 && $msCards($emailA) === 1 && str_contains($msFlash($html), 'already'), $msFlash($html));
$msPost($admin, '/admin/members/' . $a . '/card/resend', $tokenOf($admin, $a));
check('"Re-send card e-mail" mails the same version again', $msCards($emailA) === 2 && (int) $msRow($a)['card_version'] === 1);
[$st, $body, $h] = $admin('GET', '/admin/members/' . $a . '/card.pdf');
[$stI, , $hI] = $admin('GET', '/admin/members/' . $a . '/card.pdf?inline=1');
check('card.pdf: a download by default, inline (framable by this site only) with ?inline=1', $st === 200 && str_starts_with($body, '%PDF') && str_starts_with($h['content-disposition'] ?? '', 'attachment')
    && $stI === 200 && str_starts_with($hI['content-disposition'] ?? '', 'inline') && ($hI['x-frame-options'] ?? '') === 'SAMEORIGIN');
$tok1 = (string) $msRow($a)['card_verify_token'];
$visitor = $msClient();

// a replaced photo after issue only FLAGS the card
$r = $admin('POST', '/admin/members/' . $a . '/photo', ['multipart' => ['_token' => $tokenOf($admin, $a), 'photo' => $msCurl($okPng, 'again.png', 'image/png')], 'follow' => true]);
$m = $msRow($a);
check('staff replace the photo after the card was issued: the card is untouched but flagged (photo_staff), the list and the screen say so',
    (int) $m['card_version'] === 1 && (int) $m['card_needs_reissue'] === 1 && $m['card_reissue_reason'] === 'photo_staff' && $m['card_verify_token'] === $tok1
    && str_contains($msGet($admin, '/admin/members?card=reissue')[1], 'MS Person a') && str_contains($msGet($admin, '/admin/members/' . $a)[1], 'out of date'));
$msPost($admin, '/admin/members/' . $a . '/card/reissue', $tokenOf($admin, $a));
$m = $msRow($a);
$old = $visitor('GET', '/membership/verify/' . $tok1)[0];
$new = $visitor('GET', '/membership/verify/' . $m['card_verify_token'])[0];
check('"Re-issue (new version)": version 2, a NEW verification token (the old QR link dies), flag cleared, e-mailed',
    (int) $m['card_version'] === 2 && $m['card_verify_token'] !== $tok1 && (int) $m['card_needs_reissue'] === 0 && $msCards($emailA) === 3 && $old === 404 && $new === 200, $old . '/' . $new);
$msPost($admin, '/admin/members/' . $a . '/card/revoke', $tokenOf($admin, $a), ['reason' => 'lost']);
$v = $visitor('GET', '/membership/verify/' . $m['card_verify_token']);
check('revoke: the QR code says NOT VALID (404), no PDF to download, listed under card=revoked',
    $msRow($a)['card_status'] === 'revoked' && $v[0] === 404 && str_contains($v[1], 'NOT VALID') && $admin('GET', '/admin/members/' . $a . '/card.pdf')[0] === 404 && str_contains($msGet($admin, '/admin/members?card=revoked')[1], 'MS Person a'));
$msPost($admin, '/admin/members/' . $a . '/card/reissue', $tokenOf($admin, $a));
$m = $msRow($a);
check('a new card after a revoke: issued again (version 3) and e-mailed', $m['card_status'] === 'issued' && (int) $m['card_version'] === 3 && $msCards($emailA) === 4);

// ================================================================================================ B / C. the other two trigger settings
Settings::set('membership_card_trigger', 'approved');
$b = $msMember('b', $typeA, true, false);
[, $html] = $approve($admin, $b);
check('trigger "approved": the card is issued and e-mailed at approval even when unpaid; the payment later sends no second card', $msRow($b)['card_status'] === 'issued' && $msCards('b' . $msDomain) === 1
    && str_contains($msFlash($html), 'Membership card version 1 was issued and e-mailed'), $msFlash($html));
$pay($admin, $b, $received);
check('...and the payment afterwards changes nothing', $msCards('b' . $msDomain) === 1 && (int) $msRow($b)['card_version'] === 1);
Settings::set('membership_card_trigger', 'manual');
$cc = $msMember('c', $typeA, false, false);
$approve($admin, $cc);
$pay($admin, $cc, $received);
check('trigger "manual": approved and paid, no card; the member screen offers "Issue card now"', $msRow($cc)['card_status'] === 'none' && $msCards('c' . $msDomain) === 0 && str_contains($msGet($admin, '/admin/members/' . $cc)[1], 'Issue card now'));
check('the list filter "approved, card not issued" shows it with "Issue it by hand"', str_contains($msGet($admin, '/admin/members?status=approved&card=none')[1], 'Issue it by hand'));
$msPost($admin, '/admin/members/' . $cc . '/card/issue', $tokenOf($admin, $cc));
check('"Issue card now" issues a card without a photo (the initials block) and e-mails it once', $msRow($cc)['card_status'] === 'issued' && $msCards('c' . $msDomain) === 1);
Settings::set('membership_card_trigger', 'paid_approved');

// ================================================================================================ D / E / F. paid first, waived, free, renewal
$d = $msMember('d', $typeA, true, true);
$pay($admin, $d, $received + ['attach_proof' => '1']);
$before = $msRow($d)['card_status'];
$approve($admin, $d);
check('paid while pending, approved later: the card is issued by the APPROVAL (whichever comes last)', $before === 'none' && $msRow($d)['card_status'] === 'issued' && $msCards('d' . $msDomain) === 1, $before . '/' . $msRow($d)['card_status'] . ' mails ' . $msCards('d' . $msDomain) . ' ' . $msFlash($msGet($admin, '/admin/members/' . $d)[1]));
$e = $msMember('e', $typeA, false, false);
$approve($admin, $e);
$pay($admin, $e, ['status' => 'waived'] + $received);
check('a waived balance counts as cleared: card issued once', $msRow($e)['card_status'] === 'issued' && $msCards('e' . $msDomain) === 1, $msRow($e)['card_status'] . ' mails ' . $msCards('e' . $msDomain));
$f = $msMember('f', $typeF, false, false);
$approve($admin, $f);
check('a free (lifetime) type is cleared at once: card at approval, "No expiry"', $msRow($f)['card_status'] === 'issued' && $msCards('f' . $msDomain) === 1 && empty($msRow($f)['expiry_date']), $msRow($f)['card_status'] . ' mails ' . $msCards('f' . $msDomain) . ' exp ' . $msRow($f)['expiry_date'] . ' st ' . $msRow($f)['status']);
$until = $msRow($a)['card_valid_until'];
[, $html, , $tok] = $msGet($admin, '/admin/members/' . $a);
$msPost($admin, '/admin/members/' . $a . '/renew', $tok, ['rn' => ['_once' => $msAttr($html, '/name="rn\[_once\]" value="([^"]+)"/')]]);
check('renewing adds a charge: no new version while the renewal is unpaid', (int) $msRow($a)['card_version'] === 3 && $msRow($a)['card_valid_until'] === $until);
$pay($admin, $a, $received);
$m = $msRow($a);
check('paying the renewal re-issues the card (version 4) with the new valid-until date and e-mails it', (int) $m['card_version'] === 4 && $m['card_valid_until'] > $until && $msCards($emailA) === 5, json_encode([$m['card_version'], $m['card_valid_until'], $msCards($emailA)]));
// the renewal re-issue can be switched off: the card is then only FLAGGED ("update due") and staff re-issue it by hand
Settings::set('membership_card_reissue_renewal', '0');
$until4 = $msRow($a)['card_valid_until'];
[, $html, , $tok] = $msGet($admin, '/admin/members/' . $a);
$msPost($admin, '/admin/members/' . $a . '/renew', $tok, ['rn' => ['_once' => $msAttr($html, '/name="rn\[_once\]" value="([^"]+)"/')]]);
$pay($admin, $a, $received);
$m = $msRow($a);
check('setting "re-issue on renewal" OFF: a paid renewal does not touch the card (still version 4), the list and the screen say "update due"',
    (int) $m['card_version'] === 4 && $m['card_valid_until'] === $until4 && $m['expiry_date'] > $m['card_valid_until'] && str_contains($msGet($admin, '/admin/members?card=reissue')[1], 'MS Person a') && str_contains($msGet($admin, '/admin/members/' . $a)[1], 'out of date'));
$msPost($admin, '/admin/members/' . $a . '/card/reissue', $tokenOf($admin, $a));
$m = $msRow($a);
check('...and "Re-issue (new version)" brings it up to date (version 5, the new valid-until date)', (int) $m['card_version'] === 5 && $m['card_valid_until'] === $m['expiry_date'] && $msCards($emailA) === 6);
Settings::set('membership_card_reissue_renewal', '1');

// ================================================================================================ G. proof status
$g = $msMember('g', $typeA);
$emailG = 'g' . $msDomain;
check('"needs a check" lists the member and the sidebar counter equals the number of rows', str_contains($msGet($admin, '/admin/members?proof=received')[1], 'MS Person g'));
[, $html] = $msGet($admin, '/admin/members?proof=received');
$rows = (int) (preg_match('/Showing \d+-\d+ of (\d+)/', $html, $mm) ? $mm[1] : 0);
check('...the sidebar badge (proofCheckCount) equals the rows of ?proof=received (' . $rows . ')', MS::proofCheckCount() === $rows, MS::proofCheckCount() . ' vs ' . $rows);
$mailsBefore = count($msMails($emailG));
$msPost($admin, '/admin/members/' . $g . '/proof/status', $tokenOf($admin, $g), ['status' => 'rejected']);
$m = $msRow($g);
$mails = $msMails($emailG);
check('"needs a better copy": rejected, who and when stored, the applicant is e-mailed once with the private page link (no attachment)',
    $m['proof_status'] === 'rejected' && (int) $m['proof_verified_by'] === $adminId && count($mails) === $mailsBefore + 1 && str_contains(end($mails)['body'], '/membership/me/') && !end($mails)['pdf']);
$msPost($admin, '/admin/members/' . $g . '/proof/status', $tokenOf($admin, $g), ['status' => 'received']);
[, $html] = $msPost($admin, '/admin/members/' . $g . '/proof/status', $tokenOf($admin, $g), ['status' => 'rejected']);
check('a second request within 6 hours does not e-mail again and says so', count($msMails($emailG)) === $mailsBefore + 1 && str_contains($msFlash($html), 'less than 6 hours'), $msFlash($html));
$msPost($admin, '/admin/members/' . $g . '/proof/status', $tokenOf($admin, $g), ['status' => 'verified']);
$msPost($admin, '/admin/members/' . $g . '/proof/status', $tokenOf($admin, $g), ['status' => 'bogus']);
check('verified; an unknown status is refused; the member left the needs-check queue', $msRow($g)['proof_status'] === 'verified' && !str_contains($msGet($admin, '/admin/members?proof=received')[1], 'MS Person g'));

// ================================================================================================ H. staff uploads and validation
$tok = $tokenOf($admin, $g);
$admin('POST', '/admin/members/' . $g . '/proof', ['multipart' => ['_token' => $tok, 'proof' => $msCurl($okPng, 'again.png', 'image/png')], 'follow' => true]);
check('staff replace the proof: status back to "needs a check"', $msRow($g)['proof_status'] === 'received');
$r = $admin('POST', '/admin/members/' . $g . '/proof', ['multipart' => ['_token' => $tokenOf($admin, $g), 'proof' => $msCurl("<?php echo 'x';", 'evil.png', 'image/png')], 'follow' => true]);
check('a script renamed .png is refused with a clear message', str_contains($msFlash($r[1]), 'JPG') || str_contains($msFlash($r[1]), 'could not read'), $msFlash($r[1]));
$r = $admin('POST', '/admin/members/' . $g . '/photo', ['multipart' => ['_token' => $tokenOf($admin, $g), 'photo' => $msCurl($tinyPng, 'tiny.png', 'image/png')], 'follow' => true]);
check('a 200 x 200 photo is refused (too small)', str_contains($msFlash($r[1]), 'too small'), $msFlash($r[1]));
$r = $admin('POST', '/admin/members/' . $g . '/photo', ['multipart' => ['_token' => $tokenOf($admin, $g)], 'follow' => true]);
check('no file chosen: "please choose a photo"', str_contains($msFlash($r[1]), 'choose'), $msFlash($r[1]));
$photoBefore = $msRow($g)['photo_path'];
$r = $admin('POST', '/admin/members/' . $g . '/photo', ['multipart' => ['photo' => $msCurl($okPng, 'x.png', 'image/png')], 'follow' => false]);
check('a photo POST without the CSRF token is refused (4xx) and nothing changes', $r[0] >= 400 && $msRow($g)['photo_path'] === $photoBefore, (string) $r[0]);
$admin('POST', '/admin/members/' . $g . '/photo', ['multipart' => ['_token' => $tokenOf($admin, $g), 'photo' => $msCurl($msPng(340, 340), 'new.png', 'image/png')], 'follow' => true]);
clearstatcache(true);
$m = $msRow($g);
check('replacing the photo works without a consent tick (staff); the old file is deleted', $m['photo_path'] !== $photoBefore && !is_file(STORAGE_PATH . '/private/' . $photoBefore) && is_file(STORAGE_PATH . '/private/' . $m['photo_path']));
$msPost($admin, '/admin/members/' . $g . '/photo/remove', $tokenOf($admin, $g));
clearstatcache(true);
check('remove photo: the column is cleared and the file is gone', empty($msRow($g)['photo_path']) && !is_file(STORAGE_PATH . '/private/' . $m['photo_path']));

// ================================================================================================ I. proof on payments
$i = $msMember('i', $typeA);
[, $html, , $tok] = $msGet($admin, '/admin/members/' . $i);
$r = $admin('POST', '/admin/members/' . $i . '/payments', ['multipart' => ['_token' => $tok, 'pay[_once]' => $msAttr($html, '/name="pay\[_once\]" value="([^"]+)"/'), 'pay[amount]' => '20000', 'pay[status]' => 'received', 'pay[purpose]' => 'membership', 'pay[method]' => 'mobile_money',
    'pay_proof' => $msCurl('not a real picture at all', 'bad.png', 'image/png')], 'follow' => true]);
check('a bad proof file blocks the payment BEFORE anything is saved (no payment row; the error shows under the file field)', (int) Database::value('SELECT COUNT(*) FROM member_payments WHERE member_id = ?', [$i], 0) === 0 && str_contains($r[1], 'pay-proof-file-error'));
$pay($admin, $i, ['amount' => '20000', 'status' => 'pending'] + $received, ['pay[attach_proof]' => '1']);
$p = Database::one('SELECT * FROM member_payments WHERE member_id = ?', [$i]);
check('a proof attached to a PENDING payment keeps a copy but does not mark the member proof verified', $p !== null && !empty($p['proof_path']) && $msRow($i)['proof_status'] === 'received');
$pid = (int) $p['id'];
$admin('POST', '/admin/members/' . $i . '/payments/' . $pid . '/proof/remove', ['form' => ['_token' => $tokenOf($admin, $i)], 'follow' => true]);
check('removing the proof from the payment clears its columns (the member\'s own proof stays)', empty(Database::one('SELECT proof_path FROM member_payments WHERE id = ?', [$pid])['proof_path']) && $msRow($i)['proof_status'] === 'received');
$admin('POST', '/admin/members/' . $i . '/payments/' . $pid . '/proof', ['multipart' => ['_token' => $tokenOf($admin, $i), 'pay_proof' => $msCurl($okPng, 'late.png', 'image/png')], 'follow' => true]);
$p = Database::one('SELECT * FROM member_payments WHERE id = ?', [$pid]);
check('a file attached to an existing payment is stored privately', str_starts_with((string) $p['proof_path'], 'members/proofs/') && ($p['proof_mime'] ?? '') === 'image/png');
$other = $msMember('i2', $typeA);
$r = $admin('POST', '/admin/members/' . $other . '/payments/' . $pid . '/proof', ['multipart' => ['_token' => $tokenOf($admin, $other)], 'follow' => false]);
check('a payment of ANOTHER member cannot be touched through this member\'s URL (404)', $r[0] === 404, (string) $r[0]);
$ro = $msMember('i3', $typeA);
Database::update('members', ['proof_status' => 'rejected'], 'id = ?', [$ro]);
$admin('POST', '/admin/members/' . $i . '/payments/' . $pid . '/proof/remove', ['form' => ['_token' => $tokenOf($admin, $i)], 'follow' => true]);
[, $html] = $pay($admin, $ro, $received, ['pay[attach_proof]' => '1']);
check('a proof marked "needs a better copy" is never attached or verified by a posted tick', empty(Database::one('SELECT proof_path FROM member_payments WHERE member_id = ?', [$ro])['proof_path'] ?? null) && $msRow($ro)['proof_status'] === 'rejected');

// ================================================================================================ J. e-mail change
$j = $msMember('j', $typeA, false, false);
$tokBefore = MS::dashboardToken($j);
$mrow = $msRow($j);
$form = ['_token' => $msGet($admin, '/admin/members/' . $j . '?edit=1')[3], 'full_name' => $mrow['full_name'], 'gender' => $mrow['gender'], 'date_of_birth' => $mrow['date_of_birth'], 'nationality' => $mrow['nationality'], 'email' => 'j-new' . $msDomain,
    'phone' => $mrow['phone'], 'country' => $mrow['country'], 'region' => $mrow['region'], 'city' => $mrow['city'], 'occupation_status' => $mrow['occupation_status'], 'education_level' => $mrow['education_level'], 'availability' => $mrow['availability'],
    'heard_about' => $mrow['heard_about'], 'motivation' => $mrow['motivation'], 'emergency_name' => $mrow['emergency_name'], 'emergency_phone' => $mrow['emergency_phone'], 'emergency_relationship' => $mrow['emergency_relationship'], 'payment_method' => $mrow['payment_method']];
$r = $admin('POST', '/admin/members/' . $j, ['form' => $form, 'follow' => true]);
check('changing the e-mail address resets the private page link (the old link is dead) and tells staff to send the new one',
    $msRow($j)['email'] === 'j-new' . $msDomain && MS::dashboardToken($j) !== $tokBefore && MS::memberByDashboardToken($tokBefore) === null && str_contains($msFlash($r[1]), 'no longer works'), $msFlash($r[1]));
$tokAfter = MS::dashboardToken($j);
$admin('POST', '/admin/members/' . $j, ['form' => ['_token' => $tokenOf($admin, $j), 'full_name' => 'MS Person j Renamed'] + $form, 'follow' => true]);
check('a change that is not the e-mail keeps the link', MS::dashboardToken($j) === $tokAfter);

// ================================================================================================ K. bulk
Settings::set('membership_card_trigger', 'paid_approved');
$k1 = $msMember('k1', $typeA, true, false);
$k2 = $msMember('k2', $typeA, true, false);
$k3 = $msMember('k3', $typeA, true, false);
$msPost($admin, '/admin/members/bulk', $msGet($admin, '/admin/members')[3], ['action' => 'approve', 'notify' => '1', 'ids' => [$k1, $k2]]);
$pay($admin, $k1, $received);                                    // k1: approved and paid (the card was issued by the payment); k2: approved, unpaid; k3: pending
Settings::set('membership_card_trigger', 'paid_approved');
check('bulk approve + a payment: k1 got its card from the payment, k2 (unpaid) and k3 (pending) have none', $msRow($k1)['card_status'] === 'issued' && $msRow($k2)['card_status'] === 'none' && $msRow($k3)['card_status'] === 'none');
$r = $msPost($admin, '/admin/members/bulk', $msGet($admin, '/admin/members')[3], ['action' => 'card', 'ids' => [$k1, $k2, $k3, 999999]]);
$fl = $msFlash($r[1]);
check('bulk "Issue membership cards": NORMAL rules (k1 already has one; k2 unpaid and k3 pending are skipped with their reasons; an unknown id is skipped), grouped, nothing issued twice',
    $msRow($k2)['card_status'] === 'none' && $msRow($k3)['card_status'] === 'none' && $msCards('k1' . $msDomain) === 1 && str_contains($fl, 'already') && str_contains($fl, 'Waiting for the payment') && str_contains($fl, 'Waiting for the application to be approved'), $fl);
$pay($admin, $k2, $received);
check('...k2 is issued by its own payment (not twice by the earlier bulk)', $msRow($k2)['card_status'] === 'issued' && $msCards('k2' . $msDomain) === 1);
Settings::set('membership_card_trigger', 'manual');
$k4 = $msMember('k4', $typeA, true, false);
$msPost($admin, '/admin/members/bulk', $msGet($admin, '/admin/members')[3], ['action' => 'approve', 'ids' => [$k4]]);
$r = $msPost($admin, '/admin/members/bulk', $msGet($admin, '/admin/members')[3], ['action' => 'card', 'ids' => [$k4]]);
check('bulk issue under "manual": the staff choice issues it (e-mailed once); running it again changes nothing', $msRow($k4)['card_status'] === 'issued' && str_contains($msFlash($r[1]), '1 card issued') && $msCards('k4' . $msDomain) === 1
    && $msPost($admin, '/admin/members/card-bulk', $msGet($admin, '/admin/members')[3], ['ids' => [$k4]])[0] === 200 && $msCards('k4' . $msDomain) === 1);
Settings::set('membership_card_trigger', 'paid_approved');
$r = $msPost($admin, '/admin/members/bulk', $msGet($admin, '/admin/members')[3], ['action' => 'card']);
check('bulk issue with nothing ticked is refused politely', str_contains($msFlash($r[1]), 'Tick at least one'), $msFlash($r[1]));
foreach (['proof=bogus', 'card=%27%20OR%201=1', 'photo=none', 'photo=yes', 'proof=none', 'proof=verified', 'proof=rejected', 'card=issued', 'card=none'] as $q) {
    [$st] = $msGet($admin, '/admin/members?' . $q);
    if ($st !== 200) {
        check('list filter ?' . $q . ' renders (200)', false, (string) $st);
    }
}

// ================================================================================================ L. abilities
$tgt = $msMember('l', $typeA, false, true);
$tgtRef = (string) $msRow($tgt)['application_ref'];
foreach (['admin' => [200, 200], 'editor' => [200, 403], 'manage' => [200, 403], 'card' => [403, 200], 'delete' => [403, 403]] as $who => [$screen, $designer]) {
    $cl = $msLogin($who);
    if ($cl === null) {
        check("$who signs in", false);
        continue;
    }
    $ok = $screen === 200;
    $got = [$cl('GET', '/admin/members/' . $tgt)[0], $cl('GET', '/admin/members')[0], $cl('GET', '/admin/membership-card')[0], $cl('GET', '/admin/members/' . $tgt . '/photo')[0], $cl('GET', '/admin/members/' . $tgt . '/card.pdf')[0]];
    check("$who: member screen " . $screen . ', list ' . $screen . ', card designer ' . $designer . ($ok ? ', photo 404 (none), card.pdf 404 (none)' : ', photo 403, card.pdf 403'),
        $got === ($ok ? [200, 200, $designer, 404, 404] : [403, 403, $designer, 403, 403]), json_encode($got));
    $csrf = $ok ? $msGet($cl, '/admin/members/' . $tgt)[3] : $msGet($cl, '/admin/membership-card')[3];
    $acts = [];
    foreach (['card/issue', 'card/resend', 'card/reissue', 'card/revoke', 'proof/status', 'photo/remove', 'proof/remove'] as $act) {
        $acts[$act] = $cl('POST', '/admin/members/' . $tgt . '/' . $act, ['form' => ['_token' => $csrf, 'status' => 'verified']])[0];
    }
    $acts['bulk'] = $cl('POST', '/admin/members/card-bulk', ['form' => ['_token' => $csrf, 'ids' => [$tgt]]])[0];
    $acts['delete'] = $cl('POST', '/admin/members/' . $tgt . '/delete', ['form' => ['_token' => $csrf, 'confirm_ref' => 'wrong']])[0];
    $acts['erase'] = $cl('POST', '/admin/members/' . $tgt . '/anonymise', ['form' => ['_token' => $csrf, 'confirm_ref' => 'wrong']])[0];
    $redirect = static fn(int $s): bool => $s >= 300 && $s < 400;
    $want = [];
    foreach ($acts as $act => $s) {
        $want[$act] = in_array($act, ['delete', 'erase'], true) ? ($who === 'admin' ? $redirect($s) : $s === 403) : ($ok ? $redirect($s) : $s === 403);
    }
    check("$who: card / proof / photo actions " . ($ok ? 'are allowed (members.manage)' : 'are refused (403)') . '; deleting and erasing ' . ($who === 'admin' ? 'need members.delete (the administrator has it)' : 'are refused (403)'),
        !in_array(false, $want, true), json_encode($acts));
}
check('anonymous visitors are sent to the login: no proof stream, no card.pdf', $msClient()('GET', '/admin/members/' . $tgt . '/proof')[0] >= 300 && !str_starts_with($msClient()('GET', '/admin/members/' . $tgt . '/card.pdf')[1], '%PDF'));

// ================================================================================================ M. erasure
Settings::set('membership_card_trigger', 'approved');
$mm = $msMember('m', $typeA);
MS::changeStatus($mm, 'approved');
Settings::set('membership_card_trigger', 'paid_approved');
$bef = $msRow($mm);
$files = [STORAGE_PATH . '/private/' . $bef['photo_path'], STORAGE_PATH . '/private/' . $bef['proof_path'], STORAGE_PATH . '/private/' . $bef['card_photo_path']];
$verifyTok = (string) $bef['card_verify_token'];
$dashTok = MS::dashboardToken($mm);
check('before the erasure: a card is issued and the photo, the proof and the card\'s photo copy exist; erasing is not offered while approved',
    $bef['card_status'] === 'issued' && array_filter($files, 'is_file') === $files && str_contains($msGet($admin, '/admin/members/' . $mm)[1], 'Cancel the membership first'));
MS::changeStatus($mm, 'cancelled', ['reason' => 'test']);
[, $html, , $tok] = $msGet($admin, '/admin/members/' . $mm);
check('the erase panel names the photo, the proof, the card and the links', str_contains($html, 'photo, the proof of payment') && str_contains($html, 'QR code stop working'));
$msPost($admin, '/admin/members/' . $mm . '/anonymise', $tok, ['confirm_ref' => $bef['application_ref']]);
clearstatcache(true);
$aft = $msRow($mm);
check('erasure deletes the photo, the proof and the card photo copy; clears the columns, BOTH tokens and the snapshot; revokes the card',
    array_filter($files, 'is_file') === [] && empty($aft['photo_path']) && empty($aft['proof_path']) && empty($aft['dashboard_token']) && empty($aft['card_verify_token']) && empty($aft['card_snapshot']) && $aft['card_status'] === 'revoked');
check('the old QR link and the old private page link are dead (404); the erased screen says so and offers no upload',
    $msClient()('GET', '/membership/verify/' . $verifyTok)[0] === 404 && $msClient()('GET', '/membership/me/' . $dashTok)[0] === 404
    && str_contains($msGet($admin, '/admin/members/' . $mm)[1], 'erased, together with the photo') && !str_contains($msGet($admin, '/admin/members/' . $mm)[1], 'name="photo"'));

// ================================================================================================ N. audit log
$rows = Database::all("SELECT summary, details FROM audit_log WHERE entity_type = 'member' AND (action LIKE 'member.card%' OR action LIKE 'member.photo%' OR action LIKE 'member.proof%' OR action LIKE 'member.payment_proof%' OR action = 'member.link_reset' OR action = 'member.bulk')");
$leak = array_filter($rows, static fn(array $r): bool => preg_match('/MS Person|msstaff\.invalid|\.jpg|\.png|members\/photos|members\/proofs|dashboard_token|"token"/i', ($r['summary'] ?? '') . ' ' . ($r['details'] ?? '')) === 1);
check('the card / photo / proof audit entries (' . count($rows) . ') carry ids, references and counts only: no names, e-mails, file names, paths or tokens', count($rows) > 10 && $leak === [], (string) count($leak));

$msCleanup();
$msTmp = [];
check('clean-up: no test member (also none that was erased), type, user or role is left behind', (int) Database::value("SELECT COUNT(*) FROM members WHERE email LIKE ? OR membership_number LIKE 'LAF-ZQ-%' OR membership_number LIKE 'LAF-ZR-%'", ['%' . $msDomain], 0) === 0
    && (int) Database::value("SELECT COUNT(*) FROM membership_types WHERE slug LIKE 'zz-ms-%'", [], 0) === 0 && (int) Database::value("SELECT COUNT(*) FROM users WHERE email LIKE 'zz-ms-%'", [], 0) === 0
    && (int) Database::value("SELECT COUNT(*) FROM roles WHERE slug LIKE 'zz-ms-%'", [], 0) === 0);

if (!empty($msStandalone)) {
    echo $fail === 0 ? "ALL CHECKS PASSED\n" : "{$fail} CHECK(S) FAILED\n";
    exit($fail === 0 ? 0 : 1);
}
