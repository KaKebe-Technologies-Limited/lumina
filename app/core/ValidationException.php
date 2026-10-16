<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Thrown by Controller::validate() when input is invalid. App::run() turns it into a redirect back
 * (errors + old input flashed) or, for AJAX/JSON requests, a 422 JSON response. You rarely catch it.
 */
class ValidationException extends \RuntimeException
{
    public function __construct(public readonly array $errors, public readonly array $old = [], public readonly ?string $redirectTo = null)
    {
        parent::__construct('Validation failed', 422);
    }
}
