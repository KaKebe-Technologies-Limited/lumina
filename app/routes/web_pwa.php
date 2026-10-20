<?php
/**
 * PUBLIC routes - "install the website as an app" (PWA): web app manifest, service worker and the offline page.
 * Admin > Settings > Install as an app. None of these starts a session.
 */

use App\Controllers\Web\PwaController;

$router->get('/manifest.webmanifest', [PwaController::class, 'manifest'])->name('pwa.manifest');
$router->get('/sw.js', [PwaController::class, 'worker'])->name('pwa.worker');
$router->get('/offline', [PwaController::class, 'offline'])->name('pwa.offline');
