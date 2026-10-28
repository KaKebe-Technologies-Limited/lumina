<?php
/**
 * ADMIN routes - LAF ACADEMY. Two ability levels (docs/ACADEMY.md): academy.teach (facilitators: their own courses, classes and learners; the controllers add the
 * ownership checks) and academy.manage (administrators: everything). CSRF is verified by the router for every POST. $router is in scope.
 */

use App\Controllers\Admin\AcademyAdminController;
use App\Controllers\Admin\AcademyCertificatesController;
use App\Controllers\Admin\AcademyChallengesAdminController;
use App\Controllers\Admin\AcademyFacilitatorApplicationsController;
use App\Controllers\Admin\AcademyClassesController;
use App\Controllers\Admin\AcademyCoursesController;
use App\Controllers\Admin\AcademyCurriculumController;
use App\Controllers\Admin\AcademyFacilitatorsController;
use App\Controllers\Admin\AcademyLearnersController;
use App\Controllers\Admin\AcademyPartnersController;

$router->group(['prefix' => 'admin/academy', 'middleware' => ['auth', 'can:academy.teach']], function ($router) {
    $router->get('', [AcademyAdminController::class, 'index'])->name('admin.academy.index');

    // courses
    $router->get('/courses', [AcademyCoursesController::class, 'index'])->name('admin.academy.courses');
    $router->get('/courses/create', [AcademyCoursesController::class, 'create'])->name('admin.academy.courses.create');
    $router->post('/courses', [AcademyCoursesController::class, 'store'])->name('admin.academy.courses.store');
    $router->get('/courses/{id}/edit', [AcademyCoursesController::class, 'edit'])->name('admin.academy.courses.edit');
    $router->post('/courses/{id}', [AcademyCoursesController::class, 'update'])->name('admin.academy.courses.update');
    $router->post('/courses/{id}/status', [AcademyCoursesController::class, 'status'])->name('admin.academy.courses.status');
    $router->post('/courses/{id}/duplicate', [AcademyCoursesController::class, 'duplicate'])->name('admin.academy.courses.duplicate');
    $router->post('/courses/{id}/delete', [AcademyCoursesController::class, 'delete'])->name('admin.academy.courses.delete');

    // curriculum: modules, lessons, quizzes
    $router->get('/courses/{id}/curriculum', [AcademyCurriculumController::class, 'show'])->name('admin.academy.curriculum');
    $router->post('/courses/{id}/modules', [AcademyCurriculumController::class, 'storeModule'])->name('admin.academy.modules.store');
    $router->post('/modules/{mid}', [AcademyCurriculumController::class, 'updateModule'])->name('admin.academy.modules.update');
    $router->post('/modules/{mid}/delete', [AcademyCurriculumController::class, 'deleteModule'])->name('admin.academy.modules.delete');
    $router->post('/modules/{mid}/move', [AcademyCurriculumController::class, 'moveModule'])->name('admin.academy.modules.move');
    $router->get('/modules/{mid}/lessons/create', [AcademyCurriculumController::class, 'createLesson'])->name('admin.academy.lessons.create');
    $router->post('/modules/{mid}/lessons', [AcademyCurriculumController::class, 'storeLesson'])->middleware('throttle:120,60')->name('admin.academy.lessons.store');
    $router->get('/lessons/{lid}/edit', [AcademyCurriculumController::class, 'editLesson'])->name('admin.academy.lessons.edit');
    $router->post('/lessons/{lid}', [AcademyCurriculumController::class, 'updateLesson'])->middleware('throttle:120,60')->name('admin.academy.lessons.update');
    $router->post('/lessons/{lid}/delete', [AcademyCurriculumController::class, 'deleteLesson'])->name('admin.academy.lessons.delete');
    $router->post('/lessons/{lid}/move', [AcademyCurriculumController::class, 'moveLesson'])->name('admin.academy.lessons.move');
    $router->post('/lessons/{lid}/toggle', [AcademyCurriculumController::class, 'toggleLesson'])->name('admin.academy.lessons.toggle');
    $router->get('/lessons/{lid}/file', [AcademyCurriculumController::class, 'lessonFile'])->name('admin.academy.lessons.file');
    $router->get('/modules/{mid}/quiz', [AcademyCurriculumController::class, 'moduleQuiz'])->name('admin.academy.quiz.module');
    $router->get('/courses/{id}/exam', [AcademyCurriculumController::class, 'examQuiz'])->name('admin.academy.quiz.exam');
    $router->get('/quizzes/{qid}/edit', [AcademyCurriculumController::class, 'editQuiz'])->name('admin.academy.quiz.edit');
    $router->post('/quizzes/{qid}', [AcademyCurriculumController::class, 'saveQuiz'])->name('admin.academy.quiz.save');
    $router->post('/quizzes/{qid}/delete', [AcademyCurriculumController::class, 'deleteQuiz'])->name('admin.academy.quiz.delete');

    // virtual classes
    $router->get('/classes', [AcademyClassesController::class, 'index'])->name('admin.academy.classes');
    $router->get('/classes/create', [AcademyClassesController::class, 'create'])->name('admin.academy.classes.create');
    $router->post('/classes', [AcademyClassesController::class, 'store'])->name('admin.academy.classes.store');
    $router->get('/classes/{id}/edit', [AcademyClassesController::class, 'edit'])->name('admin.academy.classes.edit');
    $router->post('/classes/{id}', [AcademyClassesController::class, 'update'])->name('admin.academy.classes.update');
    $router->post('/classes/{id}/notify', [AcademyClassesController::class, 'notify'])->middleware('throttle:20,60')->name('admin.academy.classes.notify');
    $router->post('/classes/{id}/status', [AcademyClassesController::class, 'status'])->name('admin.academy.classes.status');
    $router->post('/classes/{id}/delete', [AcademyClassesController::class, 'delete'])->name('admin.academy.classes.delete');

    // challenges: the list, scoring projects and viewing entry files (facilitators); everything else is below (administrators)
    $router->get('/challenges', [AcademyChallengesAdminController::class, 'index'])->name('admin.academy.challenges');
    $router->get('/challenges/{id}/judge', [AcademyChallengesAdminController::class, 'judge'])->name('admin.academy.challenges.judge');
    $router->post('/challenges/{id}/judge/{eid}', [AcademyChallengesAdminController::class, 'saveJudgment'])->middleware('throttle:300,60')->name('admin.academy.challenges.judge.save');
    $router->get('/challenges/entries/{eid}/file/{part}', [AcademyChallengesAdminController::class, 'entryFile'])->middleware('throttle:120,60')->name('admin.academy.challenges.entry.file');

    // learners (facilitators: only the learners of their courses; the controller limits what they can do)
    $router->get('/learners', [AcademyLearnersController::class, 'index'])->name('admin.academy.learners');
    $router->get('/learners/{id}', [AcademyLearnersController::class, 'show'])->name('admin.academy.learners.show');
    $router->post('/learners/{id}/enroll', [AcademyLearnersController::class, 'enroll'])->name('admin.academy.learners.enroll');
    $router->post('/learners/{id}/unenroll/{courseId}', [AcademyLearnersController::class, 'unenroll'])->name('admin.academy.learners.unenroll');
});

// ---- administrators only ---------------------------------------------------------------------------------------------------
$router->group(['prefix' => 'admin/academy', 'middleware' => ['auth', 'can:academy.manage']], function ($router) {
    $router->post('/starter/install-all', [AcademyAdminController::class, 'installAllStarter'])->name('admin.academy.starter.all');
    $router->post('/starter/{slug}/install', [AcademyAdminController::class, 'installStarter'])->name('admin.academy.starter.install');
    $router->post('/starter/{slug}/reinstall', [AcademyAdminController::class, 'reinstallStarter'])->name('admin.academy.starter.reinstall');

    $router->get('/learners/create/new', [AcademyLearnersController::class, 'create'])->name('admin.academy.learners.create');
    $router->post('/learners', [AcademyLearnersController::class, 'store'])->name('admin.academy.learners.store');
    $router->get('/learners-export', [AcademyLearnersController::class, 'export'])->middleware('throttle:10,60')->name('admin.academy.learners.export');
    $router->post('/learners/{id}/reset-link', [AcademyLearnersController::class, 'resetLink'])->middleware('throttle:30,60')->name('admin.academy.learners.reset');
    $router->post('/learners/{id}/approve', [AcademyLearnersController::class, 'approve'])->middleware('throttle:120,60')->name('admin.academy.learners.approve');
    $router->post('/learners/{id}/verify', [AcademyLearnersController::class, 'verify'])->name('admin.academy.learners.verify');
    $router->post('/learners/{id}/approve-certificates', [AcademyLearnersController::class, 'approveCertificates'])->middleware('throttle:60,60')->name('admin.academy.learners.approve_certificates');
    $router->post('/learners/{id}/send-confirmation', [AcademyLearnersController::class, 'sendConfirmation'])->middleware('throttle:60,60')->name('admin.academy.learners.send_confirmation');
    $router->post('/learners/{id}/send-whatsapp', [AcademyLearnersController::class, 'sendWhatsApp'])->middleware('throttle:30,60')->name('admin.academy.learners.send_whatsapp');
    $router->post('/learners-send-confirmations', [AcademyLearnersController::class, 'sendConfirmations'])->middleware('throttle:10,60')->name('admin.academy.learners.send_confirmations');
    $router->post('/learners-confirm-all', [AcademyLearnersController::class, 'confirmAll'])->middleware('throttle:5,60')->name('admin.academy.learners.confirm_all');
    $router->get('/learners/{id}/photo', [AcademyLearnersController::class, 'photo'])->middleware('throttle:300,60')->name('admin.academy.learners.photo');
    $router->post('/learners/{id}/photo-remove', [AcademyLearnersController::class, 'photoRemove'])->name('admin.academy.learners.photo_remove');
    $router->post('/learners/{id}/toggle', [AcademyLearnersController::class, 'toggle'])->name('admin.academy.learners.toggle');
    $router->post('/learners/{id}/delete', [AcademyLearnersController::class, 'delete'])->name('admin.academy.learners.delete');
    $router->post('/learners/{id}/certificate', [AcademyLearnersController::class, 'issueCertificate'])->name('admin.academy.learners.certificate');

    $router->get('/certificates', [AcademyCertificatesController::class, 'index'])->name('admin.academy.certificates');
    $router->get('/certificates-export', [AcademyCertificatesController::class, 'export'])->middleware('throttle:10,60')->name('admin.academy.certificates.export');
    $router->get('/certificates/{id}/preview', [AcademyCertificatesController::class, 'preview'])->middleware('throttle:60,60')->name('admin.academy.certificates.preview');
    $router->post('/certificates/{id}/approve', [AcademyCertificatesController::class, 'approve'])->name('admin.academy.certificates.approve');
    $router->post('/certificates/{id}/decline', [AcademyCertificatesController::class, 'decline'])->middleware('throttle:60,60')->name('admin.academy.certificates.decline');
    $router->post('/certificates/{id}/email', [AcademyCertificatesController::class, 'email'])->middleware('throttle:60,60')->name('admin.academy.certificates.email');
    $router->post('/certificates-approve-all', [AcademyCertificatesController::class, 'approveAll'])->middleware('throttle:10,60')->name('admin.academy.certificates.approve_all');
    $router->post('/certificates-withdraw-all', [AcademyCertificatesController::class, 'withdrawAll'])->middleware('throttle:5,60')->name('admin.academy.certificates.withdraw_all');
    $router->post('/certificates/{id}/revoke', [AcademyCertificatesController::class, 'revoke'])->name('admin.academy.certificates.revoke');
    $router->post('/certificates/{id}/reinstate', [AcademyCertificatesController::class, 'reinstate'])->name('admin.academy.certificates.reinstate');

    $router->get('/challenges/create', [AcademyChallengesAdminController::class, 'create'])->name('admin.academy.challenges.create');
    $router->post('/challenges', [AcademyChallengesAdminController::class, 'store'])->name('admin.academy.challenges.store');
    $router->post('/challenges/starter', [AcademyChallengesAdminController::class, 'loadStarter'])->name('admin.academy.challenges.starter');
    $router->get('/challenges/{id}/edit', [AcademyChallengesAdminController::class, 'edit'])->name('admin.academy.challenges.edit');
    $router->post('/challenges/{id}', [AcademyChallengesAdminController::class, 'update'])->name('admin.academy.challenges.update');
    $router->post('/challenges/{id}/status', [AcademyChallengesAdminController::class, 'status'])->name('admin.academy.challenges.status');
    $router->post('/challenges/{id}/delete', [AcademyChallengesAdminController::class, 'delete'])->name('admin.academy.challenges.delete');
    $router->get('/challenges/{id}/entries', [AcademyChallengesAdminController::class, 'entries'])->name('admin.academy.challenges.entries');
    $router->post('/challenges/{id}/finalize', [AcademyChallengesAdminController::class, 'finalize'])->name('admin.academy.challenges.finalize');
    $router->post('/challenges/{id}/reopen', [AcademyChallengesAdminController::class, 'reopen'])->name('admin.academy.challenges.reopen');
    $router->post('/challenges/entries/{eid}/decision', [AcademyChallengesAdminController::class, 'entryDecision'])->name('admin.academy.challenges.entry.decision');
    $router->post('/challenges/entries/{eid}/photo', [AcademyChallengesAdminController::class, 'entryPhoto'])->name('admin.academy.challenges.entry.photo');

    $router->get('/facilitator-applications', [AcademyFacilitatorApplicationsController::class, 'index'])->name('admin.academy.applications');
    $router->get('/facilitator-applications/{id}', [AcademyFacilitatorApplicationsController::class, 'show'])->name('admin.academy.applications.show');
    $router->post('/facilitator-applications/{id}', [AcademyFacilitatorApplicationsController::class, 'decide'])->name('admin.academy.applications.decide');
    $router->get('/facilitator-applications/{id}/file/{part}', [AcademyFacilitatorApplicationsController::class, 'file'])->middleware('throttle:120,60')->name('admin.academy.applications.file');
    $router->post('/facilitator-applications/{id}/delete', [AcademyFacilitatorApplicationsController::class, 'delete'])->name('admin.academy.applications.delete');

    $router->get('/partner-courses', [AcademyPartnersController::class, 'index'])->name('admin.academy.partners');
    $router->get('/partner-courses/create', [AcademyPartnersController::class, 'create'])->name('admin.academy.partners.create');
    $router->post('/partner-courses', [AcademyPartnersController::class, 'store'])->name('admin.academy.partners.store');
    $router->post('/partner-courses/check', [AcademyPartnersController::class, 'checkAll'])->middleware('throttle:10,60')->name('admin.academy.partners.check_all');
    $router->post('/partner-courses/starter', [AcademyPartnersController::class, 'loadStarter'])->name('admin.academy.partners.starter');
    $router->get('/partner-courses/{id}/edit', [AcademyPartnersController::class, 'edit'])->name('admin.academy.partners.edit');
    $router->post('/partner-courses/{id}', [AcademyPartnersController::class, 'update'])->name('admin.academy.partners.update');
    $router->post('/partner-courses/{id}/check', [AcademyPartnersController::class, 'check'])->middleware('throttle:30,60')->name('admin.academy.partners.check');
    $router->post('/partner-courses/{id}/toggle', [AcademyPartnersController::class, 'toggle'])->name('admin.academy.partners.toggle');
    $router->post('/partner-courses/{id}/delete', [AcademyPartnersController::class, 'delete'])->name('admin.academy.partners.delete');

    $router->get('/facilitators', [AcademyFacilitatorsController::class, 'index'])->name('admin.academy.facilitators');
    $router->post('/facilitators', [AcademyFacilitatorsController::class, 'store'])->name('admin.academy.facilitators.store');
    $router->post('/facilitators/{id}/reset-password', [AcademyFacilitatorsController::class, 'resetPassword'])->name('admin.academy.facilitators.reset');
    $router->post('/facilitators/{id}/toggle', [AcademyFacilitatorsController::class, 'toggle'])->name('admin.academy.facilitators.toggle');
});
