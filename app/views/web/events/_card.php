<?php
/**
 * Event card (B-INNER-B; used on the Events pages instead of partials/event-card).
 * Vars: $event (events row), $past (bool), $cta (link label), $pastLabel (small badge text on a past event).
 * Photo on top (navy tile with a calendar icon when the event has no photo) with a round date badge, then place, serif title, summary and a small pill link.
 * Links to /events/{slug}.
 */
$href = url('events/' . $event['slug']);
$start = to_datetime($event['start_at']);
$img = img_url($event['image'] ?? '');
$past = !empty($past);
$cta = $cta ?? (string) content('global', 'labels.read_more');
$pastLabel = trim((string) ($pastLabel ?? ''));
$location = trim((string) ($event['location'] ?? ''));
?>
<article class="card event-item<?= $past ? ' event-item--past' : '' ?>" data-reveal>
    <a class="card__media<?= $img === '' ? ' card__media--ph' : '' ?>" href="<?= e($href) ?>" tabindex="-1" aria-hidden="true">
        <?php if ($img !== ''): ?>
            <img src="<?= e($img) ?>" alt="" loading="lazy" decoding="async">
        <?php else: ?>
            <span class="card__ph"><?= icon('calendar', 'icon--xl') ?></span>
        <?php endif; ?>
        <?php if ($past && $pastLabel !== ''): ?><span class="card__tag"><?= e($pastLabel) ?></span><?php endif; ?>
    </a>
    <div class="card__body card__body--chip">
        <?php if ($start): ?>
            <span class="card__chip card__chip--date" aria-hidden="true">
                <span class="card__chip-day"><?= e($start->format('j')) ?></span>
                <span class="card__chip-month"><?= e($start->format('M')) ?></span>
            </span>
        <?php else: ?>
            <span class="card__chip" aria-hidden="true"><?= icon('calendar', 'icon--lg') ?></span>
        <?php endif; ?>
        <div class="card__meta">
            <?php if ($start): ?><span class="card__meta-item"><?= icon('clock', 'icon--sm') ?> <time datetime="<?= e($start->format('c')) ?>"><?= e($start->format('j F Y, H:i')) ?></time></span><?php endif; ?>
            <?php if ($location !== ''): ?><span class="card__meta-item"><?= icon('map-pin', 'icon--sm') ?> <?= e($location) ?></span><?php endif; ?>
        </div>
        <h3 class="card__title"><a href="<?= e($href) ?>"><?= e($event['title']) ?></a></h3>
        <?php if (trim((string) ($event['summary'] ?? '')) !== ''): ?><p class="card__text"><?= e($event['summary']) ?></p><?php endif; ?>
        <div class="card__footer"><a class="btn <?= $past ? 'btn--outline' : 'btn--cta' ?> btn--caps btn--sm" href="<?= e($href) ?>"><?= e($cta) ?><span class="visually-hidden">: <?= e($event['title']) ?></span></a></div>
    </div>
</article>
