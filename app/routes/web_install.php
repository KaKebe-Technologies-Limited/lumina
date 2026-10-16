<?php
/**
 * First-administrator screen for hosting without a shell. The routes answer 404 unless INSTALL_TOKEN is set in .env and the users
 * table is empty (see InstallController). $router (App\Core\Router) is in scope.
 */

use App\Controllers\Web\InstallController;

$router->get('/install', [InstallController::class, 'show'])->name('install.show');
$router->post('/install', [InstallController::class, 'create'])->name('install.create')->middleware(['throttle:5,600']);
