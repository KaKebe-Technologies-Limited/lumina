<?php /** After sign-up while administrators approve the accounts (no e-mail code). Vars: $regNo (registration number or ''), $hours ("1 hour"), $thread (the Academy team's conversation with an applicant who just signed in with the right password, else empty), $fee (the sign-up fee still to pay: ['display' => 'UGX 20,000', ...] or null). */ ?>
<?php $this->start('head'); ?><link rel="stylesheet" href="<?= e(asset('css/portal.min.css')) ?>"><?php $this->end(); ?>
<?= partial('partials/page-hero', ['title' => 'Application received', 'eyebrow' => 'LAF Academy']) ?>
<section class="section ac-auth">
    <div class="container container--narrow">
        <div class="ac-notice card"><div class="card__body">
            <span class="ac-notice__icon" aria-hidden="true"><?= icon('clock', 'icon--xl') ?></span>
            <p><strong>Thank you for joining the LAF Academy.</strong> Your application is received<?= $regNo !== '' ? ' and your registration number is <strong>' . e($regNo) . '</strong>. Keep it safe.' : '.' ?></p>
            <?php if (!empty($fee)): ?>
                <div class="ac-pending-msgs" role="region" aria-label="Sign-up fee">
                    <h2 class="ac-block__title"><?= icon('credit-card', 'icon--sm') ?> Sign-up fee: <?= e($fee['display']) ?></h2>
                    <p><strong>Your account is approved only after the sign-up fee is paid and confirmed.</strong> You pay securely online. Your registration number stays reserved.</p>
                    <?php if (!empty($feePesapal)): ?><form method="post" action="<?= e(url('academy/fee/pay')) ?>" style="margin-bottom:.75rem"><?= csrf_field() ?><button class="btn btn--cta btn--caps" type="submit"><?= icon('lock', 'icon--sm') ?> Pay the sign-up fee with Pesapal</button></form><?php endif; ?>
                    <?php if (!empty($feeManual)): ?>
                        <div class="ac-paydetails">
                            <?php if ($feeManual['mobile'] !== ''): ?><h3 class="ac-block__title"><?= icon('smartphone', 'icon--sm') ?> Pay by mobile money</h3><?= nl2p($feeManual['mobile']) ?><?php endif; ?>
                            <?php if ($feeManual['bank'] !== ''): ?><h3 class="ac-block__title"><?= icon('banknote', 'icon--sm') ?> Pay by bank transfer</h3><?= nl2p($feeManual['bank']) ?><?php endif; ?>
                            <p class="text-muted">Pay exactly <strong><?= e($fee['display']) ?></strong>, then tell us below that you paid.</p>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($feeDaraza)): ?>
                        <p><a class="btn <?= !empty($feePesapal) ? 'btn--outline' : 'btn--cta' ?> btn--caps" href="<?= e($feeDaraza['url']) ?>" target="_blank" rel="noopener noreferrer"><?= icon('external-link', 'icon--sm') ?> <?= e($feeDaraza['label']) ?></a></p>
                        <p class="text-muted">After you pay on Daraza, the Academy team checks the payment and approves your account. This can take a little time. If you like, <a href="<?= e(url('academy/contact')) ?>">send us your Daraza transaction ID</a> to speed it up.</p>
                    <?php endif; ?>
                    <?php if ((!empty($feeManual) || !empty($feeDaraza)) && !empty($feeReportReady)): ?>
                        <?php if (!empty($feeReported)): ?>
                            <p class="opp-notice" role="status"><?= icon('check-circle', 'icon--sm') ?><span>You reported a payment with the transaction ID <strong><?= e($feeReported['fee_reference']) ?></strong> on <?= e(format_datetime($feeReported['fee_reported_at'], 'j M, H:i')) ?>. The Academy team is checking it. Your account is approved only after the payment is confirmed.</span></p>
                        <?php endif; ?>
                        <form method="post" action="<?= e(url('academy/fee/report')) ?>" enctype="multipart/form-data" class="ac-reportfee" novalidate>
                            <?= csrf_field() ?><?= honeypot_field() ?>
                            <h3 class="ac-block__title"><?= icon('upload', 'icon--sm') ?> I have paid</h3>
                            <?= partial('partials/form-field', ['name' => 'reference', 'label' => 'Transaction ID or receipt number', 'required' => true, 'maxlength' => 60, 'help' => 'Exactly as it appears on your payment message or receipt.']) ?>
                            <div class="field"><label class="field__label" for="fee-proof">Receipt (optional)</label><input class="input" id="fee-proof" type="file" name="proof" accept="image/jpeg,image/png,application/pdf"><div class="field__help">A screenshot, photo or PDF of the payment, up to 5 MB. Only the Academy team sees it.</div></div>
                            <button class="btn btn--primary btn--caps" type="submit">Send my payment details</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <?php if (\App\Services\Academy\LearnerAuth::approvalByPayment()): ?>
            <p><strong>Pay the sign-up fee and your account is approved automatically as soon as the payment is confirmed.</strong> Then sign in with your e-mail address and password: you do not need to confirm your e-mail address or type any code.</p>
            <?php else: ?>
            <p>The Academy team approves every new account. <strong>This takes about <?= e($hours) ?>.</strong> Please sign in with your e-mail address and password after <?= e($hours) ?>: you do not need to confirm your e-mail address or type any code.</p>
            <?php endif; ?>
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
            <?= partial('partials/academy-wa-group', ['style' => 'line']) ?>
            <p class="text-muted"><?= \App\Services\Academy\LearnerAuth::approvalByPayment() ? 'If you cannot sign in after you have paid, please write to us through the contact page.' : 'If you cannot sign in after ' . e($hours) . ', please try again a little later or write to us through the contact page.' ?></p>
        </div></div>
    </div>
</section>
