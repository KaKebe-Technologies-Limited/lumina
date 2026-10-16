<?php
/**
 * PROGRAM detail. Data: the programs row (Admin > Programs); labels: content('programs', 'detail.*').
 * Vars: $program, $projects (projects of this program), $others (up to 3 other programs).
 */

use App\Controllers\Web\HomeSupport;

$img = img_url((string) ($program['image'] ?? ''));
$join = content_btn('programs', 'detail.join');
$back = trim((string) content('programs', 'detail.back_label'));
$cta = (string) content('programs', 'overview.card_cta');
$statuses = [
    'planned'   => (string) content('projects', 'list.status_planned'),
    'ongoing'   => (string) content('projects', 'list.status_ongoing'),
    'completed' => (string) content('projects', 'list.status_completed'),
];
$summary = trim((string) ($program['summary'] ?? ''));
$bodyHtml = trim((string) ($program['body'] ?? '')) !== '' ? HomeSupport::answerHtml((string) $program['body']) : '';
?>
<?= partial('partials/page-hero', [
    'eyebrow' => (string) ($program['tagline'] ?? ''),
    'title'   => (string) $program['title'],
    'lead'    => $summary,
]) ?>

<section class="section programs-detail" aria-label="<?= e($program['title']) ?>">
    <div class="container">
        <div class="split split--start<?= $img !== '' ? ' split--wide-text' : ' ia-split--solo' ?>">
            <div data-reveal>
                <?php if ($bodyHtml !== ''): ?><div class="prose prose--wide"><?= $bodyHtml ?></div><?php endif; ?>
                <div class="cluster mt-6">
                    <?php if ($join['has']): ?><a class="btn btn--cta btn--caps" href="<?= e($join['url']) ?>"><?= e($join['label']) ?></a><?php endif; ?>
                    <?php if ($back !== ''): ?><a class="btn btn--outline btn--caps" href="<?= e(url('programs')) ?>"><?= icon('arrow-left', 'icon--sm') ?> <?= e($back) ?></a><?php endif; ?>
                </div>
            </div>
            <?php if ($img !== ''): ?>
                <figure class="ia-photo ia-photo--frame" data-reveal><img src="<?= e($img) ?>" alt="<?= e((string) ($program['image_alt'] ?? '')) ?>" width="1200" height="900" decoding="async"></figure>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php if ($projects): ?>
<section class="section section--cool programs-related" aria-labelledby="programs-related-title">
    <div class="container">
        <?= partial('partials/section-head', ['title' => (string) content('programs', 'detail.related_title'), 'align' => 'center', 'id' => 'programs-related-title']) ?>
        <div class="ia-cards">
            <?php foreach ($projects as $project): ?><?= partial('partials/project-card', ['project' => $project, 'cta' => (string) content('programs', 'detail.related_cta'), 'statusLabels' => $statuses]) ?><?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?= partial('web/programs/_accountability') ?>

<?php if ($others): ?>
<section class="section programs-more" aria-labelledby="programs-more-title">
    <div class="container">
        <?= partial('partials/section-head', ['title' => (string) content('programs', 'detail.more_title'), 'align' => 'center', 'id' => 'programs-more-title']) ?>
        <div class="ia-cards">
            <?php foreach ($others as $other): ?><?= partial('partials/program-card', ['program' => $other, 'cta' => $cta]) ?><?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?= partial('partials/cta-donate') ?>
