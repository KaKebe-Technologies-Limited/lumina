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
}
