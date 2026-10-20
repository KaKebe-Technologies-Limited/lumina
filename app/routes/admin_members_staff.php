<?php
/**
 * ADMIN routes added by the STAFF side of the membership card feature (docs/MEMBERSHIP_CARD.md): the proof of payment attached to ONE payment row.
 * (The photo, proof, card and bulk routes are in admin_membership_card.php.) Everything needs members.manage; controller App\Controllers\Admin\MembersController.
 *
 *   POST /admin/members/{id}/payments/{pid}/proof          attach a proof to this payment: a private copy of the member's uploaded proof (no file) or a posted
 *                                                          file (pay_proof); the proof of a RECEIVED payment is marked verified (who and when are stored)
 *   POST /admin/members/{id}/payments/{pid}/proof/remove   remove the proof copy of this payment (the member's own proof is not touched)
 */

use App\Controllers\Admin\MembersController;

$router->group(['prefix' => 'admin', 'middleware' => ['auth']], function ($router) {
    $router->group(['middleware' => ['can:members.manage']], function ($router) {
        $router->post('/members/{id}/payments/{pid}/proof', [MembersController::class, 'attachPaymentProof'])->where('id', '\d+')->where('pid', '\d+')->middleware('throttle:30,600')->name('admin.members.payments.proof.attach');
        $router->post('/members/{id}/payments/{pid}/proof/remove', [MembersController::class, 'removePaymentProof'])->where('id', '\d+')->where('pid', '\d+')->name('admin.members.payments.proof.remove');
    });
});
