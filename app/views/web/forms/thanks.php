<?php
/**
 * Thank-you page after a submission. Vars: $form, $reference ('' when unknown, for example after a refresh), $job (the vacancy slug of a job application or ''),
 * $again (can another response be sent now?), $draftKey (sessionStorage key of the saved draft, which forms.js removes here as a safety net).
 */
use App\Services\Forms\FormText;

$cfg = (array) $form['cfg'];
$html = trim((string) ($cfg['success']['message_html'] ?? ''));
$job = (string) ($job ?? '');
$again = !empty($again);
$this->start('scripts'); ?>
<script src="<?= e(asset('js/forms.js')) ?>" defer></script>
<?php $this->end(); ?>
<?= partial('partials/page-hero', ['title' => FormText::get('success.title'), 'eyebrow' => (string) $form['title'], 'sheet' => 'tint']) ?>
<section class="section section--tint lf-section">
    <div class="container container--narrow">
        <div class="lf-card lf-card--notice lf-card--thanks" data-lf-done data-lf-draft-key="<?= e((string) ($draftKey ?? '')) ?>">
            <span class="lf-mark lf-mark--ok" aria-hidden="true"><?= icon('check-circle') ?></span>
            <div class="lf-notice__text prose" role="status"><?= $html !== '' ? rich($html) : nl2p(FormText::get('success.text')) ?></div>
            <?php if ($reference !== ''): ?>
                <p class="lf-reference"><span class="lf-reference__label"><?= e(FormText::get('success.reference_label')) ?></span> <span class="lf-reference__value"><?= e($reference) ?></span></p>
                <p class="lf-reference-help"><?= e(FormText::get('success.reference_help')) ?></p>
            <?php endif; ?>
            <p class="lf-notice__actions">
                <a class="btn btn--cta btn--caps" href="<?= e(url('')) ?>"><?= e(FormText::get('success.home_button')) ?></a>
                <?php if ($job !== ''): ?>
                    <a class="btn btn--outline btn--caps" href="<?= e(url('careers')) ?>"><?= e(FormText::get('job.careers_button')) ?></a>
                <?php elseif ($again): ?>
                    <a class="btn btn--outline btn--caps" href="<?= e(url('forms/' . $form['slug'])) ?>"><?= e(FormText::get('success.another_button')) ?></a>
                <?php endif; ?>
            </p>
        </div>
    </div>
</section>
