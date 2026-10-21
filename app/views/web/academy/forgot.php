<?php /** Forgotten password. */ ?>
<?= partial('partials/page-hero', ['title' => 'Forgotten your password?', 'eyebrow' => 'LAF Academy', 'lead' => 'Enter the e-mail address of your account. We will send you a link to choose a new password.']) ?>
<section class="section ac-auth">
    <div class="container container--narrow">
        <form class="ac-form card" method="post" action="<?= e(url('academy/forgot')) ?>" novalidate>
            <?= csrf_field() ?><?= honeypot_field() ?>
            <div class="card__body">
                <?= partial('partials/form-field', ['name' => 'email', 'label' => 'E-mail address', 'type' => 'email', 'required' => true, 'maxlength' => 190, 'autocomplete' => 'email']) ?>
                <button class="btn btn--cta btn--caps btn--block" type="submit">Send me the link</button>
                <p class="ac-form__alt"><a href="<?= e(url('academy/login')) ?>">Back to sign in</a></p>
            </div>
        </form>
    </div>
</section>
