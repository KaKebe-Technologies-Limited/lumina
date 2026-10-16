<?php
/**
 * PROGRAMS list (B1). Copy: content('programs', ...). Vars: $programs (rows of Admin > Programs), $pagination, $faqs.
 */

$heroImg = content_img('programs', 'hero.image');
$cta = (string) content('programs', 'overview.card_cta');
$emptyBtn = content_btn('programs', 'overview.empty_cta');
?>
<?= partial('partials/page-hero', [
    'eyebrow' => (string) content('programs', 'hero.eyebrow'),
    'title'   => (string) content('programs', 'hero.title'),
    'lead'    => (string) content('programs', 'hero.lead'),
    'image'   => $heroImg['has'] ? ['src' => $heroImg['src'], 'alt' => $heroImg['alt']] : null,
]) ?>

<section class="section programs-overview" id="programs-overview" aria-labelledby="programs-overview-title">
    <div class="container">
        <div class="section__header section__header--center" data-reveal>
            <h2 id="programs-overview-title"><?= e(content('programs', 'overview.title')) ?></h2>
            <?php if (trim((string) content('programs', 'overview.subtitle')) !== ''): ?><p class="lead"><?= e(content('programs', 'overview.subtitle')) ?></p><?php endif; ?>
        </div>
        <?php if ($programs): ?>
            <div class="grid grid--2 grid--gap-lg">
                <?php foreach ($programs as $program): ?><?= partial('partials/program-card', ['program' => $program, 'cta' => $cta]) ?><?php endforeach; ?>
            </div>
            <?= partial('partials/pagination', ['pagination' => $pagination]) ?>
        <?php else: ?>
            <?= partial('partials/empty-state', [
                'icon'    => 'layers',
                'title'   => (string) content('programs', 'overview.empty_title'),
                'text'    => (string) content('programs', 'overview.empty_text'),
                'actions' => $emptyBtn['has'] ? [['label' => $emptyBtn['label'], 'url' => $emptyBtn['url'], 'class' => 'btn--primary']] : [],
            ]) ?>
        <?php endif; ?>
    </div>
</section>

<?= partial('web/programs/_accountability') ?>

<?php /* ------------------------------------------------------------------ MEASURABLE IMPACT */ ?>
<?php section_begin('programs', 'impact', ['class' => 'programs-impact', 'labelledby' => 'programs-impact-title']); $img = content_img('programs', 'impact.image'); ?>
    <div class="split">
        <div data-reveal>
            <h2 id="programs-impact-title"><?= e(content('programs', 'impact.title')) ?></h2>
            <p class="lead mt-4"><?= e(content('programs', 'impact.text')) ?></p>
        </div>
        <?php if ($img['has']): ?><div class="split__media home-photo" data-reveal><img src="<?= e($img['src']) ?>" alt="<?= e($img['alt']) ?>" loading="lazy"></div><?php endif; ?>
    </div>
<?php section_end(); ?>

<?php /* ------------------------------------------------------------------ FAQ */ ?>
<?php if ($faqs): ?>
<?php section_begin('programs', 'faq', ['class' => 'section--tint programs-faq', 'container' => 'container--narrow', 'labelledby' => 'programs-faq-title']); ?>
    <div class="section__header section__header--center" data-reveal><h2 id="programs-faq-title"><?= e(content('programs', 'faq.title')) ?></h2></div>
    <?= partial('web/home/_faq', ['faqs' => $faqs, 'prefix' => 'programs-faq']) ?>
<?php section_end(); ?>
<?php endif; ?>

<?php /* ------------------------------------------------------------------ HOW WE WORK */ ?>
<?php section_begin('programs', 'how_we_work', ['class' => 'programs-how', 'labelledby' => 'programs-how-title']); $principles = content_list('programs', 'how_we_work.items'); ?>
    <div class="section__header section__header--center" data-reveal>
        <h2 id="programs-how-title"><?= e(content('programs', 'how_we_work.title')) ?></h2>
        <?php if (trim((string) content('programs', 'how_we_work.text')) !== ''): ?><p class="lead"><?= e(content('programs', 'how_we_work.text')) ?></p><?php endif; ?>
    </div>
    <?php if ($principles): ?>
        <div class="principle-grid">
            <?php foreach ($principles as $i => $p): if (trim((string) ($p['title'] ?? '')) === '') { continue; } ?>
                <article class="principle" data-reveal>
                    <span class="icon-tile" aria-hidden="true"><?= icon(['users', 'globe', 'shield'][$i % 3], 'icon--lg') ?></span>
                    <h3><?= e($p['title']) ?></h3>
                    <?php if (trim((string) ($p['text'] ?? '')) !== ''): ?><p><?= e($p['text']) ?></p><?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
<?php section_end(); ?>

<?= partial('partials/cta-donate') ?>
