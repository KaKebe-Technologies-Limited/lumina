<?php
/**
 * Child process of bin/selftest-membership.php (parallel approvals). CLI only, local environment only.
 *   php bin/selftest-membership-child.php <member id> <start at (unix time with decimals)>
 * Waits for the common start time, approves the member and prints one JSON line: {"ok":bool,"error":?string,"number":?string}.
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    exit('CLI only.');
}
require dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Config;
use App\Services\Membership\MembershipService as MS;

if (Config::get('app.env') !== 'local') {
    exit("Refusing to run outside APP_ENV=local.\n");
}
$id = (int) ($argv[1] ?? 0);
$startAt = (float) ($argv[2] ?? 0);
while (microtime(true) < $startAt) {
    usleep(500);
}
try {
    $res = MS::changeStatus($id, 'approved');
    echo json_encode(['ok' => $res['ok'], 'error' => $res['error'], 'number' => $res['member']['membership_number'] ?? null]) . PHP_EOL;
} catch (\Throwable $e) {
    echo json_encode(['ok' => false, 'error' => $e::class . ': ' . $e->getMessage(), 'number' => null]) . PHP_EOL;
}
