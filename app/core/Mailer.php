<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Outgoing mail. Driver from .env MAIL_DRIVER:
 *   "file"  (default when APP_ENV=local) writes storage/mail/*.eml
 *   "mail"  (default otherwise) PHP mail()
 *   "smtp"  pure-PHP SMTP client (App\Core\Smtp): MAIL_HOST, MAIL_PORT, MAIL_USERNAME, MAIL_PASSWORD, MAIL_ENCRYPTION=ssl|tls|none, MAIL_TIMEOUT
 *
 * Header-injection safe: CR/LF/NUL are stripped from every header value, addresses are validated, extra header names are validated and the
 * structural headers (From, To, Subject, Date, Message-ID, MIME and Content-*) cannot be overridden. Subjects and display names are encoded
 * as RFC 2047 words (UTF-8, split on character boundaries). Every message gets Date, Message-ID, MIME-Version and, when it has HTML, a
 * multipart/alternative body whose text part is generated from the HTML when you do not pass one.
 *
 *   Mailer::send('someone@example.org', 'Subject', "Plain text body", ['reply_to' => $visitorEmail, 'html' => '<p>..</p>']);   // bool, never throws
 *   Mailer::attempt($to, $subject, $text, $opts)     // ['ok'=>bool,'error'=>?string,'transient'=>bool] - for queues that retry (newsletter)
 *   Mailer::notify('New contact message', $body, $visitorEmail);   // to Settings notify_email (fallback email_primary)
 *
 * $opts: reply_to, from, from_name, html, headers (['List-Unsubscribe' => '<https://..>', ...]), return_path (envelope sender; default = from).
 */
final class Mailer
{
    /** Headers a caller may not set through $opts['headers'] (the Mailer owns them). */
    private const RESERVED_HEADERS = ['from', 'to', 'cc', 'bcc', 'subject', 'date', 'message-id', 'mime-version', 'content-type', 'content-transfer-encoding',
        'content-disposition', 'reply-to', 'return-path', 'sender', 'x-mailer'];

    private static ?Smtp $smtp = null;
    private static ?string $smtpKey = null;
    private static ?string $lastError = null;

    /**
     * @param string|string[] $to
     * @param array{reply_to?:string,from?:string,from_name?:string,html?:string,headers?:array<string,string>,return_path?:string} $opts
     */
    public static function send(string|array $to, string $subject, string $text, array $opts = []): bool
    {
        return self::attempt($to, $subject, $text, $opts)['ok'];
    }

    /**
     * Same as send() but says why it failed. 'transient' = worth trying again later (network, timeout, 4xx), false = permanent (bad address, rejected login).
     * The error text never contains the SMTP password or e-mail addresses.
     *
     * @return array{ok:bool,error:?string,transient:bool}
     */
    public static function attempt(string|array $to, string $subject, string $text, array $opts = []): array
    {
        self::$lastError = null;
        try {
            $recipients = [];
            foreach ((array) $to as $addr) {
                $addr = trim((string) $addr);
                if (self::validEmail($addr)) {
                    $recipients[] = $addr;
                }
            }
            if ($recipients === []) {
                Log::warning('Mailer: no valid recipient', ['subject' => $subject]);
                return self::fail('No valid recipient address.', false);
            }
            $msg = self::compose($recipients, $subject, $text, $opts);
            $driver = (string) Config::get('mail.driver');

            if ($driver === 'file') {
                $dir = STORAGE_PATH . '/mail';
                if (!is_dir($dir)) {
                    @mkdir($dir, 0775, true);
                }
                $file = $dir . '/' . date('Ymd-His') . '-' . bin2hex(random_bytes(3)) . '.eml';
                $ok = file_put_contents($file, self::raw($msg, true)) !== false;
                return $ok ? ['ok' => true, 'error' => null, 'transient' => false] : self::fail('The message could not be written to storage/mail.', true);
            }
            if ($driver === 'smtp') {
                $smtp = self::smtp();
                $smtp->send($msg['envelope_from'], $recipients, self::raw($msg, true));
                return ['ok' => true, 'error' => null, 'transient' => false];
            }

            $headers = [];
            foreach ($msg['headers'] as $name => $value) {
                if ($name !== 'To' && $name !== 'Subject') {
                    $headers[] = self::fold($name, $value);
                }
            }
            $ok = @mail(implode(', ', $recipients), $msg['subject'], $msg['body'], implode("\r\n", $headers));
            return $ok ? ['ok' => true, 'error' => null, 'transient' => false] : self::fail('PHP mail() refused the message.', true);
        } catch (MailException $e) {
            Log::error('Mailer failed: ' . $e->getMessage(), ['smtp' => $e->smtpCode]);
            return self::fail($e->getMessage(), $e->transient, $e->config);
        } catch (\Throwable $e) {
            Log::error('Mailer failed: ' . preg_replace('/[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+/', '[address]', $e->getMessage()));
            return self::fail('The message could not be sent.', true);
        }
    }

    /** The reason the last send()/attempt() failed (null after a success). Safe to show to an administrator. */
    public static function lastError(): ?string
    {
        return self::$lastError;
    }

    /** Close the SMTP connection kept open for batches (call at the end of a batch; also done automatically at shutdown). */
    public static function closeConnection(): void
    {
        if (self::$smtp !== null) {
            self::$smtp->close();
        }
        self::$smtp = null;
        self::$smtpKey = null;
    }

    /** "smtp" | "mail" | "file". */
    public static function driver(): string
    {
        return (string) Config::get('mail.driver');
    }

    /** Email the organisation (Settings notify_email, falling back to email_primary). */
    public static function notify(string $subject, string $text, ?string $replyTo = null, ?string $html = null): bool
    {
        $to = trim((string) setting('notify_email', ''));
        if ($to === '') {
            $to = trim((string) setting('email_primary', ''));
        }
        $opts = $replyTo !== null ? ['reply_to' => $replyTo] : [];
        if ($html !== null) {
            $opts['html'] = $html;
        }
        return self::send($to, $subject, $text, $opts);
    }

    public static function validEmail(string $addr): bool
    {
        return $addr !== '' && !preg_match('/[\r\n,;<>]/', $addr) && filter_var($addr, FILTER_VALIDATE_EMAIL) !== false;
    }

    /** Readable plain text from an HTML e-mail (used when the caller passes no text part). */
    public static function htmlToText(string $html): string
    {
        $html = (string) preg_replace('~<(script|style|head)\b.*?</\1>~is', '', $html);
        $html = (string) preg_replace('~<a\b[^>]*\bhref="([^"]*)"[^>]*>(.*?)</a>~is', '$2 ($1)', $html);
        $html = (string) preg_replace('~<li\b[^>]*>~i', "\n- ", $html);
        $html = (string) preg_replace('~<br\s*/?>|</(p|div|h[1-6]|tr|ul|ol|table|blockquote)>~i', "\n", $html);
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = (string) preg_replace("/[ \t\x{00A0}]+/u", ' ', $text);
        $text = (string) preg_replace("/ *\n */", "\n", $text);
        return trim((string) preg_replace("/\n{3,}/", "\n\n", $text));
    }

    // ------------------------------------------------------------------------------------------------ message building

    /**
     * @param string[] $recipients validated addresses
     * @return array{headers:array<string,string>,body:string,subject:string,envelope_from:string}
     */
    private static function compose(array $recipients, string $subject, string $text, array $opts): array
    {
        $from = self::validEmail((string) ($opts['from'] ?? '')) ? (string) $opts['from'] : (string) Config::get('mail.from');
        if (!self::validEmail($from)) {
            throw new MailException('MAIL_FROM is not a valid e-mail address.', 0, false);
        }
        $fromName = self::clean((string) ($opts['from_name'] ?? Config::get('mail.from_name', '')));
        $replyTo = isset($opts['reply_to']) && self::validEmail((string) $opts['reply_to']) ? (string) $opts['reply_to'] : null;
        $returnPath = isset($opts['return_path']) && self::validEmail((string) $opts['return_path']) ? (string) $opts['return_path'] : $from;
        $subject = self::clean($subject);
        $html = isset($opts['html']) ? (string) $opts['html'] : '';
        if ($html !== '' && trim($text) === '') {
            $text = self::htmlToText($html);
        }
        $domain = strtolower((string) substr((string) strrchr($from, '@'), 1)) ?: 'localhost';

        $headers = [
            'Date'         => date('r'),
            'From'         => self::formatAddress($fromName, $from),
            'To'           => implode(', ', $recipients),
            'Subject'      => self::encodeHeader($subject),
            'Message-ID'   => '<' . bin2hex(random_bytes(12)) . '@' . $domain . '>',
            'MIME-Version' => '1.0',
            'X-Mailer'     => 'Lumina',
        ];
        if ($replyTo !== null) {
            $headers['Reply-To'] = $replyTo;
        }
        foreach ((array) ($opts['headers'] ?? []) as $name => $value) {
            $name = trim((string) $name);
            if (preg_match('/^[A-Za-z][A-Za-z0-9-]{0,60}$/', $name) !== 1 || in_array(strtolower($name), self::RESERVED_HEADERS, true) || !is_scalar($value)) {
                continue;
            }
            $v = self::clean((string) $value);
            if ($v !== '' && strlen($v) <= 2000) {
                $headers[$name] = $v;
            }
        }

        if ($html !== '') {
            $boundary = 'lumina_' . bin2hex(random_bytes(8));
            $headers['Content-Type'] = 'multipart/alternative; boundary="' . $boundary . '"';
            $body = "--{$boundary}\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n"
                . chunk_split(base64_encode($text))
                . "--{$boundary}\r\nContent-Type: text/html; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n"
                . chunk_split(base64_encode($html))
                . "--{$boundary}--\r\n";
        } else {
            $headers['Content-Type'] = 'text/plain; charset=UTF-8';
            $headers['Content-Transfer-Encoding'] = 'base64';
            $body = chunk_split(base64_encode($text));
        }
        return ['headers' => $headers, 'body' => $body, 'subject' => $headers['Subject'], 'envelope_from' => $returnPath];
    }

    /**
     * The complete message text (headers + blank line + body), CRLF endings. $withTo = include the To header (file and SMTP drivers).
     * @param array{headers:array<string,string>,body:string} $msg
     */
    private static function raw(array $msg, bool $withTo): string
    {
        $lines = [];
        foreach ($msg['headers'] as $name => $value) {
            if ($name === 'To' && !$withTo) {
                continue;
            }
            $lines[] = self::fold($name, $value);
        }
        return implode("\r\n", $lines) . "\r\n\r\n" . $msg['body'];
    }

    /** "Name: value", folded at spaces to lines of at most 76 characters (a value that already holds encoded-word breaks is left alone). */
    private static function fold(string $name, string $value): string
    {
        $line = $name . ': ' . $value;
        if (strlen($line) <= 76 || str_contains($value, "\r\n")) {
            return $line;
        }
        $out = '';
        $cur = $name . ':';
        foreach (explode(' ', $value) as $word) {
            if ($word === '') {
                continue;
            }
            if (strlen($cur) + 1 + strlen($word) > 76 && trim($cur) !== $name . ':') {
                $out .= $cur . "\r\n";
                $cur = ' ' . $word;
            } else {
                $cur .= ' ' . $word;
            }
        }
        return $out . $cur;
    }

    /** "Display Name <addr>": plain, quoted or RFC 2047 encoded depending on the characters in the name. */
    private static function formatAddress(string $name, string $addr): string
    {
        $name = self::clean($name);
        if ($name === '') {
            return $addr;
        }
        if (preg_match('/^[A-Za-z0-9 ._\'-]+$/', $name) === 1) {
            return $name . ' <' . $addr . '>';
        }
        if (preg_match('/^[\x20-\x7E]+$/', $name) === 1) {
            return '"' . addcslashes($name, '"\\') . '" <' . $addr . '>';
        }
        return self::encodeHeader($name) . ' <' . $addr . '>';
    }

    /** CR, LF and NUL become spaces; the result is trimmed. */
    private static function clean(string $v): string
    {
        return trim((string) preg_replace('/[\r\n\0]+/', ' ', $v));
    }

    /** RFC 2047: printable ASCII stays; anything else becomes =?UTF-8?B?...?= words of at most 75 characters, cut on character boundaries. */
    private static function encodeHeader(string $v): string
    {
        $v = self::clean($v);
        if (preg_match('/^[\x20-\x7E]*$/', $v) === 1) {
            return $v;
        }
        $words = [];
        $chunk = '';
        foreach (preg_split('//u', $v, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $char) {
            if (strlen($chunk) + strlen($char) > 45) {          // 45 bytes -> 60 base64 characters + 12 = 72 <= 75
                $words[] = '=?UTF-8?B?' . base64_encode($chunk) . '?=';
                $chunk = '';
            }
            $chunk .= $char;
        }
        if ($chunk !== '') {
            $words[] = '=?UTF-8?B?' . base64_encode($chunk) . '?=';
        }
        return implode("\r\n ", $words);
    }

    // ------------------------------------------------------------------------------------------------ smtp connection

    private static function smtp(): Smtp
    {
        $fresh = Smtp::fromConfig();
        $key = $fresh->fingerprint();
        if (self::$smtp === null || self::$smtpKey !== $key) {
            self::closeConnection();
            self::$smtp = $fresh;
            self::$smtpKey = $key;
            register_shutdown_function([self::class, 'closeConnection']);
        }
        return self::$smtp;
    }

    /** @return array{ok:bool,error:?string,transient:bool,config?:bool} config = the set-up is wrong for every message (login, TLS, sender, size limit) */
    private static function fail(string $message, bool $transient, bool $config = false): array
    {
        self::$lastError = $message;
        return ['ok' => false, 'error' => $message, 'transient' => $transient, 'config' => $config];
    }
}
