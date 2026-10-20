<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\AdminController;
use App\Core\Request;
use App\Core\Response;
use App\Core\UploadException;
use App\Core\Uploader;
use App\Models\Newsletter;
use App\Services\Newsletter\NewsletterContent;
use App\Services\Newsletter\NewsletterPdf;
use App\Services\Newsletter\NewsletterRenderer;
use App\Services\Newsletter\NewsletterService;

/**
 * JSON API of the newsletter editor: /admin/api/newsletters. Contract (request/response shapes, error format): docs/NEWSLETTER.md section 7.
 * OWNER: EDITOR agent. The architect delivered this as a WORKING REFERENCE (every endpoint below is implemented on top of NewsletterService);
 * the editor agent may refine it but must keep the URLs, verbs, JSON shapes and status codes: the editor script, the tests and the mailing
 * agent rely on them.
 *
 * Auth: session cookie + header X-CSRF-TOKEN (value of <meta name="csrf-token">) on every non-GET; send "Accept: application/json" so a lost
 * session answers 401 JSON instead of a redirect. Ability: newsletter.manage on every route (routes file); deleting a published issue also
 * needs newsletter.delete_published (admin). E-mail endpoints live in NewsletterMailController (admin only).
 *
 * Error format of THIS controller:  {"error":"<code>","message":"<human text>", ...extras}   codes: validation (422, + "errors":{path:message}),
 * conflict (409, + "current":{version,updated_at,updated_by} and "data": the newest item), not_found (404), forbidden (403), bad_request (400),
 * not_ready (422, + "errors" = what is missing). Framework-level failures (401 not signed in, 403 CSRF, 404 unknown route, 405) keep the
 * core shape {"error":"<Title>","status":N}.
 */
final class NewsletterApiController extends AdminController
{
    /** GET /admin/api/newsletters?status=&email_status=&q=&page=&per_page= */
    public function index(Request $request): Response
    {
        $this->authorize('newsletter.manage');
        NewsletterService::resolveScheduled();
        $p = NewsletterService::adminList([
            'status'       => $request->string('status'),
            'email_status' => $request->string('email_status'),
            'q'            => $request->string('q'),
        ], $request->page(), $this->perPage());
        $rows = NewsletterService::annotate($p->items);                 // adds `live` (visible? why not?) to every row
        return $this->json([
            'data' => array_map(fn(array $r): array => $this->item($r, false), $rows),
            'meta' => ['page' => $p->page, 'per_page' => $p->perPage, 'total' => $p->total, 'pages' => $p->pages, 'counts' => NewsletterService::statusCounts(), 'page_visible' => NewsletterService::pageVisible()],
        ]);
    }

    /** POST /admin/api/newsletters   body: details (title*, issue_number, issue_date, summary, ...) and optional content -> 201 {data} */
    public function store(Request $request): Response
    {
        $this->authorize('newsletter.manage');
        $res = NewsletterService::create($request->post(), $this->userId());
        if (!$res['ok']) {
            return $this->validation($res['errors'] ?? []);
        }
        return $this->json(['data' => $this->item($res['newsletter'], true), 'warnings' => (object) ($res['warnings'] ?? [])], 201);
    }

    /** GET /admin/api/newsletters/{id} -> {data: item with content, readiness, allowed transitions} */
    public function show(Request $request, int $id): Response
    {
        $this->authorize('newsletter.manage');
        NewsletterService::resolveScheduled();          // a scheduled issue whose time has come is shown as published
        $row = Newsletter::find($id);
        return $row === null ? $this->notFound() : $this->json(['data' => $this->item($row, true)]);
    }

    /**
     * PUT /admin/api/newsletters/{id}   body: version* (the version the editor loaded), autosave (bool), any of title, slug, issue_number,
     * issue_date, summary, cover_image, cover_alt, seo_title, seo_description, content (object).
     * 200 {data, changed, warnings} | 409 conflict | 422 validation. A save that changes nothing answers 200 with changed=false and the same version.
     */
    public function save(Request $request, int $id): Response
    {
        $this->authorize('newsletter.manage');
        $version = $request->post('version');
        if (!is_int($version) && !(is_string($version) && ctype_digit($version))) {
            return $this->error('bad_request', 'The version of the issue is missing. Reload the page.', 400);
        }
        $autosave = $request->bool('autosave');
        $input = $request->post();
        unset($input['version'], $input['autosave'], $input['_method']);
        $res = NewsletterService::save($id, $input, (int) $version, $this->userId(), ['autosave' => $autosave]);
        if (!empty($res['not_found'])) {
            return $this->notFound();
        }
        if (!empty($res['conflict'])) {
            return $this->json([
                'error'   => 'conflict',
                'message' => 'Somebody else saved a newer version of this issue.',
                'current' => $res['current'],
                'data'    => $this->item($res['newsletter'], true),
            ], 409);
        }
        if ($refused = $this->refusal($res)) {
            return $refused;
        }
        if (!$res['ok']) {
            return $this->validation($res['errors'] ?? []);
        }
        if (!$autosave && !empty($res['changed'])) {
            $this->audit('newsletter.update', 'newsletter', $id, 'Edited newsletter issue', ['version' => (int) $res['newsletter']['version']]);
        }
        return $this->json(['data' => $this->item($res['newsletter'], true), 'changed' => (bool) ($res['changed'] ?? false), 'warnings' => (object) ($res['warnings'] ?? [])]);
    }

    /** DELETE /admin/api/newsletters/{id} -> 200 {ok:true}. Published (or once public) issues: admin only. */
    public function destroy(Request $request, int $id): Response
    {
        $this->authorize('newsletter.manage');
        $row = Newsletter::find($id);
        if ($row === null) {
            return $this->notFound();
        }
        if (($row['status'] === 'published' || !empty($row['published_at'])) && !can('newsletter.delete_published')) {
            return $this->error('forbidden', 'Only an administrator can delete an issue that has been published.', 403);
        }
        $res = NewsletterService::delete($id);
        return $res['ok'] ? $this->json(['ok' => true]) : $this->error('conflict', (string) $res['error'], 409);
    }

    /**
     * POST /admin/api/newsletters/preview   body: id (optional), details + content (the UNSAVED editor state)
     * -> {html: "<!doctype html>...", readiness:{blocking,warnings}}. Nothing is written. Put html into an iframe srcdoc (same origin; the parent
     * page can listen for clicks on [data-nl-block] inside it).
     */
    public function preview(Request $request): Response
    {
        $this->authorize('newsletter.manage');
        $id = $request->int('id');
        $row = $id > 0 ? Newsletter::find($id) : null;
        if ($id > 0 && $row === null) {
            return $this->notFound();
        }
        $input = $request->post();
        $issue = NewsletterRenderer::previewRow($row, $input);
        $issue['pdf_embed'] = false;         // the editor's preview frame is sandboxed, and a sandboxed document cannot show a PDF: it gets a placeholder
        return $this->json([
            'html'      => NewsletterRenderer::previewDocument($issue),
            'readiness' => self::readiness($issue),
        ])->header('Cache-Control', 'no-store');
    }

    /** POST /admin/api/newsletters/upload (multipart, field "file") -> 201 {path, url, name} | 422 validation. */
    public function upload(Request $request): Response
    {
        $this->authorize('newsletter.manage');
        $file = $request->file('file');
        if ($file === null) {
            return $this->validation(['file' => 'Choose a picture to upload.']);
        }
        try {
            $path = Uploader::image($file);
        } catch (UploadException $e) {
            return $this->validation(['file' => $e->getMessage()]);
        }
        return $this->json(['path' => $path, 'url' => img_url($path), 'name' => basename((string) ($file['name'] ?? $path))], 201);
    }

    /** GET /admin/api/newsletters/{id}/revisions -> {data:[{id,version,title,source,saved_by_name,created_at,bytes}]} (latest 30) */
    public function revisions(Request $request, int $id): Response
    {
        $this->authorize('newsletter.manage');
        return Newsletter::find($id) === null ? $this->notFound() : $this->json(['data' => NewsletterService::revisions($id)]);
    }

    /** GET /admin/api/newsletters/{id}/revisions/{rid} -> {data:{id,version,title,source,created_at,content}, diff:[{kind,where,from,to}]} (diff = current -> that revision) */
    public function revision(Request $request, int $id, int|string $rid): Response
    {
        $this->authorize('newsletter.manage');
        $rid = (int) $rid;
        $row = Newsletter::find($id);
        $rev = $row === null ? null : NewsletterService::revision($id, $rid);
        if ($row === null) {
            return $this->notFound();
        }
        if ($rev === null) {
            return $this->error('not_found', 'That version no longer exists.', 404);
        }
        return $this->json([
            'data' => ['id' => (int) $rev['id'], 'version' => (int) $rev['version'], 'title' => $rev['title'], 'source' => $rev['source'], 'created_at' => $rev['created_at'], 'content' => $rev['content_data']],
            'diff' => NewsletterService::diffLite(NewsletterContent::decode((string) $row['content']), $rev['content_data']),
        ]);
    }

    /** POST /admin/api/newsletters/{id}/revisions/{rid}/restore   body: version* -> 200 {data} | 409 conflict */
    public function restore(Request $request, int $id, int|string $rid): Response
    {
        $this->authorize('newsletter.manage');
        $rid = (int) $rid;
        $version = $request->post('version');
        if (!is_int($version) && !(is_string($version) && ctype_digit($version))) {
            return $this->error('bad_request', 'The version of the issue is missing. Reload the page.', 400);
        }
        $res = NewsletterService::restoreRevision($id, $rid, (int) $version, $this->userId());
        if (!empty($res['not_found'])) {
            return isset($res['errors']['revision']) ? $this->error('not_found', (string) $res['errors']['revision'], 404) : $this->notFound();
        }
        if (!empty($res['conflict'])) {
            return $this->json(['error' => 'conflict', 'message' => 'Somebody else saved a newer version of this issue.', 'current' => $res['current'], 'data' => $this->item($res['newsletter'], true)], 409);
        }
        if ($refused = $this->refusal($res)) {
            return $refused;
        }
        return $res['ok'] ? $this->json(['data' => $this->item($res['newsletter'], true)]) : $this->validation($res['errors'] ?? []);
    }

    /** POST /admin/api/newsletters/{id}/publish -> 200 {data} | 422 not_ready/validation */
    public function publish(Request $request, int $id): Response
    {
        return $this->transition($id, 'published', []);
    }

    /** POST /admin/api/newsletters/{id}/schedule   body: publish_at* ("2026-11-03T09:30", Africa/Kampala time) */
    public function schedule(Request $request, int $id): Response
    {
        return $this->transition($id, 'scheduled', ['publish_at' => $request->string('publish_at')]);
    }

    /** POST /admin/api/newsletters/{id}/unpublish -> back to draft (also cancels a schedule) */
    public function unpublish(Request $request, int $id): Response
    {
        return $this->transition($id, 'draft', []);
    }

    /** POST /admin/api/newsletters/{id}/archive */
    public function archive(Request $request, int $id): Response
    {
        return $this->transition($id, 'archived', []);
    }

    /** POST /admin/api/newsletters/{id}/duplicate -> 201 {data} (a new draft) */
    public function duplicate(Request $request, int $id): Response
    {
        $this->authorize('newsletter.manage');
        $res = NewsletterService::duplicate($id, $this->userId());
        if (!$res['ok']) {
            return isset($res['errors']) ? $this->validation($res['errors']) : $this->notFound();
        }
        return $this->json(['data' => $this->item($res['newsletter'], true)], 201);
    }

    // ------------------------------------------------------------------------------------------------ helpers

    /** @param array<string,mixed> $opts */
    private function transition(int $id, string $to, array $opts): Response
    {
        $this->authorize('newsletter.manage');
        if (Newsletter::find($id) === null) {
            return $this->notFound();
        }
        $res = NewsletterService::transition($id, $to, $opts + ['user_id' => $this->userId()]);
        if (!$res['ok']) {
            $errors = $res['errors'] ?? [];
            return $this->json(['error' => $errors !== [] ? 'not_ready' : 'conflict', 'message' => (string) ($res['error'] ?? 'The change could not be made.'), 'errors' => (object) $errors, 'warnings' => (object) ($res['warnings'] ?? [])], $errors !== [] ? 422 : 409);
        }
        return $this->json(['data' => $this->item($res['newsletter'], true), 'warnings' => (object) ($res['warnings'] ?? [])]);
    }

    /** @return array<string,mixed> see itemFor() */
    private function item(array $r, bool $full): array
    {
        return self::itemFor($r, $full);
    }

    /**
     * The JSON shape of an issue (also embedded in the editor page, so the page and the API cannot drift). $full adds the content and what
     * is missing before publishing. `slug_locked` = the issue was public once, so its web address can no longer change.
     * @return array<string,mixed>
     */
    public static function itemFor(array $r, bool $full): array
    {
        $status = (string) $r['status'];
        $item = [
            'id'              => (int) $r['id'],
            'slug'            => (string) $r['slug'],
            'kind'            => (string) ($r['kind'] ?? 'online') === 'pdf' ? 'pdf' : 'online',
            'issue_number'    => $r['issue_number'] !== null ? (int) $r['issue_number'] : null,
            'title'           => (string) $r['title'],
            'summary'         => (string) ($r['summary'] ?? ''),
            'issue_date'      => (string) $r['issue_date'],
            'cover_image'     => (string) ($r['cover_image'] ?? ''),
            'cover_alt'       => (string) ($r['cover_alt'] ?? ''),
            'cover_url'       => img_url((string) ($r['cover_image'] ?? '')),
            'status'          => $status,
            'is_live'         => NewsletterService::isLive($r),
            'publish_at'      => $r['publish_at'] ?? null,
            'published_at'    => $r['published_at'] ?? null,
            'version'         => (int) $r['version'],
            'slug_locked'     => !empty($r['published_at']),
            'updated_at'      => (string) ($r['updated_at'] ?? ''),
            'url'             => NewsletterService::url($r, true),
            'pdf'             => self::pdfInfo($r),
            // "Live on the website": can visitors see it right now, and if not why not (same rules as the public site, nothing cached)
            'live'            => $r['live'] ?? NewsletterService::liveStatus($r, ['content' => array_key_exists('content', $r)]),
            'allowed'         => NewsletterService::allowedTransitions($status),
            'email'           => [
                'enabled' => (bool) ($r['email_enabled'] ?? false),
                'status'  => (string) ($r['email_status'] ?? 'none'),
                'total'   => (int) ($r['email_total'] ?? 0),
                'sent'    => (int) ($r['email_sent'] ?? 0),
                'failed'  => (int) ($r['email_failed'] ?? 0),
            ],
        ];
        if ($full) {
            $item += [
                'seo_title'       => (string) ($r['seo_title'] ?? ''),
                'seo_description' => (string) ($r['seo_description'] ?? ''),
                'content'         => NewsletterContent::decode((string) $r['content']),
                'readiness'       => self::readiness($r),
                // social media (docs/SOCIAL.md): which channels exist and what happened to the posts of this issue; the full panel is GET /admin/api/newsletters/{id}/share
                'social'          => \App\Services\Social\ShareHook::summary((int) $r['id']),
            ];
        }
        return $item;
    }

    /**
     * The PDF of an issue as the admin UI needs it: NewsletterPdf::info() plus `admin_url` (the stored file streamed inline for ANY status, the
     * "Open PDF" link). List rows carry no path, only has_pdf / pdf_name / pdf_size, so for them `exists` is not checked.
     * @return array<string,mixed>
     */
    public static function pdfInfo(array $r): array
    {
        $listRow = !array_key_exists('pdf_path', $r);
        $info = NewsletterPdf::info($listRow ? $r + ['pdf_path' => !empty($r['has_pdf']) ? 'listed' : ''] : $r);
        if ($listRow && $info['has']) {
            $info['exists'] = true;
        }
        $info['admin_url'] = $info['has'] ? url('admin/newsletters/' . (int) ($r['id'] ?? 0) . '/pdf') : '';
        return $info;
    }

    /** @return array{blocking:object,warnings:object} */
    private static function readiness(array $issue): array
    {
        $ready = NewsletterContent::readiness(NewsletterContent::decode(is_string($issue['content'] ?? null) || is_array($issue['content'] ?? null) ? $issue['content'] : null), $issue);
        return ['blocking' => (object) $ready['blocking'], 'warnings' => (object) $ready['warnings']];
    }

    /**
     * Business refusals of NewsletterService::save(): campaign_running (409, content is frozen while an e-mail campaign runs),
     * status_changed (409, an autosave reached an issue that is no longer a draft), not_ready (422, a live issue must stay complete).
     * @param array<string,mixed> $res
     */
    private function refusal(array $res): ?Response
    {
        if (!empty($res['locked'])) {
            return $this->error('campaign_running', (string) $res['error'], 409);
        }
        if (!empty($res['status_changed'])) {
            return $this->error('status_changed', (string) $res['error'], 409);
        }
        if (!empty($res['not_ready'])) {
            return $this->json([
                'error'   => 'not_ready',
                'message' => 'This issue is live, so it has to stay complete. Finish the highlighted parts, or unpublish the issue first.',
                'errors'  => (object) $res['errors'],
            ], 422);
        }
        return null;
    }

    /** @param array<string,string> $errors */
    private function validation(array $errors): Response
    {
        return $this->json(['error' => 'validation', 'message' => $errors !== [] ? (string) reset($errors) : 'Please check the highlighted fields.', 'errors' => (object) $errors], 422);
    }

    private function notFound(): Response
    {
        return $this->error('not_found', 'That issue no longer exists.', 404);
    }

    private function error(string $code, string $message, int $status): Response
    {
        return $this->json(['error' => $code, 'message' => $message], $status);
    }
}
