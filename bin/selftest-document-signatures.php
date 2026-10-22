<?php
/**
 * DOCUMENT SIGNATURES (Documents > Signatories / Signatures, signed policy PDFs), included by bin/selftest.php (shares check() and $fail) or run alone:
 *   /c/xampp/php/php.exe bin/selftest-document-signatures.php
 * Covers: the policy pack importer (private by default, idempotent), signature picture validation, signatory + attach + apply + withdraw, the signed copy (original pages plus
 * one approval page, original untouched), the public download (original while unsigned, signed copy afterwards, never while private) and the ability. Removes its own rows (zz-sig-*).
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

use App\Core\Database;
use App\Core\Uploader;
use App\Models\Document;
use App\Services\Documents\DocumentSigning as DS;

echo "-- document signatures --\n";
check('schema is ready', DS::ready());
check('ability: documents.sign is admin only', \App\Core\Roles::can('admin', 'documents.sign') && !\App\Core\Roles::can('editor', 'documents.sign'));

// ---- a tiny valid PNG for the signature -----------------------------------------------------------------------------------------
$png = static function (int $w, int $h): string {
    $raw = '';
    for ($y = 0; $y < $h; $y++) {
        $raw .= "\0";
        for ($x = 0; $x < $w; $x++) {
            $on = abs($y - (int) ($h / 2 + sin($x / 5) * ($h / 4))) < 2;
            $raw .= $on ? "\x10\x20\x70\xff" : "\x00\x00\x00\x00";
        }
    }
    $chunk = static fn(string $t, string $d): string => pack('N', strlen($d)) . $t . $d . pack('N', crc32($t . $d));
    return "\x89PNG\r\n\x1a\n" . $chunk('IHDR', pack('NNCCCCC', $w, $h, 8, 6, 0, 0, 0)) . $chunk('IDAT', gzcompress($raw)) . $chunk('IEND', '');
};
check('picture: garbage is refused', !DS::storeSignatureImage('not a picture')['ok']);
check('picture: a tiny one is refused', !DS::storeSignatureImage($png(10, 10))['ok']);
$img = DS::storeSignatureImage($png(240, 80));
check('picture: a PNG is stored privately', $img['ok'] && $img['mime'] === 'image/png' && str_starts_with($img['path'], 'documents/signatures/'));

// ---- pack importer ---------------------------------------------------------------------------------------------------------------
$tmp = sys_get_temp_dir() . '/zz-sig-' . bin2hex(random_bytes(4));
mkdir($tmp);
$source = DS::packDir() . '/LAF_Code_of_Conduct.pdf';
if (!is_file($source)) {
    check('policy pack is present in storage/private/policy-pack (skipped)', true);
    $src = null;
} else {
    $src = $source;
    copy($source, $tmp . '/zz-sig-policy.pdf');
    file_put_contents($tmp . '/zz-sig-bad.pdf', 'this is not a pdf');
    $a = DS::importPack($tmp, null);
    check('import: the good PDF is added and the bad one is refused', $a['added'] === 1 && count($a['failed']) === 1, json_encode($a));
    $b = DS::importPack($tmp, null);
    check('import: a second run adds nothing', $b['added'] === 0 && $b['skipped'] >= 1);
}

$docId = 0;
$sigIds = [];
$fileCleanup = [];
if ($src !== null) {
    $doc = Database::one("SELECT * FROM documents WHERE file_name = 'zz-sig-policy.pdf'");
    $docId = (int) ($doc['id'] ?? 0);
    check('import: private, active, policy, unsigned', $doc !== null && (int) $doc['is_public'] === 0 && (int) $doc['is_active'] === 1 && $doc['category'] === 'policy' && empty($doc['signed_path']));

    $g1 = Database::insert('document_signatories', ['name' => 'zz-sig Chair', 'title' => 'Chairperson', 'signature_path' => $img['path'], 'signature_mime' => $img['mime'], 'signature_updated_at' => date('Y-m-d H:i:s')]);
    $g2 = Database::insert('document_signatories', ['name' => 'zz-sig Director', 'title' => 'Executive Director']);
    $r1 = Database::insert('document_signatures', ['document_id' => $docId, 'signatory_id' => $g1, 'role_label' => 'Approved by', 'sort_order' => 1]);
    $r2 = Database::insert('document_signatures', ['document_id' => $docId, 'signatory_id' => $g2, 'role_label' => 'Endorsed by', 'sort_order' => 2]);
    $st = DS::status(DS::rows($docId));
    check('status: two awaiting', $st['state'] === 'awaiting' && $st['total'] === 2);

    $res = DS::apply($r2, null, '127.0.0.1');
    check('apply: refused without a signature picture', !$res['ok']);
    $res = DS::apply($r1, null, '127.0.0.1');
    $doc = Document::find($docId);
    check('apply: a signed copy is built', $res['ok'] && !empty($doc['signed_path']) && $doc['signed_mode'] === 'merged', json_encode($res));
    $st = DS::status(DS::rows($docId));
    check('status: partial after one signature', $st['state'] === 'partial' && $st['signed'] === 1);

    $orig = (string) file_get_contents(Uploader::privatePath((string) $doc['file_path']));
    $signed = (string) file_get_contents(Uploader::privatePath((string) $doc['signed_path']));
    check('signed copy: starts with the untouched original bytes (incremental update)', str_starts_with($signed, rtrim($orig, "\r\n")) || str_starts_with($signed, $orig) || strncmp($signed, $orig, strlen($orig) - 16) === 0);
    check('signed copy: larger, ends with EOF, has the signature picture', strlen($signed) > strlen($orig) && str_contains(substr($signed, -64), '%%EOF') && str_contains($signed, '/Subtype /Image'));

    // public download
    $base = 'http://localhost/' . basename(dirname(__DIR__));
    $get = static function (string $path) use ($base): array {
        $ch = curl_init($base . $path);
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true, CURLOPT_TIMEOUT => 40]);
        $out = (string) curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $hs = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);
        return ['code' => $code, 'head' => substr($out, 0, $hs), 'body' => substr($out, $hs)];
    };
    $p = $get('/reports/download/' . $docId);
    check('public: a private document is a 404', $p['code'] === 404);
    Document::update($docId, ['is_public' => 1]);
    $p = $get('/reports/download/' . $docId);
    check('public: once public the SIGNED copy is served', $p['code'] === 200 && stripos($p['head'], '-signed.pdf') !== false && strlen($p['body']) === strlen($signed), 'code ' . $p['code']);
    $list = $get('/reports?category=policy');
    check('public list: shows the Signed badge', str_contains($list['body'], 'Signed'));

    $res = DS::apply($r2, null, '127.0.0.1');
    Database::update('document_signatories', ['signature_path' => $img['path'], 'signature_mime' => 'image/png'], 'id = ?', [$g2]);
    $res = DS::apply($r2, null, '127.0.0.1');
    $st = DS::status(DS::rows($docId));
    check('apply: both signed', $res['ok'] && $st['state'] === 'signed');

    // withdraw everything -> no signed copy, original served again
    Database::execute('UPDATE document_signatures SET signed_at = NULL WHERE document_id = ?', [$docId]);
    $rb = DS::rebuild(Document::find($docId));
    $doc = Document::find($docId);
    check('withdraw: no signed copy remains', $rb['ok'] && empty($doc['signed_path']));
    $p = $get('/reports/download/' . $docId);
    check('public: the original is served again', $p['code'] === 200 && stripos($p['head'], '-signed') === false && strlen($p['body']) === strlen($orig));

    // cleanup
    $doc = Document::find($docId);
    Database::execute('DELETE FROM document_signatures WHERE document_id = ?', [$docId]);
    Database::execute('DELETE FROM document_signatories WHERE id IN (?, ?)', [$g1, $g2]);
    foreach ([$doc['file_path'] ?? null, $doc['signed_path'] ?? null, $img['path']] as $rel) {
        if ($rel) {
            Uploader::deletePrivate((string) $rel);
        }
    }
    Database::execute('DELETE FROM documents WHERE id = ?', [$docId]);
}
@unlink($tmp . '/zz-sig-policy.pdf');
@unlink($tmp . '/zz-sig-bad.pdf');
@rmdir($tmp);
check('cleanup: no test rows left', (int) Database::value("SELECT COUNT(*) FROM documents WHERE file_name LIKE 'zz-sig-%'", [], 0) === 0
    && (int) Database::value("SELECT COUNT(*) FROM document_signatories WHERE name LIKE 'zz-sig%'", [], 0) === 0);

if (!empty($standalone)) {
    echo $fail === 0 ? "\nALL CHECKS PASSED\n" : "\n{$fail} CHECK(S) FAILED\n";
    exit($fail === 0 ? 0 : 1);
}
