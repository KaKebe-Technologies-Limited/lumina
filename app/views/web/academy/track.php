<?php /** Track my application. Vars: $result (null before a lookup, else LearnerRegistry::track()), $input. */ ?>
<?= partial('partials/page-hero', ['title' => 'Track my application', 'eyebrow' => 'LAF Academy', 'lead' => 'Paste your registration number to see whether your account exists, is approved and is active.']) ?>
<section class="section ac-auth">
    <div class="container container--narrow">
        <form class="ac-form card" method="post" action="<?= e(url('academy/track')) ?>" novalidate>
            <?= csrf_field() ?><?= honeypot_field() ?>
            <div class="card__body">
                <?= partial('partials/form-field', ['name' => 'number', 'label' => 'Registration number', 'type' => 'text', 'required' => true, 'maxlength' => 40, 'autocomplete' => 'off', 'value' => $input, 'placeholder' => 'LAF-AC-2026-000123', 'help' => 'You were given it when you created your account. It starts with LAF-AC.']) ?>
                <button class="btn btn--cta btn--caps btn--block" type="submit"><?= icon('search', 'icon--sm') ?> Check my account</button>
            </div>
        </form>

        <?php if ($result !== null): ?>
            <?php if (!$result['found']): ?>
                <div class="ac-track ac-track--bad card" role="status"><div class="card__body">
                    <h2 class="ac-track__title"><?= icon('alert-circle', 'icon--sm') ?> No account found</h2>
                    <p>We could not find an account with the number <strong><?= e($result['number']) ?></strong>. Check that you copied all of it. If you are sure it is right, <a href="<?= e(url('academy/contact')) ?>">message the Academy team</a>.</p>
                </div></div>
            <?php else: ?>
                <div class="ac-track ac-track--<?= e($result['tone']) ?> card" role="status"><div class="card__body">
                    <p class="ac-track__no"><?= e($result['number']) ?><?= $result['registered_on'] ? ' &middot; registered on ' . e($result['registered_on']) : '' ?></p>
                    <h2 class="ac-track__title"><?= icon($result['tone'] === 'good' ? 'check-circle' : ($result['tone'] === 'bad' ? 'alert-circle' : 'clock'), 'icon--sm') ?> <?= e($result['headline']) ?></h2>
                    <ol class="ac-track__steps">
                        <?php foreach ($result['steps'] as $s): ?>
                            <li class="ac-track__step ac-track__step--<?= e($s['state']) ?>"><strong><?= e($s['label']) ?></strong><span><?= e($s['note']) ?></span></li>
                        <?php endforeach; ?>
                    </ol>
                    <?php if ($result['can_sign_in']): ?><p><a class="btn btn--cta btn--caps" href="<?= e(url('academy/login')) ?>">Sign in</a></p>
                    <?php else: ?><p class="text-muted">Need help? <a href="<?= e(url('academy/contact')) ?>">Message the Academy team</a>.</p><?php endif; ?>
                </div></div>
            <?php endif; ?>
            <p class="text-muted ac-track__note">For your privacy this page shows only the state of the account, never any personal details.</p>
        <?php endif; ?>
    </div>
</section>
