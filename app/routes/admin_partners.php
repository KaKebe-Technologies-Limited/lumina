<?php
/**
 * ADMIN JSON routes of the fast Partners screen (docs/PARTNERS_NEWSLETTER.md section 6). OWNER of the handlers: PARTNERS agent
 * (App\Controllers\Admin\PartnerApiController). The classic HTML routes stay in admin_org.php and keep working.
 * All inside `auth` + content.manage; static segments are listed before the {id} ones.
 */

use App\Controllers\Admin\PartnerApiController;

$router->group(['prefix' => 'admin', 'middleware' => ['auth', 'can:content.manage']], function ($router) {
    $router->get('/api/partners', [PartnerApiController::class, 'index'])->name('admin.api.partners');
    $router->post('/api/partners', [PartnerApiController::class, 'store'])->middleware('throttle:60,60')->name('admin.api.partners.store');
    $router->post('/api/partners/reorder', [PartnerApiController::class, 'reorder'])->name('admin.api.partners.reorder');
    $router->post('/api/partners/bulk', [PartnerApiController::class, 'bulk'])->name('admin.api.partners.bulk');
    $router->post('/api/partners/optimise', [PartnerApiController::class, 'optimise'])->middleware('throttle:60,60')->name('admin.api.partners.optimise');
    $router->post('/api/partners/{id}', [PartnerApiController::class, 'update'])->name('admin.api.partners.update');
    $router->post('/api/partners/{id}/logo', [PartnerApiController::class, 'logo'])->middleware('throttle:60,60')->name('admin.api.partners.logo');
    $router->post('/api/partners/{id}/logo/remove', [PartnerApiController::class, 'logoRemove'])->name('admin.api.partners.logo.remove');
    $router->post('/api/partners/{id}/toggle', [PartnerApiController::class, 'toggle'])->name('admin.api.partners.toggle');
    $router->post('/api/partners/{id}/delete', [PartnerApiController::class, 'destroy'])->name('admin.api.partners.delete');
});
