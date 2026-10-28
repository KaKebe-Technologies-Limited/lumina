<?php
/** Sign-up. Vars: $open (bool), $learner. Copy: content('academy', 'auth.*'). */
?>
<?= partial('partials/page-hero', ['title' => content('academy', 'auth.register_title'), 'eyebrow' => 'LAF Academy', 'lead' => content('academy', 'auth.register_intro')]) ?>
<section class="section ac-auth">
    <div class="container container--narrow">
        <?php if (!$open): ?>
            <p class="opp-note"><?= icon('info', 'icon--sm') ?> <?= e(content('academy', 'auth.register_closed')) ?></p>
            <p class="ac-center"><a class="btn btn--cta btn--caps" href="<?= e(url('academy/login')) ?>">Sign in</a></p>
        <?php else: ?>
            <form class="ac-form card" method="post" action="<?= e(url('academy/register')) ?>" novalidate>
                <?= csrf_field() ?><?= honeypot_field() ?>
                <div class="card__body">
                    <?= partial('partials/form-field', ['name' => 'name', 'label' => 'Full name', 'required' => true, 'maxlength' => 120, 'autocomplete' => 'name', 'help' => 'As it should appear on your certificates.']) ?>
                    <?= partial('partials/form-field', ['name' => 'email', 'label' => 'E-mail address', 'type' => 'email', 'required' => true, 'maxlength' => 190, 'autocomplete' => 'email']) ?>
                    <?= partial('partials/form-field', ['name' => 'password', 'label' => 'Password', 'type' => 'password', 'required' => true, 'maxlength' => 72, 'autocomplete' => 'new-password', 'help' => 'At least 8 characters. A short sentence is easy to remember and hard to guess.']) ?>
                    <div class="form-grid">
                        <?= partial('partials/form-field', ['name' => 'phone', 'label' => 'Phone (optional)', 'type' => 'tel', 'maxlength' => 40, 'autocomplete' => 'tel']) ?>
                        <?= partial('partials/form-field', ['name' => 'country', 'label' => 'Country (optional)', 'maxlength' => 80, 'autocomplete' => 'country-name']) ?>
                    </div>
                    <?php if (\App\Services\Academy\WhatsAppSender::enabled() && \App\Services\Academy\VerificationCode::ready()): ?>
                        <?= partial('partials/form-field', ['name' => 'whatsapp', 'label' => 'WhatsApp number (optional)', 'type' => 'tel', 'maxlength' => 24, 'autocomplete' => 'tel', 'help' => 'We will also send your confirmation code to WhatsApp. Include the country code, for example +256 772 123 456.']) ?>
                    <?php endif; ?>
                    <div class="field<?= error('agree') ? ' field--error' : '' ?>">
                        <label class="check"><input type="checkbox" name="agree" value="1"<?= old('agree') ? ' checked' : '' ?> required><span><?= e(content('academy', 'auth.consent')) ?></span></label>
                        <?php if (error('agree')): ?><div class="field__error"><?= e((string) error('agree')) ?></div><?php endif; ?>
                    </div>
                    <button class="btn btn--cta btn--caps btn--block" type="submit">Create my account</button>
                    <p class="ac-form__alt">Already have an account? <a href="<?= e(url('academy/login')) ?>">Sign in</a></p>
                </div>
            </form>
        <?php endif; ?>
    </div>
</section>
