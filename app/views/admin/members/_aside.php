<?php
/**
 * Side column of the member screen: quick contact, internal notes, e-mails, consent record and (administrators) deletion.
 * Vars: $member, $received (received payment rows), $methods, $canDelete, $hasPayments, $erasable (status allows erasing the personal data).
 * PRIVACY: the consent record shows when the person accepted each statement and from which network address; staff only.
 */
use App\Services\Inbox\Support;
use App\Services\Membership\MembershipService as MS;

$id = (int) $member['id'];
$mail = Support::mailto((string) $member['email']);
$waDigits = preg_replace('/[^0-9]/', '', (string) ($member['whatsapp'] ?? '')) ?? '';
$phoneLink = phone_href((string) $member['phone']);

$templates = ['ack' => 'Application received (acknowledgement)'];
if ($member['status'] === 'approved') {
    $templates['approved'] = 'Welcome: membership approved';
}
if ($member['status'] === 'rejected') {
    $templates['rejected'] = 'Application not accepted';
}
if ($received !== []) {
    $templates['payment'] = 'Payment received (confirmation)';
}
$receivedOptions = [];
foreach ($received as $p) {
    $receivedOptions[(int) $p['id']] = ($p['receipt_number'] ?: 'Payment') . ' - ' . MS::formatMoney($p['amount'], (string) $p['currency']);
}
$deleteBlock = !empty($member['membership_number']) || $hasPayments;
$erased = !empty($member['anonymised_at']);
$verNote = static fn(?string $v): string => $v !== null && $v !== '' ? ' <span class="cell-sub">Text version ' . e($v) . '</span>' : '';
?>
<?php if (!$erased): ?>
<section class="card" id="contact" aria-labelledby="contact-title">
    <div class="card__header"><h2 class="card__title" id="contact-title">Get in touch</h2></div>
    <div class="card__body">
        <div class="members-contact">
            <?php if ($mail !== ''): ?><a class="btn btn--outline btn--sm" href="<?= e($mail) ?>"><?= icon('mail', 'icon--sm') ?> E-mail</a><?php endif; ?>
            <?php if ($phoneLink !== ''): ?><a class="btn btn--outline btn--sm" href="<?= e($phoneLink) ?>"><?= icon('phone', 'icon--sm') ?> Call</a><?php endif; ?>
            <?php if ($waDigits !== ''): ?><a class="btn btn--outline btn--sm" href="<?= e('https://wa.me/' . $waDigits) ?>" target="_blank" rel="noopener noreferrer"><?= icon('whatsapp', 'icon--sm') ?> WhatsApp</a><?php endif; ?>
        </div>
    </div>
</section>

<section class="card" id="notes" aria-labelledby="notes-title">
    <div class="card__header"><h2 class="card__title" id="notes-title">Internal notes</h2></div>
    <form method="post" action="<?= e(url('admin/members/' . $id)) ?>" data-unsaved-guard>
        <?= csrf_field() ?>
        <input type="hidden" name="section" value="notes">
        <div class="card__body">
            <?= partial('partials/form-field', ['name' => 'nt[internal_notes]', 'id' => 'nt-notes', 'label' => 'Notes for the team', 'type' => 'textarea', 'rows' => 5, 'maxlength' => 5000,
                'value' => old('nt[internal_notes]', (string) ($member['internal_notes'] ?? '')), 'help' => 'Only staff can see these notes. Do not paste ID numbers or health details here.']) ?>
        </div>
        <div class="card__footer"><button class="btn btn--primary btn--sm" type="submit"><?= icon('save', 'icon--sm') ?> Save notes</button></div>
    </form>
</section>

<section class="card" id="emails" aria-labelledby="emails-title">
    <div class="card__header"><h2 class="card__title" id="emails-title">Send an e-mail again</h2></div>
    <form method="post" action="<?= e(url('admin/members/' . $id . '/resend')) ?>" data-confirm="Send this e-mail to the member now?" data-members-resend>
        <?= csrf_field() ?>
        <div class="card__body">
            <?= partial('partials/form-field', ['name' => 'rs[template]', 'id' => 'rs-template', 'label' => 'Which e-mail?', 'type' => 'select', 'options' => $templates, 'value' => old('rs[template]', 'ack'),
                'help' => 'Sent to the address on the application, with the wording from Pages > Membership > E-mail templates.', 'attrs' => ['data-resend-template' => true]]) ?>
            <?php if ($receivedOptions !== []): ?>
                <div data-for="payment">
                    <?= partial('partials/form-field', ['name' => 'rs[payment_id]', 'id' => 'rs-payment', 'label' => 'For which payment?', 'type' => 'select', 'options' => $receivedOptions,
                        'value' => old('rs[payment_id]', (string) array_key_first($receivedOptions))]) ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="card__footer"><button class="btn btn--outline btn--sm" type="submit"><?= icon('send', 'icon--sm') ?> Send e-mail</button></div>
    </form>
</section>

<?php else: ?>
<section class="card" id="notes" aria-labelledby="notes-title">
    <div class="card__header"><h2 class="card__title" id="notes-title">Personal data erased</h2></div>
    <div class="card__body"><p class="text-muted">Contact details, notes and e-mails are gone with the personal data. Only the reference, the membership number, the fee and the receipts remain.</p></div>
</section>
<?php endif; ?>

<section class="card" id="consent" aria-labelledby="consent-title">
    <div class="card__header"><h2 class="card__title" id="consent-title">Consent record</h2></div>
    <div class="card__body">
        <dl class="definition-list members-consent">
            <dt>Membership terms</dt><dd><?= !empty($member['consent_terms_at']) ? e(format_datetime($member['consent_terms_at'])) . $verNote($member['consent_terms_ver'] ?? null) : '<span class="text-muted">Not recorded</span>' ?></dd>
            <dt>Privacy notice</dt><dd><?= !empty($member['consent_privacy_at']) ? e(format_datetime($member['consent_privacy_at'])) . $verNote($member['consent_privacy_ver'] ?? null) : '<span class="text-muted">Not recorded</span>' ?></dd>
            <dt>Information is true</dt><dd><?= !empty($member['declaration_at']) ? e(format_datetime($member['declaration_at'])) : '<span class="text-muted">Not recorded</span>' ?></dd>
            <?php if (!empty($member['consent_guardian_at'])): ?><dt>Guardian consent</dt><dd><?= e(format_datetime($member['consent_guardian_at'])) ?></dd>
            <?php elseif (!$erased && MS::isMinor((string) $member['date_of_birth'])): ?><dt>Guardian consent</dt><dd><span class="text-muted">Not recorded</span></dd><?php endif; ?>
            <dt>News and updates</dt><dd><?= !empty($member['consent_updates']) ? 'Agreed to receive them' : 'Not requested' ?></dd>
            <?php if (!empty($member['consent_ip'])): ?><dt>Network address</dt><dd><?= e($member['consent_ip']) ?></dd><?php endif; ?>
            <?php if (!empty($member['user_agent'])): ?><dt>Browser</dt><dd title="<?= e($member['user_agent']) ?>"><?= e(Support::agent((string) $member['user_agent'])) ?></dd><?php endif; ?>
        </dl>
    </div>
</section>

<?php if ($canDelete && !$erased && $deleteBlock): ?>
    <section class="card members-danger" id="erase" aria-labelledby="erase-title">
        <div class="card__header"><h2 class="card__title" id="erase-title">Erase personal data</h2></div>
        <div class="card__body">
            <?php if ($erasable): ?>
                <p class="small text-muted">For an erasure request from someone whose record cannot be deleted. Name, contact, date of birth, guardian, ID, answers and notes are blanked. The membership number, fee and receipts stay. This cannot be undone.</p>
                <form method="post" action="<?= e(url('admin/members/' . $id . '/anonymise')) ?>" data-confirm="Erase the personal data of this record permanently? This cannot be undone.">
                    <?= csrf_field() ?>
                    <div class="field">
                        <label class="field__label" for="erase-ref">Type <strong class="members-ref"><?= e($member['application_ref']) ?></strong> to confirm</label>
                        <input class="input" id="erase-ref" type="text" name="confirm_ref" autocomplete="off" spellcheck="false" required>
                    </div>
                    <button class="btn btn--danger btn--sm" type="submit"><?= icon('trash', 'icon--sm') ?> Erase personal data</button>
                </form>
            <?php else: ?>
                <p class="text-muted">Cancel the membership first (status panel). Personal data can be erased only once the member is no longer pending, approved or suspended.</p>
            <?php endif; ?>
        </div>
    </section>
<?php endif; ?>

<?php if ($canDelete): ?>
    <section class="card members-danger" id="delete" aria-labelledby="delete-title">
        <div class="card__header"><h2 class="card__title" id="delete-title">Delete this record</h2></div>
        <div class="card__body">
            <?php if ($deleteBlock): ?>
                <p class="text-muted">
                    <?= !empty($member['membership_number']) ? 'This member has membership number ' . e($member['membership_number']) . '.' : 'This member has payment records.' ?>
                    Numbers and receipts must stay on record, so the record cannot be deleted. Use <em>Cancel the membership</em> in the status panel instead.
                </p>
            <?php else: ?>
                <p class="small text-muted">For spam, test entries, or when someone asks for an application that was never approved to be erased. This cannot be undone.</p>
                <form method="post" action="<?= e(url('admin/members/' . $id . '/delete')) ?>" data-confirm="Delete this application permanently? This cannot be undone." data-members-delete="<?= e($member['application_ref']) ?>">
                    <?= csrf_field() ?>
                    <div class="field">
                        <label class="field__label" for="confirm-ref">Type <strong class="members-ref"><?= e($member['application_ref']) ?></strong> to confirm</label>
                        <input class="input" id="confirm-ref" type="text" name="confirm_ref" autocomplete="off" spellcheck="false" required>
                    </div>
                    <button class="btn btn--danger btn--sm" type="submit" data-delete-submit><?= icon('trash', 'icon--sm') ?> Delete application</button>
                </form>
            <?php endif; ?>
        </div>
    </section>
<?php endif; ?>
