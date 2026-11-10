<?php /** Track my application. Vars: $result (null before a lookup, else Recruitment::track()), $ref, $email. */ ?>
<?= partial('partials/page-hero', ['title' => 'Track my application', 'eyebrow' => 'Ambassadors, jobs and volunteering', 'lead' => 'Enter the application number we e-mailed you and the e-mail address you applied with, to see which stage your application has reached.']) ?>
<section class="section ac-auth">
    <div class="container container--narrow">
        <form class="ac-form card" method="post" action="<?= e(url('track-application')) ?>" novalidate>
            <?= csrf_field() ?><?= honeypot_field() ?>
            <div class="card__body">
                <?= partial('partials/form-field', ['name' => 'ref', 'label' => 'Application number', 'type' => 'text', 'required' => true, 'maxlength' => 40, 'autocomplete' => 'off', 'value' => $ref, 'placeholder' => 'AMB-2026-0007', 'help' => 'It starts with AMB (ambassador), JOB (job) or VOL (volunteer).']) ?>
                <?= partial('partials/form-field', ['name' => 'email', 'label' => 'E-mail address', 'type' => 'email', 'required' => true, 'maxlength' => 190, 'autocomplete' => 'email', 'value' => $email]) ?>
                <button class="btn btn--cta btn--caps btn--block" type="submit"><?= icon('search', 'icon--sm') ?> Check my application</button>
            </div>
        </form>
        <?php if ($result !== null): ?>
            <?php if (!$result['found']): ?>
                <div class="ac-track ac-track--bad card" role="status"><div class="card__body">
                    <h2 class="ac-track__title"><?= icon('alert-circle', 'icon--sm') ?> No application found</h2>
                    <p>We could not find an application with that number and e-mail address. Check that you copied the whole number and used the same e-mail address as on the form.</p>
                </div></div>
            <?php else: ?>
                <div class="ac-track ac-track--<?= e($result['tone']) ?> card" role="status"><div class="card__body">
                    <p class="ac-track__no"><?= e($result['ref']) ?> &middot; last update <?= e(format_date($result['updated'], 'j M Y')) ?></p>
                    <h2 class="ac-track__title"><?= icon($result['tone'] === 'good' ? 'check-circle' : ($result['tone'] === 'bad' ? 'alert-circle' : 'clock'), 'icon--sm') ?> <?= e($result['label']) ?></h2>
                    <?php if ($result['steps']): ?>
                        <ol class="ac-track__steps">
                            <?php foreach ($result['steps'] as $s): ?><li class="ac-track__step ac-track__step--<?= e($s['state']) ?>"><strong><?= e($s['label']) ?></strong></li><?php endforeach; ?>
                        </ol>
                    <?php endif; ?>
                </div></div>
            <?php endif; ?>
            <p class="text-muted ac-track__note">For your privacy this page shows only the stage of the application. We also e-mail you at every stage.</p>
        <?php endif; ?>
    </div>
</section>
