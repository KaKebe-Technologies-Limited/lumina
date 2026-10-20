<?php
/**
 * Member screen: MEMBERSHIP CARD panel (status, preview, issue / re-send / re-issue / revoke). OWNER: STAFF agent. Contract: docs/MEMBERSHIP_CARD.md (sections 6, 7, 10).
 * Vars: $member (row), $card (CardService::summary), $checks (what a card needs: approved, balance, photo), $gd (bool: the PNG preview needs the GD extension).
 * Rules live in CardService (idempotent, row-locked, audited by ids): these buttons only post to the routes of app/routes/admin_membership_card.php.
 * The PDF is the authoritative card; the picture is a preview. Both are streamed to staff only (members.manage).
 */
use App\Services\Membership\Card\CardData;
use App\Services\Membership\CardService;
use App\Services\Membership\MembershipService as MS;

$id = (int) $member['id'];
$erased = !empty($member['anonymised_at']);
$state = (string) $card['state'];
$issued = $state === 'issued';
$when = static fn(?string $d): string => $d ? format_datetime($d, 'j M Y, H:i') : '';
$liveText = ['active' => 'Valid', 'expired' => 'Expired', 'suspended' => 'Suspended', 'revoked' => 'Not valid'];
$liveTone = ['active' => 'success', 'expired' => 'neutral', 'suspended' => 'danger', 'revoked' => 'danger'];
$reasonText = [
    'photo'       => 'The member replaced the photo after the card was issued.',
    'photo_staff' => 'The photo was changed after the card was issued.',
    'details'     => 'The name, place or fee was changed after the card was issued.',
    'payment'     => 'The payment was completed after the card was issued, so the card still says the payment is pending.',
    'renewal'     => 'The membership was renewed after the card was issued, so the date on the card is out of date.',
];
$stale = $issued && ($card['needs_reissue'] || $card['renewal_due']);
$staleReason = $reasonText[(string) ($card['reissue_reason'] ?: ($card['renewal_due'] ? 'renewal' : ''))] ?? 'The card may be out of date.';
$blockers = $erased ? ['erased'] : CardService::eligibility($member, true)['reasons'];      // the reasons that stop even a staff "Issue card now"
$trigger = ['paid_approved' => 'when the member is approved and the balance is paid or waived', 'approved' => 'as soon as the member is approved', 'manual' => 'only when staff press "Issue card now" (manual mode)'][$card['trigger']] ?? '';
$badge = match (true) {
    $erased => ['Erased', 'neutral'],
    $issued && $stale => ['Update due', 'warning'],
    $issued => ['Issued, version ' . (int) $card['version'], 'success'],
    $state === 'revoked' => ['Revoked', 'danger'],
    default => ['Not issued', 'neutral'],
};
$validOn = $issued ? ($card['valid_until'] ? CardData::date(MS::lastValidDay((string) $card['valid_until'])) : 'No expiry') : '';
$pngUrl = url('admin/members/' . $id . '/card.png', ['v' => (int) $card['version'] . '-' . substr(md5((string) ($card['issued_at'] ?? '')), 0, 6)]);
$pdfUrl = url('admin/members/' . $id . '/card.pdf');
?>
<section class="card mcardp" id="card" aria-labelledby="card-title">
    <div class="card__header">
        <h2 class="card__title" id="card-title">Membership card</h2>
        <span class="badge badge--<?= e($badge[1]) ?> badge--dot"><?= e($badge[0]) ?></span>
    </div>
    <div class="card__body mfiles">
        <?php if ($erased): ?>
            <p class="text-muted">The personal data of this member was erased, so <?= $state === 'none' ? 'no card can be issued.' : 'the card was revoked and its QR code no longer works.' ?></p>
        <?php else: ?>
            <div class="mcardp__grid">
                <div class="mcardp__preview">
                    <?php if ($issued): ?>
                        <?php if ($gd): ?>
                            <img class="mcardp__img" src="<?= e($pngUrl) ?>" alt="Preview of the membership card of <?= e($member['full_name']) ?>, number <?= e($member['membership_number']) ?>" width="720" height="414" loading="lazy">
                        <?php else: ?>
                            <div class="mcardp__noimg">
                                <?= icon('credit-card', 'icon--xl') ?>
                                <p class="small text-muted">This server has no image library (GD), so there is no picture preview here. The PDF is the real card: view it below or download it.</p>
                            </div>
                        <?php endif; ?>
                        <details class="mcardp__pdf" data-card-pdf="<?= e(url('admin/members/' . $id . '/card.pdf', ['inline' => 1])) ?>"<?= $gd ? '' : ' open' ?> hidden>
                            <summary>Show the PDF card here</summary>
                            <div class="mcardp__frame" data-card-frame></div>
                            <p class="small text-muted">If the card does not appear in this browser, use "Download PDF".</p>
                        </details>
                    <?php else: ?>
                        <div class="mcardp__empty" aria-hidden="true">
                            <span class="mcardp__empty-bar"></span>
                            <?= icon('credit-card', 'icon--xl') ?>
                            <strong><?= $state === 'revoked' ? 'Card revoked' : 'No card yet' ?></strong>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="mcardp__side">
                    <?php if (!empty($card['unprintable'])): ?>
                        <p class="mfiles__next mfiles__next--warn"><?= icon('alert-triangle', 'icon--sm') ?> The name or place of this member contains letters the printed card cannot draw (for example Arabic, Cyrillic or Chinese). Those letters print as "?". Check the card, and if needed edit the name to a Latin spelling and re-issue it.</p>
                    <?php endif; ?>
                    <?php if ($issued): ?>
                        <dl class="mfiles__meta">
                            <div><dt>Card number</dt><dd class="members-ref"><?= e($member['membership_number']) ?></dd></div>
                            <div><dt>On the verification page</dt><dd><span class="badge badge--<?= e($liveTone[$card['live']] ?? 'neutral') ?> badge--dot"><?= e($liveText[$card['live']] ?? $card['live']) ?></span></dd></div>
                            <div><dt>Issued</dt><dd><?= e($when($card['issued_at'])) ?></dd></div>
                            <div><dt>Valid until, as printed</dt><dd><?= e($validOn) ?></dd></div>
                            <div><dt>Last e-mailed</dt><dd><?= $card['emailed_at'] ? e($when($card['emailed_at'])) . ($card['emailed'] ? '' : ' (an earlier version)') : '<span class="mcardp__warn">Not e-mailed yet</span>' ?></dd></div>
                            <div><dt>QR code leads to</dt><dd><a href="<?= e($card['verify_url']) ?>" target="_blank" rel="noopener">the verification page</a></dd></div>
                        </dl>
                        <?php if ($stale): ?>
                            <p class="mfiles__next mfiles__next--warn"><?= icon('alert-triangle', 'icon--sm') ?> <?= e($staleReason) ?> Choose "Re-issue (new version)" to update it.</p>
                        <?php endif; ?>
                    <?php elseif ($state === 'revoked'): ?>
                        <p class="mfiles__next mfiles__next--warn"><?= icon('alert-triangle', 'icon--sm') ?> This card was revoked<?= !empty($member['card_revoked_at']) ? ' on ' . e($when($member['card_revoked_at'])) : '' ?>: scanning its QR code shows "not valid". Issue a new card to replace it.</p>
                    <?php endif; ?>

                    <?php if (!$issued): ?>
                        <h3 class="mcardp__h">What a card needs</h3>
                        <ul class="mcheck">
                            <?php foreach ($checks as $c): ?>
                                <li class="mcheck__item <?= $c['ok'] ? 'is-ok' : ($c['optional'] ? 'is-optional' : 'is-todo') ?>">
                                    <span class="mcheck__icon" aria-hidden="true"><?= $c['ok'] ? icon('check', 'icon--sm') : ($c['optional'] ? icon('minus', 'icon--sm') : icon('clock', 'icon--sm')) ?></span>
                                    <span><strong><?= e($c['label']) ?></strong> <span class="text-muted"><?= e($c['detail']) ?></span></span>
                                    <span class="visually-hidden"><?= $c['ok'] ? ' (done)' : ($c['optional'] ? ' (optional)' : ' (still to do)') ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>

                    <div class="mfiles__actions">
                        <?php if (!$issued): ?>
                            <?php if ($blockers === []): ?>
                                <form method="post" action="<?= e(url('admin/members/' . $id . '/card/' . ($state === 'revoked' ? 'reissue' : 'issue'))) ?>"
                                      data-confirm="<?= e(($state === 'revoked' ? 'Issue a new card? ' : 'Issue the card now? ') . ($card['eligible'] ? '' : 'The balance is not cleared, but staff may issue a card early. ') . 'The member is e-mailed the card.') ?>"><?= csrf_field() ?>
                                    <button class="btn btn--primary btn--sm" type="submit"><?= icon('credit-card', 'icon--sm') ?> <?= $state === 'revoked' ? 'Issue a new card' : 'Issue card now' ?></button></form>
                            <?php else: ?>
                                <button class="btn btn--primary btn--sm" type="button" disabled aria-describedby="card-blocked"><?= icon('credit-card', 'icon--sm') ?> Issue card now</button>
                                <span class="mcardp__blocked small" id="card-blocked"><?= e(CardService::WAITING[$blockers[0]] ?? 'The card cannot be issued yet.') ?></span>
                            <?php endif; ?>
                        <?php else: ?>
                            <a class="btn btn--primary btn--sm" href="<?= e($pdfUrl) ?>"><?= icon('download', 'icon--sm') ?> Download PDF</a>
                            <form method="post" action="<?= e(url('admin/members/' . $id . '/card/resend')) ?>" data-confirm="E-mail the card to the member again?"><?= csrf_field() ?>
                                <button class="btn btn--outline btn--sm" type="submit"><?= icon('send', 'icon--sm') ?> Re-send card e-mail</button></form>
                            <form method="post" action="<?= e(url('admin/members/' . $id . '/card/reissue')) ?>" data-confirm="Re-issue the card as a new version from the member's current details and photo? The QR code on the old card stops verifying, and the member is e-mailed the new card."><?= csrf_field() ?>
                                <button class="btn <?= $stale ? 'btn--accent' : 'btn--outline' ?> btn--sm" type="submit"><?= icon('refresh', 'icon--sm') ?> Re-issue (new version)</button></form>
                        <?php endif; ?>
                    </div>

                    <?php if ($issued): ?>
                        <details class="mcardp__revoke">
                            <summary>Revoke this card</summary>
                            <form method="post" action="<?= e(url('admin/members/' . $id . '/card/revoke')) ?>" data-confirm="Revoke this card? Scanning its QR code will show NOT VALID. The membership itself is not changed.">
                                <?= csrf_field() ?>
                                <div class="field">
                                    <label class="field__label" for="revoke-reason">Reason (optional, kept in the activity log)</label>
                                    <input class="input" id="revoke-reason" type="text" name="reason" maxlength="120" autocomplete="off">
                                    <div class="field__help">Use this for a lost or misused card. Keep it short (for example "lost") and do not type names or contact details. To end the membership, change the status instead.</div>
                                </div>
                                <button class="btn btn--danger btn--sm" type="submit">Revoke the card</button>
                            </form>
                        </details>
                    <?php endif; ?>

                    <p class="field__help mcardp__rule">Cards are issued and e-mailed automatically <?= e($trigger) ?>.
                        <?php if (can('members.card')): ?><a href="<?= e(url('admin/membership-card')) ?>">Change the card design and rules</a>.<?php endif; ?>
                        <?= $issued ? 'Re-send e-mails the same version; a new version replaces it.' : '"Issue card now" skips the payment check only.' ?></p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>
