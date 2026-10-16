<?php
/**
 * Event card. Vars: $event (events row), $past (bool), $cta (link label; default content('global','labels.read_more')).
 * Links to /events/{slug}.
 */
$href = url('events/' . $event['slug']);
$start = to_datetime($event['start_at']);
$cta = $cta ?? (string) content('global', 'labels.read_more');
?>
<article class="event-card<?= !empty($past) ? ' event-card--past' : '' ?>" data-reveal>
    <?php if ($start): ?>
        <div class="event-card__date" aria-hidden="true">
            <span class="event-card__day"><?= e($start->format('j')) ?></span>
            <span class="event-card__month"><?= e($start->format('M Y')) ?></span>
        </div>
    <?php endif; ?>
    <div>
        <h3 class="event-card__title"><a href="<?= e($href) ?>"><?= e($event['title']) ?></a></h3>
        <div class="event-card__meta">
            <?php if ($start): ?><span><?= icon('clock', 'icon--sm') ?> <time datetime="<?= e($start->format('c')) ?>"><?= e($start->format('j F Y, H:i')) ?></time></span><?php endif; ?>
            <?php if (!empty($event['location'])): ?><span><?= icon('map-pin', 'icon--sm') ?> <?= e($event['location']) ?></span><?php endif; ?>
        </div>
        <?php if (!empty($event['summary'])): ?><p class="event-card__text"><?= e($event['summary']) ?></p><?php endif; ?>
        <p class="mt-3"><span class="link-arrow"><?= e($cta) ?> <?= icon('arrow-right', 'icon--arrow') ?></span></p>
    </div>
</article>
