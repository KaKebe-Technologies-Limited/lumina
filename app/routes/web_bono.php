<?php
/**
 * PUBLIC routes - Bono AI, the support assistant of the website (docs/BONO.md): the chat window sends the visitor's question here and gets the answer back as JSON.
 * CSRF is verified by the router for every POST. $router is in scope.
 */

use App\Controllers\Web\BonoController;

$router->post('/bono/ask', [BonoController::class, 'ask'])->middleware('throttle:60,60')->name('bono.ask');
$router->post('/bono/feedback', [BonoController::class, 'feedback'])->middleware('throttle:60,60')->name('bono.feedback');
