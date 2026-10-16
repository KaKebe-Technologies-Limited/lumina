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
use App\Models\ContactMessage;
use App\Services\Inbox\Support;
use App\Services\Inbox\Where;

/**
 * Admin > Messages (contact form submissions). OWNER: B5 "admin-inbox".
 * Workflow: new -> read -> replied -> archived. Opening a message marks it read. Replies can be sent from here (Mailer, logged in the audit
 * trail so the thread history is shown on the message) or with the visitor's own mail program (mailto: link).
 */
final class ContactMessageController extends InboxController
{
    private const FROM = 'contact_messages m';
    private const SORTS = ['newest' => 'm.created_at DESC, m.id DESC', 'oldest' => 'm.created_at ASC, m.id ASC'];

    /** status => label (as used in tabs, selects and bulk actions) */
    private const LABELS = ['new' => 'New', 'read' => 'Read', 'replied' => 'Replied', 'archived' => 'Archived'];

    public function index(Request $request): Response
    {
        $this->authorize('inbox.manage');
        [$where, $f] = $this->filters();
        $page = $this->paged(self::FROM, 'm.*', $where, self::SORTS[$f['sort']]);

        return $this->render('admin/messages/index', [
            'pagination' => $page,
            'f'          => $f,
            'tabs'       => $this->tabs($this->statusCounts('contact_messages', ContactMessage::STATUSES), ContactMessage::STATUSES, self::LABELS),
            'labels'     => self::LABELS,
            'canDelete'  => can('inbox.delete'),
            'canExport'  => can('export.csv'),
        ], ['title' => 'Messages', 'nav' => 'messages', 'breadcrumbs' => [['Messages', null]]]);
    }

    public function show(Request $request, int $id): Response
    {
        $this->authorize('inbox.manage');
        $m = ContactMessage::findOrFail($id);

        // Opening a new message marks it as read (the owner asked for this; it never touches replied/archived messages).
        if ($m['status'] === 'new' && $request->method() === 'GET') {
            ContactMessage::update($id, ['status' => 'read']);
            $m['status'] = 'read';
        }

        $replies = [];
        $rows = Database::all(
            "SELECT created_at, user_name, details FROM audit_log WHERE action = 'message.reply' AND entity_type = 'message' AND entity_id = ? ORDER BY id DESC LIMIT 25",
            [(string) $id]
        );
        foreach ($rows as $r) {
            $d = json_decode((string) ($r['details'] ?? ''), true);
            $replies[] = [
                'at'      => $r['created_at'],
                'by'      => $r['user_name'],
                'to'      => is_array($d) ? (string) ($d['to'] ?? '') : '',
                'subject' => is_array($d) ? (string) ($d['subject'] ?? '') : '',
                'body'    => is_array($d) ? (string) ($d['body'] ?? '') : '',
            ];
        }

        $siblings = [
            'prev' => Database::value('SELECT id FROM contact_messages WHERE id < ? ORDER BY id DESC LIMIT 1', [$id]),
            'next' => Database::value('SELECT id FROM contact_messages WHERE id > ? ORDER BY id ASC LIMIT 1', [$id]),
        ];

        $subject = trim((string) ($m['subject'] ?? ''));
        return $this->render('admin/messages/show', [
            'm'              => $m,
            'replies'        => $replies,
            'siblings'       => $siblings,
            'statuses'       => self::LABELS,
            'replySubject'   => old('reply_subject', $subject !== '' ? (preg_match('/^re:/i', $subject) ? $subject : 'Re: ' . $subject) : 'Re: your message'),
            'canDelete'      => can('inbox.delete'),
        ], ['title' => 'Message from ' . (string) $m['name'], 'nav' => 'messages', 'breadcrumbs' => [['Messages', url('admin/messages')], [(string) $m['name'], null]]]);
    }

    /** Save status + private notes. */
    public function update(Request $request, int $id): Response
    {
        $this->authorize('inbox.manage');
        $m = ContactMessage::findOrFail($id);
        $data = $this->validate([
            'status'      => 'required|in:' . implode(',', ContactMessage::STATUSES),
            'admin_notes' => 'nullable|max:5000',
        ], [], ['status' => 'Status', 'admin_notes' => 'Notes']);

        $fields = ['status' => $data['status'], 'admin_notes' => ($data['admin_notes'] ?? '') !== '' ? $data['admin_notes'] : null];
        if ($data['status'] === 'replied' && empty($m['replied_at'])) {
            $fields['replied_at'] = date('Y-m-d H:i:s');
        }
        ContactMessage::update($id, $fields);
        $this->audit('message.update', 'message', $id, 'Updated message from "' . $m['name'] . '" (' . $data['status'] . ')', ['status' => $data['status']]);
        Flash::success('Message updated.');
        return $this->redirect('admin/messages/' . $id);
    }

    /** Send a reply by email from the admin (logged in the audit trail; marks the message replied). */
    public function reply(Request $request, int $id): Response
    {
        $this->authorize('inbox.manage');
        $m = ContactMessage::findOrFail($id);
        $to = trim((string) $m['email']);
        $back = 'admin/messages/' . $id;
        if (!Mailer::validEmail($to)) {
            $this->fail(['reply_body' => 'This message has no valid email address to reply to.'], $back);
        }
        $data = $this->validate([
            'reply_subject' => 'required|max:200',
            'reply_body'    => 'required|min:3|max:10000',
        ], [], ['reply_subject' => 'Subject', 'reply_body' => 'Reply'], $back);

        $siteName = (string) setting('site_name', '');
        $signature = array_values(array_filter([
            (string) (current_user()['name'] ?? ''),
            $siteName,
            (string) setting('email_primary', ''),
            (string) setting('phone_1', ''),
        ], static fn(string $s): bool => $s !== ''));
        $quoted = '> ' . str_replace(["\r\n", "\r", "\n"], "\n> ", trim((string) $m['message']));
        $text = $data['reply_body']
            . ($signature ? "\n\n-- \n" . implode("\n", $signature) : '')
            . "\n\n---- Your original message (" . format_datetime($m['created_at'], 'j F Y, H:i') . ") ----\n" . $quoted . "\n";

        $replyTo = trim((string) setting('email_primary', ''));
        if (!Mailer::validEmail($replyTo)) {
            $replyTo = (string) (current_user()['email'] ?? '');
        }
        $opts = Mailer::validEmail($replyTo) ? ['reply_to' => $replyTo] : [];
        if (!Mailer::send($to, $data['reply_subject'], $text, $opts)) {
            $this->fail(['reply_body' => 'The email could not be sent. Please try again, or use "Reply in my email program".'], $back);
        }

        ContactMessage::update($id, ['status' => 'replied', 'replied_at' => date('Y-m-d H:i:s')]);
        $this->audit('message.reply', 'message', $id, 'Replied to "' . $m['name'] . '"', [
            'to'      => $to,
            'subject' => $data['reply_subject'],
            'body'    => mb_substr($data['reply_body'], 0, 3000),
        ]);
        Flash::success('Reply sent to ' . $to . ' and the message was marked as replied.');
        return $this->redirect($back);
    }

    public function destroy(Request $request, int $id): Response
    {
        $this->authorize('inbox.manage', 'inbox.delete');
        $m = ContactMessage::findOrFail($id);
        ContactMessage::delete($id);
        $this->audit('message.delete', 'message', $id, 'Deleted message from "' . $m['name'] . '"');
        Flash::success('Message deleted.');
        return $this->redirect('admin/messages');
    }

    /** Bulk actions on ticked rows: mark_read | mark_new | mark_replied | archive | delete (delete needs inbox.delete). */
    public function bulk(Request $request): Response
    {
        $this->authorize('inbox.manage');
        $in = $this->bulkInput(['mark_read', 'mark_new', 'mark_replied', 'archive', 'delete'], 'admin/messages');
        if ($in instanceof Response) {
            return $in;
        }
        [$ids, $action] = $in;
        $marks = $this->marks($ids);

        if ($action === 'delete') {
            $this->authorize('inbox.delete');
            $n = Database::execute("DELETE FROM contact_messages WHERE id IN ({$marks})", $ids);
            $this->audit('message.bulk_delete', 'message', null, 'Deleted ' . $this->items($n, 'message'), ['ids' => $ids]);
            Flash::success($this->items($n, 'message') . ' deleted.');
            return $this->back('admin/messages');
        }

        $status = ['mark_read' => 'read', 'mark_new' => 'new', 'mark_replied' => 'replied', 'archive' => 'archived'][$action];
        $extra = $status === 'replied' ? ', replied_at = COALESCE(replied_at, ?)' : '';
        $params = array_merge([$status], $status === 'replied' ? [date('Y-m-d H:i:s')] : [], $ids);
        $n = Database::execute("UPDATE contact_messages SET status = ?{$extra} WHERE id IN ({$marks})", $params);
        $this->audit('message.bulk_status', 'message', null, 'Marked ' . $this->items($n, 'message') . ' as ' . $status, ['ids' => $ids, 'status' => $status]);
        Flash::success($this->items($n, 'message') . ' marked as ' . $status . '.');
        return $this->back('admin/messages');
    }

    public function export(Request $request): Response
    {
        $this->authorize('inbox.manage', 'export.csv');
        [$where] = $this->filters();
        $rows = $this->exportRows(self::FROM, 'm.*', $where, 'm.created_at DESC, m.id DESC');
        $this->audit('message.export', 'message', null, 'Exported ' . $this->items(count($rows), 'message') . ' to CSV');
        $out = [];
        foreach ($rows as $r) {
            $out[] = [$r['id'], $r['created_at'], $r['name'], $r['email'], $r['phone'], $r['subject'], $r['message'], $r['status'], $r['replied_at'], $r['admin_notes']];
        }
        return CsvExport::response('messages-' . date('Y-m-d') . '.csv',
            ['ID', 'Received', 'Name', 'Email', 'Phone', 'Subject', 'Message', 'Status', 'Replied at', 'Notes'], $out);
    }

    /**
     * Read the list filters from the query string.
     * @return array{0:Where,1:array{q:string,status:string,from:string,to:string,sort:string}}
     */
    private function filters(): array
    {
        $q = $this->request->string('q');
        $status = $this->request->string('status', 'all');
        $status = in_array($status, ContactMessage::STATUSES, true) ? $status : 'all';
        $from = Support::date($this->request->string('from')) ?? '';
        $to = Support::date($this->request->string('to')) ?? '';
        $sort = $this->request->string('sort', 'newest');
        $sort = isset(self::SORTS[$sort]) ? $sort : 'newest';

        $w = new Where();
        $w->addLike(['m.name', 'm.email', 'm.phone', 'm.subject', 'm.message'], Support::like($q));
        if ($status !== 'all') {
            $w->add('m.status = ?', [$status]);
        }
        if ($from !== '') {
            $w->add('m.created_at >= ?', [$from . ' 00:00:00']);
        }
        if ($to !== '') {
            $w->add('m.created_at <= ?', [$to . ' 23:59:59']);
        }
        return [$w, ['q' => $q, 'status' => $status, 'from' => $from, 'to' => $to, 'sort' => $sort]];
    }
}
