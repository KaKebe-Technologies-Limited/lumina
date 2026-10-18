<?php
/**
 * PUBLIC calendar routes. Contract: docs/CALENDAR.md. Controller: App\Controllers\Web\CalendarController (WEB agent owns the page behaviour;
 * the feed and iCal methods are complete). $router (App\Core\Router) is in scope. All routes are GET and read-only; only public, active
 * entries are ever reachable. Static segments (/calendar/feed) win over {slug}; a slug can never be "feed" (CalendarEntry::RESERVED_SLUGS).
 *
 *   GET /calendar                    the page: month grid (?month=YYYY-MM), list / coming-up views (?view=), type filters (?types=a,b)
 *   GET /calendar/feed               JSON for the JS enhancement (?start=&end= or ?month=, ?types=), range capped at 400 days
 *   GET /calendar.ics                iCal of all public entries (about four years), ?type=slug[,slug]
 *   GET /calendar/{id}.ics           iCal of one entry: ?date=YYYY-MM-DD for one occurrence, else its next occurrence
 *   GET /calendar/{slug}             detail page of an entry (?date= picks the occurrence)
 */

use App\Controllers\Web\CalendarController;

$router->get('/calendar', [CalendarController::class, 'index'])->name('calendar')->middleware('page.published:calendar');
$router->get('/calendar/feed', [CalendarController::class, 'feed'])->name('calendar.feed')->middleware(['page.published:calendar', 'throttle:120,60']);
$router->get('/calendar.ics', [CalendarController::class, 'icsAll'])->name('calendar.ics')->middleware(['page.published:calendar', 'throttle:60,60']);
$router->get('/calendar/{id}.ics', [CalendarController::class, 'icsEntry'])->where('id', '\d+')->name('calendar.ics.entry')->middleware(['page.published:calendar', 'throttle:60,60']);
$router->get('/calendar/{slug}', [CalendarController::class, 'show'])->name('calendar.show')->middleware('page.published:calendar');
