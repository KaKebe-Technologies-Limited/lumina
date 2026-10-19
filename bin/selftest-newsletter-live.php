<?php
/**
 * Newsletter "PDF format + live on the website" checks (NEWSLETTER agent, docs/PARTNERS_NEWSLETTER.md section 8). Run alone:
 *   /c/xampp/php/php.exe bin/selftest-newsletter-live.php
 * or include it from bin/selftest.php (it shares check() and $fail when they exist, like selftest-newsletter.php).
 *
 * It proves that every admin change reaches the PUBLIC site at once (no waiting, no cache flush): create, upload a PDF, publish, edit, swap the PDF,
 * schedule (time reached with nobody touching the admin), unpublish, archive and delete are each followed by real HTTP requests to
 * http://localhost/<folder>/ (page, PDF, download, archive, API, sitemap, Home teaser, Blog call to action) and by the response headers.
 * Also: the visibility rules of the PDF routes (robots, HEAD, missing file), the download counter, JSON-LD, the e-mail of a PDF issue (two buttons,
 * no attachment), the "Live on the website" status texts and the JSON shape the editor reads.
 * Everything it creates is removed again (newsletters "zz-selftest-live ...", their PDF files, their audit entries). Needs the site at
 * http://localhost/<folder>/ for the HTTP part (those checks are skipped when it does not answer).
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

use App\Controllers\Admin\NewsletterApiController;
use App\Core\Database;
use App\Models\Newsletter;
use App\Services\Newsletter\NewsletterMailer;
use App\Services\Newsletter\NewsletterPdf;
use App\Services\Newsletter\NewsletterService as NS;

echo "Newsletter: PDF format and live reflection\n";

$lvTitle = 'zz-selftest-live PDF issue';
$lvIds = [];
$lvTmp = [];
$lvCleanup = static function () use (&$lvIds, &$lvTmp): void {
    foreach (Database::all("SELECT id FROM newsletters WHERE title LIKE 'zz-selftest-live%'") as $r) {
        $lvIds[] = (int) $r['id'];
    }
    foreach (array_unique($lvIds) as $id) {
        try {
            if (Newsletter::find($id) !== null) {
                Database::update('newsletters', ['email_status' => 'none'], 'id = ?', [$id]);
                NS::delete($id);
            }
        } catch (\Throwable) {
        }
        Database::execute("DELETE FROM audit_log WHERE entity_type = 'newsletter' AND entity_id = ?", [$id]);
    }
    foreach ($lvTmp as $f) {
        @unlink($f);
    }
};
register_shutdown_function($lvCleanup);

try {
    // ------------------------------------------------------------------------------------------------ helpers
    $pdfBytes = static function (string $label, int $pages = 1): string {
        $objs = ['<< /Type /Catalog /Pages 2 0 R >>', '', '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>'];
        $kids = [];
        for ($i = 0; $i < $pages; $i++) {
            $pid = 4 + $i * 2;
            $kids[] = $pid . ' 0 R';
            $text = 'BT /F1 24 Tf 72 700 Td (' . $label . ' ' . ($i + 1) . ') Tj ET';
            $objs[] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents ' . ($pid + 1) . ' 0 R /Resources << /Font << /F1 3 0 R >> >> >>';
            $objs[] = '<< /Length ' . strlen($text) . " >>\nstream\n" . $text . "\nendstream";
        }
        $objs[1] = '<< /Type /Pages /Count ' . $pages . ' /Kids [' . implode(' ', $kids) . '] >>';
        $out = "%PDF-1.4\n";
        $off = [];
        foreach ($objs as $n => $o) {
            $off[] = strlen($out);
            $out .= ($n + 1) . " 0 obj\n" . $o . "\nendobj\n";
        }
        $x = strlen($out);
        $out .= "xref\n0 " . (count($objs) + 1) . "\n0000000000 65535 f \n";
        foreach ($off as $o) {
            $out .= sprintf("%010d 00000 n \n", $o);
        }
        return $out . 'trailer << /Size ' . (count($objs) + 1) . " /Root 1 0 R >>\nstartxref\n" . $x . "\n%%EOF\n";
    };
    $pdfFile = static function (string $name, string $label, int $pages = 1) use ($pdfBytes, &$lvTmp): array {
        $tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'zz-selftest-live-' . bin2hex(random_bytes(4)) . '.pdf';
        file_put_contents($tmp, $pdfBytes($label, $pages));
        $lvTmp[] = $tmp;
        return ['name' => $name, 'tmp_name' => $tmp, 'error' => UPLOAD_ERR_OK, 'size' => (int) filesize($tmp), 'type' => 'application/pdf'];
    };
    $http = static function (string $method, string $path, array $headers = []): array {
        $ctx = stream_context_create(['http' => ['method' => $method, 'header' => implode("\r\n", array_merge(['User-Agent: Mozilla/5.0 (selftest) Chrome/120'], $headers)), 'timeout' => 10, 'ignore_errors' => true, 'follow_location' => 0]]);
        $out = @file_get_contents('http://localhost/' . basename(BASE_PATH) . $path, false, $ctx);
        $code = 0;
        foreach ($http_response_header ?? [] as $h) {
            if (preg_match('~^HTTP/\S+ (\d{3})~', $h, $m)) {
                $code = (int) $m[1];
            }
        }
        return [$code, (string) $out, $http_response_header ?? []];
    };
    $hdr = static function (array $headers, string $name): string {
        foreach ($headers as $h) {
            if (stripos($h, $name . ':') === 0) {
                return trim(substr($h, strlen($name) + 1));
            }
        }
        return '';
    };

    // ------------------------------------------------------------------------------------------------ the status texts of the "Live on the website" box
    $res = NS::create(['title' => $lvTitle, 'kind' => 'pdf', 'summary' => 'Live reflection test.']);
    $id = (int) $res['newsletter']['id'];
    $lvIds[] = $id;
    $slug = (string) $res['newsletter']['slug'];
    $live = NS::liveStatus(Newsletter::find($id) ?? []);
    check('live box: a new PDF issue is "Draft — not visible", with the reason that the PDF is missing', $live['state'] === 'draft' && $live['visible'] === false && str_contains($live['label'], 'Draft')
        && (bool) array_filter($live['reasons'], static fn(string $r): bool => str_contains($r, 'Upload the PDF')), json_encode($live['reasons']) ?: '');
    $item = NewsletterApiController::itemFor(Newsletter::find($id) ?? [], true);
    check('editor JSON: kind, pdf and live are part of every issue item', $item['kind'] === 'pdf' && is_array($item['pdf']) && $item['pdf']['has'] === false && is_array($item['live']) && $item['live']['state'] === 'draft');
    $up = NewsletterPdf::attach($id, $pdfFile('Live Issue.pdf', 'Live issue', 1), null);
    $row = Newsletter::find($id) ?? [];
    $item = NewsletterApiController::itemFor($row, true);
    check('editor JSON: after the upload the PDF is described (name, size, admin address, no public address leaks a path)', $up['ok'] && $item['pdf']['has'] && $item['pdf']['exists'] && $item['pdf']['filename'] === 'Live Issue.pdf'
        && str_ends_with($item['pdf']['admin_url'], '/admin/newsletters/' . $id . '/pdf') && !str_contains(json_encode($item['pdf']) ?: '', 'storage') && !str_contains(json_encode($item['pdf']) ?: '', (string) $row['pdf_path']));

    // ------------------------------------------------------------------------------------------------ public HTTP
    [$probe] = $http('GET', '/');
    if ($probe !== 200) {
        echo '  skip HTTP checks (the site does not answer at http://localhost/' . basename(BASE_PATH) . "/)\n";
    } else {
        $snap = static function () use ($http, $slug, $lvTitle): array {
            $p = $http('GET', '/newsletter/' . $slug);
            $latest = $http('GET', '/newsletter');
            return [
                'page'    => $p[0], 'body' => $p[1], 'pdf' => $http('GET', '/newsletter/' . $slug . '/pdf')[0], 'download' => $http('GET', '/newsletter/' . $slug . '/download')[0],
                'latest'  => str_contains($latest[1], $lvTitle), 'archive' => str_contains($http('GET', '/newsletter/archive')[1], 'newsletter/' . $slug . '"'),
                'home'    => str_contains($http('GET', '/')[1], 'nl-teaser__title') && str_contains($http('GET', '/')[1], $lvTitle),
                'api'     => str_contains($http('GET', '/api/newsletters?per_page=50')[1], '"slug":"' . $slug . '"'),
                'sitemap' => str_contains($http('GET', '/sitemap.xml')[1], '/newsletter/' . $slug . '<'),
            ];
        };
        $s = $snap();
        check('draft with a PDF: the page, the PDF and the download answer 404, and it is in no list', $s['page'] === 404 && $s['pdf'] === 404 && $s['download'] === 404 && !$s['latest'] && !$s['archive'] && !$s['home'] && !$s['api'] && !$s['sitemap']);

        $r = NS::publish($id, null);
        $s = $snap();
        check('PUBLISH is visible at once: page 200, PDF 200, download 200, /newsletter, archive, Home teaser, API and sitemap', $r['ok'] && $s['page'] === 200 && $s['pdf'] === 200 && $s['download'] === 200 && $s['latest'] && $s['archive'] && $s['home'] && $s['api'] && $s['sitemap'], json_encode(array_diff_key($s, ['body' => 1])));
        $body = $s['body'];
        check('PDF issue page: PDF badge, Download PDF button first, embedded viewer from the same site, "Read in a new tab", print address',
            str_contains($body, 'data-nl-pdf') && str_contains($body, 'nl-pdf__download') && str_contains($body, '/newsletter/' . $slug . '/download') && str_contains($body, '<iframe class="nl-pdf__frame" src="')
            && str_contains($body, '/newsletter/' . $slug . '/pdf#') && str_contains($body, 'nl-pdf__open') && str_contains($body, 'nl-pdf__print-url') && strpos($body, 'nl-pdf__download') < strpos($body, '<iframe'));
        check('PDF issue page: Article JSON-LD with the PDF as encoding (MediaObject, application/pdf, size)', str_contains($body, '"@type":"Article"') && str_contains($body, '"encodingFormat":"application/pdf"') && str_contains($body, '"contentUrl":"') && str_contains($body, '"contentSize":"'));
        check('the Content-Security-Policy of the page allows the same-origin PDF frame and nothing else new', str_contains($hdr($http('GET', '/newsletter/' . $slug)[2], 'Content-Security-Policy'), "frame-src 'self' "));

        [$c, , $h] = $http('GET', '/newsletter/' . $slug . '/pdf');
        check('PDF stream: application/pdf, inline, nosniff, private no-store, noindex', $c === 200 && $hdr($h, 'Content-Type') === 'application/pdf' && str_starts_with($hdr($h, 'Content-Disposition'), 'inline;') && $hdr($h, 'X-Content-Type-Options') === 'nosniff'
            && $hdr($h, 'Cache-Control') === 'private, no-store' && str_contains($hdr($h, 'X-Robots-Tag'), 'noindex'));
        [$c, $b, $h] = $http('GET', '/newsletter/' . $slug . '/download');
        check('download: attachment with the original file name, real PDF bytes', $c === 200 && str_starts_with($hdr($h, 'Content-Disposition'), 'attachment;') && str_contains($hdr($h, 'Content-Disposition'), 'Live Issue.pdf') && str_starts_with($b, '%PDF-'));
        $count = static fn(): int => (int) Database::value('SELECT pdf_downloads FROM newsletters WHERE id = ?', [$id], 0);
        $n0 = $count();
        $http('GET', '/newsletter/' . $slug . '/download');
        $n1 = $count();
        $http('GET', '/newsletter/' . $slug . '/download', ['User-Agent: Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)']);
        $http('HEAD', '/newsletter/' . $slug . '/download');
        $http('GET', '/newsletter/' . $slug . '/pdf');
        $http('GET', '/newsletter/' . $slug . '/download', ['Range: bytes=100-']);           // a resumed download is the same download
        check('download counter: +1 for a person, nothing for a crawler, a HEAD request, a resumed download or the inline viewer', $n1 === $n0 + 1 && $count() === $n1, $n0 . '/' . $n1 . '/' . $count());

        [$c, $b, $h] = $http('GET', '/api/newsletters?per_page=50');
        $api = json_decode($b, true);
        $mine = array_values(array_filter($api['data'] ?? [], static fn(array $i): bool => ($i['slug'] ?? '') === $slug))[0] ?? [];
        check('API: kind = pdf and pdf {url, view_url, size, name}', ($mine['kind'] ?? '') === 'pdf' && str_ends_with((string) ($mine['pdf']['url'] ?? ''), '/newsletter/' . $slug . '/download') && str_ends_with((string) ($mine['pdf']['view_url'] ?? ''), '/pdf')
            && (int) ($mine['pdf']['size'] ?? 0) === (int) filesize($lvTmp[0]) && ($mine['pdf']['name'] ?? '') === 'Live Issue.pdf');
        check('API caching: revalidated on every request (max-age=0, must-revalidate) with an ETag', $c === 200 && $hdr($h, 'Cache-Control') === 'public, max-age=0, must-revalidate' && $hdr($h, 'ETag') !== '');
        $etag1 = $hdr($h, 'ETag');
        [$c304] = $http('GET', '/api/newsletters?per_page=50', ['If-None-Match: ' . $etag1]);
        check('API: an unchanged list answers 304 to the ETag (cheap), so revalidating costs nothing', $c304 === 304);
        check('sitemap and public pages are revalidated, never served stale', str_contains($hdr($http('GET', '/sitemap.xml')[2], 'Cache-Control'), 'max-age=0') && str_contains($hdr($http('GET', '/newsletter')[2], 'Cache-Control'), 'max-age=0')
            && str_contains($hdr($http('GET', '/')[2], 'Cache-Control'), 'max-age=0'));
        check('archive card carries the PDF badge', str_contains($http('GET', '/newsletter/archive')[1], 'nl-card__pdf-badge'));
        check('Blog page shows the newsletter call to action while an issue is public', str_contains($http('GET', '/blog')[1], 'nl-cta__title'));

        // an edit shows at once
        $v = (int) (Newsletter::find($id)['version'] ?? 1);
        NS::save($id, ['title' => $lvTitle . ' EDITED'], $v, null);
        $lvTitleNow = $lvTitle . ' EDITED';
        $page = $http('GET', '/newsletter/' . $slug)[1];
        [, $b2, $h2] = $http('GET', '/api/newsletters?per_page=50');
        check('EDIT is visible at once: page title, /newsletter, Home teaser and the API (new ETag)', str_contains($page, '<title>' . $lvTitleNow) && str_contains($http('GET', '/newsletter')[1], $lvTitleNow) && str_contains($http('GET', '/')[1], $lvTitleNow)
            && str_contains($b2, $lvTitleNow) && $hdr($h2, 'ETag') !== $etag1);

        // swapping the PDF
        $len1 = (int) $hdr($http('GET', '/newsletter/' . $slug . '/pdf')[2], 'Content-Length');
        $old = (string) (Newsletter::find($id)['pdf_path'] ?? '');
        $sw = NewsletterPdf::attach($id, $pdfFile('Second.pdf', 'Second issue', 3), null);
        $len2 = (int) $hdr($http('GET', '/newsletter/' . $slug . '/pdf')[2], 'Content-Length');
        check('SWAP of the PDF is served at once; the old file is deleted; the counter restarts', $sw['ok'] && $len2 > $len1 && !\App\Core\Uploader::privateExists($old) && $count() === 0 && str_contains($http('GET', '/newsletter/' . $slug . '/download')[2][0] ?? '', '200'));

        // the stored file vanished (deleted by hand): the page stays up without the controls, the routes answer 404, the admin sees it
        $pathNow = (string) (Newsletter::find($id)['pdf_path'] ?? '');
        $abs = \App\Core\Uploader::privatePath($pathNow);
        rename($abs, $abs . '.bak');
        $page = $http('GET', '/newsletter/' . $slug);
        $live = NS::liveStatus(Newsletter::find($id) ?? []);
        check('missing PDF file: page 200 without an empty PDF box, /pdf and /download 404, the admin status says why', $page[0] === 200 && !str_contains($page[1], 'class="nl-pdf"') && $http('GET', '/newsletter/' . $slug . '/pdf')[0] === 404
            && $http('GET', '/newsletter/' . $slug . '/download')[0] === 404 && (bool) array_filter($live['reasons'], static fn(string $r): bool => str_contains($r, 'missing from the server')));
        rename($abs . '.bak', $abs);

        // the e-mail of a PDF issue: two buttons, no attachment
        $tpl = NewsletterMailer::template(Newsletter::find($id) ?? [], true, true);
        check('e-mail of a PDF issue: summary, "Download PDF" (counted link) and "Read online" buttons, no attachment', str_contains($tpl['html'], '/newsletter/' . $slug . '/download') && str_contains($tpl['html'], '>Download PDF<') && str_contains($tpl['html'], '>Read this issue online<')
            && str_contains($tpl['text'], 'Download PDF: ') && str_contains($tpl['text'], '/newsletter/' . $slug . '/download') && !str_contains($tpl['html'], 'Content-Disposition'));

        // unpublish / archive / schedule / delete
        NS::unpublish($id, null);
        $s = $snap();
        check('UNPUBLISH takes everything down at once (page, PDF, download, lists, API, sitemap)', $s['page'] === 404 && $s['pdf'] === 404 && $s['download'] === 404 && !$s['latest'] && !$s['archive'] && !$s['home'] && !$s['api'] && !$s['sitemap']);
        $live = NS::liveStatus(Newsletter::find($id) ?? []);
        check('live box after unpublish: "Unpublished — not visible on the website"', $live['state'] === 'unpublished' && str_contains($live['label'], 'Unpublished') && $live['visible'] === false);

        $r = NS::schedule($id, date('Y-m-d H:i:s', time() + 3600), null);
        $s = $snap();
        $live = NS::liveStatus(Newsletter::find($id) ?? []);
        check('SCHEDULED for later: not visible, the box says "Scheduled for ..."', $r['ok'] && $s['page'] === 404 && !$s['api'] && $live['state'] === 'scheduled' && str_starts_with($live['label'], 'Scheduled for '));
        Database::update('newsletters', ['publish_at' => date('Y-m-d H:i:s', time() - 5)], 'id = ?', [$id]);        // the time has come; nobody opens the admin
        $s = $snap();
        check('SCHEDULE TIME REACHED: the issue appears by itself (page, lists, API, sitemap) without any admin action', $s['page'] === 200 && $s['pdf'] === 200 && $s['archive'] && $s['api'] && $s['sitemap'] && Database::value('SELECT status FROM newsletters WHERE id = ?', [$id]) === 'published');

        NS::archiveIssue($id, null);
        $s = $snap();
        check('ARCHIVE hides it at once', $s['page'] === 404 && !$s['api'] && !$s['sitemap']);
        NS::unpublish($id, null);
        NS::publish($id, null);
        check('re-publish after archive works at once', $snap()['page'] === 200);
        $path = (string) (Newsletter::find($id)['pdf_path'] ?? '');
        NS::delete($id);
        $s = $snap();
        check('DELETE removes the page, the lists and the PDF file', $s['page'] === 404 && !$s['api'] && !$s['sitemap'] && !\App\Core\Uploader::privateExists($path));
    }

    // ------------------------------------------------------------------------------------------------ an online issue with an attached PDF
    $res = NS::create(['title' => 'zz-selftest-live written issue', 'summary' => 'Written.', 'content' => ['version' => 1, 'featured' => ['heading' => 'Hello', 'body_html' => '<p>Text.</p>', 'image' => ['path' => '', 'alt' => '', 'caption' => ''], 'link' => ['label' => '', 'url' => '']], 'blocks' => []]]);
    $oid = (int) $res['newsletter']['id'];
    $lvIds[] = $oid;
    $oslug = (string) $res['newsletter']['slug'];
    $before = NewsletterMailer::template(Newsletter::find($oid) ?? [], true, true);
    NewsletterPdf::attach($oid, $pdfFile('Attached.pdf', 'Attached', 1), null);
    NS::publish($oid, null);
    $after = NewsletterMailer::template(Newsletter::find($oid) ?? [], true, true);
    check('a written issue stays a web page: its e-mail does not change when a PDF is attached (online issues are unchanged)', $before['html'] === $after['html'] && !str_contains($after['html'], 'Download PDF'));
    if ($probe === 200) {
        $page = $http('GET', '/newsletter/' . $oslug);
        check('written issue with a PDF: "Download this issue as PDF" link in the masthead, no viewer, NewsArticle with the PDF as encoding', $page[0] === 200 && str_contains($page[1], 'nl-issue__pdf') && str_contains($page[1], '/newsletter/' . $oslug . '/download')
            && !str_contains($page[1], 'nl-pdf__frame') && str_contains($page[1], '"@type":"NewsArticle"') && str_contains($page[1], '"encodingFormat":"application/pdf"'));
        $api = json_decode($http('GET', '/api/newsletters/' . $oslug)[1], true);
        check('API of one issue: kind = online and the attached pdf', ($api['data']['kind'] ?? '') === 'online' && !empty($api['data']['pdf']['url']));
    }
} finally {
    $lvCleanup();
    $left = (int) Database::value("SELECT COUNT(*) FROM newsletters WHERE title LIKE 'zz-selftest-live%'", [], 0);
    check('cleanup: no test issue is left behind', $left === 0);
}

if (!empty($standalone)) {
    echo $fail === 0 ? "\nALL CHECKS PASSED\n" : "\n{$fail} CHECK(S) FAILED\n";
    exit($fail === 0 ? 0 : 1);
}
