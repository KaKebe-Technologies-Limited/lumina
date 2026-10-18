<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Table `calendar_entries` = one schedule item (single, multi-day or a recurring RULE). Generic API: see App\Core\Model.
 * Rules and validation: App\Services\Calendar\RecurrenceRules; expansion into dates: OccurrenceExpander / CalendarService.
 * Docs: docs/CALENDAR.md. System entries (is_system = 1) are seeded holidays / international days: editable and hideable, never deleted.
 */
final class CalendarEntry extends Model
{
    protected static string $table = 'calendar_entries';
    protected static string $order = 'start_date ASC, id ASC';

    public const RECURRENCES = ['none', 'daily', 'weekly', 'monthly', 'yearly', 'nth_weekday', 'easter', 'islamic'];
    /** Slugs an entry may not use (they are routes under /calendar/). */
    public const RESERVED_SLUGS = ['feed', 'event', 'events', 'ics', 'upcoming', 'agenda', 'month'];
}
