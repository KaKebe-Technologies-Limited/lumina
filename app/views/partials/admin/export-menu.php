<?php
/**
 * "Export" drop-down: the same export in CSV (plain data), Excel, Word or PDF (the last three carry the organisation's letterhead). Every export address understands
 * "?format=csv|xlsx|docx|pdf" (App\Services\Export\Exporter).
 * Vars: $url (the export address, may already have a query), $label (default "Export"; "CSV" in it is dropped), $class (button class, default btn--outline), $small (bool).
 */
$label = trim((string) preg_replace('/\s*\(?\bCSV\b\)?/i', '', (string) ($label ?? 'Export')));
$label = $label === '' || strcasecmp($label, 'Download as') === 0 ? 'Export' : $label;
$label = preg_replace('/^Download all as$/i', 'Export all', $label) ?: $label;
$sep = str_contains((string) $url, '?') ? '&' : '?';
?>
<details class="export-menu">
    <summary class="btn <?= e($class ?? 'btn--outline') ?><?= !empty($small) ? ' btn--sm' : '' ?>"><?= icon('download', 'icon--sm') ?> <?= e($label) ?> <span class="export-menu__caret" aria-hidden="true">&#9662;</span></summary>
    <div class="export-menu__list">
        <?php foreach (\App\Services\Export\Exporter::FORMATS as $key => [$name]): ?>
            <a href="<?= e($url . $sep . 'format=' . $key) ?>" class="export-menu__item"><?= e($name) ?></a>
        <?php endforeach; ?>
    </div>
</details>
