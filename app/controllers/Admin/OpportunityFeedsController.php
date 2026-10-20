<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\AdminController;
use App\Core\Database;
use App\Core\Flash;
use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Models\Opportunity;
use App\Services\Opportunities\FeedClient;
use App\Services\Opportunities\OpportunityImporter;

/**
 * Admin > Opportunities > Sources (/admin/opportunity-sources): the websites whose RSS/Atom feeds are read for opportunities.
 * Add, edit, switch on/off, delete, "Fetch now" for one source or all. Ability content.manage (delete: content.delete).
 * The web address is checked like a webhook (public website only: FeedClient::guard) before it is saved or fetched.
 */
final class OpportunityFeedsController extends AdminController
{
    public function index(Request $request): Response
    {
        $this->authorize('content.manage');
        $rows = Database::all(
            'SELECT f.*, (SELECT COUNT(*) FROM opportunities o WHERE o.feed_id = f.id) AS items FROM opportunity_feeds f ORDER BY f.is_active DESC, f.name ASC'
        );
        return $this->render('admin/opportunities/sources', [
            'rows'       => $rows,
            'categories' => Opportunity::categoryLabels(),
            'enabled'    => OpportunityImporter::enabled(),
            'hours'      => OpportunityImporter::intervalHours(),
            'waiting'    => (int) Database::value('SELECT COUNT(*) FROM opportunities WHERE needs_review = 1', [], 0),
        ], ['title' => 'Opportunity sources', 'nav' => 'opportunities', 'breadcrumbs' => [['Opportunities', url('admin/opportunities')], ['Sources', null]]]);
    }

    public function store(Request $request): Response
    {
        $this->authorize('content.manage');
        $data = $this->collect(null);
        if ($data === null) {
            return $this->redirect('admin/opportunity-sources');
        }
        $id = Database::insert('opportunity_feeds', $data);
        $this->audit('opportunity.source.create', 'opportunity_feed', $id, 'Added opportunity source "' . $data['name'] . '"');
        $feed = $this->find($id);
        $r = OpportunityImporter::refreshFeed($feed);
        if ($r['error'] !== null) {
            Flash::warning('"' . $data['name'] . '" was added, but reading it failed: ' . $r['error']);
        } else {
            Flash::success('"' . $data['name'] . '" was added. ' . $r['new'] . ' ' . ($r['new'] === 1 ? 'opportunity was' : 'opportunities were') . ' found.');
        }
        return $this->redirect('admin/opportunity-sources');
    }

    public function update(Request $request, int $id): Response
    {
        $this->authorize('content.manage');
        $row = $this->find($id);
        $data = $this->collect($row);
        if ($data === null) {
            return $this->redirect('admin/opportunity-sources');
        }
        Database::update('opportunity_feeds', $data, 'id = ?', [$id]);
        $this->audit('opportunity.source.update', 'opportunity_feed', $id, 'Edited opportunity source "' . $data['name'] . '"');
        Flash::success('"' . $data['name'] . '" was saved.');
        return $this->redirect('admin/opportunity-sources');
    }

    public function toggle(Request $request, int $id): Response
    {
        $this->authorize('content.manage');
        $row = $this->find($id);
        $new = empty($row['is_active']) ? 1 : 0;
        Database::update('opportunity_feeds', ['is_active' => $new], 'id = ?', [$id]);
        $this->audit('opportunity.source.' . ($new ? 'activate' : 'deactivate'), 'opportunity_feed', $id, ($new ? 'Switched on' : 'Switched off') . ' opportunity source "' . $row['name'] . '"');
        Flash::success('"' . $row['name'] . '" is now ' . ($new ? 'on. It is read automatically.' : 'off. Nothing new is read from it.'));
        return $this->redirect('admin/opportunity-sources');
    }

    public function destroy(Request $request, int $id): Response
    {
        $this->authorize('content.delete');
        $row = $this->find($id);
        $removeItems = $request->bool('remove_items');
        $n = 0;
        if ($removeItems) {
            $n = Database::execute('DELETE FROM opportunities WHERE feed_id = ? AND is_featured = 0', [$id]);
        }
        Database::execute('DELETE FROM opportunity_feeds WHERE id = ?', [$id]);
        $this->audit('opportunity.source.delete', 'opportunity_feed', $id, 'Deleted opportunity source "' . $row['name'] . '"', ['items_removed' => $n]);
        Flash::success('"' . $row['name'] . '" was deleted.' . ($removeItems ? ' Its ' . $n . ' opportunities were removed too.' : ' Its opportunities stay on the website until they close.'));
        return $this->redirect('admin/opportunity-sources');
    }

    /** POST {id}/fetch: read one source now. */
    public function fetch(Request $request, int $id): Response
    {
        $this->authorize('content.manage');
        $row = $this->find($id);
        $r = OpportunityImporter::refreshFeed($row);
        if ($r['error'] !== null) {
            Flash::error('Could not read "' . $row['name'] . '": ' . $r['error']);
        } else {
            Flash::success('"' . $row['name'] . '": ' . $r['new'] . ' new ' . ($r['new'] === 1 ? 'opportunity' : 'opportunities') . '.');
        }
        return $this->redirect('admin/opportunity-sources');
    }

    /** POST fetch-all: read every active source now. */
    public function fetchAll(Request $request): Response
    {
        $this->authorize('content.manage');
        $r = OpportunityImporter::refreshDue(true, 40);
        if ($r['feeds'] === 0) {
            Flash::warning('There is no source switched on.');
        } elseif ($r['errors'] > 0) {
            Flash::warning($r['new'] . ' new from ' . $r['feeds'] . ' sources. ' . $r['errors'] . ' could not be read (see the Last check column).');
        } else {
            Flash::success($r['new'] . ' new ' . ($r['new'] === 1 ? 'opportunity' : 'opportunities') . ' from ' . $r['feeds'] . ' sources.');
        }
        return $this->redirect('admin/opportunity-sources');
    }

    // ------------------------------------------------------------------------------------------------------------------

    private function find(int $id): array
    {
        return Database::one('SELECT * FROM opportunity_feeds WHERE id = ? LIMIT 1', [$id]) ?? throw new HttpException(404, 'Not found');
    }

    /** @return array<string,mixed>|null null = an error was flashed */
    private function collect(?array $row): ?array
    {
        $r = $this->request;
        $name = trim((string) preg_replace('/\p{C}+/u', '', $r->string('name')));
        if ($name === '' || mb_strlen($name) > 120) {
            Flash::error('Give the source a name of up to 120 characters, for example "Opportunity Desk".');
            return null;
        }
        $url = trim($r->string('url'));
        $g = FeedClient::guard($url);
        if (!$g['ok']) {
            Flash::error('The feed address cannot be used: ' . $g['error']);
            return null;
        }
        $hash = sha1($g['url']);
        $dup = Database::value('SELECT id FROM opportunity_feeds WHERE url_hash = ?' . ($row !== null ? ' AND id <> ?' : ''), $row !== null ? [$hash, (int) $row['id']] : [$hash]);
        if ($dup !== null) {
            Flash::error('That feed is already in the list.');
            return null;
        }
        $cat = $r->string('default_category');
        return [
            'name'             => $name,
            'url'              => $g['url'],
            'url_hash'         => $hash,
            'default_category' => isset(Opportunity::CATEGORIES[$cat]) ? $cat : 'other',
            'is_active'        => $row === null ? 1 : ($r->bool('is_active') ? 1 : 0),
        ];
    }
}
