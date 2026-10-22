<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Admin\Inbox\InboxController;
use App\Core\CsvExport;
use App\Core\Database;
use App\Core\Flash;
use App\Core\HttpException;
use App\Core\Log;
use App\Core\Mailer;
use App\Core\Request;
use App\Core\Response;
use App\Models\AmbassadorApplication as Amb;
use App\Services\Inbox\Support;
use App\Services\Inbox\Where;
use App\Services\Web\Countries;

/**
 * Admin > Inbox > Ambassadors: the applications of people who offered to raise funds and lobby for the foundation (form on /involvement/ambassadors).
 * Workflow: new -> under review -> approved / on hold / declined, with private notes; the team can e-mail the applicant when the status changes. CSV export.
 * Needs the inbox ability (inbox.manage), deleting needs inbox.delete, the export export.csv.
 */
final class AmbassadorsController extends InboxController
{
    private const FROM = 'ambassador_applications r';
    private const SORTS = ['newest' => 'r.created_at DESC, r.id DESC', 'oldest' => 'r.created_at ASC, r.id ASC', 'amount' => 'r.annual_amount DESC, r.id DESC'];

    private function ready(): void
    {
        if (!Database::tableExists('ambassador_applications')) {
            throw new HttpException(404, 'Not found');
        }
    }

    public function index(Request $request): Response
    {
        $this->authorize('inbox.manage');
        if (!Database::tableExists('ambassador_applications')) {
            Flash::warning('The database update has not been imported yet. In phpMyAdmin, import lumina-live-import.sql into your database (it is safe to run again), then open this page again.');
            return $this->redirect('admin');
        }
        [$where, $f] = $this->filters();
        $page = $this->paged(self::FROM, 'r.*', $where, self::SORTS[$f['sort']]);
        return $this->render('admin/ambassadors/index', [
            'pagination' => $page, 'f' => $f,
            'tabs' => $this->tabs($this->statusCounts('ambassador_applications', Amb::STATUSES), Amb::STATUSES, Amb::LABELS),
            'regions' => Countries::REGIONS, 'canExport' => can('export.csv'),
        ], ['title' => 'Ambassadors', 'nav' => 'ambassadors', 'breadcrumbs' => [['Ambassadors', null]]]);
    }

    public function show(Request $request, int $id): Response
    {
        $this->authorize('inbox.manage');
        $this->ready();
        $r = Amb::findOrFail($id);
        return $this->render('admin/ambassadors/show', ['r' => $r, 'regions' => Countries::REGIONS, 'canDelete' => can('inbox.delete'),
            'reviewer' => !empty($r['reviewed_by']) ? (string) Database::value('SELECT name FROM users WHERE id = ?', [(int) $r['reviewed_by']], '') : ''],
            ['title' => 'Ambassador: ' . (string) $r['name'], 'nav' => 'ambassadors', 'breadcrumbs' => [['Ambassadors', url('admin/ambassadors')], [(string) $r['name'], null]]]);
    }

    public function update(Request $request, int $id): Response
    {
        $this->authorize('inbox.manage');
        $this->ready();
        $r = Amb::findOrFail($id);
        $data = $this->validate([
            'status' => 'required|in:' . implode(',', Amb::STATUSES), 'admin_notes' => 'nullable|max:5000', 'applicant_note' => 'nullable|max:1000',
        ], [], ['status' => 'Status', 'admin_notes' => 'Notes', 'applicant_note' => 'Message to the applicant'], 'admin/ambassadors/' . $id);
        $changed = $data['status'] !== $r['status'];
        $upd = ['status' => $data['status'], 'admin_notes' => ($data['admin_notes'] ?? '') !== '' ? $data['admin_notes'] : null];
        if ($changed) {
            $upd['reviewed_by'] = $this->userId();
            $upd['reviewed_at'] = date('Y-m-d H:i:s');
        }
        Amb::update($id, $upd);
        $this->audit('ambassador.update', 'ambassador', $id, 'Updated the ambassador application of "' . $r['name'] . '" (' . $data['status'] . ')', ['status' => $data['status']]);
        $mailed = null;
        if ($changed && $request->bool('email_applicant') && in_array($data['status'], ['approved', 'declined', 'on_hold'], true)) {
            $mailed = $this->mailApplicant($r, (string) $data['status'], trim((string) ($data['applicant_note'] ?? '')));
        }
        Flash::success('Saved.' . ($mailed === true ? ' The applicant was e-mailed.' : ($mailed === false ? ' The e-mail to the applicant could not be sent.' : '')));
        return $this->redirect('admin/ambassadors/' . $id);
    }

    /** @param array<string,mixed> $r */
    private function mailApplicant(array $r, string $status, string $note): bool
    {
        $site = trim((string) setting('site_name', 'Lumina Africa Foundation')) ?: 'Lumina Africa Foundation';
        $first = explode(' ', trim((string) $r['name']))[0] ?: 'there';
        $noteLine = $note !== '' ? "\n\nA message from our team:\n" . $note : '';
        if ($status === 'approved') {
            $subject = 'Welcome: you are now a ' . $site . ' ambassador';
            $body = "Hello {$first},\n\nWe are delighted to welcome you as an ambassador of {$site}. Thank you for offering your time, your network and your support." . $noteLine
                . "\n\nOur team will be in touch with your briefing pack and to agree the details of your yearly pledge.\n\nWith thanks,\n{$site}\n";
        } elseif ($status === 'on_hold') {
            $subject = 'Your ambassador application: an update';
            $body = "Hello {$first},\n\nThank you for applying to become an ambassador of {$site}. We are keeping your application on hold for now and will come back to you." . $noteLine . "\n\nWith thanks,\n{$site}\n";
        } else {
            $subject = 'Your ambassador application';
            $body = "Hello {$first},\n\nThank you for offering to become an ambassador of {$site}. After careful review we are not able to take your application forward at this time." . $noteLine
                . "\n\nYou are always welcome to support our work in other ways, for example by following our news, volunteering or donating.\n\nWith thanks,\n{$site}\n";
        }
        try {
            return Mailer::send((string) $r['email'], $subject, $body);
        } catch (\Throwable) {
            Log::warning('Ambassador e-mail could not be sent', ['id' => (int) $r['id']]);
            return false;
        }
    }

    public function destroy(Request $request, int $id): Response
    {
        $this->authorize('inbox.manage', 'inbox.delete');
        $this->ready();
        $r = Amb::findOrFail($id);
        Amb::delete($id);
        $this->audit('ambassador.delete', 'ambassador', $id, 'Deleted the ambassador application of "' . $r['name'] . '"');
        Flash::success('Application deleted.');
        return $this->redirect('admin/ambassadors');
    }

    public function export(Request $request): Response
    {
        $this->authorize('inbox.manage', 'export.csv');
        $this->ready();
        [$where] = $this->filters();
        $rows = $this->exportRows(self::FROM, 'r.*', $where, 'r.created_at DESC, r.id DESC');
        $this->audit('ambassador.export', 'ambassador', null, 'Exported ' . $this->items(count($rows), 'application') . ' to CSV');
        $out = [];
        foreach ($rows as $r) {
            $roles = implode('; ', array_map(static fn(string $k): string => Amb::ROLES[$k] ?? $k, Amb::jsonList($r['roles'])));
            $out[] = [$r['id'], $r['created_at'], $r['name'], $r['email'], $r['phone'], Countries::REGIONS[$r['region']] ?? $r['region'], $r['country'], $r['city'], $r['occupation'], $r['organisation'],
                $r['profile_url'], $roles, implode(' + ', array_map(static fn(string $k): string => Amb::PLEDGES[$k] ?? $k, Amb::pledgeList($r))), $r['annual_amount'], $r['currency'],
                implode('; ', array_map(static fn(string $k): string => Amb::INKIND[$k] ?? $k, Amb::jsonList($r['inkind_items']))), $r['inkind_notes'], $r['other_text'], $r['network'], $r['motivation'],
                Amb::AVAILABILITY[$r['availability'] ?? ''] ?? '', $r['public_ok'] ? 'yes' : 'no', Amb::LABELS[$r['status']] ?? $r['status'], $r['admin_notes']];
        }
        return CsvExport::response('ambassadors-' . date('Y-m-d') . '.csv', ['ID', 'Received', 'Name', 'Email', 'Phone', 'Region', 'Country', 'City', 'Occupation', 'Organisation', 'Profile',
            'Can help by', 'Pledge', 'Amount per year', 'Currency', 'In-kind items', 'In-kind notes', 'Other', 'Network', 'Why', 'Availability', 'May be shown', 'Status', 'Notes'], $out);
    }

    /** @return array{0:Where,1:array{q:string,status:string,region:string,pledge:string,sort:string}} */
    private function filters(): array
    {
        $q = $this->request->string('q');
        $status = $this->request->string('status', 'all');
        $status = in_array($status, Amb::STATUSES, true) ? $status : 'all';
        $region = $this->request->string('region');
        $region = isset(Countries::REGIONS[$region]) ? $region : '';
        $pledge = $this->request->string('pledge');
        $pledge = isset(Amb::PLEDGES[$pledge]) ? $pledge : '';
        $sort = $this->request->string('sort', 'newest');
        $sort = isset(self::SORTS[$sort]) ? $sort : 'newest';
        $w = new Where();
        $w->addLike(['r.name', 'r.email', 'r.country', 'r.city', 'r.occupation', 'r.organisation', 'r.motivation'], Support::like($q));
        if ($status !== 'all') {
            $w->add('r.status = ?', [$status]);
        }
        if ($region !== '') {
            $w->add('r.region = ?', [$region]);
        }
        if ($pledge !== '') {
            $w->add('FIND_IN_SET(?, r.pledge_types) > 0', [$pledge]);
        }
        return [$w, ['q' => $q, 'status' => $status, 'region' => $region, 'pledge' => $pledge, 'sort' => $sort]];
    }
}
