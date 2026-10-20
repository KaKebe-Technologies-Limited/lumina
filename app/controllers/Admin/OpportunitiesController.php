<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Admin\Content\CrudController;
use App\Core\Auth;
use App\Core\Database;
use App\Core\Flash;
use App\Core\Request;
use App\Core\Response;
use App\Core\UploadException;
use App\Core\Uploader;
use App\Models\Opportunity;

/**
 * Admin CRUD for `opportunities` (/admin/opportunities): the ones the team types or uploads (source "manual") and the ones read from other websites
 * (source "feed"; see OpportunityFeedsController). Extras: approve one / approve all waiting items, an optional PDF (call text or flyer) stored privately
 * and served by the website at /opportunities/{slug}/file. Engine: Content\CrudController.
 */
final class OpportunitiesController extends CrudController
{
    protected function config(): array
    {
        return [
            'entity' => 'opportunity', 'table' => 'opportunities', 'singular' => 'opportunity', 'plural' => 'opportunities', 'title' => 'Opportunities',
            'path' => 'admin/opportunities', 'views' => 'admin/opportunities', 'nav' => 'opportunities',
            'search' => ['title', 'organisation', 'location', 'summary', 'source_name'],
            'sorts' => [
                'posted'   => 't.posted_at {dir}, t.id {dir}',
                'title'    => 't.title {dir}, t.id DESC',
                'deadline' => 't.deadline IS NULL, t.deadline {dir}, t.id DESC',
                'updated'  => 't.updated_at {dir}, t.id {dir}',
            ],
            'default_sort' => ['posted', 'desc'],
            'order_sql' => 'id ASC',
            'reorderable' => false,
            'public' => static fn(array $r): ?string => (!empty($r['is_active']) && empty($r['needs_review'])) ? 'opportunities/' . $r['slug'] : null,
        ];
    }

    protected function imageFields(): array
    {
        return ['image' => 'image_alt'];
    }

    protected function richColumns(): array
    {
        return ['description'];
    }

    protected function defaults(): array
    {
        return ['is_active' => 1, 'category' => 'scholarship', 'deadline' => null, 'is_featured' => 0, 'source' => 'manual', 'needs_review' => 0, 'sort_order' => 0];
    }

    protected function formData(?array $row): array
    {
        return ['categories' => Opportunity::categoryLabels(), 'pdfMaxMb' => (int) round(Uploader::pdfMaxBytes() / 1048576)];
    }

    protected function listData(): array
    {
        return ['categories' => Opportunity::categoryLabels()];
    }

    protected function tabs(): array
    {
        $shown = Opportunity::SHOWN;
        return [
            'all'    => ['label' => 'All', 'sql' => null, 'params' => []],
            'live'   => ['label' => 'On the website', 'sql' => $shown . ' AND ' . Opportunity::OPEN, 'params' => []],
            'review' => ['label' => 'Waiting for approval', 'sql' => 't.needs_review = 1', 'params' => []],
            'closed' => ['label' => 'Closed', 'sql' => 't.deadline < CURDATE()', 'params' => []],
            'hidden' => ['label' => 'Hidden', 'sql' => 't.is_active = 0', 'params' => []],
            'mine'   => ['label' => 'Added by us', 'sql' => "t.source = 'manual'", 'params' => []],
            'feeds'  => ['label' => 'From other websites', 'sql' => "t.source = 'feed'", 'params' => []],
        ];
    }

    protected function listSelect(): string
    {
        return 't.*, DATEDIFF(t.deadline, CURDATE()) AS days_left';
    }

    protected function applyFilters(Request $request, array &$where, array &$params): array
    {
        $cat = $request->string('category');
        if (isset(Opportunity::CATEGORIES[$cat])) {
            $where[] = 't.category = ?';
            $params[] = $cat;
            return ['category' => $cat];
        }
        return [];
    }

    protected function rules(?array $row): array
    {
        return [
            'rules' => [
                'title'        => 'required|max:255',
                'slug'         => 'nullable|max:190',
                'category'     => 'required|in:' . implode(',', array_keys(Opportunity::CATEGORIES)),
                'organisation' => 'nullable|max:190',
                'location'     => 'nullable|max:190',
                'summary'      => 'nullable|max:1000',
                'description'  => 'nullable|max:400000',
                'eligibility'  => 'nullable|max:5000',
                'benefits'     => 'nullable|max:5000',
                'how_to_apply' => 'nullable|max:5000',
                'deadline'     => 'nullable|date',
                'apply_url'    => 'nullable|url|max:500',
            ] + $this->commonRules(),
            'labels' => ['title' => 'Title', 'apply_url' => 'Application link', 'how_to_apply' => 'How to apply', 'seo_title' => 'SEO title', 'seo_description' => 'SEO description'],
            'messages' => ['category.in' => 'Please choose a type from the list.'],
        ];
    }

    protected function payload(Request $request, ?array $row, array $valid): array
    {
        $title = (string) $this->text($valid['title']);
        $data = [
            'slug'            => $this->slugFor($request, $row, $title),
            'title'           => $title,
            'category'        => (string) $valid['category'],
            'organisation'    => $this->text($valid['organisation']),
            'location'        => $this->text($valid['location']),
            'summary'         => $this->plain($valid['summary']),
            'description'     => $this->html($request->post('description')),
            'eligibility'     => $this->plain($valid['eligibility']),
            'benefits'        => $this->plain($valid['benefits']),
            'how_to_apply'    => $this->plain($valid['how_to_apply']),
            'deadline'        => $this->dateOrNull($valid['deadline']),
            'apply_url'       => $this->text($valid['apply_url']),
            'is_active'       => $request->bool('is_active') ? 1 : 0,
            'is_featured'     => $request->bool('is_featured') ? 1 : 0,
            'seo_title'       => $this->text($valid['seo_title']),
            'seo_description' => $this->text($valid['seo_description']),
        ];
        if ($row === null) {
            $data['source'] = 'manual';
            $data['created_by'] = Auth::id();
            $data['posted_at'] = date('Y-m-d H:i:s');
        } elseif (!empty($row['needs_review']) && $request->bool('is_active')) {
            $data['needs_review'] = 0;         // saving an item that was waiting, with "shown" ticked, is an approval
        }
        return $data;
    }

    /** Adds the optional PDF to what CrudController::gather() prepared. */
    protected function gather(Request $request, ?array $row): array
    {
        [$data, $media] = parent::gather($request, $row);
        $file = $request->file('attachment');
        if ($file !== null && (int) ($file['error'] ?? 0) !== UPLOAD_ERR_NO_FILE) {
            try {
                $pdf = Uploader::pdf($file, ['folder' => 'opportunities']);
            } catch (UploadException $e) {
                \App\Services\ImageCleanup::releaseMany($media['uploaded']);
                $this->fail(['attachment' => $e->getMessage()]);
            }
            $data['file_path'] = $pdf['path'];
            $data['file_name'] = $pdf['name'];
            $data['file_size'] = $pdf['size'];
        } elseif ($row !== null && $request->bool('remove_attachment')) {
            $data['file_path'] = null;
            $data['file_name'] = null;
            $data['file_size'] = null;
        }
        return [$data, $media];
    }

    protected function saved(string $mode, int $id, array $row, ?array $before): void
    {
        if ($before !== null && !empty($before['file_path']) && array_key_exists('file_path', $row) && (string) $row['file_path'] !== (string) $before['file_path']) {
            Uploader::deletePrivate((string) $before['file_path']);
        }
        $close = (string) ($row['deadline'] ?? '');
        if ($close !== '' && $close < date('Y-m-d')) {
            Flash::warning('The closing date has already passed, so this opportunity is shown as closed on the website.');
        }
    }

    public function delete(Request $request, int $id): Response
    {
        $row = $this->find($id);
        $res = parent::delete($request, $id);
        if (!empty($row['file_path'])) {
            Uploader::deletePrivate((string) $row['file_path']);
        }
        return $res;
    }

    /** POST {id}/approve: an item read from another website is now shown on the website. */
    public function approve(Request $request, int $id): Response
    {
        $this->authorize('content.manage');
        $row = $this->find($id);
        Database::update('opportunities', ['needs_review' => 0, 'is_active' => 1], 'id = ?', [$id]);
        $this->audit('opportunity.approve', 'opportunity', $id, 'Approved opportunity "' . $row['title'] . '"');
        if ($request->expectsJson()) {
            return $this->json(['ok' => true, 'id' => $id]);
        }
        Flash::success('"' . $row['title'] . '" is now shown on the website.');
        return Response::redirect($this->returnUrl($request, 'admin/opportunities'));
    }

    /** POST approve-all: every item waiting for approval is shown. */
    public function approveAll(Request $request): Response
    {
        $this->authorize('content.manage');
        $n = Database::execute('UPDATE opportunities SET needs_review = 0, is_active = 1 WHERE needs_review = 1');
        $this->audit('opportunity.approve_all', 'opportunity', null, 'Approved ' . $n . ' waiting opportunities');
        Flash::success($n === 0 ? 'Nothing was waiting for approval.' : $n . ' ' . ($n === 1 ? 'opportunity is' : 'opportunities are') . ' now shown on the website.');
        return Response::redirect(url('admin/opportunities'));
    }
}
