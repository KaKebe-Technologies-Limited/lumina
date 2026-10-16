<?php
/**
 * Careers. Vars: $jobs (open vacancies). Copy: content('careers', ...) - admin: Pages > Careers; vacancies: Content > Jobs.
 * Order: hero -> openings -> why join (photos + ticks) -> recruitment steps -> volunteer panel -> equal opportunity -> FAQ with photo ->
 * talent-network form -> closing band.
 */
$this->start('scripts'); ?>
<script src="<?= e(asset('js/pages/careers.js')) ?>" defer></script>
<?php $this->end();

$heroImg = content_img('careers', 'hero.image');
$actions = [];
foreach (['hero.cta1', 'hero.cta2'] as $i => $key) {
    $b = content_btn('careers', $key);
    if ($b['has']) {
        $actions[] = ['label' => $b['label'], 'url' => $b['url'], 'class' => $i === 0 ? 'btn--cta btn--caps' : 'btn--photo btn--caps'];
    }
}
?>
<?= partial('partials/page-hero', [
    'title'   => content('careers', 'hero.title'),
    'eyebrow' => content('careers', 'hero.eyebrow'),
    'lead'    => content('careers', 'hero.lead'),
    'image'   => $heroImg['has'] ? ['src' => $heroImg['src'], 'alt' => $heroImg['alt'], 'focus' => '50% 30%'] : null,
    'actions' => $actions,
]) ?>

<?php /* ------------------------------------------------------------------ OPENINGS (always on) */ ?>
<?php section_begin('careers', 'openings', ['id' => 'openings', 'labelledby' => 'openings-title']); ?>
    <?= partial('partials/section-head', ['eyebrow' => (string) content('careers', 'openings.eyebrow'), 'title' => (string) content('careers', 'openings.title'), 'lead' => (string) content('careers', 'openings.intro'), 'align' => 'center', 'id' => 'openings-title']) ?>
    <?php if ($jobs): ?>
        <?php if (trim((string) content('careers', 'openings.available_label')) !== ''): ?><h3 class="job-list__title"><?= e(content('careers', 'openings.available_label')) ?></h3><?php endif; ?>
        <div class="job-list">
            <?php foreach ($jobs as $job): ?><?= partial('web/careers/_job_card', ['job' => $job]) ?><?php endforeach; ?>
        </div>
    <?php else: $none = content_btn('careers', 'openings.none_button'); ?>
        <div class="no-openings" data-reveal>
            <span class="no-openings__icon"><?= icon('briefcase', 'icon--xl') ?></span>
            <p class="no-openings__title"><?= e(content('careers', 'openings.none_text')) ?></p>
            <?php if (trim((string) content('careers', 'openings.none_help')) !== ''): ?><p class="no-openings__text"><?= e(content('careers', 'openings.none_help')) ?></p><?php endif; ?>
            <?php if ($none['has'] && section_on('careers', 'apply')): ?><p><a class="btn btn--cta btn--caps" href="<?= e($none['url']) ?>"><?= e($none['label']) ?></a></p><?php endif; ?>
        </div>
    <?php endif; ?>
<?php section_end(); ?>

<?php /* ------------------------------------------------------------------ WHY JOIN US */ ?>
<?php
$whyImgs = [content_img('careers', 'why.image'), content_img('careers', 'why.image2')];
$whyHasMedia = (bool) array_filter($whyImgs, static fn(array $i): bool => $i['has']);
$reasons = array_values(array_filter(content_list('careers', 'why.items'), static fn(array $r): bool => trim((string) ($r['text'] ?? '')) !== ''));
section_begin('careers', 'why', ['class' => 'section--tint', 'labelledby' => 'why-title']); ?>
    <div class="split split--start<?= $whyHasMedia ? ' split--wide-media' : ' ia-split--solo' ?>">
        <?= partial('partials/photo-stack', ['images' => $whyImgs]) ?>
        <div data-reveal>
            <?= partial('partials/section-head', ['eyebrow' => (string) content('careers', 'why.eyebrow'), 'title' => (string) content('careers', 'why.title'), 'id' => 'why-title']) ?>
            <ul class="ia-checklist ia-checklist--roomy">
                <?php foreach ($reasons as $r): ?>
                    <li><span class="ia-checklist__tick" aria-hidden="true"><?= icon('check') ?></span><span><?= e($r['text']) ?></span></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
<?php section_end(); ?>

<?php /* ------------------------------------------------------------------ RECRUITMENT PROCESS */ ?>
<?php section_begin('careers', 'process', ['labelledby' => 'process-title']); $steps = array_values(array_filter(content_list('careers', 'process.steps'), static fn(array $s): bool => trim((string) ($s['text'] ?? '')) !== '')); ?>
    <?= partial('partials/section-head', ['eyebrow' => (string) content('careers', 'process.eyebrow'), 'title' => (string) content('careers', 'process.title'), 'align' => 'center', 'id' => 'process-title']) ?>
    <?php if ($steps): ?>
        <ol class="ia-steps">
            <?php foreach ($steps as $i => $s): ?>
                <li class="ia-step" data-reveal>
                    <span class="ia-step__num" aria-hidden="true"><?= (int) $i + 1 ?></span>
                    <p class="ia-step__text"><?= e($s['text']) ?></p>
                </li>
            <?php endforeach; ?>
        </ol>
    <?php endif; ?>
<?php section_end(); ?>

<?php /* ------------------------------------------------------------------ VOLUNTEER (deep navy panel) */ ?>
<?php section_begin('careers', 'volunteer', ['class' => 'careers-volunteer', 'id' => 'volunteer', 'labelledby' => 'volunteer-title']);
$roles = array_values(array_filter(content_list('careers', 'volunteer.roles'), static fn(array $r): bool => trim((string) ($r['text'] ?? '')) !== ''));
$btn = content_btn('careers', 'volunteer.button'); ?>
    <div class="ia-panel ia-panel--split">
        <div class="ia-panel__intro" data-reveal>
            <?php if (trim((string) content('careers', 'volunteer.eyebrow')) !== ''): ?><span class="eyebrow"><?= e(content('careers', 'volunteer.eyebrow')) ?></span><?php endif; ?>
            <h2 id="volunteer-title"><?= highlight((string) content('careers', 'volunteer.title')) ?></h2>
            <p class="lead"><?= e(content('careers', 'volunteer.text')) ?></p>
            <?php if ($btn['has']): ?><p><a class="btn btn--cta btn--caps btn--lg" href="<?= e($btn['url']) ?>"><?= e($btn['label']) ?></a></p><?php endif; ?>
        </div>
        <?php if ($roles): ?>
            <div class="role-card" data-reveal>
                <?php if (trim((string) content('careers', 'volunteer.roles_title')) !== ''): ?><h3 class="role-card__title"><?= e(content('careers', 'volunteer.roles_title')) ?></h3><?php endif; ?>
                <ul class="role-card__list">
                    <?php foreach ($roles as $r): ?><li><span class="role-card__tick" aria-hidden="true"><?= icon('check', 'icon--sm') ?></span><span><?= e($r['text']) ?></span></li><?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>
<?php section_end(); ?>

<?php /* ------------------------------------------------------------------ EQUAL OPPORTUNITY */ ?>
<?php section_begin('careers', 'equal', ['class' => 'section--cool', 'container' => 'container--narrow', 'labelledby' => 'equal-title']); ?>
    <?= partial('partials/section-head', ['eyebrow' => (string) content('careers', 'equal.eyebrow'), 'title' => (string) content('careers', 'equal.title'), 'lead' => (string) content('careers', 'equal.text'), 'align' => 'center', 'id' => 'equal-title', 'class' => 'mb-0']) ?>
<?php section_end(); ?>

<?php /* ------------------------------------------------------------------ FAQ with photo */ ?>
<?php $faqs = array_values(array_filter(content_list('careers', 'faq.items'), static fn(array $f): bool => trim((string) ($f['question'] ?? '')) !== ''));
if ($faqs): section_begin('careers', 'faq', ['attrs' => ['aria-label' => strip_highlight((string) content('careers', 'faq.title'))]]); ?>
    <?= partial('partials/faq-split', [
        'faqs'    => $faqs,
        'image'   => content_img('careers', 'faq.image'),
        'eyebrow' => (string) content('careers', 'faq.eyebrow'),
        'title'   => (string) content('careers', 'faq.title'),
        'prefix'  => 'careers-faq',
    ]) ?>
<?php section_end(); endif; ?>

<?php /* ------------------------------------------------------------------ TALENT NETWORK FORM */ ?>
<?php section_begin('careers', 'apply', ['id' => 'apply', 'class' => 'section--tint', 'container' => 'container--narrow', 'labelledby' => 'apply-title']); ?>
    <?= partial('partials/section-head', ['eyebrow' => (string) content('careers', 'apply.eyebrow'), 'title' => (string) content('careers', 'apply.title'), 'lead' => (string) content('careers', 'apply.text'), 'align' => 'center', 'id' => 'apply-title']) ?>
    <?= partial('web/careers/_apply_form', [
        'action'      => url('careers/apply'),
        'idPrefix'    => 'talent',
        'messageHelp' => content('careers', 'apply.message_help'),
        'submit'      => content('careers', 'form.submit'),
    ]) ?>
<?php section_end(); ?>

<?php /* ------------------------------------------------------------------ CLOSING BAND */ ?>
<?php section_begin('careers', 'cta', ['class' => 'careers-cta-section', 'labelledby' => 'careers-cta-title']); ?>
    <div class="cta careers-cta" data-reveal>
        <h2 class="cta__title" id="careers-cta-title"><?= highlight((string) content('careers', 'cta.title')) ?></h2>
        <p class="cta__text"><?= e(content('careers', 'cta.text')) ?></p>
        <?php if (trim((string) content('careers', 'cta.quote')) !== ''): ?><p class="careers-cta__quote"><?= e(content('careers', 'cta.quote')) ?></p><?php endif; ?>
    </div>
<?php section_end(); ?>
