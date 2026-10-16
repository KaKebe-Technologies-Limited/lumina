<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Outgoing mail. Driver from .env MAIL_DRIVER: "file" (default when APP_ENV=local: writes storage/mail/*.eml) or "mail" (PHP mail()).
 * Header-injection safe: CR/LF are stripped from every header value and addresses are validated.
 * Never throws; returns false (and logs) on failure.
 *
 *   Mailer::send('someone@example.org', 'Subject', "Plain text body", ['reply_to' => $visitorEmail, 'html' => '<p>..</p>']);
 *   Mailer::notify('New contact message', $body, $visitorEmail);   // to Settings notify_email (fallback email_primary)
 */
final class Mailer
{
    /**
     * @param string|string[] $to
     * @param array{reply_to?:string,from?:string,from_name?:string,html?:string} $opts
     */
    public static function send(string|array $to, string $subject, string $text, array $opts = []): bool
    {
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
                return false;
            }
            $from = self::validEmail((string) ($opts['from'] ?? '')) ? (string) $opts['from'] : (string) Config::get('mail.from');
            $fromName = self::clean((string) ($opts['from_name'] ?? Config::get('mail.from_name', '')));
            $replyTo = isset($opts['reply_to']) && self::validEmail((string) $opts['reply_to']) ? (string) $opts['reply_to'] : null;
            $subject = self::clean($subject);

            $headers = [
                'From: ' . ($fromName !== '' ? self::encodeHeader($fromName) . " <{$from}>" : $from),
                'MIME-Version: 1.0',
                'X-Mailer: Lumina',
            ];
            if ($replyTo !== null) {
                $headers[] = 'Reply-To: ' . $replyTo;
            }
            $html = isset($opts['html']) ? (string) $opts['html'] : '';
            if ($html !== '') {
                $boundary = 'lumina_' . bin2hex(random_bytes(8));
                $headers[] = 'Content-Type: multipart/alternative; boundary="' . $boundary . '"';
                $body = "--{$boundary}\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n"
                    . chunk_split(base64_encode($text))
                    . "--{$boundary}\r\nContent-Type: text/html; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n"
                    . chunk_split(base64_encode($html))
                    . "--{$boundary}--\r\n";
            } else {
                $headers[] = 'Content-Type: text/plain; charset=UTF-8';
                $headers[] = 'Content-Transfer-Encoding: base64';
                $body = chunk_split(base64_encode($text));
            }
            $encSubject = self::encodeHeader($subject);

            if ((string) Config::get('mail.driver') === 'file') {
                $dir = STORAGE_PATH . '/mail';
                if (!is_dir($dir)) {
                    @mkdir($dir, 0775, true);
                }
                $file = $dir . '/' . date('Ymd-His') . '-' . bin2hex(random_bytes(3)) . '.eml';
                $eml = 'To: ' . implode(', ', $recipients) . "\r\nSubject: {$encSubject}\r\nDate: " . date('r') . "\r\n"
                    . implode("\r\n", $headers) . "\r\n\r\n" . $body;
                return file_put_contents($file, $eml) !== false;
            }

            return @mail(implode(', ', $recipients), $encSubject, $body, implode("\r\n", $headers));
        } catch (\Throwable $e) {
            Log::error('Mailer failed: ' . $e->getMessage());
            return false;
        }
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

    private static function clean(string $v): string
    {
        return trim((string) preg_replace('/[\r\n\0]+/', ' ', $v));
    }

    private static function encodeHeader(string $v): string
    {
        $v = self::clean($v);
        return preg_match('/^[\x20-\x7E]*$/', $v) ? $v : '=?UTF-8?B?' . base64_encode($v) . '?=';
    }
}
