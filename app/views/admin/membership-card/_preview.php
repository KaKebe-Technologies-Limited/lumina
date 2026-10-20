<?php
/**
 * The designer's preview column. OWNER: CARD agent. Vars: see index.php ($scenarios, $previewPdf, $previewPng, $caps, $adminEmail).
 * The frame shows the REAL card (a PDF made by the same engine that makes members' cards) with a fictional member. It reflects the form even before you save: the
 * script posts the form to admin/membership-card/preview (values are kept in the session, never in the database) and reloads the frame. Phones often cannot show a PDF
 * inside a page, so a picture version (when the server has GD) and an "Open the PDF" link are always offered.
 */
$pdfFrame = $previewPdf . '#toolbar=0&navpanes=0&view=Fit';
?>
<aside class="mc-preview" aria-label="Card preview" data-mc-preview data-has-png="<?= $caps['png'] ? '1' : '0' ?>" data-png-url="<?= e($previewPng) ?>">
    <section class="card mc-preview__card">
        <div class="card__header">
            <h2 class="card__title">Live preview</h2>
            <span class="mc-live" data-mc-live><span class="mc-live__dot" aria-hidden="true"></span> <span data-mc-live-text>Saved design</span></span>
        </div>
        <div class="card__body">
            <div class="field">
                <label class="field__label" for="mc-scenario">Show the card of</label>
                <select class="select" id="mc-scenario" data-mc-scenario>
                    <?php foreach ($scenarios as $k => $label): ?><option value="<?= e($k) ?>"><?= e($label) ?></option><?php endforeach; ?>
                </select>
                <div class="field__help">A made-up member, to try the design with difficult cases. Nothing here is a real person.</div>
            </div>

            <?php if ($caps['png']): ?>
                <div class="mc-viewswitch" role="group" aria-label="Show the preview as" data-mc-viewswitch>
                    <button type="button" class="mc-viewswitch__btn is-active" data-mc-view="pdf" aria-pressed="true">PDF (exact)</button>
                    <button type="button" class="mc-viewswitch__btn" data-mc-view="png" aria-pressed="false">Picture</button>
                </div>
            <?php endif; ?>

            <div class="mc-stage" data-mc-stage aria-busy="false">
                <iframe class="mc-stage__frame" title="Preview of the membership card (PDF)" src="<?= e($pdfFrame) ?>" data-mc-frame></iframe>
                <?php if ($caps['png']): ?><img class="mc-stage__img" src="<?= e($previewPng) ?>" alt="Preview of the membership card" width="720" height="414" hidden data-mc-img><?php endif; ?>
                <div class="mc-stage__busy" hidden data-mc-busy><span class="mc-spinner" aria-hidden="true"></span> Updating</div>
            </div>
            <p class="mc-preview__status" data-mc-status role="status" aria-live="polite"></p>
            <noscript><p class="field__help">Without JavaScript the preview shows the saved design: save, then reload the page to see a change.</p></noscript>

            <div class="mc-preview__buttons">
                <button class="btn btn--outline btn--sm" type="button" data-mc-refresh><?= icon('refresh', 'icon--sm') ?> Update now</button>
                <button class="btn btn--outline btn--sm" type="button" data-mc-fullsize hidden><?= icon('eye', 'icon--sm') ?> Full size</button>
                <a class="btn btn--ghost btn--sm" href="<?= e(url('admin/membership-card/preview.pdf')) ?>" target="_blank" rel="noopener" data-mc-open><?= icon('external-link', 'icon--sm') ?> Open the PDF</a>
                <a class="btn btn--ghost btn--sm" href="<?= e(url('admin/membership-card/preview.pdf', ['download' => 1])) ?>" data-mc-download><?= icon('download', 'icon--sm') ?> Download</a>
            </div>
            <label class="check mc-auto"><input type="checkbox" checked data-mc-auto> <span>Update the preview while I type</span></label>
            <details class="mc-more">
                <summary>The card does not show?</summary>
                <p class="field__help">Some browsers and phones cannot display a PDF inside a page. Use "Open the PDF"<?= $caps['png'] ? ' or the Picture view' : '' ?> to see the card.
                    <?php if (!$caps['png']): ?>A picture version needs the PHP "gd" extension, which this server does not have. The PDF is the real card either way.
                    <?php elseif (!$caps['ttf']): ?>The picture view uses plain fonts on this server; the PDF is the exact card.<?php endif; ?></p>
            </details>
        </div>
    </section>

    <section class="card mc-side">
        <div class="card__body">
            <form method="post" action="<?= e(url('admin/membership-card/test-email')) ?>" id="mc-test" data-mc-test>
                <?= csrf_field() ?>
                <button class="btn btn--outline btn--sm" type="submit"<?= $adminEmail === '' ? ' disabled' : '' ?>><?= icon('send', 'icon--sm') ?> Send a test card to my e-mail</button>
            </form>
            <p class="field__help"><?php if ($adminEmail !== ''): ?>Goes to <?= e($adminEmail) ?> only. It carries the card you see here (even if unsaved) and is marked as a test.<?php else: ?>Your administrator account has no e-mail address.<?php endif; ?></p>
        </div>
    </section>

    <dialog class="mc-dialog" data-mc-dialog aria-labelledby="mc-dialog-title">
        <div class="mc-dialog__bar">
            <h2 class="mc-dialog__title" id="mc-dialog-title">Membership card, full size</h2>
            <button type="button" class="btn btn--outline btn--sm" data-mc-dialog-close><?= icon('x', 'icon--sm') ?> Close</button>
        </div>
        <iframe class="mc-dialog__frame" title="Membership card preview, full size" data-mc-dialog-frame></iframe>
    </dialog>
</aside>
