<?php
/**
 * Plain-language catalogue of the abilities an administrator can tick when creating a ROLE (Admin > Roles).
 * Ability keys are the ones in app/config/app.php 'abilities' (each route / button checks one of them). 'locked' = every role always has it;
 * 'admin_only' = never assignable to a custom role (it would let the holder create administrators or change roles).
 * group => [label, items => ability => [label, help]]
 */
return [
    'basics' => [
        'label' => 'Basics',
        'items' => [
            'dashboard.view' => ['Sign in and see the dashboard', 'Every role has this.', 'locked' => true],
        ],
    ],
    'content' => [
        'label' => 'Website content',
        'items' => [
            'pages.edit'     => ['Edit page text and custom pages', 'Headings, paragraphs, images, buttons and SEO on every page, and create custom pages.'],
            'pages.delete'   => ['Delete custom pages', null],
            'content.manage' => ['Manage content items', 'Programs, projects, blog and news posts, activities, gallery, jobs, team, partners, documents, events, stories, FAQs and impact figures.'],
            'content.delete' => ['Delete content items', null],
            'menus.manage'   => ['Change the website menus', null],
        ],
    ],
    'inbox' => [
        'label' => 'Messages and people',
        'items' => [
            'inbox.manage' => ['Handle the inbox', 'Contact messages, subscribers, involvement requests, job applications, donations and event registrations.'],
            'inbox.delete' => ['Delete inbox items', null],
            'export.csv'   => ['Download CSV exports', 'Lists of people and records.'],
        ],
    ],
    'membership' => [
        'label' => 'Membership',
        'items' => [
            'members.manage' => ['Manage members', 'View applications, approve or reject, record payments, receipts and notes.'],
            'members.delete' => ['Delete member records', null],
            'members.types'  => ['Set membership types and fees', 'Create types and change the fees.'],
        ],
    ],
    'newsletter' => [
        'label' => 'Newsletter',
        'items' => [
            'newsletter.manage'           => ['Write and publish newsletters', 'Create, edit, preview, schedule and publish issues (written or PDF).'],
            'newsletter.delete_published' => ['Delete published newsletters', null],
            'newsletter.send'             => ['Email newsletters to subscribers', 'Test sends and sending to the whole list.'],
        ],
    ],
    'calendar' => [
        'label' => 'Calendar and meetings',
        'items' => [
            'calendar.manage'   => ['Manage the calendar', 'Add and edit dates, confirm holiday dates, import and export.'],
            'calendar.advanced' => ['Use advanced repeat rules', null],
            'calendar.delete'   => ['Bulk-delete calendar entries', null],
            'meetings.manage'   => ['Schedule meetings and invite people', 'Meeting links, invitations, reminders and meeting requests.'],
            'meetings.bulk'     => ['Invite large groups at once', 'More than 50 recipients, such as all members.'],
            'meetings.delete'   => ['Delete meeting requests', null],
        ],
    ],
    'site' => [
        'label' => 'Website administration',
        'items' => [
            'settings.manage' => ['Site settings and website on/off', 'Contact details, donation details, the maintenance switch and other site-wide settings.'],
            'audit.view'      => ['View the activity log', 'Who changed what, and when.'],
        ],
    ],
    'admin' => [
        'label' => 'Administrators only',
        'items' => [
            'users.manage' => ['Create users and change roles', 'Never available to a custom role.', 'admin_only' => true],
            'roles.manage' => ['Create and edit roles', 'Never available to a custom role.', 'admin_only' => true],
        ],
    ],
];
