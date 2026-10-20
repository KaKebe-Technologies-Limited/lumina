<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Tiny file logger -> storage/logs/app-YYYY-MM-DD.log  (never throws).
 *   Log::error('Mail failed', ['to' => $to]);  Log::warning(...);  Log::info(...);
 */
final class Log
{
    public static function write(string $level, string $message, array $context = []): void
    {
        try {
            $dir = STORAGE_PATH . '/logs';
            if (!is_dir($dir)) {
                @mkdir($dir, 0775, true);
            }
            $ctx = $context === [] ? '' : ' ' . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR);
            $line = '[' . date('Y-m-d H:i:s') . '] ' . strtoupper($level) . ' ' . str_replace(["\r", "\n"], ' ', $message) . $ctx . PHP_EOL;
            // a secret link is a credential (the private order page, the payment return key, a meeting or unsubscribe link): keep it out of the files
            $line = (string) preg_replace('~(/(?:shop/(?:order|pay|pay/return)|meeting|unsubscribe)/)[A-Fa-f0-9]{20,}~', '$1[link]', $line);
            // social-media credentials: a Telegram bot token is part of its URL path, the others travel as parameters or headers
            $line = (string) preg_replace('~bot\d{5,}:[A-Za-z0-9_-]{10,}~i', 'bot[hidden]', $line);
            $line = (string) preg_replace('~(access_token|oauth_token|oauth_signature|bearer_token|client_secret)=[^&\s"\']+~i', '$1=[hidden]', $line);
            $line = (string) preg_replace('~\b(Bearer|OAuth)\s+[A-Za-z0-9._\~+/=-]{12,}~', '$1 [hidden]', $line);
            @file_put_contents($dir . '/app-' . date('Y-m-d') . '.log', $line, FILE_APPEND | LOCK_EX);
        } catch (\Throwable) {
            // logging must never break a request
        }
    }

    public static function error(string $message, array $context = []): void
    {
        self::write('error', $message, $context);
    }

    public static function warning(string $message, array $context = []): void
    {
        self::write('warning', $message, $context);
    }

    public static function info(string $message, array $context = []): void
    {
        self::write('info', $message, $context);
    }
}
