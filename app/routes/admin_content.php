<?php
/**
 * ADMIN routes - OWNER: B4 "admin-content" (programs, projects, posts incl. post_type blog/news/press, activities, gallery, jobs).
 * Every route sits inside the 'auth' group and needs the content.manage ability (editors and admins); deleting also needs content.delete,
 * checked in the controllers. CSRF is verified by the router for every POST. $router (App\Core\Router) is in scope.
 *
 * Shape per area:  GET /admin/<area>  (list)   GET /admin/<area>/create   POST /admin/<area>  (store)
 *                  GET /admin/<area>/{id}/edit   POST /admin/<area>/{id}  (update)   POST /admin/<area>/{id}/delete
 *                  POST /admin/<area>/{id}/toggle  (active on/off)   POST /admin/<area>/{id}/move  (manual order; not for posts/gallery)
 * Extras: posts   GET /admin/posts/{id}/preview
 *         gallery POST /admin/gallery/upload  /reorder  /bulk   POST /admin/gallery/{id}/quick (inline alt/caption/album save)
 */

use App\Controllers\Admin\ActivityController;
use App\Controllers\Admin\GalleryController;
use App\Controllers\Admin\JobController;
use App\Controllers\Admin\OpportunitiesController;
use App\Controllers\Admin\OpportunityFeedsController;
use App\Controllers\Admin\PostController;
use App\Controllers\Admin\ProgramController;
use App\Controllers\Admin\ProjectController;

$router->group(['prefix' => 'admin', 'middleware' => ['auth', 'can:content.manage']], function ($router) {
    /** @var array<string,array{0:class-string,1:bool}> path => [controller, supports manual move] */
    $areas = [
        'programs'   => [ProgramController::class, true],
        'projects'   => [ProjectController::class, true],
        'posts'      => [PostController::class, false],
        'activities' => [ActivityController::class, true],
        'jobs'       => [JobController::class, true],
        'opportunities' => [OpportunitiesController::class, false],
        'gallery'    => [GalleryController::class, false],
    ];
    foreach ($areas as $path => [$controller, $movable]) {
        $router->get("/{$path}", [$controller, 'index'])->name("admin.{$path}.index");
        $router->get("/{$path}/create", [$controller, 'create'])->name("admin.{$path}.create");
        $router->post("/{$path}", [$controller, 'store'])->name("admin.{$path}.store");
        $router->get("/{$path}/{id}/edit", [$controller, 'edit'])->name("admin.{$path}.edit");
        $router->post("/{$path}/{id}", [$controller, 'update'])->name("admin.{$path}.update");
        $router->post("/{$path}/{id}/delete", [$controller, 'delete'])->name("admin.{$path}.delete");
        $router->post("/{$path}/{id}/toggle", [$controller, 'toggle'])->name("admin.{$path}.toggle");
        if ($movable) {
            $router->post("/{$path}/{id}/move", [$controller, 'move'])->name("admin.{$path}.move");
        }
    }

    // posts: preview of drafts / scheduled posts rendered in the public layout
    $router->get('/posts/{id}/preview', [PostController::class, 'preview'])->name('admin.posts.preview');

    // opportunities: approve what was read from other websites; the sources (feeds) the website reads
    $router->post('/opportunities/approve-all', [OpportunitiesController::class, 'approveAll'])->name('admin.opportunities.approve_all');
    $router->post('/opportunities/{id}/approve', [OpportunitiesController::class, 'approve'])->name('admin.opportunities.approve');
    $router->get('/opportunity-sources', [OpportunityFeedsController::class, 'index'])->name('admin.opportunity_sources.index');
    $router->post('/opportunity-sources', [OpportunityFeedsController::class, 'store'])->middleware('throttle:20,60')->name('admin.opportunity_sources.store');
    $router->post('/opportunity-sources/fetch-all', [OpportunityFeedsController::class, 'fetchAll'])->middleware('throttle:10,60')->name('admin.opportunity_sources.fetch_all');
    $router->post('/opportunity-sources/{id}', [OpportunityFeedsController::class, 'update'])->name('admin.opportunity_sources.update');
    $router->post('/opportunity-sources/{id}/toggle', [OpportunityFeedsController::class, 'toggle'])->name('admin.opportunity_sources.toggle');
    $router->post('/opportunity-sources/{id}/fetch', [OpportunityFeedsController::class, 'fetch'])->middleware('throttle:20,60')->name('admin.opportunity_sources.fetch');
    $router->post('/opportunity-sources/{id}/delete', [OpportunityFeedsController::class, 'destroy'])->name('admin.opportunity_sources.delete');

    // gallery: multi upload, drag-and-drop ordering, bulk actions, inline quick edit
    $router->post('/gallery/upload', [GalleryController::class, 'upload'])->middleware('throttle:600,60')->name('admin.gallery.upload');
    $router->post('/gallery/reorder', [GalleryController::class, 'reorder'])->name('admin.gallery.reorder');
    $router->post('/gallery/bulk', [GalleryController::class, 'bulk'])->name('admin.gallery.bulk');
    $router->post('/gallery/{id}/quick', [GalleryController::class, 'quick'])->name('admin.gallery.quick');
});
