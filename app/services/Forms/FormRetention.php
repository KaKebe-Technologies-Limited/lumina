<?php
declare(strict_types=1);

namespace App\Services\Forms;

use App\Core\AuditLog;
use App\Core\Database;

/**
 * RETENTION: forms with settings.retention_days > 0 delete their submissions (and uploaded files) once they are older than that many days.
 * Runs (a) lazily, at most every 6 hours, when an admin opens the forms or submissions screens (FormRetention::lazy()), and (b) from the cron
 * script bin/forms-maintenance.php. Deletion is permanent. Counts only are returned / logged. Contract: docs/FORMS.md section 13.
 *
 * Uganda's Data Protection and Privacy Act 2019 asks that personal data is kept no longer than needed. The right period depends on the purpose and
 * the lawful basis of each form: the owner (with legal advice) decides; the module only enforces the number they set.
 */
final class FormRetention
{
    public const LAZY_INTERVAL = 21600;          // 6 hours

    /**
     * Delete expired submissions.
     * @param ?int $formId limit to one form (default: every form with a retention period)
     * @return array{forms:int,deleted:int}
     */
    public static function run(?int $formId = null, ?\DateTimeImmutable $now = null): array
    {
        $now ??= FormService::now();
        $sql = "SELECT id, settings FROM forms WHERE settings IS NOT NULL AND settings LIKE '%retention_days%'";
        $params = [];
        if ($formId !== null) {
            $sql .= ' AND id = ?';
            $params[] = $formId;
        }
        $forms = 0;
        $deleted = 0;
        foreach (Database::all($sql, $params) as $row) {
            $cfg = FormSettings::decode((string) $row['settings']);
            $days = (int) ($cfg['retention_days'] ?? 0);
            if ($days < 1) {
                continue;
            }
            $forms++;
            $cutoff = $now->modify('-' . $days . ' days')->format('Y-m-d H:i:s');
            $formDeleted = 0;
            for ($guard = 0; $guard < 200; $guard++) {
                $ids = array_map('intval', array_column(Database::all('SELECT id FROM form_submissions WHERE form_id = ? AND created_at < ? ORDER BY id ASC LIMIT 200', [(int) $row['id'], $cutoff]), 'id'));
                if ($ids === []) {
                    break;
                }
                $formDeleted += SubmissionService::deleteMany((int) $row['id'], $ids);
            }
            $deleted += $formDeleted;
            if ($formDeleted > 0) {
                AuditLog::record('form.retention', 'form', (int) $row['id'], 'Retention removed old submissions', ['deleted' => $formDeleted, 'days' => $days]);
            }
        }
        return ['forms' => $forms, 'deleted' => $deleted];
    }

    /** Run at most every LAZY_INTERVAL seconds (a stamp file in storage/cache). Never throws. */
    public static function lazy(): void
    {
        try {
            $dir = STORAGE_PATH . '/cache';
            $stamp = $dir . '/forms-retention.stamp';
            if (is_file($stamp) && (time() - (int) filemtime($stamp)) < self::LAZY_INTERVAL) {
                return;
            }
            if (!is_dir($dir)) {
                @mkdir($dir, 0775, true);
            }
            @touch($stamp);                                     // touch first: two admins at once do not both run it
            self::run();
        } catch (\Throwable) {
        }
    }

    /**
     * Files in storage/private/forms that no form_files row points to and that are older than a day (left behind by a crash between the upload and
     * the database insert). @return list<string> relative paths; deleted when $delete is true.
     */
    public static function orphanFiles(bool $delete = false, int $minAgeSeconds = 86400): array
    {
        $root = STORAGE_PATH . '/private/' . FormFileStore::FOLDER;
        if (!is_dir($root)) {
            return [];
        }
        $known = array_flip(array_column(Database::all('SELECT stored_path FROM form_files'), 'stored_path'));
        $out = [];
        $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS));
        foreach ($it as $file) {
            if (!$file->isFile()) {
                continue;
            }
            $rel = FormFileStore::FOLDER . '/' . ltrim(str_replace('\\', '/', substr($file->getPathname(), strlen($root))), '/');
            if (isset($known[$rel]) || !FormFileStore::validPath($rel) || (time() - $file->getMTime()) < $minAgeSeconds) {
                continue;
            }
            $out[] = $rel;
            if ($delete) {
                FormFileStore::delete($rel);
            }
        }
        return $out;
    }
}
