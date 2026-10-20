<?php
/**
 * Designer tab "The e-mail". OWNER: CARD agent. Vars: see index.php ($f, $emailPlaceholders, $emailPreview (subject, text, html), $adminEmail, $tab).
 * The subject and the message are settings (placeholders in braces); the HTML version of the e-mail is built from the message by
 * MembershipMail::renderCard(): the same words with the organisation's colours, a button to the member's private page, and the support contact. The card itself is
 * attached as a PDF generated at the moment of sending. The preview below is that real message built with a fictional member; it is shown in a sandboxed frame.
 */
$desc = [
    '{name}' => 'Full name', '{first_name}' => 'First name', '{site}' => 'Website name', '{number}' => 'Member number', '{type}' => 'Membership type',
    '{valid_until}' => 'Last valid day', '{start_date}' => 'Start date', '{dashboard_url}' => 'Link to the private page', '{contact_email}' => 'Contact e-mail',
    '{support_phone}' => 'Support phone', '{photo_note}' => 'Photo reminder (only if no photo)',
];
$items = [];
foreach ($emailPlaceholders as $ph) {
    $items[$ph] = $desc[$ph] ?? '';
}
?>
<section class="card mc-panel" id="tab-email" role="tabpanel" aria-labelledby="mc-tab-email" data-mc-panel="email">
    <?= partial('admin/membership-card/_panel_head', ['key' => 'email', 'tab' => $tab]) ?>
    <div class="card__body">
        <section class="form-section" aria-labelledby="mc-sec-mail">
            <h3 class="form-section__title" id="mc-sec-mail">Message to the member</h3>
            <p class="form-section__help">Sent with the card attached as a PDF when a card is issued, re-issued, or re-sent. Put each placeholder in braces; it is replaced with the member's own details.</p>
            <div class="form-grid">
                <?= $f['text']('membership_card_email_subject', ['class' => 'field--full', 'attrs' => ['data-mc-insertable' => 'email']]) ?>
                <?= $f['text']('membership_card_email_body', ['rows' => 14, 'class' => 'field--full', 'attrs' => ['data-mc-insertable' => 'email'],
                    'help' => 'Leave a blank line between paragraphs. Lines written as "Label: value" are shown as a small table in the HTML version. A link is made clickable automatically.']) ?>
            </div>
            <?= $f['chips']('[data-mc-insertable="email"]', $items, 'Click to put a placeholder into the subject or message (the one you used last)') ?>
        </section>

        <section class="form-section" aria-labelledby="mc-sec-mailprev">
            <h3 class="form-section__title" id="mc-sec-mailprev">What the member receives</h3>
            <p class="form-section__help">Built with a fictional member from the words above, even before you save. Mail programs show it a little differently.</p>
            <div class="mc-mail" data-mc-mail>
                <p class="mc-mail__subject"><span class="mc-mail__label">Subject</span> <strong data-mc-mail-subject><?= e($emailPreview['subject']) ?></strong></p>
                <div class="mc-viewswitch" role="group" aria-label="Show the message as">
                    <button type="button" class="mc-viewswitch__btn is-active" data-mc-mail-view="html" aria-pressed="true">Formatted</button>
                    <button type="button" class="mc-viewswitch__btn" data-mc-mail-view="text" aria-pressed="false">Plain text</button>
                </div>
                <iframe class="mc-mail__frame" title="Preview of the card e-mail" sandbox="" srcdoc="<?= e($emailPreview['html']) ?>" data-mc-mail-html></iframe>
                <pre class="mc-mail__text" data-mc-mail-text hidden><?= e($emailPreview['text']) ?></pre>
            </div>
            <p class="cluster">
                <button class="btn btn--outline btn--sm" type="submit" form="mc-test"<?= $adminEmail === '' ? ' disabled' : '' ?>><?= icon('send', 'icon--sm') ?> Send a test card to my e-mail</button>
                <?php if ($adminEmail !== ''): ?><span class="field__help">Goes to <?= e($adminEmail) ?> only, with the sample card attached, clearly marked as a test.</span><?php endif; ?>
            </p>
        </section>
    </div>
</section>
