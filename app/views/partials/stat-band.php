<?php
/**
 * Deep rounded stats card with big gold numerals. Vars: $stats (rows of the stats table: ['label','value'|'value_number','prefix','suffix']),
 * $overlap (bool: pull the card up over the band above), $class. Renders 1-4 columns and NOTHING when there are no stats
 * (never invent figures). Numerals count up on scroll (data-count-to); the final value is already in the HTML.
 *   <?= partial('partials/stat-band', ['stats' => Stat::forGroup('home'), 'overlap' => true]) ?>
 */
$sbStats = array_slice(array_values(array_filter((array) ($stats ?? []), static fn($s): bool => is_array($s) && trim((string) ($s['label'] ?? '')) !== '')), 0, 4);
if (!$sbStats) {
    return;
}
$sbCols = count($sbStats);
?>
<div class="stat-band stat-band--<?= $sbCols ?><?= !empty($overlap) ? ' stat-band--overlap' : '' ?><?= !empty($class) ? ' ' . e($class) : '' ?>" data-reveal>
    <div class="stat-band__card">
        <?php foreach ($sbStats as $s):
            $v = $s['value'] ?? $s['value_number'] ?? 0;
            $prefix = (string) ($s['prefix'] ?? '');
            $suffix = (string) ($s['suffix'] ?? '');
        ?>
            <div class="stat-band__item">
                <span class="stat-band__value" data-count-to="<?= (int) $v ?>" data-count-prefix="<?= e($prefix) ?>" data-count-suffix="<?= e($suffix) ?>"><?= e($prefix . number_format((int) $v) . $suffix) ?></span>
                <span class="stat-band__label"><?= e($s['label']) ?></span>
            </div>
        <?php endforeach; ?>
    </div>
</div>
