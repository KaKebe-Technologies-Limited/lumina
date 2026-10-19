<?php
/**
 * Admin > Newsletter > Write an issue / Upload a PDF issue: the small "new draft" form (EDITOR / NEWSLETTER agent). Vars: $nextNumber, $kind ('online' | 'pdf',
 * the format that is preselected, from ?kind=), $pdfMax (largest PDF in human form, e.g. "25 MB").
 * Step 1: choose HOW the issue is made (the choice cannot be changed afterwards) and give it a title. The editor opens right after; for a PDF issue its
 * first box is the PDF drop zone. Nothing is public until the issue is published.
 */
$kind = (string) ($kind ?? 'online');
$chosen = old('kind', $kind);
$chosen = $chosen === 'pdf' ? 'pdf' : 'online';
$this->start('scripts'); ?>
<script src="<?= e(asset('js/admin-newsletter.js')) ?>" defer></script>
<?php $this->end(); ?>
<div class="nl-admin" data-nl-create>
    <?= partial('partials/admin/page-header', ['title' => 'New newsletter issue', 'subtitle' => 'Choose how you want to make this issue, and give it a title. You can change everything except the format later; nothing is public until you publish it.']) ?>
    <form class="card nl-create" method="post" action="<?= e(url('admin/newsletters')) ?>" novalidate>
        <?= csrf_field() ?>
        <div class="card__body">
            <fieldset class="nl-kinds" aria-describedby="nl-kind-help">
                <legend class="field__label">How will this issue be made?</legend>
                <div class="nl-kinds__grid">
                    <label class="nl-kind" for="nl-kind-online">
                        <input class="nl-kind__input" type="radio" name="kind" id="nl-kind-online" value="online"<?= $chosen === 'online' ? ' checked' : '' ?>>
                        <span class="nl-kind__card">
                            <span class="nl-kind__icon" aria-hidden="true"><?= icon('edit', 'icon--lg') ?></span>
                            <span class="nl-kind__title">Write it here</span>
                            <span class="nl-kind__text">Type the issue with the editor: headings, text, pictures, quotes and buttons, with a live preview. Visitors read it as a web page.</span>
                        </span>
                    </label>
                    <label class="nl-kind" for="nl-kind-pdf">
                        <input class="nl-kind__input" type="radio" name="kind" id="nl-kind-pdf" value="pdf"<?= $chosen === 'pdf' ? ' checked' : '' ?>>
                        <span class="nl-kind__card">
                            <span class="nl-kind__icon" aria-hidden="true"><?= icon('file-text', 'icon--lg') ?></span>
                            <span class="nl-kind__title">Upload a PDF</span>
                            <span class="nl-kind__text">Already have the newsletter as a PDF file (up to <?= e($pdfMax) ?>)? Upload it. Visitors can read it on the page and download it.</span>
                        </span>
                    </label>
                </div>
                <?php if (has_error('kind')): ?><p class="field__error" role="alert"><?= e(error('kind')) ?></p><?php endif; ?>
                <p class="field__help" id="nl-kind-help">The format is chosen once. A written issue can also carry an optional PDF download later.</p>
            </fieldset>

            <?= partial('partials/form-field', ['name' => 'title', 'label' => 'Title', 'required' => true, 'maxlength' => 190, 'help' => 'The headline of this issue. It appears on the website, in the archive and in e-mails.', 'attrs' => ['autofocus' => true]]) ?>
            <div class="nl-grid-2">
                <?= partial('partials/form-field', ['name' => 'issue_number', 'label' => 'Issue number', 'type' => 'number', 'value' => old('issue_number', (string) $nextNumber), 'min' => 1, 'help' => 'Suggested: the next free number. Each issue needs its own.']) ?>
                <?= partial('partials/form-field', ['name' => 'issue_date', 'label' => 'Issue date', 'type' => 'date', 'value' => old('issue_date', date('Y-m-d')), 'help' => 'The date printed on the issue.']) ?>
            </div>
            <p class="nl-create__next"><?= icon('info', 'icon--sm') ?> <span data-kind-note="online"<?= $chosen === 'pdf' ? ' hidden' : '' ?>>Next you will write the featured article, add sections (articles, pictures, quotes, buttons) and see a live preview as you go.</span><span data-kind-note="pdf"<?= $chosen === 'online' ? ' hidden' : '' ?>>Next you will upload the PDF, add a short summary and an optional cover picture, then publish when you are ready.</span></p>
        </div>
        <div class="card__footer"><button class="btn btn--primary" type="submit"><?= icon('plus', 'icon--sm') ?> Create the draft</button> <a class="btn btn--ghost" href="<?= e(url('admin/newsletters')) ?>">Cancel</a></div>
    </form>
</div>
