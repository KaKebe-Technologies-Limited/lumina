<?php
/**
 * CRON JOB: Bono AI learns the website again (docs/BONO.md). Run it once a day, at night:
 *
 *   php /path/to/site/bin/bono-learn.php [--force] [--verbose]
 *
 * Hostinger (hPanel > Advanced > Cron Jobs), every day at 02:30:
 *     30 2 * * *  /usr/bin/php /home/ACCOUNT/domains/DOMAIN/public_html/bin/bono-learn.php > /dev/null 2>&1
 * Pages that did not change cost nothing; changed pages are learned again; pages that left the sitemap are forgotten. --force re-reads every page.
 * Refuses to run outside the command line.
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require_once dirname(__DIR__) . '/app/bootstrap.php';

use App\Services\Bono\BonoIndexer;

try {
    $args = array_slice($argv, 1);
    $r = BonoIndexer::run(240, in_array('--force', $args, true));
    if (in_array('--verbose', $args, true)) {
        echo 'Bono learned: ' . $r['pages'] . ' pages read, ' . $r['updated'] . ' updated, ' . $r['passages'] . ' passages, ' . $r['removed'] . ' forgotten, ' . $r['errors'] . ' errors'
            . ($r['done'] ? '' : ', ' . $r['left'] . ' left for the next run') . ' (' . $r['seconds'] . 's)' . PHP_EOL;
    }
    exit(0);
} catch (\Throwable $e) {
    fwrite(STDERR, 'bono-learn failed: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
