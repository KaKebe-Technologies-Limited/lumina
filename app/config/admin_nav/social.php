<?php
/**
 * Admin sidebar fragment: SOCIAL MEDIA (profile links, auto-share channels, post templates, share log, share kit). Group order 37 (right after Newsletter, whose file sorts
 * first). Items follow the abilities: Profile links / Auto-share channels / Post templates need social.manage (admin only by default: they hold the API keys), the Share log
 * and the Share kit need social.share (admin + editor). The badge counts what needs a person: failed posts, posts that need review, channels that need a new token,
 * tokens about to expire (SocialConfig::attention, one cheap query per request, a missing table gives 0). Contract: docs/SOCIAL.md.
 */

use App\Services\Social\SocialConfig;

return [
    'key'   => 'social',
    'label' => 'Social media',
    'order' => 37,
    'items' => [
        ['key' => 'social_profiles', 'label' => 'Profile links', 'url' => 'admin/social', 'icon' => 'link', 'can' => 'social.manage', 'order' => 10, 'badge' => null],
        ['key' => 'social_channels', 'label' => 'Auto-share channels', 'url' => 'admin/social/channels', 'icon' => 'send', 'can' => 'social.manage', 'order' => 20,
         'badge' => static function (): int {
             $a = SocialConfig::attention();
             return count($a['needs_token']) + count($a['expiring']);
         }],
        ['key' => 'social_templates', 'label' => 'Post templates', 'url' => 'admin/social/templates', 'icon' => 'edit', 'can' => 'social.manage', 'order' => 30, 'badge' => null],
        ['key' => 'social_log', 'label' => 'Share log', 'url' => 'admin/social/log', 'icon' => 'history', 'can' => 'social.share', 'order' => 40,
         'badge' => static function (): int {
             $a = SocialConfig::attention();
             return $a['failed'] + $a['review'] + count($a['needs_token']);
         }],
        ['key' => 'social_kit', 'label' => 'Share kit', 'url' => 'admin/social/kit', 'icon' => 'image', 'can' => 'social.share', 'order' => 50, 'badge' => null],
    ],
];
