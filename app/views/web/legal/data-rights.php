<?php
/**
 * Data rights request. Vars: $done (null | reference, due, mailed), $types (key => label), $preselect, $days.
 * Copy: content('data-rights', ...) - admin: Pages > Data rights request.
 */

use App\Services\Web\TextTokens;

$tok = static fn(string $s): string => TextTokens::text(str_replace('{days}', (string) $days, $s));
?>
<?= partial('partials/page-hero', [
    'title'   => content('data-rights', 'hero.title'),
    'eyebrow' => content('data-rights', 'hero.eyebrow'),
    'lead'    => TextTokens::text((string) content('data-rights', 'hero.lead')),
    'image'   => null,
]) ?>

<section class="section legal">
    <div class="container">
        <div class="legal__layout legal__layout--single">
            <div class="legal__body">
                <?php if ($done): ?>
                    <div class="alert alert--success rights-done" id="received" role="status" tabindex="-1">
                        <h2><?= e(content('data-rights', 'form.success_title')) ?></h2>
                        <p><?= e(str_replace(['{reference}', '{date}'], [(string) $done['reference'], date('j F Y', strtotime((string) $done['due']))], (string) content('data-rights', 'form.success'))) ?></p>
                        <p class="rights-ref">Reference: <strong><?= e((string) $done['reference']) ?></strong></p>
                    </div>
                <?php endif; ?>

                <div class="prose"><?= TextTokens::html(content_html('data-rights', 'form.intro')) ?></div>
                <p class="rights-target"><?= icon('clock', 'icon--sm') ?> <?= e($tok((string) content('data-rights', 'form.response_note'))) ?></p>
                <p><a href="<?= e(url('privacy')) ?>#rights">What are my rights?</a> &middot; <a href="<?= e(url('privacy')) ?>">Read the Privacy Policy</a></p>

                <form class="form-card ia-form" id="request-form" method="post" action="<?= e(url('privacy/request')) ?>" novalidate>
                    <?= csrf_field() ?>
                    <?= honeypot_field() ?>
                    <div class="form-grid">
                        <?= partial('partials/form-field', ['name' => 'name', 'id' => 'dr-name', 'label' => (string) content('data-rights', 'form.name_label'), 'required' => true, 'autocomplete' => 'name', 'maxlength' => 150]) ?>
                        <?= partial('partials/form-field', ['name' => 'email', 'id' => 'dr-email', 'label' => (string) content('data-rights', 'form.email_label'), 'type' => 'email', 'required' => true, 'autocomplete' => 'email', 'maxlength' => 190]) ?>
                        <?= partial('partials/form-field', ['name' => 'phone', 'id' => 'dr-phone', 'label' => (string) content('data-rights', 'form.phone_label'), 'type' => 'tel', 'autocomplete' => 'tel', 'maxlength' => 40]) ?>
                        <?= partial('partials/form-field', ['name' => 'type', 'id' => 'dr-type', 'label' => (string) content('data-rights', 'form.type_label'), 'type' => 'select', 'required' => true, 'options' => $types, 'empty' => 'Choose one', 'value' => old('type', $preselect)]) ?>
                        <?= partial('partials/form-field', ['name' => 'details', 'id' => 'dr-details', 'label' => (string) content('data-rights', 'form.details_label'), 'help' => (string) content('data-rights', 'form.details_help'), 'type' => 'textarea', 'rows' => 6, 'maxlength' => 3000, 'class' => 'field--full']) ?>
                        <?= partial('partials/form-field', ['name' => 'confirm', 'id' => 'dr-confirm', 'type' => 'checkbox', 'required' => true, 'label' => 'I am asking about my own personal data, or I am the parent or guardian of the young person I am asking about.', 'class' => 'field--full']) ?>
                    </div>
                    <div class="form-actions">
                        <button class="btn btn--cta btn--caps btn--lg" type="submit"><?= e(content('data-rights', 'form.submit')) ?></button>
                    </div>
                    <p class="form-note mt-4"><?= e(content('data-rights', 'form.why_note')) ?> <a href="<?= e(url('privacy')) ?>#rights">Privacy Policy</a></p>
                </form>

                <div class="prose rights-complaint"><?= TextTokens::html(content_html('data-rights', 'form.complaint_note')) ?></div>
            </div>
        </div>
    </div>
</section>
