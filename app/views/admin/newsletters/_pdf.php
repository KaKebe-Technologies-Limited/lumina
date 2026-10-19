<?php
/**
 * Newsletter editor: the PDF box. For a PDF issue (kind = pdf) it is THE content of the issue (open by default, first box); for a written issue it is
 * an optional "Download this issue as PDF" attachment (collapsed). OWNER: NEWSLETTER agent. Behaviour: assets/js/admin-newsletter.js ([data-pdf-box]),
 * which talks to POST / DELETE /admin/api/newsletters/{id}/pdf. The file is stored privately (never reachable by URL) and streamed by the website only
 * while the issue is visible, so unpublishing, deleting or swapping the PDF takes effect at once.
 * Vars: $kind ('online' | 'pdf'), $pdfLimits (max_human, server_human, setting_mb, can_settings, settings_url).
 */
$pdfIssue = ($kind ?? 'online') === 'pdf';
$lim = (array) ($pdfLimits ?? []);
$maxHuman = (string) ($lim['max_human'] ?? '25 MB');
// say "the server accepts at most ..." only when the SERVER (not the Newsletter setting) is what limits the size
$serverLimits = isset($lim['max_bytes'], $lim['setting_mb']) && (int) $lim['max_bytes'] < (int) $lim['setting_mb'] * 1048576;
?>
<section class="nl-card nl-pdfbox<?= $pdfIssue ? ' is-open' : '' ?>" data-card="pdf" data-pdf-box id="nl-card-pdf" aria-labelledby="nl-pdf-card-title">
    <header class="nl-card__head">
        <h2 class="nl-card__h" id="nl-pdf-card-title">
            <button type="button" class="nl-card__toggle" data-collapse-toggle aria-expanded="<?= $pdfIssue ? 'true' : 'false' ?>" aria-controls="nl-pdf-body">
                <span class="nl-card__title"><?= $pdfIssue ? 'The PDF' : 'PDF download (optional)' ?></span>
                <span class="nl-card__sub" data-pdf-sub><?= $pdfIssue ? 'The PDF that visitors read on the page and download' : 'Let visitors also download this issue as a PDF file' ?></span>
                <?= icon('chevron-down', 'icon--sm nl-card__chev') ?>
            </button>
        </h2>
    </header>
    <div class="nl-card__body" id="nl-pdf-body"<?= $pdfIssue ? '' : ' hidden' ?>>
        <?php if (!$pdfIssue): ?>
            <p class="nl-pdfbox__about">The issue stays a web page. With a PDF attached, a button “Download this issue as PDF” appears on the page. Visitors do not see the file until the issue is published.</p>
        <?php endif; ?>

        <div class="nl-pdf-current" data-pdf-current hidden>
            <span class="nl-pdf-current__icon" aria-hidden="true"><?= icon('file-text', 'icon--lg') ?></span>
            <div class="nl-pdf-current__text">
                <strong class="nl-pdf-current__name" data-pdf-name></strong>
                <span class="nl-pdf-current__meta" data-pdf-meta></span>
            </div>
            <div class="nl-pdf-current__actions">
                <a class="btn btn--outline btn--sm" href="#" target="_blank" rel="noopener" data-pdf-open><?= icon('external-link', 'icon--sm') ?> Open PDF<span class="visually-hidden"> (opens in a new tab)</span></a>
                <button type="button" class="btn btn--ghost btn--sm nl-btn-danger" data-pdf-remove><?= icon('trash', 'icon--sm') ?> Remove PDF</button>
            </div>
        </div>
        <p class="field__help nl-pdf-remove-note" data-pdf-remove-note hidden></p>

        <div class="nl-drop" data-pdf-drop>
            <input class="nl-drop__file" type="file" id="nl-pdf-file" accept="application/pdf,.pdf" data-pdf-file aria-describedby="nl-pdf-help">
            <span class="nl-drop__icon" aria-hidden="true"><?= icon('upload', 'icon--xl') ?></span>
            <span class="nl-drop__title" data-pdf-drop-title>Drag your PDF here</span>
            <span class="nl-drop__or">or</span>
            <label class="btn btn--primary btn--sm nl-drop__pick" for="nl-pdf-file"><?= icon('upload', 'icon--sm') ?> <span data-pdf-pick-label>Choose a PDF</span></label>
        </div>
        <p class="field__help" id="nl-pdf-help">PDF files only, up to <?= e($maxHuman) ?><?= $serverLimits && !empty($lim['server_human']) ? ' (this server accepts at most ' . e((string) $lim['server_human']) . ')' : '' ?>. It must not be password protected. Use “Compress” in your PDF program if the file is too large.<?php if (!empty($lim['can_settings'])): ?> An administrator can change the limit in <a href="<?= e((string) ($lim['settings_url'] ?? '#')) ?>">Site settings &gt; Newsletter</a>.<?php endif; ?></p>
        <div class="nl-progress" data-pdf-progress hidden>
            <progress max="100" value="0" aria-label="PDF upload progress"></progress>
            <span data-pdf-progress-text role="status"></span>
        </div>
        <p class="field__error" data-pdf-error role="alert" hidden></p>
    </div>
</section>
