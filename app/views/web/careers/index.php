<?php
/**
 * Careers. Vars: $jobs (open vacancies). Copy: content('careers', ...) - admin: Pages > Careers; vacancies: Content > Jobs.
 */
$this->start('scripts'); ?>
<script src="<?= e(asset('js/pages/careers.js')) ?>" defer></script>
<?php $this->end();

$actions = [];
foreach (['hero.cta1', 'hero.cta2'] as $i => $key) {
    $b = content_btn('careers', $key);
    if ($b['has']) {
        $actions[] = ['label' => $b['label'], 'url' => $b['url'], 'class' => $i === 0 ? 'btn--accent' : 'btn--outline-light'];
    }
}
?>
<?= partial('partials/page-hero', [
    'title'   => content('careers', 'hero.title'),
    'eyebrow' => content('careers', 'hero.eyebrow'),
    'lead'    => content('careers', 'hero.lead'),
    'actions' => $actions,
]) ?>

<?php section_begin('careers', 'openings', ['id' => 'openings', 'labelledby' => 'openings-title']); ?>
    <div class="section__header">
        <h2 id="openings-title"><?= e(content('careers', 'openings.title')) ?></h2>
        <?php if (trim((string) content('careers', 'openings.intro')) !== ''): ?><p class="lead"><?= e(content('careers', 'openings.intro')) ?></p><?php endif; ?>
    </div>
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
            <?php if ($none['has'] && section_on('careers', 'apply')): ?><p><a class="btn btn--primary" href="<?= e($none['url']) ?>"><?= e($none['label']) ?></a></p><?php endif; ?>
        </div>
    <?php endif; ?>
<?php section_end(); ?>

<?php section_begin('careers', 'why', ['class' => 'section--tint', 'labelledby' => 'why-title']); $img = content_img('careers', 'why.image'); $reasons = content_list('careers', 'why.items'); ?>
    <div class="split<?= $img['has'] ? '' : ' split--single' ?>">
        <div>
            <h2 id="why-title"><?= e(content('careers', 'why.title')) ?></h2>
            <ul class="check-list mt-5">
                <?php foreach ($reasons as $r): if (trim((string) $r['text']) === '') { continue; } ?>
                    <li><?= icon('check-circle', 'icon--lg') ?><span><?= e($r['text']) ?></span></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php if ($img['has']): ?><div class="split__media"><img src="<?= e($img['src']) ?>" alt="<?= e($img['alt']) ?>" loading="lazy"></div><?php endif; ?>
    </div>
<?php section_end(); ?>

<?php section_begin('careers', 'process', ['container' => 'container--narrow', 'labelledby' => 'process-title']); $steps = content_list('careers', 'process.steps'); ?>
    <div class="section__header section__header--center"><h2 id="process-title"><?= e(content('careers', 'process.title')) ?></h2></div>
    <ol class="timeline">
        <?php foreach ($steps as $i => $s): if (trim((string) $s['text']) === '') { continue; } ?>
            <li class="timeline__item" data-reveal>
                <span class="timeline__marker" aria-hidden="true"><?= (int) $i + 1 ?></span>
                <div class="timeline__body"><p class="timeline__text mt-0"><?= e($s['text']) ?></p></div>
            </li>
        <?php endforeach; ?>
    </ol>
<?php section_end(); ?>

<?php section_begin('careers', 'volunteer', ['class' => 'section--navy', 'id' => 'volunteer', 'labelledby' => 'volunteer-title']); $roles = content_list('careers', 'volunteer.roles'); $btn = content_btn('careers', 'volunteer.button'); ?>
    <div class="split split--start">
        <div>
            <?php if (trim((string) content('careers', 'volunteer.eyebrow')) !== ''): ?><span class="eyebrow"><?= e(content('careers', 'volunteer.eyebrow')) ?></span><?php endif; ?>
            <h2 id="volunteer-title"><?= e(content('careers', 'volunteer.title')) ?></h2>
            <p class="lead mt-4"><?= e(content('careers', 'volunteer.text')) ?></p>
            <?php if ($btn['has']): ?><p class="mt-6"><a class="btn btn--accent btn--lg" href="<?= e($btn['url']) ?>"><?= e($btn['label']) ?></a></p><?php endif; ?>
        </div>
        <?php if ($roles): ?>
            <div class="role-card">
                <?php if (trim((string) content('careers', 'volunteer.roles_title')) !== ''): ?><h3 class="role-card__title"><?= e(content('careers', 'volunteer.roles_title')) ?></h3><?php endif; ?>
                <ul class="role-card__list">
                    <?php foreach ($roles as $r): if (trim((string) $r['text']) === '') { continue; } ?><li><?= icon('check', 'icon--sm') ?> <?= e($r['text']) ?></li><?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>
<?php section_end(); ?>

<?php section_begin('careers', 'equal', ['container' => 'container--narrow', 'labelledby' => 'equal-title']); ?>
    <div class="equal text-center">
        <h2 id="equal-title"><?= e(content('careers', 'equal.title')) ?></h2>
        <p class="lead mt-4"><?= e(content('careers', 'equal.text')) ?></p>
    </div>
<?php section_end(); ?>

<?php section_begin('careers', 'faq', ['class' => 'section--tint', 'container' => 'container--narrow', 'labelledby' => 'faq-title']); $faqs = content_list('careers', 'faq.items'); ?>
    <div class="section__header section__header--center"><h2 id="faq-title"><?= e(content('careers', 'faq.title')) ?></h2></div>
    <div class="accordion" data-accordion="single">
        <?php foreach ($faqs as $i => $f): if (trim((string) $f['question']) === '') { continue; } $pid = 'careers-faq-' . ($i + 1); ?>
            <div class="accordion__item">
                <h3 class="accordion__heading">
                    <button class="accordion__trigger" type="button" id="<?= e($pid) ?>-q" aria-expanded="false" aria-controls="<?= e($pid) ?>-a" data-accordion-trigger>
                        <span><?= e($f['question']) ?></span><?= icon('chevron-down') ?>
                    </button>
                </h3>
                <div class="accordion__panel" id="<?= e($pid) ?>-a" role="region" aria-labelledby="<?= e($pid) ?>-q" data-accordion-panel hidden>
                    <?= nl2p($f['answer']) ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php section_end(); ?>

<?php section_begin('careers', 'apply', ['id' => 'apply', 'container' => 'container--narrow', 'labelledby' => 'apply-title']); ?>
    <div class="section__header section__header--center">
        <h2 id="apply-title"><?= e(content('careers', 'apply.title')) ?></h2>
        <?php if (trim((string) content('careers', 'apply.text')) !== ''): ?><p class="lead"><?= e(content('careers', 'apply.text')) ?></p><?php endif; ?>
    </div>
    <?= partial('web/careers/_apply_form', [
        'action'      => url('careers/apply'),
        'idPrefix'    => 'talent',
        'messageHelp' => content('careers', 'apply.message_help'),
        'submit'      => content('careers', 'form.submit'),
    ]) ?>
<?php section_end(); ?>

<?php section_begin('careers', 'cta', ['class' => 'section--dark', 'container' => 'container--narrow', 'labelledby' => 'careers-cta-title']); ?>
    <div class="careers-cta text-center">
        <h2 id="careers-cta-title"><?= e(content('careers', 'cta.title')) ?></h2>
        <p class="lead mt-4"><?= e(content('careers', 'cta.text')) ?></p>
        <?php if (trim((string) content('careers', 'cta.quote')) !== ''): ?><p class="careers-cta__quote mt-5"><?= e(content('careers', 'cta.quote')) ?></p><?php endif; ?>
    </div>
<?php section_end(); ?>
