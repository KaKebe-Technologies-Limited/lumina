<?php
/** PUBLIC route of RECRUITMENT: GET and POST /track-application (tracking number + e-mail address -> the stage of the application). */

use App\Controllers\Web\TrackApplicationController;

$router->get('/track-application', [TrackApplicationController::class, 'show'])->name('recruitment.track');
$router->post('/track-application', [TrackApplicationController::class, 'show'])->middleware(['honeypot', 'throttle:30,600'])->name('recruitment.track.post');
