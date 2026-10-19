<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Core\ImageOptimizer;
use App\Core\Request;
use App\Core\Response;
use App\Core\UploadException;
use App\Core\View;
use App\Models\Partner;
use App\Services\PartnerLogos;

/**
 * JSON endpoints behind the fast Partners screen (quick add, inline edit, drag-and-drop order, bulk actions, logo replace).
 * OWNER: PARTNERS agent (docs/PARTNERS_NEWSLETTER.md section 6). URLs, verbs and item shape are the contract's; two additive extras:
 *   - every response that returns ONE item also carries `html` = the server-rendered card (admin/partners/_card), so the screen never
 *     re-implements the card markup in JavaScript and always shows exactly what the website shows;
 *   - every write answers `meta` = {total, active, hidden, pending_logos, notice} so the counters on the screen never go stale.
 *
 * Auth: session cookie + header X-CSRF-TOKEN on every POST, Accept: application/json. Abilities: content.manage (admin + editor) for everything,
 * content.delete (admin + editor) for delete / bulk delete; each method calls $this->authorize() as well as the route middleware.
 * Error shape: {"error":"<code>","message":"<human text>", "errors":{field:message}} with validation (422), not_found (404), forbidden (403).
 * Writes: AuditLog ids only (partner.create|update|delete|reorder|bulk|logo|optimise). After EVERY write nothing else is needed for the website: it
 * reads the table on every request, there is no cache to clear (contract section 10).
 *
 * The save rule for a logo (never loses a file, never deletes a shared one):
 *   $up = PartnerLogos::store($file) -> Partner::create/update($data + $up['columns']) -> on failure PartnerLogos::discard($up['result'])
 *   -> after success PartnerLogos::release($oldRow).
 */
final class PartnerApiController extends OrgAdminController
{
    protected function area(): string
    {
        return 'admin/partners';
    }

    // ------------------------------------------------------------------ reads

    /** GET /admin/api/partners -> {data:[item], meta:{total, active, hidden, pending_logos, notice}} (item shape: contract section 6) */
    public function index(Request $request): Response
    {
        $this->authorize('content.manage');
        $rows = Database::all('SELECT * FROM partners ORDER BY sort_order ASC, id ASC');
        return $this->json(['data' => array_map(fn(array $r): array => $this->item($r), $rows), 'meta' => $this->meta()]);
    }

    // ------------------------------------------------------------------ create / edit

    /** POST /admin/api/partners  multipart: name*, logo (file), website, partner_type, is_active -> 201 {data:item, html, warnings:[...], notice, meta} */
    public function store(Request $request): Response
    {
        $this->authorize('content.manage');
        $errors = [];
        $f = $this->fields($request, false, $errors);
        $file = $request->file('logo');
        $up = null;
        if ($errors === [] && $file !== null) {
            try {
                $up = PartnerLogos::store($file);
            } catch (UploadException $e) {
                $errors['logo'] = $e->getMessage();
            }
        }
        if ($errors !== []) {
            return $this->invalid($errors);
        }
        $data = $f + [
            'partner_type' => 'partner',
            'is_active'    => $request->has('is_active') ? $this->flag('is_active') : 1,
            'sort_order'   => $this->nextSortOrder('partners'),
        ];
        if ($up !== null) {
            $data += $up['columns'];
        }
        try {
            $id = Partner::create($data);
        } catch (\Throwable $e) {
            if ($up !== null) {
                PartnerLogos::discard($up['result']);
            }
            throw $e;
        }
        $this->audit('partner.create', 'partner', $id, 'Added partner #' . $id, ['type' => $data['partner_type'], 'active' => (bool) $data['is_active'], 'logo' => $up !== null]);
        return $this->json($this->one($id) + ['warnings' => $up['result']['warnings'] ?? [], 'notice' => $up['result']['notice'] ?? null], 201);
    }

    /** POST /admin/api/partners/{id}  JSON: any of name, website, partner_type, description, is_active (inline edit) -> {data:item, html, meta} */
    public function update(Request $request, int $id): Response
    {
        $this->authorize('content.manage');
        $row = Partner::find($id);
        if ($row === null) {
            return $this->gone();
        }
        $errors = [];
        $changes = $this->fields($request, true, $errors);
        if ($request->has('is_active')) {
            $changes['is_active'] = $this->flag('is_active');
        }
        if ($errors !== []) {
            return $this->invalid($errors);
        }
        $changed = [];
        foreach ($changes as $col => $val) {
            if ((string) ($row[$col] ?? '') !== (string) ($val ?? '')) {
                $changed[] = $col;
            }
        }
        if ($changed !== []) {
            Partner::update($id, $changes);
            $this->audit('partner.update', 'partner', $id, 'Edited partner #' . $id, ['fields' => $changed]);
        }
        return $this->json($this->one($id));
    }

    /** POST /admin/api/partners/{id}/logo  multipart: logo (file) -> {data:item, html, warnings, notice, meta}; the old files are released after the save */
    public function logo(Request $request, int $id): Response
    {
        $this->authorize('content.manage');
        $row = Partner::find($id);
        if ($row === null) {
            return $this->gone();
        }
        $file = $request->file('logo');
        if ($file === null) {
            return $this->invalid(['logo' => 'Choose a logo picture first.']);
        }
        try {
            $up = PartnerLogos::store($file);
        } catch (UploadException $e) {
            return $this->invalid(['logo' => $e->getMessage()]);
        }
        try {
            Partner::update($id, $up['columns']);
        } catch (\Throwable $e) {
            PartnerLogos::discard($up['result']);
            throw $e;
        }
        $released = PartnerLogos::release($row);               // AFTER the save: files another row / page still uses are kept
        $this->audit('partner.logo', 'partner', $id, 'Replaced the logo of partner #' . $id, ['optimised' => (bool) $up['columns']['logo_optimised'], 'released' => $released]);
        return $this->json($this->one($id) + ['warnings' => $up['result']['warnings'] ?? [], 'notice' => $up['result']['notice'] ?? null, 'released' => $released]);
    }

    /** POST /admin/api/partners/{id}/logo/remove -> {data:item, html, meta} (back to the name-only card) */
    public function logoRemove(Request $request, int $id): Response
    {
        $this->authorize('content.manage');
        $row = Partner::find($id);
        if ($row === null) {
            return $this->gone();
        }
        Partner::update($id, PartnerLogos::emptyColumns());
        $released = PartnerLogos::release($row);
        $this->audit('partner.logo', 'partner', $id, 'Removed the logo of partner #' . $id, ['released' => $released]);
        return $this->json($this->one($id) + ['released' => $released]);
    }

    /** POST /admin/api/partners/{id}/toggle  optional JSON is_active -> {data:item, html, meta} */
    public function toggle(Request $request, int $id): Response
    {
        $this->authorize('content.manage');
        $row = Partner::find($id);
        if ($row === null) {
            return $this->gone();
        }
        $new = $request->has('is_active') ? $this->flag('is_active') : ((int) $row['is_active'] === 1 ? 0 : 1);
        if ($new !== (int) $row['is_active']) {
            Partner::update($id, ['is_active' => $new]);
            $this->audit('partner.update', 'partner', $id, ($new ? 'Showed' : 'Hid') . ' partner #' . $id, ['fields' => ['is_active']]);
        }
        return $this->json($this->one($id));
    }

    // ------------------------------------------------------------------ delete

    /** POST /admin/api/partners/{id}/delete -> {ok:true, name, files_removed, meta} */
    public function destroy(Request $request, int $id): Response
    {
        $this->authorize('content.delete');
        $row = Partner::find($id);
        if ($row === null) {
            return $this->gone();
        }
        $removed = $this->remove($row);
        return $this->json(['ok' => true, 'id' => $id, 'name' => (string) $row['name'], 'files_removed' => $removed, 'meta' => $this->meta()]);
    }

    // ------------------------------------------------------------------ order + bulk

    /**
     * POST /admin/api/partners/reorder  JSON {ids:[all ids in the new order]} -> {ok:true, count} (sort_order = 10, 20, 30 ...)
     * Partners missing from the list (added by someone else while this page was open) keep their relative order and go after the listed ones,
     * so the numbering is always one clean sequence.
     */
    public function reorder(Request $request): Response
    {
        $this->authorize('content.manage');
        $ids = $this->postedIds();
        if ($ids === []) {
            return $this->invalid(['ids' => 'Nothing to reorder.']);
        }
        $existing = array_map('intval', array_column(Database::all('SELECT id FROM partners ORDER BY sort_order ASC, id ASC'), 'id'));
        $listed = array_values(array_intersect($ids, $existing));
        $final = array_merge($listed, array_values(array_diff($existing, $listed)));
        $n = $this->applyOrder('partners', $final);
        $this->audit('partner.reorder', 'partner', null, 'Reordered the partners', ['count' => $n]);
        return $this->json(['ok' => true, 'count' => $n]);
    }

    /** POST /admin/api/partners/bulk  JSON {action:'show'|'hide'|'delete', ids:[...]} -> {ok:true, count, ids, meta}; show / hide also return `items` ({id:html}) */
    public function bulk(Request $request): Response
    {
        $this->authorize('content.manage');
        $action = $request->string('action');
        if (!in_array($action, ['show', 'hide', 'delete'], true)) {
            return $this->invalid(['action' => 'Choose show, hide or delete.']);
        }
        $ids = $this->postedIds();
        if ($ids === []) {
            return $this->invalid(['ids' => 'Select at least one partner first.']);
        }
        $rows = Database::all('SELECT * FROM partners WHERE id IN (' . implode(',', array_fill(0, count($ids), '?')) . ')', $ids);
        $found = array_map('intval', array_column($rows, 'id'));
        if ($found === []) {
            return $this->gone();
        }
        if ($action === 'delete') {
            $this->authorize('content.delete');
            $files = 0;
            foreach ($rows as $row) {
                $files += $this->remove($row);
            }
            $this->audit('partner.bulk', 'partner', null, 'Deleted ' . count($found) . ' partners', ['action' => 'delete', 'ids' => $found]);
            return $this->json(['ok' => true, 'count' => count($found), 'ids' => $found, 'files_removed' => $files, 'meta' => $this->meta()]);
        }
        $this->setFlag('partners', 'is_active', $action === 'show' ? 1 : 0, $found);
        $this->audit('partner.bulk', 'partner', null, ($action === 'show' ? 'Showed ' : 'Hid ') . count($found) . ' partners', ['action' => $action, 'ids' => $found]);
        $items = [];
        foreach (Database::all('SELECT * FROM partners WHERE id IN (' . implode(',', array_fill(0, count($found), '?')) . ')', $found) as $fresh) {
            $items[(string) $fresh['id']] = $this->card($fresh);
        }
        return $this->json(['ok' => true, 'count' => count($found), 'ids' => $found, 'items' => (object) $items, 'meta' => $this->meta()]);
    }

    // ------------------------------------------------------------------ logos uploaded before the pipeline existed

    /**
     * POST /admin/api/partners/optimise  JSON {after?:int, limit?:int} -> {done, failed, remaining, last_id, notice, errors:{id:message}, capabilities}
     * Runs the logo pipeline over logos uploaded before it existed, a few per call. Call again with `after = last_id` until `remaining` is 0.
     */
    public function optimise(Request $request): Response
    {
        $this->authorize('content.manage');
        $after = max(0, (int) $request->post('after', 0));
        $limit = max(1, min(25, (int) $request->post('limit', 5)));
        $res = PartnerLogos::optimiseExisting($limit, $after);
        $res['errors'] = (object) $res['errors'];
        $this->audit('partner.optimise', 'partner', null, 'Optimised existing partner logos', ['done' => $res['done'], 'failed' => $res['failed']]);
        return $this->json($res + ['capabilities' => ImageOptimizer::capabilities(), 'meta' => $this->meta()]);
    }

    // ------------------------------------------------------------------ internals

    /**
     * Validate the text fields. With $partial only the fields present in the request are returned (inline edit); otherwise name is required.
     * @param array<string,string> $errors filled by reference
     * @return array<string,mixed> columns to save
     */
    private function fields(Request $r, bool $partial, array &$errors): array
    {
        $out = [];
        if (!$partial || $r->has('name')) {
            $name = $this->clean($r->string('name'));
            if ($name === '') {
                $errors['name'] = 'Enter the organisation\'s name.';
            } elseif (mb_strlen($name) > 190) {
                $errors['name'] = 'The name can have at most 190 characters.';
            } else {
                $out['name'] = $name;
            }
        }
        if (!$partial || $r->has('partner_type')) {
            $type = $r->string('partner_type', 'partner');
            if (!in_array($type, Partner::TYPES, true)) {
                $errors['partner_type'] = 'Choose one of the listed types.';
            } else {
                $out['partner_type'] = $type;
            }
        }
        if ($r->has('website')) {
            $site = $this->webAddress($r->string('website'));
            if ($site === null) {
                $errors['website'] = 'Enter a full web address, for example https://www.example.org.';
            } else {
                $out['website'] = $site === '' ? null : $site;
            }
        }
        if ($r->has('description')) {
            $desc = $this->clean($r->string('description'), true);
            if (mb_strlen($desc) > 2000) {
                $errors['description'] = 'The description can have at most 2000 characters.';
            } else {
                $out['description'] = $desc === '' ? null : $desc;
            }
        }
        return $out;
    }

    /** Delete a partner row and its logo files (files another row still uses are kept). Returns the number of files removed. */
    private function remove(array $row): int
    {
        $id = (int) $row['id'];
        Partner::delete($id);
        $removed = PartnerLogos::release($row);
        $this->audit('partner.delete', 'partner', $id, 'Deleted partner #' . $id, ['files_removed' => $removed]);
        return $removed;
    }

    /** @return array<string,mixed> the item of the contract (+ type_label, edit_url) */
    private function item(array $row): array
    {
        $type = (string) $row['partner_type'];
        return [
            'id'           => (int) $row['id'],
            'name'         => (string) $row['name'],
            'partner_type' => $type,
            'type_label'   => PartnerController::TYPE_LABELS[$type] ?? label_from($type),
            'website'      => (string) ($row['website'] ?? ''),
            'description'  => (string) ($row['description'] ?? ''),
            'is_active'    => (int) $row['is_active'] === 1,
            'sort_order'   => (int) $row['sort_order'],
            'created_at'   => (string) $row['created_at'],
            'edit_url'     => url('admin/partners/' . (int) $row['id'] . '/edit'),
            'logo'         => PartnerLogos::view($row),
        ];
    }

    /** The server-rendered card of one partner. */
    private function card(array $row): string
    {
        return View::partial('admin/partners/_card', ['p' => $row, 'types' => PartnerController::TYPE_LABELS, 'canDelete' => can('content.delete')]);
    }

    /** @return array<string,mixed> {data:item, html:string, meta} for the partner as saved now */
    private function one(int $id): array
    {
        $row = Partner::find($id) ?? [];
        return ['data' => $this->item($row), 'html' => $this->card($row), 'meta' => $this->meta()];
    }

    /** @return array{total:int,active:int,hidden:int,pending_logos:int,missing_logos:int,notice:?string,about_limit:int,footer_limit:int} */
    private function meta(): array
    {
        $total = (int) Database::value('SELECT COUNT(*) FROM partners', [], 0);
        $active = (int) Database::value('SELECT COUNT(*) FROM partners WHERE is_active = 1', [], 0);
        return [
            'total' => $total, 'active' => $active, 'hidden' => $total - $active, 'pending_logos' => PartnerLogos::pendingCount(), 'missing_logos' => PartnerLogos::missingCount(), 'notice' => ImageOptimizer::capabilityNotice(),
            // how many active partners each place on the website shows (the first N in the website order), so the screen can say where a new partner appears
            'about_limit' => max(4, min(60, (int) content('about', 'partners.limit'))), 'footer_limit' => max(4, min(40, (int) content('global', 'footer.partners_limit'))),
        ];
    }

    /** @param array<string,string> $errors */
    private function invalid(array $errors): Response
    {
        return $this->json(['error' => 'validation', 'message' => (string) reset($errors), 'errors' => (object) $errors], 422);
    }

    private function gone(): Response
    {
        return $this->json(['error' => 'not_found', 'message' => 'That partner no longer exists. Reload the page to see the current list.'], 404);
    }
}
