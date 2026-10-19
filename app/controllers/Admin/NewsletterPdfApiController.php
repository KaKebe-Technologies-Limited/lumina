<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\AdminController;
use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Core\Uploader;
use App\Models\Newsletter;
use App\Services\Newsletter\NewsletterPdf;
use App\Services\Newsletter\NewsletterService;

/**
 * Admin side of the PDF format: upload / replace / remove the PDF of an issue, stream it for the admin preview, and the "Live on the website"
 * status. OWNER: NEWSLETTER agent (docs/PARTNERS_NEWSLETTER.md sections 5 and 8). The URLs, verbs and the JSON keys of the architect's baseline are
 * unchanged; every answer now ALSO carries `item` (the whole issue in the shape of NewsletterApiController::itemFor, without the heavy content), so
 * the editor can refresh its toolbar, checklist and "Live on the website" box from one response. Ability: newsletter.manage (routes file) and again
 * here. Error shape as NewsletterApiController: {"error":"<code>","message":"..."} with validation (422 + errors), not_found (404),
 * campaign_running (409), conflict (409).
 */
final class NewsletterPdfApiController extends AdminController
{
    /** POST /admin/api/newsletters/{id}/pdf   multipart field "pdf" -> 201 {data:{id,kind,pdf,live,item}} | 422 validation */
    public function upload(Request $request, int $id): Response
    {
        $this->authorize('newsletter.manage');
        if (Newsletter::find($id) === null) {
            return $this->err('not_found', 'That issue no longer exists.', 404);
        }
        $file = $request->file('pdf');
        if ($file === null) {
            // PHP drops the whole upload silently when it is larger than post_max_size: tell the admin that, not "choose a file"
            $limit = Uploader::serverUploadLimit();
            $sent = (int) ($_SERVER['CONTENT_LENGTH'] ?? 0);
            $msg = $limit > 0 && $sent > $limit
                ? 'That file is larger than this server accepts (' . human_filesize($limit) . '). Make the PDF smaller (use "Reduce file size" or "Compress" in your PDF program) and try again.'
                : 'Choose a PDF file to upload.';
            return $this->json(['error' => 'validation', 'message' => $msg, 'errors' => ['pdf' => $msg]], 422);
        }
        $res = NewsletterPdf::attach($id, $file, $this->userId());
        if (!$res['ok']) {
            if (!empty($res['locked'])) {
                return $this->err('campaign_running', (string) $res['error'], 409);
            }
            $msg = (string) ($res['error'] ?? 'The PDF could not be saved.');
            return $this->json(['error' => 'validation', 'message' => $msg, 'errors' => (object) ($res['errors'] ?? ['pdf' => $msg])], 422);
        }
        return $this->json(['data' => $this->payload($res['newsletter'])], 201)->header('Cache-Control', 'no-store');
    }

    /** DELETE /admin/api/newsletters/{id}/pdf -> 200 {data:{id,kind,pdf,live,item}} | 409 */
    public function remove(Request $request, int $id): Response
    {
        $this->authorize('newsletter.manage');
        $res = NewsletterPdf::remove($id, $this->userId());
        if (!empty($res['not_found'])) {
            return $this->err('not_found', 'That issue no longer exists.', 404);
        }
        if (!$res['ok']) {
            return $this->err(!empty($res['locked']) ? 'campaign_running' : 'conflict', (string) $res['error'], 409);
        }
        return $this->json(['data' => $this->payload($res['newsletter'])])->header('Cache-Control', 'no-store');
    }

    /** GET /admin/api/newsletters/{id}/live -> {data:{id,kind,pdf,live,item}}: the "Live on the website" box, always freshly computed */
    public function live(Request $request, int $id): Response
    {
        $this->authorize('newsletter.manage');
        NewsletterService::resolveScheduled();
        $row = Newsletter::find($id);
        return $row === null ? $this->err('not_found', 'That issue no longer exists.', 404) : $this->json(['data' => $this->payload($row)])->header('Cache-Control', 'no-store');
    }

    /** GET /admin/newsletters/{id}/pdf   the stored PDF inline for ANY status (admin preview of a draft); 404 when there is none */
    public function file(Request $request, int $id): Response
    {
        $this->authorize('newsletter.manage');
        $row = Newsletter::find($id);
        if ($row === null || !NewsletterPdf::exists($row)) {
            if ($request->expectsJson()) {
                return $this->err('not_found', 'That issue has no PDF.', 404);
            }
            throw new HttpException(404, 'This issue has no PDF.');          // a browser tab gets the normal "not found" page, not raw JSON
        }
        return NewsletterPdf::response($row, true)->header('X-Robots-Tag', 'noindex, nofollow');
    }

    /** @return array<string,mixed> */
    private function payload(array $row): array
    {
        $item = NewsletterApiController::itemFor($row, true);
        unset($item['content']);
        return ['id' => (int) $row['id'], 'kind' => (string) ($row['kind'] ?? 'online'), 'pdf' => $item['pdf'], 'live' => $item['live'], 'item' => $item];
    }

    private function err(string $code, string $message, int $status): Response
    {
        return $this->json(['error' => $code, 'message' => $message], $status);
    }
}
