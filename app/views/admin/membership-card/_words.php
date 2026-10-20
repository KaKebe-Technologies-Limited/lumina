<?php
/** Designer tab "Words". OWNER: CARD agent. Vars: see index.php ($f, $tab). Every line is cut to the length shown by its counter, and the card shrinks a long line to fit. */
$t = $f['text'];
?>
<section class="card mc-panel" id="tab-words" role="tabpanel" aria-labelledby="mc-tab-words" data-mc-panel="words">
    <?= partial('admin/membership-card/_panel_head', ['key' => 'words', 'tab' => $tab]) ?>
    <div class="card__body">
        <section class="form-section" aria-labelledby="mc-sec-head">
            <h3 class="form-section__title" id="mc-sec-head">Top of the card</h3>
            <div class="form-grid">
                <?= $t('membership_card_org_name', ['help' => 'Printed in capital letters next to the logo.']) ?>
                <?= $t('membership_card_tagline', ['help' => 'Leave it empty to print no tagline.']) ?>
                <?= $t('membership_card_label', ['help' => 'The small grey words above the membership type. Leave empty to hide them.']) ?>
            </div>
        </section>

        <section class="form-section" aria-labelledby="mc-sec-name">
            <h3 class="form-section__title" id="mc-sec-name">Name and location</h3>
            <div class="form-grid">
                <?= $t('membership_card_label_member', ['help' => 'Small label above the name. Empty hides it.']) ?>
                <?= $t('membership_card_label_from', ['help' => 'Small label above the location. Empty hides it.']) ?>
                <div class="field--full">
                    <?= $t('membership_card_location_format', ['help' => 'How the place is written. Missing pieces and their commas simply disappear, for example "Gulu City, Uganda".']) ?>
                    <?= $f['chips']('#f-membership_card_location_format', ['{city}' => 'City or sub-county', '{district}' => 'District or region', '{country}' => 'Country']) ?>
                </div>
            </div>
        </section>

        <section class="form-section" aria-labelledby="mc-sec-panel">
            <h3 class="form-section__title" id="mc-sec-panel">Side panel</h3>
            <div class="form-grid">
                <?= $t('membership_card_label_number') ?>
                <?= $t('membership_card_label_valid_until') ?>
                <?= $t('membership_card_scan_text', ['help' => 'Printed under the QR code.']) ?>
                <?= $t('membership_card_no_expiry_text', ['help' => 'Printed instead of a date for lifetime and one-time memberships.']) ?>
                <div class="field--full">
                    <?= $t('membership_card_renews_text', ['help' => 'Printed under the expiry date when the membership has to be renewed. Empty hides it.']) ?>
                    <?= $f['chips']('#f-membership_card_renews_text', ['{period}' => 'annually, monthly or quarterly']) ?>
                </div>
                <?= $t('membership_card_adverb_annual', ['help' => 'The word that replaces {period} in the text under the expiry date.']) ?>
                <?= $t('membership_card_adverb_monthly') ?>
                <?= $t('membership_card_adverb_quarterly') ?>
                <?= $t('membership_card_panel_text', ['rows' => 2, 'class' => 'field--full', 'help' => 'At the bottom of the side panel. One or two lines (press Enter for a second line).']) ?>
            </div>
        </section>

        <section class="form-section" aria-labelledby="mc-sec-status">
            <h3 class="form-section__title" id="mc-sec-status">Status wording</h3>
            <p class="form-section__help">The coloured pill under the member number. It always shows the member's real situation when the card is opened: an expired membership turns grey, a suspended or revoked one turns red.</p>
            <div class="form-grid">
                <?= $t('membership_card_status_active') ?>
                <?= $t('membership_card_status_expired') ?>
                <?= $t('membership_card_status_suspended') ?>
                <?= $t('membership_card_status_revoked') ?>
            </div>
        </section>
    </div>
</section>
