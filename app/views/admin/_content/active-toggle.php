<?php
/**
 * One-click active/inactive toggle for a list row (POST {path}/{id}/toggle; JSON-enhanced by admin-content.js). OWNER: B4.
 * Vars: $c (controller config), $row, $returnQs, $on (bool, default !empty($row['is_active'])), $onLabel ('Active'), $offLabel ('Inactive'), $name (row title for aria).
 */
$on = $on ?? !empty($row['is_active']);
$onLabel = $onLabel ?? 'Active';
$offLabel = $offLabel ?? 'Inactive';
$name = (string) ($name ?? ($row[$c['title_col'] ?? 'title'] ?? ''));
?>
<form method="post" action="<?= e(url($c['path'] . '/' . (int) $row['id'] . '/toggle')) ?>" class="inline-form" data-toggle-form>
    <?= csrf_field() ?>
    <input type="hidden" name="return" value="<?= e($returnQs ?? '') ?>">
    <button type="submit" class="status-toggle<?= $on ? ' is-on' : '' ?>" aria-pressed="<?= $on ? 'true' : 'false' ?>" data-on-label="<?= e($onLabel) ?>" data-off-label="<?= e($offLabel) ?>"
            title="<?= $on ? 'Click to hide this on the website' : 'Click to show this on the website' ?>">
        <span class="badge badge--<?= $on ? 'success' : 'neutral' ?> badge--dot" data-toggle-badge><?= e($on ? $onLabel : $offLabel) ?></span>
        <span class="visually-hidden"> - <?= e($name) ?>. <?= $on ? 'Press to deactivate' : 'Press to activate' ?></span>
    </button>
</form>
