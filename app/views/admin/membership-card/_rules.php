<?php
/**
 * Designer tab "When it is issued". OWNER: CARD agent. Vars: see index.php ($f, $values, $triggers, $stats, $tab).
 * The trigger itself is evaluated by MembershipService / CardService::event() (approval, payment, renewal, photo upload); this tab only sets the rules.
 */
$trigger = (string) ($values['membership_card_trigger'] ?? 'paid_approved');
$choices = [
    'paid_approved' => ['Approved and paid', 'Recommended. The card is made and e-mailed once the member is approved AND the balance is cleared (paid, waived, or a free membership), whichever happens last.'],
    'approved'      => ['Approved', 'The card goes out as soon as the application is approved, even before a payment is recorded. Use this when fees are collected later.'],
    'manual'        => ['Staff decide', 'Nothing is sent automatically. Staff press "Issue card now" on the member\'s page when they are ready.'],
];
?>
<section class="card mc-panel" id="tab-rules" role="tabpanel" aria-labelledby="mc-tab-rules" data-mc-panel="rules">
    <?= partial('admin/membership-card/_panel_head', ['key' => 'rules', 'tab' => $tab]) ?>
    <div class="card__body">
        <section class="form-section" aria-labelledby="mc-sec-trigger">
            <h3 class="form-section__title" id="mc-sec-trigger">Automatic card</h3>
            <fieldset class="mc-choice<?= has_error('membership_card_trigger') ? ' mc-choice--error' : '' ?>">
                <legend class="field__label"><?= e($f['label']('membership_card_trigger')) ?></legend>
                <div class="mc-choice__list">
                    <?php foreach ($triggers as $value => $long): $c = $choices[$value] ?? [$long, '']; ?>
                        <label class="mc-choice__item">
                            <input type="radio" name="membership_card_trigger" value="<?= e((string) $value) ?>"<?= $trigger === (string) $value ? ' checked' : '' ?> data-mc-field>
                            <span class="mc-choice__label"><?= e($c[0]) ?></span>
                            <span class="mc-choice__hint"><?= e($c[1]) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
                <?php if (has_error('membership_card_trigger')): ?><div class="field__error" role="alert"><?= e((string) error('membership_card_trigger')) ?></div><?php endif; ?>
            </fieldset>
            <p class="field__help">Staff can always issue, re-send, re-issue or revoke a card by hand on the member's page. Every card e-mail is sent once per event: approving or recording a payment twice never sends two cards.</p>
        </section>

        <section class="form-section" aria-labelledby="mc-sec-conditions">
            <h3 class="form-section__title" id="mc-sec-conditions">Photo, renewals and retention</h3>
            <div class="mc-switches">
                <?= $f['check']('membership_card_reissue_renewal', 'When a renewal is approved and paid, the card is issued again with the new valid-until date and e-mailed. The previous card then stops verifying.') ?>
                <?= $f['check']('membership_card_require_photo', 'On: nothing is issued until the member has a photo (the member and staff are told why). Off: a member without a photo gets a card showing their initials, like the sample.') ?>
                <?= $f['check']('membership_photo_required', 'On: the registration form asks for a photo and the consent tick "I agree my photo is printed on my membership card".') ?>
            </div>
            <div class="mc-retention">
                <?= $f['text']('membership_proof_retention_days', ['type' => 'number', 'min' => 0, 'max' => 3650, 'step' => 1, 'attrs' => ['inputmode' => 'numeric'],
                    'help' => 'Photos and proofs of payment are personal data. After a rejection or a cancellation they are deleted this many days later. 0 = keep them until you erase the member. Needs the daily job bin/membership-maintenance.php.']) ?>
            </div>
        </section>

        <section class="form-section" aria-labelledby="mc-sec-state">
            <h3 class="form-section__title" id="mc-sec-state">Cards today</h3>
            <dl class="mc-stats">
                <div><dt>Cards issued</dt><dd><?= (int) $stats['issued'] ?></dd></div>
                <div><dt>Waiting for a re-issue</dt><dd><?= (int) $stats['reissue'] ?></dd></div>
                <div><dt>Revoked</dt><dd><?= (int) $stats['revoked'] ?></dd></div>
            </dl>
            <p class="field__help">A card changes its look when you change the design (the next download or e-mail uses it), but the member's own details on it (name, photo, number, dates) stay as they were at issue until you use "Re-issue" on that member.
                <?php if ((int) $stats['reissue'] > 0): ?><a href="<?= e(url('admin/members', ['card' => 'reissue'])) ?>">Show the members waiting for a re-issue</a>.<?php endif; ?></p>
        </section>

        <details class="mc-details">
            <summary>Exactly what happens, step by step</summary>
            <table class="table mc-rules-table" aria-label="What happens in each situation">
                <thead><tr><th scope="col">Situation</th><th scope="col">Approved and paid</th><th scope="col">Approved</th><th scope="col">Staff decide</th></tr></thead>
                <tbody>
                    <tr><th scope="row">Not approved yet</th><td>Nothing</td><td>Nothing</td><td>Nothing</td></tr>
                    <tr><th scope="row">Approved, balance not cleared</th><td>Waits for the payment</td><td>Issue and e-mail</td><td>Nothing</td></tr>
                    <tr><th scope="row">Approved, balance cleared</th><td>Issue and e-mail</td><td>Issue and e-mail</td><td>Nothing (staff issue)</td></tr>
                    <tr><th scope="row">A photo is needed but missing</th><td colspan="3">Waits until the photo arrives, then issues (only with "Do not issue a card until the member has a photo")</td></tr>
                    <tr><th scope="row">Renewal approved and paid</th><td>New card, e-mailed</td><td>New card, e-mailed</td><td>Nothing</td></tr>
                    <tr><th scope="row">Photo or name changed after issue</th><td colspan="3">The member is flagged "needs re-issue"; staff decide</td></tr>
                    <tr><th scope="row">Card revoked</th><td colspan="3">Nothing is issued again until staff issue it on purpose</td></tr>
                </tbody>
            </table>
        </details>
    </div>
</section>
