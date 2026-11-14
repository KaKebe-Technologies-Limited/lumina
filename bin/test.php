<?php
declare(strict_types=1);

/**
 * TEST RUNNER: runs the bin/selftest-*.php scripts one after another, each in its own PHP process, and prints a summary.
 *
 *   php bin/test.php                  run everything (needs MySQL; some tests also need Apache, see docs/TESTING.md)
 *   php bin/test.php --list           list the tests and their groups, run nothing
 *   php bin/test.php core-units       run one test (name without "selftest-" and ".php"; part of a name also works; "master" = bin/selftest.php)
 *   php bin/test.php payments shop    run every test whose name contains "payments" or "shop"
 *   php bin/test.php --group=quick    quick = no database or web server; smoke = the critical flows (see FLOWS); full = the master script
 *   php bin/test.php --stop           stop at the first failing test
 *   php bin/test.php --verbose        show the output of passing tests too (failing tests always show their FAIL lines)
 *
 * Exit code 0 = everything passed, 1 = something failed. Tests refuse to run unless APP_ENV=local (they write temporary rows and clean up).
 */

if (PHP_SAPI !== 'cli') {
    exit('CLI only.');
}

/** group => tests that guard the money and sign-up paths. Keep this list short: it is what to run before every upload. */
const FLOWS = [
    'quick' => ['core-units'],
    'smoke' => ['core-units', 'payment-options', 'payments-dashboard', 'daraza', 'daraza-api', 'shop', 'membership-pay', 'academy', 'academy-payments', 'fund', 'forms', 'pesapal'],
    // the master script runs the older suites in one process: roles, membership, newsletter, calendar, meetings, cards, shop, social, academy... (20+ minutes)
    'full' => ['master'],
];

$root = dirname(__DIR__);
$args = array_slice($argv, 1);
$opts = ['list' => false, 'stop' => false, 'verbose' => false, 'group' => ''];
$names = [];
foreach ($args as $a) {
    if ($a === '--list') {
        $opts['list'] = true;
    } elseif ($a === '--stop') {
        $opts['stop'] = true;
    } elseif ($a === '--verbose' || $a === '-v') {
        $opts['verbose'] = true;
    } elseif (str_starts_with($a, '--group=')) {
        $opts['group'] = substr($a, 8);
    } elseif ($a === '--help' || $a === '-h') {
        echo substr((string) file_get_contents(__FILE__), 0, 1400), PHP_EOL;
        exit(0);
    } else {
        $names[] = preg_replace('/^selftest-|\.php$/', '', basename($a));
    }
}

$all = [];
foreach (glob($root . '/bin/selftest-*.php') ?: [] as $f) {
    if (!str_contains((string) file_get_contents($f), 'bootstrap.php')) {
        continue;          // a CHILD file: it is require-d by bin/selftest.php (the master) and cannot run alone
    }
    $all[preg_replace('/^selftest-|\.php$/', '', basename($f))] = $f;
}
ksort($all);
$all['master'] = $root . '/bin/selftest.php';   // only run on request (it repeats many of the others)

if ($opts['group'] !== '') {
    if (!isset(FLOWS[$opts['group']])) {
        fwrite(STDERR, 'Unknown group "' . $opts['group'] . '". Groups: ' . implode(', ', array_keys(FLOWS)) . PHP_EOL);
        exit(2);
    }
    $names = array_merge($names, FLOWS[$opts['group']]);
}

$run = [];
foreach ($all as $name => $file) {
    if ($names === []) {
        if ($name !== 'master') {
            $run[$name] = $file;
        }
        continue;
    }
    foreach ($names as $n) {
        if ($name === $n || (!in_array($n, array_merge(...array_values(FLOWS)), true) && str_contains($name, $n)) || ($opts['group'] !== '' && $name === $n)) {
            $run[$name] = $file;
        }
    }
}
if ($run === []) {
    fwrite(STDERR, 'No test matches: ' . implode(' ', $names) . '. Try --list.' . PHP_EOL);
    exit(2);
}

if ($opts['list']) {
    foreach ($run as $name => $file) {
        $in = array_keys(array_filter(FLOWS, static fn(array $t): bool => in_array($name, $t, true)));
        echo str_pad($name, 36), $in ? '[' . implode(', ', $in) . ']' : '', PHP_EOL;
    }
    echo PHP_EOL, count($run), ' test(s).', PHP_EOL;
    exit(0);
}

$php = PHP_BINARY;
$results = [];
$t0 = microtime(true);
foreach ($run as $name => $file) {
    $start = microtime(true);
    $proc = proc_open([$php, $file], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, $root);
    if (!is_resource($proc)) {
        $results[$name] = ['code' => 99, 'ok' => 0, 'fail' => 0, 'secs' => 0.0, 'out' => 'could not start PHP'];
        continue;
    }
    $out = stream_get_contents($pipes[1]) . stream_get_contents($pipes[2]);
    $code = proc_close($proc);
    $ok = preg_match_all('/^\s+ok\s/m', $out);
    $fail = preg_match_all('/^\s+FAIL\s/m', $out);
    $passed = $code === 0 && $fail === 0;
    $results[$name] = ['code' => $code, 'ok' => (int) $ok, 'fail' => (int) $fail, 'secs' => microtime(true) - $start, 'out' => $out];
    printf("%s %-36s %3d ok %s  %.1fs\n", $passed ? 'PASS' : 'FAIL', $name, $ok, $fail > 0 ? $fail . ' failed' : ($code !== 0 ? 'exit ' . $code : ''), $results[$name]['secs']);
    if (!$passed) {
        $lines = array_filter(explode("\n", $out), static fn(string $l): bool => preg_match('/FAIL|Refusing|Fatal|Warning|Exception|Error/i', $l) === 1);
        foreach (array_slice($lines, 0, 12) as $l) {
            echo '       ', rtrim($l), PHP_EOL;
        }
        if ($lines === []) {
            echo '       ', trim(substr($out, -300)), PHP_EOL;
        }
    } elseif ($opts['verbose']) {
        echo $out;
    }
    if (!$passed && $opts['stop']) {
        break;
    }
}

$bad = array_filter($results, static fn(array $r): bool => $r['code'] !== 0 || $r['fail'] > 0);
printf("\n%d test file(s), %d checks passed, %d failing file(s), %.0fs\n", count($results), array_sum(array_column($results, 'ok')), count($bad), microtime(true) - $t0);
if ($bad) {
    echo 'Failing: ', implode(', ', array_keys($bad)), PHP_EOL;
}
exit($bad ? 1 : 0);
