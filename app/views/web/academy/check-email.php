<?php /** After sign-up: confirm your e-mail. Vars: $email, $codeOn (codes available), $whatsappOn (WhatsApp codes available). */ ?>
<?= partial('partials/page-hero', ['title' => content('academy', 'auth.check_email_title'), 'eyebrow' => 'LAF Academy']) ?>
<section class="section ac-auth">
    <div class="container container--narrow">
        <div class="ac-notice card"><div class="card__body">
            <span class="ac-notice__icon" aria-hidden="true"><?= icon('mail', 'icon--xl') ?></span>
            <p><?= e(content('academy', 'auth.check_email_text')) ?></p>
            <?php if (!empty($codeOn)): ?>
            <form method="post" action="<?= e(url('academy/verify/code')) ?>" class="ac-form" id="code" novalidate>
                <?= csrf_field() ?><?= honeypot_field() ?>
                <h2 class="ac-block__title">Type your 6-digit code</h2>
                <p class="text-muted">The e-mail we sent you contains a code<?= !empty($whatsappOn) ? ', and so does the WhatsApp message if you asked for one' : '' ?>. Type it here to confirm your account.</p>
                <?= partial('partials/form-field', ['name' => 'email', 'label' => 'Your e-mail address', 'type' => 'email', 'value' => $email, 'maxlength' => 190, 'autocomplete' => 'email', 'required' => true]) ?>
                <div class="field<?= error('code') ? ' field--error' : '' ?>">
                    <label class="field__label" for="f-code">Confirmation code<span class="field__req" aria-hidden="true"> *</span></label>
                    <input class="input ac-code-input" id="f-code" type="text" name="code" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9 ]*" maxlength="9" required placeholder="123456">
                    <?php if (error('code')): ?><div class="field__error" role="alert"><?= e((string) error('code')) ?></div><?php endif; ?>
                </div>
                <button class="btn btn--cta btn--caps" type="submit">Confirm my account</button>
            </form>
            <?php endif; ?>
            <?php if (!empty($whatsappOn)): ?>
            <form method="post" action="<?= e(url('academy/verify/whatsapp')) ?>" class="ac-form" id="whatsapp" novalidate>
                <?= csrf_field() ?><?= honeypot_field() ?>
                <h2 class="ac-block__title">Get the code on WhatsApp</h2>
                <p class="text-muted">No e-mail? Type your WhatsApp number with the country code and we will send the code there.</p>
                <input type="hidden" name="email" value="<?= e($email) ?>">
                <div class="field<?= error('whatsapp') ? ' field--error' : '' ?>">
                    <label class="field__label" for="f-wa">WhatsApp number</label>
                    <input class="input" id="f-wa" type="tel" name="whatsapp" inputmode="tel" autocomplete="tel" maxlength="24" placeholder="+256 772 123 456" value="<?= e((string) old('whatsapp')) ?>">
                    <?php if (error('whatsapp')): ?><div class="field__error" role="alert"><?= e((string) error('whatsapp')) ?></div><?php endif; ?>
                </div>
                <button class="btn btn--outline btn--caps" type="submit">Send the code on WhatsApp</button>
            </form>
            <?php endif; ?>
            <form method="post" action="<?= e(url('academy/verify/resend')) ?>" class="ac-form">
                <?= csrf_field() ?><?= honeypot_field() ?>
                <?= partial('partials/form-field', ['name' => 'email', 'label' => 'Send the link again to', 'type' => 'email', 'value' => $email, 'maxlength' => 190, 'autocomplete' => 'email']) ?>
                <button class="btn btn--outline btn--caps" type="submit">Send the link again</button>
            </form>
            <p class="ac-form__alt"><a href="<?= e(url('academy/login')) ?>">Back to sign in</a></p>
        </div></div>
    </div>
</section>
