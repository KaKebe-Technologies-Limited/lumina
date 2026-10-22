<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Core\Flash;
use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Core\Uploader;
use App\Models\Document;
use App\Models\DocumentSignatory;
use App\Models\DocumentSignature;
use App\Services\Documents\DocumentSigning;

/**
 * Admin > Documents > Signatories and Signatures (needs the ability documents.sign: administrators). The people responsible for the policies get a SIGNATORY record with
 * their signature picture (kept privately); an administrator attaches them to a document with a role and applies the signature; every change rebuilds the signed PDF (the original
 * file is never changed). Also here: the one-click public / private switch of a document, and the import of the policy pack. Docs: docs/DOCUMENTS-SIGNATURES.md.
 */
final class DocumentSignaturesController extends OrgAdminController
{
    protected function area(): string
    {
        return 'admin/documents';
    }

    private function gate(): void
    {
        $this->authorize('documents.sign');
        if (!DocumentSigning::ready()) {
            throw new HttpException(404, 'Not found');
        }
    }

    private function notReady(): ?Response
    {
        if (DocumentSigning::ready()) {
            return null;
        }
        Flash::warning('The database update has not been imported yet. In phpMyAdmin, import lumina-live-import.sql into your database (it is safe to run again), then open this page again.');
        return $this->redirect('admin/documents');
    }

    // ------------------------------------------------------------------------------------------------------------- signatories

    public function signatories(Request $request): Response
    {
        $this->authorize('documents.sign');
        if ($r = $this->notReady()) {
            return $r;
        }
        if (DocumentSigning::roles() === []) {
            // first visit: read the signature pages of the documents by themselves
            @set_time_limit(120);
            DocumentSigning::detectAll();
            DocumentSigning::autoAssign();
        }
        $rows = Database::all(
            'SELECT g.*, (SELECT COUNT(*) FROM document_signatures s WHERE s.signatory_id = g.id) AS attached, (SELECT COUNT(*) FROM document_signatures s2 WHERE s2.signatory_id = g.id AND s2.signed_at IS NULL) AS waiting
             FROM document_signatories g ORDER BY g.is_active DESC, g.name ASC'
        );
        $docs = Database::all("SELECT id, title, category, is_public FROM documents WHERE file_path LIKE '%.pdf' ORDER BY (category = 'policy') DESC, title ASC LIMIT 300");
        $attached = [];
        foreach (Database::all('SELECT s.signatory_id, s.document_id, s.role_label, s.signed_at, d.title FROM document_signatures s JOIN documents d ON d.id = s.document_id ORDER BY d.title ASC') as $a) {
            $attached[(int) $a['signatory_id']][] = $a;
        }
        return $this->render('admin/documents/signatories', ['rows' => $rows, 'docs' => $docs, 'attached' => $attached, 'roles' => DocumentSignature::ROLES,
            'found' => DocumentSigning::roles(), 'people' => array_values(array_filter($rows, static fn(array $g): bool => (int) $g['is_active'] === 1))],
            ['title' => 'Signatories', 'nav' => 'documents', 'breadcrumbs' => [['Documents', url('admin/documents')], ['Signatories', null]]]);
    }

    public function storeSignatory(Request $request): Response
    {
        $this->gate();
        $back = 'admin/documents/signatories';
        $data = $this->validate(['name' => 'required|max:150', 'title' => 'nullable|max:190'], [], ['name' => 'Name', 'title' => 'Title'], $back);
        $pic = $this->picture('signature', $back);
        $id = DocumentSignatory::create([
            'name' => $this->clean((string) $data['name']), 'title' => $this->clean((string) ($data['title'] ?? '')) ?: null, 'signature_path' => $pic['path'] ?: null,
            'signature_mime' => $pic['mime'] ?: null, 'signature_updated_at' => $pic['path'] !== '' ? date('Y-m-d H:i:s') : null, 'created_by' => $this->userId(),
        ]);
        $this->audit('document.signatory.create', 'document_signatory', $id, 'Added signatory "' . $data['name'] . '"');
        $n = count($this->attachFromRequest($request, $id));
        $auto = DocumentSigning::autoAssign();
        if ($auto > 0) {
            Flash::success('"' . $data['name'] . '" was matched by title to ' . $auto . ' signature block' . ($auto === 1 ? '' : 's') . ' in the documents. Press "Sign" under Roles found in the documents when you are ready.');
        }
        Flash::success('"' . $data['name'] . '" was added' . ($pic['path'] === '' ? '. Upload their signature picture to be able to sign documents.' : ' with their signature.')
            . ($n > 0 ? ' Attached to ' . $n . ' document' . ($n === 1 ? '' : 's') . ': apply the signature when you are ready.' : ''));
        return $this->redirect($back);
    }

    public function updateSignatory(Request $request, int $id): Response
    {
        $this->gate();
        $g = DocumentSignatory::findOrFail($id);
        $back = 'admin/documents/signatories';
        $data = $this->validate(['name' => 'required|max:150', 'title' => 'nullable|max:190'], [], ['name' => 'Name', 'title' => 'Title'], $back);
        $upd = ['name' => $this->clean((string) $data['name']), 'title' => $this->clean((string) ($data['title'] ?? '')) ?: null];
        $pic = $this->picture('signature', $back);
        $oldPath = (string) ($g['signature_path'] ?? '');
        if ($pic['path'] !== '') {
            $upd += ['signature_path' => $pic['path'], 'signature_mime' => $pic['mime'], 'signature_updated_at' => date('Y-m-d H:i:s')];
        }
        DocumentSignatory::update($id, $upd);
        DocumentSigning::autoAssign();
        if ($pic['path'] !== '' && $oldPath !== '') {
            Uploader::deletePrivate($oldPath);
        }
        $this->audit('document.signatory.update', 'document_signatory', $id, 'Edited signatory "' . $upd['name'] . '"' . ($pic['path'] !== '' ? ' (new signature picture)' : ''));
        Flash::success('Saved.' . ($pic['path'] !== '' ? ' The new picture is used the next time a signed copy is built (use "Rebuild" on a document).' : ''));
        return $this->redirect($back);
    }

    public function toggleSignatory(Request $request, int $id): Response
    {
        $this->gate();
        $g = DocumentSignatory::findOrFail($id);
        DocumentSignatory::update($id, ['is_active' => (int) $g['is_active'] === 1 ? 0 : 1]);
        DocumentSigning::autoAssign();
        Flash::success('"' . $g['name'] . '" is now ' . ((int) $g['is_active'] === 1 ? 'inactive (hidden from the pick lists)' : 'active') . '.');
        return $this->redirect('admin/documents/signatories');
    }

    public function deleteSignatory(Request $request, int $id): Response
    {
        $this->gate();
        $g = DocumentSignatory::findOrFail($id);
        $docs = Database::all('SELECT DISTINCT document_id FROM document_signatures WHERE signatory_id = ?', [$id]);
        DocumentSigning::releasePerson($id);                              // their blocks wait again, other rows go, signed copies are rebuilt
        DocumentSignatory::delete($id);
        Uploader::deletePrivate((string) ($g['signature_path'] ?? ''));
        $this->audit('document.signatory.delete', 'document_signatory', $id, 'Deleted signatory "' . $g['name'] . '"');
        Flash::success('"' . $g['name'] . '" was deleted' . ($docs ? ' and removed from ' . count($docs) . ' document(s); their signed copies were rebuilt.' : '.'));
        return $this->redirect('admin/documents/signatories');
    }

    /** The signature picture, for staff (inline). */
    public function signatureImage(Request $request, int $id): Response
    {
        $this->gate();
        $g = DocumentSignatory::findOrFail($id);
        if (empty($g['signature_path'])) {
            throw new HttpException(404, 'Not found');
        }
        return Uploader::download((string) $g['signature_path'], 'signature-' . $id . '.' . ($g['signature_mime'] === 'image/png' ? 'png' : 'jpg'), true);
    }

    /** Attach a signatory (with a role) to the chosen documents; the ticked ones, or every policy when none is ticked. */
    public function attach(Request $request, int $id): Response
    {
        $this->gate();
        $g = DocumentSignatory::findOrFail($id);
        $done = $this->attachFromRequest($request, $id);
        if ($done === []) {
            Flash::warning('Tick at least one document (each with its role), or they were all attached already.');
            return $this->redirect('admin/documents/signatories');
        }
        Flash::success('"' . $g['name'] . '" was attached to ' . count($done) . ' document' . (count($done) === 1 ? '' : 's') . '. Apply the signatures when you are ready.');
        return $this->redirect('admin/documents/signatories');
    }

    /**
     * Attach a signatory to the documents ticked in the form (documents[] ids), each with its own role (doc_role[id]).
     * @return array<int,string> document id => role, for the ones newly attached
     */
    private function attachFromRequest(Request $request, int $signatoryId): array
    {
        $roles = (array) $request->input('doc_role', []);
        $done = [];
        foreach ((array) $request->input('documents', []) as $v) {
            if (!is_scalar($v) || !ctype_digit((string) $v) || isset($done[(int) $v]) || Document::find((int) $v) === null) {
                continue;
            }
            $docId = (int) $v;
            $role = $this->clean(is_scalar($roles[$docId] ?? null) ? (string) $roles[$docId] : '') ?: 'Approved by';
            $order = (int) Database::value('SELECT COALESCE(MAX(sort_order), 0) + 10 FROM document_signatures WHERE document_id = ?', [$docId], 10);
            if (Database::execute('INSERT IGNORE INTO document_signatures (document_id, signatory_id, role_label, sort_order) VALUES (?, ?, ?, ?)', [$docId, $signatoryId, mb_substr($role, 0, 80), $order]) > 0) {
                $done[$docId] = $role;
            }
        }
        if ($done !== []) {
            $this->audit('document.signature.attach', 'document_signatory', $signatoryId, 'Attached a signatory to ' . count($done) . ' document(s)', ['documents' => $done]);
        }
        return $done;
    }

    /** Apply this signatory's signature to every attached document where it is still awaited. */
    public function signAll(Request $request, int $id): Response
    {
        $this->gate();
        $g = DocumentSignatory::findOrFail($id);
        if (empty($g['signature_path'])) {
            Flash::warning('Upload the signature picture of ' . $g['name'] . ' first.');
            return $this->redirect('admin/documents/signatories');
        }
        @set_time_limit(180);
        $rows = Database::all('SELECT id FROM document_signatures WHERE signatory_id = ? AND signed_at IS NULL', [$id]);
        $n = 0;
        $failed = 0;
        foreach ($rows as $r) {
            $res = DocumentSigning::apply((int) $r['id'], $this->userId(), $request->ip());
            $res['ok'] ? $n++ : $failed++;
        }
        $this->audit('document.signature.sign_all', 'document_signatory', $id, 'Applied the signature of "' . $g['name'] . '" to ' . $n . ' document(s)');
        $n === 0 && $failed === 0 ? Flash::info('Nothing is waiting for this signature.') : Flash::success('The signature of ' . $g['name'] . ' was applied to ' . $n . ' document' . ($n === 1 ? '' : 's') . ($failed > 0 ? ', ' . $failed . ' could not be built' : '') . '.');
        return $this->redirect('admin/documents/signatories');
    }

    // ------------------------------------------------------------------------------------------------------------- reading who must sign

    /** POST /admin/documents/read-signers: read the signature page of every PDF document and match people to the roles found. */
    public function readSigners(Request $request): Response
    {
        $this->gate();
        @set_time_limit(180);
        $r = DocumentSigning::detectAll();
        $auto = DocumentSigning::autoAssign();
        $this->audit('document.signature.read_all', 'document', null, 'Read the signature pages: ' . $r['blocks'] . ' block(s) in ' . $r['documents'] . ' document(s), ' . $auto . ' matched');
        if ($r['blocks'] === 0) {
            Flash::info('Every document was already read. Nothing new was found.');
        } else {
            Flash::success('Found ' . $r['blocks'] . ' signature block' . ($r['blocks'] === 1 ? '' : 's') . ' in ' . $r['documents'] . ' document' . ($r['documents'] === 1 ? '' : 's') . '.' . ($auto > 0 ? ' ' . $auto . ' matched a person by title.' : ''));
        }
        return $this->redirect('admin/documents/signatories');
    }

    /** POST /admin/documents/{id}/signatures/read: read one document. */
    public function readOne(Request $request, int $id): Response
    {
        $this->gate();
        Document::findOrFail($id);
        $n = DocumentSigning::detectSlots($id);
        $auto = DocumentSigning::autoAssign();
        Flash::{$n > 0 ? 'success' : 'info'}($n > 0 ? 'Found ' . $n . ' signature block' . ($n === 1 ? '' : 's') . ' in this document.' . ($auto > 0 ? ' ' . $auto . ' matched a person by title.' : '') : 'No new signature block was found in this document.');
        return $this->redirect('admin/documents/' . $id . '/signatures');
    }

    /** POST /admin/documents/signatories/assign-role: one person for a role in every document that prints it (optionally signing at once). */
    public function assignRole(Request $request): Response
    {
        $this->gate();
        $back = 'admin/documents/signatories';
        $role = mb_substr($this->clean($request->string('role')), 0, 80);
        $g = DocumentSignatory::find($request->int('signatory_id'));
        if ($role === '' || $g === null) {
            Flash::warning('Choose a person for the role.');
            return $this->redirect($back);
        }
        @set_time_limit(240);
        DocumentSigning::assignRole($role, (int) $g['id']);
        $n = (int) Database::value('SELECT COUNT(*) FROM document_signatures WHERE slot_index IS NOT NULL AND role_label = ? AND signatory_id = ?', [$role, (int) $g['id']], 0);
        $signed = 0;
        $failed = 0;
        if ($request->bool('sign')) {
            if (empty($g['signature_path'])) {
                Flash::warning('Upload the signature picture of ' . $g['name'] . ' first; the person was assigned but nothing was signed.');
            } else {
                foreach (Database::all('SELECT id FROM document_signatures WHERE slot_index IS NOT NULL AND role_label = ? AND signatory_id = ? AND signed_at IS NULL', [$role, (int) $g['id']]) as $r) {
                    DocumentSigning::apply((int) $r['id'], $this->userId(), $request->ip())['ok'] ? $signed++ : $failed++;
                }
            }
        }
        $this->audit('document.signature.assign_role', 'document_signatory', (int) $g['id'], 'Assigned "' . $g['name'] . '" to the role "' . $role . '" (' . $n . ' document(s), ' . $signed . ' signed)');
        Flash::success('"' . $g['name'] . '" is now ' . $role . ' in ' . $n . ' document' . ($n === 1 ? '' : 's') . ($signed > 0 ? ' and signed ' . $signed : '') . ($failed > 0 ? ' (' . $failed . ' could not be built)' : '') . '.');
        return $this->redirect($back);
    }

    /** POST /admin/documents/{id}/signatures/{sigid}/assign: a person for one block of one document. */
    public function assignRow(Request $request, int $id, int $sigid): Response
    {
        $this->gate();
        $back = 'admin/documents/' . $id . '/signatures';
        $row = $this->row($id, $sigid);
        $g = DocumentSignatory::find($request->int('signatory_id'));
        if ($g === null) {
            Flash::warning('Choose a person.');
            return $this->redirect($back);
        }
        if (!empty($row['signed_at'])) {
            Flash::warning('This block is signed already. Withdraw the signature first.');
            return $this->redirect($back);
        }
        $ok = Database::execute('UPDATE IGNORE document_signatures SET signatory_id = ? WHERE id = ?', [(int) $g['id'], $sigid]);
        Flash::{$ok > 0 ? 'success' : 'warning'}($ok > 0 ? '"' . $g['name'] . '" will sign as ' . $row['role_label'] . '.' : $g['name'] . ' is already on this document.');
        return $this->redirect($back);
    }
    // ------------------------------------------------------------------------------------------------------------- one document

    public function sign(Request $request, int $id): Response
    {
        $this->authorize('documents.sign');
        if ($r = $this->notReady()) {
            return $r;
        }
        $doc = Document::findOrFail($id);
        $rows = DocumentSigning::rows($id);
        $attached = array_map(static fn(array $r): int => (int) ($r['signatory_id'] ?? 0), $rows);
        $pick = array_values(array_filter(Database::all('SELECT id, name, title, signature_path FROM document_signatories WHERE is_active = 1 ORDER BY name ASC'), static fn(array $g): bool => !in_array((int) $g['id'], $attached, true)));
        return $this->render('admin/documents/signatures', [
            'doc' => $doc, 'rows' => $rows, 'status' => DocumentSigning::status($rows), 'pick' => $pick, 'roles' => DocumentSignature::ROLES, 'people' => Database::all('SELECT id, name, title, signature_path FROM document_signatories WHERE is_active = 1 ORDER BY name ASC'), 'isPdf' => strtolower(pathinfo((string) $doc['file_path'], PATHINFO_EXTENSION)) === 'pdf',
        ], ['title' => 'Signatures: ' . $doc['title'], 'nav' => 'documents', 'breadcrumbs' => [['Documents', url('admin/documents')], [(string) $doc['title'], url('admin/documents/' . $id . '/edit')], ['Signatures', null]]]);
    }

    public function details(Request $request, int $id): Response
    {
        $this->gate();
        $doc = Document::findOrFail($id);
        $back = 'admin/documents/' . $id . '/signatures';
        $d = $this->validate(['version' => 'nullable|max:20', 'approval_authority' => 'nullable|max:120', 'adoption_date' => 'nullable|date'], [], ['adoption_date' => 'Date of adoption'], $back);
        Document::update($id, ['version' => $this->clean((string) ($d['version'] ?? '')) ?: null, 'approval_authority' => $this->clean((string) ($d['approval_authority'] ?? '')) ?: null,
            'adoption_date' => ($d['adoption_date'] ?? '') !== '' ? $d['adoption_date'] : null]);
        $res = DocumentSigning::rebuild((array) Document::find($id));
        $this->audit('document.signature.details', 'document', $id, 'Edited the approval details of "' . $doc['title'] . '"');
        Flash::success('Approval details saved.' . ($res['mode'] !== null ? ' The signed copy was rebuilt.' : ''));
        return $this->redirect($back);
    }

    public function addRow(Request $request, int $id): Response
    {
        $this->gate();
        $doc = Document::findOrFail($id);
        $gid = $request->int('signatory_id');
        $g = DocumentSignatory::find($gid);
        $back = 'admin/documents/' . $id . '/signatures';
        if ($g === null) {
            Flash::warning('Choose a person to add.');
            return $this->redirect($back);
        }
        $role = $this->clean($request->string('role_label')) ?: 'Approved by';
        $order = (int) Database::value('SELECT COALESCE(MAX(sort_order), 0) + 10 FROM document_signatures WHERE document_id = ?', [$id], 10);
        Database::execute('INSERT IGNORE INTO document_signatures (document_id, signatory_id, role_label, sort_order) VALUES (?, ?, ?, ?)', [$id, $gid, mb_substr($role, 0, 80), $order]);
        $this->audit('document.signature.add', 'document', $id, 'Added "' . $g['name'] . '" (' . $role . ') to "' . $doc['title'] . '"');
        Flash::success('"' . $g['name'] . '" was added as "' . $role . '". Apply their signature when they have signed off.');
        return $this->redirect($back);
    }

    private function row(int $docId, int $sigid): array
    {
        $r = Database::one('SELECT * FROM document_signatures WHERE id = ? AND document_id = ?', [$sigid, $docId]);
        if ($r === null) {
            throw new HttpException(404, 'Not found');
        }
        return $r;
    }

    public function apply(Request $request, int $id, int $sigid): Response
    {
        $this->gate();
        $this->row($id, $sigid);
        $res = DocumentSigning::apply($sigid, $this->userId(), $request->ip());
        $this->audit('document.signature.apply', 'document', $id, 'Applied a signature on document #' . $id, ['row' => $sigid]);
        $res['ok'] ? Flash::success('The signature was applied. ' . $res['message']) : Flash::warning($res['message']);
        return $this->redirect('admin/documents/' . $id . '/signatures');
    }

    public function unsign(Request $request, int $id, int $sigid): Response
    {
        $this->gate();
        $this->row($id, $sigid);
        Database::update('document_signatures', ['signed_at' => null, 'signed_by' => null, 'signed_ip' => null], 'id = ?', [$sigid]);
        $res = DocumentSigning::rebuild((array) Document::find($id));
        $this->audit('document.signature.withdraw', 'document', $id, 'Withdrew a signature on document #' . $id, ['row' => $sigid]);
        Flash::success('The signature was withdrawn. ' . $res['message']);
        return $this->redirect('admin/documents/' . $id . '/signatures');
    }

    public function removeRow(Request $request, int $id, int $sigid): Response
    {
        $this->gate();
        $row = $this->row($id, $sigid);
        if ($row['slot_index'] !== null) {
            // a block of the document's own signature page stays (the role is printed there); only the person is taken off it
            Database::execute('UPDATE document_signatures SET signatory_id = NULL, signed_at = NULL, signed_by = NULL, signed_ip = NULL WHERE id = ?', [$sigid]);
        } else {
            Database::execute('DELETE FROM document_signatures WHERE id = ?', [$sigid]);
        }
        $res = DocumentSigning::rebuild((array) Document::find($id));
        $this->audit('document.signature.remove', 'document', $id, 'Removed a signatory from document #' . $id, ['row' => $sigid]);
        Flash::success('Removed. ' . $res['message']);
        return $this->redirect('admin/documents/' . $id . '/signatures');
    }

    public function generate(Request $request, int $id): Response
    {
        $this->gate();
        Document::findOrFail($id);
        $res = DocumentSigning::rebuild((array) Document::find($id));
        $res['ok'] ? Flash::success($res['message']) : Flash::warning($res['message']);
        return $this->redirect('admin/documents/' . $id . '/signatures');
    }

    /** The signed copy, for staff. */
    public function downloadSigned(Request $request, int $id): Response
    {
        $this->gate();
        $doc = Document::findOrFail($id);
        if (empty($doc['signed_path'])) {
            throw new HttpException(404, 'Not found');
        }
        $name = pathinfo((string) ($doc['file_name'] ?: 'document.pdf'), PATHINFO_FILENAME) . '-signed.pdf';
        return Uploader::download((string) $doc['signed_path'], $name);
    }

    // ------------------------------------------------------------------------------------------------------------- public / private and the policy pack

    /** One click: make a document public or private (content.manage, like the rest of the documents screens). */
    public function visibility(Request $request, int $id): Response
    {
        $this->authorize('content.manage');
        $doc = Document::findOrFail($id);
        $new = (int) $doc['is_public'] === 1 ? 0 : 1;
        Document::update($id, ['is_public' => $new]);
        $this->audit('document.' . ($new ? 'public' : 'private'), 'document', $id, ($new ? 'Made public' : 'Made private') . ': "' . $doc['title'] . '"');
        $signed = !empty($doc['signed_path']);
        Flash::success('"' . $doc['title'] . '" is now ' . ($new ? 'public' . ($signed ? ' (visitors get the signed copy).' : ($doc['category'] === 'policy' ? ' (it has no signature yet: visitors get the unsigned file).' : '.')) : 'private: it is no longer downloadable.'));
        return Response::redirect($this->returnUrl());
    }

    public function importPack(Request $request): Response
    {
        $this->authorize('documents.sign');
        $dir = DocumentSigning::packDir();
        if (!is_dir($dir) || (glob($dir . '/*.pdf') ?: []) === []) {
            Flash::warning('There is no policy pack on the server yet. Upload the PDF files into storage/private/policy-pack (the update package does this), then try again.');
            return $this->redirect('admin/documents');
        }
        @set_time_limit(180);
        $r = DocumentSigning::importPack($dir, $this->userId());
        $this->audit('document.import_pack', 'document', null, 'Imported the policy pack: ' . $r['added'] . ' added, ' . $r['skipped'] . ' already there');
        Flash::success($r['added'] . ' polic' . ($r['added'] === 1 ? 'y' : 'ies') . ' added to Documents (private until you make them public)' . ($r['skipped'] > 0 ? ', ' . $r['skipped'] . ' already there' : '') . '.');
        foreach ($r['failed'] as $f) {
            Flash::warning($f);
        }
        return $this->redirect('admin/documents?category=policy');
    }

    // ------------------------------------------------------------------------------------------------------------- helpers

    /** The uploaded signature picture, checked and stored; path '' when none was sent. @return array{path:string,mime:string} */
    private function picture(string $field, string $back): array
    {
        $f = $this->request->file($field);
        if ($f === null) {
            return ['path' => '', 'mime' => ''];
        }
        if ((int) ($f['error'] ?? 0) !== UPLOAD_ERR_OK || !is_uploaded_file((string) $f['tmp_name'])) {
            $this->fail([$field => 'The picture could not be uploaded. Try a smaller file.'], $back);
        }
        $r = DocumentSigning::storeSignatureImage((string) file_get_contents((string) $f['tmp_name']));
        if (!$r['ok']) {
            $this->fail([$field => $r['error']], $back);
        }
        return ['path' => $r['path'], 'mime' => $r['mime']];
    }
}
