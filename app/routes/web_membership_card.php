<?php
/**
 * PUBLIC routes of the MEMBERSHIP CARD feature. Contract: docs/MEMBERSHIP_CARD.md. $router (App\Core\Router) is in scope.
 * Controllers (DASHBOARD agent): App\Controllers\Web\MembershipDashboardController (the private "My membership" page), MembershipVerifyController (the QR target).
 *
 * The private page is reached ONLY with its unguessable token (64 hex characters = 256 bits, members.dashboard_token): no login, no password. Every response is
 * noindex, "Cache-Control: private, no-store" and "Referrer-Policy: no-referrer"; failed token lookups are counted per IP and answered 429 after a few.
 * The token pattern is deliberately loose here: a mistyped token gets the friendly "this link is not valid" page (and a rate-limit hit), not a bare router 404.
 *
 *   GET  /membership/me/{token}                 the page: progress, photo, proof of payment, card, details
 *   POST /membership/me/{token}/photo           upload / replace the headshot (multipart: photo, photo_consent)
 *   POST /membership/me/{token}/proof           upload / replace the proof of payment (multipart: proof)
 *   GET  /membership/me/{token}/photo           stream the member's own photo (image)
 *   GET  /membership/me/{token}/proof           stream the member's own proof (image inline, PDF as download)
 *   GET  /membership/me/{token}/card.pdf        the membership card (PDF, attachment)
 *   GET  /membership/me/{token}/card.png        PNG preview of the card (404 when the server has no GD)
 *   POST /membership/me/{token}/resend          e-mail the card to the member again (3 per day)
 *   GET  /membership/verify/{token}             the QR target: VALID / EXPIRED / SUSPENDED / NOT VALID + name, type, number, since, valid until. Nothing else.
 */

use App\Controllers\Web\MembershipDashboardController as Me;
use App\Controllers\Web\MembershipVerifyController as Verify;

$token = '[A-Za-z0-9]{8,128}';
$router->get('/membership/me/{token}', [Me::class, 'show'])->name('membership.me')->where('token', $token)->middleware(['throttle:60,600']);
$router->post('/membership/me/{token}/photo', [Me::class, 'uploadPhoto'])->name('membership.me.photo.upload')->where('token', $token)->middleware(['throttle:12,600']);
$router->post('/membership/me/{token}/proof', [Me::class, 'uploadProof'])->name('membership.me.proof.upload')->where('token', $token)->middleware(['throttle:12,600']);
$router->get('/membership/me/{token}/photo', [Me::class, 'photo'])->name('membership.me.photo')->where('token', $token)->middleware('throttle:120,600');
$router->get('/membership/me/{token}/proof', [Me::class, 'proof'])->name('membership.me.proof')->where('token', $token)->middleware('throttle:60,600');
$router->get('/membership/me/{token}/card.pdf', [Me::class, 'cardPdf'])->name('membership.me.card.pdf')->where('token', $token)->middleware('throttle:30,600');
$router->get('/membership/me/{token}/card.png', [Me::class, 'cardPng'])->name('membership.me.card.png')->where('token', $token)->middleware('throttle:60,600');
$router->post('/membership/me/{token}/resend', [Me::class, 'resend'])->name('membership.me.resend')->where('token', $token)->middleware(['throttle:10,600']);
$router->get('/membership/verify/{token}', [Verify::class, 'show'])->name('membership.verify')->where('token', $token)->middleware('throttle:240,600');
