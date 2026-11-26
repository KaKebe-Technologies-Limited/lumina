<?php
declare(strict_types=1);

/**
 * Cache-header check for the private areas (the same check as Admin > Security > Cloudflare, from the command line).
 * Fetches key pages WITHOUT a cookie and says whether a shared cache such as Cloudflare would be allowed to store them.
 * Changes nothing and never talks to Cloudflare's own interfaces: it only requests the site's own pages.
 *
 *   php bin/check-cloudflare.php                      uses APP_URL (or http://localhost/<folder> when APP_ENV=local)
 *   php bin/check-cloudflare.php https://www.example  checks that address
 * Exit code 0 = no page is storable by a shared cache, 1 = at least one is.
 */

if (PHP_SAPI !== 'cli') {
    exit('CLI only.');
}
require dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Config;
use App\Services\Security\CloudflareStatus;

$base = $argv[1] ?? '';
if ($base === '') {
    $base = (string) Config::get('app.url', '');
}
if ($base === '' && Config::get('app.env') === 'local') {
    $base = 'http://localhost/' . basename(dirname(__DIR__));
}
if (!preg_match('~^https?://[A-Za-z0-9.\-:\[\]]+(/[A-Za-z0-9._\-/]*)?$~', $base)) {
    fwrite(STDERR, "Give the site address, e.g. php bin/check-cloudflare.php https://www.luminaafricafoundation.com\n");
    exit(2);
}
$rows = CloudflareStatus::runCacheCheck($base, false);
$bad = 0;
printf("%-22s %-8s %-34s %-10s %s\n", 'Page', 'Result', 'Cache-Control', 'CF cache', 'Note');
foreach ($rows as $r) {
    $bad += $r['status'] === 'bad' ? 1 : 0;
    printf("%-22s %-8s %-34s %-10s %s\n", $r['path'], strtoupper((string) $r['status']), mb_substr((string) $r['cache_control'] ?: '-', 0, 34), (string) $r['cf_cache'] ?: '-', $r['note']);
}
echo $bad === 0 ? "\nNo private page can be stored by a shared cache.\n" : "\n{$bad} page(s) could be stored. Fix before turning Cloudflare caching on.\n";
exit($bad === 0 ? 0 : 1);
