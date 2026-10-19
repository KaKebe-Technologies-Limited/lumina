<?php
/**
 * Admin sidebar fragment: FORMS (the form builder and the answers). Group order 38 (core=10, content=20, org=30, membership=35, calendar=36,
 * newsletter=37, inbox=40). The badge on "Forms" is the number of NEW (unread) submissions across all forms: one cheap COUNT(*), memoised per request
 * (SubmissionService::newCount; a missing table gives 0). Items need forms.manage; a role that only has forms.submissions sees "Form answers".
 * Contract: docs/FORMS.md section 16.
 */

use App\Services\Forms\SubmissionService;

return [
    'key'   => 'forms',
    'label' => 'Forms',
    'order' => 38,
    'items' => [
        ['key' => 'forms', 'label' => 'Forms', 'url' => 'admin/forms', 'icon' => 'file-text', 'can' => 'forms.manage', 'order' => 10,
         'badge' => static fn(): int => SubmissionService::newCount()],
        ['key' => 'form_create', 'label' => 'New form', 'url' => 'admin/forms/new', 'icon' => 'plus', 'can' => 'forms.manage', 'order' => 20, 'badge' => null],
        ['key' => 'form_answers', 'label' => 'Form answers', 'url' => 'admin/form-answers', 'icon' => 'inbox', 'can' => 'forms.submissions', 'order' => 30,
         'badge' => static fn(): int => can('forms.manage') ? 0 : SubmissionService::newCount()],
    ],
];
