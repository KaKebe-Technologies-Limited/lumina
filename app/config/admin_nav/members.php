<?php
/**
 * Admin sidebar fragment: MEMBERSHIP (applications + members, the proofs of payment waiting for a check, the membership card designer, and the
 * membership types = fee configuration).
 * Group order 35 (core=10, content=20, org=30, inbox=40). The Members badge is the number of applications waiting for review; "Proof checks" counts the
 * members whose uploaded proof of payment waits for staff (filter proof=received). Contract: docs/MEMBERSHIP.md and docs/MEMBERSHIP_CARD.md.
 */

use App\Services\Membership\MembershipService;

return [
    'key'   => 'membership',
    'label' => 'Membership',
    'order' => 35,
    'items' => [
        ['key' => 'members', 'label' => 'Members', 'url' => 'admin/members', 'icon' => 'users', 'can' => 'members.manage', 'order' => 10,
         'badge' => static fn(): int => MembershipService::pendingCount()],
        ['key' => 'members_proofs', 'label' => 'Needs proof check', 'url' => 'admin/members?proof=received', 'icon' => 'check-circle', 'can' => 'members.manage', 'order' => 15,
         'badge' => static fn(): int => MembershipService::proofCheckCount()],
        ['key' => 'membership_card', 'label' => 'Membership card', 'url' => 'admin/membership-card', 'icon' => 'credit-card', 'can' => 'members.card', 'order' => 18,
         'badge' => null],
        ['key' => 'membership_types', 'label' => 'Membership types & fees', 'url' => 'admin/membership-types', 'icon' => 'banknote', 'can' => 'members.types', 'order' => 20,
         'badge' => null],
    ],
];
