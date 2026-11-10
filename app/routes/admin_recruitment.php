<?php
/**
 * ADMIN routes of RECRUITMENT (ambassador, job and volunteer applications: tracking number, KPI scoring, status e-mails). Inside `auth`; the controller checks inbox.manage.
 *   GET  /admin/recruitment/criteria?scope=   the KPIs of a role + the interview level     POST /admin/recruitment/criteria (save one), /{id}/delete, /threshold
 *   POST /admin/recruitment/{scope}/{id}/judge     save the clicks, work out the %, invite to interview at the level
 *   GET  /admin/recruitment/{scope}/{id}/portrait | /cv   the stored files (never public)
 */

use App\Controllers\Admin\RecruitmentController;

$router->group(['prefix' => 'admin/recruitment', 'middleware' => ['auth']], function ($router) {
    $router->get('/criteria', [RecruitmentController::class, 'criteria'])->name('admin.recruitment.criteria');
    $router->post('/criteria', [RecruitmentController::class, 'saveCriterion'])->name('admin.recruitment.criteria.save');
    $router->post('/criteria/threshold', [RecruitmentController::class, 'saveThreshold'])->name('admin.recruitment.threshold');
    $router->post('/criteria/{id}/delete', [RecruitmentController::class, 'deleteCriterion'])->where('id', '\d+')->name('admin.recruitment.criteria.delete');
    $router->post('/{scope}/{id}/judge', [RecruitmentController::class, 'judge'])->where('id', '\d+')->middleware('throttle:120,600')->name('admin.recruitment.judge');
    $router->post('/{scope}/{id}/status', [RecruitmentController::class, 'status'])->where('id', '\d+')->middleware('throttle:120,600')->name('admin.recruitment.status');
    $router->get('/{scope}/{id}/{part}', [RecruitmentController::class, 'file'])->where('id', '\d+')->where('part', 'portrait|cv')->middleware('throttle:120,300')->name('admin.recruitment.file');
});
