<?php
/**
 * "You can pay with MTN or Airtel" banner, shown wherever mobile money is offered (donate, shop checkout, projects, membership, Academy fees).
 * Vars: $compact (bool, one line), $text (optional own sentence).
 */
$compact = !empty($compact);
?>
<div class="paynet<?= $compact ? ' paynet--compact' : '' ?>" role="note" aria-label="Mobile-money payment">
    <span class="paynet__logos" aria-hidden="true"><span class="paynet__tag paynet__tag--mtn">MTN</span><span class="paynet__tag paynet__tag--airtel">Airtel</span></span>
    <span class="paynet__text"><strong><?= e((string) ($text ?? 'Pay with MTN or Airtel Mobile Money')) ?></strong><?php if (!$compact): ?><span>Any MTN or Airtel number works. You approve the payment on your phone with your PIN: it takes less than a minute, and we never see your PIN.</span><?php endif; ?></span>
</div>
