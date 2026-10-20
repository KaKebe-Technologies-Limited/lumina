<?php
/**
 * CRON JOB: reads the opportunity sources (Admin > Opportunities > Sources) and adds what is new to the Opportunities page.
 *
 *   php /path/to/site/bin/opportunities-fetch.php [--force] [--verbose] [--max-seconds=120]
 *
 * The website already does this by itself after a visitor's page was sent, when a source is due (every 12 hours by default, Settings > Opportunities).
 * This job only matters for a quiet website: it keeps the list fresh even when nobody visits. Without --force it reads only the sources that are due,
 * so it can run often. With --force every active source is read now. It also removes old items that were read from other websites (closed for two weeks,
 * or without a closing date and older than the keep time); items the team added are never removed. Refuses to run outside the command line.
 *
 * Hostinger (hPanel > Advanced > Cron Jobs), for example every 6 hours:
 *     0 0,6,12,18 * * *  /usr/bin/php /home/ACCOUNT/domains/DOMAIN/public_html/bin/opportunities-fetch.php > /dev/null 2>&1
 * (use the PHP path shown in hPanel). Output: nothing unless --verbose (counts only) or something is wrong (message on the error stream, exit code 1).
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once dirname(__DIR__) . '/app/bootstrap.php';

use App\Services\Opportunities\OpportunityImporter;

$force = false;
$verbose = false;
$max = 120;
foreach (array_slice($argv, 1) as $arg) {
    if ($arg === '--force') {
        $force = true;
    } elseif ($arg === '--verbose') {
        $verbose = true;
    } elseif (str_starts_with($arg, '--max-seconds=')) {
        $max = max(10, min(600, (int) substr($arg, 14)));
    }
}

try {
    if (!$force && !OpportunityImporter::enabled()) {
        if ($verbose) {
            fwrite(STDOUT, "Reading from other websites is switched off (Settings > Opportunities).\n");
        }
        exit(0);
    }
    $r = OpportunityImporter::refreshDue($force, $max);
    if ($verbose) {
        fwrite(STDOUT, 'Sources read: ' . $r['feeds'] . ', new opportunities: ' . $r['new'] . ', sources with a problem: ' . $r['errors'] . "\n");
    }
    exit(0);
} catch (\Throwable $e) {
    fwrite(STDERR, 'opportunities-fetch failed: ' . $e->getMessage() . "\n");
    exit(1);
}
