<?php
declare(strict_types=1);

namespace App\Services\Meetings;

/**
 * "Generate a free private video room": an unguessable https://meet.jit.si/<room> link. Jitsi Meet is an EXTERNAL free service (no account,
 * nothing is created on their side until someone opens the link). The room name carries 128 bits from random_bytes, so it cannot be guessed.
 * The site cannot create Zoom / Google Meet / Teams rooms (no vendor accounts or APIs): the organiser pastes those links.
 */
final class JitsiLink
{
    public const HOST = 'meet.jit.si';
    public const PREFIX = 'LuminaMeeting';

    public static function generate(): string
    {
        return 'https://' . self::HOST . '/' . self::PREFIX . '-' . bin2hex(random_bytes(16));
    }

    public static function isJitsi(string $url): bool
    {
        return preg_match('~^https://' . preg_quote(self::HOST, '~') . '/[A-Za-z0-9_-]{8,120}$~', $url) === 1;
    }
}
