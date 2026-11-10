<?php
/** Pay with mobile money (Daraza). Vars: $row (ledger row), $what, $orig (amount in the original currency or ''), $phone, $ready, $next (where to go when paid). */
$paid = $row['status'] === 'paid';
$this->start('scripts'); ?><script src="<?= e(asset('js/mobile-money.js')) ?>" defer></script><?php $this->end();
?>
<?= partial('partials/page-hero', ['title' => 'Pay with mobile money', 'eyebrow' => $what, 'lead' => 'MTN Mobile Money or Airtel Money. You approve the payment on your phone.']) ?>
<section class="section mm">
    <div class="container container--narrow">
        <div class="card" data-mm data-run="<?= e(url('pay/mobile-money/' . $row['reference'] . '/run')) ?>" data-status="<?= e(url('pay/mobile-money/' . $row['reference'] . '/status')) ?>" data-next="<?= e($next) ?>" data-paid="<?= $paid ? '1' : '0' ?>">
            <div class="card__body">
                <p class="mm__amount"><span>Amount</span> <strong>UGX <?= e(number_format((int) $row['amount_ugx'])) ?></strong><?php if ($orig !== ''): ?> <small class="text-muted">(<?= e($orig) ?>)</small><?php endif; ?></p>
                <p class="text-muted mm__ref">Reference <?= e($row['reference']) ?></p>

                <div class="mm__done" data-mm-done<?= $paid ? '' : ' hidden' ?>>
                    <p class="opp-notice ac-ok" role="status"><?= icon('check-circle', 'icon--sm') ?> <strong>Payment received. Thank you!</strong></p>
                    <p><a class="btn btn--cta btn--caps" href="<?= e($next) ?>" data-mm-next>Continue</a></p>
                </div>

                <?php if (!$ready && !$paid): ?>
                    <p class="opp-notice opp-notice--closed" role="status"><?= icon('alert-triangle', 'icon--sm') ?> Mobile-money payment is not available right now. Please go back and choose another way to pay.</p>
                <?php elseif (!$paid): ?>
                    <form class="mm__form" data-mm-form novalidate>
                        <?= csrf_field() ?>
                        <div class="field"><label class="field__label" for="mm-phone">Mobile-money number</label>
                            <input class="input" id="mm-phone" name="phone" type="tel" inputmode="tel" autocomplete="tel" value="<?= e($phone) ?>" placeholder="0772 123 456" required data-mm-phone>
                            <div class="field__help">A Ugandan MTN or Airtel number. We send a payment request to this phone.</div></div>
                        <fieldset class="mm__nets"><legend class="field__label">Network</legend>
                            <label class="check"><input type="radio" name="method" value="1" data-mm-net> MTN Mobile Money</label>
                            <label class="check"><input type="radio" name="method" value="2" data-mm-net> Airtel Money</label>
                        </fieldset>
                        <p class="field__error" role="alert" data-mm-error hidden></p>
                        <button class="btn btn--cta btn--caps btn--lg" type="submit" data-mm-pay><?= icon('lock', 'icon--sm') ?> Pay UGX <?= e(number_format((int) $row['amount_ugx'])) ?></button>
                    </form>
                    <div class="mm__wait" data-mm-wait hidden role="status" aria-live="polite">
                        <p class="mm__spinner" aria-hidden="true"></p>
                        <p><strong>Check your phone now.</strong> Approve the payment request and enter your mobile-money PIN. This can take up to 5 minutes: please keep this page open.</p>
                        <p class="text-muted" data-mm-timer></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <p class="text-muted mm__note">Your PIN is entered on your phone only: we never see it. Questions? <a href="<?= e(url('academy/contact')) ?>">Write to us</a> and quote the reference above.</p>
    </div>
</section>
