<?php
/**
 * Printable membership payment receipt (standalone page, no admin chrome). OWNER: ADMIN agent.
 * Vars: $payment (received, with receipt_number), $member, $summary (MembershipService::paymentSummary), $methods.
 * The look is shared with the donation receipt (scss/admin/_inbox.scss: body.admin-receipt); this file adds only the small account table
 * (scss/admin/_members.scss). The organisation details come from Settings. Prints cleanly on A4 / Letter.
 */
use App\Services\Membership\MembershipService as MS;

$org = (string) setting('site_name', '');
$reg = trim((string) setting('reg_number', ''));
$logo = logo_url(72);
$contact = array_values(array_filter([
    trim((string) setting('address', '')),
    trim((string) setting('city_country', '')),
    trim((string) setting('email_primary', '')),
    trim((string) setting('phone_1', '')),
], static fn(string $s): bool => $s !== ''));
$cur = (string) $payment['currency'];
$method = (string) $payment['method'] !== '' ? ($methods[$payment['method']] ?? MS::optionLabel('payment_methods', (string) $payment['method'])) : '';
$when = !empty($payment['paid_at']) ? $payment['paid_at'] : $payment['created_at'];
$purpose = MS::PURPOSE_LABELS[$payment['purpose']] ?? label_from((string) $payment['purpose']);
$sameCurrency = strtoupper($cur) === strtoupper((string) $summary['currency']);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Receipt <?= e($payment['receipt_number']) ?> | <?= e($org) ?></title>
    <meta name="robots" content="noindex,nofollow">
    <link rel="icon" href="<?= e(img_url((string) setting('favicon_path', 'assets/img/brand/favicon-32.png'))) ?>" sizes="32x32">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,700&amp;family=Poppins:wght@500;600;700&amp;display=swap">
    <link rel="stylesheet" href="<?= e(asset('css/admin.min.css')) ?>">
    <script src="<?= e(asset('js/boot.js')) ?>"></script>
</head>
<body class="admin admin-receipt admin-member-receipt">
<div class="receipt-bar no-print">
    <a class="btn btn--ghost" href="<?= e(url('admin/members/' . (int) $member['id'] . '#payments')) ?>"><?= icon('arrow-left', 'icon--sm') ?> Back to the member</a>
    <button class="btn btn--primary" type="button" data-print><?= icon('file-text', 'icon--sm') ?> Print or save as PDF</button>
</div>

<main class="receipt" id="main">
    <header class="receipt__head">
        <?php if ($logo !== ''): ?><img class="receipt__logo" src="<?= e($logo) ?>" alt="" width="72" height="72"><?php endif; ?>
        <div>
            <h1 class="receipt__org"><?= e($org) ?></h1>
            <?php if ($reg !== ''): ?><p class="receipt__line">Registration no. <?= e($reg) ?></p><?php endif; ?>
            <?php foreach ($contact as $line): ?><p class="receipt__line"><?= e($line) ?></p><?php endforeach; ?>
        </div>
    </header>

    <h2 class="receipt__title">Membership payment receipt</h2>

    <dl class="receipt__grid">
        <dt>Receipt number</dt><dd><strong><?= e($payment['receipt_number']) ?></strong></dd>
        <dt>Date received</dt><dd><?= e(format_date($when, 'j F Y')) ?></dd>
        <dt>Received from</dt><dd><?= e($member['full_name']) ?><br><span class="receipt__muted"><?= e($member['email']) ?></span></dd>
        <dt>Membership</dt>
        <dd><?= e($member['type_name']) ?>
            <br><span class="receipt__muted"><?= !empty($member['membership_number']) ? 'Membership no. ' . e($member['membership_number']) . ' - ' : '' ?>Application <?= e($member['application_ref']) ?></span></dd>
        <dt>Payment for</dt>
        <dd><?= e($purpose) ?><?php if (!empty($payment['term_start'])): ?>
            <br><span class="receipt__muted">Covers <?= e(format_date($payment['term_start'], 'j F Y')) ?><?= !empty($payment['term_end']) ? ' to ' . e(format_date($payment['term_end'], 'j F Y')) : '' ?></span><?php endif; ?></dd>
        <?php if ($method !== ''): ?><dt>Payment method</dt><dd><?= e($method) ?></dd><?php endif; ?>
        <?php if (!empty($payment['reference'])): ?><dt>Reference</dt><dd><?= e($payment['reference']) ?></dd><?php endif; ?>
    </dl>

    <p class="receipt__amount"><span class="receipt__amount-label">Amount received</span> <strong><?= e(MS::formatMoney($payment['amount'], $cur)) ?></strong></p>

    <?php if ($sameCurrency): ?>
        <table class="receipt__account">
            <caption>Membership account at the time of printing</caption>
            <tbody>
            <tr><th scope="row">Total due</th><td><?= e(MS::formatMoney($summary['charges'], $cur)) ?></td></tr>
            <tr><th scope="row">Received to date</th><td><?= e(MS::formatMoney($summary['paid'], $cur)) ?></td></tr>
            <?php if ($summary['waived'] > 0): ?><tr><th scope="row">Waived</th><td><?= e(MS::formatMoney($summary['waived'], $cur)) ?></td></tr><?php endif; ?>
            <tr class="is-total"><th scope="row"><?= $summary['balance'] < 0 ? 'Credit' : 'Balance' ?></th><td><?= e(MS::formatMoney(abs((float) $summary['balance']), $cur)) ?></td></tr>
            </tbody>
        </table>
    <?php endif; ?>

    <p class="receipt__thanks">Thank you.</p>

    <div class="receipt__sign">
        <div class="receipt__sign-line"></div>
        <div class="receipt__sign-label">Authorised signature</div>
    </div>
</main>
<script src="<?= e(asset('js/admin-members.js')) ?>" defer></script>
</body>
</html>
