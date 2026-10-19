<?php
/**
 * Print view of ONE submission: a standalone page without the admin chrome (layout null), like the donation receipt.
 * Vars: $form, $sub, $ref, $labels, $withNotes, $backUrl. PERSONAL DATA: staff only (route needs forms.submissions); the page is noindex and never cached.
 */
$org = (string) setting('site_name', '');
$filesBy = [];
foreach ($sub['files'] as $file) {
    $filesBy[(string) $file['field_id']][] = $file;
}
$groups = [];
foreach ($sub['entries'] as $entry) {
    $groups[(string) ($entry['page'] ?? '')][] = $entry;
}
$fid = (int) $form['id'];
$notesUrl = url('admin/forms/' . $fid . '/submissions/' . (int) $sub['id'] . '/print', $withNotes ? [] : ['notes' => '1']);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($ref) ?> | <?= e((string) $form['title']) ?> | <?= e($org) ?></title>
    <meta name="robots" content="noindex,nofollow">
    <link rel="icon" href="<?= e(img_url((string) setting('favicon_path', 'assets/img/brand/favicon-32.png'))) ?>" sizes="32x32">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,700&amp;family=Poppins:wght@500;600;700&amp;display=swap">
    <link rel="stylesheet" href="<?= e(asset('css/admin.min.css')) ?>">
    <script src="<?= e(asset('js/boot.js')) ?>"></script>
</head>
<body class="admin fs-printpage">
<div class="fs-printbar no-print">
    <a class="btn btn--ghost" href="<?= e($backUrl) ?>"><?= icon('arrow-left', 'icon--sm') ?> Back to the answer</a>
    <span class="fs-printbar__spacer"></span>
    <?php if (trim((string) ($sub['notes'] ?? '')) !== ''): ?>
        <a class="btn btn--outline" href="<?= e($notesUrl) ?>"><?= $withNotes ? 'Leave out the internal notes' : 'Include the internal notes' ?></a>
    <?php endif; ?>
    <button class="btn btn--primary" type="button" data-fs-print><?= icon('file-text', 'icon--sm') ?> Print or save as PDF</button>
</div>

<main class="fs-sheet" id="main">
    <header class="fs-sheet__head">
        <p class="fs-sheet__org"><?= e($org) ?></p>
        <h1 class="fs-sheet__title"><?= e((string) $form['title']) ?></h1>
        <p class="fs-sheet__meta">
            <strong><?= e($ref) ?></strong> &middot; Received <?= e(format_datetime($sub['created_at'], 'j F Y, H:i')) ?> (East Africa Time) &middot; Status: <?= e($labels[$sub['status']] ?? $sub['status']) ?>
        </p>
        <?php if (!empty($sub['context_label'])): ?><p class="fs-sheet__meta">Applying for: <strong><?= e($sub['context_label']) ?></strong></p><?php endif; ?>
    </header>

    <?php foreach ($groups as $pageTitle => $entries): ?>
        <?php if (count($groups) > 1 && $pageTitle !== ''): ?><h2 class="fs-sheet__group"><?= e($pageTitle) ?></h2><?php endif; ?>
        <dl class="fs-sheet__list">
            <?php foreach ($entries as $entry): ?>
                <div class="fs-sheet__row">
                    <dt><?= e($entry['label'] ?? '') ?></dt>
                    <dd><?= partial('admin/forms/submissions/_answer', ['e' => $entry, 'files' => $filesBy[(string) ($entry['id'] ?? '')] ?? [], 'base' => '', 'sid' => (int) $sub['id'], 'print' => true]) ?></dd>
                </div>
            <?php endforeach; ?>
            <?php if ($pageTitle === array_key_last($groups) && !empty($sub['consent_at']) && !array_filter($sub['entries'], static fn(array $x): bool => ($x['type'] ?? '') === 'consent' && !empty($x['at']))): ?>
                <div class="fs-sheet__row"><dt>Consent given</dt><dd><?= e(format_datetime($sub['consent_at'])) ?></dd></div>
            <?php endif; ?>
        </dl>
    <?php endforeach; ?>

    <?php if ($withNotes && trim((string) ($sub['notes'] ?? '')) !== ''): ?>
        <h2 class="fs-sheet__group">Internal notes</h2>
        <p class="fs-text"><?= e((string) $sub['notes']) ?></p>
    <?php endif; ?>

    <p class="fs-sheet__foot">Confidential: contains personal information. Printed from the <?= e($org) ?> admin area.</p>
</main>
<script src="<?= e(asset('js/admin-form-submissions.js')) ?>" defer></script>
</body>
</html>
