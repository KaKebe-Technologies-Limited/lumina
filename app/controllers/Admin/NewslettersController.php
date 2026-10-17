<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Web\NewsletterController as PublicNewsletterController;
use App\Core\AdminController;
use App\Core\Flash;
use App\Core\Request;
use App\Core\Response;
use App\Core\SitePages;
use App\Models\Newsletter;
use App\Services\Newsletter\NewsletterContent;
use App\Services\Newsletter\NewsletterRenderer;
use App\Services\Newsletter\NewsletterService;

/**
 * Admin > Newsletter pages (HTML): issues list, create, the two-pane editor, full-page preview, duplicate, delete.
 * OWNER: EDITOR agent (this file, views/admin/newsletters/* except mail/, scss/admin/_newsletter.scss, assets/js/admin-newsletter.js).
 * The architect delivered the list, create/store, preview, duplicate and delete as a working baseline and the editor page (edit) as a STUB:
 * the editor agent builds the real editor UI on top of the JSON API (NewsletterApiController). Keep the URLs. Contract: docs/NEWSLETTER.md section 7.
 */
final class NewslettersController extends AdminController
{
    /** GET /admin/newsletters?status=&q=&page= */
    public function index(Request $request): Response
    {
        $this->authorize('newsletter.manage');
        $filters = ['status' => $request->string('status'), 'q' => $request->string('q'), 'email_status' => $request->string('email_status')];
        $p = NewsletterService::adminList($filters, $request->page(), $this->perPage());
        return $this->render('admin/newsletters/index', [
            'pagination' => $p,
            'filters'    => $filters,
            'counts'     => NewsletterService::statusCounts(),
            'canSend'    => can('newsletter.send'),
            'canDeletePublished' => can('newsletter.delete_published'),
        ], ['title' => 'Newsletter', 'nav' => 'newsletters', 'breadcrumbs' => [['Newsletter', null]]]);
    }

    /** GET /admin/newsletters/create - a small form (title, number, date); POST creates the draft and opens the editor. */
    public function create(Request $request): Response
    {
        $this->authorize('newsletter.manage');
        return $this->render('admin/newsletters/create', [
            'nextNumber' => NewsletterService::nextIssueNumber(),
        ], ['title' => 'New issue', 'nav' => 'newsletter_create', 'breadcrumbs' => [['Newsletter', url('admin/newsletters')], ['New issue', null]]]);
    }

    /** POST /admin/newsletters */
    public function store(Request $request): Response
    {
        $this->authorize('newsletter.manage');
        $res = NewsletterService::create($request->only(['title', 'issue_number', 'issue_date', 'summary']), $this->userId());
        if (!$res['ok']) {
            $this->fail($res['errors'] ?? ['title' => 'Please check the form.'], 'admin/newsletters/create');
        }
        Flash::success('The draft was created. Write your issue below.');
        return $this->redirect('admin/newsletters/' . (int) $res['newsletter']['id'] . '/edit');
    }

    /**
     * GET /admin/newsletters/{id}/edit - THE EDITOR: two panes (editing left, server-rendered live preview right). The page embeds the issue
     * (the same JSON shape as GET /admin/api/newsletters/{id}) and a few settings as a JSON data block; assets/js/admin-newsletter.js builds the
     * block list from it and talks to the JSON API (autosave, preview, publish, revisions...).
     */
    public function edit(Request $request, int $id): Response
    {
        $this->authorize('newsletter.manage');
        NewsletterService::resolveScheduled();          // a scheduled issue whose time has come opens as published
        $row = Newsletter::findOrFail($id);
        $item = NewsletterApiController::itemFor($row, true);
        $boot = [
            'item'    => $item,
            'api'     => url('admin/api/newsletters'),
            'site'    => (string) setting('site_name', ''),
            'urls'    => [
                'list'      => url('admin/newsletters'),
                'edit'      => url('admin/newsletters/' . $id . '/edit'),
                'preview'   => url('admin/newsletters/' . $id . '/preview'),
                'email'     => can('newsletter.send') ? url('admin/newsletters/' . $id . '/email') : '',
                'public'    => abs_url('newsletter'),
                'slug_base' => abs_url('newsletter/'),
                'archive'   => url('newsletter/archive'),
                'login'     => url('admin/login'),
            ],
            'now'     => date('Y-m-d\TH:i'),
            'tz'      => (string) date_default_timezone_get(),
            'next'    => NewsletterService::nextIssueNumber(),
            'can'     => [
                'send'             => can('newsletter.send'),
                'delete_published' => can('newsletter.delete_published'),
            ],
            'labels'  => ['status' => NewsletterService::STATUS_LABELS, 'tones' => NewsletterService::STATUS_TONES],
            'limits'  => ['blocks' => NewsletterContent::MAX_BLOCKS],
            'pages'   => $this->linkSuggestions(),
        ];
        return $this->render('admin/newsletters/edit', [
            'issue'   => $row,
            'item'    => $item,
            'boot'    => $boot,
            'pages'   => $boot['pages'],
            'canSend' => can('newsletter.send'),
            'canDeletePublished' => can('newsletter.delete_published'),
        ], ['title' => 'Edit issue', 'nav' => 'newsletters', 'breadcrumbs' => [['Newsletter', url('admin/newsletters')], [(string) $row['title'], null]]]);
    }

    /**
     * Pages of this site the editor suggests when someone types a link address ("/programs  Programs"): the published registry pages and the
     * custom pages, without the technical ones. Only shown as hints; any http(s), mailto: or tel: address can still be typed.
     *
     * @return list<array{url:string,title:string}>
     */
    private function linkSuggestions(): array
    {
        $out = [['url' => '/', 'title' => 'Home page']];
        foreach (SitePages::all() as $slug => $p) {
            $route = (string) ($p['route'] ?? '');
            if (!empty($p['virtual']) || $route === '' || $route === '/' || empty($p['is_published']) || str_starts_with($slug, 'site_') || $slug === 'global' || $slug === 'membership-terms') {
                continue;
            }
            $out[] = ['url' => $route, 'title' => (string) $p['title']];
        }
        return $out;
    }
    /**
     * GET /admin/newsletters/{id}/preview - the issue as visitors would see it (site header and footer), "Preview - not published" banner,
     * noindex, no-store. Works for any status. Shows the SAVED state (the editor's unsaved state is previewed through the API in the live pane).
     */
    public function preview(Request $request, int $id): Response
    {
        $this->authorize('newsletter.manage');
        return (new PublicNewsletterController($request))->previewResponse(Newsletter::findOrFail($id));
    }

    /** POST /admin/newsletters/{id}/duplicate */
    public function duplicate(Request $request, int $id): Response
    {
        $this->authorize('newsletter.manage');
        $res = NewsletterService::duplicate($id, $this->userId());
        if (!$res['ok']) {
            Flash::error((string) ($res['error'] ?? 'The issue could not be copied.'));
            return $this->redirect('admin/newsletters');
        }
        Flash::success('A copy was created as a draft.');
        return $this->redirect('admin/newsletters/' . (int) $res['newsletter']['id'] . '/edit');
    }

    /** POST /admin/newsletters/{id}/delete (form with data-confirm). A published (or once public) issue can only be deleted by an administrator. */
    public function destroy(Request $request, int $id): Response
    {
        $this->authorize('newsletter.manage');
        $row = Newsletter::findOrFail($id);
        if (($row['status'] === 'published' || !empty($row['published_at'])) && !can('newsletter.delete_published')) {
            Flash::error('Only an administrator can delete an issue that has been published. You can unpublish it instead.');
            return $this->redirect('admin/newsletters');
        }
        $res = NewsletterService::delete($id);
        $res['ok'] ? Flash::success('The issue was deleted.') : Flash::error((string) $res['error']);
        return $this->redirect('admin/newsletters');
    }
}
