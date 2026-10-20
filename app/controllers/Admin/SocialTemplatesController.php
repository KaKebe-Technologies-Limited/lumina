<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\AdminController;
use App\Core\Database;
use App\Core\Flash;
use App\Core\Request;
use App\Core\Response;
use App\Models\Newsletter;
use App\Services\Social\CaptionBuilder;
use App\Services\Social\Channels;
use App\Services\Social\ShareHook;
use App\Services\Social\SocialCard;
use App\Services\Social\SocialConfig;

/**
 * ADMIN > Social media > Post templates (docs/SOCIAL.md section 7). Ability social.manage. OWNER: ADMIN agent builds the final screen (live counter, placeholder buttons);
 * the architect delivered working controller logic and a plain view. All rules live in SocialConfig::saveTemplates / CaptionBuilder.
 *
 *   GET  admin/social/templates           one template per channel (+ TikTok for the Share kit), the default hashtags, the UTM option, "share by default" per channel
 *   POST admin/social/templates           templates[<channel|tiktok>], default_on[<channel>], hashtags, utm. A template is test-built with a sample issue: known placeholders only,
 *                                         {title} or {summary} present, within the channel limit after shortening. Empty = back to the built-in template.
 *   POST admin/social/templates/preview   JSON {channel, template, newsletter_id?} -> {text, length, limit, over, trimmed, unknown, counting, soft}  (the live counter)
 */
final class SocialTemplatesController extends AdminController
{
    public function index(Request $request): Response
    {
        $this->authorize('social.manage');
        $rows = [];
        $sample = CaptionBuilder::sampleIssue();
        foreach (array_merge(Channels::ids(), Channels::KIT_ONLY) as $ch) {
            $meta = Channels::has($ch) ? Channels::get($ch)->meta() : ['label' => Channels::label($ch), 'icon' => 'tiktok', 'limit' => CaptionBuilder::limit($ch), 'soft' => 0, 'count' => 'plain', 'links' => 'none'];
            $tpl = SocialConfig::template($ch);
            $rows[$ch] = [
                'meta' => $meta, 'template' => $tpl, 'default' => CaptionBuilder::defaultTemplate($ch), 'custom' => trim(SocialConfig::str('social_tpl_' . $ch)) !== '',
                'default_on' => Channels::has($ch) ? SocialConfig::defaultOn($ch) : null, 'sample' => CaptionBuilder::build($ch, $sample, $tpl),
            ];
        }
        return $this->render('admin/social/templates', [
            'rows' => $rows, 'placeholders' => CaptionBuilder::PLACEHOLDERS, 'hashtags' => SocialConfig::str('social_hashtags'), 'utm' => SocialConfig::bool('social_utm'),
            'design' => SocialCard::designer(), 'designDefaults' => SocialConfig::CARD_DEFAULTS, 'designTagline' => SocialConfig::str('social_card_tagline'),
            'previewId' => (int) Database::value('SELECT id FROM newsletters ORDER BY issue_date DESC, id DESC LIMIT 1', [], 0), 'canRender' => SocialCard::available(),
        ], ['title' => 'Post templates', 'nav' => 'social_templates', 'breadcrumbs' => [['Social media', url('admin/social')], ['Post templates', null]]]);
    }

    public function save(Request $request): Response
    {
        $this->authorize('social.manage');
        $in = $request->post();
        $in['utm'] = $request->bool('utm');
        $defaults = [];
        foreach (Channels::ids() as $ch) {
            $defaults[$ch] = $request->bool('default_on_' . $ch);
        }
        $in['default_on'] = $defaults;
        $tpls = [];
        foreach (array_merge(Channels::ids(), Channels::KIT_ONLY) as $ch) {
            if ($request->has('tpl_' . $ch)) {
                $tpls[$ch] = $request->post('tpl_' . $ch);
            }
        }
        $in['templates'] = $tpls + (is_array($in['templates'] ?? null) ? $in['templates'] : []);
        $res = SocialConfig::saveTemplates($in, $this->userId());
        if (!$res['ok']) {
            $errors = [];
            foreach ($res['errors'] as $k => $msg) {
                $errors[Channels::has((string) $k) || in_array($k, Channels::KIT_ONLY, true) ? 'tpl_' . $k : (string) $k] = $msg;
            }
            if ($request->expectsJson()) {
                return $this->json(['error' => 'validation', 'message' => (string) reset($errors), 'errors' => (object) $errors], 422);
            }
            $this->fail($errors, 'admin/social/templates');
        }
        if ($request->expectsJson()) {
            return $this->json(['ok' => true, 'warnings' => (object) $res['warnings']]);
        }
        foreach ($res['warnings'] as $w) {
            Flash::warning($w);
        }
        Flash::success('Post templates saved.');
        return $this->redirect('admin/social/templates');
    }

    /** POST admin/social/design: colours, tagline and switches of the share pictures (or reset=1 for the brand defaults). */
    public function saveDesign(Request $request): Response
    {
        $this->authorize('social.manage');
        $in = $request->post();
        $in['show_logo'] = $request->bool('show_logo');
        $in['show_url'] = $request->bool('show_url');
        $in['reset'] = $request->bool('reset');
        $res = SocialConfig::saveCard($in, $this->userId());
        if (!$res['ok']) {
            $this->fail($res['errors'], 'admin/social/templates');
        }
        Flash::success($in['reset'] ? 'The share picture design is back to the brand colours.' : 'Share picture design saved. Pictures of issues that are already public are redrawn the next time they are opened, at the same address.');
        return $this->redirect('admin/social/templates#design');
    }

    public function preview(Request $request): Response
    {
        $this->authorize('social.manage');
        $ch = $request->string('channel');
        if (!Channels::has($ch) && !in_array($ch, Channels::KIT_ONLY, true)) {
            return $this->json(['error' => 'bad_request', 'message' => 'Unknown channel.'], 400);
        }
        $tpl = $request->post('template');
        $tpl = is_string($tpl) ? trim(str_replace("\r\n", "\n", $tpl)) : '';
        $row = $request->int('newsletter_id') > 0 ? Newsletter::find($request->int('newsletter_id')) : null;
        $issue = $row !== null ? ShareHook::issueForShare($row) : CaptionBuilder::sampleIssue();
        $b = CaptionBuilder::build($ch, $issue, $tpl !== '' ? $tpl : null);
        return $this->json($b + ['counting' => CaptionBuilder::counting($ch), 'soft' => Channels::has($ch) ? (int) Channels::get($ch)->meta()['soft'] : 0]);
    }
}
