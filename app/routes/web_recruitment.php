<?php
/** PUBLIC route of RECRUITMENT: GET and POST /track-application (tracking number + e-mail address -> the stage of the application). */

use App\Controllers\Web\TrackApplicationController;

$router->get('/track-application', [TrackApplicationController::class, 'show'])->name('recruitment.track');
$router->post('/track-application', [TrackApplicationController::class, 'show'])->middleware(['honeypot', 'throttle:30,600'])->name('recruitment.track.post');

// Ambassador profile: the private page where an ambassador applicant follows the application (the link is e-mailed on submission)
use App\Controllers\Web\AmbassadorProfileController;

$router->get('/ambassador/profile', [AmbassadorProfileController::class, 'request'])->name('ambassador.profile.request');
$router->post('/ambassador/profile', [AmbassadorProfileController::class, 'sendLink'])->middleware(['honeypot', 'throttle:10,600'])->name('ambassador.profile.send');
$router->get('/ambassador/profile/{token}', [AmbassadorProfileController::class, 'show'])->middleware('throttle:120,600')->name('ambassador.profile');
