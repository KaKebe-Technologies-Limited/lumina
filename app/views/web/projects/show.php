<?php
/**
 * PROJECT detail = one planned area of intervention. Data: the projects row (Admin > Projects) + its program pillar; labels: content('projects', 'detail.*'),
 * content('programs', 'labels.*'), the notice and the call to action from content('projects', ...).
 * Vars (ProjectsController::show): $project, $program (parent pillar row or null), $number (position of the pillar, 0 = none),
 *   $others (the pillar's other interventions, or up to 3 other projects when there is no pillar), $statuses (status => label).
 */

use App\Controllers\Web\HomeSupport;

$summary = trim((string) ($project['summary'] ?? ''));
$img = img_url((string) ($project['image'] ?? ''));
$status = (string) ($project['status'] ?? '');
$statusText = trim((string) ($statuses[$status] ?? ''));
$statusTone = ['planned' => 'info', 'ongoing' => 'success', 'completed' => 'neutral'][$status] ?? 'neutral';
$join = content_btn('projects', 'detail.join');
$back = trim((string) content('projects', 'detail.back_label'));
$cardCta = trim((string) content('projects', 'list.card_cta'));
$focusLabel = trim((string) content('programs', 'labels.planned_focus'));
$viewPillar = trim((string) content('programs', 'labels.view_pillar'));

// body: the summary is already the banner's lead, so a first paragraph that only repeats it is dropped
$bodyHtml = trim((string) ($project['body'] ?? '')) !== '' ? HomeSupport::answerHtml((string) $project['body']) : '';
if ($bodyHtml !== '' && $summary !== '' && preg_match('~^\s*<p>(.*?)</p>\s*~su', $bodyHtml, $first) === 1
    && trim(html_entity_decode(strip_tags($first[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8')) === $summary) {
    $bodyHtml = substr($bodyHtml, strlen($first[0]));
}
$bodyHtml = trim($bodyHtml);

// optional facts (only rows that have a value; the planned interventions have no place or dates yet)
$dates = '';
if (!empty($project['start_date'])) {
    $dates = format_date($project['start_date']);
    if (!empty($project['end_date'])) {
        $dates .= ' – ' . format_date($project['end_date']);
    }
}
$facts = array_values(array_filter([
    ['icon' => 'map-pin', 'label' => (string) content('projects', 'detail.location_label'), 'value' => trim((string) ($project['location'] ?? ''))],
    ['icon' => 'calendar', 'label' => (string) content('projects', 'detail.dates_label'), 'value' => $dates],
], static fn(array $f): bool => $f['value'] !== ''));

// the parent pillar card
$pillarHref = $program !== null ? url('programs/' . $program['slug']) : '';
$pillarImg = $program !== null ? img_url((string) ($program['image'] ?? '')) : '';
$pillarFocus = $program !== null ? trim((string) ($program['tagline'] ?? '')) : '';
$pillarIcon = $program !== null && !empty($program['icon']) ? safe_icon((string) $program['icon'], 'layers') : 'layers';
$tone = $number > 0 ? (($number - 1) % 3) + 1 : 0;
?>
<?= partial('partials/projects-hero', [
    'eyebrow' => (string) ($project['tagline'] ?? ''),
    'title'   => (string) $project['title'],
    'lead'    => $summary,
    'badge'   => $statusText !== '' ? ['label' => $statusText, 'tone' => $statusTone] : null,
]) ?>

<section class="section pi-detail">
    <div class="container">
        <div class="pi-detail__inner">
            <?php if ($img !== ''): ?>
                <figure class="ia-photo pi-detail__figure"><img src="<?= e($img) ?>" alt="<?= e((string) ($project['image_alt'] ?? '')) ?>" width="1200" height="800" decoding="async"></figure>
            <?php endif; ?>
            <?php if ($bodyHtml !== ''): ?><div class="prose prose--wide" data-reveal><?= $bodyHtml ?></div><?php endif; ?>

            <?php if ($facts): ?>
                <dl class="ia-facts pi-detail__facts" data-reveal>
                    <?php foreach ($facts as $f): ?>
                        <div class="ia-facts__row">
                            <span class="ia-facts__icon" aria-hidden="true"><?= icon($f['icon']) ?></span>
                            <div><dt><?= e($f['label']) ?></dt><dd><?= e($f['value']) ?></dd></div>
                        </div>
                    <?php endforeach; ?>
                </dl>
            <?php endif; ?>

            <?php if ($program !== null): ?>
                <div class="pi-part pi-tone-<?= (int) $tone ?><?= $pillarImg !== '' ? ' pi-part--media' : '' ?>" role="group" aria-labelledby="projects-part-title" data-reveal>
                    <?php if ($pillarImg !== ''): ?>
                        <a class="pi-part__media" href="<?= e($pillarHref) ?>" tabindex="-1" aria-hidden="true"><img src="<?= e($pillarImg) ?>" alt="" width="800" height="600" loading="lazy" decoding="async"></a>
                    <?php endif; ?>
                    <div class="pi-part__body">
                        <span class="pi-part__icon" aria-hidden="true"><?= icon($pillarIcon, 'icon--lg') ?></span>
                        <?php $programLabel = trim((string) content('projects', 'detail.program_label')); if ($programLabel !== ''): ?><p class="pi-part__label"><?= e($programLabel) ?></p><?php endif; ?>
                        <h2 class="pi-part__title" id="projects-part-title"><a href="<?= e($pillarHref) ?>"><?= e($program['title']) ?></a></h2>
                        <?php if ($pillarFocus !== ''): ?><p class="pi-part__focus"><?php if ($focusLabel !== ''): ?><strong><?= e($focusLabel) ?>:</strong> <?php endif; ?><?= e($pillarFocus) ?></p><?php endif; ?>
                        <?php if ($viewPillar !== ''): ?><p class="pi-part__cta"><a class="btn btn--outline btn--caps btn--sm" href="<?= e($pillarHref) ?>"><?= e($viewPillar) ?><span class="visually-hidden">: <?= e($program['title']) ?></span> <?= icon('arrow-right', 'icon--sm') ?></a></p><?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?= partial('partials/projects-notice', ['tag' => 'h2', 'id' => 'projects-notice-title']) ?>

            <?php if ($join['has'] || $back !== ''): ?>
                <div class="cluster pi-detail__actions">
                    <?php if ($join['has']): ?><a class="btn btn--cta btn--caps" href="<?= e($join['url']) ?>"<?= $join['external'] ? ' target="_blank" rel="noopener noreferrer"' : '' ?>><?= e($join['label']) ?></a><?php endif; ?>
                    <?php if ($back !== ''): ?><a class="btn btn--outline btn--caps" href="<?= e(url('projects')) ?>"><?= icon('arrow-left', 'icon--sm') ?> <?= e($back) ?></a><?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php if ($others): ?>
<section class="section section--cool pi-more pi-tone-<?= (int) $tone ?>" aria-labelledby="projects-more-title">
    <div class="container">
        <?= partial('partials/section-head', [
            'eyebrow' => $program !== null ? (string) $program['title'] : '',
            'title'   => (string) content('projects', 'detail.more_title'),
            'align'   => 'center',
            'id'      => 'projects-more-title',
        ]) ?>
        <ul class="pi-grid" role="list">
            <?php foreach ($others as $other): ?>
                <li class="pi-grid__item"><?= partial('partials/projects-card', ['project' => $other, 'pillar' => $program, 'showPillar' => false, 'statuses' => $statuses, 'cta' => $cardCta]) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>
<?php endif; ?>

<?= partial('partials/projects-cta') ?>
