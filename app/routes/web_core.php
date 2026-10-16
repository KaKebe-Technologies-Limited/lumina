<?php
/**
 * PUBLIC routes - OWNER: B1 "web-core" (home, about, programs(+detail), projects(+detail), involvement(+form), donate(+pledge form)).
 * $router (App\Core\Router) is in scope. Routes are matched static-before-dynamic regardless of file order.
 * Controllers: app/controllers/Web/*, views: app/views/web/<page>/*. See docs/ARCHITECTURE.md (URL map + ownership).
 * Every public POST has the middleware: honeypot (spam trap), throttle (rate limit) and page.published (404 while the page is unpublished);
 * CSRF is verified automatically by the router, and the forms contain csrf_field() + honeypot_field().
 */

use App\Controllers\Web\AboutController;
use App\Controllers\Web\DonateController;
use App\Controllers\Web\HomeController;
use App\Controllers\Web\InvolvementController;
use App\Controllers\Web\ProgramsController;
use App\Controllers\Web\ProjectsController;

// Home
$router->get('/', [HomeController::class, 'index'])->name('home')->middleware('page.published:home');

// About
$router->get('/about', [AboutController::class, 'index'])->name('about')->middleware('page.published:about');

// Programs (list + detail)
$router->get('/programs', [ProgramsController::class, 'index'])->name('programs.index')->middleware('page.published:programs');
$router->get('/programs/{slug}', [ProgramsController::class, 'show'])->name('programs.show')->middleware('page.published:programs');

// Projects (list + detail)
$router->get('/projects', [ProjectsController::class, 'index'])->name('projects.index')->middleware('page.published:projects');
$router->get('/projects/{slug}', [ProjectsController::class, 'show'])->name('projects.show')->middleware('page.published:projects');

// Involvement (page + volunteer / partner / advocate form)
$router->get('/involvement', [InvolvementController::class, 'index'])->name('involvement')->middleware('page.published:involvement');
$router->post('/involvement', [InvolvementController::class, 'submit'])->name('involvement.submit')
    ->middleware(['page.published:involvement', 'honeypot', 'throttle:5,600']);

// Donate (page + pledge form: a record of intent, never a payment)
$router->get('/donate', [DonateController::class, 'index'])->name('donate')->middleware('page.published:donate');
$router->post('/donate/pledge', [DonateController::class, 'pledge'])->name('donate.pledge')
    ->middleware(['page.published:donate', 'honeypot', 'throttle:5,600']);
