<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\AdminController;
use App\Core\Flash;
use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Services\Letters\LetterService;

/**
 * Admin > Official letters (ability letters.manage, administrators). Write a letter, preview it as a PDF, then e-mail the PDF to one address or many (one e-mail each, or one e-mail to the group).
 * Routes: admin/letters (list), /new, /{id}/edit, /{id}/preview.pdf, /{id}/duplicate, /{id}/delete, /{id}/send (+ /review, /go), admin/letters/contacts (+ add, import, delete).
 */
final class LettersController extends AdminController
{
    private function crumbs(string $label): array
    {
        return [['Official letters', url('admin/letters')], [$label, null]];
    }

    private function gate(): void
    {
        $this->authorize('letters.manage');
    }

    private function letter(int $id): array
    {
        return LetterService::find($id) ?? throw new HttpException(404, 'Not found');
    }

    public function index(Request $request): Response
    {
        $this->gate();
        $ready = LetterService::install();
        return $this->render('admin/letters/index', ['ready' => $ready, 'letters' => $ready ? LetterService::all() : []], ['title' => 'Official letters', 'nav' => 'letters', 'breadcrumbs' => [['Official letters', null]]]);
    }

    public function create(Request $request): Response
    {
        $this->gate();
        if (!LetterService::install()) {
            return $this->redirect('admin/letters');
        }
        return $this->form(LetterService::blank(), 'New letter');
    }

    public function edit(Request $request, int|string $id): Response
    {
        $this->gate();
        $l = $this->letter((int) $id);
        return $this->form($l, 'Edit letter');
    }

    private function form(array $letter, string $title): Response
    {
        return $this->render('admin/letters/form', ['l' => $letter, 'signatories' => LetterService::signatories(), 'sigModes' => LetterService::SIG_MODES, 'errors' => $this->formErrors ?? []],
            ['title' => $title, 'nav' => 'letters', 'breadcrumbs' => $this->crumbs($title)]);
    }

    /** @var array<string,string> */
    private array $formErrors = [];

    /** Save (new or existing). "Save and preview" (preview=1) shows the PDF next; plain Save returns to the list. */
    public function save(Request $request, int|string $id = 0): Response
    {
        $this->gate();
        LetterService::install();
        $id = (int) $id;
        $existing = $id > 0 ? $this->letter($id) : null;
        $in = $request->all();
        $res = LetterService::validate($in);
        if ($res['errors'] !== []) {
            $this->formErrors = $res['errors'];
            $letter = array_merge($existing ?? LetterService::blank(), $in, ['id' => $id]);
            Flash::error('Please fix the marked fields: the letter was not saved.');
            return $this->form($letter, $existing ? 'Edit letter' : 'New letter');
        }
        $data = $res['data'];
        $data['auto_ref'] = !empty($in['auto_ref']);
        $newId = LetterService::save($existing ? $id : null, $data, $this->userId());
        $this->audit($existing ? 'letter.update' : 'letter.create', 'letter', $newId, 'Official letter saved: ' . $data['subject']);
        Flash::success('The letter was saved.');
        return $this->redirect($request->string('preview') === '1' ? 'admin/letters/' . $newId . '/preview' : 'admin/letters');
    }

    /** The preview screen: the letter as a PDF in a frame, with Edit and Send. */
    public function preview(Request $request, int|string $id): Response
    {
        $this->gate();
        $l = $this->letter((int) $id);
        return $this->render('admin/letters/preview', ['l' => $l, 'sends' => LetterService::sends((int) $id)], ['title' => 'Preview of the letter', 'nav' => 'letters', 'breadcrumbs' => $this->crumbs('Preview')]);
    }

    public function pdf(Request $request, int|string $id): Response
    {
        $this->gate();
        $l = $this->letter((int) $id);
        $pdf = LetterService::pdf($l);
        $disp = $request->string('download') === '1' ? 'attachment' : 'inline';
        return new Response($pdf, 200, [
            'Content-Type' => 'application/pdf', 'Content-Length' => (string) strlen($pdf), 'Content-Disposition' => $disp . '; filename="' . LetterService::pdfName($l) . '"',
            'X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'private, no-store', 'X-Frame-Options' => 'SAMEORIGIN',
        ]);
    }

    public function duplicate(Request $request, int|string $id): Response
    {
        $this->gate();
        $this->letter((int) $id);
        $new = LetterService::duplicate((int) $id, $this->userId());
        Flash::success('A copy was made. Change what you need and save.');
        return $this->redirect('admin/letters/' . $new . '/edit');
    }

    public function delete(Request $request, int|string $id): Response
    {
        $this->gate();
        $l = $this->letter((int) $id);
        LetterService::delete((int) $id);
        $this->audit('letter.delete', 'letter', (int) $id, 'Official letter deleted: ' . (string) $l['subject']);
        Flash::success('The letter was deleted.');
        return $this->redirect('admin/letters');
    }

    // ------------------------------------------------------------------------------------------------------------- sending

    public function sendForm(Request $request, int|string $id): Response
    {
        $this->gate();
        $l = $this->letter((int) $id);
        return $this->render('admin/letters/send', [
            'l' => $l, 'contacts' => LetterService::contacts(), 'old' => [], 'defaultMessage' => LetterService::defaultMessage($l),
            'groups' => array_values(array_unique(array_filter(array_map(static fn(array $c): string => (string) ($c['grp'] ?? ''), LetterService::contacts())))),
            'adminEmail' => (string) ($this->user()['email'] ?? ''),
        ], ['title' => 'Send the letter', 'nav' => 'letters', 'breadcrumbs' => $this->crumbs('Send')]);
    }

    /** Step 2: show exactly who gets what, and ask to confirm. Nothing is sent here. */
    public function review(Request $request, int|string $id): Response
    {
        $this->gate();
        $l = $this->letter((int) $id);
        $set = $this->readSend($request, $l);
        if ($set['rows'] === []) {
            Flash::error('Add at least one valid e-mail address (type them, or tick contacts).');
            return $this->redirect('admin/letters/' . (int) $id . '/send');
        }
        $done = LetterService::alreadySent((int) $id);
        $new = array_values(array_filter($set['rows'], static fn(array $r): bool => empty($done[strtolower($r['email'])])));
        return $this->render('admin/letters/confirm', ['l' => $l, 'set' => $set, 'newCount' => $set['again'] ? count($set['rows']) : count($new), 'already' => count($set['rows']) - count($new), 'token' => base64_encode(json_encode($set['payload']) ?: '')],
            ['title' => 'Check before sending', 'nav' => 'letters', 'breadcrumbs' => $this->crumbs('Check before sending')]);
    }

    /** Step 3: send. */
    public function go(Request $request, int|string $id): Response
    {
        $this->gate();
        $l = $this->letter((int) $id);
        $p = json_decode((string) base64_decode($request->string('token'), true), true);
        if (!is_array($p) || empty($p['rows'])) {
            Flash::error('The list was lost. Please start the sending again.');
            return $this->redirect('admin/letters/' . (int) $id . '/send');
        }
        $rows = [];
        foreach ((array) $p['rows'] as $r) {
            if (is_array($r) && !empty($r['email'])) {
                $rows[] = ['name' => (string) ($r['name'] ?? ''), 'email' => (string) $r['email']];
            }
        }
        @set_time_limit(240);
        $res = LetterService::send($l, $rows, [
            'mode' => $p['mode'] ?? 'each', 'hide' => !empty($p['hide']), 'personalise' => !empty($p['personalise']), 'subject' => (string) ($p['subject'] ?? ''), 'message' => (string) ($p['message'] ?? ''),
            'reply_to' => (string) ($p['reply_to'] ?? ''), 'again' => !empty($p['again']), 'cap' => 100,
        ], $this->userId());
        $this->audit('letter.send', 'letter', (int) $id, sprintf('Official letter e-mailed: %d sent, %d failed', $res['sent'], $res['failed']), ['mode' => $p['mode'] ?? 'each']);
        if ($res['sent'] > 0) {
            Flash::success($res['sent'] . ($res['sent'] === 1 ? ' e-mail was sent' : ' e-mails were sent') . ' with the letter attached.' . ($res['skipped'] > 0 ? ' ' . $res['skipped'] . ' address(es) already had this letter and were skipped.' : ''));
        }
        if ($res['failed'] > 0) {
            Flash::error($res['failed'] . ' could not be sent' . ($res['stopped'] ? ': ' . $res['stopped'] : '.') . ' See the list below.');
        }
        if ($res['remaining'] > 0) {
            Flash::warning($res['remaining'] . ' address(es) are still waiting (at most 100 e-mails are sent at once). Press Send again: the ones already sent are skipped.');
        }
        return $this->redirect('admin/letters/' . (int) $id . '/preview');
    }

    /** @return array{rows:list<array{name:string,email:string}>,bad:list<string>,over:bool,mode:string,hide:bool,personalise:bool,again:bool,payload:array<string,mixed>} */
    private function readSend(Request $request, array $l): array
    {
        $ids = array_map('intval', (array) ($request->post('contacts') ?? []));
        $group = trim((string) $request->post('group', ''));
        if ($group !== '') {
            foreach (LetterService::contacts() as $c) {
                if ((string) ($c['grp'] ?? '') === $group) {
                    $ids[] = (int) $c['id'];
                }
            }
        }
        $r = LetterService::recipients((string) $request->post('typed', ''), $ids);
        $mode = $request->string('mode') === 'one' ? 'one' : 'each';
        $set = $r + [
            'mode' => $mode, 'hide' => $mode === 'one' && $request->string('hide') === '1', 'personalise' => $mode === 'each' && $request->string('personalise') === '1', 'again' => $request->string('again') === '1',
        ];
        $set['subject'] = mb_substr(trim($request->string('subject')) ?: 'Official letter: ' . (string) $l['subject'], 0, 300);
        $set['message'] = mb_substr(trim($request->string('message')) ?: LetterService::defaultMessage($l), 0, 4000);
        $set['reply_to'] = trim($request->string('reply_to'));
        $set['payload'] = ['rows' => $r['rows'], 'mode' => $mode, 'hide' => $set['hide'], 'personalise' => $set['personalise'], 'again' => $set['again'], 'subject' => $set['subject'], 'message' => $set['message'], 'reply_to' => $set['reply_to']];
        return $set;
    }

    // ------------------------------------------------------------------------------------------------------------- contacts

    public function contacts(Request $request): Response
    {
        $this->gate();
        $ready = LetterService::install();
        return $this->render('admin/letters/contacts', ['ready' => $ready, 'contacts' => $ready ? LetterService::contacts() : []], ['title' => 'Letter contacts', 'nav' => 'letters', 'breadcrumbs' => $this->crumbs('Contacts')]);
    }

    public function addContact(Request $request): Response
    {
        $this->gate();
        LetterService::install();
        $res = LetterService::addContact($request->string('name'), $request->string('email'), $request->string('organisation'), $request->string('phone'), $request->string('grp'));
        $res['ok'] ? Flash::success('The contact was saved.') : Flash::error($res['error']);
        return $this->redirect('admin/letters/contacts');
    }

    public function importContacts(Request $request): Response
    {
        $this->gate();
        LetterService::install();
        $p = LetterService::parseList($request->string('list'));
        $grp = $request->string('grp');
        $n = 0;
        foreach ($p['rows'] as $r) {
            if (LetterService::addContact($r['name'], $r['email'], $r['org'], $r['phone'], $grp)['ok']) {
                $n++;
            }
        }
        Flash::success($n . ' contact(s) saved.');
        if ($p['bad'] !== []) {
            Flash::warning('Not understood, so skipped: ' . implode('; ', array_slice($p['bad'], 0, 10)));
        }
        return $this->redirect('admin/letters/contacts');
    }

    public function deleteContact(Request $request, int|string $id): Response
    {
        $this->gate();
        LetterService::deleteContact((int) $id);
        Flash::success('The contact was removed.');
        return $this->redirect('admin/letters/contacts');
    }
}
