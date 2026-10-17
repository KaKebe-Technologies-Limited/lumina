<?php
/**
 * ADMIN membership routes. Contract: docs/MEMBERSHIP.md. Controllers (ADMIN agent): MembersController, MembershipTypesController.
 * Everything is inside the `auth` group; members need ability members.manage (admin + editor), the fee configuration needs members.types
 * (admin only), deleting a member needs members.delete (admin only). Controllers ALSO call $this->authorize() (defence in depth).
 * Static segments (export, bulk, create) win over the {id} routes automatically.
 *
 *   /admin/members                       GET index (search, filters, sort, pagination)
 *   /admin/members/export                GET CSV (formula-injection safe; members.manage + export.csv)
 *   /admin/members/bulk                  POST approve | reject | export selected
 *   /admin/members/{id}                  GET detail (all data, status workflow, payments)   POST update details / notes
 *   /admin/members/{id}/status           POST change status (reason)
 *   /admin/members/{id}/renew            POST start a new term
 *   /admin/members/{id}/resend           POST resend an e-mail (template)
 *   /admin/members/{id}/payments         POST record a payment
 *   /admin/members/{id}/payments/{pid}   POST update a payment      /delete  POST delete (only without receipt)   /receipt  GET printable receipt
 *   /admin/members/{id}/delete           POST delete (admin; only a member without payments)
 *   /admin/members/{id}/anonymise        POST erase the personal data of a cancelled / rejected / expired member (admin; number and receipts stay)
 *   /admin/membership-types              GET list | GET create | POST store | GET {id}/edit | POST {id} | POST {id}/toggle | POST {id}/delete
 */

use App\Controllers\Admin\MembersController;
use App\Controllers\Admin\MembershipTypesController;

$router->group(['prefix' => 'admin', 'middleware' => ['auth']], function ($router) {
    $router->group(['middleware' => ['can:members.manage']], function ($router) {
        $router->get('/members', [MembersController::class, 'index'])->name('admin.members');
        $router->get('/members/export', [MembersController::class, 'export'])->middleware('can:export.csv')->name('admin.members.export');
        $router->post('/members/bulk', [MembersController::class, 'bulk'])->name('admin.members.bulk');
        $router->get('/members/{id}', [MembersController::class, 'show'])->name('admin.members.show');
        $router->post('/members/{id}', [MembersController::class, 'update'])->name('admin.members.update');
        $router->post('/members/{id}/status', [MembersController::class, 'status'])->name('admin.members.status');
        $router->post('/members/{id}/renew', [MembersController::class, 'renew'])->name('admin.members.renew');
        $router->post('/members/{id}/resend', [MembersController::class, 'resend'])->middleware('throttle:20,600')->name('admin.members.resend');
        $router->post('/members/{id}/payments', [MembersController::class, 'recordPayment'])->name('admin.members.payments.store');
        $router->post('/members/{id}/payments/{pid}', [MembersController::class, 'updatePayment'])->where('pid', '\d+')->name('admin.members.payments.update');
        $router->post('/members/{id}/payments/{pid}/delete', [MembersController::class, 'deletePayment'])->where('pid', '\d+')->name('admin.members.payments.delete');
        $router->get('/members/{id}/payments/{pid}/receipt', [MembersController::class, 'receipt'])->where('pid', '\d+')->name('admin.members.payments.receipt');
        $router->post('/members/{id}/delete', [MembersController::class, 'destroy'])->middleware('can:members.delete')->name('admin.members.delete');
        $router->post('/members/{id}/anonymise', [MembersController::class, 'anonymise'])->middleware('can:members.delete')->name('admin.members.anonymise');
    });

    $router->group(['middleware' => ['can:members.types']], function ($router) {
        $router->get('/membership-types', [MembershipTypesController::class, 'index'])->name('admin.membership-types');
        $router->get('/membership-types/create', [MembershipTypesController::class, 'create'])->name('admin.membership-types.create');
        $router->post('/membership-types', [MembershipTypesController::class, 'store'])->name('admin.membership-types.store');
        $router->get('/membership-types/{id}/edit', [MembershipTypesController::class, 'edit'])->name('admin.membership-types.edit');
        $router->post('/membership-types/{id}', [MembershipTypesController::class, 'update'])->name('admin.membership-types.update');
        $router->post('/membership-types/{id}/toggle', [MembershipTypesController::class, 'toggle'])->name('admin.membership-types.toggle');
        $router->post('/membership-types/{id}/delete', [MembershipTypesController::class, 'destroy'])->name('admin.membership-types.delete');
    });
});
