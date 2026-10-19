<?php
/**
 * ADMIN form routes (pages + JSON API). Contract: docs/FORMS.md section 16. Everything is inside the `auth` group.
 * Abilities: forms.manage (create / edit / publish ...), forms.submissions (read and handle answers), forms.export (CSV), forms.delete (delete forms and
 * submissions; always IN ADDITION to the manage / submissions ability of the group it sits in). Controllers ALSO call $this->authorize() (defence in depth).
 * CSRF is automatic on POST/PUT/DELETE: JSON clients send the header X-CSRF-TOKEN and "Accept: application/json".
 *
 * Pages (BUILDER agent: Admin\FormsController)
 *   GET  /admin/forms                      forms list (status tabs, search, counts, copy link / embed code)
 *   GET  /admin/forms/new                  template picker  (POST /admin/forms creates: source = blank | <template key> | duplicate:<id>)
 *   POST /admin/forms                      create a draft and go to the builder
 *   GET  /admin/forms/{id}/edit            the builder (single page; talks to the JSON API below)
 *   POST /admin/forms/{id}/duplicate | /status | /delete
 * JSON API (BUILDER agent: Admin\FormBuilderApiController)
 *   GET /admin/api/forms            list           | GET  /admin/api/forms/catalog        field types, presets, operators, limits, templates
 *   POST /admin/api/forms/validate  normalise + checklist of an unsaved definition       | POST /admin/api/forms/preview   server-rendered preview HTML
 *   GET /admin/api/forms/{id}       load           | PUT  /admin/api/forms/{id}           save / autosave (optimistic locking, 409 conflict)
 *   POST /admin/api/forms/{id}/status | /test-submit | /duplicate     | DELETE /admin/api/forms/{id}
 * Submissions (SUBMISSIONS agent: Admin\FormSubmissionsController)
 *   GET  /admin/form-answers                                        every form with its counts (for roles that only read answers)
 *   GET  /admin/forms/{id}/submissions                              inbox: filters, search, sort, pagination
 *   GET  /admin/forms/{id}/submissions/stats                        summary tab (per day, per choice)
 *   GET  /admin/forms/{id}/submissions/export                       CSV (forms.export)
 *   POST /admin/forms/{id}/submissions/bulk                         read | unread | star | archive | spam | status:<key> | delete (forms.delete)
 *   GET  /admin/forms/{id}/submissions/{sid}                        detail (+ print view /print)
 *   POST /admin/forms/{id}/submissions/{sid}/status | /notes | /delete
 *   GET  /admin/forms/{id}/submissions/{sid}/files/{fid}            authenticated file download
 *   GET  /admin/api/forms/{id}/stats                                JSON statistics
 */

use App\Controllers\Admin\FormBuilderApiController;
use App\Controllers\Admin\FormsController;
use App\Controllers\Admin\FormSubmissionsController;

$router->group(['prefix' => 'admin', 'middleware' => ['auth']], function ($router) {
    // ------------------------------------------------------------------------------------------------ builder side: forms.manage
    $router->group(['middleware' => ['can:forms.manage']], function ($router) {
        $router->get('/forms', [FormsController::class, 'index'])->name('admin.forms');
        $router->get('/forms/new', [FormsController::class, 'templates'])->name('admin.forms.new');
        $router->post('/forms', [FormsController::class, 'store'])->name('admin.forms.store');
        $router->get('/forms/{id}/edit', [FormsController::class, 'edit'])->name('admin.forms.edit');
        $router->post('/forms/{id}/duplicate', [FormsController::class, 'duplicate'])->name('admin.forms.duplicate');
        $router->post('/forms/{id}/status', [FormsController::class, 'status'])->name('admin.forms.status');

        $router->get('/api/forms', [FormBuilderApiController::class, 'index'])->name('admin.api.forms');
        $router->get('/api/forms/catalog', [FormBuilderApiController::class, 'catalog'])->name('admin.api.forms.catalog');
        $router->post('/api/forms/validate', [FormBuilderApiController::class, 'validateDefinition'])->middleware('throttle:240,60')->name('admin.api.forms.validate');
        $router->post('/api/forms/preview', [FormBuilderApiController::class, 'preview'])->middleware('throttle:240,60')->name('admin.api.forms.preview');
        $router->get('/api/forms/{id}', [FormBuilderApiController::class, 'show'])->name('admin.api.forms.show');
        $router->put('/api/forms/{id}', [FormBuilderApiController::class, 'save'])->name('admin.api.forms.save');
        $router->post('/api/forms/{id}/status', [FormBuilderApiController::class, 'status'])->name('admin.api.forms.status');
        $router->post('/api/forms/{id}/test-submit', [FormBuilderApiController::class, 'testSubmit'])->middleware('throttle:120,60')->name('admin.api.forms.test');
        $router->post('/api/forms/{id}/duplicate', [FormBuilderApiController::class, 'duplicate'])->name('admin.api.forms.duplicate');

        // deleting a whole form needs forms.delete as well
        $router->group(['middleware' => ['can:forms.delete']], function ($router) {
            $router->post('/forms/{id}/delete', [FormsController::class, 'destroy'])->name('admin.forms.delete');
            $router->delete('/api/forms/{id}', [FormBuilderApiController::class, 'destroy'])->name('admin.api.forms.delete');
        });
    });

    // ------------------------------------------------------------------------------------------------ answers: forms.submissions
    $router->group(['middleware' => ['can:forms.submissions']], function ($router) {
        $router->get('/form-answers', [FormSubmissionsController::class, 'overview'])->name('admin.form_answers');
        $router->get('/forms/{id}/submissions', [FormSubmissionsController::class, 'index'])->name('admin.forms.submissions');
        $router->get('/forms/{id}/submissions/stats', [FormSubmissionsController::class, 'stats'])->name('admin.forms.stats');
        $router->post('/forms/{id}/submissions/bulk', [FormSubmissionsController::class, 'bulk'])->name('admin.forms.bulk');
        $router->get('/forms/{id}/submissions/{sid}', [FormSubmissionsController::class, 'show'])->where('sid', '\d+')->name('admin.forms.submission');
        $router->get('/forms/{id}/submissions/{sid}/print', [FormSubmissionsController::class, 'print'])->where('sid', '\d+')->name('admin.forms.submission.print');
        $router->post('/forms/{id}/submissions/{sid}/status', [FormSubmissionsController::class, 'status'])->where('sid', '\d+')->name('admin.forms.submission.status');
        $router->post('/forms/{id}/submissions/{sid}/notes', [FormSubmissionsController::class, 'notes'])->where('sid', '\d+')->name('admin.forms.submission.notes');
        $router->get('/forms/{id}/submissions/{sid}/files/{fid}', [FormSubmissionsController::class, 'file'])->where('sid', '\d+')->where('fid', '\d+')->middleware('throttle:120,60')->name('admin.forms.submission.file');
        $router->get('/api/forms/{id}/stats', [FormSubmissionsController::class, 'statsJson'])->name('admin.api.forms.stats');

        // CSV export: forms.export as well
        $router->group(['middleware' => ['can:forms.export']], function ($router) {
            $router->get('/forms/{id}/submissions/export', [FormSubmissionsController::class, 'export'])->middleware('throttle:30,60')->name('admin.forms.export');
        });
        // deleting submissions: forms.delete as well
        $router->group(['middleware' => ['can:forms.delete']], function ($router) {
            $router->post('/forms/{id}/submissions/{sid}/delete', [FormSubmissionsController::class, 'destroy'])->where('sid', '\d+')->name('admin.forms.submission.delete');
        });
    });
});
