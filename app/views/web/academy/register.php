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
                <?php if (\App\Services\Academy\GoogleSignIn::enabled()): ?>
                <a class="ac-google" href="<?= e(url('academy/google')) ?>"><svg viewBox="0 0 48 48" width="20" height="20" aria-hidden="true" focusable="false"><path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/><path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/><path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/><path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/></svg><span>Sign up with Google</span></a>
                <p class="ac-or"><span>or use your e-mail</span></p>
                <?php endif; ?>
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
