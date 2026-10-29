<?php /** After sign-up while administrators approve the accounts (no e-mail code). Vars: $regNo (registration number or ''), $hours ("1 hour"), $thread (the Academy team's conversation with an applicant who just signed in with the right password, else empty). */ ?>
<?php $this->start('head'); ?><link rel="stylesheet" href="<?= e(asset('css/portal.min.css')) ?>"><?php $this->end(); ?>
<?= partial('partials/page-hero', ['title' => 'Application received', 'eyebrow' => 'LAF Academy']) ?>
<section class="section ac-auth">
    <div class="container container--narrow">
        <div class="ac-notice card"><div class="card__body">
            <span class="ac-notice__icon" aria-hidden="true"><?= icon('clock', 'icon--xl') ?></span>
            <p><strong>Thank you for joining the LAF Academy.</strong> Your application is received<?= $regNo !== '' ? ' and your registration number is <strong>' . e($regNo) . '</strong>. Keep it safe.' : '.' ?></p>
            <p>The Academy team approves every new account. <strong>This takes about <?= e($hours) ?>.</strong> Please sign in with your e-mail address and password after <?= e($hours) ?>: you do not need to confirm your e-mail address or type any code.</p>
            <?php if (!empty($thread)): ?>
                <div class="ac-pending-msgs" role="region" aria-label="Messages from the Academy team">
                    <h2 class="ac-block__title"><?= icon('message-circle', 'icon--sm') ?> Messages from the Academy team</h2>
                    <?php foreach ($thread as $m): ?>
                        <p class="ac-pending-msgs__item ac-pending-msgs__item--<?= $m['sender'] === 'admin' ? 'team' : 'me' ?>"><strong><?= $m['sender'] === 'admin' ? 'Academy team' : 'You' ?></strong> <small class="text-muted"><?= e(format_datetime($m['created_at'], 'j M, H:i')) ?></small><br><?= nl2br(e((string) $m['body'])) ?></p>
                    <?php endforeach; ?>
                    <p><a class="btn btn--cta btn--caps btn--sm" href="<?= e(url('academy/contact')) ?>"><?= icon('message-circle', 'icon--sm') ?> Reply to the Academy team</a></p>
                </div>
            <?php endif; ?>
            <p class="ac-form__alt"><a class="btn btn--cta btn--caps" href="<?= e(url('academy/login')) ?>">Go to my learner portal</a></p>
            <p class="ac-form__alt"><a class="btn btn--outline btn--caps" href="<?= e(url('academy/contact')) ?>"><?= icon('message-circle', 'icon--sm') ?> Message the Academy team</a></p>
            <?php if (($sw = \App\Services\Academy\LearnerAuth::supportWhatsapp()) !== null): ?>
            <p class="ac-regno">For quick inquiries about your approval, contact <a href="<?= e($sw['url']) ?>" rel="noopener" target="_blank"><strong><?= e($sw['display']) ?></strong> on WhatsApp</a>.</p>
            <?php endif; ?>
            <p class="text-muted">If you cannot sign in after <?= e($hours) ?>, please try again a little later or write to us through the contact page.</p>
        </div></div>
    </div>
</section>
