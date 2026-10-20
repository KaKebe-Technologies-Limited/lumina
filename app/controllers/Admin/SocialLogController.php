<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\AdminController;
use App\Core\Flash;
use App\Core\Request;
use App\Core\Response;
use App\Models\SocialPost;
use App\Services\Social\Channels;
use App\Services\Social\ShareHook;
use App\Services\Social\PostQueue;
use App\Services\Social\SocialConfig;
use App\Services\Social\SocialDispatcher;

/**
 * ADMIN > Social media > Share log (docs/SOCIAL.md section 2): every post with status, channel, time, the link the platform returned and a short error; filter by channel and
 * status; Retry / Skip / "Mark as posted" / "Post anyway" buttons. Ability social.share. OWNER: ADMIN agent builds the final screen; the architect delivered the working
 * controller and a plain view. The log never holds a token or a full payload.
 *
 *   GET  admin/social/log?channel=&status=&issue=&page=
 *   POST admin/social/log/{id}/retry         failed | skipped  -> queued again (posted right after the response)
 *   POST admin/social/log/{id}/skip          queued | failed | review -> skipped
 *   POST admin/social/log/{id}/mark-posted   review | failed -> posted  (field remote_url optional, https only)
 *   POST admin/social/log/{id}/post-anyway   review -> queued: the admin checked and it is NOT on the platform (or accepts a duplicate)
 */
final class SocialLogController extends AdminController
{
    public function index(Request $request): Response
    {
        $this->authorize('social.share');
        $filters = ['channel' => $request->string('channel'), 'status' => $request->string('status'), 'newsletter_id' => $request->int('issue')];
        $p = PostQueue::log($filters, $request->page(), 25);
        return $this->render('admin/social/log', [
            'pagination' => $p, 'rows' => $p->items, 'filters' => $filters, 'counts' => PostQueue::counts(), 'attention' => SocialConfig::attention(true),
            'canManage' => can('social.manage'), 'labels' => SocialPost::STATUS_LABELS, 'tones' => SocialPost::STATUS_TONES,
        ], ['title' => 'Share log', 'nav' => 'social_log', 'breadcrumbs' => [['Social media', can('social.manage') ? url('admin/social') : null], ['Share log', null]]]);
    }

    public function retry(Request $request, int $id): Response
    {
        return $this->act($id, 'retry', fn(): array => PostQueue::retry($id, $this->userId()), 'The post is queued again.');
    }

    public function skip(Request $request, int $id): Response
    {
        return $this->act($id, 'skip', fn(): array => PostQueue::skip($id, $this->userId()), 'The post was skipped.');
    }

    public function markPosted(Request $request, int $id): Response
    {
        $url = $request->string('remote_url');
        return $this->act($id, 'mark-posted', fn(): array => PostQueue::markPostedByAdmin($id, $url !== '' ? $url : null, $this->userId()), 'The post was marked as posted.');
    }

    public function postAnyway(Request $request, int $id): Response
    {
        return $this->act($id, 'post-anyway', fn(): array => PostQueue::retryAnyway($id, $this->userId()), 'The post is queued again.');
    }

    /** Authorise FIRST (defence in depth next to the route middleware), look the row up, and only then change anything. @param callable():array{ok:bool,error?:string} $do */
    private function act(int $id, string $what, callable $do, string $okMessage): Response
    {
        $this->authorize('social.share');
        $row = SocialPost::find($id);
        if ($row === null || !ShareHook::mayUse(\App\Models\Newsletter::find((int) $row['newsletter_id']))) {
            throw new \App\Core\HttpException(404, 'Not found');
        }
        $res = $do();
        if ($res['ok']) {
            Flash::success($okMessage);
            if (in_array($what, ['retry', 'post-anyway'], true) && Channels::has((string) $row['channel'])) {
                SocialDispatcher::afterResponse((int) $row['newsletter_id']);
            }
        } else {
            Flash::error((string) ($res['error'] ?? 'The change could not be made.'));
        }
        return $this->back('admin/social/log');
    }
}
