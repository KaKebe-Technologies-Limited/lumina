<?php
declare(strict_types=1);

namespace App\Services\Documents;

use App\Core\Database;
use App\Core\PdfValidator;
use App\Core\Uploader;
use App\Models\Document;
use App\Services\Membership\Card\PdfWriter;

/**
 * SIGNING of documents (policies). The people responsible for a policy are "signatories": each has a name, a title and a signature picture (PNG or JPEG, kept privately).
 * An administrator attaches signatories to a document with a role ("Approved by" ...) and applies each signature; every change rebuilds the SIGNED COPY of the PDF:
 * the original file plus an "Approval and signatures" page (see PdfSigner). The original is never changed. Visitors download the signed copy when there is one.
 * A document counts as SIGNED when it has signatures and every one of them is applied. Also here: the importer of the policy pack (a folder of PDFs).
 * Docs: docs/DOCUMENTS-SIGNATURES.md.
 */
final class DocumentSigning
{
    public const SIGNATURE_MAX_BYTES = 1572864;                 // 1.5 MB

    /** The 17 policies of the LAF policy pack: file name => [title, review cycle, adoption date (only where the document states one), reference code]. */
    public const PACK = [
        'LAF_Anti-Fraud_Bribery_Corruption_Policy.pdf' => ['Anti-Fraud, Bribery & Corruption Policy', 'every 2 years', null],
        'LAF_Code_of_Conduct.pdf' => ['Code of Conduct', 'every 2 years', null],
        'LAF_Conflict_of_Interest_Policy.pdf' => ['Conflict of Interest Policy', 'every 2 years', null],
        'LAF_Data_Protection_Privacy_Policy.pdf' => ['Data Protection and Privacy Policy', 'every 2 years', null],
        'LAF_Financial_Management_Policy.pdf' => ['Financial Management Policy', 'every 3 years', null],
        'LAF_GESI_Policy.pdf' => ['Gender Equality & Social Inclusion (GESI) Policy', 'every 3 years', null],
        'LAF_Governance_Leadership_Policy.pdf' => ['Governance & Leadership Policy', 'every 3 years', null],
        'LAF_HR_Policy_Manual.pdf' => ['Human Resource Policy Manual', 'every 3 years', '2026-11-01'],
        'LAF_Health_Safety_Security_Policy.pdf' => ['Health, Safety and Security Policy', 'every 2 years', null],
        'LAF_ICT_Social_Media_Policy.pdf' => ['ICT and Social Media Policy', 'every 2 years', null],
        'LAF_MEL_Policy.pdf' => ['Monitoring, Evaluation & Learning Policy', 'every 3 years', null],
        'LAF_PSEA_Policy.pdf' => ['Prevention of Sexual Exploitation and Abuse (PSEA) Policy', 'every 2 years', null],
        'LAF_Procurement_Asset_Management_Policy.pdf' => ['Procurement & Asset Management Policy', 'every 3 years', null],
        'LAF_Risk_Management_Policy.pdf' => ['Risk Management & Internal Control Policy', 'every 2 years', null],
        'LAF_Safeguarding_Child_Protection_Policy.pdf' => ['Safeguarding & Child Protection Policy', 'every 2 years', null],
        'LAF_Volunteer_Policy_Handbook.pdf' => ['Volunteer Policy and Handbook', 'every 2 years', null],
        'LAF_Whistleblower_Policy.pdf' => ['Whistleblower Policy', 'every 2 years', null],
    ];

    /** Where the policy pack PDFs are put on the server (private, not reachable from the web). */
    public static function packDir(): string
    {
        return STORAGE_PATH . '/private/policy-pack';
    }

    public static function ready(): bool
    {
        static $ok = null;
        if ($ok === null) {
            try {
                $ok = Database::tableExists('document_signatories') && Database::tableExists('document_signatures')
                    && (int) Database::value("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'documents' AND column_name = 'signed_path'", [], 0) === 1
                    && (int) Database::value("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'document_signatures' AND column_name = 'slot_index'", [], 0) === 1;
            } catch (\Throwable) {
                $ok = false;
            }
        }
        return $ok;
    }

    // ------------------------------------------------------------------------------------------------------------- reading

    /** The signature rows of a document with the signatory's details. @return list<array<string,mixed>> */
    public static function rows(int $documentId): array
    {
        return Database::all(
            'SELECT s.*, g.name, g.title, g.signature_path, g.signature_mime, g.is_active AS signatory_active FROM document_signatures s LEFT JOIN document_signatories g ON g.id = s.signatory_id
             WHERE s.document_id = ? ORDER BY s.sort_order ASC, s.id ASC',
            [$documentId]
        );
    }

    /** @param list<array<string,mixed>> $rows @return array{total:int,signed:int,state:string,label:string} state: none | awaiting | partial | signed */
    public static function status(array $rows): array
    {
        $total = count($rows);
        $signed = count(array_filter($rows, static fn(array $r): bool => !empty($r['signed_at'])));
        if ($total === 0) {
            return ['total' => 0, 'signed' => 0, 'state' => 'none', 'label' => 'No signatures set up'];
        }
        if ($signed === $total) {
            return ['total' => $total, 'signed' => $signed, 'state' => 'signed', 'label' => 'Signed (' . $signed . ' of ' . $total . ')'];
        }
        if ($signed === 0) {
            return ['total' => $total, 'signed' => 0, 'state' => 'awaiting', 'label' => 'Awaiting signatures (0 of ' . $total . ')'];
        }
        return ['total' => $total, 'signed' => $signed, 'state' => 'partial', 'label' => 'Partly signed (' . $signed . ' of ' . $total . ')'];
    }

    /** signed / total per document id for a list. @param list<int> $ids @return array<int,array{total:int,signed:int}> */
    public static function counts(array $ids): array
    {
        $out = [];
        if ($ids === [] || !self::ready()) {
            return $out;
        }
        foreach (Database::all('SELECT document_id, COUNT(*) AS n, SUM(signed_at IS NOT NULL) AS done FROM document_signatures WHERE document_id IN (' . implode(',', array_map('intval', $ids)) . ') GROUP BY document_id') as $r) {
            $out[(int) $r['document_id']] = ['total' => (int) $r['n'], 'signed' => (int) $r['done']];
        }
        return $out;
    }

    // ------------------------------------------------------------------------------------------------------------- signature pictures

    /**
     * Check and store a signature picture (PNG or JPEG, up to 1.5 MB) privately.
     * @return array{ok:bool,error:string,path:string,mime:string}
     */
    public static function storeSignatureImage(string $bytes): array
    {
        if ($bytes === '' || strlen($bytes) > self::SIGNATURE_MAX_BYTES) {
            return ['ok' => false, 'error' => 'The signature picture must be smaller than 1.5 MB.', 'path' => '', 'mime' => ''];
        }
        $info = PdfWriter::imageInfo($bytes);
        if ($info === null) {
            return ['ok' => false, 'error' => 'Use a PNG or JPEG picture of the signature (a transparent PNG up to about 1000 x 1000 pixels looks best; interlaced and 16-bit PNGs are not supported).', 'path' => '', 'mime' => ''];
        }
        if ($info['w'] < 60 || $info['h'] < 20 || $info['w'] > 4000 || $info['h'] > 4000) {
            return ['ok' => false, 'error' => 'The signature picture is too small or too large (' . $info['w'] . ' x ' . $info['h'] . ' pixels). Use one between 60 x 20 and 4000 x 4000 pixels.', 'path' => '', 'mime' => ''];
        }
        $ext = $info['mime'] === 'image/png' ? 'png' : 'jpg';
        $rel = 'documents/signatures/' . date('Y') . '/' . bin2hex(random_bytes(16)) . '.' . $ext;
        if (!self::write($rel, $bytes)) {
            return ['ok' => false, 'error' => 'The server could not save the picture.', 'path' => '', 'mime' => ''];
        }
        return ['ok' => true, 'error' => '', 'path' => $rel, 'mime' => $info['mime']];
    }

    /** Write a file under storage/private (creating folders). */
    private static function write(string $rel, string $bytes): bool
    {
        $abs = STORAGE_PATH . '/private/' . $rel;
        $dir = dirname($abs);
        if (!is_dir($dir) && !@mkdir($dir, 0750, true) && !is_dir($dir)) {
            return false;
        }
        if (@file_put_contents($abs, $bytes) === false) {
            return false;
        }
        @chmod($abs, 0640);
        return true;
    }

    // ------------------------------------------------------------------------------------------------------------- building the signed copy

    /**
     * Rebuild the signed copy of a document from its original file and the applied signatures (no applied signature = no signed copy).
     * @param array<string,mixed> $doc a documents row
     * @return array{ok:bool,message:string,mode:?string}
     */
    public static function rebuild(array $doc): array
    {
        $id = (int) $doc['id'];
        $rows = self::rows($id);
        $old = (string) ($doc['signed_path'] ?? '');
        if (count(array_filter($rows, static fn(array $r): bool => !empty($r['signed_at']))) === 0) {
            if ($old !== '') {
                Uploader::deletePrivate($old);
            }
            Database::update('documents', ['signed_path' => null, 'signed_size' => null, 'signed_at' => null, 'signed_mode' => null], 'id = ?', [$id]);
            return ['ok' => true, 'message' => 'No signature is applied, so there is no signed copy.', 'mode' => null];
        }
        if (strtolower(pathinfo((string) $doc['file_path'], PATHINFO_EXTENSION)) !== 'pdf') {
            return ['ok' => false, 'message' => 'Only PDF documents can be signed.', 'mode' => null];
        }
        try {
            $original = (string) file_get_contents(Uploader::privatePath((string) $doc['file_path']));
        } catch (\Throwable) {
            return ['ok' => false, 'message' => 'The original file is missing on the server.', 'mode' => null];
        }
        if (!str_starts_with($original, '%PDF-')) {
            return ['ok' => false, 'message' => 'The original file is not a PDF.', 'mode' => null];
        }
        $info = PdfSlots::read($original);
        $slots = [];
        foreach (($info['slots'] ?? []) as $sl) {
            $slots[(int) $sl['index']] = $sl;
        }
        $draws = [];
        $data = [];
        foreach ($rows as $r) {
            if (empty($r['signatory_id'])) {
                continue;                                                          // a block that still waits for a person
            }
            $image = null;
            if (!empty($r['signed_at']) && !empty($r['signature_path'])) {
                try {
                    $image = (string) file_get_contents(Uploader::privatePath((string) $r['signature_path']));
                } catch (\Throwable) {
                    $image = null;
                }
            }
            if ($r['slot_index'] !== null && isset($slots[(int) $r['slot_index']])) {
                // a block of the document's own signature page: the signature is drawn ON its lines
                if (!empty($r['signed_at'])) {
                    $draws[] = ['slot' => $slots[(int) $r['slot_index']], 'name' => (string) $r['name'], 'title' => $r['title'] !== null ? (string) $r['title'] : null,
                        'date' => date('j M Y', (int) strtotime((string) $r['signed_at'])), 'image' => $image];
                }
                continue;
            }
            $data[] = ['role' => (string) $r['role_label'], 'name' => (string) $r['name'], 'title' => $r['title'] !== null ? (string) $r['title'] : null,
                'signed_at' => !empty($r['signed_at']) ? (string) $r['signed_at'] : null, 'image' => $image];
        }
        $site = trim((string) setting('academy_org_name', '')) ?: (trim((string) setting('site_name', 'Lumina Africa Foundation')) ?: 'Lumina Africa Foundation');
        $meta = [
            'title' => (string) $doc['title'], 'reference' => 'LAF-DOC-' . str_pad((string) $id, 4, '0', STR_PAD_LEFT), 'version' => ($doc['version'] ?? null) ?: null,
            'authority' => ($doc['approval_authority'] ?? null) ?: null, 'adoption' => !empty($doc['adoption_date']) ? date('j F Y', (int) strtotime((string) $doc['adoption_date'])) : null,
            'org' => $site, 'fingerprint' => hash('sha256', $original), 'generated' => time(),
        ];
        try {
            $overlay = $draws !== [] ? PdfSigner::overlayPdf($info, $draws) : null;
            $page = $data !== [] ? PdfSigner::approvalPdf($meta, $data) : null;
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => 'The signatures could not be drawn: ' . $e->getMessage(), 'mode' => null];
        }
        $merged = $info !== null || $page !== null ? PdfSigner::build($original, $info, $overlay, $page) : null;
        $mode = $merged !== null ? ($overlay !== null && $page === null ? 'inplace' : 'merged') : 'page';
        if ($merged === null) {
            // the original cannot be extended: every applied signature goes on a standalone approval page
            $all = [];
            foreach ($rows as $r) {
                if (empty($r['signatory_id']) || empty($r['signed_at'])) {
                    continue;
                }
                $img = null;
                if (!empty($r['signature_path'])) {
                    try {
                        $img = (string) file_get_contents(Uploader::privatePath((string) $r['signature_path']));
                    } catch (\Throwable) {
                        $img = null;
                    }
                }
                $all[] = ['role' => (string) $r['role_label'], 'name' => (string) $r['name'], 'title' => $r['title'] !== null ? (string) $r['title'] : null, 'signed_at' => (string) $r['signed_at'], 'image' => $img];
            }
            if ($all === []) {
                return ['ok' => false, 'message' => 'Nothing to sign: no applied signature has a person.', 'mode' => null];
            }
            $bytes = PdfSigner::approvalPdf($meta, $all);
        } else {
            $bytes = $merged;
        }
        $rel = 'documents/' . date('Y/m') . '/' . bin2hex(random_bytes(16)) . '.pdf';
        if (!self::write($rel, $bytes)) {
            return ['ok' => false, 'message' => 'The server could not save the signed copy.', 'mode' => null];
        }
        Database::update('documents', ['signed_path' => $rel, 'signed_size' => strlen($bytes), 'signed_at' => date('Y-m-d H:i:s'), 'signed_mode' => $mode], 'id = ?', [$id]);
        if ($old !== '') {
            Uploader::deletePrivate($old);
        }
        return ['ok' => true, 'mode' => $mode, 'message' => match ($mode) {
            'inplace' => 'The signed copy was built: the signatures, names and dates are on the document\'s own signature lines.',
            'merged' => 'The signed copy was built: the original pages plus an "Approval and signatures" page.',
            default => 'This PDF cannot be extended, so the signed copy is the approval page on its own (download it together with the original).',
        }];
    }

    /** Apply the signature of a row now (needs the signatory\'s picture) and rebuild. @return array{ok:bool,message:string} */
    public static function apply(int $rowId, ?int $userId, string $ip): array
    {
        $r = Database::one('SELECT s.*, g.name, g.signature_path FROM document_signatures s LEFT JOIN document_signatories g ON g.id = s.signatory_id WHERE s.id = ?', [$rowId]);
        if ($r === null) {
            return ['ok' => false, 'message' => 'Signature not found.'];
        }
        if (empty($r['signatory_id'])) {
            return ['ok' => false, 'message' => 'Choose who signs as "' . $r['role_label'] . '" first.'];
        }
        if ($r === null || empty($r['signature_path'])) {
            return ['ok' => false, 'message' => ($r['name'] ?? 'This person') . ' has no signature picture yet. Upload one under Signatories first.'];
        }
        Database::update('document_signatures', ['signed_at' => date('Y-m-d H:i:s'), 'signed_by' => $userId, 'signed_ip' => mb_substr($ip, 0, 45)], 'id = ?', [$rowId]);
        $doc = Document::find((int) $r['document_id']);
        $res = $doc !== null ? self::rebuild($doc) : ['ok' => false, 'message' => 'Document not found.'];
        return ['ok' => $res['ok'], 'message' => $res['message']];
    }

    // ------------------------------------------------------------------------------------------------------------- reading who must sign

    /**
     * Read the signature page of a PDF document and add one row per signature block that is not there yet (role as printed, no person). Safe to repeat.
     * @return int how many blocks were added (0 = nothing found or all known)
     */
    public static function detectSlots(int $documentId): int
    {
        $doc = Document::find($documentId);
        if ($doc === null || strtolower(pathinfo((string) $doc['file_path'], PATHINFO_EXTENSION)) !== 'pdf') {
            return 0;
        }
        try {
            $bytes = (string) file_get_contents(Uploader::privatePath((string) $doc['file_path']));
        } catch (\Throwable) {
            return 0;
        }
        $info = PdfSlots::read($bytes);
        if ($info === null || $info['slots'] === []) {
            return 0;
        }
        $have = array_map('intval', array_column(Database::all('SELECT slot_index FROM document_signatures WHERE document_id = ? AND slot_index IS NOT NULL', [$documentId]), 'slot_index'));
        $added = 0;
        foreach ($info['slots'] as $sl) {
            if (in_array((int) $sl['index'], $have, true)) {
                continue;
            }
            $added += Database::execute('INSERT INTO document_signatures (document_id, signatory_id, role_label, sort_order, slot_index) VALUES (?, NULL, ?, ?, ?)',
                [$documentId, (string) $sl['role'], ((int) $sl['index'] + 1) * 10, (int) $sl['index']]) > 0 ? 1 : 0;
        }
        return $added;
    }

    /** Read every PDF document. @return array{documents:int,blocks:int} documents that gained blocks, blocks added */
    public static function detectAll(): array
    {
        $docs = 0;
        $blocks = 0;
        foreach (Database::all("SELECT id FROM documents WHERE file_path LIKE '%.pdf' AND is_active = 1 ORDER BY id") as $d) {
            $n = self::detectSlots((int) $d['id']);
            if ($n > 0) {
                $docs++;
                $blocks += $n;
            }
        }
        return ['documents' => $docs, 'blocks' => $blocks];
    }

    /** The roles found in the documents, with how many documents print each, who is assigned and how many are signed. @return list<array<string,mixed>> */
    public static function roles(): array
    {
        $out = [];
        foreach (Database::all(
            'SELECT s.role_label, COUNT(*) AS total, SUM(s.signatory_id IS NULL) AS waiting, SUM(s.signed_at IS NOT NULL) AS signed, COUNT(DISTINCT s.signatory_id) AS people, MIN(s.signatory_id) AS signatory_id
             FROM document_signatures s WHERE s.slot_index IS NOT NULL GROUP BY s.role_label ORDER BY COUNT(*) DESC, s.role_label ASC'
        ) as $r) {
            $out[] = ['role' => (string) $r['role_label'], 'total' => (int) $r['total'], 'waiting' => (int) $r['waiting'], 'signed' => (int) $r['signed'], 'people' => (int) $r['people'],
                'signatory_id' => $r['signatory_id'] !== null ? (int) $r['signatory_id'] : null];
        }
        return $out;
    }

    /** Words that carry the meaning of a role or title, for matching ("Chairperson of the Board" and "CHAIRPERSON, BOARD OF DIRECTORS" give the same words). @return list<string> */
    public static function roleWords(string $text): array
    {
        $text = strtolower(str_replace(['&', ',', '/', '-'], ' ', $text));
        $words = [];
        foreach (preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $w) {
            $w = ['chairperson' => 'chair', 'chairman' => 'chair', 'chairwoman' => 'chair', 'directors' => 'board', 'ed' => 'executive', 'secretary' => 'secretary'][$w] ?? $w;
            if (in_array($w, ['of', 'the', 'and', 'for', 'a', 'to'], true)) {
                continue;
            }
            $words[$w] = $w;
        }
        return array_values($words);
    }

    /** How well a person's title fits a printed role, 0 to 1 (1 = the same words). */
    public static function roleMatch(string $role, string $title): float
    {
        $a = self::roleWords($role);
        $b = self::roleWords($title);
        if ($a === [] || $b === []) {
            return 0.0;
        }
        $both = count(array_intersect($a, $b));
        $all = count(array_unique(array_merge($a, $b)));
        return $all === 0 ? 0.0 : $both / $all;
    }

    /**
     * Give the people to the blocks that wait for one, by title: every role with no person goes to the active signatory whose title fits it best (at least half the words),
     * provided that person fits no other waiting role better. Never changes a block that already has a person. @return int blocks assigned
     */
    public static function autoAssign(): int
    {
        $people = Database::all('SELECT id, title FROM document_signatories WHERE is_active = 1 AND title IS NOT NULL AND title <> \'\'');
        $n = 0;
        foreach (Database::all('SELECT DISTINCT role_label FROM document_signatures WHERE slot_index IS NOT NULL AND signatory_id IS NULL') as $r) {
            $best = null;
            $bestScore = 0.0;
            foreach ($people as $p) {
                $sc = self::roleMatch((string) $r['role_label'], (string) $p['title']);
                if ($sc > $bestScore + 0.0001) {
                    $best = (int) $p['id'];
                    $bestScore = $sc;
                }
            }
            if ($best !== null && $bestScore >= 0.5) {
                $n += self::assignRole((string) $r['role_label'], $best);
            }
        }
        return $n;
    }

    /** Put a person on every block of this role that has nobody (and is not signed). @return int blocks assigned */
    public static function assignRole(string $role, int $signatoryId): int
    {
        return Database::execute('UPDATE IGNORE document_signatures SET signatory_id = ? WHERE slot_index IS NOT NULL AND role_label = ? AND signatory_id IS NULL AND signed_at IS NULL', [$signatoryId, $role]);
    }

    /** A person leaves: their blocks of the documents' own signature pages wait again, any other row of theirs is removed; the signed copies are rebuilt. */
    public static function releasePerson(int $signatoryId): void
    {
        $docIds = array_map('intval', array_column(Database::all('SELECT DISTINCT document_id FROM document_signatures WHERE signatory_id = ?', [$signatoryId]), 'document_id'));
        Database::execute('UPDATE document_signatures SET signatory_id = NULL, signed_at = NULL, signed_by = NULL, signed_ip = NULL WHERE signatory_id = ? AND slot_index IS NOT NULL', [$signatoryId]);
        Database::execute('DELETE FROM document_signatures WHERE signatory_id = ?', [$signatoryId]);
        foreach ($docIds as $d) {
            if (($doc = Document::find($d)) !== null) {
                self::rebuild($doc);
            }
        }
    }
    // ------------------------------------------------------------------------------------------------------------- policy pack importer

    /**
     * Add the PDFs of a folder to Documents as policies (private, active, not public until an administrator publishes them). A file already imported (same file name) is skipped.
     * @return array{added:int,skipped:int,failed:list<string>}
     */
    public static function importPack(string $dir, ?int $userId = null): array
    {
        $added = 0;
        $skipped = 0;
        $failed = [];
        $files = glob(rtrim($dir, '/\\') . '/*.pdf') ?: [];
        sort($files);
        foreach ($files as $file) {
            $name = basename($file);
            if (Database::value('SELECT 1 FROM documents WHERE file_name = ? LIMIT 1', [$name]) !== null) {
                $skipped++;
                continue;
            }
            try {
                PdfValidator::inspect($file, 64 * 1024 * 1024);
            } catch (\Throwable $e) {
                $failed[] = $name . ': ' . $e->getMessage();
                continue;
            }
            $meta = self::PACK[$name] ?? [trim(str_replace(['_', 'LAF '], [' ', ''], preg_replace('/\.pdf$/i', '', $name) ?? $name)), 'every 2 years', null];
            $rel = 'documents/' . date('Y/m') . '/' . bin2hex(random_bytes(16)) . '.pdf';
            $bytes = file_get_contents($file);
            if ($bytes === false || !self::write($rel, $bytes)) {
                $failed[] = $name . ': could not be copied.';
                continue;
            }
            Document::create([
                'title' => $meta[0], 'category' => 'policy', 'year' => null, 'description' => 'Lumina Africa Foundation policy. Version 1.0. Approved by the Board of Directors once signed. Reviewed ' . $meta[1] . '.',
                'version' => '1.0', 'approval_authority' => 'Board of Directors', 'adoption_date' => $meta[2],
                'file_path' => $rel, 'file_name' => $name, 'file_size' => strlen($bytes), 'mime' => 'application/pdf', 'is_public' => 0, 'is_active' => 1,
            ]);
            $added++;
        }
        if ($added > 0) {
            self::detectAll();
            self::autoAssign();
        }
        return ['added' => $added, 'skipped' => $skipped, 'failed' => $failed];
    }
}
