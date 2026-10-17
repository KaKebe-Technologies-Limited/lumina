<?php
declare(strict_types=1);

namespace App\Core;

/**
 * A small SMTP client in pure PHP sockets (no Composer). Used by Mailer when MAIL_DRIVER=smtp; documented in docs/NEWSLETTER.md section 9.
 *
 *   MAIL_ENCRYPTION=ssl   implicit TLS (usually port 465): the TLS handshake happens right after the TCP connection.
 *   MAIL_ENCRYPTION=tls   STARTTLS (usually port 587): plain greeting, then the connection is upgraded; if the server does not offer STARTTLS
 *                         the connection is REFUSED (no silent downgrade to plain text).
 *   MAIL_ENCRYPTION=none  no encryption: allowed for loopback hosts (a local relay, the dev mock server) only when logging in; a login over an
 *                         unencrypted connection to any other host is refused so the password can never cross the network in clear text.
 * Certificates are ALWAYS verified (peer and host name, TLS 1.2 or newer). MAIL_CAFILE may point to a CA bundle when the system has none.
 *
 * Login: AUTH PLAIN (preferred) or AUTH LOGIN, whichever the server offers. One connection is reused for several messages (RSET between them;
 * it reconnects once on its own when the server dropped an idle connection). Every wait has a timeout (MAIL_TIMEOUT).
 * Messages: the caller passes a complete RFC 5322 message; this class normalises line endings to CRLF, dot-stuffs, and checks the SIZE limit.
 *
 * SAFETY: exceptions (MailException) never contain the password (nor its base64 form), never contain e-mail addresses (server replies are
 * masked), and the class keeps no transcript. Addresses are validated here again so CR/LF or '>' can never reach a command.
 */
final class Smtp
{
    /** @var resource|null */
    private $sock = null;
    /** @var array<string,string> capabilities of the last EHLO (upper-case keyword => parameters) */
    private array $caps = [];
    private bool $encrypted = false;
    private int $sent = 0;

    public function __construct(
        private string $host,
        private int $port = 587,
        private string $encryption = 'tls',
        private string $username = '',
        private string $password = '',
        private int $timeout = 20,
        private string $caFile = '',
        private string $clientName = '',
    ) {
        $this->encryption = in_array($encryption, ['ssl', 'tls', 'none'], true) ? $encryption : 'tls';
        $this->timeout = max(3, min(120, $timeout));
        $name = $clientName !== '' ? $clientName : (string) gethostname();
        $this->clientName = preg_match('/^[A-Za-z0-9]([A-Za-z0-9.-]{0,251}[A-Za-z0-9])?$/', $name) === 1 ? $name : 'localhost';
    }

    /** Build the client from config('mail.smtp') (filled from .env: MAIL_HOST, MAIL_PORT, MAIL_USERNAME, MAIL_PASSWORD, MAIL_ENCRYPTION, MAIL_TIMEOUT, MAIL_CAFILE). */
    public static function fromConfig(): self
    {
        $c = (array) Config::get('mail.smtp', []);
        $host = trim((string) ($c['host'] ?? ''));
        if ($host === '') {
            throw new MailException('MAIL_DRIVER is "smtp" but MAIL_HOST is not set in the .env file.', 0, false, null, true);
        }
        $appHost = (string) (parse_url((string) Config::get('app.url', ''), PHP_URL_HOST) ?? '');
        return new self(
            $host,
            (int) ($c['port'] ?? 587) ?: 587,
            (string) ($c['encryption'] ?? 'tls'),
            (string) ($c['username'] ?? ''),
            (string) ($c['password'] ?? ''),
            (int) ($c['timeout'] ?? 20) ?: 20,
            (string) ($c['cafile'] ?? ''),
            $appHost
        );
    }

    /** A cache key that changes when the connection settings change (so Mailer can reuse a connection safely). */
    public function fingerprint(): string
    {
        return hash('sha256', implode('|', [$this->host, $this->port, $this->encryption, $this->username, $this->password, $this->timeout, $this->caFile]));
    }

    public function isOpen(): bool
    {
        return is_resource($this->sock) && !feof($this->sock);
    }

    // ------------------------------------------------------------------------------------------------ connect

    public function connect(): void
    {
        $this->close(false);
        if (preg_match('/^[A-Za-z0-9.:\-\[\]]{1,253}$/', $this->host) !== 1) {
            throw new MailException('MAIL_HOST is not a valid host name.', 0, false, null, true);
        }
        $scheme = $this->encryption === 'ssl' ? 'ssl' : 'tcp';
        $context = stream_context_create(['ssl' => $this->sslOptions()]);
        $errno = 0;
        $errstr = '';
        $sock = @stream_socket_client($scheme . '://' . $this->host . ':' . $this->port, $errno, $errstr, $this->timeout, STREAM_CLIENT_CONNECT, $context);
        if (!is_resource($sock)) {
            $why = $this->encryption === 'ssl' ? 'connection or TLS handshake failed' : 'connection failed';
            throw new MailException('Could not reach the mail server (' . $why . ': ' . $this->scrub($errstr !== '' ? $errstr : 'error ' . $errno) . ').', 0, true);
        }
        stream_set_timeout($sock, $this->timeout);
        $this->sock = $sock;
        $this->encrypted = $this->encryption === 'ssl';
        $this->caps = [];

        try {
            $this->expect($this->readReply(), [220], 'greeting');
            $this->ehlo();
            if ($this->encryption === 'tls') {
                if (!isset($this->caps['STARTTLS'])) {
                    throw new MailException('The mail server does not offer STARTTLS, so the connection was refused instead of sending without encryption. Use MAIL_ENCRYPTION=ssl on port 465, or check the port.', 0, false, null, true);
                }
                $this->command('STARTTLS', [220]);
                $ok = @stream_socket_enable_crypto($this->sock, true, $this->cryptoMethod());
                if ($ok !== true) {
                    throw new MailException('TLS could not be established (the server certificate is not trusted or does not match MAIL_HOST, or TLS 1.2 is not supported).', 0, false, null, true);
                }
                $this->encrypted = true;
                $this->ehlo();
            }
            if ($this->username !== '') {
                $this->authenticate();
            }
        } catch (\Throwable $e) {
            $this->close(false);
            throw $e;
        }
    }

    /** @return array<string,mixed> */
    private function sslOptions(): array
    {
        $o = [
            'verify_peer'       => true,
            'verify_peer_name'  => true,
            'allow_self_signed' => false,
            'peer_name'         => trim($this->host, '[]'),
            'SNI_enabled'       => true,
            'SNI_server_name'   => trim($this->host, '[]'),
            'crypto_method'     => $this->cryptoMethod(),
        ];
        $ca = $this->caFile !== '' ? $this->caFile : (string) (ini_get('openssl.cafile') ?: ini_get('curl.cainfo') ?: '');
        if ($ca !== '' && is_file($ca)) {
            $o['cafile'] = $ca;
        }
        return $o;
    }

    private function cryptoMethod(): int
    {
        $m = STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;
        if (defined('STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT')) {
            $m |= STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT;
        }
        return $m;
    }

    private function ehlo(): void
    {
        $this->write('EHLO ' . $this->clientName . "\r\n");
        $reply = $this->readReply();
        if ($reply[0] !== 250) {
            // very old servers only know HELO (no extensions, so no AUTH or STARTTLS)
            $this->write('HELO ' . $this->clientName . "\r\n");
            $this->expect($this->readReply(), [250], 'HELO');
            $this->caps = [];
            return;
        }
        $this->caps = [];
        foreach (array_slice($reply[1], 1) as $line) {
            $parts = preg_split('/[ =]/', trim($line), 2) ?: [];
            if (($parts[0] ?? '') !== '') {
                $this->caps[strtoupper($parts[0])] = (string) ($parts[1] ?? '');
            }
        }
    }

    private function authenticate(): void
    {
        if (!$this->encrypted && !$this->isLoopback()) {
            throw new MailException('Refusing to send the SMTP password without encryption. Set MAIL_ENCRYPTION=tls (port 587) or ssl (port 465).', 0, false, null, true);
        }
        $offered = array_filter(explode(' ', strtoupper($this->caps['AUTH'] ?? '')));
        if ($offered === []) {
            throw new MailException('The mail server does not offer a login method (AUTH). Check MAIL_ENCRYPTION and the port, or remove MAIL_USERNAME for a server without login.', 0, false, null, true);
        }
        if (in_array('PLAIN', $offered, true)) {
            $this->write('AUTH PLAIN ' . base64_encode("\0" . $this->username . "\0" . $this->password) . "\r\n");
            $this->expectAuth($this->readReply());
            return;
        }
        if (in_array('LOGIN', $offered, true)) {
            $this->write("AUTH LOGIN\r\n");
            $r = $this->readReply();
            if ($r[0] !== 334) {
                $this->expectAuth($r);
            }
            $this->write(base64_encode($this->username) . "\r\n");
            $r = $this->readReply();
            if ($r[0] !== 334) {
                $this->expectAuth($r);
            }
            $this->write(base64_encode($this->password) . "\r\n");
            $this->expectAuth($this->readReply());
            return;
        }
        throw new MailException('The mail server offers no login method this site supports (PLAIN or LOGIN).', 0, false, null, true);
    }

    /** @param array{0:int,1:list<string>} $reply */
    private function expectAuth(array $reply): void
    {
        if ($reply[0] === 235) {
            return;
        }
        throw new MailException('The mail server rejected the SMTP login (' . $reply[0] . '). Check MAIL_USERNAME and MAIL_PASSWORD.', $reply[0], $reply[0] >= 400 && $reply[0] < 500, null, true);
    }

    private function isLoopback(): bool
    {
        $h = strtolower(trim($this->host, '[]'));
        return $h === 'localhost' || $h === '::1' || str_starts_with($h, '127.');
    }

    // ------------------------------------------------------------------------------------------------ sending

    /**
     * Send one message.
     *
     * @param string       $from     envelope sender (MAIL FROM)
     * @param list<string> $rcpts    envelope recipients (RCPT TO); at least one must be accepted
     * @param string       $message  complete message (headers, blank line, body); any line ending style
     * @return int number of accepted recipients
     * @throws MailException
     */
    public function send(string $from, array $rcpts, string $message): int
    {
        $from = $this->address($from);
        $rcpts = array_values(array_map(fn(string $r): string => $this->address($r), $rcpts));
        if ($rcpts === []) {
            throw new MailException('No recipient.', 0, false);
        }
        $data = $this->prepareData($message);
        for ($attempt = 0; ; $attempt++) {
            try {
                if (!$this->isOpen()) {
                    $this->connect();
                } elseif ($this->sent > 0) {
                    $this->command('RSET', [250]);          // also detects a connection the server dropped while we were idle
                }
                return $this->transaction($from, $rcpts, $data);
            } catch (MailException $e) {
                $dropped = $e->smtpCode === 0 && $e->transient && $attempt === 0 && $this->sent > 0;
                if ($e->smtpCode === 0) {
                    $this->close(false);
                } else {
                    $this->quietReset();
                }
                if (!$dropped) {
                    throw $e;
                }
                // an idle connection was closed by the server: connect once more and try the same message again
            }
        }
    }

    /** @param list<string> $rcpts */
    private function transaction(string $from, array $rcpts, string $data): int
    {
        $size = (int) ($this->caps['SIZE'] ?? 0);
        if ($size > 0 && strlen($data) > $size) {
            throw new MailException('The message is larger than the mail server accepts (' . $size . ' bytes).', 552, false, null, true);
        }
        $this->command('MAIL FROM:<' . $from . '>' . (isset($this->caps['SIZE']) ? ' SIZE=' . strlen($data) : ''), [250]);
        $accepted = 0;
        $firstError = null;
        foreach ($rcpts as $rcpt) {
            $this->write('RCPT TO:<' . $rcpt . ">\r\n");
            $r = $this->readReply();
            if ($r[0] === 250 || $r[0] === 251) {
                $accepted++;
            } elseif ($firstError === null) {
                $firstError = $r;
            }
        }
        if ($accepted === 0) {
            $r = $firstError ?? [0, []];
            throw new MailException('The mail server did not accept the recipient (' . $r[0] . ' ' . $this->scrub((string) ($r[1][0] ?? '')) . ').', $r[0], $r[0] >= 400 && $r[0] < 500);
        }
        $this->command('DATA', [354]);
        $this->write($data . ".\r\n");
        $r = $this->readReply();
        if ($r[0] !== 250) {
            throw $this->replyError($r, 'The mail server did not accept the message');
        }
        $this->sent++;
        return $accepted;
    }

    /** CRLF line endings, dot-stuffing, and a final CRLF (the terminating ".\r\n" is added by the caller). */
    private function prepareData(string $message): string
    {
        $message = str_replace("\0", '', $message);
        $message = (string) preg_replace('/\r\n|\r|\n/', "\r\n", $message);
        $message = (string) preg_replace('/^\./m', '..', $message);
        return str_ends_with($message, "\r\n") ? $message : $message . "\r\n";
    }

    private function address(string $a): string
    {
        $a = trim($a);
        if (!Mailer::validEmail($a) || strlen($a) > 254) {
            throw new MailException('An e-mail address is not valid.', 0, false);
        }
        return $a;
    }

    private function quietReset(): void
    {
        try {
            if ($this->isOpen()) {
                $this->write("RSET\r\n");
                $this->readReply();
            }
        } catch (\Throwable) {
            $this->close(false);
        }
    }

    public function close(bool $quit = true): void
    {
        if (is_resource($this->sock)) {
            if ($quit) {
                try {
                    stream_set_timeout($this->sock, 3);
                    @fwrite($this->sock, "QUIT\r\n");
                    @fgets($this->sock, 512);
                } catch (\Throwable) {
                    // closing must never throw
                }
            }
            @fclose($this->sock);
        }
        $this->sock = null;
        $this->encrypted = false;
        $this->sent = 0;
    }

    public function __destruct()
    {
        $this->close();
    }

    // ------------------------------------------------------------------------------------------------ wire level

    /**
     * @param list<int> $ok accepted reply codes
     * @return array{0:int,1:list<string>}
     */
    private function command(string $line, array $ok): array
    {
        $this->write($line . "\r\n");
        $r = $this->readReply();
        $this->expect($r, $ok, strtok($line, ' ') ?: 'command');
        return $r;
    }

    /** @param array{0:int,1:list<string>} $reply @param list<int> $ok */
    private function expect(array $reply, array $ok, string $what): void
    {
        if (!in_array($reply[0], $ok, true)) {
            throw $this->replyError($reply, 'The mail server refused ' . $what);
        }
    }

    /** @param array{0:int,1:list<string>} $r */
    private function replyError(array $r, string $prefix): MailException
    {
        // authentication required / refused, or the sender address refused: wrong for every message, not for one recipient
        $config = in_array($r[0], [530, 534, 535, 538], true) || ($r[0] >= 500 && str_contains($prefix, 'refused MAIL'));
        return new MailException($prefix . ' (' . $r[0] . ' ' . $this->scrub((string) ($r[1][0] ?? '')) . ').', $r[0], $r[0] >= 400 && $r[0] < 500, null, $config);
    }

    private function write(string $data): void
    {
        if (!is_resource($this->sock)) {
            throw new MailException('The connection to the mail server is closed.', 0, true);
        }
        $len = strlen($data);
        $off = 0;
        while ($off < $len) {
            $n = @fwrite($this->sock, substr($data, $off, 16384));
            if ($n === false || $n === 0) {
                $meta = stream_get_meta_data($this->sock);
                throw new MailException(!empty($meta['timed_out']) ? 'The mail server stopped responding while the message was being sent (timeout).' : 'The connection to the mail server was lost while sending.', 0, true);
            }
            $off += $n;
        }
    }

    /** @return array{0:int,1:list<string>} reply code and its text lines */
    private function readReply(): array
    {
        if (!is_resource($this->sock)) {
            throw new MailException('The connection to the mail server is closed.', 0, true);
        }
        $lines = [];
        $code = 0;
        for ($i = 0; $i < 200; $i++) {
            $line = @fgets($this->sock, 2048);
            if ($line === false) {
                $meta = stream_get_meta_data($this->sock);
                throw new MailException(!empty($meta['timed_out']) ? 'The mail server did not answer in time (timeout).' : 'The mail server closed the connection unexpectedly.', 0, true);
            }
            $line = rtrim($line, "\r\n");
            if (preg_match('/^(\d{3})([ -]?)(.*)$/', $line, $m) !== 1) {
                throw new MailException('The mail server sent an unexpected reply.', 0, true);
            }
            $code = (int) $m[1];
            $lines[] = $m[3];
            if ($m[2] !== '-') {
                return [$code, $lines];
            }
        }
        throw new MailException('The mail server sent a reply that is too long.', 0, true);
    }

    /** Make a server text safe for logs and screens: no password, no e-mail addresses, no control characters, short. */
    private function scrub(string $s): string
    {
        if ($this->password !== '') {
            $s = str_replace([$this->password, base64_encode($this->password), rawurlencode($this->password)], '***', $s);
        }
        $s = (string) preg_replace('/[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+/', '[address]', $s);
        $s = (string) preg_replace('/[\x00-\x1F\x7F]+/', ' ', $s);
        return mb_substr(trim($s), 0, 160);
    }
}
