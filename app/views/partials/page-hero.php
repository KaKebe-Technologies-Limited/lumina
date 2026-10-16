<?php
/**
 * Inner-page banner: photo (or navy gradient) background, breadcrumbs, small-caps eyebrow, serif title, lead, optional buttons, and the
 * rounded white "sheet" that cuts into its bottom edge. Vars: $title (required; [[highlight]] markers allowed), $eyebrow, $lead,
 * $image (['src','alt'] optional background photo; optional 'focus' => CSS object-position e.g. '50% 20%'),
 * $actions (list of ['label','url','class']), $breadcrumbs (default: the layout's $breadcrumbs),
 * ADDITIVE: $sheet ('white' default | 'tint' | 'cool': colour of the sheet = background of the first section below the hero).
 * All text must come from content()/DB, never hardcoded.
 */
$crumbs = $breadcrumbs ?? [];
$phHasImg = !empty($image['src']);
$phFocus = preg_match('/^[0-9%. a-z-]{1,30}$/i', (string) ($image['focus'] ?? '')) ? (string) $image['focus'] : '';
$phSheet = in_array($sheet ?? 'white', ['tint', 'cool'], true) ? ' page-hero--sheet-' . $sheet : '';
?>
<header class="page-hero<?= $phHasImg ? ' page-hero--image' : '' ?><?= $phSheet ?>">
    <?php if ($phHasImg): ?><img class="page-hero__bg" src="<?= e($image['src']) ?>" alt="<?= e($image['alt'] ?? '') ?>" fetchpriority="high" decoding="async"<?= $phFocus !== '' ? ' style="object-position:' . e($phFocus) . '"' : '' ?>><?php endif; ?>
    <div class="page-hero__inner">
        <?= partial('partials/breadcrumbs', ['breadcrumbs' => $crumbs]) ?>
        <?php if (!empty($eyebrow)): ?><span class="eyebrow"><?= e($eyebrow) ?></span><br><?php endif; ?>
        <h1 class="page-hero__title"><?= highlight((string) $title) ?></h1>
        <?php if (!empty($lead)): ?><p class="page-hero__lead"><?= e($lead) ?></p><?php endif; ?>
        <?php if (!empty($actions)): ?>
            <div class="page-hero__actions">
                <?php foreach ($actions as $a): if (empty($a['label']) || empty($a['url'])) { continue; } ?>
                    <a class="btn <?= e($a['class'] ?? 'btn--cta btn--caps') ?>" href="<?= e($a['url']) ?>"><?= e($a['label']) ?></a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</header>
