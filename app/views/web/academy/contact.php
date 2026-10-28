<?php
/** Public "Message the Academy team". Vars: $sent (bool), $email (prefill), $sw (support WhatsApp line or null), $hours ("1 hour"). */
?>
<?= partial('partials/page-hero', ['title' => 'Message the Academy team', 'eyebrow' => 'LAF Academy', 'lead' => 'A question about your registration, your approval or the courses? Write to us here: your message goes straight to the Academy team.']) ?>
<section class="section ac-auth">
    <div class="container container--narrow">
        <?php if (!empty($sent)): ?>
            <div class="ac-notice card"><div class="card__body">
                <span class="ac-notice__icon" aria-hidden="true"><?= icon('message-circle', 'icon--xl') ?></span>
                <p><strong>Thank you, your message was sent.</strong> The Academy team will answer you by e-mail<?= $sw !== null ? ' or WhatsApp' : '' ?>. If you already have an account, you will also see the answer in your learner portal after you sign in.</p>
                <?php if ($sw !== null): ?><p class="ac-regno">For a quick answer about your approval, contact <a href="<?= e($sw['url']) ?>" target="_blank" rel="noopener"><strong><?= e($sw['display']) ?></strong> on WhatsApp</a>.</p><?php endif; ?>
                <p class="ac-form__alt"><a class="btn btn--cta btn--caps" href="<?= e(url('academy/login')) ?>">Go to my learner portal</a></p>
            </div></div>
        <?php else: ?>
            <form class="ac-form card" method="post" action="<?= e(url('academy/contact')) ?>" novalidate>
                <?= csrf_field() ?><?= honeypot_field() ?>
                <div class="card__body">
                    <div class="form-grid">
                        <?= partial('partials/form-field', ['name' => 'name', 'label' => 'Your name', 'required' => true, 'maxlength' => 120, 'autocomplete' => 'name']) ?>
                        <?= partial('partials/form-field', ['name' => 'email', 'label' => 'E-mail address', 'type' => 'email', 'required' => true, 'maxlength' => 190, 'autocomplete' => 'email', 'value' => old('email', $email), 'help' => 'The e-mail you registered with, so we can find your account.']) ?>
                    </div>
                    <?= partial('partials/form-field', ['name' => 'phone', 'label' => 'Phone or WhatsApp (optional)', 'type' => 'tel', 'maxlength' => 40, 'autocomplete' => 'tel']) ?>
                    <?= partial('partials/form-field', ['name' => 'message', 'label' => 'Your message', 'type' => 'textarea', 'rows' => 5, 'required' => true, 'maxlength' => 2000, 'placeholder' => 'For example: I registered yesterday and my account is not approved yet.']) ?>
                    <button class="btn btn--cta btn--caps btn--block" type="submit"><?= icon('send', 'icon--sm') ?> Send my message</button>
                    <?php if ($sw !== null): ?><p class="ac-form__alt">Quick inquiries about your approval (it takes about <?= e($hours) ?>)? <a href="<?= e($sw['url']) ?>" target="_blank" rel="noopener">WhatsApp <?= e($sw['display']) ?></a></p><?php endif; ?>
                    <p class="ac-form__alt"><a href="<?= e(url('academy/login')) ?>">Sign in</a> &middot; <a href="<?= e(url('academy/register')) ?>">Create an account</a></p>
                </div>
            </form>
        <?php endif; ?>
    </div>
</section>
