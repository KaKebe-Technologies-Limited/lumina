<?php
/**
 * DONATE: the thank-you page a donor sees right after sending the pledge form (GET /donate/thanks; DonateController::thanks).
 * Vars: $t (what was pledged: name, amount, currency, method, ref = "DON-<id>", amount_text), $giveNow (is there a give-online form on /donate?).
 * It thanks the donor, says that no money has been taken, shows the foundation's bank account with the reference to quote and the amount (partials/bank-card: nothing is
 * printed while the manual-details switch is off), the mobile-money / PayPal details of Settings > Donations when they are set, and what happens next.
 * Copy: content('donate', 'thanks.*'). The page is private (no-store) and noindex.
 */

$first = trim((string) strtok(trim((string) ($t['name'] ?? '')), ' '));
$first = trim(str_replace(['[', ']', '*'], '', $first));
$fill = static fn(string $text): string => strtr($text, ['{name}' => $first !== '' ? $first : 'friend', '{amount}' => (string) ($t['amount_text'] ?? '')]);

$manualOn = \App\Services\Payments\PaymentOptions::manual();
$bankCard = \App\Services\Payments\OfficialAccount::visible();
$mobile = $manualOn ? trim((string) setting('donate_mobile_money', '')) : '';
$paypal = $manualOn ? trim((string) setting('donate_paypal_email', '')) : '';
$copy = (string) content('donate', 'ways.copy_label');
$copied = (string) content('donate', 'ways.copied_label');
$hasDetails = $bankCard || $mobile !== '' || $paypal !== '';
$foreign = strtoupper((string) ($t['currency'] ?? 'UGX')) !== 'UGX' ? trim((string) content('donate', 'thanks.foreign_note')) : '';
$steps = [];
foreach (content_list('donate', 'thanks.next_items') as $row) {
    if (trim((string) ($row['text'] ?? '')) !== '') {
        $steps[] = (string) $row['text'];
    }
}
$donateUrl = url('donate');
?>
<?= partial('partials/page-hero', [
    'title' => $fill((string) content('donate', 'thanks.title')),
    'lead'  => $fill((string) content('donate', 'thanks.lead')),
]) ?>
<?php $this->start('scripts'); ?><script src="<?= e(asset('js/pages/donate.js')) ?>" defer></script><?php $this->end(); ?>

<section class="section donate-ways donate-thanks" id="thanks-pay" aria-labelledby="donate-thanks-title">
    <div class="container">
        <?= partial('partials/section-head', ['title' => (string) content('donate', 'thanks.pay_title'), 'align' => 'center', 'id' => 'donate-thanks-title']) ?>
        <?php if ($hasDetails): ?>
            <div class="ia-cards donate-ways__grid">
                <?php if ($bankCard): ?>
                    <div class="donate-way donate-way--account" id="donate-account" data-reveal>
                        <?= partial('partials/bank-card', [
                            'id' => 'thanks-account', 'title' => (string) content('donate', 'ways.bank_title'), 'lead' => (string) content('donate', 'thanks.bank_lead'),
                            'reference' => (string) $t['ref'], 'amount' => (string) ($t['amount_text'] ?? ''), 'copyLabel' => $copy, 'copiedLabel' => $copied,
                        ]) ?>
                    </div>
                <?php endif; ?>
                <?php if ($mobile !== ''): ?>
                    <article class="feature-card donate-way" data-reveal>
                        <span class="feature-card__icon"><?= icon('phone') ?></span>
                        <div class="feature-card__body"><h3 class="feature-card__title"><?= e(content('donate', 'ways.mobile_title')) ?></h3></div>
                        <div class="donate-details" id="donate-mobile"><?= nl2br(e($mobile), false) ?></div>
                        <p class="donate-way__cta"><button class="btn btn--outline btn--caps btn--sm" type="button" data-copy="<?= e($mobile) ?>" data-copied="<?= e($copied) ?>" hidden><?= icon('copy', 'icon--sm') ?> <span><?= e($copy) ?></span></button></p>
                    </article>
                <?php endif; ?>
                <?php if ($paypal !== ''): ?>
                    <article class="feature-card donate-way" data-reveal>
                        <span class="feature-card__icon"><?= icon('globe') ?></span>
                        <div class="feature-card__body"><h3 class="feature-card__title"><?= e(content('donate', 'ways.paypal_title')) ?></h3></div>
                        <p class="donate-details"><?= e(content('donate', 'ways.paypal_text')) ?> <a href="mailto:<?= e($paypal) ?>"><?= e($paypal) ?></a></p>
                        <p class="donate-way__cta"><button class="btn btn--outline btn--caps btn--sm" type="button" data-copy="<?= e($paypal) ?>" data-copied="<?= e($copied) ?>" hidden><?= icon('copy', 'icon--sm') ?> <span><?= e($copy) ?></span></button></p>
                    </article>
                <?php endif; ?>
            </div>
            <?php if ($foreign !== ''): ?><p class="donate-details donate-thanks__foreign" role="note"><?= e($foreign) ?></p><?php endif; ?>
        <?php else: ?>
            <div class="donate-details donate-thanks__none"><?= e(content('donate', 'thanks.no_details')) ?></div>
        <?php endif; ?>
        <?php if (!empty($giveNow) && trim((string) content('donate', 'thanks.online_label')) !== ''): ?>
            <p class="donate-thanks__online"><?= e(content('donate', 'thanks.online_text')) ?>
                <a class="btn btn--outline btn--caps" href="<?= e($donateUrl . '#give-online') ?>"><?= e(content('donate', 'thanks.online_label')) ?> <?= icon('arrow-right', 'icon--sm') ?></a></p>
        <?php endif; ?>
    </div>
</section>

<?php if ($steps !== []): ?>
<section class="section section--tint donate-thanks-next" id="thanks-next" aria-labelledby="donate-next-title">
    <div class="container container--narrow">
        <?= partial('partials/section-head', ['title' => (string) content('donate', 'thanks.next_title'), 'align' => 'center', 'id' => 'donate-next-title']) ?>
        <ol class="donate-next">
            <?php foreach ($steps as $s): ?><li><?= e($s) ?></li><?php endforeach; ?>
        </ol>
        <p class="donate-thanks__back"><a class="btn btn--cta btn--caps" href="<?= e($donateUrl) ?>"><?= e(content('donate', 'thanks.back_label')) ?></a></p>
    </div>
</section>
<?php else: ?>
<section class="section" aria-label="<?= e(content('donate', 'thanks.back_label')) ?>">
    <div class="container container--narrow"><p class="donate-thanks__back"><a class="btn btn--cta btn--caps" href="<?= e($donateUrl) ?>"><?= e(content('donate', 'thanks.back_label')) ?></a></p></div>
</section>
<?php endif; ?>
