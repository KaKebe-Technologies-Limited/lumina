<?php
/**
 * CRON JOB: e-mails learners a reminder before a virtual class of the LAF Academy (Settings > LAF Academy > "Remind learners ... before").
 *
 *   php /path/to/site/bin/academy-reminders.php [--verbose]
 *
 * Each class is reminded once (academy_classes.reminder_sent_at). Run it often, for example every 15 minutes. Hostinger (hPanel > Advanced > Cron Jobs):
 *     0,15,30,45 * * * *  /usr/bin/php /home/ACCOUNT/domains/DOMAIN/public_html/bin/academy-reminders.php > /dev/null 2>&1
 * Output: nothing unless --verbose (counts only). Refuses to run outside the command line.
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require_once dirname(__DIR__) . '/app/bootstrap.php';

use App\Services\Academy\ClassService;

try {
    $n = ClassService::sendDueReminders();
    ClassService::settle();
    if (in_array('--verbose', array_slice($argv, 1), true)) {
        echo 'Classes reminded: ' . $n . PHP_EOL;
    }
    exit(0);
} catch (\Throwable $e) {
    fwrite(STDERR, 'academy-reminders failed: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
