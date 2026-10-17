<?php
/**
 * PUBLIC membership routes. Contract: docs/MEMBERSHIP.md. Controller: App\Controllers\Web\MembershipController (WEB agent).
 * $router (App\Core\Router) is in scope. The POST has the public-form middleware: page.published, honeypot (spam trap) and throttle (per IP);
 * CSRF is verified automatically. The confirmation page is reachable only with the signed token (?t=) that the redirect after
 * submitting carries; the controller must also rate-limit failed token checks per IP (RateLimit), because the route throttle is per path.
 *
 *   GET  /membership                          the Membership tab (hero, benefits, eligibility, types + current fees, steps, payment, FAQ)
 *   GET  /membership/register                 the registration form (or the "closed" notice)
 *   POST /membership/register                 submit
 *   GET  /membership/confirmation/{ref}?t=    confirmation (noindex); 404 without a valid token
 *   GET  /membership/terms                    editable membership terms (Pages > Membership terms)
 */

use App\Controllers\Web\MembershipController;

$router->get('/membership', [MembershipController::class, 'index'])->name('membership')->middleware('page.published:membership');
$router->get('/membership/register', [MembershipController::class, 'register'])->name('membership.register')->middleware('page.published:membership');
$router->post('/membership/register', [MembershipController::class, 'submit'])->name('membership.submit')
    ->middleware(['page.published:membership', 'honeypot', 'throttle:5,600']);
$router->get('/membership/confirmation/{ref}', [MembershipController::class, 'confirmation'])->name('membership.confirmation')
    ->where('ref', '[A-Za-z0-9-]{6,40}')->middleware(['page.published:membership', 'throttle:30,600']);
$router->get('/membership/terms', [MembershipController::class, 'terms'])->name('membership.terms')->middleware('page.published:membership-terms');
