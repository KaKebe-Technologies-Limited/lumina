<?php
/**
 * PUBLIC form routes. Contract: docs/FORMS.md section 8 (PUBLIC agent owns Web\FormController and the views).
 * $router (App\Core\Router) is in scope.
 *
 *   GET  /forms                  the optional public list of forms (404 unless Site settings > Forms > "Show a public list" is on)
 *   GET  /forms/{slug}           the form (or the friendly notice: closed / full / not open yet; 404 for drafts and archived forms, except for staff who may preview)
 *   POST /forms/{slug}           the submission: CSRF (stateless token) + honeypot + timing + throttle, then FormValidator, files, SubmissionService, mails
 *   GET  /forms/{slug}/thanks    the thank-you page shown after a successful submission (PRG)
 *
 * {slug} = lower-case letters, digits and hyphens. Static segments (thanks) are handled before the dynamic route automatically.
 * A custom page can also embed a form with the [form slug="..."] shortcode (App\Services\Forms\FormShortcode).
 */

use App\Controllers\Web\FormController;

$router->get('/forms', [FormController::class, 'index'])->name('forms');
$router->get('/forms/{slug}', [FormController::class, 'show'])->name('forms.show');
$router->post('/forms/{slug}', [FormController::class, 'submit'])->name('forms.submit')->middleware('throttle:60,600');
$router->get('/forms/{slug}/thanks', [FormController::class, 'thanks'])->name('forms.thanks');
