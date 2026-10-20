<?php
/**
 * Member screen: PROOF OF PAYMENT panel. OWNER: STAFF agent. Contract: docs/MEMBERSHIP_CARD.md (sections 4, 10, 11).
 * Vars: $member (row), $summary (MembershipService::paymentSummary), $proofBy (name of the staff member who last checked it, '').
 * The picture is streamed by the controller (authenticated, no-store, never a file path). Staff can zoom / rotate it (assets/js/admin-members-card.js),
 * mark it verified, ask for a better copy (the applicant gets a polite e-mail with the link to their private page) or put it back to "needs a check".
 */
use App\Services\Membership\MemberFiles;
use App\Services\Membership\MembershipService as MS;

$id = (int) $member['id'];
$erased = !empty($member['anonymised_at']);
$hasProof = MemberFiles::hasProof($member);
$status = (string) ($member['proof_status'] ?? 'none');
$isPdf = ($member['proof_mime'] ?? '') === 'application/pdf';
$when = static fn(?string $d): string => $d ? format_datetime($d, 'j M Y, H:i') : '';
$states = [
    'none'     => ['No proof', 'neutral'],
    'received' => ['Needs a check', 'warning'],
    'verified' => ['Verified', 'success'],
    'rejected' => ['Needs a better copy', 'danger'],
];
[$stateLabel, $stateTone] = $states[$hasProof ? $status : 'none'] ?? $states['none'];
$owing = (float) $summary['balance'] > 0;
$open = in_array((string) $member["status"], ["pending", "approved"], true);
$method = (string) ($member['payment_method'] ?? '') !== '' ? MS::optionLabel('payment_methods', (string) $member['payment_method']) : '';
$fileUrl = url('admin/members/' . $id . '/proof', ['v' => substr(md5((string) ($member['proof_uploaded_at'] ?? '')), 0, 8)]);
?>
<section class="card mproof" id="proof" aria-labelledby="proof-title">
    <div class="card__header">
        <h2 class="card__title" id="proof-title">Proof of payment</h2>
        <span class="badge badge--<?= e($stateTone) ?> badge--dot"><?= e($stateLabel) ?></span>
    </div>
    <div class="card__body mfiles">
        <?php if ($erased): ?>
            <p class="text-muted">The personal data of this member was erased, together with the proof of payment.</p>
        <?php elseif (!$hasProof): ?>
            <p class="text-muted">No proof of payment was uploaded. The member can add one from their private page, or you can upload one that reached you another way (for example a screenshot sent on WhatsApp).</p>
            <?php if (!empty($member['payment_reference'])): ?>
                <p class="mfiles__fact"><span>Reference the member typed</span><strong class="members-ref"><?= e($member['payment_reference']) ?></strong></p>
            <?php endif; ?>
        <?php else: ?>
            <div class="mproof__grid">
                <div class="mproof__view">
                    <?php if ($isPdf): ?>
                        <div class="mproof__pdf">
                            <?= icon('file-text', 'icon--lg') ?>
                            <strong><?= e($member['proof_name'] ?? 'proof.pdf') ?></strong>
                            <span class="text-muted small">PDF document. It opens in your PDF reader after you download it.</span>
                            <a class="btn btn--primary btn--sm" href="<?= e(url('admin/members/' . $id . '/proof', ['download' => 1])) ?>"><?= icon('download', 'icon--sm') ?> Download the PDF</a>
                        </div>
                    <?php else: ?>
                        <div class="mviewer" data-mviewer>
                            <div class="mviewer__bar" role="toolbar" aria-label="Zoom and rotate the picture" data-mviewer-bar hidden>
                                <button class="btn btn--outline btn--sm" type="button" data-mv="out" aria-label="Zoom out"><span aria-hidden="true">&minus;</span></button>
                                <output class="mviewer__zoom" data-mv-zoom aria-live="polite">100%</output>
                                <button class="btn btn--outline btn--sm" type="button" data-mv="in" aria-label="Zoom in"><span aria-hidden="true">+</span></button>
                                <button class="btn btn--outline btn--sm" type="button" data-mv="left" aria-label="Rotate left"><span aria-hidden="true">&#8634;</span> Left</button>
                                <button class="btn btn--outline btn--sm" type="button" data-mv="right" aria-label="Rotate right"><span aria-hidden="true">&#8635;</span> Right</button>
                                <button class="btn btn--ghost btn--sm" type="button" data-mv="reset">Reset</button>
                            </div>
                            <div class="mviewer__stage" tabindex="0" role="region" aria-label="Proof of payment. Scroll to move around; press plus or minus to zoom, R or L to rotate." data-mviewer-stage>
                                <div class="mviewer__canvas" data-mviewer-canvas>
                                    <img class="mviewer__img" src="<?= e($fileUrl) ?>" alt="Proof of payment uploaded for <?= e($member['application_ref']) ?>" data-mviewer-img>
                                </div>
                            </div>
                            <p class="mviewer__links small"><a href="<?= e($fileUrl) ?>" target="_blank" rel="noopener">Open full size</a> &middot; <a href="<?= e(url('admin/members/' . $id . '/proof', ['download' => 1])) ?>">Download</a></p>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="mproof__side">
                    <dl class="mfiles__meta">
                        <div><dt>Uploaded</dt><dd><?= e($when($member['proof_uploaded_at'] ?? null)) ?></dd></div>
                        <?php if (!empty($member['proof_verified_at']) && in_array($status, ['verified', 'rejected'], true)): ?>
                            <div><dt><?= $status === 'verified' ? 'Verified' : 'Marked "needs a better copy"' ?></dt><dd><?= e($when($member['proof_verified_at'])) ?><?= $proofBy !== '' ? ' by ' . e($proofBy) : '' ?></dd></div>
                        <?php endif; ?>
                        <?php if (!empty($member['payment_reference'])): ?><div><dt>Reference the member typed</dt><dd class="members-ref"><?= e($member['payment_reference']) ?></dd></div><?php endif; ?>
                        <?php if ($method !== ''): ?><div><dt>Paying by</dt><dd><?= e($method) ?></dd></div><?php endif; ?>
                        <div><dt>Balance on the account</dt><dd><?= $summary['status'] === 'tbd' ? 'Fee not set' : ($owing ? e(MS::formatMoney((float) $summary['balance'], (string) $summary['currency'])) . ' owing' : 'Nothing owing') ?></dd></div>
                    </dl>

                    <?php if ($status === 'received'): ?>
                        <p class="mfiles__next"><?= icon('info', 'icon--sm') ?> Check the amount, the date and the name on the proof against your bank or mobile money statement.
                            <?php if ($open && $owing): ?>Then <a href="#add-payment" data-open-payment>record the payment</a> and tick "attach the member's proof": it is marked verified in the same step.<?php endif; ?></p>
                    <?php elseif ($status === 'rejected'): ?>
                        <p class="mfiles__next"><?= icon('info', 'icon--sm') ?> The member was asked for a clearer copy. A new upload puts it back in the "needs a check" queue.</p>
                    <?php endif; ?>

                    <div class="mfiles__actions" role="group" aria-label="Set the status of the proof">
                        <?php foreach ([
                            'verified' => ['Mark as verified', 'check', 'btn--primary', ''],
                            'rejected' => ['Needs a better copy', 'mail', 'btn--outline', 'Ask the member for a clearer copy? They get a polite e-mail with the link to upload a new one.'],
                            'received' => ['Back to "needs a check"', 'refresh', 'btn--ghost', ''],
                        ] as $to => [$text, $ico, $cls, $ask]): $now = $status === $to; ?>
                            <form method="post" action="<?= e(url('admin/members/' . $id . '/proof/status')) ?>"<?= $ask !== '' && !$now ? ' data-confirm="' . e($ask) . '"' : '' ?>><?= csrf_field() ?><input type="hidden" name="status" value="<?= e($to) ?>">
                                <button class="btn <?= $now ? 'btn--outline is-current' : e($cls) ?> btn--sm" type="submit"<?= $now ? ' aria-current="true"' : '' ?>><?= icon($ico, 'icon--sm') ?> <?= e($text) ?><?= $now ? '<span class="visually-hidden"> (the current status)</span>' : '' ?></button></form>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!$erased): ?>
            <div class="mfiles__upload">
                <form method="post" action="<?= e(url('admin/members/' . $id . '/proof')) ?>" enctype="multipart/form-data" class="mfiles__row">
                    <?= csrf_field() ?>
                    <div class="field">
                        <label class="field__label" for="staff-proof"><?= $hasProof ? 'Replace the proof of payment' : 'Upload a proof of payment' ?></label>
                        <input class="input" id="staff-proof" type="file" name="proof" accept="image/jpeg,image/png,image/webp,application/pdf" required aria-describedby="staff-proof-help">
                        <div class="field__help" id="staff-proof-help">JPG, PNG, WebP or PDF, 5 MB at most. Pictures are saved without location data.</div>
                    </div>
                    <button class="btn btn--outline btn--sm" type="submit"><?= icon('upload', 'icon--sm') ?> <?= $hasProof ? 'Replace' : 'Upload' ?></button>
                </form>
                <?php if ($hasProof): ?>
                    <form method="post" action="<?= e(url('admin/members/' . $id . '/proof/remove')) ?>" data-confirm="Remove the proof of payment permanently? Copies attached to payments stay."><?= csrf_field() ?>
                        <button class="btn btn--ghost btn--sm" type="submit"><?= icon('trash', 'icon--sm') ?> Remove the proof</button></form>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
