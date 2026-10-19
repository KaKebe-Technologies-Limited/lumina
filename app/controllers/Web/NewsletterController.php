<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Controller;
use App\Core\Database;
use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Core\SitePages;
use App\Services\Newsletter\NewsletterContent;
use App\Services\Newsletter\NewsletterPdf;
use App\Services\Newsletter\NewsletterRenderer;
use App\Services\Newsletter\NewsletterService;
use App\Services\Web\ShareLinks;

/**
 * PUBLIC newsletter: /newsletter (the current issue), /newsletter/archive, /newsletter/{slug}, and the read-only JSON API
 * GET /api/newsletters and GET /api/newsletters/{slug}. Contract: docs/NEWSLETTER.md.
 * OWNER: WEB agent (this controller, views/web/newsletter/*, scss/pages/_newsletter.scss, assets/js/newsletter.js, SitemapBuilder additions).
 *
 * Rules (do not break): ONLY issues that NewsletterService treats as visible are reachable (published, or scheduled with publish_at <= now;
 * drafts and archived issues are 404). Every query goes through NewsletterService (the shared visibleSql predicate). The issue itself is
 * rendered ONLY by NewsletterRenderer::renderIssue($row, 'public'): do not duplicate its markup here. previewResponse() is called by the
 * admin editor for "Preview in new tab": keep its signature.
 */
final class NewsletterController extends Controller
{
    private const PER_PAGE = 12;
    private const API_MAX_PER_PAGE = 50;
    /**
     * LIVE REFLECTION: the JSON API revalidates on every request (the ETag makes the answer a cheap 304 while nothing changed), so a newly
     * published, edited, unpublished or deleted issue is visible to API readers, browsers and any CDN at once. (It used to be max-age=60.)
     */
    private const API_CACHE = 'public, max-age=0, must-revalidate';

    /** GET /newsletter - the newest published issue, or the friendly empty state. */
    public function index(Request $request): Response
    {
        $issue = NewsletterService::latestPublished();
        if ($issue === null) {
            return $this->renderPage('newsletter', 'web/newsletter/empty', [], [
                'meta'        => ['robots' => 'noindex,follow', 'canonical' => '/newsletter'],
                'breadcrumbs' => [[HomeSupport::navLabel('newsletter', 'Newsletter'), null]],
                'bodyClass'   => 'page-newsletter-empty',
            ]);
        }
        return $this->issueResponse($issue, true);
    }

    /** GET /newsletter/archive?page= - paginated grid of the published issues (the current one included), newest first. */
    public function archive(Request $request): Response
    {
        $p = NewsletterService::archive($request->page(), self::PER_PAGE);
        $seoTitle = trim((string) content('newsletter', 'archive.seo_title'));
        $seoDesc = trim((string) content('newsletter', 'archive.seo_description'));
        $label = HomeSupport::navLabel('newsletter', 'Newsletter');
        return $this->renderPage('newsletter', 'web/newsletter/archive', ['pagination' => $p, 'issues' => $p->items], [
            'meta'        => [
                'title'       => $seoTitle !== '' ? $seoTitle : 'Newsletter archive',
                'description' => $seoDesc,
                'canonical'   => '/newsletter/archive' . ($p->page > 1 ? '?page=' . $p->page : ''),
                'robots'      => $p->total === 0 ? 'noindex,follow' : '',
            ],
            'breadcrumbs' => [[$label, url('newsletter')], [strip_highlight((string) content('newsletter', 'archive.title')) ?: 'Archive', null]],
            'bodyClass'   => 'page-newsletter-archive',
        ]);
    }

    /** GET /newsletter/{slug} - any published issue. */
    public function show(Request $request, string $slug): Response
    {
        $issue = NewsletterService::findPublishedBySlug($slug);
        if ($issue === null) {
            throw new HttpException(404, 'Not found');
        }
        return $this->issueResponse($issue, (int) $issue['id'] === $this->latestId());
    }

    /**
     * Admin "Preview in new tab": the page as visitors would see it (site header and footer included) for ANY issue row, with a banner,
     * noindex, no-store. $issue may be an unsaved editor state (see NewsletterRenderer::previewRow()). Nothing is written.
     * Called by Admin\NewslettersController (EDITOR agent); signature is part of the contract.
     *
     * @param array<string,mixed> $opts banner (string; default depends on the status: live / scheduled / not published)
     */
    public function previewResponse(array $issue, array $opts = []): Response
    {
        $issue = NewsletterRenderer::prepare($issue);
        if (isset($opts['banner'])) {
            $banner = trim((string) $opts['banner']);
        } elseif (NewsletterService::isVisible($issue)) {
            $banner = 'Preview of the live issue (visitors can already see it)';
        } elseif ((string) ($issue['status'] ?? '') === 'scheduled') {
            $banner = 'Preview — scheduled, not published yet';
        } else {
            $banner = 'Preview — not published';
        }
        $response = $this->renderIssueView($issue, false, true, $banner);
        return $response->withHeaders(['Cache-Control' => 'no-store, max-age=0', 'X-Robots-Tag' => 'noindex, nofollow']);
    }

    // ------------------------------------------------------------------------------------------------ public JSON API

    /**
     * GET /api/newsletters?page=&per_page= - published issues, newest first (no content). Read-only, public data only (no e-mail addresses,
     * no drafts), always revalidated (Cache-Control max-age=0 + ETag / 304, so a change shows at once), readable cross-origin (GET, no credentials).
     * Every item says how it is read: kind = online | pdf, and pdf = {url (download), view_url (inline), size (bytes), name} or null.
     */
    public function apiIndex(Request $request): Response
    {
        if (!SitePages::isPublished('newsletter')) {
            return $this->apiResponse($request, ['error' => 'Not found'], 404);      // the Newsletter page is switched off: so is its API
        }
        $per = max(1, min(self::API_MAX_PER_PAGE, $request->int('per_page', self::PER_PAGE)));
        try {
            $p = NewsletterService::archive($request->page(), $per);
        } catch (HttpException) {
            return $this->apiResponse($request, ['error' => 'Not found'], 404);      // ?page= beyond the last page
        }
        return $this->apiResponse($request, [
            'data' => array_map(fn(array $r): array => $this->apiItem($r, false), $p->items),
            'meta' => ['page' => $p->page, 'per_page' => $p->perPage, 'total' => $p->total, 'pages' => $p->pages],
        ]);
    }

    /** GET /api/newsletters/{slug} - one published issue with its structured content (absolute URLs) and the rendered public HTML. */
    public function apiShow(Request $request, string $slug): Response
    {
        $issue = SitePages::isPublished('newsletter') ? NewsletterService::findPublishedBySlug($slug) : null;
        if ($issue === null) {
            return $this->apiResponse($request, ['error' => 'Not found'], 404);
        }
        return $this->apiResponse($request, ['data' => $this->apiItem($issue, true)]);
    }

    /** JSON + cache headers + CORS + ETag. Errors are never cached. @param array<string,mixed> $payload */
    private function apiResponse(Request $request, array $payload, int $status = 200): Response
    {
        $response = $this->json($payload, $status)->header('Access-Control-Allow-Origin', '*');
        if ($status !== 200) {
            return $response;       // Response::json already says no-store
        }
        $etag = 'W/"' . md5($response->body()) . '"';
        $headers = ['Cache-Control' => self::API_CACHE, 'ETag' => $etag, 'Vary' => 'Accept-Encoding'];
        $sent = (string) $request->header('If-None-Match', '');
        if ($sent !== '' && in_array($etag, array_map('trim', explode(',', $sent)), true)) {
            return (new Response('', 304, $headers))->header('Access-Control-Allow-Origin', '*');
        }
        return $response->withHeaders($headers);
    }

    /**
     * The PDF of an issue for API readers: absolute download and inline addresses, size in bytes, the file name a browser saves; null when the issue
     * has none (list rows carry only has_pdf, full rows carry the path).
     * @return array{url:string,view_url:string,size:int,name:string}|null
     */
    private function apiPdf(array $r): ?array
    {
        // the file must really be on disk, otherwise url / view_url would answer 404 (a restored database without the storage folder)
        $has = array_key_exists('pdf_path', $r) ? NewsletterPdf::exists($r) : !empty($r['has_pdf']);
        if (!$has) {
            return null;
        }
        $u = NewsletterPdf::urls($r, true);
        return ['url' => $u['download'], 'view_url' => $u['view'], 'size' => (int) ($r['pdf_size'] ?? 0), 'name' => NewsletterPdf::downloadName($r)];
    }

    /** @return array<string,mixed> */
    private function apiItem(array $r, bool $full): array
    {
        $item = [
            'slug'         => (string) $r['slug'],
            'kind'         => (string) ($r['kind'] ?? 'online') === 'pdf' ? 'pdf' : 'online',
            'issue_number' => $r['issue_number'] !== null ? (int) $r['issue_number'] : null,
            'title'        => (string) $r['title'],
            'summary'      => (string) ($r['summary'] ?? ''),
            'issue_date'   => (string) $r['issue_date'],
            'published_at' => $this->iso($r['published_at'] ?? $r['publish_at'] ?? null),
            'url'          => NewsletterService::url($r, true),
            'cover'        => NewsletterContent::validImagePath((string) ($r['cover_image'] ?? '')) ? ['url' => abs_url_for_image((string) $r['cover_image']), 'alt' => (string) ($r['cover_alt'] ?? '')] : null,
            'pdf'          => $this->apiPdf($r),
        ];
        if ($full) {
            $item['content'] = $this->apiContent(NewsletterContent::decode((string) $r['content']));
            $item['html'] = $this->absolutize(NewsletterRenderer::renderIssue($r, 'public'));
            $seo = NewsletterService::seo($r);
            $seo['og_image'] = $seo['og_image'] !== '' ? abs_url_for_image($seo['og_image']) : '';
            $seo['published'] = $this->iso($seo['published']);
            $seo['modified'] = $this->iso($seo['modified']);
            $item['seo'] = $seo;
        }
        return $item;
    }

    /**
     * The structured content as an outside reader needs it: every image gets an absolute `url` next to its upload `path`, links and buttons carry
     * absolute addresses, rich text has absolute href / src. (Same data as the stored JSON otherwise.)
     *
     * @param array<string,mixed> $c
     * @return array<string,mixed>
     */
    private function apiContent(array $c): array
    {
        $image = static function (array $i): array {
            $i['url'] = $i['path'] !== '' ? abs_url_for_image((string) $i['path']) : '';
            return $i;
        };
        $link = static function (array $l): array {
            $l['url'] = $l['url'] !== '' ? NewsletterRenderer::href((string) $l['url'], 'email') : '';
            return $l;
        };
        $f = &$c['featured'];
        $f['body_html'] = NewsletterRenderer::absolute((string) $f['body_html']);
        $f['image'] = $image($f['image']);
        $f['link'] = $link($f['link']);
        unset($f);
        foreach ($c['blocks'] as &$b) {
            if (isset($b['body_html'])) {
                $b['body_html'] = NewsletterRenderer::absolute((string) $b['body_html']);
            }
            if (isset($b['image'])) {
                $b['image'] = $image($b['image']);
            }
            if (isset($b['link'])) {
                $b['link'] = $link($b['link']);
            }
            if (isset($b['button'])) {
                $b['button'] = $link($b['button']);
            }
        }
        unset($b);
        return $c;
    }

    /** Stored application-time-zone date-time ("2026-10-17 09:00:00") -> ISO 8601 with the offset ("2026-10-17T09:00:00+03:00"); null when empty. */
    private function iso(mixed $value): ?string
    {
        return to_datetime($value)?->format(DATE_ATOM);
    }

    /** Output that already carries the base path ("/lumina/about", "/lumina/uploads/x.jpg") -> absolute addresses. */
    private function absolutize(string $html): string
    {
        return (string) preg_replace('~\b(href|src)="/(?!/)~i', '$1="' . origin() . '/', $html);
    }

    // ------------------------------------------------------------------------------------------------ rendering

    /** Id of the newest public issue (0 when none): the one /newsletter shows. Cheap: no content column. */
    private function latestId(): int
    {
        [$sql, $params] = NewsletterService::visibleSql('n');
        return (int) Database::value("SELECT n.id FROM newsletters n WHERE {$sql} ORDER BY " . NewsletterService::PUBLIC_ORDER . ' LIMIT 1', $params, 0);
    }

    private function issueResponse(array $issue, bool $isCurrent): Response
    {
        return $this->renderIssueView($issue, $isCurrent, false, '');
    }

    private function renderIssueView(array $issue, bool $isCurrent, bool $preview, string $banner): Response
    {
        $seo = NewsletterService::seo($issue);
        if ($seo['og_image'] !== '' && !NewsletterContent::validImagePath($seo['og_image'])) {
            $seo['og_image'] = '';
        }
        $adjacent = $preview ? ['prev' => null, 'next' => null] : NewsletterService::adjacent($issue);
        $label = HomeSupport::navLabel('newsletter', 'Newsletter');
        $absUrl = NewsletterService::url($issue, true);
        $meta = [
            'title'       => $seo['title'],
            'description' => $seo['description'],
            'og_image'    => $seo['og_image'],
            'og_type'     => 'article',
            // /newsletter shows the newest issue: it names that issue's own address as canonical so the two never compete
            'canonical'   => $absUrl,
            'robots'      => $preview ? 'noindex,nofollow' : '',
        ];
        // the masthead shows the "latest issue" badge (the renderer passes the row through to web/newsletter/_issue.php)
        $issue['is_latest'] = !$preview && $isCurrent;
        // a preview must work even while the Newsletter page is unpublished, so it skips the published check
        $render = $preview ? fn(string $v, array $d, array $o): Response => $this->render($v, $d + ['pageSlug' => 'newsletter'], $o + ['page' => 'newsletter'])
            : fn(string $v, array $d, array $o): Response => $this->renderPage('newsletter', $v, $d, $o);
        return $render('web/newsletter/show', [
            'issue'      => $issue,
            'issueHtml'  => NewsletterRenderer::renderIssue($issue, $preview ? 'preview' : 'public'),
            'seo'        => $seo,
            'adjacent'   => $adjacent,
            'isCurrent'  => $isCurrent,
            'preview'    => $preview,
            'banner'     => $banner,
            'absUrl'     => $absUrl,
            'share'      => ShareLinks::for($absUrl, (string) $issue['title']),
            'ld'         => $preview ? [] : $this->jsonLd($issue, $seo, $label),
        ], [
            'meta'        => $meta,
            'breadcrumbs' => [[$label, url('newsletter')], [(string) $issue['title'], null]],
            'bodyClass'   => 'page-newsletter-issue',
        ]);
    }

    /**
     * Structured data for an issue: NewsArticle for a written issue, Article for a PDF issue. A PDF (the issue itself, or the PDF attached to a
     * written issue) is described as a MediaObject (`encoding` and `associatedMedia`: contentUrl = the absolute inline PDF address, encodingFormat
     * application/pdf, contentSize). The PDF routes themselves are noindex; the page is what search engines list.
     * The issue number and date belong to a PublicationIssue of the newsletter (a Periodical): schema.org has no issueNumber on an article.
     * @return array<string,mixed>
     */
    private function jsonLd(array $issue, array $seo, string $navLabel): array
    {
        $site = (string) setting('site_name', '');
        $published = to_datetime($seo['published'])?->format(DATE_ATOM);
        $modified = to_datetime($seo['modified'])?->format(DATE_ATOM);
        $image = $seo['og_image'] !== '' ? abs_url_for_image($seo['og_image']) : '';
        $logo = abs_url_for_image((string) setting('logo_path', ''));
        $org = ['@type' => 'Organization', 'name' => $site];
        $number = $issue['issue_number'] !== null ? (int) $issue['issue_number'] : 0;
        $isPdf = (string) ($issue['kind'] ?? 'online') === 'pdf';
        $words = $isPdf ? 0 : str_word_count(NewsletterContent::plainText(NewsletterContent::decode((string) ($issue['content'] ?? ''))));
        $media = null;
        if (NewsletterPdf::exists($issue)) {
            $info = NewsletterPdf::info($issue, true);
            $media = array_filter([
                '@type'          => 'MediaObject',
                'name'           => $info['filename'],
                'contentUrl'     => $info['view_url'],
                'encodingFormat' => 'application/pdf',
                'contentSize'    => $info['size_human'],
                'uploadDate'     => to_datetime($info['updated_at'] !== '' ? $info['updated_at'] : ($seo['published'] ?? ''))?->format(DATE_ATOM),
            ], static fn($v): bool => $v !== null && $v !== '');
        }
        return array_filter([
            '@context'         => 'https://schema.org',
            '@type'            => $isPdf ? 'Article' : 'NewsArticle',
            'headline'         => mb_substr((string) $issue['title'], 0, 110),
            'description'      => $seo['description'],
            'inLanguage'       => 'en',
            'datePublished'    => $published,
            'dateModified'     => $modified ?: $published,
            'image'            => $image !== '' ? [$image] : null,
            'url'              => $seo['canonical'],
            'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $seo['canonical']],
            'wordCount'        => $words > 0 ? $words : null,
            'encoding'         => $media,
            'associatedMedia'  => $media,
            'author'           => $org,
            'publisher'        => $org + ($logo !== '' ? ['logo' => ['@type' => 'ImageObject', 'url' => $logo]] : []),
            'isPartOf'         => array_filter([
                '@type'         => 'PublicationIssue',
                'issueNumber'   => $number > 0 ? (string) $number : null,
                'datePublished' => (string) $issue['issue_date'],
                'isPartOf'      => ['@type' => 'Periodical', 'name' => trim($site . ' ' . $navLabel), 'url' => abs_url('newsletter')],
            ], static fn($v): bool => $v !== null && $v !== ''),
        ], static fn($v): bool => $v !== null && $v !== '');
    }
}
