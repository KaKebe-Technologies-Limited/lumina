<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Controller;
use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Services\Newsletter\NewsletterPdf;
use App\Services\Newsletter\NewsletterService;

/**
 * PUBLIC PDF routes of a newsletter issue. OWNER: NEWSLETTER agent (docs/PARTNERS_NEWSLETTER.md section 5). The architect delivered a WORKING
 * baseline; the URLs and the visibility rule are unchanged.
 *
 *   GET /newsletter/{slug}/pdf       the PDF inline (for the embedded viewer iframe and "Read in a new tab"); never counted
 *   GET /newsletter/{slug}/download  the PDF as an attachment, +1 on the download counter
 *
 * Rule: ONLY an issue NewsletterService treats as visible (published, or scheduled and due) with a stored PDF is served; everything else is 404
 * (drafts, archived, unpublished, deleted, no PDF, file gone from the disk), so unpublishing stops the file at once. The file is read from
 * storage/private by a path taken from the database row, never from the request ({slug} is matched against the table, nothing else is used).
 * Responses are Cache-Control private, no-store (nothing keeps a stale copy of an issue that was swapped or taken down), noindex and nosniff.
 *
 * Download counter: one +1 per real GET of /download. HEAD requests, resumed downloads (Range from byte 1 on) and well-known crawlers and link-preview robots do not count (the
 * counter then says "people who pressed Download", not "everything that fetched the URL"). It is a plain counter, not analytics.
 */
final class NewsletterPdfController extends Controller
{
    /** substrings (lower-case) of the User-Agent of robots that must not move the download counter */
    private const ROBOTS = ['bot', 'crawl', 'spider', 'slurp', 'facebookexternalhit', 'preview', 'headless', 'lighthouse', 'pingdom', 'uptime', 'monitor', 'whatsapp', 'telegram'];

    public function view(Request $request, string $slug): Response
    {
        return $this->serve($slug, true, false, $request->isMethod('HEAD'));
    }

    public function download(Request $request, string $slug): Response
    {
        return $this->serve($slug, false, !$request->isMethod('HEAD') && !$this->isRobot($request) && !$this->isContinuation($request), $request->isMethod('HEAD'));
    }

    private function serve(string $slug, bool $inline, bool $count, bool $head = false): Response
    {
        $issue = NewsletterService::findPublishedBySlug($slug);
        // the file must really be there: a row that points at a deleted file answers 404 (never a 500, never a counter increase)
        if ($issue === null || !NewsletterPdf::exists($issue)) {
            throw new HttpException(404, 'Not found');
        }
        $response = NewsletterPdf::response($issue, $inline)->header('X-Robots-Tag', 'noindex, nofollow');
        if ($head) {
            return new Response('', 200, $response->headers());          // HEAD: the same headers (type, size, disposition), without reading a 25 MB file for nothing
        }
        if ($count) {
            NewsletterPdf::recordDownload((int) $issue['id']);
        }
        return $response;
    }

    /** A resumed download ("Range: bytes=N-" with N > 0, which Apache answers with 206) is the same download continuing: not counted again. */
    private function isContinuation(Request $request): bool
    {
        return preg_match('/^bytes=(\d+)-/i', (string) $request->header('Range', ''), $m) === 1 && (int) $m[1] > 0;
    }

    private function isRobot(Request $request): bool
    {
        $ua = strtolower((string) $request->header('User-Agent', ''));
        if ($ua === '') {
            return false;
        }
        foreach (self::ROBOTS as $needle) {
            if (str_contains($ua, $needle)) {
                return true;
            }
        }
        return false;
    }
}
