<?php
/**
 * PROGRAMS list. Copy: content('programs', ...). Vars: $programs (rows of Admin > Programs), $pagination, $faqs (list of question/answer).
 * Order: hero -> programs grid -> accountability -> impact (+ figures card) -> how we work -> FAQ with photo -> donate band.
 */

use App\Models\Stat;

$heroImg = content_img('programs', 'hero.image');
$cta = (string) content('programs', 'overview.card_cta');
$emptyBtn = content_btn('programs', 'overview.empty_cta');
?>
<?= partial('partials/page-hero', [
    'eyebrow' => (string) content('programs', 'hero.eyebrow'),
    'title'   => (string) content('programs', 'hero.title'),
    'lead'    => (string) content('programs', 'hero.lead'),
    'image'   => $heroImg['has'] ? ['src' => $heroImg['src'], 'alt' => $heroImg['alt'], 'focus' => '50% 28%'] : null,
]) ?>

<?php /* ------------------------------------------------------------------ PROGRAMS GRID (always on) */ ?>
<?php section_begin('programs', 'overview', ['class' => 'programs-overview', 'labelledby' => 'programs-overview-title']); ?>
    <?= partial('partials/section-head', ['eyebrow' => (string) content('programs', 'overview.eyebrow'), 'title' => (string) content('programs', 'overview.title'), 'lead' => (string) content('programs', 'overview.subtitle'), 'align' => 'center', 'id' => 'programs-overview-title']) ?>
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
<?php section_end(); ?>

<?= partial('web/programs/_accountability') ?>

<?php /* ------------------------------------------------------------------ MEASURABLE IMPACT (+ figures card from Admin > Stats) */ ?>
<?php
$impImgs = [content_img('programs', 'impact.image'), content_img('programs', 'impact.image2')];
$impHasMedia = (bool) array_filter($impImgs, static fn(array $i): bool => $i['has']);
$impStats = [];
if (content('programs', 'impact.show_stats')) {
    try {
        $impStats = Stat::forGroup('home');
    } catch (\Throwable) {
        $impStats = [];
    }
}
section_begin('programs', 'impact', ['class' => 'programs-impact', 'labelledby' => 'programs-impact-title']); ?>
    <div class="split split--start<?= $impHasMedia ? ' split--wide-media' : ' ia-split--solo' ?>">
        <?= partial('partials/photo-stack', ['images' => $impImgs]) ?>
        <div data-reveal>
            <?= partial('partials/section-head', ['eyebrow' => (string) content('programs', 'impact.eyebrow'), 'title' => (string) content('programs', 'impact.title'), 'id' => 'programs-impact-title']) ?>
            <p class="lead"><?= e(content('programs', 'impact.text')) ?></p>
        </div>
    </div>
    <?php if ($impStats): ?><div class="programs-impact__stats"><?= partial('partials/stat-band', ['stats' => $impStats]) ?></div><?php endif; ?>
<?php section_end(); ?>

<?php /* ------------------------------------------------------------------ HOW WE WORK */ ?>
<?php section_begin('programs', 'how_we_work', ['class' => 'section--cool programs-how', 'labelledby' => 'programs-how-title']); $principles = content_list('programs', 'how_we_work.items'); ?>
    <?= partial('partials/section-head', ['eyebrow' => (string) content('programs', 'how_we_work.eyebrow'), 'title' => (string) content('programs', 'how_we_work.title'), 'lead' => (string) content('programs', 'how_we_work.text'), 'align' => 'center', 'id' => 'programs-how-title']) ?>
    <?php if ($principles): ?>
        <div class="ia-cards">
            <?php foreach ($principles as $p): ?>
                <?= partial('partials/feature-card', ['icon' => (string) ($p['icon'] ?? ''), 'title' => (string) ($p['title'] ?? ''), 'text' => (string) ($p['text'] ?? '')]) ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
<?php section_end(); ?>

<?php /* ------------------------------------------------------------------ FAQ with photo */ ?>
<?php if ($faqs): ?>
<?php section_begin('programs', 'faq', ['class' => 'programs-faq', 'attrs' => ['aria-label' => strip_highlight((string) content('programs', 'faq.title'))]]); ?>
    <?= partial('partials/faq-split', [
        'faqs'    => $faqs,
        'image'   => content_img('programs', 'faq.image'),
        'eyebrow' => (string) content('programs', 'faq.eyebrow'),
        'title'   => (string) content('programs', 'faq.title'),
        'prefix'  => 'programs-faq',
    ]) ?>
<?php section_end(); ?>
<?php endif; ?>

<?= partial('partials/cta-donate') ?>
