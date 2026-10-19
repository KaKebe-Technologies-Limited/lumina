<?php
/**
 * ADMIN routes of the PDF side of the newsletter (docs/PARTNERS_NEWSLETTER.md sections 5 and 8). Inside `auth` + newsletter.manage.
 * Loaded after admin_newsletter.php; the paths below do not overlap its routes.
 *   POST   /admin/api/newsletters/{id}/pdf    upload or replace the PDF (multipart "pdf")
 *   DELETE /admin/api/newsletters/{id}/pdf    remove it
 *   GET    /admin/api/newsletters/{id}/live   "Live on the website" status
 *   GET    /admin/newsletters/{id}/pdf        the stored PDF inline (admin preview, any status)
 */

use App\Controllers\Admin\NewsletterPdfApiController;

$router->group(['prefix' => 'admin', 'middleware' => ['auth', 'can:newsletter.manage']], function ($router) {
    $router->post('/api/newsletters/{id}/pdf', [NewsletterPdfApiController::class, 'upload'])->middleware('throttle:60,60')->name('admin.api.newsletters.pdf');
    $router->delete('/api/newsletters/{id}/pdf', [NewsletterPdfApiController::class, 'remove'])->name('admin.api.newsletters.pdf.remove');
    $router->get('/api/newsletters/{id}/live', [NewsletterPdfApiController::class, 'live'])->name('admin.api.newsletters.live');
    $router->get('/newsletters/{id}/pdf', [NewsletterPdfApiController::class, 'file'])->name('admin.newsletters.pdf');
});
