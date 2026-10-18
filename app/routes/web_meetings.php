<?php
/**
 * PUBLIC meeting routes. Contract: docs/MEETINGS.md section 10. Controllers (WEB agent): Web\MeetingController (invitee page, answer, .ics),
 * Web\MeetingRequestController (the "Request a meeting" form on the Contact page).
 *
 *   GET  /meeting/{token}        the invitee page: details, join button, add to calendar, answer. noindex, no-store, never lists other people.
 *   POST /meeting/{token}        the answer (field `rsvp` = accepted | tentative | declined). A POST, never a GET side effect. CSRF is automatic.
 *   GET  /meeting/{token}.ics    the "Add to calendar" file (METHOD:PUBLISH)
 *   POST /contact/meeting-request  the request form (honeypot + throttle + consent), answered with a redirect to /contact#meeting-request
 *
 * {token} = 48 lower-case hex characters (192 bits); anything else is a plain 404 without touching the database. A removed attendee's token is a 404 too.
 */

use App\Controllers\Web\MeetingController;
use App\Controllers\Web\MeetingRequestController;

$token = '[a-f0-9]{48}';
$router->get('/meeting/{token}', [MeetingController::class, 'show'])->where('token', $token)->name('meeting')->middleware('throttle:90,60');
$router->post('/meeting/{token}', [MeetingController::class, 'rsvp'])->where('token', $token)->name('meeting.rsvp')->middleware('throttle:30,60');
$router->get('/meeting/{token}.ics', [MeetingController::class, 'ics'])->where('token', $token)->name('meeting.ics')->middleware('throttle:60,60');
$router->post('/contact/meeting-request', [MeetingRequestController::class, 'submit'])->name('meeting.request')->middleware(['page.published:contact', 'honeypot', 'throttle:5,600']);
