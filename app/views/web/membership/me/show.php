<?php
/**
 * MY MEMBERSHIP (/membership/me/{token}): the member's private page. OWNER: DASHBOARD agent. Contract: docs/MEMBERSHIP_CARD.md. All words come from the registry
 * (Admin > Pages > Membership: sections "dashboard" and "uploads"); the controller (Web/MembershipDashboardController) prepares everything below.
 * Vars: $member (row, or null = the link is not valid), $token, $first (first name, safe), $initials, $card (CardService::summary), $summary (payment summary), $fee, $joining,
 *       $instructions, $contact, $timeline (received|payment|approved|card => done|current|todo), $state (key of dashboard.state_*), $renewal (null | ['kind' => soon|expired,
 *       'date' => last valid day]), $hasPhoto, $hasProof, $proofIsPdf, $canPhoto, $canProof, $photoRequired, $hasPng, $liveLabel (status pill wording of the card), $urls (page, photo, proof, pdf, png, resend, photoPost, proofPost).
 * PRIVACY: only the member's own data; no other member, no file path (the pictures are streamed by the controller); noindex, no-store, no referrer.
 * Layout: progress -> card (or why there is none yet) -> two columns (the things to do | details and help). The panels of the first column are printed in the order that
 * matters NOW (a missing photo first), in the DOM as well, so the tab order is the visual order.
 */
use App\Services\Membership\Card\CardData;
use App\Services\Membership\MemberFiles;
use App\Services\Membership\MembershipService;

$c = static fn(string $k): string => (string) content('membership', $k);
if ($member === null): ?>
<?= partial('partials/page-hero', [
    'title'   => $c('dashboard.invalid_title'),
    'lead'    => $c('dashboard.invalid_text'),
    'actions' => [['label' => $c('confirmation.home_label'), 'url' => url('membership'), 'class' => 'btn--cta btn--caps']],
    'sheet'   => 'white',
]) ?>
<?php return; endif;

$steps = ['received', 'payment', 'approved', 'card'];
$proofStatus = (string) ($member['proof_status'] ?? 'none');
$cur = (string) $summary['currency'];
$balance = (float) $summary['balance'];
$cleared = in_array($summary['status'], ['paid', 'free'], true);
$issued = $card['state'] === 'issued';
$revoked = $card['state'] === 'revoked';
$photoWanted = !$hasPhoto && $canPhoto && ($photoRequired || in_array('photo_missing', $card['waiting'], true));       // the registration asked for it, or no card is issued without it
$sb = MembershipService::statusBadge((string) $member['status']);
$when = static fn(?string $d): string => $d ? CardData::date(substr((string) $d, 0, 10)) : '';
$validUntil = !empty($member['expiry_date']) ? $when(MembershipService::lastValidDay((string) $member['expiry_date'])) : '';
$stateIcons = ['pending' => 'clock', 'approved_unpaid' => 'banknote', 'approved_photo' => 'camera', 'approved_ready' => 'clock', 'issued' => 'check-circle', 'revoked' => 'alert-circle',
    'rejected' => 'alert-circle', 'cancelled' => 'alert-circle', 'suspended' => 'alert-triangle', 'expired' => 'clock'];
$stateTone = ['issued' => 'ok', 'rejected' => 'bad', 'cancelled' => 'bad', 'revoked' => 'bad', 'suspended' => 'bad', 'expired' => 'warn', 'approved_unpaid' => 'info', 'approved_photo' => 'info'][$state] ?? 'info';
$uploadMsgs = static fn(string $kind): array => [
    'none' => $c('uploads.err_' . $kind . '_none'), 'size' => $c('uploads.err_' . $kind . '_size'), 'type' => $c('uploads.err_' . $kind . '_type'), 'invalid' => $c('uploads.err_' . $kind . '_invalid'),
    'small' => $kind === 'photo' ? $c('uploads.err_photo_small') : '', 'missing' => $kind === 'photo' ? $c('uploads.err_photo_missing') : '',
];
$remove = MembershipService::copy('uploads.remove_label', 'Remove');
$chosen = MembershipService::copy('uploads.chosen_label', 'Chosen file:');
$this->start('scripts'); ?><script src="<?= e(asset('js/membership-dashboard.js')) ?>" defer></script><?php $this->end();

// ---- the panels of the first column (rendered once, printed in the order that matters now) -----------------------------------------------------------
ob_start(); ?>
<section class="mb-me__panel mb-me__panel--payment" id="payment" aria-labelledby="mb-me-payment">
    <h2 class="mb-me__h" id="mb-me-payment"><span class="mb-me__hicon" aria-hidden="true"><?= icon('banknote') ?></span> <?= e($c('dashboard.payment_title')) ?></h2>
    <?php if ($cleared): ?>
        <p class="mb-me__ok"><?= icon('check-circle') ?> <span><?= e($c('dashboard.payment_cleared')) ?></span></p>
    <?php else: ?>
        <?php if ($balance > 0): ?>
            <p class="mb-me__due"><strong><?= e(str_replace('{amount}', MembershipService::formatMoney($balance, $cur), $c('dashboard.payment_due'))) ?></strong></p>
        <?php endif; ?>
        <h3 class="mb-me__h3"><?= e($c('dashboard.instructions_title')) ?></h3>
        <div class="mb-me__prose"><?= $instructions !== '' ? nl2p($instructions) : nl2p($c('dashboard.payment_none')) ?></div>
    <?php endif; ?>
</section>
<?php $panelPayment = (string) ob_get_clean();

ob_start(); ?>
<section class="mb-me__panel mb-me__panel--proof<?= $proofStatus === 'rejected' ? ' is-attention' : '' ?>" id="proof" aria-labelledby="mb-me-proof">
    <h2 class="mb-me__h" id="mb-me-proof"><span class="mb-me__hicon" aria-hidden="true"><?= icon('file-text') ?></span> <?= e($c('dashboard.proof_title')) ?></h2>
    <?php if ($hasProof): ?>
        <?php $pst = $proofStatus === 'none' ? 'received' : $proofStatus; ?>
        <p class="mb-me__chip mb-me__chip--<?= e($pst) ?>" role="status">
            <?= icon($pst === 'verified' ? 'check-circle' : ($pst === 'rejected' ? 'alert-circle' : 'clock')) ?>
            <span><?= e($c('dashboard.proof_' . $pst)) ?></span>
        </p>
        <p class="mb-me__meta">
            <?php if (!empty($member['proof_uploaded_at'])): ?><?= e(str_replace('{date}', $when($member['proof_uploaded_at']), $c('dashboard.proof_uploaded_on'))) ?> &middot; <?php endif; ?>
            <a href="<?= e($urls['proof']) ?>" target="_blank" rel="noopener noreferrer"><?= e($c('dashboard.proof_view')) ?></a>
        </p>
    <?php else: ?>
        <p class="mb-me__empty"><?= e($c('dashboard.proof_none')) ?></p>
    <?php endif; ?>
    <?php if ($canProof): ?>
        <form method="post" action="<?= e($urls['proofPost']) ?>" enctype="multipart/form-data" class="mb-me__form" novalidate data-me-upload>
            <?= csrf_field() ?>
            <?= partial('web/membership/_upload', [
                'kind' => 'proof', 'name' => 'proof', 'id' => 'me-proof', 'required' => true, 'hideLabel' => true,
                'label' => $c('uploads.proof_label'),
                'pick' => $c($hasProof ? 'dashboard.proof_replace_label' : 'dashboard.proof_upload_label'), 'accept' => 'image/jpeg,image/png,image/webp,application/pdf',
                'help' => $c('uploads.proof_help'), 'remove' => $remove, 'chosen' => $chosen, 'previewAlt' => $c('uploads.preview_alt'),
                'msgs' => $uploadMsgs('proof'), 'maxBytes' => MemberFiles::PROOF_MAX_BYTES,
                'current' => $hasProof && !$proofIsPdf ? $urls['proof'] : '', 'currentKind' => 'image', 'currentAlt' => '',
            ]) ?>
            <div class="form-actions"><button class="btn btn--cta btn--caps" type="submit"><?= e($c('dashboard.proof_submit')) ?></button></div>
        </form>
    <?php elseif ($proofStatus === 'verified'): ?>
        <p class="mb-me__note"><?= e($c('dashboard.proof_closed_note')) ?></p>
    <?php else: ?>
        <p class="mb-me__note"><?= e($c('uploads.err_proof_closed')) ?></p>
    <?php endif; ?>
</section>
<?php $panelProof = (string) ob_get_clean();

ob_start(); ?>
<section class="mb-me__panel mb-me__panel--photo<?= $photoWanted ? ' is-attention' : '' ?>" id="photo" aria-labelledby="mb-me-photo">
    <h2 class="mb-me__h" id="mb-me-photo"><span class="mb-me__hicon" aria-hidden="true"><?= icon('camera') ?></span> <?= e($c('dashboard.photo_title')) ?></h2>
    <p class="mb-me__lead"><?= e($hasPhoto ? $c('dashboard.photo_tips') : $c('dashboard.photo_none')) ?></p>
    <?php if ($canPhoto): ?>
        <form method="post" action="<?= e($urls['photoPost']) ?>" enctype="multipart/form-data" class="mb-me__form" novalidate data-me-upload>
            <?= csrf_field() ?>
            <?= partial('web/membership/_upload', [
                'kind' => 'photo', 'name' => 'photo', 'id' => 'me-photo', 'required' => true, 'hideLabel' => true,
                'label' => $c('uploads.photo_label'),
                'pick' => $c($hasPhoto ? 'dashboard.photo_replace_label' : 'dashboard.photo_upload_label'), 'accept' => 'image/jpeg,image/png,image/webp',
                'help' => $c('uploads.photo_help'), 'camera' => $c('uploads.camera_hint'), 'remove' => $remove, 'chosen' => $chosen,
                'previewAlt' => $c('uploads.preview_alt'), 'msgs' => $uploadMsgs('photo'), 'minSide' => MemberFiles::PHOTO_MIN_SIDE, 'maxBytes' => MemberFiles::PHOTO_MAX_BYTES,
                'current' => $hasPhoto ? $urls['photo'] : '', 'currentKind' => 'image', 'currentAlt' => $c('dashboard.photo_current_alt'), 'initials' => $initials,
            ]) ?>
            <?php // the consent of the registration form is not carried over: a new photo is a new picture of a person, so the box is ticked again ?>
            <div class="field mb-me__consent">
                <label class="check" for="me-photo-consent">
                    <input type="checkbox" id="me-photo-consent" name="photo_consent" value="1" required data-msg-consent="<?= e($c('uploads.err_photo_consent')) ?>">
                    <span><?= e($c('uploads.photo_consent_label')) ?> <span class="field__req" aria-hidden="true">*</span></span>
                </label>
                <?php if (trim($c('uploads.photo_consent_help')) !== ''): ?><p class="field__help"><?= e($c('uploads.photo_consent_help')) ?></p><?php endif; ?>
            </div>
            <div class="form-actions"><button class="btn btn--cta btn--caps" type="submit"><?= e($c('dashboard.photo_submit')) ?></button></div>
        </form>
        <?php if ($issued): ?><p class="mb-me__note"><?= icon('info', 'icon--sm') ?> <span><?= e($c('dashboard.photo_reissue_note')) ?></span></p><?php endif; ?>
    <?php elseif ($hasPhoto): ?>
        <img class="mb-me__photo" src="<?= e($urls['photo']) ?>" alt="<?= e($c('dashboard.photo_current_alt')) ?>" width="160" height="160" loading="lazy" decoding="async">
    <?php endif; ?>
</section>
<?php $panelPhoto = (string) ob_get_clean();

// what the person can still DO exists only while the application is open (pending or approved); a rejected, cancelled, suspended or expired membership shows the progress,
// the card (if there is one), the details and the help
$open = in_array((string) $member['status'], ['pending', 'approved'], true);
$closedApplication = in_array((string) $member['status'], ['rejected', 'cancelled'], true);
$photoFirst = $photoWanted;                                           // a missing photo is what holds the card back: ask for it first
$firstColumn = !$open ? [] : ($photoFirst ? [$panelPhoto, $panelPayment, $panelProof] : [$panelPayment, $panelProof, $panelPhoto]);
?>
<?= partial('partials/page-hero', [
    'title' => $c('dashboard.title'),
    'lead'  => str_replace('{first_name}', $first, $c('dashboard.lead')),
    'sheet' => 'tint',
]) ?>

<section class="section section--tint mb-me" aria-label="<?= e(strip_highlight($c('dashboard.title'))) ?>">
    <div class="container">
        <div class="mb-me__flash" id="mb-status" tabindex="-1" data-me-status><?= partial('partials/flash') ?></div>
        <p class="mb-me__private"><?= icon('shield', 'icon--sm') ?> <span><?= e($c('dashboard.private_note')) ?></span></p>

        <?php /* ---------------------------------------------------------------------------------------- progress */ ?>
        <section class="mb-me__panel mb-me__panel--progress" aria-labelledby="mb-me-progress">
            <div class="mb-me__statehead">
                <h2 class="mb-me__h" id="mb-me-progress"><?= e($c('dashboard.progress_title')) ?></h2>
                <p class="mb-me__state mb-me__state--<?= e($stateTone) ?>" role="status"><?= icon($stateIcons[$state] ?? 'info') ?> <span><?= e($c('dashboard.state_' . $state)) ?></span></p>
            </div>
            <?php if (!$closedApplication): ?>
            <ol class="mb-me__steps">
                <?php foreach ($steps as $i => $s): $st = $timeline[$s]; ?>
                    <li class="mb-me__step mb-me__step--<?= e($st) ?>"<?= $st === 'current' ? ' aria-current="step"' : '' ?>>
                        <span class="mb-me__dot" aria-hidden="true"><?= $st === 'done' ? icon('check') : (string) ($i + 1) ?></span>
                        <span class="mb-me__steptext"><?= e($c('dashboard.step_' . $s)) ?> <span class="visually-hidden">(<?= e($c('dashboard.step_' . ($st === 'done' ? 'done' : ($st === 'current' ? 'current' : 'todo')))) ?>)</span></span>
                    </li>
                <?php endforeach; ?>
            </ol>
            <?php endif; ?>
            <dl class="mb-me__facts">
                <div><dt><?= e($c('dashboard.ref_label')) ?></dt><dd class="mb-me__ref"><?= e($member['application_ref']) ?></dd></div>
                <?php if (!empty($member['membership_number'])): ?><div><dt><?= e($c('dashboard.number_label')) ?></dt><dd class="mb-me__ref"><?= e($member['membership_number']) ?></dd></div><?php endif; ?>
                <div><dt><?= e($c('dashboard.status_label')) ?></dt><dd><?= e($c('dashboard.status_value_' . (string) $member['status'])) ?></dd></div>
                <div><dt><?= e($c('dashboard.type_label')) ?></dt><dd><?= e($member['type_name']) ?></dd></div>
                <div><dt><?= e($c('dashboard.fee_label')) ?></dt><dd><?= e($fee) ?><?= $joining !== '' ? '<span class="mb-me__sub">' . e($joining) . '</span>' : '' ?></dd></div>
                <?php if ($validUntil !== ''): ?><div><dt><?= e($c('dashboard.valid_until_label')) ?></dt><dd><?= e($validUntil) ?></dd></div><?php endif; ?>
            </dl>
            <?php if ($renewal !== null): ?>
                <p class="mb-me__renewal mb-me__renewal--<?= e($renewal['kind']) ?>" role="note"><?= icon('refresh') ?>
                    <span><strong><?= e($c('dashboard.renewal_title')) ?>:</strong>
                    <?= e(str_replace('{date}', $when($renewal['date']), $c($renewal['kind'] === 'expired' ? 'dashboard.renewal_expired' : 'dashboard.renewal_soon'))) ?></span></p>
            <?php endif; ?>
        </section>

        <?php /* ---------------------------------------------------------------------------------------- the card */ ?>
        <?php if ($issued || $revoked || $open): ?>
        <section class="mb-me__panel mb-me__panel--card<?= $issued ? ' is-issued' : '' ?>" id="card" aria-labelledby="mb-me-card">
            <?php if ($issued): ?>
                <div class="mb-me__cardgrid">
                    <div class="mb-me__cardvisual">
                        <?php if ($hasPng): ?>
                            <img class="mb-me__cardimg" src="<?= e($urls['png']) ?>" alt="<?= e($c('dashboard.card_preview_alt')) ?>" width="720" height="414" decoding="async">
                        <?php else: ?>
                            <?php // no picture library on this server: a small pass with the figures that matter (the PDF is the real card) ?>
                            <div class="mb-pass" role="img" aria-label="<?= e($c('dashboard.card_preview_alt')) ?>">
                                <span class="mb-pass__bar" aria-hidden="true"></span>
                                <p class="mb-pass__label"><?= e($c('dashboard.number_label')) ?></p>
                                <p class="mb-pass__number"><?= e((string) $member['membership_number']) ?></p>
                                <p class="mb-pass__pill mb-pass__pill--<?= e($card['live']) ?>"><?= e($liveLabel) ?></p>
                                <?php if ($validUntil !== ''): ?>
                                    <p class="mb-pass__label"><?= e($c('dashboard.valid_until_label')) ?></p>
                                    <p class="mb-pass__date"><?= e($validUntil) ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="mb-me__cardinfo">
                        <h2 class="mb-me__h" id="mb-me-card"><span class="mb-me__hicon" aria-hidden="true"><?= icon('credit-card') ?></span> <?= e($c('dashboard.card_title')) ?></h2>
                        <p class="mb-me__note"><?= e(str_replace(['{n}', '{date}'], [(string) $card['version'], $when($card['issued_at'])], $c('dashboard.card_version'))) ?></p>
                        <?php if ($card['needs_reissue']): ?><p class="mb-me__note mb-me__note--warn"><?= icon('info', 'icon--sm') ?> <span><?= e($c('dashboard.card_reissue_pending')) ?></span></p><?php endif; ?>
                        <div class="mb-me__actions">
                            <a class="btn btn--cta btn--caps" href="<?= e($urls['pdf']) ?>"><?= icon('download', 'icon--sm') ?> <?= e($c('dashboard.card_download')) ?></a>
                            <?php if ($hasPng): ?><a class="btn btn--outline-light btn--caps" href="<?= e($urls['png']) ?>" download="<?= e(App\Services\Membership\CardService::filename($member, 'png')) ?>"><?= icon('image', 'icon--sm') ?> <?= e($c('dashboard.card_download_png')) ?></a><?php endif; ?>
                        </div>
                        <form method="post" action="<?= e($urls['resend']) ?>" class="mb-me__resend"><?= csrf_field() ?>
                            <button class="btn btn--outline-light" type="submit"><?= icon('mail', 'icon--sm') ?> <?= e($c('dashboard.card_resend')) ?></button>
                        </form>
                    </div>
                </div>
            <?php elseif ($revoked): ?>
                <h2 class="mb-me__h" id="mb-me-card"><span class="mb-me__hicon" aria-hidden="true"><?= icon('credit-card') ?></span> <?= e($c('dashboard.card_title')) ?></h2>
                <p class="mb-me__note mb-me__note--warn"><?= icon('alert-circle', 'icon--sm') ?> <span><?= e($c('dashboard.card_revoked')) ?></span></p>
            <?php else: ?>
                <h2 class="mb-me__h" id="mb-me-card"><span class="mb-me__hicon" aria-hidden="true"><?= icon('credit-card') ?></span> <?= e($c('dashboard.card_title')) ?></h2>
                <p class="mb-me__lead"><?= e($c('dashboard.card_not_ready')) ?></p>
                <?php $waiting = []; foreach ($card['waiting'] as $w) { $k = ['not_approved' => 'card_waiting_approval', 'unpaid' => 'card_waiting_payment', 'photo_missing' => 'card_waiting_photo'][$w] ?? null; if ($k) { $waiting[] = $k; } } ?>
                <?php if ($waiting): ?>
                    <ul class="mb-me__waiting">
                        <?php foreach ($waiting as $k): ?><li><?= icon('clock', 'icon--sm') ?> <span><?= e($c('dashboard.' . $k)) ?></span></li><?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            <?php endif; ?>
        </section>
        <?php endif; ?>

        <div class="mb-me__grid<?= $firstColumn === [] ? ' mb-me__grid--single' : '' ?>">
            <?php if ($firstColumn !== []): ?>
            <div class="mb-me__main">
                <?= implode("\n", $firstColumn) ?>
            </div>
            <?php endif; ?>

            <aside class="mb-me__side" aria-label="<?= e($c('dashboard.details_title')) ?>">
                <section class="mb-me__panel" aria-labelledby="mb-me-details">
                    <h2 class="mb-me__h" id="mb-me-details"><span class="mb-me__hicon" aria-hidden="true"><?= icon('user') ?></span> <?= e($c('dashboard.details_title')) ?></h2>
                    <dl class="mb-me__facts mb-me__facts--stack">
                        <div><dt><?= e($c('dashboard.detail_name')) ?></dt><dd><?= e(\App\Services\Membership\Card\CardSettings::cleanLine((string) $member['full_name'], 150)) ?></dd></div>
                        <div><dt><?= e($c('dashboard.detail_email')) ?></dt><dd><?= e($member['email']) ?></dd></div>
                        <div><dt><?= e($c('dashboard.detail_phone')) ?></dt><dd><?= e($member['phone']) ?></dd></div>
                        <div><dt><?= e($c('dashboard.detail_location')) ?></dt><dd><?= e(CardData::location('{city}, {district}, {country}', (string) $member['city'], (string) $member['region'], (string) $member['country'])) ?></dd></div>
                    </dl>
                    <p class="mb-me__note"><?= e($c('dashboard.details_note')) ?></p>
                </section>

                <section class="mb-me__panel" aria-labelledby="mb-me-help">
                    <h2 class="mb-me__h" id="mb-me-help"><span class="mb-me__hicon" aria-hidden="true"><?= icon('help-circle') ?></span> <?= e($c('dashboard.help_title')) ?></h2>
                    <p><?= e(str_replace('{contact_email}', $contact, $c('dashboard.help_text'))) ?></p>
                </section>
            </aside>
        </div>
    </div>
</section>
