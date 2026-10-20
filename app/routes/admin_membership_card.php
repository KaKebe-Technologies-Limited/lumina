<?php
/**
 * ADMIN routes of the MEMBERSHIP CARD feature. Contract: docs/MEMBERSHIP_CARD.md. Everything is inside the `auth` group.
 *
 *   The DESIGNER (ability members.card = admin only by default; controller App\Controllers\Admin\MembershipCardController, CARD agent):
 *     GET  /admin/membership-card                  the designer screen (words, colours, blocks, number format, rules, e-mail, live preview)
 *     POST /admin/membership-card                  save (CardSettings::save + the logo upload)
 *     POST /admin/membership-card/preview          remember the UNSAVED form values for the preview (session) -> JSON {pdf, png}
 *     GET  /admin/membership-card/preview.pdf      the real PDF with SAMPLE data (watermarked), same origin: shown in an <iframe> (CSP frame-src 'self')
 *     GET  /admin/membership-card/preview.png      PNG preview (404 without GD)
 *     POST /admin/membership-card/test-email       send the sample card to the signed-in administrator's own address
 *
 *   MEMBER FILES AND CARD (ability members.manage = admin + editor; controller App\Controllers\Admin\MembersController, STAFF agent):
 *     GET  /admin/members/{id}/photo               stream the headshot (inline)
 *     GET  /admin/members/{id}/proof               stream the proof of payment (?download=1 forces a download; PDFs always download)
 *     GET  /admin/members/{id}/payments/{pid}/proof   stream the proof attached to a payment
 *     POST /admin/members/{id}/photo               replace the headshot (multipart: photo; staff need no consent tick)
 *     POST /admin/members/{id}/photo/remove        remove the headshot
 *     POST /admin/members/{id}/proof               upload a proof for the member
 *     POST /admin/members/{id}/proof/status        status = verified | rejected | received (rejected e-mails a polite request with the dashboard link)
 *     POST /admin/members/{id}/proof/remove        remove the proof
 *     POST /admin/members/{id}/card/issue          Issue card now   (force = skip the payment rule)
 *     POST /admin/members/{id}/card/resend         Re-send the card e-mail
 *     POST /admin/members/{id}/card/reissue        Re-issue (new version, new verification token)
 *     POST /admin/members/{id}/card/revoke         Revoke (the QR code then says NOT VALID)
 *     GET  /admin/members/{id}/card.pdf            download the issued card (PDF)
 *     GET  /admin/members/{id}/card.png            PNG preview of the issued card
 *     POST /admin/members/card-bulk                issue cards for the selected members (the same rules, one result line per member)
 */

use App\Controllers\Admin\MembershipCardController;
use App\Controllers\Admin\MembersController;

$router->group(['prefix' => 'admin', 'middleware' => ['auth']], function ($router) {
    $router->group(['middleware' => ['can:members.card']], function ($router) {
        $router->get('/membership-card', [MembershipCardController::class, 'index'])->name('admin.membership-card');
        $router->post('/membership-card', [MembershipCardController::class, 'save'])->name('admin.membership-card.save');
        $router->post('/membership-card/preview', [MembershipCardController::class, 'preview'])->middleware('throttle:60,600')->name('admin.membership-card.preview');
        $router->get('/membership-card/preview.pdf', [MembershipCardController::class, 'previewPdf'])->name('admin.membership-card.preview.pdf');
        $router->get('/membership-card/preview.png', [MembershipCardController::class, 'previewPng'])->name('admin.membership-card.preview.png');
        $router->post('/membership-card/test-email', [MembershipCardController::class, 'testEmail'])->middleware('throttle:10,600')->name('admin.membership-card.test');
    });

    $router->group(['middleware' => ['can:members.manage']], function ($router) {
        $router->get('/members/{id}/photo', [MembersController::class, 'photo'])->where('id', '\d+')->name('admin.members.photo');
        $router->get('/members/{id}/proof', [MembersController::class, 'proofFile'])->where('id', '\d+')->name('admin.members.proof');
        $router->get('/members/{id}/payments/{pid}/proof', [MembersController::class, 'paymentProof'])->where('id', '\d+')->where('pid', '\d+')->name('admin.members.payments.proof');
        $router->post('/members/{id}/photo', [MembersController::class, 'replacePhoto'])->where('id', '\d+')->middleware('throttle:30,600')->name('admin.members.photo.replace');
        $router->post('/members/{id}/photo/remove', [MembersController::class, 'removePhoto'])->where('id', '\d+')->name('admin.members.photo.remove');
        $router->post('/members/{id}/proof', [MembersController::class, 'uploadProof'])->where('id', '\d+')->middleware('throttle:30,600')->name('admin.members.proof.upload');
        $router->post('/members/{id}/proof/status', [MembersController::class, 'proofStatus'])->where('id', '\d+')->middleware('throttle:30,600')->name('admin.members.proof.status');
        $router->post('/members/{id}/proof/remove', [MembersController::class, 'removeProof'])->where('id', '\d+')->name('admin.members.proof.remove');
        $router->post('/members/{id}/card/issue', [MembersController::class, 'issueCard'])->where('id', '\d+')->name('admin.members.card.issue');
        $router->post('/members/{id}/card/resend', [MembersController::class, 'resendCard'])->where('id', '\d+')->middleware('throttle:20,600')->name('admin.members.card.resend');
        $router->post('/members/{id}/card/reissue', [MembersController::class, 'reissueCard'])->where('id', '\d+')->name('admin.members.card.reissue');
        $router->post('/members/{id}/card/revoke', [MembersController::class, 'revokeCard'])->where('id', '\d+')->name('admin.members.card.revoke');
        $router->get('/members/{id}/card.pdf', [MembersController::class, 'cardPdf'])->where('id', '\d+')->name('admin.members.card.pdf');
        $router->get('/members/{id}/card.png', [MembersController::class, 'cardPng'])->where('id', '\d+')->name('admin.members.card.png');
        $router->post('/members/card-bulk', [MembersController::class, 'bulkCard'])->middleware('throttle:10,600')->name('admin.members.card.bulk');
    });
});
