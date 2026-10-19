<?php
declare(strict_types=1);

namespace App\Services\Newsletter;

use App\Core\AuditLog;
use App\Core\Database;
use App\Core\HttpException;
use App\Core\Log;
use App\Core\Response;
use App\Core\UploadException;
use App\Core\Uploader;
use App\Models\Newsletter;

/**
 * The PDF of a newsletter issue (kind = 'pdf', or an online issue with an attached "Download this issue as PDF").
 * Contract: docs/PARTNERS_NEWSLETTER.md section 5. All methods are static.
 *
 * The file lives OUTSIDE the web root (storage/private/newsletters/YYYY/MM/<random>.pdf, never reachable by URL), so unpublishing, archiving or
 * deleting an issue stops the file immediately: it is only ever streamed by a controller that checked NewsletterService::isVisible() (public)
 * or the admin session (preview). Swapping the PDF replaces the file at once (a new random name, so no browser or CDN can keep serving the old one).
 *
 *   NewsletterPdf::attach($id, $request->file('pdf'), $userId)   // upload or replace
 *   NewsletterPdf::remove($id, $userId)                           // refused for a live / scheduled PDF-format issue (it would be empty)
 *   NewsletterPdf::response($row, inline: true|false)             // streams; HttpException(404) when the file is gone
 *   NewsletterPdf::recordDownload($id)                            // +1 on pdf_downloads (the "Download PDF" route only)
 *   NewsletterPdf::info($row)                                     // what views / the API show: name, size, sizes, urls
 */
final class NewsletterPdf
{
    /** Does the row carry a PDF (a path is stored)? */
    public static function has(array $row): bool
    {
        return trim((string) ($row['pdf_path'] ?? '')) !== '';
    }

    /** Is the stored PDF really on disk? */
    public static function exists(array $row): bool
    {
        return self::has($row) && Uploader::privateExists((string) $row['pdf_path']);
    }

    /**
     * Store a PDF for an issue (first upload or replacement). Does not change `version` (it is a file operation like a status change, so an open
     * editor tab keeps saving its text without a conflict). The download counter restarts for a new document.
     *
     * @return array{ok:bool,newsletter?:array,errors?:array<string,string>,error?:string,not_found?:bool,locked?:bool}
     */
    public static function attach(int $id, ?array $file, ?int $userId = null): array
    {
        $row = Newsletter::find($id);
        if ($row === null) {
            return ['ok' => false, 'not_found' => true, 'error' => 'That issue no longer exists.'];
        }
        if (in_array($row['email_status'], Newsletter::EMAIL_ACTIVE, true)) {
            return ['ok' => false, 'locked' => true, 'error' => 'The e-mail campaign of this issue is running (or paused). Cancel the campaign before changing the PDF, so every subscriber receives the same message.'];
        }
        try {
            $stored = Uploader::pdf($file);
        } catch (UploadException $e) {
            return ['ok' => false, 'errors' => ['pdf' => $e->getMessage()], 'error' => $e->getMessage()];
        }
        try {
            Database::update('newsletters', [
                'pdf_path'       => $stored['path'],
                'pdf_name'       => $stored['name'],
                'pdf_size'       => $stored['size'],
                'pdf_downloads'  => 0,
                'pdf_updated_at' => date('Y-m-d H:i:s'),
                'updated_by'     => $userId,
            ], 'id = ?', [$id]);
        } catch (\Throwable $e) {
            Uploader::deletePrivate($stored['path']);
            Log::warning('NewsletterPdf::attach failed - ' . $e->getMessage());
            return ['ok' => false, 'errors' => ['pdf' => 'The PDF could not be saved. Please try again.'], 'error' => 'The PDF could not be saved. Please try again.'];
        }
        self::releaseFile((string) ($row['pdf_path'] ?? ''), $id);
        AuditLog::record('newsletter.pdf_attach', 'newsletter', $id, 'Uploaded the PDF of a newsletter issue', ['bytes' => $stored['size'], 'replaced' => self::has($row)]);
        return ['ok' => true, 'newsletter' => Newsletter::find($id) ?? []];
    }

    /**
     * Remove the PDF of an issue. A PDF-FORMAT issue that is published or scheduled cannot lose its PDF (the page would be empty): unpublish first.
     * @return array{ok:bool,newsletter?:array,error?:string,not_found?:bool,locked?:bool}
     */
    public static function remove(int $id, ?int $userId = null): array
    {
        $row = Newsletter::find($id);
        if ($row === null) {
            return ['ok' => false, 'not_found' => true, 'error' => 'That issue no longer exists.'];
        }
        if (in_array($row['email_status'], Newsletter::EMAIL_ACTIVE, true)) {
            return ['ok' => false, 'locked' => true, 'error' => 'The e-mail campaign of this issue is running (or paused). Cancel the campaign before changing the PDF.'];
        }
        if (!self::has($row)) {
            return ['ok' => true, 'newsletter' => $row];
        }
        if ((string) ($row['kind'] ?? 'online') === 'pdf' && in_array((string) $row['status'], ['published', 'scheduled'], true)) {
            return ['ok' => false, 'error' => 'This issue is a PDF issue and is published or scheduled, so it cannot lose its PDF. Replace the PDF with another one, or unpublish the issue first.'];
        }
        Database::update('newsletters', ['pdf_path' => null, 'pdf_name' => null, 'pdf_size' => 0, 'pdf_downloads' => 0, 'pdf_updated_at' => null, 'updated_by' => $userId], 'id = ?', [$id]);
        self::releaseFile((string) $row['pdf_path'], $id);
        AuditLog::record('newsletter.pdf_remove', 'newsletter', $id, 'Removed the PDF of a newsletter issue');
        return ['ok' => true, 'newsletter' => Newsletter::find($id) ?? []];
    }

    /**
     * Delete a stored PDF file unless another issue row still uses it (duplicates get their own copy, so normally nothing does).
     * Never throws.
     */
    public static function releaseFile(?string $path, ?int $exceptId = null): void
    {
        $path = trim((string) $path);
        if ($path === '') {
            return;
        }
        try {
            $users = (int) Database::value(
                'SELECT COUNT(*) FROM newsletters WHERE pdf_path = ?' . ($exceptId !== null ? ' AND id <> ?' : ''),
                $exceptId !== null ? [$path, $exceptId] : [$path],
                0
            );
            if ($users === 0) {
                Uploader::deletePrivate($path);
            }
        } catch (\Throwable $e) {
            Log::warning('NewsletterPdf::releaseFile kept ' . $path . ' - ' . $e->getMessage());
        }
    }

    /**
     * A copy of the issue's PDF for a duplicated issue: ['pdf_path','pdf_name','pdf_size'] columns, or [] when there is nothing to copy.
     * @return array<string,mixed>
     */
    public static function copyColumns(array $row): array
    {
        if (!self::has($row)) {
            return [];
        }
        $new = Uploader::copyPrivate((string) $row['pdf_path']);
        if ($new === null) {
            return [];
        }
        return ['pdf_path' => $new, 'pdf_name' => $row['pdf_name'], 'pdf_size' => (int) $row['pdf_size'], 'pdf_updated_at' => date('Y-m-d H:i:s')];
    }

    /**
     * Stream the PDF. inline = shown in the browser (the embedded viewer, "read in a new tab"); false = "Download PDF" (attachment).
     * Headers: application/pdf, nosniff, Cache-Control private no-store (so unpublishing takes effect at once and nothing is kept by a shared cache).
     * The CALLER must have checked that the visitor may see this issue.
     *
     * @throws HttpException 404 when the issue has no PDF or the file is gone
     */
    public static function response(array $row, bool $inline): Response
    {
        if (!self::has($row)) {
            throw new HttpException(404);
        }
        return Uploader::download((string) $row['pdf_path'], self::downloadName($row), $inline);
    }

    /** +1 on the download counter (race-safe single UPDATE). Never throws. */
    public static function recordDownload(int $id): void
    {
        try {
            Database::execute('UPDATE newsletters SET pdf_downloads = pdf_downloads + 1, updated_at = updated_at WHERE id = ?', [$id]);
        } catch (\Throwable) {
        }
    }

    /** File name a visitor's browser saves: the original name the admin uploaded, else "<slug>.pdf". Always ends in .pdf. */
    public static function downloadName(array $row): string
    {
        $name = trim((string) ($row['pdf_name'] ?? ''));
        if ($name === '') {
            $name = (string) ($row['slug'] ?? 'newsletter');
        }
        return preg_match('/\.pdf$/i', $name) === 1 ? $name : $name . '.pdf';
    }

    /**
     * Public addresses of the PDF routes for an issue (relative to the site; pass $absolute for e-mail / JSON).
     * @return array{download:string,view:string}
     */
    public static function urls(array $row, bool $absolute = false): array
    {
        $slug = (string) ($row['slug'] ?? '');
        return [
            'download' => url('newsletter/' . $slug . '/download', [], $absolute),
            'view'     => url('newsletter/' . $slug . '/pdf', [], $absolute),
        ];
    }

    /**
     * What views and the API need about an issue's PDF.
     * @return array{has:bool,exists:bool,name:string,filename:string,size:int,size_human:string,downloads:int,updated_at:string,download_url:string,view_url:string}
     */
    public static function info(array $row, bool $absolute = false): array
    {
        $has = self::has($row);
        $u = self::urls($row, $absolute);
        return [
            'has'          => $has,
            'exists'       => $has && self::exists($row),
            'name'         => $has ? (string) ($row['pdf_name'] ?? '') : '',
            'filename'     => $has ? self::downloadName($row) : '',
            'size'         => $has ? (int) ($row['pdf_size'] ?? 0) : 0,
            'size_human'   => $has ? human_filesize((int) ($row['pdf_size'] ?? 0)) : '',
            'downloads'    => $has ? (int) ($row['pdf_downloads'] ?? 0) : 0,
            'updated_at'   => (string) ($row['pdf_updated_at'] ?? ''),
            'download_url' => $has ? $u['download'] : '',
            'view_url'     => $has ? $u['view'] : '',
        ];
    }
}
