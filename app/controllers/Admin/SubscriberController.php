<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Admin\Inbox\InboxController;
use App\Core\CsvExport;
use App\Core\Database;
use App\Core\Flash;
use App\Core\Mailer;
use App\Core\Request;
use App\Core\Response;
use App\Models\NewsletterDelivery;
use App\Models\Subscriber;
use App\Services\Inbox\Support;
use App\Services\Inbox\Where;

/**
 * Admin > Subscribers (newsletter list). OWNER: B5 "admin-inbox".
 * People are never re-subscribed silently: a person who unsubscribed stays unsubscribed unless an admin explicitly resubscribes them.
 */
final class SubscriberController extends InboxController
{
    private const FROM = 'subscribers s';
    private const STATUSES = ['subscribed', 'unsubscribed'];
    private const LABELS = ['subscribed' => 'Subscribed', 'unsubscribed' => 'Unsubscribed'];
    private const SORTS = [
        'newest' => 's.subscribed_at DESC, s.id DESC',
        'oldest' => 's.subscribed_at ASC, s.id ASC',
        'email'  => 's.email ASC',
    ];
    private const ADD_LIMIT = 500;

    public function index(Request $request): Response
    {
        $this->authorize('inbox.manage');
        [$where, $f] = $this->filters();
        $page = $this->paged(self::FROM, 's.*', $where, self::SORTS[$f['sort']]);

        return $this->render('admin/subscribers/index', [
            'pagination' => $page,
            'f'          => $f,
            'tabs'       => $this->tabs($this->statusCounts('subscribers', self::STATUSES), self::STATUSES, self::LABELS),
            'canDelete'  => can('inbox.delete'),
            'canExport'  => can('export.csv'),
        ], ['title' => 'Subscribers', 'nav' => 'subscribers', 'breadcrumbs' => [['Subscribers', null]]]);
    }

    /** Add one or several people (one per line: "email" or "Name <email>" or "email, Name"). */
    public function store(Request $request): Response
    {
        $this->authorize('inbox.manage');
        $this->validate([
            'emails'  => 'required|max:60000',
            'consent' => 'required|accepted',
        ], [
            'emails.required'  => 'Enter at least one email address.',
            'consent.required' => 'Please confirm that these people agreed to receive the newsletter.',
            'consent.accepted' => 'Please confirm that these people agreed to receive the newsletter.',
        ], ['emails' => 'Email addresses', 'consent' => 'Consent']);

        $added = 0;
        $already = 0;
        $reopened = 0;
        $invalid = [];
        $seen = [];
        $tokens = preg_split('/[\r\n;]+/', (string) $request->string('emails')) ?: [];
        foreach ($tokens as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            if (count($seen) >= self::ADD_LIMIT) {
                Flash::warning('Only the first ' . self::ADD_LIMIT . ' addresses were processed. Add the rest in a second batch.');
                break;
            }
            [$email, $name] = $this->parseLine($line);
            if ($email === '' || !Mailer::validEmail($email) || mb_strlen($email) > 190) {
                $invalid[] = mb_substr($line, 0, 60);
                continue;
            }
            $key = strtolower($email);
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $existing = Subscriber::findBy('email', $email);
            if ($existing !== null) {
                $existing['status'] === 'unsubscribed' ? $reopened++ : $already++;
                continue;
            }
            try {
                Subscriber::create([
                    'email'         => $email,
                    'name'          => $name !== '' ? mb_substr($name, 0, 150) : null,
                    'status'        => 'subscribed',
                    'token'         => bin2hex(random_bytes(20)),
                    'source'        => 'admin',
                    'subscribed_at' => date('Y-m-d H:i:s'),
                ]);
                $added++;
            } catch (\PDOException $e) {
                if ((string) $e->getCode() !== '23000') {
                    throw $e;
                }
                $already++;                          // someone added the same address a moment ago
            }
        }

        $this->audit('subscriber.create', 'subscriber', null, 'Added ' . $this->items($added, 'subscriber'), ['added' => $added, 'skipped' => $already, 'unsubscribed' => $reopened, 'invalid' => count($invalid)]);
        $bits = [];
        if ($already > 0) {
            $bits[] = $already . ' already on the list';
        }
        if ($reopened > 0) {
            $bits[] = $reopened . ' previously unsubscribed (not re-added: use "Resubscribe" if they asked to rejoin)';
        }
        if ($invalid !== []) {
            $bits[] = count($invalid) . ' not valid (' . implode(', ', array_slice($invalid, 0, 3)) . (count($invalid) > 3 ? ', ...' : '') . ')';
        }
        $msg = $added > 0 ? 'Added ' . $this->items($added, 'subscriber') . '.' : 'No new subscribers were added.';
        if ($bits !== []) {
            $msg .= ' Skipped: ' . implode('; ', $bits) . '.';
        }
        $added > 0 ? Flash::success($msg) : Flash::warning($msg);
        return $this->redirect('admin/subscribers');
    }

    public function unsubscribe(Request $request, int $id): Response
    {
        $this->authorize('inbox.manage');
        $s = Subscriber::findOrFail($id);
        if ($s['status'] !== 'unsubscribed') {
            Subscriber::update($id, ['status' => 'unsubscribed', 'unsubscribed_at' => date('Y-m-d H:i:s')]);
            $this->audit('subscriber.unsubscribe', 'subscriber', $id, 'Unsubscribed ' . $s['email']);
        }
        Flash::success($s['email'] . ' was unsubscribed.');
        return $this->back('admin/subscribers');
    }

    public function resubscribe(Request $request, int $id): Response
    {
        $this->authorize('inbox.manage');
        $s = Subscriber::findOrFail($id);
        if ($s['status'] !== 'subscribed') {
            Subscriber::update($id, ['status' => 'subscribed', 'subscribed_at' => date('Y-m-d H:i:s'), 'unsubscribed_at' => null]);
            $this->audit('subscriber.resubscribe', 'subscriber', $id, 'Resubscribed ' . $s['email']);
        }
        Flash::success($s['email'] . ' was resubscribed.');
        return $this->back('admin/subscribers');
    }

    public function destroy(Request $request, int $id): Response
    {
        $this->authorize('inbox.manage', 'inbox.delete');
        $s = Subscriber::findOrFail($id);
        Database::transaction(static function () use ($id, $s): void {
            NewsletterDelivery::erase([$id], [(string) $s['email']]);       // the address must not stay in any e-mail campaign record
            Subscriber::delete($id);
        });
        $this->audit('subscriber.delete', 'subscriber', $id, 'Deleted a subscriber (the address is not kept in the activity log)');
        Flash::success($s['email'] . ' was deleted.');
        return $this->back('admin/subscribers');
    }

    /** Bulk: unsubscribe | resubscribe | delete (delete needs inbox.delete). */
    public function bulk(Request $request): Response
    {
        $this->authorize('inbox.manage');
        $in = $this->bulkInput(['unsubscribe', 'resubscribe', 'delete'], 'admin/subscribers');
        if ($in instanceof Response) {
            return $in;
        }
        [$ids, $action] = $in;
        $marks = $this->marks($ids);
        $now = date('Y-m-d H:i:s');

        if ($action === 'delete') {
            $this->authorize('inbox.delete');
            $emails = array_column(Database::all("SELECT email FROM subscribers WHERE id IN ({$marks})", $ids), 'email');
            $n = Database::transaction(static function () use ($ids, $emails, $marks): int {
                NewsletterDelivery::erase($ids, $emails);
                return Database::execute("DELETE FROM subscribers WHERE id IN ({$marks})", $ids);
            });
            $this->audit('subscriber.bulk_delete', 'subscriber', null, 'Deleted ' . $this->items($n, 'subscriber'), ['ids' => $ids]);
            Flash::success($this->items($n, 'subscriber') . ' deleted.');
        } elseif ($action === 'unsubscribe') {
            $n = Database::execute("UPDATE subscribers SET status = 'unsubscribed', unsubscribed_at = ? WHERE status = 'subscribed' AND id IN ({$marks})", array_merge([$now], $ids));
            $this->audit('subscriber.bulk_unsubscribe', 'subscriber', null, 'Unsubscribed ' . $this->items($n, 'subscriber'), ['ids' => $ids]);
            Flash::success($this->items($n, 'subscriber') . ' unsubscribed.');
        } else {
            $n = Database::execute("UPDATE subscribers SET status = 'subscribed', subscribed_at = ?, unsubscribed_at = NULL WHERE status = 'unsubscribed' AND id IN ({$marks})", array_merge([$now], $ids));
            $this->audit('subscriber.bulk_resubscribe', 'subscriber', null, 'Resubscribed ' . $this->items($n, 'subscriber'), ['ids' => $ids]);
            Flash::success($this->items($n, 'subscriber') . ' resubscribed.');
        }
        return $this->back('admin/subscribers');
    }

    /** CSV of the subscribers: only ACTIVE ones by default (?status=all or ?status=unsubscribed for the others). */
    public function export(Request $request): Response
    {
        $this->authorize('inbox.manage', 'export.csv');
        $status = $this->request->string('status', 'subscribed');
        $status = in_array($status, ['all', 'unsubscribed', 'subscribed'], true) ? $status : 'subscribed';
        $w = new Where();
        if ($status !== 'all') {
            $w->add('s.status = ?', [$status]);
        }
        $q = Support::like($this->request->string('q'));
        $w->addLike(['s.email', 's.name'], $q);
        $rows = $this->exportRows(self::FROM, 's.*', $w, 's.email ASC');
        $this->audit('subscriber.export', 'subscriber', null, 'Exported ' . $this->items(count($rows), 'subscriber') . ' to CSV (' . $status . ')');
        $out = [];
        foreach ($rows as $r) {
            $out[] = [$r['email'], $r['name'], $r['status'], $r['source'], $r['subscribed_at'], $r['unsubscribed_at']];
        }
        return CsvExport::response('subscribers-' . ($status === 'subscribed' ? 'active' : $status) . '-' . date('Y-m-d') . '.csv',
            ['Email', 'Name', 'Status', 'Source', 'Subscribed at', 'Unsubscribed at'], $out);
    }

    /** @return array{0:string,1:string} [email, name] */
    private function parseLine(string $line): array
    {
        if (preg_match('/^(.*?)<\s*([^<>\s]+)\s*>\s*$/', $line, $m) === 1) {
            return [trim($m[2]), trim($m[1], " \t\"'")];
        }
        if (str_contains($line, ',')) {
            [$a, $b] = array_map('trim', explode(',', $line, 2));
            if (str_contains($a, '@')) {
                return [$a, $b];
            }
            if (str_contains($b, '@')) {
                return [$b, $a];
            }
        }
        return [trim($line), ''];
    }

    /** @return array{0:Where,1:array{q:string,status:string,sort:string}} */
    private function filters(): array
    {
        $q = $this->request->string('q');
        $status = $this->request->string('status', 'all');
        $status = in_array($status, self::STATUSES, true) ? $status : 'all';
        $sort = $this->request->string('sort', 'newest');
        $sort = isset(self::SORTS[$sort]) ? $sort : 'newest';
        $w = new Where();
        $w->addLike(['s.email', 's.name', 's.source'], Support::like($q));
        if ($status !== 'all') {
            $w->add('s.status = ?', [$status]);
        }
        return [$w, ['q' => $q, 'status' => $status, 'sort' => $sort]];
    }
}
