<?php
/**
 * Admin sidebar fragment: INBOX. OWNER: B5 "admin-inbox" (contact messages, subscribers, involvement, applications, donations).
 * The badges are the "unread / needs attention" counters: closures that read App\Services\Inbox\Counts (ONE query for all four,
 * memoised per request; AdminNav hides a badge that is 0/null and silently ignores a failing closure). Items need `inbox.manage`.
 */

use App\Services\Inbox\Counts;

return [
    'key'   => 'inbox',
    'label' => 'Inbox',
    'order' => 40,
    'items' => [
        ['key' => 'messages', 'label' => 'Messages', 'url' => 'admin/messages', 'icon' => 'mail', 'can' => 'inbox.manage', 'order' => 10,
         'badge' => static fn(): int => Counts::get('messages')],
        ['key' => 'subscribers', 'label' => 'Subscribers', 'url' => 'admin/subscribers', 'icon' => 'user-plus', 'can' => 'inbox.manage', 'order' => 20,
         'badge' => null],
        ['key' => 'involvement', 'label' => 'Involvement requests', 'url' => 'admin/involvement', 'icon' => 'heart', 'can' => 'inbox.manage', 'order' => 30,
         'badge' => static fn(): int => Counts::get('involvement')],
        ['key' => 'ambassadors', 'label' => 'Ambassadors', 'url' => 'admin/ambassadors', 'icon' => 'globe', 'can' => 'inbox.manage', 'order' => 35,
         'badge' => static function (): int {
             try {
                 return (int) \App\Core\Database::value("SELECT COUNT(*) FROM ambassador_applications WHERE status = 'new'", [], 0);
             } catch (\Throwable) {
                 return 0;                                   // the table is not imported yet
             }
         }],
        ['key' => 'applications', 'label' => 'Job applications', 'url' => 'admin/applications', 'icon' => 'briefcase', 'can' => 'inbox.manage', 'order' => 40,
         'badge' => static fn(): int => Counts::get('applications')],
        ['key' => 'donations', 'label' => 'Donations', 'url' => 'admin/donations', 'icon' => 'banknote', 'can' => 'inbox.manage', 'order' => 50,
         'badge' => static fn(): int => Counts::get('donations')],
    ],
];
