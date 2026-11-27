<?php
/**
 * Thank you after a project gift that was not paid on the spot (a bigger gift, or another way to give). Vars: $p (project), $t (what was pledged: label, detail, amount, currency, method, name, big and
 * ref = what to write on a bank transfer, "<project> DON-<id>"; a session from before the reference existed has none and the project name is used), $methods.
 * Shows the foundation's bank account (partials/bank-card, with the reference and the amount) so the donor can give right away; the card is hidden while Admin > Payment options > "Bank and manual
 * mobile-money details" is off, and then the page says the team contacts the donor with the details.
 */
$donate = url('donate');
$bankOn = \App\Services\Payments\OfficialAccount::visible();
$ref = trim((string) ($t['ref'] ?? '')) !== '' ? trim((string) $t['ref']) : (string) $p['short'];
?>
<section class="fund-hero fund-hero--thanks">
    <div class="container container--narrow">
        <?= partial('partials/breadcrumbs', ['breadcrumbs' => $breadcrumbs ?? []]) ?>
        <div class="fund-thanks">
            <span class="fund-thanks__icon"><?= icon('heart', 'icon--xl') ?></span>
            <h1>Thank you, <?= e(trim((string) strtok((string) $t['name'], ' '))) ?>!</h1>
            <p class="fund-thanks__lead">Your gift to <strong><?= e($p['short']) ?></strong> has reached us.</p>
            <dl class="fund-thanks__sum">
                <div><dt>Your contribution</dt><dd><?= e($t['label']) ?></dd></div>
                <?php if ($t['detail'] !== ''): ?><div><dt>Details</dt><dd><?= e($t['detail']) ?></dd></div><?php endif; ?>
                <div><dt>Amount</dt><dd><strong><?= e($t['currency']) ?> <?= e(number_format((float) $t['amount'], 2)) ?></strong></dd></div>
                <div><dt>How you would like to give</dt><dd><?= e($methods[$t['method']] ?? $t['method']) ?></dd></div>
            </dl>
            <h2>What happens next</h2>
            <ol class="fund-thanks__next">
                <li><?= !empty($t['big']) ? 'Your gift is above the UGX 5,000,000 limit of one mobile-money payment, so we contact you, usually within a few working days, to arrange the payment with you.' . ($bankOn ? ' You can also send it by bank transfer to the account below.' : '') : 'We read your message and contact you, usually within a few working days, with the details to complete your gift.' ?></li>
                <?php if ($bankOn): ?>
                <li>You can give right now by bank transfer to the account below. Please quote <strong><?= e($ref) ?></strong> as the reference and share your proof of payment: we match it to your pledge and send your receipt.</li>
                <?php else: ?>
                <li>Please keep this reference and quote it if you write to us: <strong><?= e($ref) ?></strong>.</li>
                <?php endif; ?>
                <li>We report back to you on how your gift is used.</li>
            </ol>
            <?= partial('partials/bank-card', ['id' => 'thanks', 'tag' => 'h2', 'title' => 'Pay by bank transfer', 'lead' => 'Your pledge is saved. To give straight away, send it to our bank account.', 'reference' => $ref, 'amount' => (string) $t['currency'] . ' ' . number_format((float) $t['amount'], 2)]) ?>
            <p><a class="btn btn--cta btn--caps" href="<?= e($donate) ?>"><?= $bankOn ? 'More ways to give' : 'See the payment details' ?></a> <a class="btn btn--outline btn--caps" href="<?= e(url('fund-a-project')) ?>">Fund another project</a></p>
            <p class="fund-thanks__note">No money has been taken yet. Questions? Call <a href="<?= e(phone_href((string) setting('phone_1', ''))) ?>"><?= e((string) setting('phone_1', '')) ?></a> or write to <a href="mailto:<?= e((string) setting('email_primary', '')) ?>"><?= e((string) setting('email_primary', '')) ?></a>.</p>
        </div>
    </div>
</section>
