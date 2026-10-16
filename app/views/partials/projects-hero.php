<?php
/**
 * Banner of /projects/{slug}: the shared inner-page banner (same markup and classes as partials/page-hero, so it looks identical) plus a
 * status badge under the title. page-hero has no slot for a badge and is a shared file, hence this local copy.
 * Vars: $title* ([[highlight]] allowed), $eyebrow, $lead, $badge (['label' => text, 'tone' => info|success|neutral]),
 *       $breadcrumbs (default: the layout's), $sheet ('white' default | 'tint' | 'cool').
 * All text comes from the database / content().
 */
$phCrumbs = $breadcrumbs ?? [];
$phSheet = in_array($sheet ?? 'white', ['tint', 'cool'], true) ? ' page-hero--sheet-' . $sheet : '';
$phBadge = is_array($badge ?? null) && trim((string) ($badge['label'] ?? '')) !== '' ? $badge : null;
?>
<header class="page-hero pi-hero<?= $phSheet ?>">
    <div class="page-hero__inner">
        <?= partial('partials/breadcrumbs', ['breadcrumbs' => $phCrumbs]) ?>
        <?php if (!empty($eyebrow)): ?><span class="eyebrow"><?= e($eyebrow) ?></span><br><?php endif; ?>
        <h1 class="page-hero__title"><?= highlight((string) $title) ?></h1>
        <?php if ($phBadge !== null): ?><p class="pi-hero__badge"><span class="badge badge--<?= e((string) ($phBadge['tone'] ?? 'neutral')) ?>"><?= e((string) $phBadge['label']) ?></span></p><?php endif; ?>
        <?php if (!empty($lead)): ?><p class="page-hero__lead"><?= e($lead) ?></p><?php endif; ?>
    </div>
</header>
