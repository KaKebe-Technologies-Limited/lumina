<?php
/**
 * PUBLIC newsletter routes. Contract: docs/NEWSLETTER.md. Controller: App\Controllers\Web\NewsletterController (WEB agent).
 * $router (App\Core\Router) is in scope. Static segments win over {slug} automatically, so /newsletter/archive is never an issue slug
 * (NewsletterService also refuses "archive" as a slug). All routes are GET and read-only; drafts, archived and not-yet-due scheduled issues are 404.
 *
 *   GET /newsletter                the newest published issue (or the friendly empty state)
 *   GET /newsletter/archive        paginated grid of published issues (?page=)
 *   GET /newsletter/{slug}         any published issue
 *   GET /api/newsletters           JSON list of published issues (?page=&per_page=, no content)
 *   GET /api/newsletters/{slug}    JSON of one published issue (structured content + rendered HTML)
 *
 * The subscribe box posts to the existing POST /subscribe; unsubscribing (page and RFC 8058 one-click POST) is /unsubscribe/{token}.
 */

use App\Controllers\Web\NewsletterController;

$router->get('/newsletter', [NewsletterController::class, 'index'])->name('newsletter')->middleware('page.published:newsletter');
$router->get('/newsletter/archive', [NewsletterController::class, 'archive'])->name('newsletter.archive')->middleware('page.published:newsletter');
$router->get('/newsletter/{slug}', [NewsletterController::class, 'show'])->name('newsletter.show')->middleware('page.published:newsletter');

$router->get('/api/newsletters', [NewsletterController::class, 'apiIndex'])->name('api.newsletters')->middleware('throttle:120,60');
$router->get('/api/newsletters/{slug}', [NewsletterController::class, 'apiShow'])->name('api.newsletters.show')->middleware('throttle:120,60');
