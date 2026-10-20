<?php
/**
 * Child process of bin/selftest-membership-card.php (things that must be safe when two requests arrive at the same instant). CLI only, local environment only.
 *   php bin/selftest-membership-card-child.php <approve|issue|number> <member id> <start at (unix time with decimals)>
 * Waits for the common start time, does the work and prints one JSON line.
 *   approve  MembershipService::changeStatus(id, 'approved')            -> {"ok", "error", "number"}
 *   issue    CardService::issue(id, [source auto])                      -> {"ok", "error", "issued", "already", "version", "emailed"}
 *   number   MembershipService::assignMembershipNumber(id, year 2031)    -> {"ok", "number"}
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    exit('CLI only.');
}
require dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Config;
use App\Services\Membership\CardService;
use App\Services\Membership\MembershipService as MS;

if (Config::get('app.env') !== 'local') {
    exit("Refusing to run outside APP_ENV=local.\n");
}
$what = (string) ($argv[1] ?? '');
$id = (int) ($argv[2] ?? 0);
$startAt = (float) ($argv[3] ?? 0);
while (microtime(true) < $startAt) {
    usleep(500);
}
try {
    if ($what === 'approve') {
        $res = MS::changeStatus($id, 'approved');
        echo json_encode(['ok' => $res['ok'], 'error' => $res['error'], 'number' => $res['member']['membership_number'] ?? null]) . PHP_EOL;
    } elseif ($what === 'issue') {
        $res = CardService::issue($id, ['source' => 'auto:selftest']);
        echo json_encode(['ok' => $res['ok'], 'error' => $res['error'], 'issued' => $res['issued'], 'already' => $res['already'], 'version' => $res['version'], 'emailed' => $res['emailed']]) . PHP_EOL;
    } elseif ($what === 'number') {
        echo json_encode(['ok' => true, 'number' => MS::assignMembershipNumber($id, 2031)]) . PHP_EOL;
    } else {
        echo json_encode(['ok' => false, 'error' => 'unknown job']) . PHP_EOL;
    }
} catch (\Throwable $e) {
    echo json_encode(['ok' => false, 'error' => $e::class . ': ' . $e->getMessage()]) . PHP_EOL;
}
