<?php
/**
 * Learner portal > Overview (the dashboard). Vars: $me, $tab, $unread, $portrait, $courses (enrolled, with progress), $classes, $certificates, $latest (newest message or null),
 * $stats (courses, completed, lessons, certificates), $complete (pct, missing), $available (courses the learner can enrol in, at most 6), $availableTotal, $q (search words).
 * Styles: assets/css/portal.css (its own file, loaded here).
 */
use App\Services\Academy\CertificateService;
use App\Services\Academy\CourseService;
use App\Services\Academy\LearnerAuth;

$first = explode(' ', trim((string) $me['name']))[0];
$hasPhoto = !empty($portrait['photo_path']);
$cont = null;
foreach ($courses as $c) {
    if ($c['completed_at'] === null && $c['status'] === 'published' && $c['resume_id'] !== null) {
        $cont = $c;
        break;
    }
}
$sw = LearnerAuth::supportWhatsapp();
$active = (int) ($me['is_active'] ?? 1) === 1;
?>
<?php $this->start('head'); ?><link rel="stylesheet" href="<?= e(asset('css/portal.min.css')) ?>"><?php $this->end(); ?>

<section class="pt-hero" aria-label="My profile">
    <div class="container">
        <div class="pt-hero__inner">
            <div class="pt-avatar">
                <?php if ($hasPhoto): ?><img class="pt-avatar__img" src="<?= e(url('academy/account/photo')) ?>?v=<?= e(substr(md5((string) $portrait['photo_path']), 0, 8)) ?>" alt="My profile picture" width="176" height="176"><?php else: ?><span class="pt-avatar__img pt-avatar__img--initial" aria-hidden="true"><?= e(mb_strtoupper(mb_substr($first, 0, 1))) ?></span><?php endif; ?>
                <?php if (\App\Services\Academy\LearnerPortrait::ready()): ?>
                <form class="pt-avatar__form" method="post" action="<?= e(url('academy/account/photo')) ?>" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <input type="hidden" name="back" value="dashboard">
                    <?php if (!empty($portrait['photo_consent_at'])): ?><input type="hidden" name="consent" value="1"><?php endif; ?>
                    <label class="pt-avatar__btn"><?= icon('camera', 'icon--sm') ?> <?= $hasPhoto ? 'Change photo' : 'Add your photo' ?><input type="file" name="photo" accept="image/jpeg,image/png,image/webp" data-autosubmit aria-label="<?= $hasPhoto ? 'Change my profile picture' : 'Add my profile picture' ?>"></label>
                </form>
                <?php endif; ?>
            </div>
            <div class="pt-hero__text">
                <p class="pt-hero__eyebrow">LAF Academy | My portal</p>
                <h1 class="pt-hero__name">Welcome back, <?= e($first) ?></h1>
                <p class="pt-hero__meta"><?= e((string) $me['name']) ?> &middot; <?= e((string) $me['email']) ?></p>
                <div class="pt-hero__chips">
                    <?php if (!empty($me['reg_no'])): ?><span class="pt-chip"><?= icon('award', 'icon--sm') ?> Reg no. <?= e((string) $me['reg_no']) ?></span><?php endif; ?>
                    <span class="pt-chip <?= $active ? 'pt-chip--ok' : 'pt-chip--warn' ?>"><?= icon($active ? 'check-circle' : 'clock', 'icon--sm') ?> <?= $active ? 'Active learner' : 'Waiting for approval' ?></span>
                    <?php if (!empty($me['created_at'])): ?><span class="pt-chip"><?= icon('calendar', 'icon--sm') ?> Member since <?= e(format_date($me['created_at'], 'M Y')) ?></span><?php endif; ?>
                </div>
            </div>
            <div class="pt-hero__actions">
                <a class="pt-btn pt-btn--gold" href="#choose"><?= icon('book-open', 'icon--sm') ?> Choose a course</a>
                <a class="pt-btn pt-btn--ghost" href="<?= e(url('academy/portal/messages')) ?>"><?= icon('message-circle', 'icon--sm') ?> Messages<?php if ((int) $unread > 0): ?> <span class="pt-badge"><span class="visually-hidden">New: </span><?= (int) $unread ?></span><?php endif; ?></a>
                <a class="pt-btn pt-btn--ghost" href="<?= e(url('academy/account')) ?>"><?= icon('user', 'icon--sm') ?> Edit profile</a>
                <form method="post" action="<?= e(url('academy/logout')) ?>" class="pt-hero__out"><?= csrf_field() ?><button class="pt-btn pt-btn--ghost" type="submit"><?= icon('log-out', 'icon--sm') ?> Sign out</button></form>
            </div>
        </div>
    </div>
</section>

<div class="pt-tabs-wrap"><div class="container"><?= partial('web/academy/portal/_nav', ['me' => $me, 'tab' => $tab, 'unread' => $unread, 'portrait' => $portrait, 'showHead' => false]) ?></div></div>

<section class="pt-body ac-dash">
    <div class="container">
        <?php if (LearnerAuth::missingContact((array) $me)): ?>
            <p class="opp-notice" role="status"><?= icon('info', 'icon--sm') ?> Please add your phone number, country and WhatsApp number: <a href="<?= e(url('academy/account#details')) ?>">complete my details</a>.</p>
        <?php endif; ?>
        <?php if ((int) $unread > 0): ?>
            <p class="opp-notice ac-notice--yellow" role="status"><?= icon('message-circle', 'icon--sm') ?> You have <strong><?= (int) $unread ?> new message<?= (int) $unread === 1 ? '' : 's' ?></strong> from the Academy team. <a href="<?= e(url('academy/portal/messages')) ?>">Read <?= (int) $unread === 1 ? 'it' : 'them' ?></a></p>
        <?php endif; ?>

        <ul class="ac-pstats" aria-label="My learning in numbers">
            <li><span class="pt-stat__icon" aria-hidden="true"><?= icon('layers', 'icon--md') ?></span><strong><?= (int) $stats['courses'] ?></strong><span class="pt-stat__label">Courses</span></li>
            <li><span class="pt-stat__icon" aria-hidden="true"><?= icon('check-circle', 'icon--md') ?></span><strong><?= (int) $stats['lessons'] ?></strong><span class="pt-stat__label">Lessons done</span></li>
            <li><span class="pt-stat__icon" aria-hidden="true"><?= icon('graduation-cap', 'icon--md') ?></span><strong><?= (int) $stats['completed'] ?></strong><span class="pt-stat__label">Courses completed</span></li>
            <li><span class="pt-stat__icon" aria-hidden="true"><?= icon('award', 'icon--md') ?></span><strong><?= (int) $stats['certificates'] ?></strong><span class="pt-stat__label">Certificates</span></li>
        </ul>

        <div class="pt-layout">
            <div class="pt-main">
                <?php if ($cont !== null): $p = $cont['progress']; ?>
                    <section class="pt-continue" aria-label="Continue learning">
                        <div>
                            <span class="pt-continue__eyebrow">Continue learning</span>
                            <h2 class="pt-continue__title"><?= e($cont['title']) ?></h2>
                            <div class="ac-progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= (int) $p['pct'] ?>" aria-label="Progress in <?= e($cont['title']) ?>"><span style="width:<?= (int) $p['pct'] ?>%"></span></div>
                            <p class="pt-continue__meta"><?= (int) $p['done'] ?> of <?= (int) $p['total'] ?> lessons (<?= (int) $p['pct'] ?>%)</p>
                        </div>
                        <a class="pt-btn pt-btn--gold" href="<?= e(url('academy/learn/' . $cont['slug'] . '/lesson/' . (int) $cont['resume_id'])) ?>"><?= $p['done'] > 0 ? 'Continue' : 'Start' ?> <?= icon('arrow-right', 'icon--sm') ?></a>
                    </section>
                <?php endif; ?>

                <section class="pt-block" aria-labelledby="mine-title">
                    <div class="pt-block__head">
                        <div><h2 id="mine-title">My courses</h2><p><?= $courses === [] ? 'You have not started a course yet.' : 'Your progress in each course.' ?></p></div>
                        <?php if (count($courses) > 3): ?><a class="pt-btn pt-btn--line" href="<?= e(url('academy/portal/courses')) ?>">See all <?= count($courses) ?></a><?php endif; ?>
                    </div>
                    <?php if ($courses === []): ?>
                        <div class="pt-empty"><?= icon('book-open', 'icon--xl') ?><p><?= e(content('academy', 'learn.dashboard_empty')) ?></p><a class="pt-btn pt-btn--enrol" href="#choose">Choose your first course</a></div>
                    <?php endif; ?>
                    <?php foreach (array_slice($courses, 0, 3) as $c): ?><?= partial('web/academy/portal/_course', ['c' => $c]) ?><?php endforeach; ?>
                </section>

                <section class="pt-block" id="choose" aria-labelledby="choose-title">
                    <div class="pt-block__head">
                        <div><h2 id="choose-title">Choose a course and enrol</h2><p>Free courses with lessons, quizzes and certificates. <?= (int) $availableTotal ?> course<?= (int) $availableTotal === 1 ? '' : 's' ?> to choose from.</p></div>
                        <form class="pt-search" method="get" action="<?= e(url('academy/dashboard')) ?>#choose" role="search">
                            <label class="visually-hidden" for="pt-q">Search the courses</label>
                            <input id="pt-q" type="search" name="q" value="<?= e((string) $q) ?>" placeholder="Search: Excel, email, typing..." maxlength="60">
                            <button type="submit">Search</button>
                        </form>
                    </div>
                    <?php if ($available === []): ?>
                        <div class="pt-empty"><p><?= $q !== '' ? 'No course matches your search.' : 'You are enrolled in every course available right now. Well done!' ?></p><a class="pt-btn pt-btn--line" href="<?= e(url('academy')) ?>">See the whole catalogue</a></div>
                    <?php else: ?>
                        <div class="pt-grid">
                            <?php foreach ($available as $cr): $href = url('academy/courses/' . $cr['slug']); ?>
                                <article class="pt-course">
                                    <div class="pt-course__top ac-tone-<?= e((string) $cr['category']) ?>">
                                        <span class="pt-course__icon" aria-hidden="true"><?= icon(CourseService::icon($cr), 'icon--lg') ?></span>
                                        <span class="pt-tag"><?= e(CourseService::categoryLabel($cr['category'])) ?></span>
                                    </div>
                                    <div class="pt-course__body">
                                        <h3 class="pt-course__title"><a href="<?= e($href) ?>"><?= e($cr['title']) ?></a></h3>
                                        <?php if (trim((string) $cr['summary']) !== ''): ?><p class="pt-course__text"><?= e(mb_strimwidth((string) $cr['summary'], 0, 120, '...')) ?></p><?php endif; ?>
                                        <ul class="pt-course__facts">
                                            <li><span class="pt-tag pt-tag--level"><?= e(CourseService::LEVELS[$cr['level']] ?? ucfirst((string) $cr['level'])) ?></span></li>
                                            <li><?= icon('layers', 'icon--sm') ?> <?= (int) $cr['lessons'] ?> lessons</li>
                                            <li><?= icon('clock', 'icon--sm') ?> <?= e(academy_duration((int) $cr['minutes'])) ?></li>
                                            <?php if ((int) $cr['certificate_enabled'] === 1): ?><li><?= icon('award', 'icon--sm') ?> Certificate</li><?php endif; ?>
                                        </ul>
                                        <div class="pt-course__go">
                                            <form method="post" action="<?= e(url('academy/courses/' . $cr['slug'] . '/enroll')) ?>"><?= csrf_field() ?><button class="pt-btn pt-btn--enrol" type="submit"><?= icon('check', 'icon--sm') ?> Enrol now<span class="visually-hidden">: <?= e($cr['title']) ?></span></button></form>
                                            <a class="pt-btn pt-btn--line" href="<?= e($href) ?>">Details</a>
                                        </div>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                        <p class="pt-block__more"><a class="pt-btn pt-btn--line" href="<?= e(url('academy') . '#courses') ?>">Browse all courses <?= icon('arrow-right', 'icon--sm') ?></a></p>
                    <?php endif; ?>
                </section>
            </div>

            <aside class="pt-side">
                <section class="ac-panel" data-reveal>
                    <h2 class="ac-panel__title"><?= icon('user', 'icon--sm') ?> My profile</h2>
                    <div class="ac-meter" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= (int) $complete['pct'] ?>" aria-label="Profile complete"><span style="width:<?= (int) $complete['pct'] ?>%"></span></div>
                    <p class="ac-mine__meta"><?= (int) $complete['pct'] ?>% complete<?= $complete['missing'] !== [] ? '. Add ' . e(implode(', ', array_slice($complete['missing'], 0, 3))) . '.' : '. Well done!' ?></p>
                    <p><a href="<?= e(url('academy/account')) ?>">Update my profile</a></p>
                </section>
                <section class="ac-panel" data-reveal>
                    <h2 class="ac-panel__title"><?= icon('message-circle', 'icon--sm') ?> Messages</h2>
                    <?php if ($latest === null): ?><p class="text-muted">No message yet. Write to the Academy team any time.</p>
                    <?php else: ?><p class="ac-msgpreview"><strong><?= $latest['sender'] === 'admin' ? 'Academy team' : 'You' ?>:</strong> <?= e(mb_strimwidth((string) $latest['body'], 0, 110, '...')) ?><br><small class="text-muted"><?= e(format_datetime($latest['created_at'], 'j M, H:i')) ?></small></p><?php endif; ?>
                    <p><a href="<?= e(url('academy/portal/messages')) ?>">Open my messages</a></p>
                </section>
                <section class="ac-panel" data-reveal>
                    <h2 class="ac-panel__title"><?= icon('video', 'icon--sm') ?> Next classes</h2>
                    <?php if ($classes === []): ?><p class="text-muted">No class is scheduled right now.</p><?php endif; ?>
                    <ul class="ac-minilist">
                        <?php foreach ($classes as $cl): ?>
                            <li><strong><?= e($cl['title']) ?></strong><span><?= e(format_datetime($cl['start_at'], 'D j M, H:i')) ?> EAT</span></li>
                        <?php endforeach; ?>
                    </ul>
                    <p><a href="<?= e(url('academy/classes')) ?>">All virtual classes</a></p>
                </section>
                <section class="ac-panel" data-reveal>
                    <h2 class="ac-panel__title"><?= icon('award', 'icon--sm') ?> My certificates</h2>
                    <?php $waiting = CertificateService::pendingForLearner((int) $me['id']); ?>
                    <?php if ($certificates === [] && $waiting === []): ?><p class="text-muted">Pass a final exam to earn your first certificate.</p><?php endif; ?>
                    <?php foreach ($waiting as $wc): ?><p class="text-muted"><?= icon('clock', 'icon--sm') ?> <?= e($wc['course_title']) ?>: <?= e(CertificateService::waitingShort()) ?></p><?php endforeach; ?>
                    <ul class="ac-minilist">
                        <?php foreach ($certificates as $ce): ?>
                            <li><a href="<?= e(url('academy/certificate/' . $ce['code'])) ?>"><?= e($ce['course_title']) ?></a><span><?= $ce['revoked_at'] !== null ? 'Withdrawn' : e(format_date($ce['issued_at'])) ?></span></li>
                        <?php endforeach; ?>
                    </ul>
                    <p><a href="<?= e(url('academy/portal/certificates')) ?>">All my certificates</a></p>
                </section>
                <?php if ($sw !== null): ?>
                <section class="ac-panel" data-reveal>
                    <h2 class="ac-panel__title"><?= icon('whatsapp', 'icon--sm') ?> Need help?</h2>
                    <p>Quick questions? Chat with the Academy team on WhatsApp <a href="<?= e($sw['url']) ?>" target="_blank" rel="noopener"><?= e($sw['display']) ?></a>, or ask Bono AI in the corner of the page.</p>
                </section>
                <?php endif; ?>
            </aside>
        </div>
    </div>
</section>
<?php $this->start('scripts'); ?><script src="<?= e(asset('js/academy.js')) ?>" defer></script><?php $this->end(); ?>
