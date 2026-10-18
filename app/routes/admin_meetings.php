<?php
/**
 * ADMIN meeting routes. Contract: docs/MEETINGS.md section 10. Everything is inside the `auth` group and needs `meetings.manage` (admin + editor);
 * the controllers ALSO call $this->authorize() (defence in depth). `meetings.bulk` (admin) is checked inside when more than 50 people are added at once,
 * `meetings.delete` (admin) guards deleting a request. CSRF is automatic on POST; JSON clients send X-CSRF-TOKEN and Accept: application/json.
 *
 * Controllers
 *   Admin\MeetingsController          ADMIN agent: list, schedule form, meeting page, edit / reschedule / cancel / reopen / duplicate / delete, attendees (add, remove, CSV)
 *   Admin\MeetingRequestsController   ADMIN agent: the request inbox
 *   Admin\MeetingMailController       MAIL agent: send / queue / batch / pause / resume / retry / test / message / reminders (JSON)
 *   Admin\MeetingPickersController    ARCHITECT (complete): invitee box parser, member / staff / earlier-attendee pickers, Jitsi room (JSON)
 */

use App\Controllers\Admin\MeetingMailController;
use App\Controllers\Admin\MeetingPickersController;
use App\Controllers\Admin\MeetingRequestsController;
use App\Controllers\Admin\MeetingsController;

$router->group(['prefix' => 'admin', 'middleware' => ['auth', 'can:meetings.manage']], function ($router) {
    // ---- meetings: pages
    $router->get('/meetings', [MeetingsController::class, 'index'])->name('admin.meetings');                                   // ?status= &when=upcoming|past|all &q= &page=
    $router->get('/meetings/create', [MeetingsController::class, 'create'])->name('admin.meetings.create');                    // ?request=ID (prefill from a request) &date=YYYY-MM-DD
    $router->post('/meetings', [MeetingsController::class, 'store'])->name('admin.meetings.store');                             // intent = draft | send
    $router->get('/meetings/{id}', [MeetingsController::class, 'show'])->name('admin.meetings.show');                          // the meeting page: status, attendee table, actions
    $router->get('/meetings/{id}/edit', [MeetingsController::class, 'edit'])->name('admin.meetings.edit');
    $router->post('/meetings/{id}', [MeetingsController::class, 'update'])->name('admin.meetings.update');                      // intent = save | send (send = also queue the update for everyone)
    $router->post('/meetings/{id}/cancel', [MeetingsController::class, 'cancel'])->name('admin.meetings.cancel');               // reason, send_cancellation
    $router->post('/meetings/{id}/reopen', [MeetingsController::class, 'reopen'])->name('admin.meetings.reopen');
    $router->post('/meetings/{id}/duplicate', [MeetingsController::class, 'duplicate'])->name('admin.meetings.duplicate');
    $router->post('/meetings/{id}/delete', [MeetingsController::class, 'destroy'])->name('admin.meetings.delete');               // drafts only
    $router->get('/meetings/{id}/attendees.csv', [MeetingsController::class, 'exportAttendees'])->name('admin.meetings.attendees.csv');
    $router->post('/meetings/{id}/attendees', [MeetingsController::class, 'addAttendees'])->name('admin.meetings.attendees.add');          // emails (raw text), items (JSON list), bulk, staff[]
    $router->post('/meetings/{id}/attendees/{aid}/remove', [MeetingsController::class, 'removeAttendee'])->where('aid', '\d+')->name('admin.meetings.attendees.remove');

    // ---- meeting requests (inbox)
    $router->get('/meeting-requests', [MeetingRequestsController::class, 'index'])->name('admin.meeting_requests');            // ?status= &q= &page=
    $router->get('/meeting-requests/export.csv', [MeetingRequestsController::class, 'export'])->name('admin.meeting_requests.export');
    $router->get('/meeting-requests/{id}', [MeetingRequestsController::class, 'show'])->name('admin.meeting_requests.show');
    $router->post('/meeting-requests/{id}/status', [MeetingRequestsController::class, 'status'])->name('admin.meeting_requests.status');   // status, notes, notify, note
    $router->post('/meeting-requests/{id}/delete', [MeetingRequestsController::class, 'destroy'])->middleware('can:meetings.delete')->name('admin.meeting_requests.delete');

    // ---- JSON: pickers and helpers (ARCHITECT, complete)
    $router->post('/api/meetings/parse', [MeetingPickersController::class, 'parse'])->middleware('throttle:120,60')->name('admin.api.meetings.parse');
    $router->post('/api/meetings/jitsi', [MeetingPickersController::class, 'jitsi'])->middleware('throttle:60,60')->name('admin.api.meetings.jitsi');
    $router->get('/api/meetings/pickers/members', [MeetingPickersController::class, 'members'])->middleware('throttle:240,60')->name('admin.api.meetings.members');
    $router->get('/api/meetings/pickers/member-types', [MeetingPickersController::class, 'memberTypes'])->name('admin.api.meetings.member_types');
    $router->get('/api/meetings/pickers/members-bulk', [MeetingPickersController::class, 'membersBulk'])->name('admin.api.meetings.members_bulk');
    $router->get('/api/meetings/pickers/staff', [MeetingPickersController::class, 'staff'])->name('admin.api.meetings.staff');
    $router->get('/api/meetings/pickers/previous', [MeetingPickersController::class, 'previous'])->middleware('throttle:240,60')->name('admin.api.meetings.previous');

    // ---- JSON: sending (MAIL agent)
    $router->get('/api/meetings/{id}/mail', [MeetingMailController::class, 'status'])->name('admin.api.meetings.mail');
    $router->post('/api/meetings/{id}/mail/send', [MeetingMailController::class, 'send'])->name('admin.api.meetings.mail.send');                 // scope, confirm_count, attendee_id
    $router->post('/api/meetings/{id}/mail/batch', [MeetingMailController::class, 'batch'])->name('admin.api.meetings.mail.batch');
    $router->post('/api/meetings/{id}/mail/pause', [MeetingMailController::class, 'pause'])->name('admin.api.meetings.mail.pause');
    $router->post('/api/meetings/{id}/mail/resume', [MeetingMailController::class, 'resume'])->name('admin.api.meetings.mail.resume');
    $router->post('/api/meetings/{id}/mail/retry', [MeetingMailController::class, 'retry'])->name('admin.api.meetings.mail.retry');
    $router->post('/api/meetings/{id}/mail/test', [MeetingMailController::class, 'test'])->middleware('throttle:30,600')->name('admin.api.meetings.mail.test');
    $router->post('/api/meetings/{id}/mail/message', [MeetingMailController::class, 'message'])->middleware('throttle:60,600')->name('admin.api.meetings.mail.message');
    $router->post('/api/meetings/{id}/mail/reminders', [MeetingMailController::class, 'reminders'])->name('admin.api.meetings.mail.reminders');
});
