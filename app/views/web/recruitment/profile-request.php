<?php /** Ask for the ambassador profile link again. Vars: $sent. */ ?>
<?= partial('partials/page-hero', ['title' => 'My ambassador profile', 'eyebrow' => 'Ambassadors', 'lead' => 'Lost your link? Enter your application number and the e-mail address you applied with, and we will send it again.']) ?>
<section class="section ac-auth">
    <div class="container container--narrow">
        <?php if ($sent): ?>
            <div class="card" role="status"><div class="card__body"><h2 class="ap-h"><?= icon('mail', 'icon--sm') ?> Check your inbox</h2>
                <p>If the number and the e-mail address match an application, we have sent the link to that address. It may take a minute to arrive; look in the spam folder too.</p></div></div>
        <?php else: ?>
            <form class="ac-form card" method="post" action="<?= e(url('ambassador/profile')) ?>" novalidate>
                <?= csrf_field() ?><?= honeypot_field() ?>
                <div class="card__body">
                    <?= partial('partials/form-field', ['name' => 'ref', 'label' => 'Application number', 'type' => 'text', 'required' => true, 'maxlength' => 40, 'autocomplete' => 'off', 'placeholder' => 'AMB-2026-0007']) ?>
                    <?= partial('partials/form-field', ['name' => 'email', 'label' => 'E-mail address', 'type' => 'email', 'required' => true, 'maxlength' => 190, 'autocomplete' => 'email']) ?>
                    <button class="btn btn--cta btn--caps btn--block" type="submit"><?= icon('send', 'icon--sm') ?> Send me my link</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</section>
