<?php /** Choose a new password. Vars: $token, $valid. */ ?>
<?= partial('partials/page-hero', ['title' => 'Choose a new password', 'eyebrow' => 'LAF Academy']) ?>
<section class="section ac-auth">
    <div class="container container--narrow">
        <?php if (!$valid): ?>
            <p class="opp-note opp-note--warn"><?= icon('alert-triangle', 'icon--sm') ?> This link has expired or was already used.</p>
            <p class="ac-center"><a class="btn btn--cta btn--caps" href="<?= e(url('academy/forgot')) ?>">Ask for a new link</a></p>
        <?php else: ?>
            <form class="ac-form card" method="post" action="<?= e(url('academy/reset/' . $token)) ?>" novalidate>
                <?= csrf_field() ?><?= honeypot_field() ?>
                <div class="card__body">
                    <?= partial('partials/form-field', ['name' => 'password', 'label' => 'New password', 'type' => 'password', 'required' => true, 'maxlength' => 72, 'autocomplete' => 'new-password', 'help' => 'At least 8 characters.']) ?>
                    <?= partial('partials/form-field', ['name' => 'password_confirm', 'label' => 'Repeat the new password', 'type' => 'password', 'required' => true, 'maxlength' => 72, 'autocomplete' => 'new-password']) ?>
                    <button class="btn btn--cta btn--caps btn--block" type="submit">Save the new password</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</section>
