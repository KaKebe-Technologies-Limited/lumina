<?php
/**
 * ADMIN routes of OFFICIAL LETTERS (Admin > Official letters). Inside `auth` + letters.manage.
 *   GET  /admin/letters                      list          GET /admin/letters/new, POST /admin/letters          write and save
 *   GET  /admin/letters/{id}/edit, POST /admin/letters/{id}           edit and save ("Save and preview" goes to the preview)
 *   GET  /admin/letters/{id}/preview         the PDF in a frame, with Send        GET /admin/letters/{id}/preview.pdf   the PDF itself
 *   POST /admin/letters/{id}/duplicate | /delete
 *   GET  /admin/letters/{id}/send            choose the addresses                 POST .../review  check the list      POST .../go  send
 *   GET  /admin/letters/contacts             the contact book (POST add, import, {id}/delete)
 */

use App\Controllers\Admin\LettersController;

$router->group(['prefix' => 'admin', 'middleware' => ['auth', 'can:letters.manage']], function ($router) {
    $router->get('/letters', [LettersController::class, 'index'])->name('admin.letters');
    $router->get('/letters/new', [LettersController::class, 'create'])->name('admin.letters.new');
    $router->post('/letters', [LettersController::class, 'save'])->name('admin.letters.create');
    $router->get('/letters/contacts', [LettersController::class, 'contacts'])->name('admin.letters.contacts');
    $router->post('/letters/contacts', [LettersController::class, 'addContact'])->name('admin.letters.contacts.add');
    $router->post('/letters/contacts/import', [LettersController::class, 'importContacts'])->name('admin.letters.contacts.import');
    $router->post('/letters/contacts/{id}/delete', [LettersController::class, 'deleteContact'])->where('id', '\d+')->name('admin.letters.contacts.delete');
    $router->get('/letters/{id}/edit', [LettersController::class, 'edit'])->where('id', '\d+')->name('admin.letters.edit');
    $router->post('/letters/{id}', [LettersController::class, 'save'])->where('id', '\d+')->name('admin.letters.update');
    $router->get('/letters/{id}/preview', [LettersController::class, 'preview'])->where('id', '\d+')->name('admin.letters.preview');
    $router->get('/letters/{id}/preview.pdf', [LettersController::class, 'pdf'])->where('id', '\d+')->name('admin.letters.pdf');
    $router->post('/letters/{id}/duplicate', [LettersController::class, 'duplicate'])->where('id', '\d+')->name('admin.letters.duplicate');
    $router->post('/letters/{id}/delete', [LettersController::class, 'delete'])->where('id', '\d+')->name('admin.letters.delete');
    $router->get('/letters/{id}/send', [LettersController::class, 'sendForm'])->where('id', '\d+')->name('admin.letters.send');
    $router->post('/letters/{id}/review', [LettersController::class, 'review'])->where('id', '\d+')->middleware('throttle:30,600')->name('admin.letters.review');
    $router->post('/letters/{id}/go', [LettersController::class, 'go'])->where('id', '\d+')->middleware('throttle:20,600')->name('admin.letters.go');
});
