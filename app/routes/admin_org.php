<?php
/**
 * ADMIN routes - OWNER: B8 "org-admin" (team members, partners, documents, events(+registrations), stories).
 * Everything sits inside the 'auth' middleware group; every controller method also authorize()s its ability
 * (content.manage / content.delete / inbox.manage / inbox.delete / export.csv). CSRF is verified by the router on every POST.
 * Static segments (create, reorder, bulk, export) are listed before the {id} routes (the router also prefers static paths).
 */

use App\Controllers\Admin\DocumentController;
use App\Controllers\Admin\DocumentSignaturesController;
use App\Controllers\Admin\EventController;
use App\Controllers\Admin\EventRegistrationController;
use App\Controllers\Admin\PartnerController;
use App\Controllers\Admin\StoryController;
use App\Controllers\Admin\TeamMemberController;

$router->group(['prefix' => 'admin', 'middleware' => ['auth']], function ($router) {

    // ---- Team members -------------------------------------------------------------------------------------------
    $router->get('/team', [TeamMemberController::class, 'index'])->name('admin.team.index');
    $router->get('/team/create', [TeamMemberController::class, 'create'])->name('admin.team.create');
    $router->post('/team', [TeamMemberController::class, 'store'])->name('admin.team.store');
    $router->get('/team/reorder', [TeamMemberController::class, 'reorder'])->name('admin.team.reorder');
    $router->post('/team/reorder', [TeamMemberController::class, 'saveOrder']);
    $router->post('/team/bulk', [TeamMemberController::class, 'bulk'])->name('admin.team.bulk');
    $router->get('/team/{id}/edit', [TeamMemberController::class, 'edit'])->name('admin.team.edit');
    $router->post('/team/{id}', [TeamMemberController::class, 'update'])->name('admin.team.update');
    $router->post('/team/{id}/toggle', [TeamMemberController::class, 'toggle'])->name('admin.team.toggle');
    $router->post('/team/{id}/move', [TeamMemberController::class, 'move'])->name('admin.team.move');
    $router->post('/team/{id}/delete', [TeamMemberController::class, 'destroy'])->name('admin.team.delete');

    // ---- Partners -----------------------------------------------------------------------------------------------
    $router->get('/partners', [PartnerController::class, 'index'])->name('admin.partners.index');
    $router->get('/partners/create', [PartnerController::class, 'create'])->name('admin.partners.create');
    $router->post('/partners', [PartnerController::class, 'store'])->name('admin.partners.store');
    $router->get('/partners/reorder', [PartnerController::class, 'reorder'])->name('admin.partners.reorder');
    $router->post('/partners/reorder', [PartnerController::class, 'saveOrder']);
    $router->post('/partners/bulk', [PartnerController::class, 'bulk'])->name('admin.partners.bulk');
    $router->get('/partners/{id}/edit', [PartnerController::class, 'edit'])->name('admin.partners.edit');
    $router->post('/partners/{id}', [PartnerController::class, 'update'])->name('admin.partners.update');
    $router->post('/partners/{id}/toggle', [PartnerController::class, 'toggle'])->name('admin.partners.toggle');
    $router->post('/partners/{id}/move', [PartnerController::class, 'move'])->name('admin.partners.move');
    $router->post('/partners/{id}/delete', [PartnerController::class, 'destroy'])->name('admin.partners.delete');

    // ---- Documents (private files; admin download is auth-gated, public download is B7's /reports/download/{id}) ----
    $router->get('/documents', [DocumentController::class, 'index'])->name('admin.documents.index');
    $router->get('/documents/create', [DocumentController::class, 'create'])->name('admin.documents.create');
    $router->post('/documents', [DocumentController::class, 'store'])->name('admin.documents.store');
    $router->post('/documents/bulk', [DocumentController::class, 'bulk'])->name('admin.documents.bulk');
    $router->get('/documents/{id}/edit', [DocumentController::class, 'edit'])->name('admin.documents.edit');
    $router->get('/documents/{id}/download', [DocumentController::class, 'download'])->name('admin.documents.download');
    $router->post('/documents/{id}', [DocumentController::class, 'update'])->name('admin.documents.update');
    $router->post('/documents/{id}/toggle', [DocumentController::class, 'toggle'])->name('admin.documents.toggle');
    $router->post('/documents/{id}/delete', [DocumentController::class, 'destroy'])->name('admin.documents.delete');

    // ---- Document signatures (docs/DOCUMENTS-SIGNATURES.md): signatories and their pictures, signing a document, the public / private switch, the policy pack (ability documents.sign) ----
    $router->get('/documents/signatories', [DocumentSignaturesController::class, 'signatories'])->name('admin.documents.signatories');
    $router->post('/documents/signatories', [DocumentSignaturesController::class, 'storeSignatory'])->middleware('throttle:60,60')->name('admin.documents.signatories.store');
    $router->post('/documents/signatories/{id}', [DocumentSignaturesController::class, 'updateSignatory'])->middleware('throttle:60,60')->name('admin.documents.signatories.update');
    $router->get('/documents/signatories/{id}/signature', [DocumentSignaturesController::class, 'signatureImage'])->middleware('throttle:120,60')->name('admin.documents.signatories.image');
    $router->post('/documents/signatories/{id}/toggle', [DocumentSignaturesController::class, 'toggleSignatory'])->name('admin.documents.signatories.toggle');
    $router->post('/documents/signatories/{id}/delete', [DocumentSignaturesController::class, 'deleteSignatory'])->name('admin.documents.signatories.delete');
    $router->post('/documents/signatories/{id}/attach', [DocumentSignaturesController::class, 'attach'])->name('admin.documents.signatories.attach');
    $router->post('/documents/signatories/{id}/sign-all', [DocumentSignaturesController::class, 'signAll'])->middleware('throttle:10,60')->name('admin.documents.signatories.sign_all');
    $router->post('/documents/import-pack', [DocumentSignaturesController::class, 'importPack'])->middleware('throttle:10,60')->name('admin.documents.import_pack');
    $router->get('/documents/{id}/signatures', [DocumentSignaturesController::class, 'sign'])->name('admin.documents.signatures');
    $router->post('/documents/{id}/signatures/details', [DocumentSignaturesController::class, 'details'])->name('admin.documents.signatures.details');
    $router->post('/documents/{id}/signatures/add', [DocumentSignaturesController::class, 'addRow'])->name('admin.documents.signatures.add');
    $router->post('/documents/{id}/signatures/generate', [DocumentSignaturesController::class, 'generate'])->middleware('throttle:30,60')->name('admin.documents.signatures.generate');
    $router->post('/documents/{id}/signatures/{sigid}/apply', [DocumentSignaturesController::class, 'apply'])->middleware('throttle:60,60')->name('admin.documents.signatures.apply');
    $router->post('/documents/{id}/signatures/{sigid}/unsign', [DocumentSignaturesController::class, 'unsign'])->name('admin.documents.signatures.unsign');
    $router->post('/documents/{id}/signatures/{sigid}/delete', [DocumentSignaturesController::class, 'removeRow'])->name('admin.documents.signatures.delete');
    $router->get('/documents/{id}/signed', [DocumentSignaturesController::class, 'downloadSigned'])->middleware('throttle:60,60')->name('admin.documents.signed');
    $router->post('/documents/{id}/visibility', [DocumentSignaturesController::class, 'visibility'])->name('admin.documents.visibility');

    // ---- Events -------------------------------------------------------------------------------------------------
    $router->get('/events', [EventController::class, 'index'])->name('admin.events.index');
    $router->get('/events/create', [EventController::class, 'create'])->name('admin.events.create');
    $router->post('/events', [EventController::class, 'store'])->name('admin.events.store');
    $router->post('/events/bulk', [EventController::class, 'bulk'])->name('admin.events.bulk');
    $router->get('/events/{id}/edit', [EventController::class, 'edit'])->name('admin.events.edit');
    $router->post('/events/{id}', [EventController::class, 'update'])->name('admin.events.update');
    $router->post('/events/{id}/toggle', [EventController::class, 'toggle'])->name('admin.events.toggle');
    $router->post('/events/{id}/delete', [EventController::class, 'destroy'])->name('admin.events.delete');

    // ---- Event registrations ({event} = event id; {id} = registration id) ---------------------------------------------
    $router->get('/events/{event}/registrations', [EventRegistrationController::class, 'index'])->where('event', '\d+')->name('admin.registrations.index');
    $router->get('/events/{event}/registrations/export', [EventRegistrationController::class, 'export'])->where('event', '\d+')->name('admin.registrations.export');
    $router->post('/events/{event}/registrations', [EventRegistrationController::class, 'store'])->where('event', '\d+')->name('admin.registrations.store');
    $router->post('/events/{event}/registrations/bulk', [EventRegistrationController::class, 'bulk'])->where('event', '\d+')->name('admin.registrations.bulk');
    $router->get('/events/{event}/registrations/{id}', [EventRegistrationController::class, 'show'])->where('event', '\d+')->name('admin.registrations.show');
    $router->post('/events/{event}/registrations/{id}', [EventRegistrationController::class, 'update'])->where('event', '\d+')->name('admin.registrations.update');
    $router->post('/events/{event}/registrations/{id}/status', [EventRegistrationController::class, 'status'])->where('event', '\d+')->name('admin.registrations.status');
    $router->post('/events/{event}/registrations/{id}/delete', [EventRegistrationController::class, 'destroy'])->where('event', '\d+')->name('admin.registrations.delete');

    // ---- Impact stories -------------------------------------------------------------------------------------------
    $router->get('/stories', [StoryController::class, 'index'])->name('admin.stories.index');
    $router->get('/stories/create', [StoryController::class, 'create'])->name('admin.stories.create');
    $router->post('/stories', [StoryController::class, 'store'])->name('admin.stories.store');
    $router->post('/stories/bulk', [StoryController::class, 'bulk'])->name('admin.stories.bulk');
    $router->get('/stories/{id}/edit', [StoryController::class, 'edit'])->name('admin.stories.edit');
    $router->post('/stories/{id}', [StoryController::class, 'update'])->name('admin.stories.update');
    $router->post('/stories/{id}/toggle', [StoryController::class, 'toggle'])->name('admin.stories.toggle');
    $router->post('/stories/{id}/delete', [StoryController::class, 'destroy'])->name('admin.stories.delete');
});
