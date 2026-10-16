<?php
/**
 * PUBLIC routes - OWNER: B2 "web-content" (blog(+post), activities(+detail), gallery, careers(+detail+application), contact,
 * subscribe/unsubscribe, privacy, sitemap.xml, robots.txt). The themed error/maintenance pages are views (app/views/web/errors).
 * $router (App\Core\Router) is in scope. See docs/ARCHITECTURE.md (URL map + ownership).
 * Every public POST carries the 'honeypot' + 'throttle:N,S' middleware (CSRF is automatic) and the 'page.published' guard of the
 * page that owns the form, so an unpublished page can never receive submissions.
 */

use App\Controllers\Web\ActivitiesController;
use App\Controllers\Web\BlogController;
use App\Controllers\Web\CareersController;
use App\Controllers\Web\ContactController;
use App\Controllers\Web\GalleryController;
use App\Controllers\Web\PrivacyController;
use App\Controllers\Web\SeoController;
use App\Controllers\Web\SubscribeController;

// ---- Blog -----------------------------------------------------------------------------------------------------------
$router->get('/blog', [BlogController::class, 'index'])->name('blog.index');
$router->get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');

// ---- Activities -------------------------------------------------------------------------------------------------------
$router->get('/activities', [ActivitiesController::class, 'index'])->name('activities.index');
$router->get('/activities/{slug}', [ActivitiesController::class, 'show'])->name('activities.show');

// ---- Gallery ----------------------------------------------------------------------------------------------------------
$router->get('/gallery', [GalleryController::class, 'index'])->name('gallery.index');

// ---- Careers (+ general expression of interest, + application with optional private CV upload) --------------------------------
$router->get('/careers', [CareersController::class, 'index'])->name('careers.index');
$router->get('/careers/{slug}', [CareersController::class, 'show'])->name('careers.show');
$router->post('/careers/apply', [CareersController::class, 'applyGeneral'])
    ->name('careers.apply_general')->middleware(['page.published:careers', 'honeypot', 'throttle:5,600']);
$router->post('/careers/{slug}/apply', [CareersController::class, 'apply'])
    ->name('careers.apply')->middleware(['page.published:careers', 'honeypot', 'throttle:5,600']);

// ---- Contact ----------------------------------------------------------------------------------------------------------
$router->get('/contact', [ContactController::class, 'index'])->name('contact.index');
$router->post('/contact', [ContactController::class, 'send'])
    ->name('contact.send')->middleware(['page.published:contact', 'honeypot', 'throttle:5,600']);

// ---- Newsletter -------------------------------------------------------------------------------------------------------------
$router->get('/subscribe', [SubscribeController::class, 'landing'])->name('subscribe.redirect');
$router->post('/subscribe', [SubscribeController::class, 'store'])
    ->name('subscribe.store')->middleware(['honeypot', 'throttle:5,600']);
$router->get('/unsubscribe/{token}', [SubscribeController::class, 'showUnsubscribe'])->name('unsubscribe.show');
$router->post('/unsubscribe/{token}', [SubscribeController::class, 'confirmUnsubscribe'])
    ->name('unsubscribe.confirm')->middleware(['throttle:20,600']);

// ---- Privacy policy ----------------------------------------------------------------------------------------------------------
$router->get('/privacy', [PrivacyController::class, 'index'])->name('privacy.index');

// ---- Search engines ------------------------------------------------------------------------------------------------------------
$router->get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('seo.sitemap');
$router->get('/robots.txt', [SeoController::class, 'robots'])->name('seo.robots');
