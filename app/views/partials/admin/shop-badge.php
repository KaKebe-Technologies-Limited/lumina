<?php
/**
 * Coloured pill for a shop ORDER status or PAYMENT status (admin). Vars: $kind ('order' | 'payment'), $value (raw status), $text (optional: label override).
 */
use App\Services\Shop\OrderService;

static $tones = [
    'order'   => ['pending' => 'warning', 'confirmed' => 'info', 'processing' => 'info', 'ready' => 'gold', 'shipped' => 'info', 'completed' => 'success', 'cancelled' => 'danger', 'refunded' => 'neutral'],
    'payment' => ['unpaid' => 'warning', 'pending' => 'info', 'paid' => 'success', 'failed' => 'danger', 'review' => 'orange', 'refunded' => 'neutral'],
];
$k = ($kind ?? 'order') === 'payment' ? 'payment' : 'order';
$v = (string) ($value ?? '');
$label = $text ?? ($k === 'payment' ? OrderService::paymentStatusLabel($v) : OrderService::statusLabel($v));
?>
<span class="badge badge--<?= e($tones[$k][$v] ?? 'neutral') ?> badge--dot"><?= e($label) ?></span>
