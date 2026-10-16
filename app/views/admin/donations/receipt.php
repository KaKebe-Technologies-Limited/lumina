<?php
/**
 * Printable donation receipt (standalone page, no admin chrome). Vars: $d (donation row, status received, receipt_no set), $methods.
 * Organisation details come from Settings; the page prints cleanly on A4 / Letter (see scss/admin/_inbox.scss, @media print).
 */
use App\Services\Inbox\Support;

$org = (string) setting('site_name', '');
$reg = trim((string) setting('reg_number', ''));
$logo = logo_url(72);
$contact = array_values(array_filter([
    trim((string) setting('address', '')),
    trim((string) setting('city_country', '')),
    trim((string) setting('email_primary', '')),
    trim((string) setting('phone_1', '')),
], static fn(string $s): bool => $s !== ''));
$donor = trim((string) $d['donor_name']) !== '' ? $d['donor_name'] : 'Donor';
$received = $d['received_at'] ?: $d['created_at'];
$method = $methods[$d['method']] ?? label_from((string) $d['method']);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Receipt <?= e($d['receipt_no']) ?> | <?= e($org) ?></title>
    <meta name="robots" content="noindex,nofollow">
    <link rel="icon" href="<?= e(img_url((string) setting('favicon_path', 'assets/img/brand/favicon-32.png'))) ?>" sizes="32x32">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,700&amp;family=Poppins:wght@500;600;700&amp;display=swap">
    <link rel="stylesheet" href="<?= e(asset('css/admin.min.css')) ?>">
    <script src="<?= e(asset('js/boot.js')) ?>"></script>
</head>
<body class="admin admin-receipt">
<div class="receipt-bar no-print">
    <a class="btn btn--ghost" href="<?= e(url('admin/donations/' . (int) $d['id'] . '/edit')) ?>"><?= icon('arrow-left', 'icon--sm') ?> Back to the donation</a>
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

    <h2 class="receipt__title">Donation receipt</h2>

    <dl class="receipt__grid">
        <dt>Receipt number</dt><dd><strong><?= e($d['receipt_no']) ?></strong></dd>
        <dt>Date received</dt><dd><?= e(format_date($received, 'j F Y')) ?></dd>
        <dt>Received from</dt><dd><?= e($donor) ?><?php if (!empty($d['donor_email'])): ?><br><span class="receipt__muted"><?= e($d['donor_email']) ?></span><?php endif; ?></dd>
        <dt>Payment method</dt><dd><?= e($method) ?></dd>
        <?php if (!empty($d['reference'])): ?><dt>Reference</dt><dd><?= e($d['reference']) ?></dd><?php endif; ?>
    </dl>

    <p class="receipt__amount"><span class="receipt__amount-label">Amount received</span> <strong><?= e(Support::money($d['amount'], (string) $d['currency'])) ?></strong></p>

    <p class="receipt__thanks">Thank you for your support.</p>

    <div class="receipt__sign">
        <div class="receipt__sign-line"></div>
        <div class="receipt__sign-label">Authorised signature</div>
    </div>
</main>
<script src="<?= e(asset('js/pages/admin-inbox.js')) ?>" defer></script>
</body>
</html>
