<?php
/**
 * Card carousel (CSS scroll-snap track + previous/next buttons, keyboard accessible, reduced-motion aware).
 * Vars: $items* = list of ready-made SAFE html strings, one card each (e.g. partial('partials/program-card', [...])),
 * $label* (accessible name of the scrolling region), $eyebrow, $title ([[highlight]] allowed), $lead, $id (heading id), $foot (SAFE html under the track, e.g. a "view all" button).
 * Renders nothing without items. Works as a plain swipeable row when JavaScript is off (the arrows then stay hidden).
 *   <?php $cards = []; foreach ($programs as $p) { $cards[] = partial('partials/program-card', ['program' => $p]); } ?>
 *   <?= partial('partials/carousel', ['items' => $cards, 'label' => content('home','programs.title'), 'title' => content('home','programs.title')]) ?>
 */
$caItems = array_values(array_filter((array) ($items ?? []), static fn($h): bool => trim((string) $h) !== ''));
if (!$caItems) {
    return;
}
$caId = preg_replace('/[^a-z0-9_-]/i', '', (string) ($id ?? '')) ?: 'carousel-' . substr(md5(implode('', array_map('strval', $caItems))), 0, 6);
$caTrack = $caId . '-track';
$caHasHead = trim((string) ($title ?? '')) !== '' || trim((string) ($eyebrow ?? '')) !== '' || trim((string) ($lead ?? '')) !== '';
$caNav = '<div class="carousel__nav" data-carousel-nav>'
    . '<button class="carousel__btn" type="button" data-carousel-prev aria-controls="' . e($caTrack) . '" aria-label="' . e((string) content('global', 'labels.prev')) . '">' . icon('chevron-left') . '</button>'
    . '<button class="carousel__btn" type="button" data-carousel-next aria-controls="' . e($caTrack) . '" aria-label="' . e((string) content('global', 'labels.next')) . '">' . icon('chevron-right') . '</button>'
    . '</div>';
?>
<div class="carousel" data-carousel>
    <?php if ($caHasHead): ?>
        <?= partial('partials/section-head', ['eyebrow' => $eyebrow ?? '', 'title' => $title ?? '', 'lead' => $lead ?? '', 'id' => $caId . '-title', 'actions' => $caNav]) ?>
    <?php else: ?>
        <div class="section-head section-head--split"><span></span><?= $caNav ?></div>
    <?php endif; ?>
    <div class="carousel__track" id="<?= e($caTrack) ?>" data-carousel-track tabindex="0" role="region" aria-label="<?= e((string) ($label ?? '')) ?>">
        <?php foreach ($caItems as $html): ?><div class="carousel__slide"><?= $html ?></div><?php endforeach; ?>
    </div>
    <?php if (trim((string) ($foot ?? '')) !== ''): ?><div class="carousel__foot"><?= $foot ?></div><?php endif; ?>
</div>
