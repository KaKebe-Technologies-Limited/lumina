<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/** Table `event_registrations`. Generic API: see App\Core\Model. */
final class EventRegistration extends Model
{
    protected static string $table = 'event_registrations';
    protected static string $order = 'id DESC';

    public const STATUSES = ['registered', 'attended', 'cancelled'];
}
