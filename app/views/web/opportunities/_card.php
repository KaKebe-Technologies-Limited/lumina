<?php
/**
 * Opportunity card. Vars: $o (row). Labels: content('opportunities', 'list.*').
 * Type chip + closing-date badge on top, title, short summary, who offers it / where, button.
 */
$href = url('opportunities/' . $o['slug']);
$img = img_url($o['image'] ?? '');
$today = date('Y-m-d');
$deadline = $o['deadline'] ?? null;
$isClosed = $deadline !== null && (string) $deadline < $today;
$left = $deadline !== null ? (int) ((strtotime((string) $deadline) - strtotime($today)) / 86400) : null;
$soon = !$isClosed && $left !== null && $left <= 7;
$by = trim((string) ($o['organisation'] ?? '')) ?: trim((string) ($o['source_name'] ?? ''));
?>
<article class="card opp-card<?= $isClosed ? ' opp-card--closed' : '' ?><?= !empty($o['is_featured']) ? ' opp-card--featured' : '' ?>" data-reveal>
    <?php if ($img !== ''): ?>
        <a class="card__media" href="<?= e($href) ?>" tabindex="-1" aria-hidden="true"><img src="<?= e($img) ?>" alt="" loading="lazy" decoding="async"></a>
    <?php endif; ?>
    <div class="card__body">
        <div class="opp-card__tags">
            <span class="opp-chip"><?= icon(\App\Models\Opportunity::categoryIcon($o['category']), 'icon--sm') ?> <?= e(\App\Models\Opportunity::categoryLabel($o['category'])) ?></span>
            <?php if ($isClosed): ?>
                <span class="opp-badge opp-badge--closed"><?= e(content('opportunities', 'list.closed')) ?></span>
            <?php elseif ($deadline !== null): ?>
                <span class="opp-badge<?= $soon ? ' opp-badge--soon' : '' ?>"><?= icon('clock', 'icon--sm') ?> <?= e(content('opportunities', 'list.closes')) ?> <time datetime="<?= e(format_date($deadline, 'Y-m-d')) ?>"><?= e(format_date($deadline, 'j M Y')) ?></time></span>
            <?php else: ?>
                <span class="opp-badge"><?= e(content('opportunities', 'list.rolling')) ?></span>
            <?php endif; ?>
        </div>
        <h3 class="card__title"><a href="<?= e($href) ?>"><?= e($o['title']) ?></a></h3>
        <?php if (trim((string) ($o['summary'] ?? '')) !== ''): ?><p class="card__text"><?= e(excerpt((string) $o['summary'], 190)) ?></p><?php endif; ?>
        <ul class="opp-card__facts">
            <?php if ($by !== ''): ?><li><?= icon('globe', 'icon--sm') ?> <?= e($by) ?></li><?php endif; ?>
            <?php if (!empty($o['location'])): ?><li><?= icon('map-pin', 'icon--sm') ?> <?= e($o['location']) ?></li><?php endif; ?>
        </ul>
        <div class="card__footer"><a class="btn btn--cta btn--caps btn--sm" href="<?= e($href) ?>"><?= e(content('opportunities', 'list.card_button')) ?><span class="visually-hidden">: <?= e($o['title']) ?></span></a></div>
    </div>
</article>
