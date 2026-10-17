<?php
/**
 * CONFIRMATION (/membership/confirmation/{ref}?t=...). OWNER: WEB agent. Reached only through the signed link of the redirect after submitting.
 * Vars: $member (row, or null = the link is not valid or has expired), $fee (fee snapshot label), $joining, $instructions (plain text).
 * PRIVACY: shows ONLY the first name, the reference, the type and the fee snapshot, the payment instructions and the next steps (noindex).
 */

$homeLabel = (string) content('membership', 'confirmation.home_label');
?>
<?php if ($member === null): ?>
<?= partial('partials/page-hero', [
    'title'   => (string) content('membership', 'confirmation.invalid_title'),
    'lead'    => (string) content('membership', 'confirmation.invalid_text'),
    'actions' => [['label' => $homeLabel, 'url' => url('membership'), 'class' => 'btn--cta btn--caps']],
    'sheet'   => 'white',
]) ?>
<?php return; endif;

// first name only, reduced to letters, digits and ' . - (the name was typed by the visitor; same rule as the e-mails), else the editable fallback word
$first = (string) preg_replace('~(?:https?://|www\.)\S*~iu', ' ', (string) $member['full_name']);
$first = trim((string) preg_replace('/[^\p{L}\p{M}\p{N} \'.-]+/u', ' ', $first));
$first = mb_substr(explode(' ', preg_replace('/\s+/u', ' ', $first))[0] ?? '', 0, 40);
if (preg_match('/\p{L}/u', $first) !== 1) {
    $first = (string) content('membership', 'emails.name_fallback');
}
$next = array_values(array_filter(array_map('trim', preg_split('/\R{2,}/u', trim(str_replace("\r", '', (string) content('membership', 'confirmation.next_text')))) ?: []), static fn(string $s): bool => $s !== ''));
$keepNote = trim((string) content('membership', 'confirmation.keep_note'));
?>
<?= partial('partials/page-hero', [
    'title' => (string) content('membership', 'confirmation.title'),
    'lead'  => str_replace('{first_name}', $first, (string) content('membership', 'confirmation.lead')),
    'sheet' => 'tint',
]) ?>

<section class="section section--tint mb-confirm" aria-label="<?= e(strip_highlight((string) content('membership', 'confirmation.title'))) ?>">
    <div class="container">
        <div class="mb-confirm__grid">
            <div class="mb-receipt">
                <span class="mb-receipt__icon"><?= icon('check-circle', 'icon--xl') ?></span>
                <dl class="mb-receipt__list">
                    <div>
                        <dt><?= e(content('membership', 'confirmation.ref_label')) ?></dt>
                        <dd class="mb-receipt__ref"><?= e($member['application_ref']) ?></dd>
                    </div>
                    <div>
                        <dt><?= e(content('membership', 'confirmation.type_label')) ?></dt>
                        <dd><?= e($member['type_name']) ?></dd>
                    </div>
                    <div>
                        <dt><?= e(content('membership', 'confirmation.fee_label')) ?></dt>
                        <dd><?= e($fee) ?><?php if ($joining !== ''): ?><span class="mb-receipt__sub"><?= e($joining) ?></span><?php endif; ?></dd>
                    </div>
                </dl>
                <?php if ($keepNote !== ''): ?><p class="mb-receipt__note"><?= e($keepNote) ?></p><?php endif; ?>
            </div>

            <div class="mb-confirm__main">
                <div class="mb-pay mb-pay--card">
                    <span class="mb-pay__icon"><?= icon('banknote', 'icon--lg') ?></span>
                    <div class="mb-pay__body">
                        <h2 class="mb-pay__title"><?= e(content('membership', 'confirmation.payment_title')) ?></h2>
                        <?= $instructions !== '' ? nl2p($instructions) : nl2p((string) content('membership', 'confirmation.payment_none')) ?>
                    </div>
                </div>

                <?php if ($next): ?>
                    <section class="mb-next" data-reveal aria-labelledby="mb-next-title">
                        <h2 class="mb-next__title" id="mb-next-title"><?= e(content('membership', 'confirmation.next_title')) ?></h2>
                        <ol class="mb-next__list">
                            <?php foreach ($next as $i => $line): ?>
                                <li><span class="mb-next__num" aria-hidden="true"><?= $i + 1 ?></span><span><?= nl2br(e($line), false) ?></span></li>
                            <?php endforeach; ?>
                        </ol>
                    </section>
                <?php endif; ?>

                <p class="mb-confirm__actions"><a class="btn btn--outline btn--caps" href="<?= e(url('membership')) ?>"><?= e($homeLabel) ?></a></p>
            </div>
        </div>
    </div>
</section>
