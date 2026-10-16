<?php
/**
 * PROGRAM (pillar) detail: /programs/{slug}. A PLANNED program area. Data: the programs row (Admin > Programs) and its active projects
 * (Admin > Projects = "Proposed areas of intervention"); labels and notes: content('programs', 'labels.*' / 'detail.*').
 * Vars: $program, $projects (list of ['slug','title','summary']), $number ("01"), $prev / $next (programs row|null),
 *       $bodyIntro / $bodyCallout / $bodyAfter (sanitised HTML pieces of the body; the callout starts at "Our intended contribution").
 * Copy is never hardcoded: map of keys in docs/PROGRAMS_CONTENT_MAP.md.
 */

use App\Models\Program;

$title = (string) $program['title'];
$img = img_url((string) ($program['image'] ?? ''));
$pStatus = (string) ($program['status'] ?? '');
$statusText = trim((string) (Program::statusLabels()[$pStatus] ?? ''));
$statusTone = ['planned' => 'info', 'ongoing' => 'success', 'completed' => 'neutral'][$pStatus] ?? 'neutral';
$statusLabel = trim((string) content('programs', 'labels.status_label'));
$tagline = trim((string) ($program['tagline'] ?? ''));
$summary = trim((string) ($program['summary'] ?? ''));
$disclaimer = trim((string) content('projects', 'disclaimer.text'));
$back = trim((string) content('programs', 'detail.back_label'));
$join = content_btn('programs', 'detail.join');
$interventionsTitle = (string) (trim((string) content('programs', 'detail.related_title')) !== '' ? content('programs', 'detail.related_title') : content('programs', 'labels.interventions_heading'));
$viewProject = trim((string) content('programs', 'detail.related_cta'));

// ---- hero meta row: pillar number + status badge
$kicker = '<span class="programs-hero__num" aria-hidden="true">' . e($number) . '</span>';
if ($statusText !== '') {
    $kicker .= ' <span class="badge badge--' . e($statusTone) . '">' . ($statusLabel !== '' ? '<span class="visually-hidden">' . e($statusLabel) . ': </span>' : '') . e($statusText) . '</span>';
}
$heroActions = [];
if ($projects && trim((string) content('programs', 'labels.view_interventions')) !== '') {
    $heroActions[] = ['label' => (string) content('programs', 'labels.view_interventions'), 'url' => '#interventions', 'class' => 'btn--outline-light btn--caps btn--lg'];
}
?>
<?= partial('partials/programs-hero', [
    'kicker'     => $kicker,
    'titles'     => [$title],
    'leads'      => [$tagline],
    'lead_label' => (string) content('programs', 'labels.planned_focus'),
    'actions'    => $heroActions,
    'image'      => $img !== '' ? ['src' => $img, 'alt' => (string) ($program['image_alt'] ?? ''), 'focus' => '50% 30%'] : null,
]) ?>

<?php /* ------------------------------------------------------------------ BODY + intended contribution + status note */ ?>
<?php $hasAside = $statusText !== '' || $disclaimer !== '' || $back !== ''; $hasBody = trim($bodyIntro . $bodyCallout . $bodyAfter) !== ''; ?>
<?php if ($hasBody || $hasAside || $summary !== ''): ?>
<section class="section programs-detail">
    <div class="container">
        <div class="programs-detail__grid<?= $hasAside ? '' : ' programs-detail__grid--solo' ?>">
            <div class="programs-detail__main">
                <?php if ($hasBody): ?>
                    <?php if (trim($bodyIntro) !== ''): ?><div class="prose programs-detail__prose" data-reveal><?= $bodyIntro ?></div><?php endif; ?>
                    <?php if (trim($bodyCallout) !== ''): ?>
                        <div class="programs-callout" data-reveal>
                            <span class="programs-callout__icon" aria-hidden="true"><?= icon('target', 'icon--lg') ?></span>
                            <div class="prose programs-callout__body"><?= $bodyCallout ?></div>
                        </div>
                    <?php endif; ?>
                    <?php if (trim($bodyAfter) !== ''): ?><div class="prose programs-detail__prose" data-reveal><?= $bodyAfter ?></div><?php endif; ?>
                <?php elseif ($summary !== ''): ?>
                    <div class="prose programs-detail__prose"><p><?= e($summary) ?></p></div>
                <?php endif; ?>
            </div>
            <?php if ($hasAside): ?>
                <aside class="programs-note" data-reveal<?= $statusLabel !== '' ? ' aria-label="' . e($statusLabel) . '"' : '' ?>>
                    <?php if ($statusText !== ''): ?>
                        <p class="programs-note__status">
                            <?php if ($statusLabel !== ''): ?><span class="programs-note__label"><?= e($statusLabel) ?></span><?php endif; ?>
                            <span class="badge badge--<?= e($statusTone) ?>"><?= e($statusText) ?></span>
                        </p>
                    <?php endif; ?>
                    <?php if ($disclaimer !== ''): ?><p class="programs-note__text"><?= e($disclaimer) ?></p><?php endif; ?>
                    <?php if ($back !== ''): ?><p class="programs-note__back"><a class="link-arrow programs-note__link" href="<?= e(url('programs') . '#pillars') ?>"><?= icon('arrow-left', 'icon--sm') ?> <?= e($back) ?></a></p><?php endif; ?>
                </aside>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php /* ------------------------------------------------------------------ PROPOSED AREAS OF INTERVENTION (this pillar's active projects) */ ?>
<section class="section section--cool programs-interventions" id="interventions" aria-labelledby="programs-interventions-title">
    <div class="container">
        <?= partial('partials/section-head', ['title' => $interventionsTitle, 'align' => 'center', 'id' => 'programs-interventions-title']) ?>
        <?php if ($projects): ?>
            <ul class="programs-grid programs-grid--interventions" role="list">
                <?php foreach ($projects as $p): $pHref = url('projects/' . $p['slug']); ?>
                    <li class="programs-intervention" data-reveal>
                        <h3 class="programs-intervention__title"><?= e($p['title']) ?></h3>
                        <?php if (trim((string) ($p['summary'] ?? '')) !== ''): ?><p class="programs-intervention__text"><?= e($p['summary']) ?></p><?php endif; ?>
                        <?php if ($viewProject !== ''): ?>
                            <a class="programs-intervention__link" href="<?= e($pHref) ?>"><?= e($viewProject) ?><span class="visually-hidden">: <?= e($p['title']) ?></span> <?= icon('arrow-right', 'icon--sm') ?></a>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="programs-interventions__empty ia-note" data-reveal><?= e((string) content('programs', 'labels.interventions_empty')) ?></p>
        <?php endif; ?>
    </div>
</section>

<?php /* ------------------------------------------------------------------ PREVIOUS / NEXT PILLAR */ ?>
<?php if ($prev || $next): ?>
<nav class="programs-pager-wrap" aria-label="<?= e(strip_highlight((string) content('programs', 'detail.more_title'))) ?>">
    <div class="container">
        <div class="programs-pager">
            <?php if ($prev): ?>
                <a class="programs-pager__link programs-pager__link--prev" href="<?= e(url('programs/' . $prev['slug'])) ?>" rel="prev">
                    <span class="programs-pager__dir"><?= icon('arrow-left', 'icon--sm') ?> <?= e((string) content('global', 'labels.prev')) ?></span>
                    <span class="programs-pager__title"><?= e($prev['title']) ?></span>
                </a>
            <?php endif; ?>
            <?php if ($next): ?>
                <a class="programs-pager__link programs-pager__link--next" href="<?= e(url('programs/' . $next['slug'])) ?>" rel="next">
                    <span class="programs-pager__dir"><?= e((string) content('global', 'labels.next')) ?> <?= icon('arrow-right', 'icon--sm') ?></span>
                    <span class="programs-pager__title"><?= e($next['title']) ?></span>
                </a>
            <?php endif; ?>
        </div>
    </div>
</nav>
<?php endif; ?>

<?php /* ------------------------------------------------------------------ PARTNER CTA (navy band) */ ?>
<?php
$ctaTitle = section_on('programs', 'partnerships') ? (string) content('programs', 'partnerships.title') : '';
$ctaText = section_on('programs', 'partnerships') ? (string) content('programs', 'partnerships.intro') : '';
if ($join['has'] || $ctaTitle !== ''):
?>
<div class="cta-wrap programs-cta-wrap">
    <div class="container">
        <div class="cta" data-reveal>
            <?php if ($ctaTitle !== ''): ?><h2 class="cta__title"><?= highlight($ctaTitle) ?></h2><?php endif; ?>
            <?php if ($ctaText !== ''): ?><p class="cta__text"><?= e($ctaText) ?></p><?php endif; ?>
            <?php if ($join['has']): ?>
                <div class="cta__actions"><a class="btn btn--cta btn--caps btn--lg" href="<?= e($join['url']) ?>"><?= e($join['label']) ?></a></div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>
