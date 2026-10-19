<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Core\Flash;
use App\Core\ImageOptimizer;
use App\Core\Request;
use App\Core\Response;
use App\Core\SitePages;
use App\Core\UploadException;
use App\Core\Validator;
use App\Models\Partner;
use App\Services\PartnerLogos;

/**
 * Admin > Partners (/admin/partners). OWNER: B8 "org-admin", reworked by the PARTNERS agent (docs/PARTNERS_NEWSLETTER.md section 6).
 * The list is now the fast visual screen (quick add, card grid, drag-and-drop order, bulk actions: assets/js/admin-partners.js talking to
 * Admin\PartnerApiController); this class keeps the classic full form (create / edit with description and position), the plain POST routes
 * (toggle, move, bulk, delete, reorder page) and everything that must work without JavaScript.
 * Logos go through the optimisation pipeline (PartnerLogos::store): trimmed, scaled, WebP + PNG, 1x/2x, sizes stored. Replaced and deleted
 * logo files are removed with PartnerLogos::release() (a file that something else still uses is kept).
 * Public output: /partners and the About page show active partners; a partner without a logo is shown by name.
 */
final class PartnerController extends OrgAdminController
{
    /** Display labels for the partner_type enum (the model only lists the raw values). */
    public const TYPE_LABELS = ['partner' => 'Partner', 'funder' => 'Funder', 'donor' => 'Donor', 'collaborator' => 'Collaborator'];

    protected function area(): string
    {
        return 'admin/partners';
    }

    public function index(Request $request): Response
    {
        $this->authorize('content.manage');
        $rows = Database::all('SELECT * FROM partners ORDER BY sort_order ASC, id ASC');
        $active = 0;
        foreach ($rows as $r) {
            $active += (int) $r['is_active'] === 1 ? 1 : 0;
        }

        return $this->render('admin/partners/index', [
            'partners'  => $rows,
            'types'     => self::TYPE_LABELS,
            'canDelete' => can('content.delete'),
            'total'     => count($rows),
            'active'    => $active,
            'pending'   => PartnerLogos::pendingCount(),
            'notice'    => ImageOptimizer::capabilityNotice(),
            'hasGd'     => ImageOptimizer::capabilities()['gd'],
            'aboutLive' => SitePages::isPublished('about'),
        ], ['title' => 'Partners', 'nav' => 'partners', 'breadcrumbs' => [['Partners', null]]]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('content.manage');
        return $this->render('admin/partners/create', [
            'partner' => ['is_active' => 1, 'partner_type' => 'partner', 'sort_order' => ''],
            'types'   => self::TYPE_LABELS,
        ], ['title' => 'Add partner', 'nav' => 'partners', 'breadcrumbs' => [['Partners', url('admin/partners')], ['Add', null]]]);
    }

    public function store(Request $request): Response
    {
        $this->authorize('content.manage');
        $data = $this->collect('admin/partners/create');
        $up = $this->storeLogo($request, 'admin/partners/create');
        if ($data['sort_order'] === null) {
            $data['sort_order'] = $this->nextSortOrder('partners');
        }
        try {
            $id = Partner::create($data + ($up['columns'] ?? []));
        } catch (\Throwable $e) {
            if ($up !== null) {
                PartnerLogos::discard($up['result']);
            }
            throw $e;
        }
        $this->audit('partner.create', 'partner', $id, 'Added partner #' . $id, ['type' => $data['partner_type'], 'active' => (bool) $data['is_active'], 'logo' => $up !== null]);
        Flash::success('Partner "' . $data['name'] . '" was added.' . ($data['is_active'] ? '' : ' It is hidden from the website until you show it.'));
        $this->logoNotice($up);
        return $this->redirect('admin/partners');
    }

    public function edit(Request $request, int $id): Response
    {
        $this->authorize('content.manage');
        $partner = Partner::findOrFail($id);
        return $this->render('admin/partners/edit', [
            'partner' => $partner,
            'types'   => self::TYPE_LABELS,
        ], ['title' => 'Edit partner', 'nav' => 'partners', 'breadcrumbs' => [['Partners', url('admin/partners')], [$partner['name'], null]]]);
    }

    public function update(Request $request, int $id): Response
    {
        $this->authorize('content.manage');
        $row = Partner::findOrFail($id);
        $data = $this->collect('admin/partners/' . $id . '/edit');
        $up = $this->storeLogo($request, 'admin/partners/' . $id . '/edit');
        if ($up !== null) {
            $logoColumns = $up['columns'];
        } elseif ($request->string('logo_remove') === '1') {
            $logoColumns = PartnerLogos::emptyColumns();
        } else {
            $logoColumns = [];
        }
        if ($data['sort_order'] === null) {
            $data['sort_order'] = $this->nextSortOrder('partners');
        }
        try {
            Partner::update($id, $data + $logoColumns);
        } catch (\Throwable $e) {
            if ($up !== null) {
                PartnerLogos::discard($up['result']);
            }
            throw $e;
        }
        if ($logoColumns !== []) {
            PartnerLogos::release($row);                 // AFTER the save; a file another row still uses is kept
        }

        $changed = [];
        foreach (['name', 'partner_type', 'website', 'description', 'sort_order', 'is_active'] as $f) {
            if ((string) ($row[$f] ?? '') !== (string) ($data[$f] ?? '')) {
                $changed[] = $f;
            }
        }
        if ($logoColumns !== []) {
            $changed[] = 'logo';
        }
        $this->audit('partner.update', 'partner', $id, 'Edited partner #' . $id, ['fields' => $changed]);
        Flash::success('Partner "' . $data['name'] . '" was saved.');
        $this->logoNotice($up);
        return $this->redirect('admin/partners');
    }

    /** Show / hide a partner on the website (POST). */
    public function toggle(Request $request, int $id): Response
    {
        $this->authorize('content.manage');
        $row = Partner::findOrFail($id);
        $new = (int) $row['is_active'] === 1 ? 0 : 1;
        Partner::update($id, ['is_active' => $new]);
        $this->audit('partner.' . ($new ? 'activate' : 'deactivate'), 'partner', $id, ($new ? 'Showed' : 'Hid') . ' partner #' . $id);
        Flash::success('"' . $row['name'] . '" is now ' . ($new ? 'shown on' : 'hidden from') . ' the website.');
        return Response::redirect($this->returnUrl());
    }

    /** One step up/down in the overall partner order (POST direction=up|down). */
    public function move(Request $request, int $id): Response
    {
        $this->authorize('content.manage');
        $row = Partner::findOrFail($id);
        $dir = $request->string('direction') === 'up' ? 'up' : 'down';
        if ($this->moveWithin('partners', $id, $dir)) {
            $this->audit('partner.reorder', 'partner', $id, 'Moved partner #' . $id . ' ' . $dir);
            Flash::success('"' . $row['name'] . '" moved ' . $dir . '.');
        }
        return Response::redirect($this->returnUrl());
    }

    public function reorder(Request $request): Response
    {
        $this->authorize('content.manage');
        return $this->render('admin/partners/reorder', [
            'partners' => Database::all('SELECT * FROM partners ORDER BY sort_order ASC, id ASC'),
            'types'    => self::TYPE_LABELS,
        ], ['title' => 'Reorder partners', 'nav' => 'partners', 'breadcrumbs' => [['Partners', url('admin/partners')], ['Reorder', null]]]);
    }

    public function saveOrder(Request $request): Response
    {
        $this->authorize('content.manage');
        $n = $this->applyOrder('partners', $this->postedIds());
        if ($n > 0) {
            $this->audit('partner.reorder', 'partner', null, 'Reordered the partners (' . $n . ' partners)');
            Flash::success('The new order was saved.');
        } else {
            Flash::info('Nothing to reorder.');
        }
        return $this->redirect('admin/partners');
    }

    public function bulk(Request $request): Response
    {
        $this->authorize('content.manage');
        $ids = $this->postedIds();
        $action = $request->string('bulk_action');
        $back = Response::redirect($this->returnUrl());
        if ($ids === []) {
            Flash::warning('Select at least one partner first.');
            return $back;
        }
        switch ($action) {
            case 'activate':
            case 'deactivate':
                $this->setFlag('partners', 'is_active', $action === 'activate' ? 1 : 0, $ids);
                $this->audit('partner.bulk', 'partner', null, ucfirst($action) . 'd ' . count($ids) . ' partners', ['ids' => $ids, 'action' => $action]);
                Flash::success($this->plural(count($ids), 'partner', 'partners') . ($action === 'activate' ? ' shown on the website.' : ' hidden from the website.'));
                break;
            case 'delete':
                $this->authorize('content.delete');
                $n = $this->deleteMany($ids);
                Flash::success($this->plural($n, 'partner', 'partners') . ' deleted.');
                break;
            default:
                Flash::error('Choose an action to apply to the selected partners.');
        }
        return $back;
    }

    public function destroy(Request $request, int $id): Response
    {
        $this->authorize('content.delete');
        $row = Partner::findOrFail($id);
        $this->deleteMany([$id]);
        Flash::success('Partner "' . $row['name'] . '" was deleted.');
        return Response::redirect($this->returnUrl());
    }

    // ------------------------------------------------------------------ internals

    /** @param int[] $ids */
    private function deleteMany(array $ids): int
    {
        $n = 0;
        foreach ($ids as $id) {
            $row = Partner::find($id);
            if ($row === null) {
                continue;
            }
            Partner::delete($id);
            $removed = PartnerLogos::release($row);      // every logo file of the row, except files something else still uses
            $this->audit('partner.delete', 'partner', $id, 'Deleted partner #' . $id, ['files_removed' => $removed]);
            $n++;
        }
        return $n;
    }

    /**
     * Run the logo pipeline for the file posted as "logo" (null when no file was chosen). A bad picture sends the admin back to the form with a
     * message under the field. The new files exist when this returns: the caller saves the row, or calls PartnerLogos::discard().
     * @return array{columns:array<string,mixed>,result:array<string,mixed>}|null
     */
    private function storeLogo(Request $request, string $redirectTo): ?array
    {
        $file = $request->file('logo');
        if ($file === null) {
            return null;
        }
        try {
            return PartnerLogos::store($file);
        } catch (UploadException $e) {
            $this->fail(['logo' => $e->getMessage()], $redirectTo);
        }
    }

    /** @param array{columns:array<string,mixed>,result:array<string,mixed>}|null $up */
    private function logoNotice(?array $up): void
    {
        if ($up === null) {
            return;
        }
        $warnings = (array) ($up['result']['warnings'] ?? []);
        if (!empty($up['result']['notice'])) {
            Flash::warning((string) $up['result']['notice']);
        } elseif (in_array('low_resolution', $warnings, true)) {
            Flash::info('The logo is quite small, so it may look soft on high-resolution screens. A PNG at least 400 px wide gives the sharpest result.');
        }
    }

    /**
     * Validate the form. Returns the columns to save (the logo is added by the caller after validation).
     * @return array<string,mixed>
     */
    private function collect(string $redirectTo): array
    {
        $r = $this->request;
        $v = Validator::make($r->all(), [
            'name'         => 'required|max:190',
            'partner_type' => 'required|in:' . implode(',', Partner::TYPES),
            'description'  => 'nullable|max:2000',
            'sort_order'   => 'nullable|integer|between:0,100000',
        ], [
            'partner_type.required' => 'Choose what kind of organisation this is.',
            'partner_type.in'       => 'Choose one of the listed types.',
            'sort_order.integer'    => 'The position must be a whole number, or leave it empty to put the partner last.',
            'sort_order.between'    => 'The position must be between 0 and 100000.',
        ], ['name' => 'Organisation name', 'partner_type' => 'Type', 'description' => 'Description']);
        $errors = $v->errors();
        $this->requireText($errors, 'name', $this->clean($r->string('name')), 'Organisation name is required.');

        $website = $this->webAddress($r->string('website'));
        if ($website === null) {
            $errors['website'] ??= 'Enter a full web address, for example https://www.example.org.';
        }
        if ($errors !== []) {
            $this->fail($errors, $redirectTo);
        }

        $sort = $r->string('sort_order');
        return [
            'name'         => $this->clean($r->string('name')),
            'partner_type' => $r->string('partner_type'),
            'website'      => ($website ?? '') ?: null,
            'description'  => $this->clean($r->string('description'), true) ?: null,
            'sort_order'   => $sort === '' ? null : (int) $sort,
            'is_active'    => $this->flag('is_active'),
        ];
    }
}
