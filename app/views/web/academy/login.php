<?php
/** Sign-in. Copy: content('academy', 'auth.*'). */
?>
<?= partial('partials/page-hero', ['title' => content('academy', 'auth.login_title'), 'eyebrow' => 'LAF Academy', 'lead' => content('academy', 'auth.login_intro')]) ?>
<section class="section ac-auth">
    <div class="container container--narrow">
        <form class="ac-form card" method="post" action="<?= e(url('academy/login')) ?>" novalidate>
            <?= csrf_field() ?><?= honeypot_field() ?>
            <div class="card__body">
                <?= partial('partials/form-field', ['name' => 'email', 'label' => 'E-mail address', 'type' => 'email', 'required' => true, 'maxlength' => 190, 'autocomplete' => 'username']) ?>
                <?= partial('partials/form-field', ['name' => 'password', 'label' => 'Password', 'type' => 'password', 'required' => true, 'maxlength' => 72, 'autocomplete' => 'current-password']) ?>
                <button class="btn btn--cta btn--caps btn--block" type="submit">Sign in</button>
                <p class="ac-form__alt"><a href="<?= e(url('academy/forgot')) ?>">Forgotten your password?</a></p>
                <p class="ac-form__alt">New here? <a href="<?= e(url('academy/register')) ?>">Create a free account</a></p>
            </div>
        </form>
    </div>
</section>
