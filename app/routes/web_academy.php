<?php
/**
 * PUBLIC routes - LAF ACADEMY (free ICT learning portal). Learners have their own accounts (not admin users). docs/ACADEMY.md.
 * Every public POST carries the 'honeypot' + 'throttle' middleware (CSRF is automatic). Learner screens check the learner themselves (AcademyBaseController).
 * Static paths beat dynamic ones whatever the order.
 */

use App\Controllers\Web\AcademyAccountController;
use App\Controllers\Web\AcademyChallengesController;
use App\Controllers\Web\AcademyController;
use App\Controllers\Web\AcademyFacilitateController;
use App\Controllers\Web\AcademyLearnController;
use App\Controllers\Web\AcademyPortalController;

// ---- public ---------------------------------------------------------------------------------------------------------------
$router->get('/academy', [AcademyController::class, 'index'])->name('academy.index');
$router->get('/academy/courses/{slug}', [AcademyController::class, 'course'])->name('academy.course');
$router->get('/academy/partner-courses', [AcademyController::class, 'partners'])->name('academy.partners');
$router->get('/academy/classes', [AcademyController::class, 'classes'])->name('academy.classes');
$router->get('/academy/classes/{id}/calendar.ics', [AcademyController::class, 'calendar'])->middleware('throttle:60,60')->name('academy.class.ics');
$router->get('/academy/certificate/{code}', [AcademyController::class, 'certificate'])->middleware('throttle:120,60')->name('academy.certificate');
$router->get('/academy/certificate/{code}/pdf', [AcademyController::class, 'certificatePdf'])->middleware('throttle:60,60')->name('academy.certificate.pdf');

// ---- accounts -------------------------------------------------------------------------------------------------------------
$router->get('/academy/register', [AcademyAccountController::class, 'registerForm'])->name('academy.register');
$router->post('/academy/register', [AcademyAccountController::class, 'register'])->middleware(['honeypot', 'throttle:10,600'])->name('academy.register.post');
$router->get('/academy/check-email', [AcademyAccountController::class, 'checkEmail'])->name('academy.check_email');
$router->post('/academy/verify/resend', [AcademyAccountController::class, 'resend'])->middleware(['honeypot', 'throttle:6,600'])->name('academy.verify.resend');
$router->post('/academy/verify/code', [AcademyAccountController::class, 'verifyCode'])->middleware(['honeypot', 'throttle:20,600'])->name('academy.verify.code');
$router->post('/academy/verify/whatsapp', [AcademyAccountController::class, 'verifyWhatsApp'])->middleware(['honeypot', 'throttle:8,600'])->name('academy.verify.whatsapp');
$router->get('/academy/verify/{token}', [AcademyAccountController::class, 'verify'])->middleware('throttle:30,600')->name('academy.verify');
$router->get('/academy/pending', [AcademyAccountController::class, 'pending'])->name('academy.pending');
$router->get('/academy/google', [AcademyAccountController::class, 'googleStart'])->middleware('throttle:30,600')->name('academy.google');
$router->get('/academy/google/callback', [AcademyAccountController::class, 'googleCallback'])->middleware('throttle:30,600')->name('academy.google.callback');
$router->get('/academy/login', [AcademyAccountController::class, 'loginForm'])->name('academy.login');
$router->post('/academy/login', [AcademyAccountController::class, 'login'])->middleware(['honeypot', 'throttle:20,600'])->name('academy.login.post');
$router->post('/academy/logout', [AcademyAccountController::class, 'logout'])->name('academy.logout');
$router->get('/academy/forgot', [AcademyAccountController::class, 'forgotForm'])->name('academy.forgot');
$router->post('/academy/forgot', [AcademyAccountController::class, 'forgot'])->middleware(['honeypot', 'throttle:6,600'])->name('academy.forgot.post');
$router->get('/academy/reset/{token}', [AcademyAccountController::class, 'resetForm'])->name('academy.reset');
$router->post('/academy/reset/{token}', [AcademyAccountController::class, 'reset'])->middleware(['honeypot', 'throttle:10,600'])->name('academy.reset.post');
$router->get('/academy/account', [AcademyAccountController::class, 'account'])->name('academy.account');
$router->post('/academy/account/profile', [AcademyAccountController::class, 'updateProfile'])->middleware('throttle:20,600')->name('academy.account.profile');
$router->post('/academy/account/password', [AcademyAccountController::class, 'changePassword'])->middleware('throttle:10,600')->name('academy.account.password');
$router->get('/academy/account/photo', [AcademyAccountController::class, 'photo'])->middleware('throttle:120,60')->name('academy.account.photo');
$router->post('/academy/account/photo', [AcademyAccountController::class, 'savePhoto'])->middleware('throttle:10,600')->name('academy.account.photo.save');
$router->post('/academy/account/photo/remove', [AcademyAccountController::class, 'removePhoto'])->middleware('throttle:10,600')->name('academy.account.photo.remove');
$router->get('/academy/learners/{id}/photo', [AcademyAccountController::class, 'publicPhoto'])->middleware('throttle:300,60')->name('academy.learner.photo');
$router->post('/academy/account/delete', [AcademyAccountController::class, 'deleteAccount'])->middleware('throttle:5,600')->name('academy.account.delete');

// ---- learning -------------------------------------------------------------------------------------------------------------
$router->get('/academy/dashboard', [AcademyPortalController::class, 'overview'])->name('academy.dashboard');
$router->get('/academy/portal/courses', [AcademyPortalController::class, 'courses'])->name('academy.portal.courses');
$router->get('/academy/portal/certificates', [AcademyPortalController::class, 'certificates'])->name('academy.portal.certificates');
$router->get('/academy/portal/messages', [AcademyPortalController::class, 'messages'])->name('academy.portal.messages');
$router->post('/academy/portal/messages', [AcademyPortalController::class, 'sendMessage'])->middleware('throttle:30,600')->name('academy.portal.messages.send');
$router->get('/academy/portal/security', [AcademyPortalController::class, 'security'])->name('academy.portal.security');
$router->post('/academy/courses/{slug}/enroll', [AcademyLearnController::class, 'enroll'])->middleware('throttle:30,600')->name('academy.enroll');
$router->post('/academy/courses/{slug}/certificate', [AcademyLearnController::class, 'requestCertificate'])->middleware('throttle:20,600')->name('academy.certificate.request');
$router->post('/academy/courses/{slug}/leave', [AcademyLearnController::class, 'unenroll'])->middleware('throttle:10,600')->name('academy.leave');
$router->get('/academy/learn/{slug}', [AcademyLearnController::class, 'resume'])->name('academy.learn');
$router->get('/academy/learn/{slug}/lesson/{id}', [AcademyLearnController::class, 'lesson'])->name('academy.lesson');
$router->post('/academy/learn/{slug}/lesson/{id}/complete', [AcademyLearnController::class, 'complete'])->middleware('throttle:200,600')->name('academy.lesson.complete');
$router->post('/academy/learn/{slug}/lesson/{id}/reopen', [AcademyLearnController::class, 'reopen'])->middleware('throttle:60,600')->name('academy.lesson.reopen');
$router->get('/academy/learn/{slug}/lesson/{id}/file', [AcademyLearnController::class, 'file'])->middleware('throttle:60,60')->name('academy.lesson.file');
$router->get('/academy/learn/{slug}/quiz/{id}', [AcademyLearnController::class, 'quiz'])->name('academy.quiz');
$router->post('/academy/learn/{slug}/quiz/{id}/start', [AcademyLearnController::class, 'startQuiz'])->middleware('throttle:60,600')->name('academy.quiz.start');
$router->get('/academy/learn/{slug}/attempt/{id}', [AcademyLearnController::class, 'attempt'])->name('academy.attempt');
$router->post('/academy/learn/{slug}/attempt/{id}', [AcademyLearnController::class, 'submit'])->middleware('throttle:60,600')->name('academy.attempt.submit');

// ---- volunteer facilitators, challenges and winners (docs/ACADEMY.md) --------------------------------------------------------
$router->get('/academy/facilitate', [AcademyFacilitateController::class, 'form'])->name('academy.facilitate');
$router->post('/academy/facilitate', [AcademyFacilitateController::class, 'submit'])->middleware(['honeypot', 'throttle:10,600'])->name('academy.facilitate.post');
$router->get('/academy/facilitate/thanks', [AcademyFacilitateController::class, 'thanks'])->name('academy.facilitate.thanks');

$router->get('/academy/challenges', [AcademyChallengesController::class, 'index'])->name('academy.challenges');
$router->get('/academy/challenges/{slug}', [AcademyChallengesController::class, 'show'])->name('academy.challenge');
$router->post('/academy/challenges/{slug}/apply', [AcademyChallengesController::class, 'apply'])->middleware('throttle:20,600')->name('academy.challenge.apply');
$router->post('/academy/challenges/{slug}/withdraw', [AcademyChallengesController::class, 'withdraw'])->middleware('throttle:20,600')->name('academy.challenge.withdraw');
$router->post('/academy/challenges/{slug}/photo', [AcademyChallengesController::class, 'photo'])->middleware('throttle:20,600')->name('academy.challenge.photo');
$router->post('/academy/challenges/{slug}/quiz/start', [AcademyChallengesController::class, 'quizStart'])->middleware('throttle:20,600')->name('academy.challenge.quiz.start');
$router->get('/academy/challenges/{slug}/quiz', [AcademyChallengesController::class, 'quiz'])->name('academy.challenge.quiz');
$router->post('/academy/challenges/{slug}/quiz/submit', [AcademyChallengesController::class, 'quizSubmit'])->middleware('throttle:20,600')->name('academy.challenge.quiz.submit');
$router->post('/academy/challenges/{slug}/project', [AcademyChallengesController::class, 'project'])->middleware('throttle:30,600')->name('academy.challenge.project');
$router->get('/academy/winners', [AcademyChallengesController::class, 'winners'])->name('academy.winners');
$router->get('/academy/winners/{id}/photo', [AcademyChallengesController::class, 'winnerPhoto'])->middleware('throttle:300,60')->name('academy.winner.photo');
