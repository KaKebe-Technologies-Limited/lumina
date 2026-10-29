<?php
/**
 * One course. Vars: $course, $tree, $enrolled, $progress, $resume, $certificate, $draft, $facilitator, $related, $outcomes, $requirements, $resources, $learner.
 */
use App\Services\Academy\CourseService;
use App\Services\Academy\ClassService;

$level = CourseService::LEVELS[$course['level']] ?? ucfirst((string) $course['level']);
$minutes = (int) $tree['minutes'];
$self = url('academy/courses/' . $course['slug']);
$loginUrl = url('academy/login', ['next' => 'academy/courses/' . $course['slug']]);
$registerUrl = url('academy/register', ['next' => 'academy/courses/' . $course['slug']]);
$this->start('head');
echo '<link rel="stylesheet" href="' . e(asset('css/catalog.min.css')) . '">';      // the related-course cards
$this->end();
?>
<?= partial('partials/page-hero', [
    'title'   => $course['title'],
    'eyebrow' => CourseService::categoryLabel($course['category']) . ' - ' . $level,
    'lead'    => (string) $course['summary'],
]) ?>

<section class="section ac-course" aria-label="<?= e($course['title']) ?>">
    <div class="container">
        <?php if ($draft): ?><p class="opp-notice opp-notice--closed" role="status"><?= icon('eye', 'icon--sm') ?> Preview: this course is not published yet, so visitors cannot see it.</p><?php endif; ?>
        <div class="ac-layout">
            <div class="ac-layout__main">
                <?php if (trim((string) $course['description']) !== ''): ?>
                    <section class="ac-block" data-reveal><h2 class="ac-block__title">About this course</h2><div class="prose"><?= rich($course['description']) ?></div></section>
                <?php endif; ?>

                <?php if ($outcomes): ?>
                    <section class="ac-block" data-reveal>
                        <h2 class="ac-block__title">What you will learn</h2>
                        <ul class="ia-checklist ac-outcomes">
                            <?php foreach ($outcomes as $o): ?><li><span class="ia-checklist__tick" aria-hidden="true"><?= icon('check') ?></span><span><?= e($o) ?></span></li><?php endforeach; ?>
                        </ul>
                    </section>
                <?php endif; ?>

                <section class="ac-block" data-reveal aria-labelledby="ac-curriculum">
                    <h2 class="ac-block__title" id="ac-curriculum">Course content</h2>
                    <p class="ac-block__sub"><?= count($tree['modules']) ?> modules &middot; <?= (int) $tree['lessons'] ?> lessons &middot; <?= e(academy_duration($minutes)) ?> &middot; <?= (int) $tree['quizzes'] ?> quizzes</p>
                    <?php if ($tree['modules'] === []): ?>
                        <p class="text-muted">The lessons of this course are being prepared.</p>
                    <?php endif; ?>
                    <?php foreach ($tree['modules'] as $mi => $m): $mLessons = count($m['lessons']); ?>
                        <details class="ac-module"<?= $mi === 0 ? ' open' : '' ?>>
                            <summary><span class="ac-module__title"><?= e($m['title']) ?></span><span class="ac-module__meta"><?= (int) $mLessons ?> lessons</span></summary>
                            <?php if (trim((string) $m['summary']) !== ''): ?><p class="ac-module__summary"><?= e($m['summary']) ?></p><?php endif; ?>
                            <ol class="ac-lessons">
                                <?php foreach ($m['lessons'] as $l):
                                    $isDone = $progress !== null && in_array((int) $l['id'], $progress['done_ids'], true); ?>
                                    <li class="ac-lessons__item<?= $isDone ? ' is-done' : '' ?>">
                                        <span class="ac-lessons__mark" aria-hidden="true"><?= $isDone ? icon('check-circle') : icon($l['kind'] === 'video' ? 'play' : ($l['kind'] === 'file' ? 'download' : 'file-text')) ?></span>
                                        <?php if ($enrolled !== null): ?><a href="<?= e(url('academy/learn/' . $course['slug'] . '/lesson/' . (int) $l['id'])) ?>"><?= e($l['title']) ?></a>
                                        <?php else: ?><span><?= e($l['title']) ?></span><?php endif; ?>
                                        <span class="ac-lessons__min"><?= (int) $l['minutes'] ?> min</span>
                                    </li>
                                <?php endforeach; ?>
                                <?php if ($m['quiz'] !== null): ?>
                                    <li class="ac-lessons__item ac-lessons__item--quiz"><span class="ac-lessons__mark" aria-hidden="true"><?= icon('help-circle') ?></span>
                                        <?php if ($enrolled !== null): ?><a href="<?= e(url('academy/learn/' . $course['slug'] . '/quiz/' . (int) $m['quiz']['id'])) ?>">Quiz: <?= (int) $m['quiz']['questions'] ?> questions</a>
                                        <?php else: ?><span>Quiz: <?= (int) $m['quiz']['questions'] ?> questions</span><?php endif; ?></li>
                                <?php endif; ?>
                            </ol>
                        </details>
                    <?php endforeach; ?>
                    <?php if ($tree['exam'] !== null): ?>
                        <div class="ac-exam">
                            <span class="ac-exam__icon" aria-hidden="true"><?= icon('award', 'icon--lg') ?></span>
                            <div><strong>Final exam</strong><p><?= (int) $tree['exam']['questions'] ?> questions. Pass with <?= (int) $tree['exam']['pass_mark'] ?>% or more to earn your certificate.</p></div>
                        </div>
                    <?php endif; ?>
                </section>

                <?php if ($requirements): ?>
                    <section class="ac-block" data-reveal><h2 class="ac-block__title">What you need</h2><ul class="ac-list"><?php foreach ($requirements as $r): ?><li><?= e($r) ?></li><?php endforeach; ?></ul></section>
                <?php endif; ?>
                <?php if ($resources): ?>
                    <section class="ac-block" data-reveal><h2 class="ac-block__title">Further reading</h2>
                        <ul class="ac-list"><?php foreach ($resources as $r): ?><li><a href="<?= e($r['url']) ?>" target="_blank" rel="noopener noreferrer nofollow"><?= e($r['title']) ?></a> <?= icon('external-link', 'icon--sm') ?></li><?php endforeach; ?></ul></section>
                <?php endif; ?>
            </div>

            <aside class="ac-layout__aside" aria-label="Enrol">
                <div class="ac-enrol" data-reveal>
                    <?php if ($enrolled !== null && $progress !== null): ?>
                        <p class="ac-enrol__status"><?= $enrolled['completed_at'] !== null ? 'Course completed' : 'You are enrolled' ?></p>
                        <div class="ac-progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= (int) $progress['pct'] ?>" aria-label="Your progress"><span style="width:<?= (int) $progress['pct'] ?>%"></span></div>
                        <p class="ac-enrol__pct"><?= (int) $progress['done'] ?> of <?= (int) $progress['total'] ?> lessons done (<?= (int) $progress['pct'] ?>%)</p>
                        <?php if ($resume !== null): ?><a class="btn btn--cta btn--caps btn--block" href="<?= e(url('academy/learn/' . $course['slug'] . '/lesson/' . $resume)) ?>"><?= $progress['done'] > 0 ? 'Continue learning' : 'Start learning' ?></a><?php endif; ?>
                        <?php if ($certificate !== null): ?><a class="btn btn--outline btn--caps btn--block" href="<?= e(url('academy/certificate/' . $certificate['code'])) ?>"><?= icon('award', 'icon--sm') ?> My certificate</a><?php elseif (!empty($certificatePending)): ?><p class="ac-enrol__status"><?= icon('clock', 'icon--sm') ?> Certificate filed on <?= e(format_date($certificatePending['issued_at'], 'j M Y')) ?>: <?= e(\App\Services\Academy\CertificateService::waitingShort()) ?></p><?php elseif (!empty($certificateRequestable)): ?><form method="post" action="<?= e(url('academy/courses/' . $course['slug'] . '/certificate')) ?>" class="ac-cert-request"><?= csrf_field() ?><button class="btn btn--cta btn--caps btn--block" type="submit"><?= icon('award', 'icon--sm') ?> Request my certificate</button></form><p class="text-muted ac-enrol__note">You finished the course. Ask for your certificate: the Academy team approves it, then you can download it.</p><?php endif; ?>
                    <?php elseif ($learner !== null): ?>
                        <p class="ac-enrol__status">Free. Start now.</p>
                        <?php if ($tree['lessons'] > 0): ?>
                            <form method="post" action="<?= e(url('academy/courses/' . $course['slug'] . '/enroll')) ?>"><?= csrf_field() ?>
                                <button class="btn btn--cta btn--caps btn--block" type="submit">Enrol and start</button></form>
                        <?php else: ?><p class="text-muted">No lessons yet. Please check back soon.</p><?php endif; ?>
                    <?php else: ?>
                        <p class="ac-enrol__status">Free course</p>
                        <a class="btn btn--cta btn--caps btn--block" href="<?= e($registerUrl) ?>">Create a free account</a>
                        <a class="btn btn--outline btn--caps btn--block" href="<?= e($loginUrl) ?>">I already have an account</a>
                    <?php endif; ?>
                    <dl class="ac-facts">
                        <div><dt>Level</dt><dd><?= e($level) ?></dd></div>
                        <div><dt>Lessons</dt><dd><?= (int) $tree['lessons'] ?></dd></div>
                        <div><dt>Time</dt><dd><?= e(academy_duration($minutes)) ?></dd></div>
                        <div><dt>Quizzes</dt><dd><?= (int) $tree['quizzes'] ?></dd></div>
                        <div><dt>Certificate</dt><dd><?= (int) $course['certificate_enabled'] === 1 ? 'Yes, free' : 'No' ?></dd></div>
                        <?php if ($facilitator): ?><div><dt>Facilitator</dt><dd><?= e($facilitator) ?></dd></div><?php endif; ?>
                    </dl>
                    <p class="ac-enrol__more"><a href="<?= e(url('academy/classes')) ?>"><?= icon('video', 'icon--sm') ?> Virtual classes</a></p>
                </div>
            </aside>
        </div>
    </div>
</section>

<?php if ($related): ?>
    <section class="section section--tint" aria-labelledby="ac-related">
        <div class="container">
            <?= partial('partials/section-head', ['title' => 'More courses to explore', 'align' => 'center', 'id' => 'ac-related']) ?>
            <div class="grid grid--3 grid--gap-lg post-grid ac-grid">
                <?php foreach ($related as $r): ?><?= partial('web/academy/_course_card', ['course' => $r]) ?><?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>
