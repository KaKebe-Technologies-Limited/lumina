<?php
/**
 * CRON JOB for the meeting scheduler: sends the meeting reminders that are due ("24 hours" and "1 hour" before a meeting) and finishes interrupted sendings
 * (invitations, updates and cancellations that are still waiting in a meeting's queue).
 *
 *   php /path/to/site/bin/meetings-send.php [--verbose] [--max-seconds=240]
 *
 * What it does, in order: (1) marks meetings whose end time has passed as "completed"; (2) sends every reminder that is due now to people who were invited at least
 * that long before the meeting, are still on the list and have not declined, never twice; (3) goes through every meeting that has queued messages (and is not
 * paused) and sends the next batches until the queues are empty or the time budget is used up. It uses the SAME code and the SAME database lock as the "Send"
 * button on the meeting page, so running it while somebody is sending from the admin is harmless: only one of them sends at a time, the other one just exits.
 * A person is never e-mailed the same message twice.
 *
 * It does NOT start sendings (only an administrator or editor does that from the meeting page) and cannot be started from the web: it refuses to run outside the
 * command line, and it needs no token because it has no web address.
 *
 * Hostinger (hPanel > Advanced > Cron Jobs), every 5 minutes (choose "Every 5 minutes" in the form, or write the schedule as 0-59/5 * * * *):
 *     0-59/5 * * * *  /usr/bin/php /home/ACCOUNT/domains/DOMAIN/public_html/bin/meetings-send.php
 * (use the PHP path shown in hPanel for your PHP version). Requirements: MAIL_DRIVER=smtp and the MAIL_* settings in the site's .env, and APP_URL set to the public
 * address of the site (the buttons and links in the e-mails are built from it). Without the cron job invitations still go out from the meeting page, but reminders
 * are only sent when somebody presses "Send reminder now".
 *
 * Output: nothing unless --verbose (a one-line summary with counts only, never an e-mail address) or something is wrong (message on the error stream, exit code 1).
 * --max-seconds keeps the run below the limit of the host (default 240, between 10 and 3000).
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Config;
use App\Core\Log;
use App\Services\Meetings\MeetingMailer;
use App\Services\Meetings\MeetingService;

$verbose = false;
$maxSeconds = 240;
foreach (array_slice($argv, 1) as $arg) {
    if ($arg === '--verbose' || $arg === '-v') {
        $verbose = true;
    } elseif (preg_match('/^--max-seconds=(\d{1,5})$/', $arg, $m) === 1) {
        $maxSeconds = max(10, min(3000, (int) $m[1]));
    } elseif ($arg === '--help' || $arg === '-h') {
        echo "Usage: php bin/meetings-send.php [--verbose] [--max-seconds=240]\nSends due meeting reminders and finishes queued meeting e-mails. See the comment at the top of this file.\n";
        exit(0);
    } else {
        fwrite(STDERR, "Unknown option. Use --help.\n");
        exit(1);
    }
}

if (Config::get('app.env') !== 'local' && trim((string) Config::get('app.url', '')) === '') {
    fwrite(STDERR, "APP_URL is not set in the .env file. The buttons and links in the e-mails are built from it, so nothing was sent.\n");
    exit(1);
}

try {
    if (!MeetingService::available()) {
        if ($verbose) {
            echo "The meeting tables are not there yet (import database/lumina-live-import.sql first).\n";
        }
        exit(0);
    }
    $sum = MeetingMailer::runDue($maxSeconds);
    if ($verbose) {
        printf(
            "meetings=%d processed=%d sent=%d failed=%d reminders_sent=%d reminders_failed=%d completed=%d%s%s%s\n",
            $sum['meetings'], $sum['processed'], $sum['sent'], $sum['failed'], $sum['reminders_sent'], $sum['reminders_failed'], $sum['completed'],
            $sum['halted'] > 0 ? ' (' . $sum['halted'] . ' queue(s) paused because of a mail server problem)' : '',
            $sum['busy'] ? ' (another sender is active, will continue on the next run)' : '',
            $sum['out_of_time'] ? ' (time budget used up, will continue on the next run)' : ''
        );
    }
    exit(0);
} catch (\Throwable $e) {
    $msg = MeetingMailer::safeError($e->getMessage());
    Log::error('Meetings cron failed: ' . $msg);
    fwrite(STDERR, 'Meetings send failed: ' . $msg . "\n");
    exit(1);
}
