<?php
/**
 * LAF Academy home. Vars: $courses, $counts (category => n), $filters, $filtered, $stats, $upcoming (classes), $categories, $levels, $learner.
 * Copy: content('academy', ...) - admin: Pages > LAF Academy. Courses: Admin > LAF Academy > Courses.
 */
use App\Services\Academy\ClassService;

$heroImg = content_img('academy', 'hero.image');
$actions = [];
if ($learner === null) {
    foreach (['hero.cta1', 'hero.cta2'] as $i => $key) {
        $b = content_btn('academy', $key);
        if ($b['has']) {
            $actions[] = ['label' => $b['label'], 'url' => $b['url'], 'class' => $i === 0 ? 'btn--cta btn--caps' : 'btn--photo btn--caps'];
        }
    }
} else {
    $actions[] = ['label' => 'Continue learning', 'url' => url('academy/dashboard'), 'class' => 'btn--cta btn--caps'];
    $b = content_btn('academy', 'hero.cta2');
    if ($b['has']) {
        $actions[] = ['label' => $b['label'], 'url' => $b['url'], 'class' => 'btn--photo btn--caps'];
    }
}
$steps = array_values(array_filter(content_list('academy', 'steps.items'), static fn(array $s): bool => trim((string) ($s['title'] ?? '')) !== ''));
$why = array_values(array_filter(content_list('academy', 'why.items'), static fn(array $s): bool => trim((string) ($s['title'] ?? '')) !== ''));
$cta = content_btn('academy', 'cta.button');
?>
<?= partial('partials/page-hero', [
    'title'   => content('academy', 'hero.title'),
    'eyebrow' => content('academy', 'hero.eyebrow'),
    'lead'    => content('academy', 'hero.lead'),
    'image'   => $heroImg['has'] ? ['src' => $heroImg['src'], 'alt' => $heroImg['alt'], 'focus' => '50% 35%'] : null,
    'actions' => $actions,
]) ?>

<section class="ac-stats" aria-label="The Academy in numbers">
    <div class="container">
        <ul class="ac-stats__list">
            <li><b><?= (int) $stats['courses'] ?></b><span>Free courses</span></li>
            <li><b><?= (int) $stats['lessons'] ?></b><span>Lessons</span></li>
            <li><b><?= (int) $stats['learners'] ?></b><span>Learners</span></li>
            <li><b><?= (int) $stats['certificates'] ?></b><span>Certificates issued</span></li>
        </ul>
    </div>
</section>

<?php section_begin('academy', 'steps', ['class' => 'section--tint', 'labelledby' => 'ac-steps-title']); ?>
    <?= partial('partials/section-head', ['eyebrow' => (string) content('academy', 'steps.eyebrow'), 'title' => (string) content('academy', 'steps.title'), 'align' => 'center', 'id' => 'ac-steps-title']) ?>
    <?php if ($steps): ?>
        <ol class="ia-steps">
            <?php foreach ($steps as $i => $s): ?>
                <li class="ia-step" data-reveal>
                    <span class="ia-step__num" aria-hidden="true"><?= (int) $i + 1 ?></span>
                    <p class="ia-step__text"><strong><?= e($s['title']) ?></strong><br><?= e($s['text'] ?? '') ?></p>
                </li>
            <?php endforeach; ?>
        </ol>
    <?php endif; ?>
<?php section_end(); ?>

<?php section_begin('academy', 'courses', ['id' => 'courses', 'labelledby' => 'ac-courses-title']); ?>
    <?= partial('partials/section-head', ['eyebrow' => (string) content('academy', 'courses.eyebrow'), 'title' => (string) content('academy', 'courses.title'), 'lead' => (string) content('academy', 'courses.intro'), 'align' => 'center', 'id' => 'ac-courses-title']) ?>

    <form class="filter-bar opp-filter" method="get" action="<?= e(url('academy')) ?>#courses" role="search">
        <?= partial('partials/form-field', ['name' => 'q', 'id' => 'ac-q', 'label' => content('academy', 'courses.search_label'), 'value' => $filters['q'], 'maxlength' => 80,
            'placeholder' => content('academy', 'courses.search_placeholder'), 'class' => 'opp-filter__q']) ?>
        <?= partial('partials/form-field', ['name' => 'category', 'id' => 'ac-cat', 'label' => content('academy', 'courses.category_label'), 'type' => 'select', 'options' => $categories,
            'empty' => content('academy', 'courses.all_topics'), 'value' => $filters['category']]) ?>
        <?= partial('partials/form-field', ['name' => 'level', 'id' => 'ac-level', 'label' => content('academy', 'courses.level_label'), 'type' => 'select', 'options' => $levels,
            'empty' => content('academy', 'courses.all_levels'), 'value' => $filters['level']]) ?>
        <button class="btn btn--primary btn--caps" type="submit"><?= e(content('academy', 'courses.apply_filter')) ?></button>
        <?php if ($filtered): ?><a class="btn btn--ghost btn--caps filter-bar__reset" href="<?= e(url('academy')) ?>#courses"><?= e(content('academy', 'courses.reset')) ?></a><?php endif; ?>
    </form>

    <?php if ($counts): ?>
        <nav class="pills opp-pills" aria-label="<?= e(content('academy', 'courses.category_label')) ?>">
            <a class="pills__item<?= $filters['category'] === '' ? ' is-active' : '' ?>" href="<?= e(url('academy') . '#courses') ?>"<?= $filters['category'] === '' ? ' aria-current="true"' : '' ?>><?= e(content('academy', 'courses.all_topics')) ?> (<?= (int) array_sum($counts) ?>)</a>
            <?php foreach ($categories as $key => $label): if (empty($counts[$key])) { continue; } ?>
                <a class="pills__item<?= $filters['category'] === $key ? ' is-active' : '' ?>" href="<?= e(url('academy', ['category' => $key]) . '#courses') ?>"<?= $filters['category'] === $key ? ' aria-current="true"' : '' ?>><?= e($label) ?> (<?= (int) $counts[$key] ?>)</a>
            <?php endforeach; ?>
        </nav>
    <?php endif; ?>

    <?php if ($courses): ?>
        <div class="grid grid--3 grid--gap-lg post-grid ac-grid">
            <?php foreach ($courses as $course): ?><?= partial('web/academy/_course_card', ['course' => $course]) ?><?php endforeach; ?>
        </div>
    <?php else: ?>
        <?= partial('partials/empty-state', ['title' => content('academy', 'courses.empty_title'), 'text' => content('academy', 'courses.empty_text'), 'icon' => 'book']) ?>
    <?php endif; ?>
<?php section_end(); ?>

<?php if ($partners): section_begin('academy', 'partners', ['labelledby' => 'ac-partners-title']); ?>
    <?= partial('partials/section-head', ['eyebrow' => (string) content('academy', 'partners.eyebrow'), 'title' => (string) content('academy', 'partners.title'), 'lead' => (string) content('academy', 'partners.intro'), 'align' => 'center', 'id' => 'ac-partners-title']) ?>
    <div class="grid grid--3 grid--gap-lg post-grid ac-grid">
        <?php foreach ($partners as $p): ?><?= partial('web/academy/_partner_card', ['p' => $p]) ?><?php endforeach; ?>
    </div>
    <p class="ac-center"><a class="btn btn--outline btn--caps" href="<?= e(url('academy/partner-courses')) ?>"><?= e(content('academy', 'partners.button')) ?> (<?= (int) $partnerTotal ?>)</a></p>
<?php section_end(); endif; ?>

<?php if ($challenges): section_begin('academy', 'challenges', ['class' => 'section--tint', 'labelledby' => 'ac-chal-title']); ?>
    <?= partial('partials/section-head', ['eyebrow' => (string) content('academy', 'challenges.eyebrow'), 'title' => (string) content('academy', 'challenges.title'), 'lead' => (string) content('academy', 'challenges.intro'), 'align' => 'center', 'id' => 'ac-chal-title']) ?>
    <div class="grid grid--3 grid--gap-lg post-grid ac-grid">
        <?php foreach ($challenges as $c): ?><?= partial('web/academy/_challenge_card', ['c' => $c]) ?><?php endforeach; ?>
    </div>
    <p class="ac-center"><a class="btn btn--outline btn--caps" href="<?= e(url('academy/challenges')) ?>"><?= e(content('academy', 'challenges.button')) ?></a></p>
<?php section_end(); endif; ?>

<?php if ($winners): section_begin('academy', 'winners', ['labelledby' => 'ac-win-title']); ?>
    <?= partial('partials/section-head', ['title' => (string) content('academy', 'winners.home_title'), 'align' => 'center', 'id' => 'ac-win-title']) ?>
    <div class="ac-winners">
        <?php foreach ($winners as $w): ?>
            <article class="ac-winner<?= (int) $w['rank_no'] === 1 ? ' ac-winner--first' : '' ?>" data-reveal>
                <div class="ac-winner__pic">
                    <?php if ($w['photo_ok']): ?><img src="<?= e(url('academy/winners/' . (int) $w['id'] . '/photo')) ?>" alt="Photo of <?= e($w['display_name']) ?>" width="220" height="220" loading="lazy">
                    <?php else: ?><span class="ac-winner__initials" aria-hidden="true"><?= e(mb_strtoupper(mb_substr((string) $w['display_name'], 0, 1))) ?></span><?php endif; ?>
                    <span class="ac-winner__badge"><?= icon('award', 'icon--sm') ?> <?= e((string) $w['award']) ?></span>
                </div>
                <h3 class="ac-winner__name"><?= e($w['display_name']) ?></h3>
                <p class="ac-winner__meta"><a href="<?= e(url('academy/challenges/' . $w['challenge_slug'])) ?>"><?= e($w['challenge_title']) ?></a></p>
            </article>
        <?php endforeach; ?>
    </div>
    <p class="ac-center"><a class="btn btn--outline btn--caps" href="<?= e(url('academy/winners')) ?>">All winners</a></p>
<?php section_end(); endif; ?>

<?php $showcase = \App\Services\Academy\LearnerPortrait::showcase(12); if ($showcase): ?>
<section class="section section--sm" aria-labelledby="ac-learners-title">
    <div class="container">
        <header class="section-head section-head--center"><span class="eyebrow">Our learners</span><h2 class="section-head__title" id="ac-learners-title">Meet some of our learners</h2></header>
        <ul class="ac-learners">
            <?php foreach ($showcase as $p): ?>
                <li class="ac-learners__item"><img class="ac-learners__img" src="<?= e(url('academy/learners/' . $p['id'] . '/photo')) ?>" alt="<?= e($p['first']) ?>, a LAF Academy learner" width="120" height="120" loading="lazy"><span class="ac-learners__name"><?= e($p['first']) ?></span><?php if ($p['country'] !== ''): ?><span class="ac-learners__country"><?= e($p['country']) ?></span><?php endif; ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>
<?php endif; ?>

<?php section_begin('academy', 'facilitate', ['class' => 'section--tint', 'labelledby' => 'ac-fac-title']); ?>
    <?= partial('partials/section-head', ['eyebrow' => (string) content('academy', 'facilitate.eyebrow'), 'title' => (string) content('academy', 'facilitate.title'), 'lead' => (string) content('academy', 'facilitate.intro'), 'align' => 'center', 'id' => 'ac-fac-title']) ?>
    <p class="ac-center"><a class="btn btn--cta btn--caps" href="<?= e(url('academy/facilitate')) ?>"><?= e(content('academy', 'facilitate.button')) ?></a></p>
<?php section_end(); ?>

<?php section_begin('academy', 'why', ['labelledby' => 'ac-why-title']); ?>
    <?= partial('partials/section-head', ['title' => (string) content('academy', 'why.title'), 'align' => 'center', 'id' => 'ac-why-title']) ?>
    <?php if ($why): ?>
        <ul class="ac-why">
            <?php foreach ($why as $w): ?>
                <li class="ac-why__item" data-reveal><span class="ac-why__tick" aria-hidden="true"><?= icon('check') ?></span><div><strong><?= e($w['title']) ?></strong><p><?= e($w['text'] ?? '') ?></p></div></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
<?php section_end(); ?>

<?php if ($upcoming): section_begin('academy', 'classes', ['labelledby' => 'ac-classes-title']); ?>
    <?= partial('partials/section-head', ['title' => (string) content('academy', 'classes.title'), 'lead' => (string) content('academy', 'classes.intro'), 'align' => 'center', 'id' => 'ac-classes-title']) ?>
    <ul class="ac-classlist">
        <?php foreach ($upcoming as $cl): ?>
            <li class="ac-class" data-reveal>
                <time class="ac-class__date" datetime="<?= e(format_date($cl['start_at'], 'Y-m-d')) ?>"><b><?= e(format_date($cl['start_at'], 'j')) ?></b><span><?= e(format_date($cl['start_at'], 'M')) ?></span></time>
                <div class="ac-class__main">
                    <h3 class="ac-class__title"><?= e($cl['title']) ?></h3>
                    <p class="ac-class__meta"><?= icon('clock', 'icon--sm') ?> <?= e(format_datetime($cl['start_at'], 'D j M, H:i')) ?> - <?= e(format_datetime($cl['end_at'], 'H:i')) ?> EAT
                        <?php if (!empty($cl['course_title'])): ?> &middot; <?= e($cl['course_title']) ?><?php endif; ?>
                        <?php if (!empty($cl['facilitator_name'])): ?> &middot; <?= e($cl['facilitator_name']) ?><?php endif; ?></p>
                </div>
                <span class="opp-chip"><?= e(ClassService::PLATFORMS[$cl['platform']] ?? 'Online') ?></span>
            </li>
        <?php endforeach; ?>
    </ul>
    <p class="ac-center"><a class="btn btn--outline btn--caps" href="<?= e(url('academy/classes')) ?>"><?= e(content('academy', 'classes.button')) ?></a></p>
<?php section_end(); endif; ?>

<?php section_begin('academy', 'cta', ['class' => 'section--tint', 'labelledby' => 'ac-cta-title']); ?>
    <div class="cta" data-reveal>
        <h2 class="cta__title" id="ac-cta-title"><?= highlight((string) content('academy', 'cta.title')) ?></h2>
        <p class="cta__text"><?= e(content('academy', 'cta.text')) ?></p>
        <?php if ($cta['has'] && $learner === null): ?><p><a class="btn btn--cta btn--caps btn--lg" href="<?= e($cta['url']) ?>"><?= e($cta['label']) ?></a></p><?php endif; ?>
    </div>
<?php section_end(); ?>
