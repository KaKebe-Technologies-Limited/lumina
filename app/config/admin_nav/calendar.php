<?php
/**
 * Admin sidebar fragment: CALENDAR (manager, holidays with the "dates to confirm" badge, types, import / export).
 * Group order 36 (core=10, content=20, org=30, membership=35, newsletter=37, inbox=40). Contract: docs/CALENDAR.md.
 */

use App\Services\Calendar\CalendarService;

return [
    'key'   => 'calendar',
    'label' => 'Calendar',
    'order' => 36,
    'items' => [
        ['key' => 'calendar', 'label' => 'Calendar & schedule', 'url' => 'admin/calendar', 'icon' => 'calendar', 'can' => 'calendar.manage', 'order' => 10, 'badge' => null],
        ['key' => 'calendar_holidays', 'label' => 'Holidays & days', 'url' => 'admin/calendar/holidays', 'icon' => 'star', 'can' => 'calendar.manage', 'order' => 20,
         'badge' => static fn(): int => CalendarService::pendingConfirmationCount()],
        ['key' => 'calendar_types', 'label' => 'Types & colours', 'url' => 'admin/calendar/types', 'icon' => 'tag', 'can' => 'calendar.manage', 'order' => 30, 'badge' => null],
        ['key' => 'calendar_import', 'label' => 'Import / export', 'url' => 'admin/calendar/import', 'icon' => 'upload', 'can' => 'calendar.manage', 'order' => 40, 'badge' => null],
    ],
];
