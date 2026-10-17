<?php
declare(strict_types=1);

namespace App\Core;

/**
 * A mail transport failure (SMTP connection, TLS, authentication, a rejected recipient or message). The message is safe to log and to show to
 * an administrator: it never contains the SMTP password, and the SMTP driver never puts recipient addresses in it.
 *
 *   $e->smtpCode   SMTP reply code (0 when the failure happened before a reply: DNS, connect, timeout, TLS)
 *   $e->transient  true = worth retrying later (network trouble, timeout, 4xx reply); false = permanent (bad login, 5xx reply, bad address)
 *   $e->config     true = the fault is in the SENDER's set-up (missing host, TLS, wrong login, sender refused, message larger than the server
 *                  allows), so it will fail for EVERY recipient; a bulk sender should stop instead of failing the whole list
 */
final class MailException extends \RuntimeException
{
    public function __construct(string $message, public readonly int $smtpCode = 0, public readonly bool $transient = true, ?\Throwable $previous = null, public readonly bool $config = false)
    {
        parent::__construct($message, $smtpCode, $previous);
    }
}
