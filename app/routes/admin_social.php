<?php
/**
 * ADMIN social-media routes (pages + JSON). Contract: docs/SOCIAL.md section 9. Everything is inside the `auth` group. CSRF is automatic on POST/PUT/DELETE: JSON clients send
 * the header X-CSRF-TOKEN and Accept: application/json. Controllers ALSO call $this->authorize() (defence in depth).
 *
 * social.manage (admin only by default: it holds the API keys and tokens)
 *   GET  /admin/social                                profile links form                                   SocialController::index            (ADMIN agent)
 *   POST /admin/social                                save the profile links                               SocialController::save
 *   GET  /admin/social/channels                       the channel cards                                    SocialChannelsController::index
 *   POST /admin/social/channels/global                global switch, hashtags, UTM, API versions           SocialChannelsController::saveGlobal
 *   POST /admin/social/channels/{channel}             save one channel (fields, secrets, on/off)           SocialChannelsController::save
 *   POST /admin/social/channels/{channel}/clear-secret   remove one stored secret (field `name`)            SocialChannelsController::clearSecret
 *   POST /admin/social/channels/{channel}/test        TEST CONNECTION (read-only), JSON                    SocialChannelsController::test
 *   POST /admin/social/channels/{channel}/send-test   SEND A TEST POST (needs confirm=1), JSON             SocialChannelsController::sendTest
 *   GET  /admin/social/templates | POST               post templates, hashtags, UTM, default switches      SocialTemplatesController
 *   POST /admin/social/templates/preview              JSON: caption preview {channel, template, newsletter_id?}
 *
 * social.share (admin + editor by default)
 *   GET  /admin/social/log                            the share log (filters channel, status)              SocialLogController::index
 *   POST /admin/social/log/{id}/retry | skip | mark-posted | post-anyway                                    SocialLogController
 *   GET  /admin/social/kit                            redirect to the Share kit of the newest public issue SocialKitController (IMAGES agent)
 *   GET  /admin/social/kit/{id}                       the Share kit of one issue
 *   GET  /admin/social/kit/{id}/download/{variant}    download an image (square | portrait | landscape)
 *   GET  /admin/social/preview/{id}/{variant}         admin-only picture preview for ANY status (the panel thumbnail)
 *   JSON for the "Share on social media" panel of the newsletter editor and publish dialog (SocialShareApiController, reference implementation by the architect):
 *   GET  /admin/api/newsletters/{id}/share            the panel
 *   PUT  /admin/api/newsletters/{id}/share            save the choices with the issue
 *   POST /admin/api/newsletters/{id}/share/count      length of a caption as the platform counts it
 *   POST /admin/api/newsletters/{id}/share/now        queue + post the channels never posted (issue must be public)
 *   POST /admin/api/newsletters/{id}/share/again      explicit second post (needs confirm = true)
 *   POST /admin/api/newsletters/{id}/share/dispatch   try the due posts of the issue now
 *   GET  /admin/api/newsletters/{id}/share/status     live status of every channel
 *   POST /admin/api/social/posts/{pid}/retry | skip | mark-posted | post-anyway   (JSON twins of the log buttons)
 */

use App\Controllers\Admin\SocialChannelsController;
use App\Controllers\Admin\SocialController;
use App\Controllers\Admin\SocialKitController;
use App\Controllers\Admin\SocialLogController;
use App\Controllers\Admin\SocialShareApiController;
use App\Controllers\Admin\SocialTemplatesController;

$channelPattern = '(?:facebook|instagram|x|linkedin|telegram|webhook)';

$router->group(['prefix' => 'admin', 'middleware' => ['auth']], function ($router) use ($channelPattern) {
    $router->group(['middleware' => ['can:social.manage']], function ($router) use ($channelPattern) {
        $router->get('/social', [SocialController::class, 'index'])->name('admin.social');
        $router->post('/social', [SocialController::class, 'save'])->name('admin.social.save');
        $router->get('/social/channels', [SocialChannelsController::class, 'index'])->name('admin.social.channels');
        $router->post('/social/channels/global', [SocialChannelsController::class, 'saveGlobal'])->name('admin.social.channels.global');
        $router->post('/social/channels/{channel}', [SocialChannelsController::class, 'save'])->where('channel', $channelPattern)->name('admin.social.channels.save');
        $router->post('/social/channels/{channel}/clear-secret', [SocialChannelsController::class, 'clearSecret'])->where('channel', $channelPattern)->name('admin.social.channels.clear');
        $router->post('/social/channels/{channel}/test', [SocialChannelsController::class, 'test'])->where('channel', $channelPattern)->middleware('throttle:30,600')->name('admin.social.channels.test');
        $router->post('/social/channels/{channel}/send-test', [SocialChannelsController::class, 'sendTest'])->where('channel', $channelPattern)->middleware('throttle:10,600')->name('admin.social.channels.sendtest');
        $router->get('/social/templates', [SocialTemplatesController::class, 'index'])->name('admin.social.templates');
        $router->post('/social/templates', [SocialTemplatesController::class, 'save'])->name('admin.social.templates.save');
        $router->post('/social/design', [SocialTemplatesController::class, 'saveDesign'])->name('admin.social.design.save');
        $router->post('/social/templates/preview', [SocialTemplatesController::class, 'preview'])->middleware('throttle:120,60')->name('admin.social.templates.preview');
    });

    $router->group(['middleware' => ['can:social.share']], function ($router) {
        $router->get('/social/log', [SocialLogController::class, 'index'])->name('admin.social.log');
        $router->post('/social/log/{id}/retry', [SocialLogController::class, 'retry'])->name('admin.social.log.retry');
        $router->post('/social/log/{id}/skip', [SocialLogController::class, 'skip'])->name('admin.social.log.skip');
        $router->post('/social/log/{id}/mark-posted', [SocialLogController::class, 'markPosted'])->name('admin.social.log.markposted');
        $router->post('/social/log/{id}/post-anyway', [SocialLogController::class, 'postAnyway'])->name('admin.social.log.postanyway');

        $router->get('/social/kit', [SocialKitController::class, 'index'])->name('admin.social.kit');
        $router->get('/social/kit/{id}', [SocialKitController::class, 'show'])->name('admin.social.kit.show');
        $router->get('/social/kit/{id}/download/{variant}', [SocialKitController::class, 'download'])->where('variant', '(?:square|portrait|landscape)')->name('admin.social.kit.download');
        $router->get('/social/preview/{id}/{variant}', [SocialKitController::class, 'preview'])->where('variant', '(?:square|portrait|landscape)')->name('admin.social.preview');

        // JSON: the share panel
        $router->get('/api/newsletters/{id}/share', [SocialShareApiController::class, 'panel'])->name('admin.api.share.panel');
        $router->put('/api/newsletters/{id}/share', [SocialShareApiController::class, 'save'])->name('admin.api.share.save');
        $router->post('/api/newsletters/{id}/share/count', [SocialShareApiController::class, 'count'])->middleware('throttle:180,60')->name('admin.api.share.count');
        $router->post('/api/newsletters/{id}/share/now', [SocialShareApiController::class, 'now'])->middleware('throttle:30,600')->name('admin.api.share.now');
        $router->post('/api/newsletters/{id}/share/again', [SocialShareApiController::class, 'again'])->middleware('throttle:30,600')->name('admin.api.share.again');
        $router->post('/api/newsletters/{id}/share/dispatch', [SocialShareApiController::class, 'dispatch'])->middleware('throttle:60,600')->name('admin.api.share.dispatch');
        $router->get('/api/newsletters/{id}/share/status', [SocialShareApiController::class, 'status'])->name('admin.api.share.status');
        $router->post('/api/social/posts/{id}/retry', [SocialShareApiController::class, 'retry'])->name('admin.api.social.retry');
        $router->post('/api/social/posts/{id}/skip', [SocialShareApiController::class, 'skip'])->name('admin.api.social.skip');
        $router->post('/api/social/posts/{id}/mark-posted', [SocialShareApiController::class, 'markPosted'])->name('admin.api.social.markposted');
        $router->post('/api/social/posts/{id}/post-anyway', [SocialShareApiController::class, 'postAnyway'])->name('admin.api.social.postanyway');
    });
});
