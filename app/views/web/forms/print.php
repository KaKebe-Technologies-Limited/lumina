<?php
/**
 * "Print a blank copy" sheet (GET /forms/{slug}?print=1). The form button opens it in a hidden frame and prints it; visiting the address directly shows the
 * same sheet with a print button. Every question is listed (conditions cannot be known on paper), nothing is filled in, nothing can be sent.
 * Vars: $form. Wording: Admin > Pages > Forms (ui.print_*).
 */
use App\Services\Forms\FormText;

$this->start('scripts'); ?>
<script src="<?= e(asset('js/forms.js')) ?>" defer></script>
<?php $this->end(); ?>
<section class="section lf-section">
    <div class="container container--narrow">
        <div class="lf-card">
            <p class="lf-print__bar"><button class="btn btn--primary" type="button" data-lf-print-now><?= icon('file-text', 'icon--sm') ?> <?= e(FormText::get('ui.print_button')) ?></button></p>
            <header class="lf-print__head">
                <h1><?= e((string) $form['title']) ?></h1>
                <?php if (trim((string) ($form['description'] ?? '')) !== ''): ?><p><?= e((string) $form['description']) ?></p><?php endif; ?>
                <p><strong><?= e(FormText::get('ui.print_title')) ?></strong></p>
            </header>
            <?= partial('web/forms/_form', ['form' => $form, 'input' => [], 'errors' => [], 'context' => null, 'mode' => 'print']) ?>
        </div>
    </div>
</section>
