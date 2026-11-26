<?php
/**
 * The Pesapal pay button with its short explanation: the SECOND way to pay wherever mobile money (the first way) is offered.
 * Vars: $action (POST address, required), $button (optional button text), $primary (bool, true when Pesapal is the only way shown).
 * The label and the helper text are the same everywhere (PesapalPlaces::LABEL / HELP), so a payer meets the same words on every page.
 */
use App\Services\Pesapal\PesapalPlaces;

$button = (string) ($button ?? PesapalPlaces::LABEL);
$primary = !empty($primary);
$id = 'pesapal-help-' . substr(md5((string) $action), 0, 8);
?>
<form class="pesapal-pay" method="post" action="<?= e((string) $action) ?>" style="margin:0 0 1rem">
    <?= csrf_field() ?>
    <button class="btn <?= $primary ? 'btn--cta' : 'btn--outline' ?> btn--caps" type="submit" aria-describedby="<?= e($id) ?>" data-loading-text="Opening the secure page..."><?= icon('lock', 'icon--sm') ?> <?= e($button) ?></button>
    <span class="form-note" id="<?= e($id) ?>" style="display:block;margin-top:.5rem"><?= e(PesapalPlaces::HELP) ?></span>
</form>
