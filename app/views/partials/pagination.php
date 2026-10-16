<?php
/**
 * Pagination controls. Vars: $pagination (App\Core\Pagination), $base (optional path like 'blog'; default = current path;
 * the current query string is preserved), $summary (bool, default false: "Showing 1-12 of 40" - pass text via $summaryText).
 * Labels are ARIA-only (previous/next use arrows).
 */
if (!isset($pagination) || !$pagination->hasPages()) {
    return;
}
$base = $base ?? null;
?>
<nav class="pagination" aria-label="Pagination">
    <a class="pagination__item<?= $pagination->hasPrev() ? '' : ' is-disabled' ?>" href="<?= e($pagination->url(max(1, $pagination->page - 1), $base)) ?>" rel="prev" aria-label="Previous page"<?= $pagination->hasPrev() ? '' : ' aria-disabled="true" tabindex="-1"' ?>><?= icon('chevron-left') ?></a>
    <?php foreach ($pagination->window() as $n): ?>
        <?php if ($n === null): ?><span class="pagination__gap" aria-hidden="true">…</span>
        <?php else: ?><a class="pagination__item<?= $n === $pagination->page ? ' is-current' : '' ?>" href="<?= e($pagination->url($n, $base)) ?>"<?= $n === $pagination->page ? ' aria-current="page"' : '' ?> aria-label="Page <?= (int) $n ?>"><?= (int) $n ?></a><?php endif; ?>
    <?php endforeach; ?>
    <a class="pagination__item<?= $pagination->hasNext() ? '' : ' is-disabled' ?>" href="<?= e($pagination->url(min($pagination->pages, $pagination->page + 1), $base)) ?>" rel="next" aria-label="Next page"<?= $pagination->hasNext() ? '' : ' aria-disabled="true" tabindex="-1"' ?>><?= icon('chevron-right') ?></a>
    <?php if (!empty($summaryText)): ?><span class="pagination__summary"><?= e($summaryText) ?></span><?php endif; ?>
</nav>
