<?php
/**
 * Admin sidebar fragment: MEMBERSHIP (applications + members, and the membership types = fee configuration).
 * Group order 35 (core=10, content=20, org=30, inbox=40). The badge is the number of applications waiting for review.
 * Contract: docs/MEMBERSHIP.md.
 */

use App\Services\Membership\MembershipService;

return [
    'key'   => 'membership',
    'label' => 'Membership',
    'order' => 35,
    'items' => [
        ['key' => 'members', 'label' => 'Members', 'url' => 'admin/members', 'icon' => 'users', 'can' => 'members.manage', 'order' => 10,
         'badge' => static fn(): int => MembershipService::pendingCount()],
        ['key' => 'membership_types', 'label' => 'Membership types & fees', 'url' => 'admin/membership-types', 'icon' => 'banknote', 'can' => 'members.types', 'order' => 20,
         'badge' => null],
    ],
];
