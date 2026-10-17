<?php
/**
 * ADMIN newsletter routes (pages + JSON API). Contract: docs/NEWSLETTER.md section 7. Everything is inside the `auth` group.
 * Abilities: newsletter.manage (admin + editor) for the editor side; newsletter.send (ADMIN ONLY) for everything about e-mailing;
 * newsletter.delete_published (admin) is checked inside the delete handlers. Controllers ALSO call $this->authorize() (defence in depth).
 * CSRF is automatic on POST/PUT/DELETE: JSON clients send the header X-CSRF-TOKEN (and Accept: application/json).
 *
 * Pages (EDITOR agent unless noted)
 *   GET  /admin/newsletters                         issues list (status filter, search)
 *   GET  /admin/newsletters/create | POST /admin/newsletters      new draft
 *   GET  /admin/newsletters/{id}/edit               the two-pane editor
 *   GET  /admin/newsletters/{id}/preview            full-page preview (banner, noindex, no-store)
 *   POST /admin/newsletters/{id}/duplicate | /delete
 *   GET  /admin/newsletters/{id}/email              e-mail panel (MAILING agent, admin only)
 * JSON API (EDITOR agent): /admin/api/newsletters ... list, create, show, save (PUT, optimistic locking), delete, preview, upload, revisions,
 *   restore, publish, schedule, unpublish, archive, duplicate
 * JSON API (MAILING agent, admin only): /admin/api/newsletters/{id}/email ... status, settings, test, queue, batch, pause, resume, cancel, deliveries, retry
 */

use App\Controllers\Admin\NewsletterApiController;
use App\Controllers\Admin\NewsletterMailController;
use App\Controllers\Admin\NewslettersController;

$router->group(['prefix' => 'admin', 'middleware' => ['auth']], function ($router) {
    $router->group(['middleware' => ['can:newsletter.manage']], function ($router) {
        // ---- pages
        $router->get('/newsletters', [NewslettersController::class, 'index'])->name('admin.newsletters');
        $router->get('/newsletters/create', [NewslettersController::class, 'create'])->name('admin.newsletters.create');
        $router->post('/newsletters', [NewslettersController::class, 'store'])->name('admin.newsletters.store');
        $router->get('/newsletters/{id}/edit', [NewslettersController::class, 'edit'])->name('admin.newsletters.edit');
        $router->get('/newsletters/{id}/preview', [NewslettersController::class, 'preview'])->name('admin.newsletters.preview');
        $router->post('/newsletters/{id}/duplicate', [NewslettersController::class, 'duplicate'])->name('admin.newsletters.duplicate');
        $router->post('/newsletters/{id}/delete', [NewslettersController::class, 'destroy'])->name('admin.newsletters.delete');

        // ---- JSON API (static segments first; {id} is digits only)
        $router->get('/api/newsletters', [NewsletterApiController::class, 'index'])->name('admin.api.newsletters');
        $router->post('/api/newsletters', [NewsletterApiController::class, 'store'])->name('admin.api.newsletters.store');
        $router->post('/api/newsletters/preview', [NewsletterApiController::class, 'preview'])->middleware('throttle:120,60')->name('admin.api.newsletters.preview');
        $router->post('/api/newsletters/upload', [NewsletterApiController::class, 'upload'])->middleware('throttle:60,60')->name('admin.api.newsletters.upload');
        $router->get('/api/newsletters/{id}', [NewsletterApiController::class, 'show'])->name('admin.api.newsletters.show');
        $router->put('/api/newsletters/{id}', [NewsletterApiController::class, 'save'])->name('admin.api.newsletters.save');
        $router->delete('/api/newsletters/{id}', [NewsletterApiController::class, 'destroy'])->name('admin.api.newsletters.delete');
        $router->get('/api/newsletters/{id}/revisions', [NewsletterApiController::class, 'revisions'])->name('admin.api.newsletters.revisions');
        $router->get('/api/newsletters/{id}/revisions/{rid}', [NewsletterApiController::class, 'revision'])->where('rid', '\d+')->name('admin.api.newsletters.revision');
        $router->post('/api/newsletters/{id}/revisions/{rid}/restore', [NewsletterApiController::class, 'restore'])->where('rid', '\d+')->name('admin.api.newsletters.restore');
        $router->post('/api/newsletters/{id}/publish', [NewsletterApiController::class, 'publish'])->name('admin.api.newsletters.publish');
        $router->post('/api/newsletters/{id}/schedule', [NewsletterApiController::class, 'schedule'])->name('admin.api.newsletters.schedule');
        $router->post('/api/newsletters/{id}/unpublish', [NewsletterApiController::class, 'unpublish'])->name('admin.api.newsletters.unpublish');
        $router->post('/api/newsletters/{id}/archive', [NewsletterApiController::class, 'archive'])->name('admin.api.newsletters.archive');
        $router->post('/api/newsletters/{id}/duplicate', [NewsletterApiController::class, 'duplicate'])->name('admin.api.newsletters.duplicate');
    });

    // ---- e-mailing: ADMIN ONLY
    $router->group(['middleware' => ['can:newsletter.send']], function ($router) {
        $router->get('/newsletters/{id}/email', [NewsletterMailController::class, 'panel'])->name('admin.newsletters.email');
        $router->get('/api/newsletters/{id}/email', [NewsletterMailController::class, 'status'])->name('admin.api.newsletters.email');
        $router->put('/api/newsletters/{id}/email', [NewsletterMailController::class, 'saveSettings'])->name('admin.api.newsletters.email.save');
        $router->post('/api/newsletters/{id}/email/test', [NewsletterMailController::class, 'test'])->middleware('throttle:30,600')->name('admin.api.newsletters.email.test');
        $router->post('/api/newsletters/{id}/email/queue', [NewsletterMailController::class, 'queue'])->name('admin.api.newsletters.email.queue');
        $router->post('/api/newsletters/{id}/email/batch', [NewsletterMailController::class, 'batch'])->name('admin.api.newsletters.email.batch');
        $router->post('/api/newsletters/{id}/email/pause', [NewsletterMailController::class, 'pause'])->name('admin.api.newsletters.email.pause');
        $router->post('/api/newsletters/{id}/email/resume', [NewsletterMailController::class, 'resume'])->name('admin.api.newsletters.email.resume');
        $router->post('/api/newsletters/{id}/email/cancel', [NewsletterMailController::class, 'cancel'])->name('admin.api.newsletters.email.cancel');
        $router->get('/api/newsletters/{id}/email/deliveries', [NewsletterMailController::class, 'deliveries'])->name('admin.api.newsletters.email.deliveries');
        $router->post('/api/newsletters/{id}/email/retry', [NewsletterMailController::class, 'retry'])->name('admin.api.newsletters.email.retry');
    });
});
