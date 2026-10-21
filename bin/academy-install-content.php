<?php
/**
 * Installs the LAF Academy STARTER COURSES (app/config/academy/courses/*.php) into the database.
 *
 *   php bin/academy-install-content.php [--draft] [--only=slug]
 *
 * Courses that already exist are never touched. Without --draft the installed courses are published. The same installer is behind
 * Admin > LAF Academy > Starter courses. Refuses to run outside the command line.
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require_once dirname(__DIR__) . '/app/bootstrap.php';

use App\Services\Academy\ContentInstaller;

$publish = true;
$only = null;
foreach (array_slice($argv, 1) as $a) {
    if ($a === '--draft') {
        $publish = false;
    } elseif (str_starts_with($a, '--only=')) {
        $only = substr($a, 7);
    }
}
if ($only !== null) {
    $r = ContentInstaller::install($only, null, $publish);
    fwrite($r['ok'] ? STDOUT : STDERR, $r['message'] . PHP_EOL);
    exit($r['ok'] ? 0 : 1);
}
$r = ContentInstaller::installAll(null, $publish);
echo 'Installed ' . $r['installed'] . ' course(s), ' . $r['skipped'] . ' already there.' . PHP_EOL;
foreach ($r['messages'] as $m) {
    fwrite(STDERR, $m . PHP_EOL);
}
exit($r['messages'] === [] ? 0 : 1);
