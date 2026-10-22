<?php
/**
 * One downloadable document (B-INNER-B). Uses the shared .document-row look, with a download link whose accessible name
 * includes the document title. The file type (PDF, DOCX...) sits in the round icon tile.
 * Vars: $doc (documents row), $categoryLabels (category => label), $cta (button text), $publishedLabel.
 * The button goes to /reports/download/{id}: a controller counts the download and streams the private file (public documents only).
 */
$file = trim((string) ($doc['file_name'] ?? '')) !== '' ? (string) $doc['file_name'] : (string) $doc['file_path'];
$ext = strtoupper(pathinfo($file, PATHINFO_EXTENSION));
$published = format_date($doc['published_at'] ?? null);
$cat = (string) ($categoryLabels[$doc['category']] ?? '');
?>
<article class="document-row" data-reveal>
    <span class="document-row__icon"><?= icon('file-text', 'icon--lg') ?><?php if ($ext !== ''): ?><span class="document-row__ext" aria-hidden="true"><?= e(mb_substr($ext, 0, 5)) ?></span><?php endif; ?></span>
    <div>
        <h3 class="document-row__title"><?= e($doc['title']) ?></h3>
        <div class="document-row__meta">
            <?php if ($cat !== ''): ?><span class="document-row__cat"><?= e($cat) ?></span><?php endif; ?>
            <?php if (!empty($doc['year'])): ?><span><?= (int) $doc['year'] ?></span><?php endif; ?>
            <?php if ($ext !== ''): ?><span class="visually-hidden"><?= e($ext) ?></span><?php endif; ?>
            <?php if (!empty($doc['signed_path'])): ?><span class="document-row__cat"><?= icon('check-circle', 'icon--sm') ?> Signed<?= !empty($doc['adoption_date']) ? ' &middot; adopted ' . e(format_date($doc['adoption_date'], 'j M Y')) : '' ?></span><?php endif; ?>
            <?php if (!empty($doc['file_size'])): ?><span><?= e(human_filesize((int) (!empty($doc['signed_path']) && !empty($doc['signed_size']) ? $doc['signed_size'] : $doc['file_size']))) ?></span><?php endif; ?>
            <?php if ($published !== '' && trim((string) $publishedLabel) !== ''): ?><span><?= e($publishedLabel) ?> <time datetime="<?= e(format_date($doc['published_at'], 'Y-m-d')) ?>"><?= e($published) ?></time></span><?php endif; ?>
        </div>
        <?php if (trim((string) ($doc['description'] ?? '')) !== ''): ?><p class="document-row__desc"><?= e($doc['description']) ?></p><?php endif; ?>
    </div>
    <div class="document-row__action">
        <a class="btn btn--outline btn--caps btn--sm" href="<?= e(url('reports/download/' . (int) $doc['id'])) ?>"><?= icon('download', 'icon--sm') ?> <?= e($cta) ?><span class="visually-hidden">: <?= e($doc['title']) ?></span></a>
    </div>
</article>
