<?php
declare(strict_types=1);

namespace App\Core;

/**
 * One-request flash messages, rendered by partials/flash.php in both layouts.
 *   Flash::success('Saved.'); Flash::error('Nope'); Flash::info('...'); Flash::warning('...');
 * Types: success | error | info | warning. Messages are plain text (escaped on output).
 */
final class Flash
{
    public static function add(string $type, string $message): void
    {
        $all = Session::get('_flash_msgs', []);
        $all[] = ['type' => $type, 'message' => $message];
        Session::put('_flash_msgs', $all);
    }

    public static function success(string $m): void
    {
        self::add('success', $m);
    }

    public static function error(string $m): void
    {
        self::add('error', $m);
    }

    public static function info(string $m): void
    {
        self::add('info', $m);
    }

    public static function warning(string $m): void
    {
        self::add('warning', $m);
    }

    /** Return and clear queued messages. @return array<int,array{type:string,message:string}> */
    public static function pull(): array
    {
        return (array) Session::pull('_flash_msgs', []);
    }
}
