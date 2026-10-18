<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Table `calendar_types` = entry types of the calendar (name, colour, icon, order, visibility). Generic API: see App\Core\Model.
 * Reading with caching and colour pairs: App\Services\Calendar\CalendarService::types(). Docs: docs/CALENDAR.md.
 */
final class CalendarType extends Model
{
    protected static string $table = 'calendar_types';
    protected static string $order = 'sort_order ASC, id ASC';

    /** The type whose rows come from the `events` table (no entries of this type are stored). */
    public const SLUG_EVENT = 'event';
    /** The type that is flagged "Public holiday" everywhere. */
    public const SLUG_HOLIDAY = 'uganda-holiday';
    /** Type used for JSON-LD Event output together with events. */
    public const SLUG_PROGRAMME = 'programme';
}
