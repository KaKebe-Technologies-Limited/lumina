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
                    && (int) Database::value("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'documents' AND column_name = 'signed_path'", [], 0) === 1;
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
            'SELECT s.*, g.name, g.title, g.signature_path, g.signature_mime, g.is_active AS signatory_active FROM document_signatures s JOIN document_signatories g ON g.id = s.signatory_id
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
        $data = [];
        foreach ($rows as $r) {
            $image = null;
            if (!empty($r['signed_at']) && !empty($r['signature_path'])) {
                try {
                    $image = (string) file_get_contents(Uploader::privatePath((string) $r['signature_path']));
                } catch (\Throwable) {
                    $image = null;
                }
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
            $page = PdfSigner::approvalPdf($meta, $data);
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => 'The approval page could not be drawn: ' . $e->getMessage(), 'mode' => null];
        }
        $merged = PdfSigner::appendPages($original, $page);
        $mode = $merged !== null ? 'merged' : 'page';
        $bytes = $merged ?? $page;
        $rel = 'documents/' . date('Y/m') . '/' . bin2hex(random_bytes(16)) . '.pdf';
        if (!self::write($rel, $bytes)) {
            return ['ok' => false, 'message' => 'The server could not save the signed copy.', 'mode' => null];
        }
        Database::update('documents', ['signed_path' => $rel, 'signed_size' => strlen($bytes), 'signed_at' => date('Y-m-d H:i:s'), 'signed_mode' => $mode], 'id = ?', [$id]);
        if ($old !== '') {
            Uploader::deletePrivate($old);
        }
        return ['ok' => true, 'mode' => $mode, 'message' => $mode === 'merged'
            ? 'The signed copy was built: the original pages plus an "Approval and signatures" page.'
            : 'This PDF cannot be extended, so the signed copy is the approval page on its own (download it together with the original).'];
    }

    /** Apply the signature of a row now (needs the signatory\'s picture) and rebuild. @return array{ok:bool,message:string} */
    public static function apply(int $rowId, ?int $userId, string $ip): array
    {
        $r = Database::one('SELECT s.*, g.name, g.signature_path FROM document_signatures s JOIN document_signatories g ON g.id = s.signatory_id WHERE s.id = ?', [$rowId]);
        if ($r === null) {
            return ['ok' => false, 'message' => 'Signature not found.'];
        }
        if (empty($r['signature_path'])) {
            return ['ok' => false, 'message' => $r['name'] . ' has no signature picture yet. Upload one under Signatories first.'];
        }
        Database::update('document_signatures', ['signed_at' => date('Y-m-d H:i:s'), 'signed_by' => $userId, 'signed_ip' => mb_substr($ip, 0, 45)], 'id = ?', [$rowId]);
        $doc = Document::find((int) $r['document_id']);
        $res = $doc !== null ? self::rebuild($doc) : ['ok' => false, 'message' => 'Document not found.'];
        return ['ok' => $res['ok'], 'message' => $res['message']];
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
        return ['added' => $added, 'skipped' => $skipped, 'failed' => $failed];
    }
}
