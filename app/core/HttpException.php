<?php
declare(strict_types=1);

namespace App\Core;

/** Throw (or call abort($code)) to render the matching error page: 403, 404, 405, 419, 429, 500. */
class HttpException extends \RuntimeException
{
    /** @param array<string,string> $headers extra response headers (e.g. Allow, Retry-After) */
    public function __construct(public readonly int $status = 500, string $message = '', public readonly array $headers = [])
    {
        parent::__construct($message, $status);
    }
}
