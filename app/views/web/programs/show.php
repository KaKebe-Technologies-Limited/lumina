<?php
/**
 * PROGRAM detail (B1). Data: the programs row (Admin > Programs); labels: content('programs', 'detail.*').
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
?>
<?= partial('partials/page-hero', [
    'eyebrow' => (string) ($program['tagline'] ?? ''),
    'title'   => (string) $program['title'],
    'lead'    => $summary,
]) ?>

<section class="section programs-detail" aria-label="<?= e($program['title']) ?>">
    <div class="container">
        <div class="split split--start<?= $img !== '' ? '' : ' split--single' ?>">
            <div data-reveal>
                <?php if (trim((string) ($program['body'] ?? '')) !== ''): ?>
                    <div class="prose prose--wide"><?= HomeSupport::answerHtml((string) $program['body']) ?></div>
                <?php endif; ?>
                <div class="cluster mt-6">
                    <?php if ($join['has']): ?><a class="btn btn--accent" href="<?= e($join['url']) ?>"><?= e($join['label']) ?> <?= icon('arrow-right', 'icon--arrow') ?></a><?php endif; ?>
                    <?php if ($back !== ''): ?><a class="btn btn--outline" href="<?= e(url('programs')) ?>"><?= icon('arrow-left') ?> <?= e($back) ?></a><?php endif; ?>
                </div>
            </div>
            <?php if ($img !== ''): ?>
                <div class="split__media home-photo" data-reveal><img src="<?= e($img) ?>" alt="<?= e((string) ($program['image_alt'] ?? '')) ?>"></div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php if ($projects): ?>
<section class="section section--tint programs-related" aria-labelledby="programs-related-title">
    <div class="container">
        <div class="section__header section__header--center" data-reveal><h2 id="programs-related-title"><?= e(content('programs', 'detail.related_title')) ?></h2></div>
        <div class="cards-center">
            <?php foreach ($projects as $project): ?><?= partial('partials/project-card', ['project' => $project, 'cta' => (string) content('programs', 'detail.related_cta'), 'statusLabels' => $statuses]) ?><?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?= partial('web/programs/_accountability') ?>

<?php if ($others): ?>
<section class="section programs-more" aria-labelledby="programs-more-title">
    <div class="container">
        <div class="section__header section__header--center" data-reveal><h2 id="programs-more-title"><?= e(content('programs', 'detail.more_title')) ?></h2></div>
        <div class="cards-center">
            <?php foreach ($others as $other): ?><?= partial('partials/program-card', ['program' => $other, 'cta' => $cta]) ?><?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?= partial('partials/cta-donate') ?>
