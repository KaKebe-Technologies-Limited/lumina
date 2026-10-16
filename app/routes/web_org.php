<?php
/**
 * PUBLIC routes - OWNER: B7 "org-public" (team, partners, reports(+download), events(+detail+registration), stories(+detail), news, press).
 * $router (App\Core\Router) is in scope. See docs/ARCHITECTURE.md (URL map + ownership).
 *
 * Every page route carries `page.published:<slug>`: the owner can unpublish a page in the admin and it then answers 404.
 * Static paths beat dynamic ones whatever the order (e.g. /reports/download/{id} vs /reports/{id}/download).
 */

use App\Controllers\Web\EventsController;
use App\Controllers\Web\NewsController;
use App\Controllers\Web\PartnersController;
use App\Controllers\Web\ReportsController;
use App\Controllers\Web\StoriesController;
use App\Controllers\Web\TeamController;

// ---- Team / Partners -----------------------------------------------------------------------------------------------
$router->get('/team', [TeamController::class, 'index'])->name('team.index')->middleware('page.published:team');
$router->get('/partners', [PartnersController::class, 'index'])->name('partners.index')->middleware('page.published:partners');

// ---- Reports & downloads (the download is counted and streamed from storage/private; only public documents) -------------
$router->get('/reports', [ReportsController::class, 'index'])->name('reports.index')->middleware('page.published:reports');
$router->get('/reports/download/{id}', [ReportsController::class, 'download'])->name('reports.download')
    ->middleware(['page.published:reports', 'throttle:60,300']);
$router->get('/reports/{id}/download', [ReportsController::class, 'download'])
    ->middleware(['page.published:reports', 'throttle:60,300']);                       // alias of the line above

// ---- Events (+ registration form + add-to-calendar) --------------------------------------------------------------------
$router->get('/events', [EventsController::class, 'index'])->name('events.index')->middleware('page.published:events');
$router->get('/events/{slug}', [EventsController::class, 'show'])->name('events.show')->middleware('page.published:events');
$router->get('/events/{slug}/calendar.ics', [EventsController::class, 'calendar'])->name('events.calendar')->middleware('page.published:events');
$router->get('/events/{slug}/register', [EventsController::class, 'registerForm'])->middleware('page.published:events');
$router->post('/events/{slug}/register', [EventsController::class, 'register'])->name('events.register')
    ->middleware(['page.published:events', 'honeypot', 'throttle:5,600']);

// ---- Impact stories (consent_confirmed only: enforced in the controller) ---------------------------------------------------
$router->get('/stories', [StoriesController::class, 'index'])->name('stories.index')->middleware('page.published:stories');
$router->get('/stories/{slug}', [StoriesController::class, 'show'])->name('stories.show')->middleware('page.published:stories');

// ---- News & press ----------------------------------------------------------------------------------------------------------------
$router->get('/news', [NewsController::class, 'index'])->name('news.index')->middleware('page.published:news');
$router->get('/news/{slug}', [NewsController::class, 'show'])->name('news.show')->middleware('page.published:news');
$router->get('/press', [NewsController::class, 'press'])->name('press.index')->middleware('page.published:news');
$router->get('/press/{slug}', [NewsController::class, 'pressShow'])->name('press.show')->middleware('page.published:news');
