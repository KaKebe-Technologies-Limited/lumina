<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\AdminController;
use App\Core\Database;
use App\Core\Flash;
use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Models\Newsletter;
use App\Models\SocialImage;
use App\Services\Newsletter\NewsletterService;
use App\Services\Social\CaptionBuilder;
use App\Services\Social\Channels;
use App\Services\Social\ShareHook;
use App\Services\Social\SocialCard;
use App\Services\Social\SocialConfig;

/**
 * ADMIN > Social media > Share kit (docs/SOCIAL.md section 8). Ability social.share. OWNER: IMAGES agent.
 *
 * The page: the issue link (plain and with the tracking tags of each platform), the three branded pictures (square 1080x1080, portrait 1080x1920, landscape 1200x630) with
 * Download buttons, ready-to-copy captions of every platform (TikTok included: it has no automatic channel), hashtag suggestions and Copy buttons (assets/js/share-kit.js).
 * Works without JavaScript and without GD (then the cover photo, else the logo, is offered as it is).
 *
 *   GET admin/social/kit                              redirect to the kit of the newest public issue (flash when there is none)
 *   GET admin/social/kit/{id}                         the kit of one issue (any status; a draft shows a notice that links only work once it is public)
 *   GET admin/social/kit/{id}/download/{variant}      the image as a download (Content-Disposition: attachment; filename lumina-issue-<n>-<variant>.jpg), any status, no-store
 *   GET admin/social/preview/{id}/{variant}           the same picture inline for the share panel thumbnail, any status, session-gated, no-store
 */
final class SocialKitController extends AdminController
{
    /** the picture each caption goes with */
    private const IMAGE_FOR = ['facebook' => 'square', 'instagram' => 'square', 'x' => 'square', 'linkedin' => 'landscape', 'telegram' => 'square', 'webhook' => 'square', 'tiktok' => 'portrait'];

    public function index(Request $request): Response
    {
        $this->authorize('social.share');
        [$sql, $params] = NewsletterService::visibleSql('n');
        $id = (int) Database::value("SELECT n.id FROM newsletters n WHERE {$sql} ORDER BY " . NewsletterService::PUBLIC_ORDER . ' LIMIT 1', $params, 0);
        if ($id < 1 && can('newsletter.manage')) {                  // no public issue yet: somebody who edits newsletters may prepare the kit of a draft
            $id = (int) Database::value('SELECT id FROM newsletters ORDER BY issue_date DESC, id DESC LIMIT 1', [], 0);
        }
        if ($id < 1) {
            if (!can('newsletter.manage')) {
                Flash::info('No newsletter issue is public yet: the Share kit is made from a published issue.');
                return $this->redirect('admin/social/log');
            }
            Flash::info('Write a newsletter issue first: the Share kit is made from an issue.');
            return $this->redirect('admin/newsletters');
        }
        return $this->redirect('admin/social/kit/' . $id);
    }

    public function show(Request $request, int $id): Response
    {
        $this->authorize('social.share');
        $row = Newsletter::find($id);
        if (!ShareHook::mayUse($row)) {
            throw new HttpException(404, 'Not found');
        }
        $issue = ShareHook::issueForShare($row);
        $live = NewsletterService::isLive($row);
        $canRender = SocialCard::available();
        $captions = [];
        foreach (array_merge(Channels::ids(), Channels::KIT_ONLY) as $ch) {
            $captions[$ch] = ['label' => $ch === 'webhook' ? 'Any other platform (generic caption)' : Channels::label($ch), 'image' => self::IMAGE_FOR[$ch] ?? 'square']
                + CaptionBuilder::build($ch, $issue);
        }
        $images = [];
        foreach (SocialCard::VARIANTS as $v) {
            [$w, $h] = SocialImage::SIZES[$v];
            $images[$v] = [
                'variant' => $v, 'width' => $w, 'height' => $h, 'use' => SocialCard::USE[$v],
                'preview' => SocialCard::adminUrl($id, $v), 'download' => url('admin/social/kit/' . $id . '/download/' . $v),
                'public' => $live && $canRender ? (SocialCard::ensure($id, $v)['url'] ?? '') : '',
            ];
        }
        $tracked = [];
        foreach (['facebook', 'instagram', 'x', 'linkedin', 'telegram', 'tiktok', 'whatsapp'] as $ch) {
            $tracked[$ch] = ['label' => $ch === 'x' ? 'X' : ($ch === 'whatsapp' ? 'WhatsApp' : Channels::label($ch)), 'url' => CaptionBuilder::utmUrl($issue['url'], $ch, true)];
        }
        return $this->render('admin/social/kit', [
            'issue' => $issue, 'row' => $row, 'live' => $live, 'captions' => $captions, 'images' => $images, 'tracked' => $tracked,
            'url' => $issue['url'], 'hashtags' => CaptionBuilder::hashtagList(SocialConfig::str('social_hashtags'), 30), 'variants' => SocialCard::VARIANTS, 'canRender' => $canRender,
            'countUrl' => url('admin/api/newsletters/' . $id . '/share/count'), 'designUrl' => can('social.manage') ? url('admin/social/templates') : '',
        ], ['title' => 'Share kit', 'nav' => 'social_kit', 'breadcrumbs' => [['Social media', can('social.manage') ? url('admin/social') : null], ['Share kit', null]]]);
    }

    public function download(Request $request, int $id, string $variant): Response
    {
        $this->authorize('social.share');
        return $this->picture($id, $variant, true);
    }

    public function preview(Request $request, int $id, string $variant): Response
    {
        $this->authorize('social.share');
        return $this->picture($id, $variant, false);
    }

    /** One picture for ANY status of the issue; session-gated (the routes are inside the auth group); never cached. */
    private function picture(int $id, string $variant, bool $attachment): Response
    {
        if (!in_array($variant, SocialCard::VARIANTS, true)) {
            throw new HttpException(404, 'Not found');
        }
        $row = Newsletter::find($id);
        if (!ShareHook::mayUse($row)) {
            throw new HttpException(404, 'Not found');
        }
        $img = SocialCard::adminImage($row, $variant) ?? throw new HttpException(404, 'Not found');
        $label = isset($row['issue_number']) && $row['issue_number'] !== null ? (string) (int) $row['issue_number'] : 'id' . $id;
        $name = 'lumina-issue-' . $label . '-' . $variant . '.' . $img['ext'];
        return new Response($img['bytes'], 200, [
            'Content-Type'           => $img['mime'],
            'Content-Length'         => (string) strlen($img['bytes']),
            'Content-Disposition'    => ($attachment ? 'attachment' : 'inline') . '; filename="' . $name . '"',
            'Cache-Control'          => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
