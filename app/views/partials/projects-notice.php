<?php
/**
 * Calm "responsible development" notice: planned program areas are not confirmation of funded / operational / delivering activities.
 * Copy: content('projects', 'disclaimer.*') (always on). Vars: $tag (heading tag, default h2), $id (heading id, default projects-notice-title).
 */
$pnTitle = trim((string) content('projects', 'disclaimer.title'));
$pnText = trim((string) content('projects', 'disclaimer.text'));
$pnEyebrow = trim((string) content('projects', 'disclaimer.eyebrow'));
if ($pnTitle === '' && $pnText === '') {
    return;
}
$pnTag = in_array($tag ?? 'h2', ['h2', 'h3'], true) ? ($tag ?? 'h2') : 'h2';
$pnId = preg_replace('/[^a-z0-9_-]/i', '', (string) ($id ?? 'projects-notice-title'));
?>
<div class="pi-notice" role="note"<?= $pnTitle !== '' ? ' aria-labelledby="' . e($pnId) . '"' : '' ?> data-reveal>
    <span class="pi-notice__icon" aria-hidden="true"><?= icon('shield', 'icon--lg') ?></span>
    <div class="pi-notice__body">
        <?php if ($pnEyebrow !== ''): ?><span class="eyebrow"><?= e($pnEyebrow) ?></span><?php endif; ?>
        <?php if ($pnTitle !== ''): ?><<?= $pnTag ?> class="pi-notice__title" id="<?= e($pnId) ?>"><?= highlight($pnTitle) ?></<?= $pnTag ?>><?php endif; ?>
        <?php if ($pnText !== ''): ?><?= nl2p($pnText) ?><?php endif; ?>
    </div>
</div>
