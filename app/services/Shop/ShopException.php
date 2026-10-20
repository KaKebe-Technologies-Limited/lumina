<?php
declare(strict_types=1);

namespace App\Services\Shop;

/**
 * Thrown inside a shop transaction to roll it back with a reason the caller turns into a normal result array.
 * Never reaches a visitor: OrderService / PaymentService catch it and return ['ok' => false, 'error' => $code, ...].
 */
final class ShopException extends \RuntimeException
{
    /** @param array<string,mixed> $details */
    public function __construct(public readonly string $errorCode, public readonly array $details = [])
    {
        parent::__construct($errorCode);
    }
}
