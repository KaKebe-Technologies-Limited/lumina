<?php
/**
 * Hero of the Programs pages (list and pillar). Same look as partials/page-hero (photo or navy gradient, breadcrumbs, serif title, rounded
 * sheet cut-in) but with the extras the planned-programs copy needs: a title in several display lines, several intro paragraphs,
 * a vision sentence and a small meta row (pillar number + status badge). All text must come from content() / DB rows.
 *
 * Vars:
 *  $titles  list of title lines (text; [[highlight]] markers allowed). Together they form the ONE <h1>.
 *  $eyebrow text above the title.
 *  $kicker  SAFE html shown between the breadcrumbs and the title (number + status badge), optional.
 *  $leads   list of intro paragraphs (text). The first is the large lead under the title; the rest go in the panel beside it (desktop).
 *  $lead_label  optional label printed (bold) before the first lead, e.g. "Planned focus".
 *  $quote   the vision sentence (text), printed at the bottom of the panel with a gold bar.
 *  $actions list of ['label','url','class'].
 *  $image   ['src','alt','focus'] background photo (optional).
 *  $sheet   colour of the first section below: 'white' (default) | 'tint' | 'cool'.
 */
$phTitles = array_values(array_filter(array_map(static fn($t): string => trim((string) $t), (array) ($titles ?? [])), static fn(string $t): bool => $t !== ''));
if ($phTitles === []) {
    return;
}
$phLeads = array_values(array_filter(array_map(static fn($t): string => trim((string) $t), (array) ($leads ?? [])), static fn(string $t): bool => $t !== ''));
$phLead = $phLeads[0] ?? '';
$phMore = array_slice($phLeads, 1);
$phQuote = trim((string) ($quote ?? ''));
$phLabel = trim((string) ($lead_label ?? ''));
$phHasImg = !empty($image['src']);
$phFocus = preg_match('/^[0-9%. a-z-]{1,30}$/i', (string) ($image['focus'] ?? '')) ? (string) $image['focus'] : '';
$phSheet = in_array($sheet ?? 'white', ['tint', 'cool'], true) ? ' page-hero--sheet-' . $sheet : '';
$phHasPanel = $phMore !== [] || $phQuote !== '';
$phActions = array_values(array_filter((array) ($actions ?? []), static fn($a): bool => !empty($a['label']) && !empty($a['url'])));
$phMeta = (string) ($kicker ?? '');
?>
<header class="page-hero programs-hero<?= $phHasImg ? ' page-hero--image' : '' ?><?= $phSheet ?><?= $phHasPanel ? ' programs-hero--panel' : '' ?>">
    <?php if ($phHasImg): ?><?= partial('partials/hero-img', ['src' => $image['src'], 'alt' => $image['alt'] ?? '', 'class' => 'page-hero__bg', 'focus' => $phFocus]) ?><?php endif; ?>
    <div class="page-hero__inner">
        <?= partial('partials/breadcrumbs', ['breadcrumbs' => $breadcrumbs ?? []]) ?>
        <?php if ($phMeta !== ''): ?><div class="programs-hero__meta"><?= $phMeta ?></div><?php endif; ?>
        <?php if (!empty($eyebrow)): ?><span class="eyebrow"><?= e($eyebrow) ?></span><br><?php endif; ?>
        <h1 class="page-hero__title programs-hero__title"><?php foreach ($phTitles as $i => $t): ?><span class="programs-hero__line"><?= highlight($t) ?></span><?= $i < count($phTitles) - 1 ? ' ' : '' ?><?php endforeach; ?></h1>
        <div class="programs-hero__grid">
            <div class="programs-hero__main">
                <?php if ($phLead !== ''): ?><p class="page-hero__lead programs-hero__lead"><?php if ($phLabel !== ''): ?><strong class="programs-hero__label"><?= e($phLabel) ?>:</strong> <?php endif; ?><?= e($phLead) ?></p><?php endif; ?>
                <?php if ($phActions): ?>
                    <div class="page-hero__actions">
                        <?php foreach ($phActions as $a): ?>
                            <a class="btn <?= e($a['class'] ?? 'btn--cta btn--caps') ?>" href="<?= e($a['url']) ?>"><?= e($a['label']) ?></a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <?php if ($phHasPanel): ?>
                <div class="programs-hero__panel">
                    <?php foreach ($phMore as $p): ?><p class="programs-hero__para"><?= e($p) ?></p><?php endforeach; ?>
                    <?php if ($phQuote !== ''): ?><p class="programs-hero__vision"><?= e($phQuote) ?></p><?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</header>
