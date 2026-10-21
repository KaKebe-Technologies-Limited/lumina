<?php /** After sign-up: confirm your e-mail. Vars: $email. */ ?>
<?= partial('partials/page-hero', ['title' => content('academy', 'auth.check_email_title'), 'eyebrow' => 'LAF Academy']) ?>
<section class="section ac-auth">
    <div class="container container--narrow">
        <div class="ac-notice card"><div class="card__body">
            <span class="ac-notice__icon" aria-hidden="true"><?= icon('mail', 'icon--xl') ?></span>
            <p><?= e(content('academy', 'auth.check_email_text')) ?></p>
            <form method="post" action="<?= e(url('academy/verify/resend')) ?>" class="ac-form">
                <?= csrf_field() ?><?= honeypot_field() ?>
                <?= partial('partials/form-field', ['name' => 'email', 'label' => 'Send the link again to', 'type' => 'email', 'value' => $email, 'maxlength' => 190, 'autocomplete' => 'email']) ?>
                <button class="btn btn--outline btn--caps" type="submit">Send the link again</button>
            </form>
            <p class="ac-form__alt"><a href="<?= e(url('academy/login')) ?>">Back to sign in</a></p>
        </div></div>
    </div>
</section>
