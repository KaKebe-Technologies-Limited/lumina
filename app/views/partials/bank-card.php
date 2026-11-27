<?php
/**
 * The foundation's BANK ACCOUNT as one clean, reusable card (account name, number, bank, branch), with a copy button on every row, the reference to quote and the
 * "confirm the account name / share your proof of payment" note. Used on the Donate page and thank-you page, the project gift pages and their thank-you page, the shop order page
 * and the gateway pages of a gift that did not go through. The data comes from App\Services\Payments\OfficialAccount (settings official_bank_*); styles: scss/components/_bank-card.scss;
 * the copy buttons: assets/js/bank-card.js (loaded once per page by this partial; the buttons stay hidden without JavaScript, the values are plain selectable text).
 *
 * It prints NOTHING while the site-wide switch Admin > Payment options > "Bank and manual mobile-money details" is off (see OfficialAccount::visible()).
 *
 * Vars (all optional):
 *   $reference   string  what the payer must quote on the transfer (a row with its own copy button and a hint)
 *   $amount      string  an already formatted amount to send ("UGX 6,300,000.00"), shown as a row without a button
 *   $title       string  heading (default "Pay by bank transfer")        $lead  string  one sentence under the heading
 *   $tag         string  heading tag (default h3)                         $id    string  makes the ids unique when the card is printed twice (default "bank")
 *   $extra       bool    print the extra free-text instructions (default true)
 *   $compact     bool    leave out the "send your proof of payment to ..." line (default false)
 *   $rows        array   [['label'=>..,'value'=>..,'copy'=>..(optional),'field'=>..(optional)], ...] to show a DIFFERENT account (the shop's own account); then $note is used too
 *   $note        string  the note under the details when $rows is given (default: the official note)
 *   $copyLabel / $copiedLabel  strings (default "Copy" / "Copied")
 */

use App\Services\Payments\OfficialAccount as OA;
use App\Services\Payments\PaymentOptions;

$ownRows = isset($rows) && is_array($rows);
$show = $ownRows ? (PaymentOptions::manual() && $rows !== []) : OA::visible();
if (!$show) {
    return;
}
$list = [];
foreach ($ownRows ? $rows : OA::rows() as $r) {
    $value = trim((string) ($r['value'] ?? ''));
    if ($value === '') {
        continue;
    }
    $field = (string) ($r['field'] ?? '');
    $label = (string) ($r['label'] ?? '');
    $isNumber = $field === 'number' || ($field === '' && preg_match('/account\s*number/i', $label) === 1);
    $list[] = ['label' => $label, 'value' => $value, 'copy' => (string) ($r['copy'] ?? ($isNumber ? preg_replace('/\s+/', '', $value) : $value)), 'key' => $isNumber];
}
if ($list === []) {
    return;
}
$id = preg_replace('/[^a-z0-9_-]/i', '-', (string) ($id ?? 'bank'));
$title = trim((string) ($title ?? '')) !== '' ? (string) $title : 'Pay by bank transfer';
$tag = in_array(($tag ?? 'h3'), ['h2', 'h3', 'h4'], true) ? (string) ($tag ?? 'h3') : 'h3';
$copyLabel = (string) ($copyLabel ?? 'Copy');
$copiedLabel = (string) ($copiedLabel ?? 'Copied');
$reference = trim((string) ($reference ?? ''));
$amount = trim((string) ($amount ?? ''));
$extraText = ($extra ?? true) ? OA::extra() : '';
$noteText = $ownRows ? trim((string) ($note ?? '')) : OA::note();
$phone = trim((string) setting('phone_1', ''));
$wa = whatsapp_url();
$mail = trim((string) setting('email_primary', ''));
$showProof = !($compact ?? false) && ($phone !== '' || $wa !== '' || $mail !== '');

if (!str_contains($this->section('scripts'), 'js/bank-card.js')) {
    $this->start('scripts'); ?><script src="<?= e(asset('js/bank-card.js')) ?>" defer></script><?php $this->end();
}
?>
<section class="bankcard" id="bankcard-<?= e($id) ?>" aria-labelledby="bankcard-<?= e($id) ?>-title" data-bankcard>
    <header class="bankcard__head">
        <span class="bankcard__icon" aria-hidden="true"><?= icon('banknote', 'icon--lg') ?></span>
        <div>
            <<?= $tag ?> class="bankcard__title" id="bankcard-<?= e($id) ?>-title"><?= e($title) ?></<?= $tag ?>>
            <?php if (trim((string) ($lead ?? '')) !== ''): ?><p class="bankcard__lead"><?= e((string) $lead) ?></p><?php endif; ?>
        </div>
    </header>
    <dl class="bankcard__list">
        <?php foreach ($list as $r): ?>
        <div class="bankcard__row<?= $r['key'] ? ' bankcard__row--key' : '' ?>">
            <dt><?= e($r['label']) ?></dt>
            <dd>
                <span class="bankcard__value" translate="no"><?= e($r['value']) ?></span>
                <button class="bankcard__copy" type="button" data-bankcard-copy="<?= e($r['copy']) ?>" data-copied="<?= e($copiedLabel) ?>" aria-label="<?= e($copyLabel . ' ' . strtolower($r['label'])) ?>" hidden><?= icon('copy', 'icon--sm') ?> <span><?= e($copyLabel) ?></span></button>
            </dd>
        </div>
        <?php endforeach; ?>
        <?php if ($reference !== ''): ?>
        <div class="bankcard__row bankcard__row--ref">
            <dt>Payment reference</dt>
            <dd>
                <span class="bankcard__value" translate="no"><?= e($reference) ?></span>
                <button class="bankcard__copy" type="button" data-bankcard-copy="<?= e($reference) ?>" data-copied="<?= e($copiedLabel) ?>" aria-label="<?= e($copyLabel) ?> payment reference" hidden><?= icon('copy', 'icon--sm') ?> <span><?= e($copyLabel) ?></span></button>
            </dd>
            <dd class="bankcard__hint">Write this on your transfer so we can match it to you.</dd>
        </div>
        <?php endif; ?>
        <?php if ($amount !== ''): ?>
        <div class="bankcard__row">
            <dt>Amount to send</dt>
            <dd><span class="bankcard__value" translate="no"><?= e($amount) ?></span></dd>
        </div>
        <?php endif; ?>
    </dl>
    <?php if ($extraText !== ''): ?><p class="bankcard__extra"><?= nl2br(e($extraText), false) ?></p><?php endif; ?>
    <?php if ($noteText !== '' || $showProof): ?>
    <p class="bankcard__note" role="note">
        <?= icon('info', 'icon--sm') ?>
        <span><?= e($noteText) ?><?php if ($showProof): ?><?= $noteText !== '' ? ' ' : '' ?>Send the proof of payment to
            <?php $links = []; if ($wa !== '') { $links[] = '<a href="' . e($wa) . '" rel="noopener">WhatsApp</a>'; } if ($phone !== '') { $links[] = '<a href="' . e(phone_href($phone)) . '">' . e($phone) . '</a>'; } if ($mail !== '') { $links[] = '<a href="mailto:' . e($mail) . '">' . e($mail) . '</a>'; } echo implode(', ', $links); ?>.<?php endif; ?></span>
    </p>
    <?php endif; ?>
    <p class="visually-hidden" role="status" aria-live="polite" data-bankcard-live></p>
</section>
