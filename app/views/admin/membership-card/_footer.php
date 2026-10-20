<?php
/** Designer tab "Footer & contact". OWNER: CARD agent. Vars: see index.php ($f, $tab). */
$t = $f['text'];
$sitePhone = \App\Services\Membership\Card\CardData::localPhone((string) setting('phone_1', ''));
$siteEmail = trim((string) (setting('email_info', '') ?: setting('email_primary', '')));
?>
<section class="card mc-panel" id="tab-footer" role="tabpanel" aria-labelledby="mc-tab-footer" data-mc-panel="footer">
    <?= partial('admin/membership-card/_panel_head', ['key' => 'footer', 'tab' => $tab]) ?>
    <div class="card__body">
        <section class="form-section" aria-labelledby="mc-sec-lines">
            <h3 class="form-section__title" id="mc-sec-lines">Footer lines</h3>
            <p class="form-section__help">Up to three small lines at the bottom left. A piece that is missing (for example the joining fee of a free membership) leaves no dangling dot. Leave a line empty to hide it.</p>
            <div class="mc-footer-lines" data-mc-footer>
                <?= $t('membership_card_footer_1', ['attrs' => ['data-mc-insertable' => 'footer']]) ?>
                <?= $t('membership_card_footer_2', ['attrs' => ['data-mc-insertable' => 'footer']]) ?>
                <?= $t('membership_card_footer_3', ['attrs' => ['data-mc-insertable' => 'footer']]) ?>
            </div>
            <?= $f['chips']('[data-mc-insertable="footer"]', [
                '{joining}' => 'Joining fee line', '{issued}' => 'Issue date', '{phone}' => 'Support phone', '{email}' => 'Support e-mail',
                '{number}' => 'Member number', '{name}' => 'Member name', '{since}' => 'Member since', '{valid_until}' => 'Valid until',
            ], 'Click to put a placeholder into the line you used last') ?>
            <p class="field__help">The red line "SAMPLE CARD" you see in the preview only appears on samples. It is never printed on a real card.</p>
        </section>

        <section class="form-section" aria-labelledby="mc-sec-joining">
            <h3 class="form-section__title" id="mc-sec-joining">The joining fee line</h3>
            <p class="form-section__help">What <code>{joining}</code> says, depending on whether the joining fee was paid, waived or is still due. Use {amount} for the figure. Members without a joining fee get nothing.</p>
            <div class="form-grid form-grid--3">
                <?= $t('membership_card_joining_paid') ?>
                <?= $t('membership_card_joining_due') ?>
                <?= $t('membership_card_joining_waived') ?>
            </div>
        </section>

        <section class="form-section" aria-labelledby="mc-sec-contact">
            <h3 class="form-section__title" id="mc-sec-contact">Support contact</h3>
            <div class="form-grid">
                <?= $t('membership_card_support_phone', ['help' => 'Written as it should appear, for example 0779 712 990. Empty = the phone number in Site settings' . ($sitePhone !== '' ? ' (now ' . $sitePhone . ').' : ' (none is set there yet).')]) ?>
                <?= $t('membership_card_support_email', ['help' => 'Empty = the info e-mail address in Site settings' . ($siteEmail !== '' ? ' (now ' . $siteEmail . ').' : ' (none is set there yet).')]) ?>
            </div>
        </section>
    </div>
</section>
