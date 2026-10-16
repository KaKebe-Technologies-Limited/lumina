<?php
/**
 * ADMIN routes - OWNER: B5 "admin-inbox" (contact messages, subscribers(+CSV), involvement requests, job applications(+CV download), donations).
 * $router (App\Core\Router) is in scope. Static segments (create, export, bulk) always win over the {id} routes.
 * Abilities are enforced inside the controllers (inbox.manage everywhere, + inbox.delete for deletes, + export.csv for CSV downloads).
 *
 *   /admin/messages        GET index | GET export | POST bulk | GET {id} | POST {id} (status+notes) | POST {id}/reply | POST {id}/delete
 *   /admin/subscribers     GET index | POST (add one or many) | GET export | POST bulk | POST {id}/unsubscribe | {id}/resubscribe | {id}/delete
 *   /admin/involvement     GET index | GET export | POST bulk | GET {id} | POST {id} (status+notes) | POST {id}/delete
 *   /admin/applications    GET index | GET export | POST bulk | GET {id} | GET {id}/cv (authenticated download) | POST {id} | POST {id}/delete
 *   /admin/donations       GET index | GET create | POST (store) | GET export | GET {id}/edit | POST {id} | POST {id}/status | GET {id}/receipt | POST {id}/delete
 */

use App\Controllers\Admin\ApplicationController;
use App\Controllers\Admin\ContactMessageController;
use App\Controllers\Admin\DonationController;
use App\Controllers\Admin\InvolvementController;
use App\Controllers\Admin\SubscriberController;

$router->group(['prefix' => 'admin', 'middleware' => ['auth']], function ($router) {
    // ---- contact messages
    $router->get('/messages', [ContactMessageController::class, 'index'])->name('admin.messages');
    $router->get('/messages/export', [ContactMessageController::class, 'export'])->name('admin.messages.export');
    $router->post('/messages/bulk', [ContactMessageController::class, 'bulk'])->name('admin.messages.bulk');
    $router->get('/messages/{id}', [ContactMessageController::class, 'show'])->name('admin.messages.show');
    $router->post('/messages/{id}', [ContactMessageController::class, 'update'])->name('admin.messages.update');
    $router->post('/messages/{id}/reply', [ContactMessageController::class, 'reply'])->middleware('throttle:20,600')->name('admin.messages.reply');
    $router->post('/messages/{id}/delete', [ContactMessageController::class, 'destroy'])->name('admin.messages.delete');

    // ---- newsletter subscribers
    $router->get('/subscribers', [SubscriberController::class, 'index'])->name('admin.subscribers');
    $router->post('/subscribers', [SubscriberController::class, 'store'])->name('admin.subscribers.store');
    $router->get('/subscribers/export', [SubscriberController::class, 'export'])->name('admin.subscribers.export');
    $router->post('/subscribers/bulk', [SubscriberController::class, 'bulk'])->name('admin.subscribers.bulk');
    $router->post('/subscribers/{id}/unsubscribe', [SubscriberController::class, 'unsubscribe'])->name('admin.subscribers.unsubscribe');
    $router->post('/subscribers/{id}/resubscribe', [SubscriberController::class, 'resubscribe'])->name('admin.subscribers.resubscribe');
    $router->post('/subscribers/{id}/delete', [SubscriberController::class, 'destroy'])->name('admin.subscribers.delete');

    // ---- involvement requests
    $router->get('/involvement', [InvolvementController::class, 'index'])->name('admin.involvement');
    $router->get('/involvement/export', [InvolvementController::class, 'export'])->name('admin.involvement.export');
    $router->post('/involvement/bulk', [InvolvementController::class, 'bulk'])->name('admin.involvement.bulk');
    $router->get('/involvement/{id}', [InvolvementController::class, 'show'])->name('admin.involvement.show');
    $router->post('/involvement/{id}', [InvolvementController::class, 'update'])->name('admin.involvement.update');
    $router->post('/involvement/{id}/delete', [InvolvementController::class, 'destroy'])->name('admin.involvement.delete');

    // ---- job applications
    $router->get('/applications', [ApplicationController::class, 'index'])->name('admin.applications');
    $router->get('/applications/export', [ApplicationController::class, 'export'])->name('admin.applications.export');
    $router->post('/applications/bulk', [ApplicationController::class, 'bulk'])->name('admin.applications.bulk');
    $router->get('/applications/{id}', [ApplicationController::class, 'show'])->name('admin.applications.show');
    $router->get('/applications/{id}/cv', [ApplicationController::class, 'cv'])->middleware('throttle:60,300')->name('admin.applications.cv');
    $router->post('/applications/{id}', [ApplicationController::class, 'update'])->name('admin.applications.update');
    $router->post('/applications/{id}/delete', [ApplicationController::class, 'destroy'])->name('admin.applications.delete');

    // ---- donations
    $router->get('/donations', [DonationController::class, 'index'])->name('admin.donations');
    $router->get('/donations/create', [DonationController::class, 'create'])->name('admin.donations.create');
    $router->post('/donations', [DonationController::class, 'store'])->name('admin.donations.store');
    $router->get('/donations/export', [DonationController::class, 'export'])->name('admin.donations.export');
    $router->get('/donations/{id}/edit', [DonationController::class, 'edit'])->name('admin.donations.edit');
    $router->post('/donations/{id}', [DonationController::class, 'update'])->name('admin.donations.update');
    $router->post('/donations/{id}/status', [DonationController::class, 'status'])->name('admin.donations.status');
    $router->get('/donations/{id}/receipt', [DonationController::class, 'receipt'])->name('admin.donations.receipt');
    $router->post('/donations/{id}/delete', [DonationController::class, 'destroy'])->name('admin.donations.delete');
});
