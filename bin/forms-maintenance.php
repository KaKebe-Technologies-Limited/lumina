<?php
/**
 * FORMS maintenance (CLI only): retention and file clean-up. Schedule it once a day from the hosting panel (cPanel / hPanel "Cron jobs"):
 *
 *     /usr/bin/php /home/USER/public_html/bin/forms-maintenance.php
 *
 * What it does (contract: docs/FORMS.md section 13):
 *   1. RETENTION   deletes the submissions (and their uploaded files) of every form whose "Delete submissions after N days" is set and has passed.
 *   2. ORPHANS     deletes files in storage/private/forms that no form_files row points to and that are older than one day (left by a crash between the
 *                  upload and the database insert).
 * The admin screens also run the retention step lazily (at most every 6 hours), so the cron job is a safety net for sites nobody opens for days.
 *
 *     php bin/forms-maintenance.php             do it
 *     php bin/forms-maintenance.php --dry-run   only count (nothing is deleted)
 *     php bin/forms-maintenance.php --quiet     print nothing unless something was deleted (for cron mail)
 *
 * Output has counts only: never names, addresses or answers. Exit code 0 on success, 1 on a database error.
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Database;
use App\Core\Log;
use App\Services\Forms\FormRetention;
use App\Services\Forms\FormService;
use App\Services\Forms\FormSettings;

$dry = in_array('--dry-run', $argv, true);
$quiet = in_array('--quiet', $argv, true);
$say = static function (string $m) use ($quiet): void {
    if (!$quiet) {
        fwrite(STDOUT, $m . PHP_EOL);
    }
};

try {
    if (!FormService::available()) {
        $say('The forms tables are not imported yet: nothing to do.');
        exit(0);
    }
    $deleted = 0;
    $forms = 0;
    if ($dry) {
        $now = FormService::now();
        foreach (Database::all("SELECT id, settings FROM forms WHERE settings IS NOT NULL AND settings LIKE '%retention_days%'") as $row) {
            $days = (int) (FormSettings::decode((string) $row['settings'])['retention_days'] ?? 0);
            if ($days < 1) {
                continue;
            }
            $forms++;
            $deleted += (int) Database::value('SELECT COUNT(*) FROM form_submissions WHERE form_id = ? AND created_at < ?', [(int) $row['id'], $now->modify('-' . $days . ' days')->format('Y-m-d H:i:s')], 0);
        }
        $orphans = FormRetention::orphanFiles(false);
        $say("Dry run: {$deleted} submission(s) of {$forms} form(s) with a retention period are past it; " . count($orphans) . ' orphan file(s).');
        exit(0);
    }
    $r = FormRetention::run();
    $orphans = FormRetention::orphanFiles(true);
    $msg = "Forms maintenance: {$r['deleted']} submission(s) deleted from {$r['forms']} form(s) with a retention period; " . count($orphans) . ' orphan file(s) removed.';
    if ($r['deleted'] > 0 || $orphans !== []) {
        fwrite(STDOUT, $msg . PHP_EOL);
        Log::info($msg);
    } else {
        $say($msg);
    }
    @touch(STORAGE_PATH . '/cache/forms-retention.stamp');
    exit(0);
} catch (\Throwable $e) {
    fwrite(STDERR, 'Forms maintenance failed: ' . get_class($e) . PHP_EOL);
    Log::error('Forms maintenance failed', ['error' => get_class($e), 'message' => $e->getMessage()]);
    exit(1);
}
