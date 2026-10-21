<?php
/**
 * Card for a programs row. Vars: $program (DB row), $cta (link label; default content('global','labels.read_more')),
 * $statusLabels (optional map status => label; default = Program::statusLabels(), the editable registry labels programs > labels.status_*).
 * Links to /programs/{slug}. The round badge on the photo edge shows the program's icon column (a known icon name); the footer is a small orange pill.
 */
$href = url('programs/' . $program['slug']);
$cta = $cta ?? (string) content('global', 'labels.read_more');
$img = img_url($program['image'] ?? '');
$statusLabels ??= \App\Models\Program::statusLabels();
$pStatus = (string) ($program['status'] ?? '');
$pStatusText = trim((string) ($statusLabels[$pStatus] ?? ''));
$pStatusTone = ['planned' => 'info', 'ongoing' => 'success', 'completed' => 'neutral'][$pStatus] ?? 'neutral';
?>
<article class="card" data-reveal>
    <?php if ($img !== ''): ?>
        <a class="card__media" href="<?= e($href) ?>" tabindex="-1" aria-hidden="true"><img src="<?= e($img) ?>" alt="" loading="lazy">
            <?php if ($pStatusText !== ''): ?><span class="card__badge badge badge--<?= e($pStatusTone) ?>"><?= e($pStatusText) ?></span><?php endif; ?>
        </a>
    <?php endif; ?>
    <?php if ($img === ''): $bannerIcon = !empty($program['icon']) && in_array($program['icon'], \App\Core\Icons::names(), true) ? $program['icon'] : 'target'; ?>
        <a class="card__media card__media--icon" href="<?= e($href) ?>" tabindex="-1" aria-hidden="true"><?= icon($bannerIcon, 'icon--xl') ?>
            <?php if ($pStatusText !== ''): ?><span class="card__badge badge badge--<?= e($pStatusTone) ?>"><?= e($pStatusText) ?></span><?php endif; ?>
        </a>
    <?php endif; ?>
    <?php $chip = !empty($program['icon']) && in_array($program['icon'], \App\Core\Icons::names(), true) ? $program['icon'] : ''; ?>
    <div class="card__body<?= $chip !== '' && $img !== '' ? ' card__body--chip' : '' ?>">
        <?php if ($chip !== '' && $img !== ''): ?><span class="card__chip"><?= icon($chip, 'icon--lg') ?></span><?php endif; ?>
        <?php if (!empty($program['tagline'])): ?><div class="card__meta"><span><?= e($program['tagline']) ?></span></div><?php endif; ?>
        <h3 class="card__title"><a href="<?= e($href) ?>"><?= e($program['title']) ?></a></h3>
        <?php if (!empty($program['summary'])): ?><p class="card__text"><?= e($program['summary']) ?></p><?php endif; ?>
        <?php if (empty($program['summary']) && !empty($program['focus'])): ?><ul class="card__list"><?php foreach ($program['focus'] as $fa): ?><li><?= e($fa) ?></li><?php endforeach; ?></ul><?php endif; ?>
        <div class="card__footer"><a class="btn btn--cta btn--caps btn--sm" href="<?= e($href) ?>"><?= e($cta) ?><span class="visually-hidden">: <?= e($program['title']) ?></span></a></div>
    </div>
</article>
