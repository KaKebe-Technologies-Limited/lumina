<?php
/**
 * ADMIN routes - OWNER: B3 "admin-core" (login/logout/password, dashboard, settings, pages editor + custom pages, menus, stats, FAQs,
 * users, my profile, audit log, media upload). Everything except login sits inside the 'auth' middleware group.
 * Every controller method also calls authorize('<ability>'), so the route-level 'can:' middleware below is a second line of defence.
 * $router (App\Core\Router) is in scope.
 */

use App\Controllers\Admin\AuditLogController;
use App\Controllers\Admin\AuthController;
use App\Controllers\Admin\CustomPagesController;
use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\FaqsController;
use App\Controllers\Admin\MaintenanceController;
use App\Controllers\Admin\RolesController;
use App\Controllers\Admin\MediaController;
use App\Controllers\Admin\MenusController;
use App\Controllers\Admin\PagesController;
use App\Controllers\Admin\ProfileController;
use App\Controllers\Admin\SettingsController;
use App\Controllers\Admin\StatsController;
use App\Controllers\Admin\UsersController;

// Registry page slugs are file names (app/config/pages/<slug>.php) and may contain underscores (e.g. site_errors);
// custom page slugs are lowercase words joined by hyphens.
$pageSlug = '[a-z0-9]+(?:[_-][a-z0-9]+)*';

$router->group(['prefix' => 'admin'], function ($router) use ($pageSlug) {
    // --- public admin entry points ---
    $router->get('/login', [AuthController::class, 'showLogin'])->name('admin.login')->middleware('guest');
    $router->post('/login', [AuthController::class, 'login'])->middleware(['guest', 'throttle:20,300']);

    // --- everything below requires a signed-in user ---
    $router->group(['middleware' => ['auth']], function ($router) use ($pageSlug) {
        $router->get('/', [DashboardController::class, 'index'])->name('admin.dashboard');
        $router->post('/logout', [AuthController::class, 'logout'])->name('admin.logout');
        $router->get('/profile/password', [AuthController::class, 'showPassword'])->name('admin.password');
        $router->post('/profile/password', [AuthController::class, 'updatePassword'])->middleware('throttle:15,600');
        $router->post('/media/upload', [MediaController::class, 'upload'])->middleware('throttle:120,60');

        // my profile
        $router->get('/profile', [ProfileController::class, 'index'])->name('admin.profile');
        $router->post('/profile', [ProfileController::class, 'update'])->middleware('throttle:30,600');

        // pages editor (registry pages: slug = file name) + custom pages
        $router->group(['middleware' => ['can:pages.edit']], function ($router) use ($pageSlug) {
            $router->get('/pages', [PagesController::class, 'index'])->name('admin.pages');
            $router->get('/pages/create', [CustomPagesController::class, 'create'])->name('admin.pages.create');
            $router->post('/pages', [CustomPagesController::class, 'store']);
            $router->get('/pages/{slug}/edit', [PagesController::class, 'edit'])->name('admin.pages.edit')->where('slug', $pageSlug);
            $router->post('/pages/{slug}', [PagesController::class, 'update'])->where('slug', $pageSlug);
            $router->post('/pages/{slug}/publish', [PagesController::class, 'publish'])->where('slug', $pageSlug);
            $router->post('/pages/{slug}/duplicate', [CustomPagesController::class, 'duplicate'])->where('slug', $pageSlug);
        });
        $router->post('/pages/{slug}/reset', [PagesController::class, 'reset'])->middleware('can:pages.delete')->where('slug', $pageSlug);
        $router->post('/pages/{slug}/delete', [CustomPagesController::class, 'delete'])->middleware('can:pages.delete')->where('slug', $pageSlug);

        // menus (admins)
        $router->get('/menus', [MenusController::class, 'index'])->name('admin.menus')->middleware('can:menus.manage');
        $router->post('/menus', [MenusController::class, 'update'])->middleware('can:menus.manage');

        // site settings (admins)
        $router->get('/settings', [SettingsController::class, 'index'])->name('admin.settings')->middleware('can:settings.manage');
        $router->post('/settings', [SettingsController::class, 'update'])->middleware('can:settings.manage');

        // roles and permissions (administrators only)
        $router->get('/roles', [RolesController::class, 'index'])->name('admin.roles')->middleware('can:roles.manage');
        $router->get('/roles/create', [RolesController::class, 'create'])->middleware('can:roles.manage');
        $router->post('/roles', [RolesController::class, 'store'])->middleware('can:roles.manage');
        $router->get('/roles/{id}/edit', [RolesController::class, 'edit'])->middleware('can:roles.manage');
        $router->post('/roles/{id}', [RolesController::class, 'update'])->middleware('can:roles.manage');
        $router->post('/roles/{id}/delete', [RolesController::class, 'delete'])->middleware('can:roles.manage');

        // website on / off (maintenance page for visitors; admins only)
        $router->get('/maintenance', [MaintenanceController::class, 'index'])->name('admin.maintenance')->middleware('can:settings.manage');
        $router->post('/maintenance/toggle', [MaintenanceController::class, 'toggle'])->middleware('can:settings.manage');
        $router->post('/maintenance/save', [MaintenanceController::class, 'save'])->middleware('can:settings.manage');
        $router->get('/maintenance/preview', [MaintenanceController::class, 'preview'])->middleware('can:settings.manage');

        // impact figures + FAQs (content.manage)
        $router->group(['middleware' => ['can:content.manage']], function ($router) {
            $router->get('/stats', [StatsController::class, 'index'])->name('admin.stats');
            $router->get('/stats/create', [StatsController::class, 'create']);
            $router->post('/stats', [StatsController::class, 'store']);
            $router->post('/stats/reorder', [StatsController::class, 'reorder']);
            $router->get('/stats/{id}/edit', [StatsController::class, 'edit']);
            $router->post('/stats/{id}', [StatsController::class, 'update']);
            $router->post('/stats/{id}/toggle', [StatsController::class, 'toggle']);
            $router->post('/stats/{id}/delete', [StatsController::class, 'delete'])->middleware('can:content.delete');

            $router->get('/faqs', [FaqsController::class, 'index'])->name('admin.faqs');
            $router->get('/faqs/create', [FaqsController::class, 'create']);
            $router->post('/faqs', [FaqsController::class, 'store']);
            $router->post('/faqs/reorder', [FaqsController::class, 'reorder']);
            $router->get('/faqs/{id}/edit', [FaqsController::class, 'edit']);
            $router->post('/faqs/{id}', [FaqsController::class, 'update']);
            $router->post('/faqs/{id}/toggle', [FaqsController::class, 'toggle']);
            $router->post('/faqs/{id}/delete', [FaqsController::class, 'delete'])->middleware('can:content.delete');
        });

        // users (admins)
        $router->group(['middleware' => ['can:users.manage']], function ($router) {
            $router->get('/users', [UsersController::class, 'index'])->name('admin.users');
            $router->get('/users/create', [UsersController::class, 'create']);
            $router->post('/users', [UsersController::class, 'store'])->middleware('throttle:30,600');
            $router->get('/users/{id}/edit', [UsersController::class, 'edit']);
            $router->post('/users/{id}', [UsersController::class, 'update']);
            $router->post('/users/{id}/reset-password', [UsersController::class, 'resetPassword'])->middleware('throttle:30,600');
            $router->post('/users/{id}/delete', [UsersController::class, 'delete']);
        });

        // activity log (admins)
        $router->get('/audit-log', [AuditLogController::class, 'index'])->name('admin.audit')->middleware('can:audit.view');
        $router->get('/audit-log/export', [AuditLogController::class, 'export'])->middleware('can:audit.view');
    });
});
