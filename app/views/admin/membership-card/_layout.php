<?php
/** Designer tab "Blocks & tiles". OWNER: CARD agent. Vars: see index.php ($f, $tileOptions, $values, $tab). */
$t = $f['text'];
$tileLabelKeys = [
    'member_since' => 'membership_card_label_member_since', 'payment' => 'membership_card_label_payment', 'fee' => 'membership_card_label_fee',
    'type' => 'membership_card_label_type', 'district' => 'membership_card_label_district',
];
?>
<section class="card mc-panel" id="tab-layout" role="tabpanel" aria-labelledby="mc-tab-layout" data-mc-panel="layout">
    <?= partial('admin/membership-card/_panel_head', ['key' => 'layout', 'tab' => $tab]) ?>
    <div class="card__body">
        <section class="form-section" aria-labelledby="mc-sec-blocks">
            <h3 class="form-section__title" id="mc-sec-blocks">Blocks</h3>
            <div class="mc-switches">
                <?= $f['check']('membership_card_show_photo', 'The square with the member\'s photo, or with their initials when there is no photo yet. Off: the name moves to the left edge.') ?>
                <?= $f['check']('membership_card_show_qr', 'The QR code opens the verification page, which says whether the card is genuine and current. Switching it off removes the only way to check a card.') ?>
            </div>
        </section>

        <section class="form-section" aria-labelledby="mc-sec-tiles">
            <h3 class="form-section__title" id="mc-sec-tiles">The three information tiles</h3>
            <p class="form-section__help">Choose what each tile says. Choose "Leave this tile out" and the other tiles grow wider.</p>
            <div class="mc-tiles" data-mc-tiles>
                <?php foreach (['membership_card_tile_1', 'membership_card_tile_2', 'membership_card_tile_3'] as $k): ?>
                    <div class="mc-tileslot">
                        <?= $f['select']($k, $tileOptions, ['attrs' => ['data-mc-tile' => true]]) ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="form-section" aria-labelledby="mc-sec-tilewords">
            <h3 class="form-section__title" id="mc-sec-tilewords">Tile labels</h3>
            <p class="form-section__help">The small grey label of a tile. "Valid until" and "Member number" use the labels from the Words tab. Labels of tiles you are not using are dimmed.</p>
            <div class="form-grid">
                <?php foreach ($tileLabelKeys as $tileKey => $k): ?>
                    <div class="mc-tilelabel" data-mc-tilelabel="<?= e($tileKey) ?>">
                        <?= $t($k, $k === 'membership_card_label_fee' ? ['help' => 'Use {period} for ANNUAL, MONTHLY, QUARTERLY, LIFETIME or ONE-TIME.'] : []) ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="form-section mc-tilelabel" data-mc-tilelabel="payment" aria-labelledby="mc-sec-pay">
            <h3 class="form-section__title" id="mc-sec-pay">Payment tile wording</h3>
            <p class="form-section__help">What the Payment tile says, depending on the member's payment.</p>
            <div class="form-grid form-grid--3">
                <?= $t('membership_card_pay_paid') ?>
                <?= $t('membership_card_pay_waived') ?>
                <?= $t('membership_card_pay_pending') ?>
                <?= $t('membership_card_pay_partial') ?>
                <?= $t('membership_card_pay_free') ?>
                <?= $t('membership_card_pay_tbd') ?>
            </div>
        </section>

        <section class="form-section mc-tilelabel" data-mc-tilelabel="fee" aria-labelledby="mc-sec-fee">
            <h3 class="form-section__title" id="mc-sec-fee">Fee tile wording</h3>
            <p class="form-section__help">What the fee tile prints after the amount (for example "UGX 50,000 / year"), and what it says when there is no amount to show. Empty keeps just the amount.</p>
            <div class="form-grid form-grid--3">
                <?= $t('membership_card_fee_per_year') ?>
                <?= $t('membership_card_fee_per_month') ?>
                <?= $t('membership_card_fee_per_quarter') ?>
                <?= $t('membership_card_fee_for_life') ?>
                <?= $t('membership_card_fee_one_off') ?>
                <?= $t('membership_card_fee_free') ?>
                <?= $t('membership_card_fee_tbd') ?>
            </div>
        </section>

        <section class="form-section mc-tilelabel" data-mc-tilelabel="fee" aria-labelledby="mc-sec-period">
            <h3 class="form-section__title" id="mc-sec-period">Period words</h3>
            <p class="form-section__help">The word that replaces {period} in the fee tile label (for example "ANNUAL MEMBERSHIP"). Capital letters look best.</p>
            <div class="form-grid form-grid--3">
                <?= $t('membership_card_period_annual') ?>
                <?= $t('membership_card_period_monthly') ?>
                <?= $t('membership_card_period_quarterly') ?>
                <?= $t('membership_card_period_lifetime') ?>
                <?= $t('membership_card_period_one_time') ?>
            </div>
        </section>
    </div>
</section>
