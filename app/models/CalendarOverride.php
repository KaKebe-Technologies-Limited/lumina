<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Table `calendar_overrides` = a change to ONE occurrence of a recurring entry (move, cancel, confirm). Generic API: see App\Core\Model.
 * Identified by (entry_id, occurrence_date) where occurrence_date is the day the RULE produces. Write through
 * App\Services\Calendar\CalendarService::saveOverride() (validates against the rule). Docs: docs/CALENDAR.md.
 */
final class CalendarOverride extends Model
{
    protected static string $table = 'calendar_overrides';
    protected static string $order = 'occurrence_date ASC, id ASC';
}
