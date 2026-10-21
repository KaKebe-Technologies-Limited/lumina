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
        ['key' => 'academy_classes',      'label' => 'Virtual classes', 'url' => 'admin/academy/classes',      'icon' => 'video',     'can' => 'academy.teach',  'order' => 30, 'badge' => null],
        ['key' => 'academy_learners',     'label' => 'Learners',        'url' => 'admin/academy/learners',     'icon' => 'users',     'can' => 'academy.teach',  'order' => 40, 'badge' => null],
        ['key' => 'academy_certificates', 'label' => 'Certificates',    'url' => 'admin/academy/certificates', 'icon' => 'award',     'can' => 'academy.manage', 'order' => 50, 'badge' => null],
        ['key' => 'academy_facilitators', 'label' => 'Facilitators',    'url' => 'admin/academy/facilitators', 'icon' => 'user-plus', 'can' => 'academy.manage', 'order' => 60, 'badge' => null],
    ],
];
