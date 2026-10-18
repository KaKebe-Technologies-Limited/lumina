<?php
/**
 * ADMIN calendar routes. Contract: docs/CALENDAR.md section 8. Everything is inside the `auth` group.
 * Abilities: calendar.manage (admin + editor) for all day-to-day work; calendar.delete (admin) for BULK delete; calendar.advanced (admin)
 * is checked inside the entry save (the advanced repeat types); settings.manage for the settings panel. Controllers ALSO call
 * $this->authorize() (defence in depth). CSRF is automatic on POST; JSON clients send X-CSRF-TOKEN.
 *
 * Controllers (ADMIN agent): CalendarController (manager, entries, occurrences, holidays), CalendarTypesController, CalendarTransferController
 * (CSV import / export), CalendarApiController (JSON: month data, live preview).
 * Business rules live in App\Services\Calendar\CalendarWriter (validate / save / override / types): controllers only call it.
 */

use App\Controllers\Admin\CalendarApiController;
use App\Controllers\Admin\CalendarController;
use App\Controllers\Admin\CalendarTransferController;
use App\Controllers\Admin\CalendarTypesController;

$router->group(['prefix' => 'admin', 'middleware' => ['auth']], function ($router) {
    $router->group(['middleware' => ['can:calendar.manage']], function ($router) {
        // ---- manager (month view = default tab, ?view=list for the table)
        $router->get('/calendar', [CalendarController::class, 'index'])->name('admin.calendar');
        $router->get('/calendar/holidays', [CalendarController::class, 'holidays'])->name('admin.calendar.holidays');
        $router->get('/calendar/create', [CalendarController::class, 'create'])->name('admin.calendar.create');           // full editor (?date= prefills)
        $router->post('/calendar/quick-add', [CalendarController::class, 'quickAdd'])->name('admin.calendar.quick');
        $router->post('/calendar/bulk', [CalendarController::class, 'bulk'])->name('admin.calendar.bulk');                // delete needs calendar.delete (checked inside)
        $router->post('/calendar', [CalendarController::class, 'store'])->name('admin.calendar.store');

        // ---- types
        $router->get('/calendar/types', [CalendarTypesController::class, 'index'])->name('admin.calendar.types');
        $router->post('/calendar/types', [CalendarTypesController::class, 'store'])->name('admin.calendar.types.store');
        $router->post('/calendar/types/{id}', [CalendarTypesController::class, 'update'])->name('admin.calendar.types.update');
        $router->post('/calendar/types/{id}/delete', [CalendarTypesController::class, 'destroy'])->name('admin.calendar.types.delete');

        // ---- CSV
        $router->get('/calendar/export.csv', [CalendarTransferController::class, 'export'])->name('admin.calendar.export');
        $router->get('/calendar/import', [CalendarTransferController::class, 'form'])->name('admin.calendar.import');
        $router->get('/calendar/import-template.csv', [CalendarTransferController::class, 'template'])->name('admin.calendar.import.template');
        $router->post('/calendar/import', [CalendarTransferController::class, 'run'])->middleware('throttle:20,600')->name('admin.calendar.import.run');   // dry_run=1 previews, commit=1 saves

        // ---- JSON
        $router->get('/api/calendar/month', [CalendarApiController::class, 'month'])->name('admin.api.calendar.month');                 // ?month=YYYY-MM -> everything, flags included
        $router->post('/api/calendar/preview', [CalendarApiController::class, 'preview'])->name('admin.api.calendar.preview');          // next 5 dates of an UNSAVED schedule

        // ---- one entry
        $router->get('/calendar/{id}/edit', [CalendarController::class, 'edit'])->name('admin.calendar.edit');
        $router->post('/calendar/{id}', [CalendarController::class, 'update'])->name('admin.calendar.update');
        $router->post('/calendar/{id}/delete', [CalendarController::class, 'destroy'])->name('admin.calendar.delete');
        $router->post('/calendar/{id}/duplicate', [CalendarController::class, 'duplicate'])->name('admin.calendar.duplicate');
        $router->post('/calendar/{id}/toggle', [CalendarController::class, 'toggle'])->name('admin.calendar.toggle');                   // activate / deactivate (hide a system holiday)
        $router->post('/calendar/{id}/occurrence', [CalendarController::class, 'occurrence'])->name('admin.calendar.occurrence');      // occurrence_date + action move|cancel|confirm|restore|reset
        $router->post('/calendar/{id}/occurrence/{oid}/delete', [CalendarController::class, 'occurrenceDelete'])->where('oid', '\d+')->name('admin.calendar.occurrence.delete');
    });

    // ---- settings panel of the calendar (week start, contact block, default types): the organisation settings ability
    $router->group(['middleware' => ['can:settings.manage']], function ($router) {
        $router->get('/calendar/settings', [CalendarController::class, 'settings'])->name('admin.calendar.settings');
        $router->post('/calendar/settings', [CalendarController::class, 'saveSettings'])->name('admin.calendar.settings.save');
    });
});
