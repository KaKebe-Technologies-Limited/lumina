<?php
/**
 * Learner portal > Overview. Vars: $me, $tab, $unread, $portrait, $courses, $classes, $certificates, $latest (newest message or null), $stats (courses, completed, lessons, certificates),
 * $complete (pct, missing).
 */
use App\Services\Academy\CertificateService;
use App\Services\Academy\CourseService;
use App\Services\Academy\LearnerAuth;

$first = explode(' ', trim((string) $me['name']))[0];
$cont = null;
foreach ($courses as $c) {
    if ($c['completed_at'] === null && $c['status'] === 'published' && $c['resume_id'] !== null) {
        $cont = $c;
        break;
    }
}
$sw = LearnerAuth::supportWhatsapp();
?>
<?= partial('partials/page-hero', ['title' => 'Welcome, ' . $first, 'eyebrow' => 'LAF Academy | My portal', 'lead' => 'Your learning, your certificates and your messages with the Academy team, in one place.',
    'actions' => [['label' => 'Browse the courses', 'url' => url('academy') . '#courses', 'class' => 'btn--cta btn--caps']]]) ?>

<section class="section ac-dash">
    <div class="container">
        <?= partial('web/academy/portal/_nav', compact('me', 'tab', 'unread', 'portrait')) ?>

        <?php if (LearnerAuth::missingContact((array) $me)): ?>
            <p class="opp-notice" role="status"><?= icon('info', 'icon--sm') ?> Please add your phone number, country and WhatsApp number: <a href="<?= e(url('academy/account#details')) ?>">complete my details</a>.</p>
        <?php endif; ?>
        <?php if ((int) $unread > 0): ?>
            <p class="opp-notice ac-notice--yellow" role="status"><?= icon('message-circle', 'icon--sm') ?> You have <strong><?= (int) $unread ?> new message<?= (int) $unread === 1 ? '' : 's' ?></strong> from the Academy team. <a href="<?= e(url('academy/portal/messages')) ?>">Read <?= (int) $unread === 1 ? 'it' : 'them' ?></a></p>
        <?php endif; ?>

        <ul class="ac-pstats" aria-label="My learning in numbers">
            <li><strong><?= (int) $stats['courses'] ?></strong><span>Courses</span></li>
            <li><strong><?= (int) $stats['lessons'] ?></strong><span>Lessons done</span></li>
            <li><strong><?= (int) $stats['completed'] ?></strong><span>Courses completed</span></li>
            <li><strong><?= (int) $stats['certificates'] ?></strong><span>Certificates</span></li>
        </ul>

        <div class="ac-dash__grid">
            <div class="ac-dash__main">
                <?php if ($cont !== null): $p = $cont['progress']; ?>
                    <section class="ac-continue card" data-reveal>
                        <div>
                            <span class="ac-continue__eyebrow">Continue learning</span>
                            <h2 class="ac-continue__title"><?= e($cont['title']) ?></h2>
                            <div class="ac-progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= (int) $p['pct'] ?>" aria-label="Progress in <?= e($cont['title']) ?>"><span style="width:<?= (int) $p['pct'] ?>%"></span></div>
                            <p class="ac-mine__meta"><?= (int) $p['done'] ?> of <?= (int) $p['total'] ?> lessons (<?= (int) $p['pct'] ?>%)</p>
                        </div>
                        <a class="btn btn--cta btn--caps" href="<?= e(url('academy/learn/' . $cont['slug'] . '/lesson/' . (int) $cont['resume_id'])) ?>"><?= $p['done'] > 0 ? 'Continue' : 'Start' ?> <?= icon('arrow-right', 'icon--sm') ?></a>
                    </section>
                <?php endif; ?>

                <h2 class="ac-block__title">My courses</h2>
                <?php if ($courses === []): ?>
                    <?= partial('partials/empty-state', ['title' => content('academy', 'learn.dashboard_empty'), 'icon' => 'book', 'actions' => [['label' => 'Browse the courses', 'url' => url('academy') . '#courses', 'class' => 'btn--cta btn--caps']]]) ?>
                <?php endif; ?>
                <?php foreach (array_slice($courses, 0, 3) as $c): ?>
                    <?= partial('web/academy/portal/_course', ['c' => $c]) ?>
                <?php endforeach; ?>
                <?php if (count($courses) > 3): ?><p><a class="btn btn--outline btn--sm" href="<?= e(url('academy/portal/courses')) ?>">See all <?= count($courses) ?> courses</a></p><?php endif; ?>
            </div>

            <aside class="ac-dash__aside">
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
