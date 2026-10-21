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
            'content.manage' => ['Manage content items', 'Programs, projects, blog and news posts, activities, gallery, jobs, opportunities (add, upload, approve, and the websites they are read from), team, partners, documents, events, stories, FAQs and impact figures.'],
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
            'members.card'   => ['Design the membership card', 'Change how the membership card looks and reads (logo, colours, wording, number format, card e-mail) and when cards are issued. Issuing or re-sending one member\'s card only needs "Manage members".'],
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
    'social' => [
        'label' => 'Social media',
        'items' => [
            'social.share'  => ['Share newsletters on social media', 'Choose the channels and edit the captions when an issue is published, use the Share kit (images and ready captions), see the share log and retry a failed post.'],
            'social.manage' => ['Social media settings and accounts', 'Profile links, the automatic posting channels (Facebook, Instagram, X, LinkedIn, Telegram, webhook) with their API keys and access tokens, post templates and the share image design. Give this to trusted administrators only.', 'secrets' => true, 'secrets_label' => 'Holds API keys and tokens'],
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
    'forms' => [
        'label' => 'Forms',
        'items' => [
            'forms.manage'      => ['Create and edit forms', 'Build forms from templates (job applications, volunteer sign-up, event RSVP, surveys ...), change their settings and publish, close or archive them.'],
            'forms.submissions' => ['See and handle form answers', 'Read what people sent, change the status, add notes and download uploaded files. Also needed to have the answers themselves e-mailed to staff or to change who gets those e-mails. This is personal information.'],
            'forms.export'      => ['Download form answers as CSV', null],
            'forms.delete'      => ['Delete forms and answers', 'Delete a whole form or single and bulk submissions. Also needed to set or shorten the retention period of a form, because answers older than that are deleted for good.'],
        ],
    ],
    'shop' => [
        'label' => 'Shop',
        'items' => [
            'shop.manage'   => ['Manage shop products', 'Add and edit products, categories, prices, stock and pictures. Nothing here touches orders or payments.'],
            'shop.orders'   => ['See and handle shop orders', 'Read orders, confirm bookings, record and verify payments, change the order status, add notes and print packing slips. Orders contain the names, phone numbers, e-mail addresses and delivery addresses of the people who bought.'],
            'shop.settings' => ['Shop, delivery and payment settings', 'Opens and closes the shop, sets delivery, and holds the PAYMENT settings, including the secret keys for card and mobile-money payments. Give this to trusted administrators only.', 'secrets' => true, 'secrets_label' => 'Holds payment secrets'],
            'shop.export'   => ['Download shop orders as CSV', null],
            'shop.delete'   => ['Delete products and orders', null],
        ],
    ],
    'academy' => [
        'label' => 'LAF Academy',
        'items' => [
            'academy.teach'  => ['Teach in the LAF Academy', 'The facilitator role. Create and edit their own courses (lessons, handouts, quizzes), schedule virtual classes, and see the progress of the learners in those courses.'],
            'academy.manage' => ['Run the LAF Academy', 'Everything in the Academy: every course, learners (verify, deactivate, reset passwords, export), certificates (issue, revoke), facilitators, Academy settings and the starter courses. Give this to trusted administrators only.'],
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
