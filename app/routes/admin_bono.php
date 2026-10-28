<?php
/**
 * ADMIN routes - Bono AI (docs/BONO.md): learning status, "learn now", taught answers and the questions visitors asked. Ability settings.manage.
 * CSRF is verified by the router for every POST. $router is in scope.
 */

use App\Controllers\Admin\BonoController;

$router->group(['prefix' => 'admin/bono', 'middleware' => ['auth', 'can:settings.manage']], function ($router) {
    $router->get('', [BonoController::class, 'index'])->name('admin.bono');
    $router->post('/learn', [BonoController::class, 'learn'])->middleware('throttle:6,60')->name('admin.bono.learn');
    $router->post('/teach', [BonoController::class, 'teach'])->middleware('throttle:60,60')->name('admin.bono.teach');
    $router->post('/taught/{id}/toggle', [BonoController::class, 'toggle'])->name('admin.bono.toggle');
    $router->post('/taught/{id}/delete', [BonoController::class, 'delete'])->name('admin.bono.delete');
    $router->post('/dismiss/{id}', [BonoController::class, 'dismiss'])->name('admin.bono.dismiss');
});
