<?php
/** Learner dashboard. Vars: $me, $courses (with progress), $classes, $certificates, $learner. */
use App\Services\Academy\ClassService;
use App\Services\Academy\CourseService;
?>
<?= partial('partials/page-hero', ['title' => content('academy', 'learn.dashboard_title'), 'eyebrow' => 'Hello, ' . explode(' ', trim((string) $me['name']))[0], 'lead' => 'Pick up where you stopped, join your next class and open your certificates.',
    'actions' => [['label' => 'Browse the courses', 'url' => url('academy') . '#courses', 'class' => 'btn--cta btn--caps']]]) ?>

<section class="section ac-dash">
    <div class="container">
        <div class="ac-dash__grid">
            <div class="ac-dash__main">
                <h2 class="ac-block__title">My courses</h2>
                <?php if ($courses === []): ?>
                    <?= partial('partials/empty-state', ['title' => content('academy', 'learn.dashboard_empty'), 'icon' => 'book', 'actions' => [['label' => 'Browse the courses', 'url' => url('academy') . '#courses', 'class' => 'btn--cta btn--caps']]]) ?>
                <?php endif; ?>
                <?php foreach ($courses as $c): $p = $c['progress']; $done = $c['completed_at'] !== null; ?>
                    <article class="ac-mine card" data-reveal>
                        <div class="ac-mine__icon ac-tone-<?= e((string) $c['category']) ?>" aria-hidden="true"><?= icon(CourseService::icon($c), 'icon--lg') ?></div>
                        <div class="ac-mine__body">
                            <h3 class="ac-mine__title"><a href="<?= e(url('academy/courses/' . $c['slug'])) ?>"><?= e($c['title']) ?></a>
                                <?php if ($done): ?><span class="opp-badge">Completed</span><?php endif; ?>
                                <?php if ($c['status'] !== 'published'): ?><span class="opp-badge opp-badge--soon">Not available now</span><?php endif; ?></h3>
                            <div class="ac-progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= (int) $p['pct'] ?>" aria-label="Progress in <?= e($c['title']) ?>"><span style="width:<?= (int) $p['pct'] ?>%"></span></div>
                            <p class="ac-mine__meta"><?= (int) $p['done'] ?> of <?= (int) $p['total'] ?> lessons (<?= (int) $p['pct'] ?>%)</p>
                            <div class="ac-mine__actions">
                                <?php if ($c['status'] === 'published' && $c['resume_id'] !== null): ?><a class="btn btn--cta btn--caps btn--sm" href="<?= e(url('academy/learn/' . $c['slug'] . '/lesson/' . (int) $c['resume_id'])) ?>"><?= $p['done'] > 0 ? 'Continue' : 'Start' ?></a><?php endif; ?>
                                <?php if ($c['certificate'] !== null): ?><a class="btn btn--outline btn--caps btn--sm" href="<?= e(url('academy/certificate/' . $c['certificate']['code'])) ?>"><?= icon('award', 'icon--sm') ?> Certificate</a>
                                <?php elseif (!empty($c['cert_pending'])): ?><span class="opp-badge opp-badge--soon"><?= icon('clock', 'icon--sm') ?> Certificate requested</span>
                                <?php elseif (!empty($c['cert_requestable'])): ?><form method="post" action="<?= e(url('academy/courses/' . $c['slug'] . '/certificate')) ?>" class="ac-cert-request"><?= csrf_field() ?><button class="btn btn--cta btn--caps btn--sm" type="submit"><?= icon('award', 'icon--sm') ?> Request my certificate</button></form><?php endif; ?>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <aside class="ac-dash__aside">
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
                    <?php $waiting = \App\Services\Academy\CertificateService::pendingForLearner((int) $me['id']); ?>
                    <?php if ($certificates === [] && $waiting === []): ?><p class="text-muted">Pass a final exam to earn your first certificate.</p><?php endif; ?>
                    <?php foreach ($waiting as $wc): ?><p class="text-muted"><?= icon('clock', 'icon--sm') ?> <?= e($wc['course_title']) ?>: waiting for approval</p><?php endforeach; ?>
                    <ul class="ac-minilist">
                        <?php foreach ($certificates as $ce): ?>
                            <li><a href="<?= e(url('academy/certificate/' . $ce['code'])) ?>"><?= e($ce['course_title']) ?></a><span><?= $ce['revoked_at'] !== null ? 'Withdrawn' : e(format_date($ce['issued_at'])) ?></span></li>
                        <?php endforeach; ?>
                    </ul>
                </section>
                <section class="ac-panel" data-reveal>
                    <h2 class="ac-panel__title"><?= icon('user', 'icon--sm') ?> My account</h2>
                    <?php $mine = \App\Services\Academy\LearnerPortrait::of((int) $me['id']); if (!empty($mine['photo_path'])): ?><img class="ac-learners__img" src="<?= e(url('academy/account/photo')) ?>?v=<?= e(substr(md5((string) $mine['photo_path']), 0, 8)) ?>" alt="My picture" width="104" height="104"><?php endif; ?>
                    <p><?= e($me['email']) ?></p>
                    <p><a href="<?= e(url('academy/account')) ?>">Change my details, picture or password</a></p>
                    <form method="post" action="<?= e(url('academy/logout')) ?>"><?= csrf_field() ?><button class="btn btn--ghost btn--sm" type="submit">Sign out</button></form>
                </section>
            </aside>
        </div>
    </div>
</section>
