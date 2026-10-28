<?php
declare(strict_types=1);

/**
 * Exports in every format: CSV (data), Excel, Word, PDF with the standard letterhead. Included by bin/selftest.php or run alone:
 *   /c/xampp/php/php.exe bin/selftest-exports.php
 * Covers the writers (ZIP container, Excel, Word, PDF), the format choice on real admin export addresses, the Export menu and that a CSV meant for importing stays CSV.
 * Removes its own rows (zz-exp-*).
 */

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

use App\Core\Auth;
use App\Core\Database;
use App\Core\ZipWriter;
use App\Models\User;
use App\Services\Export\DocxExport;
use App\Services\Export\ExportDocument;
use App\Services\Export\Exporter;
use App\Services\Export\Letterhead;
use App\Services\Export\PdfExport;
use App\Services\Export\XlsxExport;

/** Read a ZIP made by ZipWriter without ZipArchive: name => bytes, checking each CRC. @return array<string,string>|null */
$unzip = static function (string $zip): ?array {
    $eocd = strrpos($zip, "PK\x05\x06");
    if ($eocd === false) {
        return null;
    }
    $e = unpack('vdisk/vcd/ventries/vtotal/Vsize/Voffset/vcomment', substr($zip, $eocd + 4, 18));
    $out = [];
    $p = $e['offset'];
    for ($i = 0; $i < $e['entries']; $i++) {
        $h = unpack('Vsig/vmade/vneed/vflags/vmethod/vtime/vdate/Vcrc/Vcsize/Vsize/vnl/vel/vcl/vdisk/vint/Vext/Voff', substr($zip, $p, 46));
        if ($h['sig'] !== 0x02014b50) {
            return null;
        }
        $name = substr($zip, $p + 46, $h['nl']);
        $p += 46 + $h['nl'] + $h['el'] + $h['cl'];
        $l = unpack('Vsig/vneed/vflags/vmethod/vtime/vdate/Vcrc/Vcsize/Vsize/vnl/vel', substr($zip, $h['off'], 30));
        $raw = substr($zip, $h['off'] + 30 + $l['nl'] + $l['el'], $l['csize']);
        $data = $l['method'] === 8 ? gzinflate($raw) : $raw;
        if ($data === false || (crc32($data) & 0xFFFFFFFF) !== $h['crc'] || strlen($data) !== $h['size']) {
            return null;
        }
        $out[$name] = $data;
    }
    return $out;
};
$wellFormed = static function (string $xml): bool {
    $prev = libxml_use_internal_errors(true);
    $d = new DOMDocument();
    $ok = $d->loadXML($xml);
    libxml_clear_errors();
    libxml_use_internal_errors($prev);
    return $ok;
};

// ------------------------------------------------------------------------------------------------------------- the writers
$zw = new ZipWriter();
$zw->add('a/hello.txt', str_repeat('Hello, Lumina. ', 40));
$zw->add('b.bin', "\x00\x01\x02");
$zr = $unzip($zw->output());
check('zip: the pure-PHP ZIP writer makes a valid archive (names, deflate, CRC)', $zr !== null && ($zr['a/hello.txt'] ?? '') === str_repeat('Hello, Lumina. ', 40) && ($zr['b.bin'] ?? '') === "\x00\x01\x02");

$lh = Letterhead::get();
check('letterhead: organisation, address, e-mail, phones and the logo come from Settings', $lh['org'] !== '' && $lh['address'] !== '' && $lh['email'] !== '' && $lh['phones'] !== '' && $lh['logo'] !== null && $lh['logo']['ext'] === 'png');

$header = ['ID', 'Name', 'E-mail', 'Phone', 'Status', 'Message'];
$rows = [];
for ($i = 1; $i <= 120; $i++) {
    $rows[] = [(string) $i, 'Person ' . $i . ' & <b>Co</b>', 'p' . $i . '@example.test', $i % 2 ? '0772 000 ' . $i : '+256 779 712 990', 'Active', $i === 3 ? "=HYPERLINK(\"http://evil\")\nsecond line" : 'ok'];
}
$doc = new ExportDocument('Test export', $header, $rows, $lh, ['Filters: Status: active'], 'Exported by Tester on ' . ExportDocument::now(), false, count($rows));

$xlsx = XlsxExport::render($doc);
$xp = $unzip($xlsx);
$sheet = $xp['xl/worksheets/sheet1.xml'] ?? '';
check('excel: a valid .xlsx container with workbook, styles, sheet, drawing and the logo picture', $xp !== null && isset($xp['xl/workbook.xml'], $xp['xl/styles.xml'], $xp['xl/drawings/drawing1.xml'], $xp['xl/media/image1.png'], $xp['[Content_Types].xml']) && str_starts_with($xlsx, 'PK'));
check('excel: every part is well-formed XML', $xp !== null && array_reduce(array_keys($xp), static fn(bool $c, string $n): bool => $c && (str_ends_with($n, '.png') || $wellFormed($xp[$n])), true));
check('excel: letterhead rows (organisation, address, e-mail), title, header row and all 120 rows', str_contains($sheet, htmlspecialchars($lh['org'])) && str_contains($sheet, $lh['email'] === '' ? 'Email' : htmlspecialchars(explode(',', $lh['email'])[0])) && str_contains($sheet, 'Test export') && substr_count($sheet, '<row ') >= 120 + 10 && str_contains($sheet, '<autoFilter'));
check('excel: text stays text (a formula is not a formula, markup is escaped, phones keep their zeros) and plain numbers are numbers', !str_contains($sheet, '<f>') && str_contains($sheet, '&lt;b&gt;Co&lt;/b&gt;') && str_contains($sheet, '0772 000 1') && str_contains($sheet, '<c r="A12" s="5"><v>1</v></c>'));

$docx = DocxExport::render($doc);
$dp = $unzip($docx);
$body = $dp['word/document.xml'] ?? '';
check('word: a valid .docx container with document, header, footer and the logo picture', $dp !== null && isset($dp['word/header1.xml'], $dp['word/footer1.xml'], $dp['word/media/logo.png'], $dp['word/_rels/header1.xml.rels']) && str_starts_with($docx, 'PK'));
check('word: every part is well-formed XML', $dp !== null && array_reduce(array_keys($dp), static fn(bool $c, string $n): bool => $c && (str_ends_with($n, '.png') || $wellFormed($dp[$n])), true));
check('word: the letterhead is the page header (organisation, address, e-mail), the table header row repeats, six columns print landscape', str_contains($dp['word/header1.xml'] ?? '', htmlspecialchars($lh['org'])) && str_contains($dp['word/header1.xml'] ?? '', 'r:embed="rId1"') && str_contains($body, '<w:tblHeader/>') && str_contains($body, 'w:orient="landscape"') && str_contains($dp['word/footer1.xml'] ?? '', 'NUMPAGES'));
check('word: all 120 rows and the escaped text are in the table', substr_count($body, '<w:tr>') === 121 && str_contains($body, '&lt;b&gt;Co&lt;/b&gt;') && str_contains($body, '<w:br/>'));

$pdf = PdfExport::render($doc);
check('pdf: a multi-page landscape PDF with the letterhead, a repeating table header and "Page n of N"', str_starts_with($pdf, '%PDF-') && str_contains($pdf, '%%EOF') && preg_match_all('/\/Type \/Page[^s]/', $pdf) >= 3 && str_contains($pdf, '/MediaBox [0 0 841.89 595.28]') && str_contains($pdf, '/Subtype /Image'));
$narrow = PdfExport::render(new ExportDocument('Narrow', ['Name', 'Email'], [['A', 'a@b.c']], $lh, [], 'Exported on ' . ExportDocument::now(), false, 1));
check('pdf: a table with up to five columns prints portrait', str_contains($narrow, '/MediaBox [0 0 595.28 841.89]'));
$empty = [PdfExport::render(new ExportDocument('Nothing', ['A', 'B'], [], $lh, [], '', false, 0)), XlsxExport::render(new ExportDocument('Nothing', ['A', 'B'], [], $lh, [], '', false, 0)), DocxExport::render(new ExportDocument('Nothing', ['A', 'B'], [], $lh, [], '', false, 0))];
check('empty list: PDF, Excel and Word still open (header only)', str_starts_with($empty[0], '%PDF-') && str_starts_with($empty[1], 'PK') && str_starts_with($empty[2], 'PK'));
check('exporter: titles from file names, and no format in the address means CSV', Exporter::titleFromFilename('academy-learners-2026-10-28.csv') === 'Academy learners' && Exporter::titleFromFilename('lumina-calendar.csv') === 'Lumina calendar' && Exporter::requested() === 'csv');

// ------------------------------------------------------------------------------------------------------------- over HTTP: real export addresses
$base = 'http://localhost/' . basename(dirname(__DIR__));
$req = static function (string $path, ?string &$jar = null, array $form = null) use ($base): array {
    $jar ??= tempnam(sys_get_temp_dir(), 'exj');
    $ch = curl_init($base . $path);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true, CURLOPT_TIMEOUT => 90, CURLOPT_COOKIEJAR => $jar, CURLOPT_COOKIEFILE => $jar, CURLOPT_FOLLOWLOCATION => false]);
    if ($form !== null) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($form));
    }
    $raw = (string) curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $hs = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);
    return ['code' => $code, 'head' => substr($raw, 0, $hs), 'body' => substr($raw, $hs)];
};
Database::execute("DELETE FROM users WHERE email LIKE 'zz-exp-%'");
Database::execute("DELETE FROM academy_learners WHERE email LIKE 'zz-exp-%'");
Database::execute('DELETE FROM rate_limits');
$pwA = 'Admin exp pass 91';
User::create(['name' => 'ZZ Exp Admin', 'email' => 'zz-exp-admin@example.test', 'password_hash' => Auth::hashPassword($pwA), 'role' => 'admin', 'is_active' => 1, 'must_change_password' => 0]);
Database::insert('academy_learners', ['name' => 'ZZ Exp Learner', 'email' => 'zz-exp-learner@example.test', 'password_hash' => Auth::hashPassword('Secret-12345'), 'country' => 'Uganda', 'is_active' => 1]);
$jA = null;
$page = $req('/admin/login', $jA);
preg_match('/name="_token" value="([^"]+)"/', $page['body'], $m);
$li = $req('/admin/login', $jA, ['_token' => $m[1] ?? '', 'email' => 'zz-exp-admin@example.test', 'password' => $pwA]);
check('http: the test administrator can sign in', in_array($li['code'], [302, 303], true));

$csv = $req('/admin/academy/learners-export', $jA);
check('http: an export without a format is still the plain CSV (UTF-8, header row)', $csv['code'] === 200 && str_contains($csv['head'], 'text/csv') && str_contains($csv['body'], 'Name,Email') && str_contains($csv['body'], 'zz-exp-learner@example.test'));
$xl = $req('/admin/academy/learners-export?format=xlsx&status=unverified', $jA);
check('http: ?format=xlsx gives an Excel workbook download that lists the learner and the filter', $xl['code'] === 200 && str_contains($xl['head'], 'spreadsheetml.sheet') && str_contains($xl['head'], '.xlsx') && str_starts_with($xl['body'], 'PK')
    && str_contains((string) ($unzip($xl['body'])['xl/worksheets/sheet1.xml'] ?? ''), 'zz-exp-learner@example.test') && str_contains((string) ($unzip($xl['body'])['xl/worksheets/sheet1.xml'] ?? ''), 'Filters: Status: unverified'));
$wd = $req('/admin/academy/learners-export?format=docx', $jA);
check('http: ?format=docx gives a Word document download', $wd['code'] === 200 && str_contains($wd['head'], 'wordprocessingml.document') && str_contains($wd['head'], '.docx') && str_contains((string) ($unzip($wd['body'])['word/document.xml'] ?? ''), 'zz-exp-learner@example.test'));
$pd = $req('/admin/academy/learners-export?format=pdf', $jA);
check('http: ?format=pdf gives a PDF download with the exporter\'s name and date', $pd['code'] === 200 && str_contains($pd['head'], 'application/pdf') && str_contains($pd['head'], '.pdf') && str_starts_with($pd['body'], '%PDF-'));
check('http: "xls" and "word" are accepted as Excel and Word; an unknown format falls back to CSV', str_starts_with($req('/admin/academy/learners-export?format=xls', $jA)['body'], 'PK') && str_contains($req('/admin/academy/learners-export?format=nonsense', $jA)['head'], 'text/csv'));
foreach (['/admin/audit-log/export' => 'activity log', '/admin/donations/export' => 'donations', '/admin/messages/export' => 'messages', '/admin/subscribers/export' => 'subscribers', '/admin/members/export' => 'members', '/admin/academy/certificates-export' => 'certificates', '/admin/calendar/export.csv' => 'calendar'] as $path => $label) {
    $r = $req($path . '?format=pdf', $jA);
    $x = $req($path . '?format=xlsx', $jA);
    check('http: the ' . $label . ' export also comes as PDF and Excel', $r['code'] === 200 && str_starts_with($r['body'], '%PDF-') && $x['code'] === 200 && str_starts_with($x['body'], 'PK'), $path . ' ' . $r['code'] . '/' . $x['code']);
}
// who downloaded it, the serial number, the "No." column and the confidentiality clause, in every format
$inflateAll = static function (string $pdfBytes): string {
    $all = '';
    if (preg_match_all('/stream\r?\n(.*?)\r?\nendstream/s', $pdfBytes, $mm) > 0) {
        foreach ($mm[1] as $st) {
            $z = @gzuncompress($st);
            $all .= $z !== false ? $z : '';
        }
    }
    return $all;
};
$ox = $req('/admin/academy/learners-export?format=xlsx&q=zz-exp', $jA);
$oxSheet = (string) ($unzip($ox['body'])['xl/worksheets/sheet1.xml'] ?? '');
check('excel: the sheet says who exported it (name, role, e-mail, user number, address), the document serial number, a "No." column and the confidentiality clause', str_contains($oxSheet, 'Exported by ZZ Exp Admin (') && str_contains($oxSheet, 'Account: zz-exp-admin@example.test') && str_contains($oxSheet, 'User no. ')
    && preg_match('/Document serial no\.: (LAF-EXP-\d{6}-[A-Z2-9]{6})/', $oxSheet, $sm) === 1 && str_contains($oxSheet, '>No.<') && str_contains($oxSheet, 'CONFIDENTIAL: this information is for use or consumption within ' . $lh['org'] . ' only'));
$serial1 = $sm[1] ?? '';
$ow = $req('/admin/academy/learners-export?format=docx&q=zz-exp', $jA);
$owp = $unzip($ow['body']);
check('word: the same details in the page, the "No." column, and the clause plus serial number in the footer on every page', str_contains((string) ($owp['word/document.xml'] ?? ''), 'Exported by ZZ Exp Admin (') && str_contains((string) ($owp['word/document.xml'] ?? ''), '>No.<')
    && str_contains((string) ($owp['word/footer1.xml'] ?? ''), 'CONFIDENTIAL: this information is for use or consumption within') && str_contains((string) ($owp['word/footer1.xml'] ?? ''), 'Serial no. LAF-EXP-'));
$op = $req('/admin/academy/learners-export?format=pdf&q=zz-exp', $jA);
$opText = $inflateAll($op['body']);
check('pdf: the same details on the first page and the clause and serial number in the footer', str_contains($opText, 'Exported by ZZ Exp Admin \(') && str_contains($opText, 'CONFIDENTIAL: this information is for use or consumption within') && str_contains($opText, 'Serial no. LAF-EXP-') && str_contains($opText, '(No.)'));
$logRow = Database::one("SELECT * FROM audit_log WHERE action = 'export.download' AND entity_id = ? LIMIT 1", [$serial1]);
check('audit: every Excel / Word / PDF download is written to the activity log with its serial number, the person, the format and the number of records', $logRow !== null && str_contains((string) $logRow['user_name'], 'zz-exp-admin@example.test') && str_contains((string) $logRow['summary'], $serial1) && str_contains((string) $logRow['details'], '"format":"xlsx"'));
check('serial: two downloads never share a serial number', $serial1 !== '' && preg_match('/Document serial no\.: (LAF-EXP-\d{6}-[A-Z2-9]{6})/', (string) ($unzip($req('/admin/academy/learners-export?format=xlsx&q=zz-exp', $jA)['body'])['xl/worksheets/sheet1.xml'] ?? ''), $sm2) === 1 && $sm2[1] !== $serial1);
Database::execute("DELETE FROM audit_log WHERE action = 'export.download' AND user_name LIKE '%zz-exp-%'");
$tpl = $req('/admin/calendar/import-template.csv?format=pdf', $jA);
check('http: a CSV meant for importing (the calendar template) always stays CSV', $tpl['code'] === 200 && str_contains($tpl['head'], 'text/csv') && !str_starts_with($tpl['body'], '%PDF'));
$ex = $req('/admin/academy/learners-export?format=pdf');
check('http: signed out, an export address does not give any data', $ex['code'] !== 200 && !str_starts_with($ex['body'], '%PDF'));
$list = $req('/admin/academy/learners', $jA);
check('http: the list pages offer the Export menu with CSV, Excel, Word and PDF', str_contains($list['body'], 'export-menu') && str_contains($list['body'], 'format=xlsx') && str_contains($list['body'], 'format=docx') && str_contains($list['body'], 'format=pdf') && str_contains($list['body'], 'Excel (.xlsx)'));
check('http: the calendar page and a meeting page use the same menu', str_contains($req('/admin/calendar', $jA)['body'], 'format=pdf'));

Database::execute("DELETE FROM users WHERE email LIKE 'zz-exp-%'");
Database::execute("DELETE FROM academy_learners WHERE email LIKE 'zz-exp-%'");
Database::execute('DELETE FROM rate_limits');
check('cleanup: no zz-exp rows are left', (int) Database::value("SELECT COUNT(*) FROM academy_learners WHERE email LIKE 'zz-exp-%'", [], 0) === 0);

if (!empty($standalone)) {
    echo PHP_EOL . ($fail === 0 ? 'ALL CHECKS PASSED' : $fail . ' CHECK(S) FAILED') . PHP_EOL;
    exit($fail === 0 ? 0 : 1);
}
