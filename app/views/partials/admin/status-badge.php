<?php
/**
 * Coloured status pill. Vars: $status (raw value, e.g. 'published'), $label (optional text; default = humanised status),
 * $tone (optional override: success|warning|danger|info|neutral|primary|gold|orange).
 * Default tone map covers the statuses used across the schema.
 */
static $tones = [
    'published' => 'success', 'active' => 'success', 'received' => 'success', 'registered' => 'info', 'attended' => 'success', 'hired' => 'success',
    'shortlisted' => 'gold', 'replied' => 'success', 'contacted' => 'info', 'in_progress' => 'info', 'reviewing' => 'info', 'ongoing' => 'success',
    'subscribed' => 'success', 'read' => 'neutral', 'new' => 'warning', 'pledged' => 'warning', 'draft' => 'warning', 'planned' => 'info',
    'archived' => 'neutral', 'closed' => 'neutral', 'completed' => 'neutral', 'cancelled' => 'danger', 'rejected' => 'danger',
    'unsubscribed' => 'neutral', 'inactive' => 'neutral', 'hidden' => 'neutral', 'yes' => 'success', 'no' => 'neutral',
];
$s = strtolower((string) ($status ?? ''));
$tone = $tone ?? ($tones[$s] ?? 'neutral');
?>
<span class="badge badge--<?= e($tone) ?> badge--dot"><?= e($label ?? label_from($s)) ?></span>
