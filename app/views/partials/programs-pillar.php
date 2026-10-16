<?php
/**
 * One PLANNED program pillar as a large split card (photo | text). Used by /programs#pillars.
 * Vars: $program (programs row)*, $number ("01"), $interventions (list of ['slug','title'] = the pillar's active projects, owner's order),
 * $reverse (bool: photo on the right from lg). Copy: the row itself + content('programs', 'labels.*' / 'pillars.card_cta').
 * The photo is informative (alt = programs.image_alt); the title link and the button are the only tab stops, the chips are plain text
 * and the "+N" chip links to the full list on the pillar page.
 */
use App\Models\Program;

$href = url('programs/' . $program['slug']);
$img = img_url($program['image'] ?? '');
$icon = safe_icon($program['icon'] ?? '', 'layers');
$number = (string) ($number ?? '');
$interventions = (array) ($interventions ?? []);
$maxChips = 4;
$chips = array_slice($interventions, 0, $maxChips);
$moreCount = max(0, count($interventions) - $maxChips);
$pStatus = (string) ($program['status'] ?? '');
$statusText = trim((string) (Program::statusLabels()[$pStatus] ?? ''));
$statusTone = ['planned' => 'info', 'ongoing' => 'success', 'completed' => 'neutral'][$pStatus] ?? 'neutral';
$statusLabel = trim((string) content('programs', 'labels.status_label'));
$focusLabel = trim((string) content('programs', 'labels.planned_focus'));
$tagline = trim((string) ($program['tagline'] ?? ''));
$summary = trim((string) ($program['summary'] ?? ''));
$interventionsLabel = trim((string) content('programs', 'labels.interventions_heading'));
$viewInterventions = trim((string) content('programs', 'labels.view_interventions'));
$cta = trim((string) content('programs', 'pillars.card_cta'));
$tid = 'pillar-' . preg_replace('/[^a-z0-9-]/', '', (string) $program['slug']) . '-title';
?>
<article class="pillar-card<?= !empty($reverse) ? ' pillar-card--reverse' : '' ?><?= $img === '' ? ' pillar-card--no-photo' : '' ?>" aria-labelledby="<?= e($tid) ?>" data-reveal>
    <?php if ($img !== ''): ?>
        <figure class="pillar-card__media">
            <img src="<?= e($img) ?>" alt="<?= e((string) ($program['image_alt'] ?? '')) ?>" width="1200" height="900" loading="lazy" decoding="async">
            <?php if ($number !== ''): ?><span class="pillar-card__num" aria-hidden="true"><?= e($number) ?></span><?php endif; ?>
        </figure>
    <?php endif; ?>
    <div class="pillar-card__body">
        <div class="pillar-card__top">
            <span class="pillar-card__icon" aria-hidden="true"><?= icon($icon, 'icon--lg') ?></span>
            <?php if ($statusText !== ''): ?>
                <span class="badge badge--<?= e($statusTone) ?>"><?php if ($statusLabel !== ''): ?><span class="visually-hidden"><?= e($statusLabel) ?>: </span><?php endif; ?><?= e($statusText) ?></span>
            <?php endif; ?>
        </div>
        <h3 class="pillar-card__title" id="<?= e($tid) ?>"><?php if ($img === '' && $number !== ''): ?><span class="pillar-card__num-inline" aria-hidden="true"><?= e($number) ?></span> <?php endif; ?><a href="<?= e($href) ?>"><?= e($program['title']) ?></a></h3>
        <?php if ($tagline !== ''): ?><p class="pillar-card__focus"><?php if ($focusLabel !== ''): ?><strong class="pillar-card__focus-label"><?= e($focusLabel) ?>:</strong> <?php endif; ?><?= e($tagline) ?></p><?php endif; ?>
        <?php if ($summary !== ''): ?><p class="pillar-card__summary"><?= e($summary) ?></p><?php endif; ?>
        <?php if ($chips): ?>
            <ul class="pillar-card__chips"<?= $interventionsLabel !== '' ? ' aria-label="' . e($interventionsLabel) . '"' : '' ?>>
                <?php foreach ($chips as $c): ?><li class="pillar-card__chip"><?= e($c['title']) ?></li><?php endforeach; ?>
                <?php if ($moreCount > 0): ?>
                    <li><a class="pillar-card__chip pillar-card__chip--more" href="<?= e($href . '#interventions') ?>">+<?= (int) $moreCount ?><span class="visually-hidden"> <?= e($viewInterventions) ?>: <?= e($program['title']) ?></span></a></li>
                <?php endif; ?>
            </ul>
        <?php endif; ?>
        <?php if ($cta !== ''): ?>
            <div class="pillar-card__actions"><a class="btn btn--cta btn--caps" href="<?= e($href) ?>"><?= e($cta) ?><span class="visually-hidden">: <?= e($program['title']) ?></span> <?= icon('arrow-right', 'icon--sm') ?></a></div>
        <?php endif; ?>
    </div>
</article>
