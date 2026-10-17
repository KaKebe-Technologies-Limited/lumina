<?php
/**
 * THROWAWAY LOCAL MOCK SMTP SERVER (development and tests only). Lets you try the whole newsletter sending flow without real credentials.
 *
 *   /c/xampp/php/php.exe bin/dev/mock-smtp.php [options]        (leave it running in its own terminal; Ctrl+C stops it)
 *
 *   --port=2525            port to listen on (always 127.0.0.1 - this script refuses to listen on anything else)
 *   --tls=none|starttls|implicit   offer STARTTLS, or require TLS from the first byte (needs a certificate, see --make-cert). Default: none
 *   --cert=PATH            PEM file with certificate + private key (default storage/mock-smtp/cert.pem)
 *   --make-cert            create a self-signed certificate for 127.0.0.1 / localhost in storage/mock-smtp/cert.pem and exit
 *                          (point the site at it with MAIL_CAFILE=storage/mock-smtp/cert.pem so certificate checks stay ON)
 *   --user=U --pass=P      require AUTH PLAIN / AUTH LOGIN with this login (otherwise any client may send)
 *   --max-size=BYTES       advertise SIZE and answer 552 above it (default 10485760)
 *   --out=DIR              where received messages are written (default storage/mock-smtp)
 *   --quiet                no console output
 *
 * Every accepted message is written to DIR/NNNN-<random>.eml, preceded by two X-Mock- headers with the SMTP envelope (MAIL FROM, RCPT TO).
 * Test behaviours triggered by the recipient's local part (before the @):
 *   reject5...  -> 550 (permanent rejection)      reject4... -> 451 (temporary failure)
 *   slow...     -> waits 8 s before answering RCPT (to test client timeouts)      dropconn... -> closes the connection at RCPT
 *
 * One connection at a time, no queue, nothing leaves this machine. Set in .env to use it:
 *   MAIL_DRIVER=smtp  MAIL_HOST=127.0.0.1  MAIL_PORT=2525  MAIL_ENCRYPTION=none   (add MAIL_USERNAME / MAIL_PASSWORD when you start it with --user/--pass)
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    exit('CLI only.');
}

$root = dirname(__DIR__, 2);
$opt = [];
foreach (array_slice($argv, 1) as $a) {
    if (preg_match('/^--([a-z-]+)(?:=(.*))?$/', $a, $m) === 1) {
        $opt[$m[1]] = $m[2] ?? '1';
    }
}
$port = (int) ($opt['port'] ?? 2525);
$tls = (string) ($opt['tls'] ?? 'none');
$out = (string) ($opt['out'] ?? $root . '/storage/mock-smtp');
$cert = (string) ($opt['cert'] ?? $root . '/storage/mock-smtp/cert.pem');
$user = (string) ($opt['user'] ?? '');
$pass = (string) ($opt['pass'] ?? '');
$maxSize = (int) ($opt['max-size'] ?? 10485760);
$quiet = isset($opt['quiet']);
if (!is_dir($out)) {
    mkdir($out, 0775, true);
}

if (isset($opt['make-cert'])) {
    exit(makeCert($cert) ? 0 : 1);
}
if (!in_array($tls, ['none', 'starttls', 'implicit'], true)) {
    fwrite(STDERR, "--tls must be none, starttls or implicit\n");
    exit(1);
}
if ($tls !== 'none' && !is_file($cert)) {
    fwrite(STDERR, "No certificate at {$cert}. Run with --make-cert first.\n");
    exit(1);
}

function say(string $m): void
{
    global $quiet;
    if (!$quiet) {
        fwrite(STDOUT, '[' . date('H:i:s') . '] ' . $m . PHP_EOL);
    }
}

function makeCert(string $file): bool
{
    $cnf = sys_get_temp_dir() . '/lumina-mock-smtp-' . bin2hex(random_bytes(3)) . '.cnf';
    file_put_contents($cnf, "[req]\ndistinguished_name=dn\nx509_extensions=v3\nprompt=no\n[dn]\nCN=localhost\n[v3]\nsubjectAltName=DNS:localhost,IP:127.0.0.1\nbasicConstraints=CA:TRUE\n");
    $cfg = ['config' => $cnf, 'x509_extensions' => 'v3', 'req_extensions' => 'v3', 'private_key_bits' => 2048, 'digest_alg' => 'sha256'];
    $key = openssl_pkey_new($cfg);
    $csr = $key ? openssl_csr_new(['commonName' => 'localhost'], $key, $cfg) : false;
    $x509 = $csr ? openssl_csr_sign($csr, null, $key, 365, $cfg) : false;
    if (!$key || !$x509) {
        @unlink($cnf);
        fwrite(STDERR, 'Could not create the certificate: ' . openssl_error_string() . "\n");
        return false;
    }
    openssl_x509_export($x509, $certPem);
    openssl_pkey_export($key, $keyPem, null, ['config' => $cnf]);
    @unlink($cnf);
    if (!is_dir(dirname($file))) {
        mkdir(dirname($file), 0775, true);
    }
    file_put_contents($file, $certPem . $keyPem);
    fwrite(STDOUT, "Wrote {$file} (self-signed, valid for 127.0.0.1 and localhost, 365 days).\n");
    return true;
}

$ctxOpts = [];
if ($tls !== 'none') {
    $ctxOpts['ssl'] = ['local_cert' => $cert, 'verify_peer' => false, 'allow_self_signed' => true];
}
$server = @stream_socket_server('tcp://127.0.0.1:' . $port, $errno, $errstr, STREAM_SERVER_BIND | STREAM_SERVER_LISTEN, stream_context_create($ctxOpts));
if (!$server) {
    fwrite(STDERR, "Cannot listen on 127.0.0.1:{$port}: {$errstr}\n");
    exit(1);
}
say("Mock SMTP server on 127.0.0.1:{$port} (tls={$tls}" . ($user !== '' ? ', login required' : '') . "). Messages go to {$out}. Ctrl+C to stop.");

$seq = count(glob($out . '/*.eml') ?: []);
$serverCrypto = STREAM_CRYPTO_METHOD_TLSv1_2_SERVER | (defined('STREAM_CRYPTO_METHOD_TLSv1_3_SERVER') ? STREAM_CRYPTO_METHOD_TLSv1_3_SERVER : 0);

while (true) {
    if (is_file($out . '/STOP')) {
        @unlink($out . '/STOP');
        say('STOP file found, bye.');
        break;
    }
    $c = @stream_socket_accept($server, 1);
    if (!$c) {
        continue;
    }
    stream_set_timeout($c, 30);
    $secure = false;
    if ($tls === 'implicit') {
        if (@stream_socket_enable_crypto($c, true, $serverCrypto) !== true) {
            say('TLS handshake failed (client refused the certificate?)');
            fclose($c);
            continue;
        }
        $secure = true;
    }
    $reply = static function (string $line) use ($c): void {
        fwrite($c, $line . "\r\n");
    };
    $reply('220 mock-smtp ESMTP ready');
    $from = '';
    $rcpts = [];
    $authed = $user === '';
    while (($line = fgets($c, 4096)) !== false) {
        $line = rtrim($line, "\r\n");
        $verb = strtoupper(strtok($line, ' ') ?: '');
        if ($verb === 'EHLO' || $verb === 'HELO') {
            $caps = ['250-mock-smtp', '250-SIZE ' . $maxSize, '250-8BITMIME'];
            if ($tls === 'starttls' && !$secure) {
                $caps[] = '250-STARTTLS';
            }
            if ($user !== '' && ($secure || $tls === 'none')) {
                $caps[] = '250-AUTH PLAIN LOGIN';
            }
            $caps[] = '250 HELP';
            foreach ($caps as $cl) {
                $reply($cl);
            }
        } elseif ($verb === 'STARTTLS' && $tls === 'starttls' && !$secure) {
            $reply('220 Ready to start TLS');
            if (@stream_socket_enable_crypto($c, true, $serverCrypto) !== true) {
                say('STARTTLS handshake failed (client refused the certificate?)');
                break;
            }
            $secure = true;
        } elseif ($verb === 'AUTH') {
            $parts = explode(' ', $line);
            $mech = strtoupper($parts[1] ?? '');
            $ok = false;
            if ($mech === 'PLAIN') {
                $blob = $parts[2] ?? null;
                if ($blob === null) {
                    $reply('334 ');
                    $blob = rtrim((string) fgets($c, 4096), "\r\n");
                }
                $f = explode("\0", (string) base64_decode($blob, true));
                $ok = count($f) === 3 && $f[1] === $user && $f[2] === $pass;
            } elseif ($mech === 'LOGIN') {
                $reply('334 ' . base64_encode('Username:'));
                $u = (string) base64_decode(rtrim((string) fgets($c, 4096), "\r\n"), true);
                $reply('334 ' . base64_encode('Password:'));
                $p = (string) base64_decode(rtrim((string) fgets($c, 4096), "\r\n"), true);
                $ok = $u === $user && $p === $pass;
            } else {
                $reply('504 Unrecognized authentication type');
                continue;
            }
            if ($ok) {
                $authed = true;
                $reply('235 2.7.0 Authentication successful');
            } else {
                $reply('535 5.7.8 Authentication credentials invalid');
            }
        } elseif ($verb === 'MAIL') {
            if (!$authed) {
                $reply('530 5.7.0 Authentication required');
                continue;
            }
            $from = preg_match('/<([^>]*)>/', $line, $m) === 1 ? $m[1] : '';
            $rcpts = [];
            if (preg_match('/SIZE=(\d+)/i', $line, $m) === 1 && (int) $m[1] > $maxSize) {
                $reply('552 5.3.4 Message size exceeds fixed limit');
                continue;
            }
            $reply('250 2.1.0 OK');
        } elseif ($verb === 'RCPT') {
            $to = preg_match('/<([^>]*)>/', $line, $m) === 1 ? $m[1] : '';
            $local = strtolower((string) strstr($to, '@', true));
            if (str_starts_with($local, 'slow')) {
                sleep(8);
            }
            if (str_starts_with($local, 'dropconn')) {
                break;
            }
            if (str_starts_with($local, 'reject5')) {
                $reply('550 5.1.1 Mailbox unavailable');
            } elseif (str_starts_with($local, 'reject4')) {
                $reply('451 4.3.0 Temporary local problem, try again later');
            } else {
                $rcpts[] = $to;
                $reply('250 2.1.5 OK');
            }
        } elseif ($verb === 'DATA') {
            if ($rcpts === []) {
                $reply('503 5.5.1 No valid recipients');
                continue;
            }
            $reply('354 End data with <CR><LF>.<CR><LF>');
            $data = '';
            while (($l = fgets($c, 4096)) !== false) {
                if ($l === ".\r\n" || $l === ".\n") {
                    break;
                }
                $data .= str_starts_with($l, '..') ? substr($l, 1) : $l;
            }
            $seq++;
            $file = sprintf('%s/%04d-%s.eml', $out, $seq, bin2hex(random_bytes(3)));
            file_put_contents($file, 'X-Mock-Mail-From: ' . $from . "\r\nX-Mock-Rcpt-To: " . implode(', ', $rcpts) . "\r\n" . $data);
            say('Message #' . $seq . ' stored (' . strlen($data) . ' bytes, ' . count($rcpts) . ' recipient(s))');
            $reply('250 2.0.0 OK queued as ' . $seq);
            $from = '';
            $rcpts = [];
        } elseif ($verb === 'RSET') {
            $from = '';
            $rcpts = [];
            $reply('250 2.0.0 OK');
        } elseif ($verb === 'NOOP') {
            $reply('250 2.0.0 OK');
        } elseif ($verb === 'QUIT') {
            $reply('221 2.0.0 Bye');
            break;
        } else {
            $reply('502 5.5.2 Command not recognized');
        }
    }
    @fclose($c);
}
fclose($server);
