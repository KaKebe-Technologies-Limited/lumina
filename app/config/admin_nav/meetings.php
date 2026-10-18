<?php
/**
 * Admin sidebar fragment: MEETINGS. The items are merged into the "Calendar" group (see AdminNav `merge_into`): Meetings, Schedule a meeting,
 * Meeting requests (badge = new requests). Contract: docs/MEETINGS.md section 9.
 */

use App\Services\Meetings\MeetingService;

return [
    'key'        => 'meetings',
    'merge_into' => 'calendar',
    'label'      => 'Calendar',
    'order'      => 36,
    'items'      => [
        ['key' => 'meetings', 'label' => 'Meetings', 'url' => 'admin/meetings', 'icon' => 'users', 'can' => 'meetings.manage', 'order' => 50, 'badge' => null],
        ['key' => 'meeting_create', 'label' => 'Schedule a meeting', 'url' => 'admin/meetings/create', 'icon' => 'plus', 'can' => 'meetings.manage', 'order' => 51, 'badge' => null],
        ['key' => 'meeting_requests', 'label' => 'Meeting requests', 'url' => 'admin/meeting-requests', 'icon' => 'inbox', 'can' => 'meetings.manage', 'order' => 52,
         'badge' => static fn(): int => MeetingService::newRequestCount()],
    ],
];
