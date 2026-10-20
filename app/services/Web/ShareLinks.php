<?php
declare(strict_types=1);

namespace App\Services\Web;

/**
 * Social share links for a public URL (no third-party scripts: plain links that open the network's share dialog).
 *   ShareLinks::for(abs_url('blog/my-post'), 'My post')  ->  [['key','label','url','icon'], ...]
 */
final class ShareLinks
{
    /** @return list<array{key:string,label:string,url:string,icon:string}> */
    public static function for(string $absoluteUrl, string $title): array
    {
        $u = rawurlencode($absoluteUrl);
        $t = rawurlencode($title);
        return [
            ['key' => 'facebook', 'label' => 'Facebook', 'icon' => 'facebook', 'url' => 'https://www.facebook.com/sharer/sharer.php?u=' . $u],
            ['key' => 'x', 'label' => 'X', 'icon' => 'twitter', 'url' => 'https://twitter.com/intent/tweet?url=' . $u . '&text=' . $t],
            ['key' => 'linkedin', 'label' => 'LinkedIn', 'icon' => 'linkedin', 'url' => 'https://www.linkedin.com/sharing/share-offsite/?url=' . $u],
            ['key' => 'whatsapp', 'label' => 'WhatsApp', 'icon' => 'whatsapp', 'url' => 'https://wa.me/?text=' . rawurlencode($title . ' ' . $absoluteUrl)],
        ];
    }

    /**
     * The extended row of the public newsletter page (docs/SOCIAL.md section 8): Facebook, X, LinkedIn, WhatsApp, Telegram and e-mail, all plain links.
     * $labels overrides the visible names (the page registry `newsletter` > `share` > label_*), $mailSubject / $mailBody the e-mail text (already expanded).
     * @param array<string,string> $labels key => label
     * @return list<array{key:string,label:string,url:string,icon:string}>
     */
    public static function extended(string $absoluteUrl, string $title, array $labels = [], string $mailSubject = '', string $mailBody = ''): array
    {
        $rows = self::for($absoluteUrl, $title);
        $rows[] = ['key' => 'telegram', 'label' => 'Telegram', 'icon' => 'telegram', 'url' => 'https://t.me/share/url?url=' . rawurlencode($absoluteUrl) . '&text=' . rawurlencode($title)];
        $rows[] = ['key' => 'email', 'label' => 'E-mail', 'icon' => 'mail', 'url' => 'mailto:?subject=' . rawurlencode($mailSubject !== '' ? $mailSubject : $title) . '&body=' . rawurlencode($mailBody !== '' ? $mailBody : $title . "\n" . $absoluteUrl)];
        foreach ($rows as &$r) {
            if (isset($labels[$r['key']]) && trim($labels[$r['key']]) !== '') {
                $r['label'] = trim($labels[$r['key']]);
            }
        }
        unset($r);
        return $rows;
    }
}
