<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\AdminController;
use App\Core\Request;
use App\Core\Response;
use App\Models\Newsletter;
use App\Models\SocialPost;
use App\Services\Social\CaptionBuilder;
use App\Services\Social\Channels;
use App\Services\Social\PostQueue;
use App\Services\Social\ShareHook;
use App\Services\Social\SocialDispatcher;

/**
 * JSON API of the "Share on social media" panel (newsletter editor + publish confirmation) and of the retry buttons. REFERENCE IMPLEMENTATION by the architect: complete and
 * tested, thin over ShareHook / PostQueue / SocialDispatcher. The ADMIN agent builds the panel UI (views, assets/js/admin-social.js) on these endpoints and may refine the
 * controller, but must keep the URLs, verbs, JSON shapes and status codes. Contract: docs/SOCIAL.md section 9. Ability: social.share on every route (route middleware AND here).
 *
 * Auth and CSRF as for the newsletter API: session cookie, header X-CSRF-TOKEN on every non-GET, "Accept: application/json".
 * Errors: {"error":"<code>","message":"<human text>", ...}  codes: validation (422, + "errors":{channel|_ : message}), not_found (404), bad_request (400), conflict (409), confirm_required (422).
 *
 *   GET  /admin/api/newsletters/{id}/share            -> ShareHook::panel() (see its comment for the shape)
 *   PUT  /admin/api/newsletters/{id}/share            body {"channels":{"facebook":{"on":true,"caption":"..."}, ...}} -> {"ok":true,"panel":{...}} | 422 validation
 *   POST /admin/api/newsletters/{id}/share/count      body {"channel","text"} -> {"length","limit","over","counting","soft"}   (the live counter; X counts every URL as 23)
 *   POST /admin/api/newsletters/{id}/share/now        body {"channels":["telegram",...]} -> {"ok","queued":[...],"errors":{...},"panel"}; posts right after the response
 *   POST /admin/api/newsletters/{id}/share/again      body {"channels":[...],"confirm":true,"share_version":N} -> same (share_version = the version the panel showed: a stale or repeated click is refused with 409)
 *   POST /admin/api/newsletters/{id}/share/dispatch   try the due posts of this issue NOW (the panel calls it when the host cannot post after the response) -> {"summary":{...},"statuses":{...}}
 *   GET  /admin/api/newsletters/{id}/share/status     -> {"statuses":{"facebook":{state,label,tone,remote_url,error,post_id,attempts,posted_at,share_version}, ...},"queued":N}
 *   POST /admin/api/social/posts/{id}/retry | skip | mark-posted (body {"remote_url"?}) | post-anyway   -> {"ok":true,"status":{...}} | 409 conflict
 */
final class SocialShareApiController extends AdminController
{
    public function panel(Request $request, int $id): Response
    {
        $this->authorize('social.share');
        if (!ShareHook::mayUse(Newsletter::find($id))) {
            return $this->notFound();
        }
        $p = ShareHook::panel($id);
        return empty($p['ok']) ? $this->notFound() : $this->json($p)->header('Cache-Control', 'no-store');
    }

    public function save(Request $request, int $id): Response
    {
        $this->authorize('social.share');
        if (!ShareHook::mayUse(Newsletter::find($id))) {
            return $this->notFound();
        }
        $res = ShareHook::saveChoices($id, ['channels' => (array) $request->post('channels', [])], $this->userId());
        if (!$res['ok']) {
            return $this->validation($res['errors']);
        }
        return $this->json(['ok' => true, 'panel' => $res['panel']]);
    }

    /** The live counter never works on more than this many characters (the longest limit is 5000): anything longer is refused before it is cleaned or counted. */
    private const COUNT_MAX_CHARS = 10000;

    public function count(Request $request, int $id): Response
    {
        $this->authorize('social.share');
        $ch = $request->string('channel');
        if (!Channels::has($ch) && $ch !== 'tiktok') {
            return $this->error('bad_request', 'Unknown channel.', 400);
        }
        $text = $request->post('text');
        if (is_string($text) && (strlen($text) > self::COUNT_MAX_CHARS * 4 || mb_strlen($text) > self::COUNT_MAX_CHARS)) {
            return $this->json(['error' => 'validation', 'message' => 'That text is far too long for any platform (' . number_format(self::COUNT_MAX_CHARS) . ' characters at most).', 'errors' => (object) [], 'over' => true, 'limit' => CaptionBuilder::limit($ch)], 422);
        }
        $clean = trim((string) preg_replace('/\n{3,}/', "\n\n", CaptionBuilder::sanitize(is_string($text) ? $text : '', true)));
        $len = CaptionBuilder::length($ch, $clean);
        $limit = CaptionBuilder::limit($ch);
        return $this->json(['length' => $len, 'limit' => $limit, 'over' => $len > $limit, 'counting' => CaptionBuilder::counting($ch), 'soft' => Channels::has($ch) ? (int) Channels::get($ch)->meta()['soft'] : 0]);
    }

    public function now(Request $request, int $id): Response
    {
        $this->authorize('social.share');
        if (!ShareHook::mayUse(Newsletter::find($id))) {
            return $this->notFound();
        }
        $res = ShareHook::shareNow($id, $this->channelList($request), $this->userId());
        return $this->queued($id, $res);
    }

    public function again(Request $request, int $id): Response
    {
        $this->authorize('social.share');
        if (!ShareHook::mayUse(Newsletter::find($id))) {
            return $this->notFound();
        }
        if (!$request->bool('confirm')) {
            return $this->error('confirm_required', 'Posting the same issue again needs a confirmation (confirm = true).', 422);
        }
        $seen = $request->post('share_version');
        $res = ShareHook::postAgain($id, $this->channelList($request), $this->userId(), is_scalar($seen) && ctype_digit((string) $seen) ? (int) $seen : null);
        return $this->queued($id, $res);
    }

    public function dispatch(Request $request, int $id): Response
    {
        $this->authorize('social.share');
        if (!ShareHook::mayUse(Newsletter::find($id))) {
            return $this->notFound();
        }
        $sum = SocialDispatcher::dispatchNewsletter($id, 15.0);
        return $this->json(['summary' => $sum, 'statuses' => $this->statuses($id)]);
    }

    public function status(Request $request, int $id): Response
    {
        $this->authorize('social.share');
        if (!ShareHook::mayUse(Newsletter::find($id))) {
            return $this->notFound();
        }
        $st = $this->statuses($id);
        $queued = count(array_filter($st, static fn(array $s): bool => in_array($s['state'], ['queued', 'posting'], true)));
        return $this->json(['statuses' => $st, 'queued' => $queued])->header('Cache-Control', 'no-store');
    }

    public function retry(Request $request, int $id): Response
    {
        return $this->act($id, 'retry', fn(int $pid): array => PostQueue::retry($pid, $this->userId()));
    }

    public function skip(Request $request, int $id): Response
    {
        return $this->act($id, 'skip', fn(int $pid): array => PostQueue::skip($pid, $this->userId()));
    }

    public function markPosted(Request $request, int $id): Response
    {
        $url = $request->string('remote_url');
        return $this->act($id, 'mark-posted', fn(int $pid): array => PostQueue::markPostedByAdmin($pid, $url !== '' ? $url : null, $this->userId()));
    }

    public function postAnyway(Request $request, int $id): Response
    {
        return $this->act($id, 'post-anyway', fn(int $pid): array => PostQueue::retryAnyway($pid, $this->userId()));
    }

    // ------------------------------------------------------------------------------------------------ helpers

    /** @param callable(int):array{ok:bool,error?:string} $do */
    private function act(int $postId, string $what, callable $do): Response
    {
        $this->authorize('social.share');
        $row = SocialPost::find($postId);
        if ($row === null || !ShareHook::mayUse(Newsletter::find((int) $row['newsletter_id']))) {
            return $this->error('not_found', 'That post no longer exists.', 404);
        }
        $res = $do($postId);
        if (!$res['ok']) {
            return $this->error('conflict', (string) ($res['error'] ?? 'The change could not be made.'), 409);
        }
        if (in_array($what, ['retry', 'post-anyway'], true)) {
            SocialDispatcher::afterResponse((int) $row['newsletter_id']);
        }
        return $this->json(['ok' => true, 'status' => ShareHook::postStatus(SocialPost::find($postId))]);
    }

    /** @return list<string> */
    private function channelList(Request $request): array
    {
        $c = $request->post('channels', []);
        return is_array($c) ? array_values(array_filter($c, 'is_string')) : [];
    }

    /** @param array{ok:bool,queued:list<string>,errors:array<string,string>} $res */
    private function queued(int $id, array $res): Response
    {
        if ($res['queued'] === [] && $res['errors'] !== []) {
            return $this->validation($res['errors']);
        }
        return $this->json(['ok' => $res['ok'], 'queued' => $res['queued'], 'errors' => (object) $res['errors'], 'panel' => ShareHook::panel($id)] + (isset($res['share_version']) ? ['share_version' => $res['share_version']] : []));
    }

    /** @return array<string,array<string,mixed>> */
    private function statuses(int $id): array
    {
        $out = [];
        foreach (PostQueue::latestByChannel($id) as $ch => $row) {
            $out[$ch] = ShareHook::postStatus($row);
        }
        return $out;
    }

    /** @param array<string,string> $errors */
    private function validation(array $errors): Response
    {
        return $this->json(['error' => 'validation', 'message' => $errors !== [] ? (string) reset($errors) : 'Please check the choices.', 'errors' => (object) $errors], 422);
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
