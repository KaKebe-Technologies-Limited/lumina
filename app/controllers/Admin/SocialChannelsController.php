<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\AdminController;
use App\Core\Flash;
use App\Core\Request;
use App\Core\Response;
use App\Services\Shop\SecretStore;
use App\Services\Social\Channels;
use App\Services\Social\PostQueue;
use App\Services\Social\SocialConfig;
use App\Services\Social\SocialDispatcher;
use App\Services\Social\SocialHttp;

/**
 * ADMIN > Social media > Auto-share channels (docs/SOCIAL.md section 3). Ability social.manage (admin only by default: it holds the API keys and tokens).
 * OWNER: ADMIN agent builds the final screen; the architect delivered working controller logic (all rules live in SocialConfig / SocialDispatcher) and a plain view.
 *
 *   GET  admin/social/channels                     one card per channel (SocialConfig::publicView + Channels::get($id)->meta()) + the global options
 *   POST admin/social/channels/global              enabled, utm, hashtags, graph_version, li_version  (SocialConfig::saveGlobal)
 *   POST admin/social/channels/{channel}           fields + enabled + default_on; secrets: empty keeps, a value replaces, clear_<key>=1 removes  (SocialConfig::saveChannel)
 *   POST admin/social/channels/{channel}/clear-secret   field `name` (a secret field key)
 *   POST admin/social/channels/{channel}/test      TEST CONNECTION (read-only). JSON {ok,state,message,detail?,at}; a form post gets a flash + redirect instead
 *   POST admin/social/channels/{channel}/send-test SEND A TEST POST. Needs confirm=1 (the UI asks first); JSON {ok,state,message,remote_url?,at}
 * SECRETS are write-only: never rendered back (only "Set (hidden)" / "Set in the .env file"), never in the old input after a validation error (stripped below), never in the audit
 * trail (field NAMES only), never in a JSON answer or an error text (SocialHttp::redact).
 */
final class SocialChannelsController extends AdminController
{
    public function index(Request $request): Response
    {
        $this->authorize('social.manage');
        SocialConfig::migrateLegacySecrets();                       // the webhook address used to be a plain setting: encrypt it now (idempotent)
        $channels = [];
        foreach (Channels::ids() as $id) {
            $channels[$id] = ['meta' => Channels::get($id)->meta(), 'view' => SocialConfig::publicView($id), 'queued' => (int) \App\Core\Database::value("SELECT COUNT(*) FROM social_posts WHERE channel = ? AND status IN ('queued','review','failed')", [$id], 0)];
        }
        return $this->render('admin/social/channels', [
            'channels'  => $channels,
            'global'    => ['enabled' => SocialConfig::globalOn(), 'utm' => SocialConfig::bool('social_utm'), 'hashtags' => SocialConfig::str('social_hashtags'), 'graph_version' => SocialConfig::str('social_graph_version'), 'li_version' => SocialConfig::str('social_li_version')],
            'canEncrypt' => SecretStore::available(),
            'devBase'   => SocialHttp::usingDevBase(),
            'attention' => SocialConfig::attention(true),
        ], ['title' => 'Auto-share channels', 'nav' => 'social_channels', 'breadcrumbs' => [['Social media', url('admin/social')], ['Auto-share channels', null]]]);
    }

    public function saveGlobal(Request $request): Response
    {
        $this->authorize('social.manage');
        $in = $request->post();
        $in['enabled'] = $request->bool('enabled');            // an unticked box is simply absent
        $in['utm'] = $request->bool('utm');
        $res = SocialConfig::saveGlobal($in, $this->userId());
        if (!$res['ok']) {
            return $this->failed($request, $res['errors']);
        }
        if ($request->expectsJson()) {
            return $this->json(['ok' => true]);
        }
        Flash::success('Social media options saved.');
        return $this->redirect('admin/social/channels');
    }

    public function save(Request $request, string $channel): Response
    {
        $this->authorize('social.manage');
        if (!Channels::has($channel)) {
            throw new \App\Core\HttpException(404, 'Not found');
        }
        $in = $request->post();
        $in['enabled'] = $request->bool('enabled');
        $in['default_on'] = $request->bool('default_on');
        $res = SocialConfig::saveChannel($channel, $in, $this->userId());
        if (!$res['ok']) {
            return $this->failed($request, $res['errors'], $channel);
        }
        // a new token: ONLY the posts that failed because the old credentials were rejected (last 48 hours, issues still public) go back to the queue, for this channel and for
        // the channels that were using the replaced secret (Instagram with the Facebook Page token). The message names the issues.
        $again = [];
        foreach ($res['reset'] as $resetChannel) {
            $q = PostQueue::retryAuthFailures($resetChannel);
            foreach ($q['issues'] as $title) {
                $again[] = Channels::label($resetChannel) . ': ' . $title;
            }
        }
        if ($again !== []) {
            $shown = array_slice($again, 0, 5);
            Flash::info(count($again) . ' post' . (count($again) === 1 ? ' that had failed because of the old token was' : 's that had failed because of the old token were') . ' queued again (' . implode('; ', $shown) . (count($again) > 5 ? '; and ' . (count($again) - 5) . ' more' : '') . '). Other failed posts were left alone: retry them one by one in the Share log.');
        }
        if ($request->expectsJson()) {
            return $this->json(['ok' => true, 'changed' => $res['changed'], 'view' => SocialConfig::publicView($channel)]);
        }
        Flash::success(Channels::label($channel) . ' saved.' . ($res['changed'] === [] ? ' (nothing changed)' : ''));
        return $this->redirect('admin/social/channels#' . $channel);
    }

    public function clearSecret(Request $request, string $channel): Response
    {
        $this->authorize('social.manage');
        $name = $request->string('name');
        $meta = Channels::get($channel)->meta();
        $ok = false;
        foreach ($meta['fields'] as $f) {
            if ($f['secret'] && $f['key'] === $name) {
                $ok = true;
            }
        }
        if (!$ok) {
            return $this->failed($request, ['_' => 'Unknown field.'], $channel);
        }
        $res = SocialConfig::saveChannel($channel, ['clear_' . $name => '1'], $this->userId());
        if ($request->expectsJson()) {
            return $this->json(['ok' => $res['ok'], 'view' => SocialConfig::publicView($channel)]);
        }
        Flash::success('The stored ' . $name . ' of ' . Channels::label($channel) . ' was removed.');
        return $this->redirect('admin/social/channels#' . $channel);
    }

    public function test(Request $request, string $channel): Response
    {
        $this->authorize('social.manage');
        $r = SocialDispatcher::testConnection($channel);
        if ($request->expectsJson()) {
            return $this->json($r)->header('Cache-Control', 'no-store');
        }
        $r['ok'] ? Flash::success(Channels::label($channel) . ': ' . $r['message']) : Flash::error(Channels::label($channel) . ': ' . $r['message']);
        return $this->redirect('admin/social/channels#' . $channel);
    }

    public function sendTest(Request $request, string $channel): Response
    {
        $this->authorize('social.manage');
        if (!$request->bool('confirm')) {
            if ($request->expectsJson()) {
                return $this->json(['error' => 'confirm_required', 'message' => 'A test post is a REAL post on the account. Confirm it first (confirm = 1).'], 422);
            }
            Flash::error('A test post is a real post on the account: tick the confirmation first.');
            return $this->redirect('admin/social/channels#' . $channel);
        }
        $r = SocialDispatcher::sendTest($channel);
        if ($request->expectsJson()) {
            return $this->json($r)->header('Cache-Control', 'no-store');
        }
        $r['ok'] ? Flash::success(Channels::label($channel) . ': ' . $r['message']) : Flash::error(Channels::label($channel) . ': ' . $r['message']);
        return $this->redirect('admin/social/channels#' . $channel);
    }

    /** @param array<string,string> $errors */
    private function failed(Request $request, array $errors, ?string $channel = null): Response
    {
        if ($request->expectsJson()) {
            return $this->json(['error' => 'validation', 'message' => (string) reset($errors), 'errors' => (object) $errors], 422);
        }
        // never put a secret into the flashed old input
        $old = $request->post();
        foreach (Channels::ids() as $id) {
            foreach (Channels::get($id)->meta()['fields'] as $f) {
                if ($f['secret']) {
                    unset($old[$f['key']]);
                }
            }
        }
        \App\Core\FormState::flash($errors, $old);
        Flash::error((string) reset($errors));
        return $this->redirect('admin/social/channels' . ($channel !== null ? '#' . $channel : ''));
    }
}
