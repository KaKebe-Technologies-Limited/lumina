<?php
/**
 * OPTIONAL CRON JOB: keeps newsletter e-mail campaigns moving when nobody has the admin page open.
 *
 *   php /path/to/site/bin/newsletter-send.php [--verbose] [--max-seconds=240]
 *
 * What it does: finds every issue whose e-mail campaign is "queued" or "sending" (never "paused" or "cancelled") and sends the next batches
 * (batch size = Admin > Site settings > Newsletter > "E-mails per batch") until the campaign is finished or the time budget is used up. It uses the
 * SAME code and the SAME database lock as the admin page, so running it while someone is sending from the admin page is harmless: only one of them
 * sends at a time, the other one just exits. An address is never e-mailed twice. Unsubscribed people are skipped.
 *
 * It does NOT start campaigns (only an administrator can, from the issue's E-mail panel) and cannot be started from the web: it refuses to run
 * outside the command line, and it needs no token because it has no web address.
 *
 * Hostinger (hPanel > Advanced > Cron Jobs), every 5 minutes (choose "Every 5 minutes" in the form, or write the schedule as 0-59/5 * * * *):
 *     0-59/5 * * * *  /usr/bin/php /home/ACCOUNT/domains/DOMAIN/public_html/bin/newsletter-send.php
 * (use the PHP path shown in hPanel for your PHP version). Requirements: MAIL_DRIVER=smtp and the MAIL_* settings in the site's .env, and APP_URL
 * set to the public address of the site (the links and pictures in the e-mails are built from it).
 *
 * Output: nothing unless --verbose (a one-line summary with counts only, never an e-mail address) or something is wrong (message on the error
 * stream, exit code 1). --max-seconds keeps the run below the limit of the host (default 240, between 10 and 3000).
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Config;
use App\Core\Database;
use App\Core\Log;
use App\Services\Newsletter\NewsletterMailer;

$verbose = false;
$maxSeconds = 240;
foreach (array_slice($argv, 1) as $arg) {
    if ($arg === '--verbose' || $arg === '-v') {
        $verbose = true;
    } elseif (preg_match('/^--max-seconds=(\d{1,5})$/', $arg, $m) === 1) {
        $maxSeconds = max(10, min(3000, (int) $m[1]));
    } elseif ($arg === '--help' || $arg === '-h') {
        echo "Usage: php bin/newsletter-send.php [--verbose] [--max-seconds=240]\nContinues queued / sending newsletter e-mail campaigns. See the comment at the top of this file.\n";
        exit(0);
    } else {
        fwrite(STDERR, "Unknown option. Use --help.\n");
        exit(1);
    }
}

if (Config::get('app.env') !== 'local' && trim((string) Config::get('app.url', '')) === '') {
    fwrite(STDERR, "APP_URL is not set in the .env file. The links and pictures in the e-mails are built from it, so nothing was sent.\n");
    exit(1);
}

try {
    $due = (int) Database::value("SELECT COUNT(*) FROM newsletters WHERE email_status IN ('queued', 'sending')", [], 0);
    if ($due === 0) {
        if ($verbose) {
            echo "No newsletter campaign to continue.\n";
        }
        exit(0);
    }
    $sum = NewsletterMailer::runDue($maxSeconds);
    if ($verbose) {
        printf(
            "campaigns=%d processed=%d sent=%d failed=%d skipped=%d%s%s\n",
            $sum['campaigns'], $sum['processed'], $sum['sent'], $sum['failed'], $sum['skipped'],
            $sum['busy'] ? ' (another sender is active, will continue on the next run)' : '',
            $sum['out_of_time'] ? ' (time budget used up, will continue on the next run)' : ''
        );
    }
    exit(0);
} catch (\Throwable $e) {
    $msg = NewsletterMailer::safeError($e->getMessage());
    Log::error('Newsletter cron failed: ' . $msg);
    fwrite(STDERR, 'Newsletter send failed: ' . $msg . "\n");
    exit(1);
}
