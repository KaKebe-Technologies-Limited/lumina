<?php
declare(strict_types=1);

/**
 * The words an administrator reads when the mail server refuses the SMTP login (Gmail answers 454 "too many login attempts", 534 "app password required", 535 "not accepted"):
 * what the server said (scrubbed) and what to do about it, never the password. A tiny fake mail server on 127.0.0.1 answers the login with the code under test.
 * Included by bin/selftest.php or run alone:   /c/xampp/php/php.exe bin/selftest-smtp-errors.php
 */

use App\Core\Config;
use App\Core\Mailer;

if (!function_exists('check')) {
    if (PHP_SAPI !== 'cli') {
        exit('CLI only.');
    }
    require dirname(__DIR__) . '/app/bootstrap.php';
    if (Config::get('app.env') !== 'local') {
        exit('Refusing to run outside APP_ENV=local.' . PHP_EOL);
    }
    $fail = 0;
    function check(string $name, bool $ok, string $extra = ''): void
    {
        global $fail;
        echo ($ok ? '  ok   ' : '  FAIL ') . $name . ($ok || $extra === '' ? '' : "  -> {$extra}") . PHP_EOL;
        if (!$ok) {
            $fail++;
        }
    }
    $standalone = true;
}

echo "-- smtp: login refusal messages --\n";
$serverCode = <<<'PHP'
$port = (int) $argv[1];
$reply = $argv[2];
$srv = stream_socket_server('tcp://127.0.0.1:' . $port, $en, $es);
if (!$srv) { exit(1); }
stream_set_blocking($srv, true);
$deadline = time() + 25;
while (time() < $deadline) {
    $c = @stream_socket_accept($srv, 5);
    if (!$c) { continue; }
    fwrite($c, "220 fake ready\r\n");
    while (($line = fgets($c)) !== false) {
        $u = strtoupper(trim($line));
        if (str_starts_with($u, 'EHLO') || str_starts_with($u, 'HELO')) {
            fwrite($c, "250-fake\r\n250 AUTH PLAIN LOGIN\r\n");
        } elseif (str_starts_with($u, 'AUTH PLAIN')) {
            fwrite($c, $reply . "\r\n");
        } elseif (str_starts_with($u, 'AUTH LOGIN')) {
            fwrite($c, "334 VXNlcm5hbWU6\r\n");
            fgets($c);
            fwrite($c, "334 UGFzc3dvcmQ6\r\n");
            fgets($c);
            fwrite($c, $reply . "\r\n");
        } elseif (str_starts_with($u, 'QUIT')) {
            fwrite($c, "221 bye\r\n");
            break;
        }
    }
    fclose($c);
}
PHP;

$secret = 'S3cret-App-Pass-9999';
$run = static function (string $reply) use ($serverCode, $secret): array {
    $port = random_int(20000, 40000);
    $proc = proc_open([PHP_BINARY, '-r', $serverCode, (string) $port, $reply], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
    usleep(600000);
    $old = [Config::get('mail.driver'), Config::get('mail.smtp')];
    Config::set('mail.driver', 'smtp');
    Config::set('mail.smtp', ['host' => '127.0.0.1', 'port' => $port, 'username' => 'someone@gmail.com', 'password' => $secret, 'encryption' => 'none', 'timeout' => 4, 'cafile' => '']);
    Mailer::closeConnection();
    $r = Mailer::attempt('zz@example.test', 'x', 'y');
    Mailer::closeConnection();
    Config::set('mail.driver', $old[0]);
    Config::set('mail.smtp', $old[1]);
    if (is_resource($proc)) {
        proc_terminate($proc);
        proc_close($proc);
    }
    return $r;
};

$r454 = $run('454 4.7.0 Too many login attempts, please try again later. for someone@gmail.com');
check('454: says what the server said and that it is usually temporary, with the unlock link', !$r454['ok'] && str_contains((string) $r454['error'], '454') && str_contains((string) $r454['error'], 'Too many login attempts') && str_contains((string) $r454['error'], 'usually temporary') && str_contains((string) $r454['error'], 'DisplayUnlockCaptcha'), (string) $r454['error']);
check('454: it is a temporary failure (tried again later), not a permanent one', $r454['transient'] === true);
check('454: the address and the password never appear in the message', !str_contains((string) $r454['error'], 'someone@gmail.com') && !str_contains((string) $r454['error'], $secret));
$r535 = $run('535 5.7.8 Username and Password not accepted.');
check('535: tells the administrator to use the full address and the App Password', !$r535['ok'] && str_contains((string) $r535['error'], '535') && str_contains((string) $r535['error'], 'App Password') && str_contains((string) $r535['error'], 'MAIL_USERNAME'), (string) $r535['error']);
$r534 = $run('534 5.7.9 Application-specific password required.');
check('534: tells the administrator an App Password is needed', !$r534['ok'] && str_contains((string) $r534['error'], '534') && str_contains((string) $r534['error'], '2-Step Verification'), (string) $r534['error']);

if (!empty($standalone)) {
    echo PHP_EOL . ($fail === 0 ? 'ALL OK' : $fail . ' CHECK(S) FAILED') . PHP_EOL;
    exit($fail === 0 ? 0 : 1);
}
