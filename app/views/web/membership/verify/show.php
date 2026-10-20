<?php
/**
 * VERIFICATION PAGE (/membership/verify/{token}, the QR target). OWNER: DASHBOARD agent. Contract: docs/MEMBERSHIP_CARD.md.
 * Vars: $result (CardService::verification(): state valid|expired|suspended|invalid; for a card that exists also name, type, number, since, valid_until, no_expiry),
 *       $contact (membership contact e-mail).
 * PRIVACY: shows ONLY the result and the five facts above (name, type, number, member since, valid until). No photo, e-mail, phone, address, payment or application
 * reference. An unknown, replaced or withdrawn card shows NOT VALID and no member data at all. noindex, never cached, no referrer.
 * The result is told three ways (icon, word, colour), so it never depends on colour alone. All words: Admin > Pages > Membership > "verify".
 */
use App\Services\Membership\Card\CardData;

$state = (string) $result['state'];
if (!in_array($state, ['valid', 'expired', 'suspended', 'invalid'], true)) {
    $state = 'invalid';
}
$c = static fn(string $k): string => (string) content('membership', 'verify.' . $k);
$tone = ['valid' => 'ok', 'expired' => 'warn', 'suspended' => 'bad', 'invalid' => 'bad'][$state];
$icon = ['valid' => 'check-circle', 'expired' => 'clock', 'suspended' => 'alert-triangle', 'invalid' => 'alert-circle'][$state];
$contactLine = $contact !== '' ? str_replace('{contact_email}', $contact, $c('contact_text')) : '';
$validUntil = !empty($result['valid_until']) ? CardData::date($result['valid_until']) : $c('no_expiry');
?>
<section class="section section--tint mb-verify" aria-labelledby="mb-verify-title">
    <div class="container">
        <?php // a scan answers one question, so the result comes first: a short heading, then the card (no tall banner that would push it below the fold on a phone) ?>
        <header class="mb-verify__intro">
            <h1 class="mb-verify__title" id="mb-verify-title"><?= highlight($c('title')) ?></h1>
            <p class="mb-verify__lead"><?= e($c('lead')) ?></p>
        </header>
        <article class="mb-verify__card mb-verify__card--<?= e($tone) ?>" aria-labelledby="mb-verify-result">
            <header class="mb-verify__head">
                <span class="mb-verify__icon" aria-hidden="true"><?= icon($icon, 'icon--xl') ?></span>
                <p class="mb-verify__result mb-verify__result--<?= e($state) ?>" id="mb-verify-result" role="status"><span class="mb-verify__label"><?= e($c('label_' . $state)) ?></span></p>
                <p class="mb-verify__text"><?= e($c('text_' . $state)) ?></p>
            </header>

            <?php if ($state !== 'invalid'): ?>
                <dl class="mb-verify__facts">
                    <div class="mb-verify__fact mb-verify__fact--name"><dt><?= e($c('name_label')) ?></dt><dd><?= e((string) $result['name']) ?></dd></div>
                    <div class="mb-verify__fact"><dt><?= e($c('type_label')) ?></dt><dd><?= e((string) $result['type']) ?></dd></div>
                    <div class="mb-verify__fact"><dt><?= e($c('number_label')) ?></dt><dd class="mb-verify__number"><?= e((string) $result['number']) ?></dd></div>
                    <?php if (!empty($result['since'])): ?><div class="mb-verify__fact"><dt><?= e($c('since_label')) ?></dt><dd><?= e(CardData::date($result['since'])) ?></dd></div><?php endif; ?>
                    <div class="mb-verify__fact"><dt><?= e($c('valid_until_label')) ?></dt><dd><?= e($validUntil) ?></dd></div>
                </dl>
            <?php endif; ?>

            <footer class="mb-verify__foot">
                <p class="mb-verify__note"><?= icon('shield', 'icon--sm') ?> <span><?= e($c('privacy_note')) ?></span></p>
                <?php if ($contactLine !== ''): ?><p class="mb-verify__note"><?= icon('mail', 'icon--sm') ?> <span><?= e($contactLine) ?></span></p><?php endif; ?>
                <p class="mb-verify__more"><a class="btn btn--outline btn--caps" href="<?= e(url('membership')) ?>"><?= e($c('home_label')) ?></a></p>
            </footer>
        </article>
    </div>
</section>
