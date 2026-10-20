<?php
/**
 * CRON JOB: posts the social-media posts that are still waiting and retries the ones that failed for a passing reason.
 *
 *   php /path/to/site/bin/social-dispatch.php [--verbose] [--max-seconds=240] [--max=20]
 *
 * What it does: the moment a newsletter issue is published the website tries to post it to the switched-on channels (Facebook, Instagram, X, LinkedIn, Telegram, webhook) right
 * after the page was sent. A post that did not go through (the platform was busy, the connection dropped before the request was sent, a rate limit) stays in the queue and is
 * retried here with growing pauses (1 min, 5 min, 15 min, 1 h, 3 h; after the fifth try it is marked "failed" for a person to look at). The admin pages also retry once a minute
 * while somebody is working in the admin, so this job only matters when nobody is. It never posts anything twice: every attempt is recorded before the platform is called, and an
 * unclear outcome (a time-out after the request was sent) is NOT retried by itself, it waits in the Share log for an administrator ("needs review").
 *
 * It does NOT start sharing an issue (that happens when the issue is published, or from the issue's share panel) and cannot be started from the web: it refuses to run outside the
 * command line. It also sends the ONE-time e-mails "a channel needs a new access token" and "an access token expires soon".
 *
 * Hostinger (hPanel > Advanced > Cron Jobs), every 5 minutes:
 *     0-59/5 * * * *  /usr/bin/php /home/ACCOUNT/domains/DOMAIN/public_html/bin/social-dispatch.php > /dev/null 2>&1
 * (use the PHP path shown in hPanel). Requirements: APP_URL set in the site's .env (the links and share pictures are built from it).
 *
 * Output: nothing unless --verbose (a one-line summary with counts only, never a caption, token or address) or something is wrong (message on the error stream, exit code 1).
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
use App\Services\Social\SocialConfig;
use App\Services\Social\SocialDispatcher;

$verbose = false;
$maxSeconds = 240;
$max = 20;
foreach (array_slice($argv, 1) as $arg) {
    if ($arg === '--verbose' || $arg === '-v') {
        $verbose = true;
    } elseif (preg_match('/^--max-seconds=(\d{1,5})$/', $arg, $m) === 1) {
        $maxSeconds = max(10, min(3000, (int) $m[1]));
    } elseif (preg_match('/^--max=(\d{1,3})$/', $arg, $m) === 1) {
        $max = max(1, min(200, (int) $m[1]));
    } elseif ($arg === '--help' || $arg === '-h') {
        echo "Usage: php bin/social-dispatch.php [--verbose] [--max-seconds=240] [--max=20]\nPosts waiting social-media posts and retries failed ones. See the comment at the top of this file.\n";
        exit(0);
    } else {
        fwrite(STDERR, "Unknown option. Use --help.\n");
        exit(1);
    }
}

if (Config::get('app.env') !== 'local' && trim((string) Config::get('app.url', '')) === '') {
    fwrite(STDERR, "APP_URL is not set in the .env file. The links and pictures in the posts are built from it, so nothing was posted.\n");
    exit(1);
}

if (trim((string) Config::get('app.url', '')) === '') {
    // local development only (production exited above): from the command line the site address is unknown, so links and picture addresses would lack the /lumina folder
    fwrite(STDERR, "Note: APP_URL is empty, so links and picture addresses in these posts are built as http://localhost/... (set APP_URL=http://localhost/lumina in .env to test the cron job locally).\n");
}

try {
    if (!Database::tableExists('social_posts')) {
        if ($verbose) {
            echo "social: the social_posts table does not exist yet (import database/lumina-live-import.sql); nothing to do.\n";
        }
        exit(0);
    }
    if (!SocialConfig::globalOn()) {
        if ($verbose) {
            echo "social: auto-share is switched off; nothing posted.\n";
        }
        exit(0);
    }
    $r = SocialDispatcher::runDue($max, (float) $maxSeconds);
    if ($verbose) {
        echo $r['busy']
            ? "social: another run is in progress; skipped.\n"
            : sprintf("social: attempted %d, posted %d, failed %d, waiting %d, needs review %d, skipped %d, deferred %d, reclaimed %d.\n", $r['attempted'], $r['posted'], $r['failed'], $r['queued'], $r['review'], $r['skipped'], $r['deferred'], $r['reclaimed']);
    }
    exit(0);
} catch (Throwable $e) {
    Log::error('social-dispatch failed: ' . $e->getMessage());
    fwrite(STDERR, 'social-dispatch failed: ' . get_class($e) . "\n");
    exit(1);
}
