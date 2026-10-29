<?php
/**
 * Admin sidebar fragment: LAF ACADEMY. Group order 39 (forms=38, inbox=40). Facilitators (academy.teach) see Overview, Courses, Virtual classes
 * and Learners (limited to their own courses); the rest needs academy.manage. Docs: docs/ACADEMY.md.
 */
return [
    'key'   => 'academy',
    'label' => 'LAF Academy',
    'order' => 39,
    'items' => [
        ['key' => 'academy',              'label' => 'Overview',        'url' => 'admin/academy',              'icon' => 'book',      'can' => 'academy.teach',  'order' => 10, 'badge' => null],
        ['key' => 'academy_courses',      'label' => 'Courses',         'url' => 'admin/academy/courses',      'icon' => 'layers',    'can' => 'academy.teach',  'order' => 20, 'badge' => null],
        ['key' => 'academy_partners',     'label' => 'Partner courses', 'url' => 'admin/academy/partner-courses', 'icon' => 'globe',    'can' => 'academy.manage', 'order' => 25, 'badge' => null],
        ['key' => 'academy_classes',     'label' => 'Virtual classes', 'url' => 'admin/academy/classes',      'icon' => 'video',     'can' => 'academy.teach',  'order' => 30, 'badge' => null],
        ['key' => 'academy_learners',     'label' => 'Learners',        'url' => 'admin/academy/learners',     'icon' => 'users',     'can' => 'academy.teach',  'order' => 40,
            // the number of new learners waiting for approval (administrators only; a facilitator sees no count of other people's learners)
            'badge' => static fn(): int => can('academy.manage') ? \App\Services\Academy\LearnerRegistry::waiting() : 0, 'badge_tone' => 'yellow'],
        // learners' unread messages (administrators only): a yellow number
        ['key' => 'academy_messages',     'label' => 'Messages',        'url' => 'admin/academy/messages',     'icon' => 'message-circle', 'can' => 'academy.manage', 'order' => 45,
            'badge' => static fn(): int => can('academy.manage') ? \App\Services\Academy\LearnerMessages::unreadForAdmin() : 0, 'badge_tone' => 'yellow'],
        ['key' => 'academy_certificates', 'label' => 'Certificates',    'url' => 'admin/academy/certificates', 'icon' => 'award',     'can' => 'academy.manage', 'order' => 50, 'badge' => null],
        ['key' => 'academy_payments',     'label' => 'Payments',        'url' => 'admin/academy/payments',     'icon' => 'credit-card', 'can' => 'academy.manage', 'order' => 52,
            'badge' => static fn(): int => can('academy.manage') && \App\Services\Academy\CertificatePayments::ready() ? (int) \App\Core\Database::value("SELECT COUNT(*) FROM academy_payments WHERE status = 'review'", [], 0) : 0, 'badge_tone' => 'yellow'],
        ['key' => 'academy_challenges',   'label' => 'Challenges',      'url' => 'admin/academy/challenges',   'icon' => 'target',    'can' => 'academy.teach',  'order' => 35, 'badge' => null],
        ['key' => 'academy_applications', 'label' => 'Facilitator applications', 'url' => 'admin/academy/facilitator-applications', 'icon' => 'user-plus', 'can' => 'academy.manage', 'order' => 55, 'badge' => null],
        ['key' => 'academy_facilitators','label' => 'Facilitators',    'url' => 'admin/academy/facilitators', 'icon' => 'user-plus', 'can' => 'academy.manage', 'order' => 60, 'badge' => null],
    ],
];
