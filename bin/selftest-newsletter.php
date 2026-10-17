<?php
/**
 * Newsletter checks, included by bin/selftest.php (shares its check() helper and $fail counter) or run alone:
 *   /c/xampp/php/php.exe bin/selftest-newsletter.php
 * Covers: JSON schema validation and normalisation (strict and lenient), XSS payloads, size caps, status transitions, the lazy-publish visibility
 * predicate, optimistic locking, revision pruning and merging, duplicate / delete rules, the renderer (public = preview, e-mail, text part),
 * the SMTP driver against the throwaway mock server bin/dev/mock-smtp.php (AUTH, large bodies, dot-stuffing, UTF-8, header safety, 4xx / 5xx,
 * timeouts, connection reuse, STARTTLS and implicit TLS with certificate verification ON), and the RFC 8058 one-click unsubscribe over HTTP.
 * Everything it creates is removed again (newsletters zz-selftest-*, subscribers @selftest.invalid, mock-smtp/selftest output).
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
use App\Core\Database;
use App\Core\Mailer;
use App\Core\MailException;
use App\Core\Smtp;
use App\Models\Newsletter;
use App\Models\NewsletterRevision;
use App\Services\Newsletter\NewsletterContent as NC;
use App\Services\Newsletter\NewsletterRenderer as NR;
use App\Services\Newsletter\NewsletterService as NS;

echo "Newsletter\n";

$nlAuditBefore = (int) Database::value('SELECT COALESCE(MAX(id), 0) FROM audit_log', [], 0);
$nlMailConfig = [Config::get('mail.driver'), Config::get('mail.smtp')];
$nlMockDir = STORAGE_PATH . '/mock-smtp/selftest';
$nlProcs = [];
$nlCleanup = static function () use ($nlAuditBefore, &$nlProcs, $nlMailConfig, $nlMockDir): void {
    foreach ($nlProcs as $p) {
        if (is_resource($p['proc'])) {
            @file_put_contents($p['dir'] . '/STOP', '1');
            @proc_terminate($p['proc']);
            @proc_close($p['proc']);
        }
    }
    $nlProcs = [];
    Mailer::closeConnection();
    Config::set('mail.driver', $nlMailConfig[0]);
    Config::set('mail.smtp', $nlMailConfig[1]);
    Database::execute("DELETE FROM newsletters WHERE slug LIKE 'zz-selftest-%' OR title LIKE 'zz-selftest%'");
    Database::execute("DELETE FROM subscribers WHERE email LIKE '%@selftest.invalid'");
    Database::execute("DELETE FROM audit_log WHERE id > ? AND action LIKE 'newsletter.%'", [$nlAuditBefore]);
    $rm = static function (string $dir) use (&$rm): void {
        foreach (glob($dir . '/*') ?: [] as $f) {
            is_dir($f) ? $rm($f) : @unlink($f);
        }
        @rmdir($dir);
    };
    usleep(300000);          // let a stopped mock server release its files (Windows)
    $rm($nlMockDir);
};
$nlCleanup();
@mkdir($nlMockDir, 0775, true);

/** Start the mock SMTP server on a free port; returns [port, dir] or null. */
$nlMock = static function (array $args = [], string $tag = 'plain') use (&$nlProcs, $nlMockDir): ?array {
    $probe = stream_socket_server('tcp://127.0.0.1:0');
    if (!$probe) {
        return null;
    }
    $port = (int) substr((string) stream_socket_get_name($probe, false), strrpos((string) stream_socket_get_name($probe, false), ':') + 1);
    fclose($probe);
    $dir = $nlMockDir . '/' . $tag;
    @mkdir($dir, 0775, true);
    $cmd = array_merge([PHP_BINARY, dirname(__DIR__) . '/bin/dev/mock-smtp.php', '--quiet', '--port=' . $port, '--out=' . $dir], $args);
    $proc = @proc_open(array_values($cmd), [0 => ['pipe', 'r'], 1 => ['file', $nlMockDir . '/' . $tag . '.log', 'w'], 2 => ['file', $nlMockDir . '/' . $tag . '.err', 'w']], $pipes);
    if (!is_resource($proc)) {
        return null;
    }
    $nlProcs[] = ['proc' => $proc, 'dir' => $dir];
    for ($i = 0; $i < 40; $i++) {
        $s = @stream_socket_client('tcp://127.0.0.1:' . $port, $en, $es, 0.5);
        if ($s) {
            fclose($s);
            return ['port' => $port, 'dir' => $dir];
        }
        usleep(100000);
    }
    return null;
};
/** Messages the mock stored in $dir, oldest first. @return list<string> */
$nlInbox = static function (string $dir): array {
    $files = glob($dir . '/*.eml') ?: [];
    sort($files);
    return array_map(static fn(string $f): string => (string) file_get_contents($f), $files);
};

try {
    // =========================================================================================================== content schema
    $ok = NC::normalise(NC::emptyContent());
    check('empty issue is valid', $ok['ok'] && $ok['content']['blocks'] === [] && $ok['content']['version'] === 1);
    $valid = [
        'version' => 1,
        'featured' => ['heading' => 'Hello <b>there</b>', 'body_html' => '<p>Body <strong>bold</strong></p>', 'image' => ['path' => 'assets/img/site/photo-05.jpg', 'alt' => 'Alt', 'caption' => 'Cap'], 'link' => ['label' => 'Go', 'url' => '/programs']],
        'blocks' => [
            ['id' => 'b1', 'type' => 'article', 'heading' => 'A', 'body_html' => '<p>x</p>', 'extra' => 'dropped'],
            ['id' => 'b1', 'type' => 'quote', 'body_html' => '<p>Q</p>', 'cite' => 'Me'],
            ['type' => 'image', 'image' => ['path' => 'assets/img/site/photo-03.jpg', 'alt' => 'a']],
            ['type' => 'button', 'button' => ['label' => 'Push', 'url' => 'https://example.org/x', 'style' => 'outline']],
            ['type' => 'divider'],
        ],
        'unknown_top_level' => true,
    ];
    $n = NC::normalise($valid);
    $ids = array_column($n['content']['blocks'], 'id');
    check('valid content passes, unknown keys dropped', $n['ok'] && !isset($n['content']['unknown_top_level']) && !isset($n['content']['blocks'][0]['extra']), json_encode($n['errors']));
    check('plain text fields are stripped of tags', $n['content']['featured']['heading'] === 'Hello there');
    check('duplicate / missing block ids are regenerated', count($ids) === 5 && count(array_unique($ids)) === 5 && $ids[0] === 'b1' && $ids[1] !== 'b1');
    check('normalise is idempotent', NC::normalise($n['content'])['content'] === $n['content']);
    check('block shapes are canonical per type', array_keys($n['content']['blocks'][4]) === ['id', 'type'] && array_keys($n['content']['blocks'][3]['button']) === ['label', 'url', 'style']);
    check('JSON string input is accepted', NC::normalise(NC::encode($valid))['ok']);
    $bad = NC::normalise(['blocks' => [['type' => 'video']]]);
    check('unknown block type is an error (strict)', !$bad['ok'] && isset($bad['errors']['blocks.0.type']));
    $lenient = NC::normalise(['blocks' => [['type' => 'video'], ['type' => 'divider']]], ['strict' => false]);
    check('unknown block type is skipped + warned (lenient)', $lenient['ok'] && count($lenient['content']['blocks']) === 1 && $lenient['warnings'] !== []);
    check('blocks must be a list', !NC::normalise(['blocks' => ['a' => ['type' => 'divider']]])['ok']);
    check('more than 60 blocks is an error', !NC::normalise(['blocks' => array_fill(0, 61, ['type' => 'divider'])])['ok'] && NC::normalise(['blocks' => array_fill(0, 60, ['type' => 'divider'])])['ok']);
    check('a wrong type for a field is an error', !NC::normalise(['featured' => ['heading' => ['x']]])['ok'] && !NC::normalise(['version' => 2])['ok'] && !NC::normalise('[1,2]')['ok']);
    check('body too long is an error', !NC::normalise(['featured' => ['body_html' => '<p>' . str_repeat('a', NC::MAX_BODY + 10) . '</p>']])['ok']);
    check('heading too long is an error (strict), cut (lenient)', !NC::normalise(['featured' => ['heading' => str_repeat('h', 300)]])['ok']
        && mb_strlen(NC::normalise(['featured' => ['heading' => str_repeat('h', 300)]], ['strict' => false])['content']['featured']['heading']) === NC::MAX_HEADING);
    check('whole issue size cap', !NC::normalise(['blocks' => array_fill(0, 40, ['type' => 'article', 'body_html' => '<p>' . str_repeat('lorem ipsum ', 1300) . '</p>'])])['ok']);

    // URLs and images
    foreach (['javascript:alert(1)', 'data:text/html,x', '//evil.example/x', 'vbscript:x', '#frag', 'ftp://x.org/a', "java\nscript:alert(1)", 'about'] as $u) {
        $r = NC::normalise(['featured' => ['link' => ['label' => 'x', 'url' => $u]]]);
        if ($u === 'about') {
            check('a bare word becomes a site path', $r['ok'] && $r['content']['featured']['link']['url'] === '/about');
            continue;
        }
        check('unsafe URL refused: ' . str_replace("\n", '\n', $u), !$r['ok'] && isset($r['errors']['featured.link.url']));
    }
    check('http, https, mailto, tel and site paths are accepted', NC::cleanUrl('https://a.org/x?y=1') !== '' && NC::cleanUrl('http://a.org') !== '' && NC::cleanUrl('mailto:a@b.org') !== ''
        && NC::cleanUrl('tel:+256700000000') !== '' && NC::cleanUrl('/about') === '/about');
    foreach (['../../etc/passwd.jpg', '2026/10/../../x.jpg', 'https://evil.example/x.jpg', '/etc/passwd', 'assets/js/main.js', 'uploads/2026/10/a.php', '2026/10/missing-file.jpg'] as $p) {
        $r = NC::normalise(['featured' => ['image' => ['path' => $p, 'alt' => 'x']]]);
        check('image path refused: ' . $p, !$r['ok'] && isset($r['errors']['featured.image.path']));
    }
    check('a bundled photo is accepted; a missing file is accepted without check_files', NC::normalise(['featured' => ['image' => ['path' => 'assets/img/site/photo-05.jpg']]])['ok']
        && NC::normalise(['featured' => ['image' => ['path' => '2026/10/missing.jpg']]], ['check_files' => false])['ok']);

    // XSS payloads
    $payloads = [
        '<script>alert(1)</script>', '<img src=x onerror=alert(1)>', '<a href="javascript:alert(1)">x</a>', '<iframe src="//evil"></iframe>', '<svg onload=alert(1)>',
        '<p onclick="alert(1)">x</p>', '<a href="data:text/html;base64,PHNjcmlwdD4=">x</a>', '<style>body{display:none}</style>', '<form action=//evil><input></form>', '<math><mi>x</mi></math>',
        '<img src="//evil.example/a.png">', '<a href="  JaVaScRiPt:alert(1)">x</a>',
    ];
    $xss = true;
    $leak = '';
    foreach ($payloads as $pl) {
        $c = NC::normalise(['featured' => ['heading' => $pl, 'body_html' => $pl], 'blocks' => [['type' => 'article', 'heading' => $pl, 'body_html' => $pl], ['type' => 'quote', 'body_html' => $pl, 'cite' => $pl]]], ['check_files' => false]);
        $json = NC::encode($c['content']);
        if (preg_match('/<script|onerror|onclick|onload|javascript:|<iframe|<svg|<style|<form|<math|data:text|\/\/evil/i', $json)) {
            $xss = false;
            $leak = $pl;
        }
    }
    check('XSS payloads are stripped from every text field', $xss, $leak);

    check('decode never throws on garbage', NC::decode('{not json')['blocks'] === [] && NC::decode(null)['version'] === 1 && NC::decode('[1,2,3]')['blocks'] === [] && NC::decode(['blocks' => 'x'])['blocks'] === []);
    check('plainText and htmlToText', str_contains(NC::plainText($n['content']), 'Body bold') && NC::htmlToText('<p>a<br>b</p><ul><li>x</li></ul><p><a href="https://e.org/z">go</a></p>') === "a\nb\n\n- x\n\ngo (https://e.org/z)",
        NC::htmlToText('<p>a<br>b</p><ul><li>x</li></ul><p><a href="https://e.org/z">go</a></p>'));
    check('firstImage', NC::firstImage($n['content'])['path'] === 'assets/img/site/photo-05.jpg' && NC::firstImage(NC::emptyContent()) === null);
    $rd = NC::readiness(NC::emptyContent(), ['title' => '']);
    check('readiness: empty issue is not publishable', isset($rd['blocking']['title'], $rd['blocking']['content']));
    $rd = NC::readiness(['featured' => ['heading' => 'x'], 'blocks' => [['type' => 'button', 'button' => ['label' => 'L', 'url' => '']]]], ['title' => 'T']);
    check('readiness: incomplete button blocks publishing', isset($rd['blocking']['blocks.0']));

    // =========================================================================================================== service
    $res = NS::create(['title' => 'zz-selftest A', 'issue_number' => '', 'content' => $valid], null);
    check('create: draft with next issue number and a slug', $res['ok'] && $res['newsletter']['status'] === 'draft' && (int) $res['newsletter']['issue_number'] === NS::nextIssueNumber() - 1 && (int) $res['newsletter']['version'] === 1
        && $res['newsletter']['slug'] === 'zz-selftest-a', json_encode($res['errors'] ?? []));
    $A = (int) $res['newsletter']['id'];
    $dup = NS::create(['title' => 'zz-selftest A']);
    check('slugs are unique', $dup['ok'] && $dup['newsletter']['slug'] === 'zz-selftest-a-2');
    $Bid = (int) $dup['newsletter']['id'];
    check('reserved slugs are refused', NS::uniqueSlug('archive') !== 'archive' && !in_array(NS::uniqueSlug('Archive'), NS::RESERVED_SLUGS, true));
    $nn = (int) $res['newsletter']['issue_number'];
    check('create: duplicate issue number is refused', !NS::create(['title' => 'zz-selftest dup number', 'issue_number' => (string) $nn])['ok']);
    check('create: title is required', !NS::create(['title' => ' '])['ok'] && !NS::create(['title' => 'zz-selftest bad date', 'issue_date' => '2026-02-30'])['ok']);

    // status workflow
    check('transitions table', NS::canTransition('draft', 'published') && NS::canTransition('published', 'draft') && !NS::canTransition('archived', 'published')
        && !NS::canTransition('published', 'scheduled') && NS::canTransition('archived', 'draft') && !NS::canTransition('draft', 'draft'));
    $empty = NS::publish($Bid);
    check('an empty issue cannot be published', !$empty['ok'] && isset($empty['errors']['content']));
    $past = NS::schedule($A, date('Y-m-d\TH:i', time() - 3600));
    check('scheduling in the past is refused; garbage too', !$past['ok'] && !NS::schedule($A, 'tomorrow')['ok']);
    $sch = NS::schedule($A, date('Y-m-d\TH:i', time() + 7200));
    check('schedule: status scheduled, publish_at set', $sch['ok'] && $sch['newsletter']['status'] === 'scheduled' && $sch['newsletter']['publish_at'] > NS::now());

    // visibility predicate (the one public rule)
    $vis = static function (int $id): bool {
        [$sql, $params] = NS::visibleSql('n');
        return (int) Database::value("SELECT COUNT(*) FROM newsletters n WHERE n.id = ? AND {$sql}", array_merge([$id], $params), 0) === 1;
    };
    check('visible: a future schedule is NOT public, a draft is not', !$vis($A) && !$vis($Bid) && NS::findPublishedBySlug('zz-selftest-a') === null);
    Database::execute("UPDATE newsletters SET publish_at = ? WHERE id = ?", [date('Y-m-d H:i:s', time() - 60), $A]);
    check('visible: a due schedule IS public at once (no cron)', $vis($A) && NS::isVisible(Newsletter::find($A)) && NS::findPublishedBySlug('zz-selftest-a') !== null);
    check('lazy updater flips the stored status once', NS::resolveScheduled(true) >= 1 && Newsletter::find($A)['status'] === 'published' && Newsletter::find($A)['published_at'] !== null && NS::resolveScheduled(true) === 0);
    check('latestPublished / archive / adjacent only see public issues', ($l = NS::latestPublished()) !== null && NS::countPublished() >= 1
        && !in_array($Bid, array_map(static fn(array $r): int => (int) $r['id'], NS::archive(1, 50)->items), true));
    $un = NS::unpublish($A);
    check('unpublish: back to draft, no longer public', $un['ok'] && $un['newsletter']['status'] === 'draft' && !$vis($A));
    $pub = NS::publish($A);
    check('publish: public with published_at', $pub['ok'] && $vis($A) && $pub['newsletter']['published_at'] !== null);
    check('archived issues are not public', NS::archiveIssue($A)['ok'] && !$vis($A) && NS::transition($A, 'published')['ok'] === false && NS::transition($A, 'draft')['ok'] && NS::publish($A)['ok']);
    $first = Newsletter::find($A)['published_at'];
    NS::unpublish($A);
    NS::publish($A);
    check('republishing keeps the first publication time', Newsletter::find($A)['published_at'] === $first);

    // optimistic locking
    $v = (int) Newsletter::find($A)['version'];
    $s1 = NS::save($A, ['summary' => 'zz summary one'], $v, null);
    check('save: bumps version, stores details', $s1['ok'] && (int) $s1['newsletter']['version'] === $v + 1 && $s1['newsletter']['summary'] === 'zz summary one');
    $conf = NS::save($A, ['summary' => 'stale writer'], $v, null);
    check('save with a stale version is a CONFLICT and changes nothing', !$conf['ok'] && !empty($conf['conflict']) && $conf['current']['version'] === $v + 1 && Newsletter::find($A)['summary'] === 'zz summary one');
    $same = NS::save($A, ['summary' => 'zz summary one'], $v + 1, null);
    check('a save that changes nothing keeps the version', $same['ok'] && $same['changed'] === false && (int) Newsletter::find($A)['version'] === $v + 1);
    $bad = NS::save($A, ['issue_date' => 'nope', 'content' => ['blocks' => [['type' => 'button', 'button' => ['url' => 'javascript:x']]]]], $v + 1, null);
    check('invalid save returns errors keyed by path and does not bump the version', !$bad['ok'] && isset($bad['errors']['issue_date'], $bad['errors']['blocks.0.button.url']) && (int) Newsletter::find($A)['version'] === $v + 1);
    $auto = NS::save($Bid, ['content' => ['blocks' => [['type' => 'button', 'button' => ['label' => 'x', 'url' => 'javascript:x']]]]], (int) Newsletter::find($Bid)['version'], null, ['autosave' => true]);
    check('autosave is lenient: a half-typed URL is cleared with a warning, not an error', $auto['ok'] && $auto['warnings'] !== []);
    $liveAuto = NS::save($A, ['content' => ['featured' => ['heading' => 'half typed by a stale tab']]], $v + 1, null, ['autosave' => true]);
    check('autosave on a PUBLISHED issue is refused (stale editor tab) and writes nothing', !$liveAuto['ok'] && !empty($liveAuto['status_changed'])
        && (int) Newsletter::find($A)['version'] === $v + 1 && !str_contains((string) Newsletter::find($A)['content'], 'half typed by a stale tab'));
    check('slug of a published issue is locked', !NS::save($A, ['slug' => 'zz-selftest-new-slug'], (int) Newsletter::find($A)['version'], null)['ok']);
    check('issue number clash is refused on save', !NS::save($Bid, ['issue_number' => (string) $nn], (int) Newsletter::find($Bid)['version'], null)['ok']);
    check('draft slug can change, must be unique and well-formed', NS::save($Bid, ['slug' => 'zz-selftest-renamed'], (int) Newsletter::find($Bid)['version'], null)['ok']
        && !NS::save($Bid, ['slug' => 'zz-selftest-a'], (int) Newsletter::find($Bid)['version'], null)['ok'] && !NS::save($Bid, ['slug' => 'Bad Slug!'], (int) Newsletter::find($Bid)['version'], null)['ok']);

    // revisions
    $before = (int) Database::value('SELECT COUNT(*) FROM newsletter_revisions WHERE newsletter_id = ?', [$A], 0);
    for ($i = 1; $i <= 40; $i++) {
        $cur = Newsletter::find($A);
        NS::save($A, ['content' => ['featured' => ['heading' => 'rev ' . $i, 'body_html' => '<p>b</p>']]], (int) $cur['version'], null);     // source 'save' (not autosave): never merged
    }
    $count = (int) Database::value('SELECT COUNT(*) FROM newsletter_revisions WHERE newsletter_id = ?', [$A], 0);
    check('revisions are pruned to the latest ' . NewsletterRevision::KEEP, $count === NewsletterRevision::KEEP, (string) $count);
    $revs = NS::revisions($A);
    check('revision list is newest first and has no content', count($revs) === NewsletterRevision::KEEP && $revs[0]['id'] > $revs[1]['id'] && !isset($revs[0]['content']));
    $cur = Newsletter::find($A);
    for ($i = 0; $i < 3; $i++) {
        $curB = Newsletter::find($Bid);
        NS::save($Bid, ['content' => ['featured' => ['heading' => 'auto ' . $i]]], (int) $curB['version'], null, ['autosave' => true]);
    }
    $autoRevs = (int) Database::value("SELECT COUNT(*) FROM newsletter_revisions WHERE newsletter_id = ? AND source = 'autosave'", [$Bid], 0);
    check('consecutive autosaves by one author merge into one revision', $autoRevs === 1, (string) $autoRevs);
    $old = NS::revision($A, (int) $revs[10]['id']);
    $cur = Newsletter::find($A);
    $diff = NS::diffLite(NC::decode((string) $cur['content']), $old['content_data']);
    check('diff-lite lists the changed heading', $diff !== [] && $diff[0]['kind'] === 'changed' && str_contains($diff[0]['where'], 'Featured'));
    $rest = NS::restoreRevision($A, (int) $revs[10]['id'], (int) $cur['version'], null);
    check('restore: content comes back, version bumps', $rest['ok'] && Newsletter::find($A)['content'] !== $cur['content'] && (int) Newsletter::find($A)['version'] === (int) $cur['version'] + 1);
    check('restore with a stale version is a conflict', !empty(NS::restoreRevision($A, (int) $revs[10]['id'], 1, null)['conflict']));


    // =========================================================================================================== guards added after review
    // a live issue must stay complete
    $liveEmpty = NS::save($A, ['content' => ['featured' => ['heading' => '', 'body_html' => ''], 'blocks' => []]], (int) Newsletter::find($A)['version'], null);
    check('a PUBLISHED issue cannot be saved empty or incomplete (readiness on every save)', !$liveEmpty['ok'] && !empty($liveEmpty['not_ready']) && isset($liveEmpty['errors']['content']));
    $liveHalf = NS::save($A, ['content' => ['featured' => ['heading' => 'ok'], 'blocks' => [['type' => 'button', 'button' => ['label' => '', 'url' => '']]]]], (int) Newsletter::find($A)['version'], null);
    check('... also an empty button block', !$liveHalf['ok'] && !empty($liveHalf['not_ready']));
    $dr = NS::create(['title' => 'zz-selftest draft empty']);
    check('a DRAFT may still be saved incomplete', NS::save((int) $dr['newsletter']['id'], ['summary' => 'x'], 1, null)['ok']);
    // the campaign freezes the issue
    Database::execute("UPDATE newsletters SET email_status = 'sending' WHERE id = ?", [$A]);
    $vA = (int) Newsletter::find($A)['version'];
    $frozen = NS::save($A, ['title' => 'CHANGED while sending'], $vA, null);
    $frozenC = NS::save($A, ['content' => ['featured' => ['heading' => 'CHANGED while sending']]], $vA, null);
    check('while an e-mail campaign runs the title and content cannot be saved (every subscriber gets the same message)', !$frozen['ok'] && !empty($frozen['locked']) && !$frozenC['ok'] && !empty($frozenC['locked'])
        && (int) Newsletter::find($A)['version'] === $vA && Newsletter::find($A)['title'] === 'zz-selftest A');
    check('... and a revision cannot be restored over it', !empty(NS::restoreRevision($A, (int) NS::revisions($A)[3]['id'], $vA, null)['locked']));
    Database::execute("UPDATE newsletters SET email_status = 'paused' WHERE id = ?", [$A]);
    check('... paused counts as running', !empty(NS::save($A, ['summary' => 'again'], $vA, null)['locked']));
    Database::execute("UPDATE newsletters SET email_status = 'none' WHERE id = ?", [$A]);
    // non-object content never throws
    $odd = [];
    foreach ([5, 1.5, true, false, 0] as $weird) {
        $r1 = NC::normalise($weird);
        $r2 = NS::create(['title' => 'zz-selftest weird', 'content' => $weird]);
        $r3 = NS::save($Bid, ['content' => $weird], (int) Newsletter::find($Bid)['version'], null);
        $r4 = NR::previewRow(null, ['title' => 't', 'content' => $weird]);
        if ($r1['ok'] || $r2['ok'] || $r3['ok'] || !isset($r1['errors']['content']) || !isset($r4['content'])) {
            $odd[] = var_export($weird, true);
        }
    }
    check('a JSON number or boolean as "content" is a validation error, not a crash (create, save, preview)', $odd === [], implode(',', $odd));
    // issue numbers are unique in the database
    $dupNum = false;
    try {
        Database::execute('UPDATE newsletters SET issue_number = ? WHERE id = ?', [$nn, $Bid]);
    } catch (\PDOException) {
        $dupNum = true;
    }
    check('two issues can never share an issue number (UNIQUE index); several without a number are fine', $dupNum
        && (int) Database::value("SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'newsletters' AND index_name = 'ux_newsletters_number' AND non_unique = 0", [], 0) === 1);
    check('the suggested number never exceeds what the form accepts', NS::nextIssueNumber() <= NS::MAX_ISSUE_NUMBER);
    // pictures are released
    $mkPic = static function (string $name): string {
        $dir = UPLOAD_PATH . '/' . date('Y') . '/' . date('m');
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        file_put_contents($dir . '/' . $name, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg=='));
        return date('Y') . '/' . date('m') . '/' . $name;
    };
    $picCover = $mkPic('zzselftest-cover-' . bin2hex(random_bytes(4)) . '.png');
    $picBody = $mkPic('zzselftest-body-' . bin2hex(random_bytes(4)) . '.png');
    $picKeep = $mkPic('zzselftest-keep-' . bin2hex(random_bytes(4)) . '.png');
    $pi = NS::create(['title' => 'zz-selftest pictures', 'cover_image' => $picCover, 'content' => ['featured' => ['heading' => 'h', 'image' => ['path' => $picBody, 'alt' => 'a']]]]);
    $Pid = (int) $pi['newsletter']['id'];
    check('pictures: create with a cover and a featured picture', $pi['ok'] && is_file(UPLOAD_PATH . '/' . $picCover) && is_file(UPLOAD_PATH . '/' . $picBody), json_encode($pi['errors'] ?? []));
    $x = NS::save($Pid, ['cover_image' => ''], 1, null);
    check('pictures: a cleared cover is deleted from disk', $x['ok'] && !is_file(UPLOAD_PATH . '/' . $picCover));
    $x = NS::save($Pid, ['content' => ['featured' => ['heading' => 'h', 'image' => ['path' => $picKeep, 'alt' => 'a']]]], 2, null);
    check('pictures: a replaced picture still needed by a saved version (History) is KEPT', $x['ok'] && is_file(UPLOAD_PATH . '/' . $picBody) && is_file(UPLOAD_PATH . '/' . $picKeep));
    check('pictures: deleting the issue removes its pictures and the ones in its versions', NS::delete($Pid)['ok'] && !is_file(UPLOAD_PATH . '/' . $picBody) && !is_file(UPLOAD_PATH . '/' . $picKeep));
    // erasure
    $erSub = Database::insert('subscribers', ['email' => 'nl-erase@selftest.invalid', 'status' => 'subscribed', 'token' => bin2hex(random_bytes(20))]);
    $erSub2 = Database::insert('subscribers', ['email' => 'nl-erase2@selftest.invalid', 'status' => 'subscribed', 'token' => bin2hex(random_bytes(20))]);
    $erDel = Database::insert('newsletter_deliveries', ['newsletter_id' => $A, 'subscriber_id' => $erSub, 'email' => 'nl-erase@selftest.invalid', 'status' => 'sent', 'last_error' => null]);
    $erDel2 = Database::insert('newsletter_deliveries', ['newsletter_id' => $A, 'subscriber_id' => $erSub2, 'email' => 'nl-erase2@selftest.invalid', 'status' => 'queued']);
    $scrubbed = \App\Models\NewsletterDelivery::erase([$erSub], ['nl-erase@selftest.invalid']);
    $erow = Database::one('SELECT * FROM newsletter_deliveries WHERE id = ?', [$erDel]);
    check('erasure: a deleted subscriber is scrubbed from the campaign record (address replaced, row and counters kept)', $scrubbed === 1 && $erow !== null && str_ends_with((string) $erow['email'], '@invalid.invalid')
        && !str_contains((string) $erow['email'], 'selftest') && $erow['subscriber_id'] === null && $erow['status'] === 'sent'
        && (int) Database::value("SELECT COUNT(*) FROM newsletter_deliveries WHERE email LIKE '%nl-erase@selftest%'", [], 0) === 0);
    \App\Models\NewsletterDelivery::erase([$erSub2]);
    $erow2 = Database::one('SELECT status, email FROM newsletter_deliveries WHERE id = ?', [$erDel2]);
    check('erasure: a recipient still waiting becomes skipped (never mailed)', $erow2 !== null && $erow2['status'] === 'skipped' && !str_contains((string) $erow2['email'], 'selftest'));
    check('erasure: running it again changes nothing', \App\Models\NewsletterDelivery::erase([$erSub], ['nl-erase@selftest.invalid']) === 0);
    Database::execute('DELETE FROM newsletter_deliveries WHERE id IN (?, ?)', [$erDel, $erDel2]);
    Database::execute('DELETE FROM subscribers WHERE id IN (?, ?)', [$erSub, $erSub2]);
    // wording of refusals
    $arch = NS::create(['title' => 'zz-selftest archived wording', 'content' => $valid]);
    NS::archiveIssue((int) $arch['newsletter']['id']);
    check('refusal wording reads correctly ("An archived issue ...")', str_starts_with((string) NS::publish((int) $arch['newsletter']['id'])['error'], 'An archived issue'));

    // duplicate + delete
    $copy = NS::duplicate($A);
    check('duplicate: a new draft titled "(copy)" with the same content and a fresh number/slug', $copy['ok'] && $copy['newsletter']['status'] === 'draft' && str_ends_with($copy['newsletter']['title'], '(copy)')
        && $copy['newsletter']['content'] === Newsletter::find($A)['content'] && $copy['newsletter']['slug'] !== Newsletter::find($A)['slug'] && $copy['newsletter']['issue_number'] !== Newsletter::find($A)['issue_number']);
    Database::execute("UPDATE newsletters SET email_status = 'sending' WHERE id = ?", [$A]);
    check('an issue with a running e-mail campaign cannot be unpublished or deleted', !NS::unpublish($A)['ok'] && !NS::delete($A)['ok']);
    Database::execute("UPDATE newsletters SET email_status = 'none' WHERE id = ?", [$A]);
    $subId = Database::insert('subscribers', ['email' => 'nl-del@selftest.invalid', 'status' => 'subscribed', 'token' => bin2hex(random_bytes(20))]);
    Database::insert('newsletter_deliveries', ['newsletter_id' => $A, 'subscriber_id' => $subId, 'email' => 'nl-del@selftest.invalid']);
    $dupDelivery = false;
    try {
        Database::insert('newsletter_deliveries', ['newsletter_id' => $A, 'subscriber_id' => $subId, 'email' => 'nl-del@selftest.invalid']);
    } catch (\PDOException) {
        $dupDelivery = true;
    }
    check('the same address cannot be queued twice for one issue (UNIQUE)', $dupDelivery);
    check('delete removes revisions and deliveries', NS::delete($A)['ok'] && (int) Database::value('SELECT COUNT(*) FROM newsletter_revisions WHERE newsletter_id = ?', [$A], 0) === 0
        && (int) Database::value('SELECT COUNT(*) FROM newsletter_deliveries WHERE newsletter_id = ?', [$A], 0) === 0);

    // =========================================================================================================== renderer
    $cr = NS::create(['title' => 'zz-selftest render', 'summary' => 'Render summary', 'cover_image' => 'assets/img/site/photo-04.jpg', 'cover_alt' => 'cover', 'content' => $valid + ['x' => 1]]);
    $R = $cr['newsletter'];
    $public = NR::renderIssue($R, 'public');
    $preview = NR::renderIssue($R, 'preview');
    $stripped = (string) preg_replace('/ data-nl-block="[^"]*" tabindex="0"/', '', $preview);
    $stripped = str_replace('nl-issue nl-issue--preview', 'nl-issue', $stripped);
    check('preview is the public HTML plus preview hooks only', $stripped === $public && str_contains($preview, 'data-nl-block="b1"') && !str_contains($public, 'data-nl-block'));
    check('public render: heading, summary, featured, blocks in order', str_contains($public, 'Render summary') && str_contains($public, '<h1 class="nl-issue__title">zz-selftest render</h1>')
        && strpos($public, 'nl-featured') < strpos($public, 'nl-block--article') && strpos($public, 'nl-block--article') < strpos($public, 'nl-block--quote'));
    $evil = NS::create(['title' => 'zz-selftest evil', 'content' => ['featured' => ['heading' => '<img src=x onerror=alert(1)>', 'body_html' => '<p>ok</p><script>alert(1)</script>'], 'blocks' => [['type' => 'button', 'button' => ['label' => '"><script>alert(1)</script>', 'url' => 'https://example.org/?a=1&b=2']]]]]);
    $leaks = '';
    foreach (['public', 'preview', 'email'] as $ctx) {
        $h = NR::renderIssue($evil['newsletter'], $ctx, ['unsubscribe_url' => 'https://example.org/u']);
        if (preg_match('/<script|onerror=|javascript:/i', preg_replace('~<script type="application/ld\+json">.*?</script>~s', '', $h) ?? $h)) {
            $leaks .= $ctx . ' ';
        }
    }
    check('no script or event handler reaches public, preview or e-mail HTML', $leaks === '', $leaks);
    $mail = NR::renderIssue($R, 'email', ['unsubscribe_url' => 'https://example.org/unsubscribe/abc', 'subject' => 'Subj']);
    check('e-mail HTML: full document, table layout, inline CSS, no script', str_starts_with(ltrim($mail), '<!doctype html>') && str_contains($mail, '<table') && str_contains($mail, 'style="') && !str_contains($mail, '<script') && !str_contains($mail, '<link'));
    check('e-mail HTML: absolute image and link URLs, preheader, view in browser, unsubscribe link', !preg_match('~(?:src|href)="/(?!/)~', $mail) && str_contains($mail, abs_url('programs'))
        && str_contains($mail, 'display:none') && str_contains($mail, 'View in browser') && str_contains($mail, 'href="https://example.org/unsubscribe/abc"') && str_contains($mail, 'Render summary'));
    $longEv = NS::create(['title' => 'zz-selftest long words', 'content' => ['featured' => ['heading' => str_repeat('W', 140), 'body_html' => '<p>' . str_repeat('x', 300) . '</p><table><tr><td>' . str_repeat('y', 150) . '</td></tr></table>'],
        'blocks' => [['type' => 'article', 'heading' => str_repeat('H', 150), 'body_html' => '<p><a href="https://example.org/' . str_repeat('p', 400) . '">https://example.org/' . str_repeat('p', 400) . '</a></p>'],
        ['type' => 'button', 'button' => ['label' => str_repeat('B', 78), 'url' => '/involvement']], ['type' => 'quote', 'body_html' => '<p>' . str_repeat('q', 200) . '</p>']]]]);
    $longHtml = NR::renderIssue($longEv['newsletter'], 'email', ['unsubscribe_url' => 'https://example.org/unsubscribe/abc']);
    check('e-mail layout: fixed card tables and wrapping on every text cell, so a pasted URL cannot stretch the 600 px card',
        substr_count($longHtml, 'table-layout:fixed') >= 2 && substr_count($longHtml, 'overflow-wrap:anywhere') >= 12 && str_contains($longHtml, 'word-break:break-word')
        && preg_match('~<h1 [^>]*overflow-wrap:anywhere~', $longHtml) === 1 && preg_match('~<h2 [^>]*overflow-wrap:anywhere~', $longHtml) === 1
        && preg_match('~<td style="[^"]*overflow-wrap:anywhere[^"]*">[^<]*y{20}~', $longHtml) === 1 && preg_match('~<a href="https://example.org/p+"[^>]*overflow-wrap:anywhere~', $longHtml) === 1);
    \App\Core\Content::set('newsletter', 'email.unsubscribe', '');
    \App\Core\Content::set('newsletter', 'email.view_in_browser', '');
    \App\Core\Content::set('newsletter', 'email.read_online', '');
    $blankHtml = NR::renderIssue($longEv['newsletter'], 'email', ['unsubscribe_url' => 'https://example.org/unsubscribe/abc']);
    $blankText = NR::emailText($longEv['newsletter'], ['unsubscribe_url' => 'https://example.org/unsubscribe/abc']);
    \App\Core\Content::reset('newsletter', 'email.unsubscribe');
    \App\Core\Content::reset('newsletter', 'email.view_in_browser');
    \App\Core\Content::reset('newsletter', 'email.read_online');
    check('e-mail: a fixed word the owner clears falls back to its default, so the unsubscribe link never becomes invisible',
        str_contains($blankHtml, '>Unsubscribe</a>') && str_contains($blankHtml, '>View in browser</a>') && str_contains($blankHtml, '>Read this issue online</a>') && !str_contains($blankHtml, '"></a>') && str_contains($blankText, 'Unsubscribe'));
    $text = NR::emailText($R, ['unsubscribe_url' => 'https://example.org/unsubscribe/abc']);
    check('e-mail text part: readable, with links and the unsubscribe URL', str_contains($text, 'zz-selftest render') && str_contains($text, abs_url('programs')) && str_contains($text, 'https://example.org/unsubscribe/abc') && !str_contains($text, '<'));
    check('test e-mails carry the test notice', str_contains(NR::renderIssue($R, 'email', ['is_test' => true]), 'This is a test e-mail'));
    check('subject: own subject, else prefix + title', NR::subject($R) === 'zz-selftest render' && NR::subject(array_merge($R, ['email_subject' => 'Custom'])) === 'Custom');
    $pd = NR::previewDocument(NR::previewRow($R, ['title' => 'Unsaved <b>title</b>', 'content' => ['featured' => ['heading' => 'Live']]]), ['banner' => 'Preview']);
    check('preview of an UNSAVED state: nothing written, escaped', str_contains($pd, 'Unsaved title') && str_contains($pd, 'noindex') && str_contains($pd, 'Preview') && Newsletter::find((int) $R['id'])['title'] === 'zz-selftest render');
    check('seo: title, description, image, canonical', ($seo = NS::seo($R))['title'] === 'zz-selftest render' && $seo['description'] === 'Render summary' && $seo['og_image'] === 'assets/img/site/photo-04.jpg' && str_ends_with($seo['canonical'], '/newsletter/zz-selftest-render'));

    // =========================================================================================================== mailer: file + headers
    $files0 = count(glob(STORAGE_PATH . '/mail/*.eml') ?: []);
    Config::set('mail.driver', 'file');
    $r = Mailer::attempt('file@selftest.invalid', "Subject\r\nBcc: evil@example.org", 'Hi', ['headers' => ['List-Unsubscribe' => "<https://x.org/u>\r\nBcc: evil@example.org", 'Subject' => 'override', 'X-Test' => 'ok', 'Bad Name' => 'x'],
        'from_name' => "Evil\r\nBcc: x@y.z", 'reply_to' => "a@b.org\r\nBcc: x@y.z"]);
    $new = array_values(array_diff(glob(STORAGE_PATH . '/mail/*.eml') ?: [], []));
    $latest = $new === [] ? '' : (string) file_get_contents(end($new));
    check('header injection is impossible (subject, name, reply-to, extra headers)', $r['ok'] && count($new) === $files0 + 1 && !preg_match('/^Bcc:/mi', $latest) && !str_contains($latest, 'Reply-To') && str_contains($latest, 'X-Test: ok')
        && !str_contains($latest, 'override') && !str_contains($latest, 'Bad Name') && str_contains($latest, 'List-Unsubscribe:'));
    if ($new !== []) {
        @unlink(end($new));
    }
    check('every message has Date, Message-ID, MIME-Version', preg_match('/^Date: /m', $latest) === 1 && preg_match('/^Message-ID: <[a-f0-9]{24}@/m', $latest) === 1 && str_contains($latest, 'MIME-Version: 1.0'));
    check('invalid recipient: attempt() says why', Mailer::attempt('not-an-address', 'x', 'y')['ok'] === false && Mailer::lastError() !== null);

    // =========================================================================================================== SMTP against the mock server
    $plain = $nlMock(['--user=tester', '--pass=p@ss#1!%&=+$x'], 'plain');
    if ($plain === null) {
        check('mock SMTP server starts', false, 'could not start bin/dev/mock-smtp.php');
    } else {
        $cfg = ['host' => '127.0.0.1', 'port' => $plain['port'], 'username' => 'tester', 'password' => 'p@ss#1!%&=+$x', 'encryption' => 'none', 'timeout' => 4, 'cafile' => ''];
        Config::set('mail.driver', 'smtp');
        Config::set('mail.smtp', $cfg);
        $html = '<p>Hello</p><p>.dotted line</p>';
        $r = Mailer::attempt('Reader <r1@selftest.invalid>', 'x', 'y');
        check('a display-name address is not a valid bare recipient', $r['ok'] === false);
        $r = Mailer::attempt('r1@selftest.invalid', 'Héllo wörld — ünïcode subject ✓ with a really long tail that has to be folded into several encoded words, past seventy-five characters', "Plain\n.dot first\nlast ünï",
            ['html' => $html, 'headers' => ['List-Unsubscribe' => '<https://example.org/unsubscribe/' . str_repeat('a', 40) . '>, <mailto:unsub@example.org?subject=unsubscribe>', 'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click'],
                'from' => 'news@example.org', 'from_name' => 'Lumina, "Africa" Foundation', 'reply_to' => 'reply@example.org']);
        $box = $nlInbox($plain['dir']);
        $m = $box[0] ?? '';
        check('SMTP: AUTH PLAIN with a password full of special characters, message delivered', $r['ok'] && count($box) === 1, (string) ($r['error'] ?? ''));
        check('SMTP: envelope + headers (From quoted, Reply-To, List-Unsubscribe folded, Post header, Message-ID, Date)', str_contains($m, 'X-Mock-Mail-From: news@example.org') && str_contains($m, 'X-Mock-Rcpt-To: r1@selftest.invalid')
            && str_contains($m, 'From: "Lumina, \"Africa\" Foundation" <news@example.org>') && str_contains($m, 'Reply-To: reply@example.org') && str_contains($m, 'List-Unsubscribe-Post: List-Unsubscribe=One-Click')
            && preg_match('/^List-Unsubscribe: <https:\/\/example\.org\/unsubscribe\/a{40}>,\r\n <mailto:unsub@example\.org\?subject=unsubscribe>/m', $m) === 1 && preg_match('/^Message-ID: <[a-f0-9]{24}@example\.org>/m', $m) === 1);
        preg_match('/^Subject: (.*?)\r\n(?! )/ms', $m, $sm);
        $decoded = '';
        foreach (preg_match_all('/=\?UTF-8\?B\?([A-Za-z0-9+\/=]+)\?=/', $sm[1] ?? '', $mm) ? $mm[1] : [] as $w) {
            $decoded .= base64_decode($w);
        }
        $lineLens = array_map('strlen', explode("\r\n", $sm[1] ?? ''));
        check('SMTP: UTF-8 subject is RFC 2047 encoded in several words of at most 75 characters', str_contains($decoded, 'Héllo wörld — ünïcode subject ✓') && count($lineLens) >= 2 && max($lineLens) <= 75, json_encode($lineLens));
        check('SMTP: multipart/alternative with both parts decodable (UTF-8 text + HTML)', preg_match('/boundary="(lumina_[a-f0-9]{16})"/', $m, $bm) === 1
            && preg_match_all('/Content-Transfer-Encoding: base64\r\n\r\n([A-Za-z0-9+\/=\r\n]+?)--/s', $m, $parts) === 2 && str_contains(base64_decode(str_replace("\r\n", '', $parts[1][0])), "last ünï") && str_contains(base64_decode(str_replace("\r\n", '', $parts[1][1])), '.dotted line'));
        $r = Mailer::attempt('r2@selftest.invalid', 'Generated text part', '', ['html' => '<p>Only <a href="https://e.org/x">html</a> given</p>']);
        $m = ($nlInbox($plain['dir']))[1] ?? '';
        preg_match_all('/Content-Transfer-Encoding: base64\r\n\r\n([A-Za-z0-9+\/=\r\n]+?)--/s', $m, $parts);
        check('SMTP: the text part is generated from the HTML when none is given', $r['ok'] && str_contains(base64_decode(str_replace("\r\n", '', $parts[1][0] ?? '')), 'Only html (https://e.org/x) given'));

        // raw client: dot-stuffing, bare LF, connection reuse (the mock serves ONE connection at a time: release the one Mailer keeps open)
        Mailer::closeConnection();
        $client = new Smtp('127.0.0.1', $plain['port'], 'none', 'tester', 'p@ss#1!%&=+$x', 4);
        $raw = "From: a@example.org\nTo: b@example.org\nSubject: raw\n\n.leading dot\n..two dots\nnormal\n.\nafter lone dot\n";
        $client->send('a@example.org', ['dot@selftest.invalid'], $raw);
        $client->send('a@example.org', ['dot2@selftest.invalid'], $raw);
        $box = $nlInbox($plain['dir']);
        $dm = $box[2] ?? '';
        check('SMTP: dot-stuffing round trip, a lone "." line does not end the message', str_contains($dm, "\r\n.leading dot\r\n..two dots\r\nnormal\r\n.\r\nafter lone dot\r\n") && count($box) === 4);
        $client->close();
        $big = str_repeat("The quick brown fox jumps over the lazy dog 0123456789\n", 60000);       // about 3.3 MB
        $r = Mailer::attempt('big@selftest.invalid', 'Large body', $big);
        $box = $nlInbox($plain['dir']);
        check('SMTP: a large body (3 MB) is delivered intact', $r['ok'] && strlen($box[4] ?? '') > 4300000 && strlen(base64_decode(str_replace(["\r", "\n"], '', substr((string) strstr($box[4] ?? '', "\r\n\r\n"), 4)))) === strlen($big));

        // failures
        $r = Mailer::attempt('reject5x@selftest.invalid', 'x', 'y');
        check('SMTP: 550 is a permanent failure, with a clear message and no address', !$r['ok'] && $r['transient'] === false && str_contains((string) $r['error'], '550') && !str_contains((string) $r['error'], '@'));
        $r = Mailer::attempt('reject4x@selftest.invalid', 'x', 'y');
        check('SMTP: 451 is a transient failure', !$r['ok'] && $r['transient'] === true && str_contains((string) $r['error'], '451'));
        Config::set('mail.smtp', ['password' => 'WrongPassword123'] + $cfg);
        $r = Mailer::attempt('ok@selftest.invalid', 'x', 'y');
        check('SMTP: a wrong password fails permanently and the message never contains it', !$r['ok'] && $r['transient'] === false && str_contains((string) $r['error'], '535') && !str_contains((string) $r['error'], 'WrongPassword123') && !str_contains((string) Mailer::lastError(), 'WrongPassword123'));
        $logTail = (string) @file_get_contents(STORAGE_PATH . '/logs/app-' . date('Y-m-d') . '.log');
        check('SMTP: neither password reaches the log file', !str_contains($logTail, 'WrongPassword123') && !str_contains($logTail, 'p@ss#1!%'));
        Config::set('mail.smtp', ['username' => '', 'password' => ''] + $cfg);
        $r = Mailer::attempt('nologin@selftest.invalid', 'x', 'y');
        check('SMTP: a server that requires login refuses a client without one', !$r['ok'] && $r['transient'] === false);
        Config::set('mail.smtp', ['port' => 1] + $cfg);
        $r = Mailer::attempt('x@selftest.invalid', 'x', 'y');
        check('SMTP: connection refused is a transient failure with a clear message', !$r['ok'] && $r['transient'] === true && str_contains((string) $r['error'], 'Could not reach'));
        Config::set('mail.smtp', ['host' => ''] + $cfg);
        check('SMTP: missing MAIL_HOST is reported', !Mailer::attempt('x@selftest.invalid', 'x', 'y')['ok'] && str_contains((string) Mailer::lastError(), 'MAIL_HOST'));
        Config::set('mail.smtp', ['timeout' => 3] + $cfg);
        $t0 = microtime(true);
        $r = Mailer::attempt('slow1@selftest.invalid', 'x', 'y');
        check('SMTP: a server that stops answering times out (transient)', !$r['ok'] && $r['transient'] === true && microtime(true) - $t0 < 7 && str_contains((string) $r['error'], 'timeout'), (string) $r['error']);
        Config::set('mail.smtp', $cfg);
        $r = Mailer::attempt('dropconn@selftest.invalid', 'x', 'y');
        check('SMTP: a dropped connection is a transient failure', !$r['ok'] && $r['transient'] === true);
        check('SMTP: the next message after a failure works again (reconnect)', Mailer::attempt('after@selftest.invalid', 'x', 'y')['ok']);
        $n0 = count($nlInbox($plain['dir']));
        $all = true;
        for ($i = 0; $i < 6; $i++) {
            $all = $all && Mailer::attempt("batch{$i}@selftest.invalid", 'Batch ' . $i, 'body')['ok'];
        }
        check('SMTP: several messages over one reused connection', $all && count($nlInbox($plain['dir'])) === $n0 + 6);
        Mailer::closeConnection();

        // a message larger than the server's SIZE limit
        $small = $nlMock(['--max-size=20000'], 'small');
        if ($small !== null) {
            Config::set('mail.smtp', ['port' => $small['port'], 'username' => '', 'password' => ''] + $cfg);
            $r = Mailer::attempt('size@selftest.invalid', 'x', str_repeat('x', 40000));
            check('SMTP: a message above the server SIZE limit is refused permanently', !$r['ok'] && $r['transient'] === false && str_contains((string) $r['error'], 'larger'));
            Mailer::closeConnection();
        }
    }

    // TLS with certificate verification ON (a throwaway self-signed certificate trusted through MAIL_CAFILE)
    $certFile = STORAGE_PATH . '/mock-smtp/selftest/cert.pem';
    $made = trim((string) shell_exec('"' . PHP_BINARY . '" ' . escapeshellarg(dirname(__DIR__) . '/bin/dev/mock-smtp.php') . ' --make-cert --cert=' . escapeshellarg($certFile) . ' 2>&1'));
    if (!is_file($certFile)) {
        echo '  skip TLS checks (could not create a test certificate: ' . substr($made, 0, 120) . ")\n";
    } else {
        $st = $nlMock(['--tls=starttls', '--user=u', '--pass=p', '--cert=' . $certFile], 'starttls');
        $im = $nlMock(['--tls=implicit', '--cert=' . $certFile], 'implicit');
        $tlsCfg = ['host' => '127.0.0.1', 'username' => 'u', 'password' => 'p', 'timeout' => 5, 'cafile' => $certFile];
        Config::set('mail.driver', 'smtp');
        if ($st !== null) {
            Config::set('mail.smtp', $tlsCfg + ['port' => $st['port'], 'encryption' => 'tls']);
            $r = Mailer::attempt('tls@selftest.invalid', 'STARTTLS', 'secure');
            check('STARTTLS: certificate verified, AUTH over TLS, message delivered', $r['ok'] && count($nlInbox($st['dir'])) === 1, (string) $r['error']);
            Mailer::closeConnection();
            Config::set('mail.smtp', $tlsCfg + ['port' => $st['port'], 'encryption' => 'none']);
            $r = Mailer::attempt('plain@selftest.invalid', 'x', 'y');
            check('a server that offers login only after STARTTLS is not logged into in clear text', !$r['ok'] && str_contains((string) $r['error'], 'login method'));
            Mailer::closeConnection();
            Config::set('mail.smtp', ['cafile' => ''] + $tlsCfg + ['port' => $st['port'], 'encryption' => 'tls']);
            $r = Mailer::attempt('noca@selftest.invalid', 'x', 'y');
            check('STARTTLS: an untrusted (self-signed) certificate is REFUSED when it is not in the CA file', !$r['ok'] && str_contains((string) $r['error'], 'TLS') && count($nlInbox($st['dir'])) === 1, json_encode($r) . ' inbox=' . count($nlInbox($st['dir'])));
            Mailer::closeConnection();
        }
        if ($im !== null) {
            Config::set('mail.smtp', ['username' => '', 'password' => ''] + $tlsCfg + ['port' => $im['port'], 'encryption' => 'ssl']);
            $r = Mailer::attempt('ssl@selftest.invalid', 'Implicit TLS', 'secure');
            check('implicit TLS (ssl): certificate verified, message delivered', $r['ok'] && count($nlInbox($im['dir'])) === 1, (string) $r['error']);
            Mailer::closeConnection();
            Config::set('mail.smtp', ['username' => '', 'password' => '', 'cafile' => ''] + $tlsCfg + ['port' => $im['port'], 'encryption' => 'ssl']);
            $r = Mailer::attempt('ssl2@selftest.invalid', 'x', 'y');
            check('implicit TLS: an untrusted certificate is REFUSED', !$r['ok'] && count($nlInbox($im['dir'])) === 1, (string) $r['error']);
            Mailer::closeConnection();
        }
    }
    Config::set('mail.driver', $nlMailConfig[0]);
    Config::set('mail.smtp', $nlMailConfig[1]);

    // =========================================================================================================== one-click unsubscribe (RFC 8058) over HTTP
    $tok = bin2hex(random_bytes(20));
    $sid = Database::insert('subscribers', ['email' => 'oneclick@selftest.invalid', 'status' => 'subscribed', 'token' => $tok]);
    $http = static function (string $method, string $path, string $body, array $headers = []): array {
        $ctx = stream_context_create(['http' => ['method' => $method, 'header' => implode("\r\n", array_merge(['Content-Type: application/x-www-form-urlencoded'], $headers)), 'content' => $body, 'timeout' => 8, 'ignore_errors' => true]]);
        $out = @file_get_contents('http://localhost/' . basename(BASE_PATH) . $path, false, $ctx);
        $code = 0;
        foreach ($http_response_header ?? [] as $h) {
            if (preg_match('~^HTTP/\S+ (\d{3})~', $h, $m)) {
                $code = (int) $m[1];
            }
        }
        return [$code, (string) $out, $http_response_header ?? []];
    };
    [$probe] = $http('GET', '/', '');
    if ($probe !== 200) {
        echo '  skip one-click unsubscribe checks (the site does not answer at http://localhost/' . basename(BASE_PATH) . "/)
";
    } else {
        [$code] = $http('POST', '/unsubscribe/' . $tok, 'nothing=1');
        check('unsubscribe form POST without a CSRF token is still refused', $code === 403 && Database::value('SELECT status FROM subscribers WHERE id = ?', [$sid]) === 'subscribed');
        [$code, $body, $hdr] = $http('POST', '/unsubscribe/' . $tok, 'List-Unsubscribe=One-Click');
        $cookie = array_filter($hdr, static fn(string $h): bool => stripos($h, 'set-cookie:') === 0);
        check('one-click POST unsubscribes at once: 200, no redirect, no cookie', $code === 200 && trim($body) === 'OK' && Database::value('SELECT status FROM subscribers WHERE id = ?', [$sid]) === 'unsubscribed'
            && Database::value('SELECT unsubscribed_at FROM subscribers WHERE id = ?', [$sid]) !== null && $cookie === []);
        [$code] = $http('POST', '/unsubscribe/' . $tok, 'List-Unsubscribe=One-Click');
        check('one-click POST is idempotent; a wrong token is 404', $code === 200 && $http('POST', '/unsubscribe/' . str_repeat('0', 40), 'List-Unsubscribe=One-Click')[0] === 404);
        [$code] = $http('GET', '/unsubscribe/' . $tok, '');
        check('the unsubscribe page (GET) still works', $code === 200);
    }

    // public pages: drafts never reachable, a published issue is
    if (($http ?? null) !== null && $probe === 200) {
        $pubIssue = NS::create(['title' => 'zz-selftest public', 'content' => $valid]);
        $slug = $pubIssue['newsletter']['slug'];
        check('a draft is 404 on every public URL', $http('GET', '/newsletter/' . $slug, '')[0] === 404 && $http('GET', '/api/newsletters/' . $slug, '')[0] === 404);
        NS::publish((int) $pubIssue['newsletter']['id']);
        [$code, $body] = $http('GET', '/newsletter/' . $slug, '');
        check('a published issue is 200 with canonical, Open Graph and NewsArticle JSON-LD', $code === 200 && str_contains($body, '"@type":"NewsArticle"') && str_contains($body, 'rel="canonical"') && str_contains($body, 'og:type" content="article"'));
        [$code, $body] = $http('GET', '/api/newsletters/' . $slug, '');
        $j = json_decode($body, true);
        check('public JSON API: one issue with content and html', $code === 200 && ($j['data']['slug'] ?? '') === $slug && isset($j['data']['content']['blocks']) && str_contains((string) ($j['data']['html'] ?? ''), 'nl-issue'));
        $found = false;
        $code = 0;
        for ($pg = 1; $pg <= 30 && !$found; $pg++) {            // a database full of issues paginates the archive: look through the pages
            [$code, $body] = $http('GET', '/newsletter/archive' . ($pg > 1 ? '?page=' . $pg : ''), '');
            if ($code !== 200) {
                break;
            }
            $found = str_contains($body, 'zz-selftest public');
        }
        check('archive lists the published issue', $found, 'last status ' . $code);
    }
} finally {
    $nlCleanup();
}

if (!empty($standalone)) {
    echo $fail === 0 ? "\nALL CHECKS PASSED\n" : "\n{$fail} CHECK(S) FAILED\n";
    exit($fail === 0 ? 0 : 1);
}
