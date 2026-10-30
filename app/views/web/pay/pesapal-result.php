<?php
/** After the Pesapal page: what Pesapal itself says about the payment. Vars: $state (paid | pending | failed | reversed | review | unknown | verify_failed), $row (the payment or null). */
$copy = [
    'paid'          => ['heart', 'Thank you for your gift!', 'Your payment was received. Your generosity helps us reach more young people and families across Africa. A confirmation has been e-mailed to you by Pesapal.'],
    'pending'       => ['clock', 'We are waiting for your payment', 'Pesapal has not confirmed the payment yet. If you have paid, it can take a few minutes: you do not need to pay again. If money was taken and nothing changes within the hour, write to us and quote the reference below.'],
    'failed'        => ['x-circle', 'The payment did not go through', 'Nothing was charged. You are welcome to try again, or choose another way to give.'],
    'reversed'      => ['x-circle', 'The payment was reversed', 'Pesapal reports that this payment was reversed. If you think this is a mistake, please write to us.'],
    'review'        => ['clock', 'We are checking your payment', 'Your payment needs a quick check by our team. We will contact you if we need anything.'],
    'verify_failed' => ['clock', 'We could not confirm the payment yet', 'We could not reach Pesapal just now. If you paid, we will receive the confirmation shortly: please do not pay again.'],
    'unknown'       => ['info', 'Payment not found', 'We could not find this payment. If you were charged, please write to us.'],
];
[$icon, $title, $text] = $copy[$state] ?? $copy['unknown'];
$purpose = (string) ($row['purpose'] ?? '');
if ($purpose === 'academy_signup' || $purpose === 'academy_cert') {
    $what = $purpose === 'academy_signup' ? 'sign-up fee' : 'certificate fee';
    $approvedNow = $purpose === 'academy_signup' && $state === 'paid' && AppCoreDatabase::value('SELECT email_verified_at FROM academy_learners WHERE id = ?', [(int) ($row['ref_id'] ?? 0)]) !== null;
    $next = $purpose === 'academy_signup' ? ($approvedNow ? 'Your account is approved: you can sign in to your learner portal now.' : 'Your account is now with the Academy team: sign in after it is approved (you do not need to pay again).') : 'Sign in to your learner portal to open your certificate.';
    $academy = [
        'paid'    => ['check-circle', 'Payment received', 'Thank you! Your ' . $what . ' was received. ' . $next],
        'pending' => ['clock', 'We are waiting for your payment', 'Pesapal has not confirmed the ' . $what . ' yet. If you have paid, it can take a few minutes: please do not pay again. You can sign in later to check.'],
        'failed'  => ['x-circle', 'The payment did not go through', 'Nothing was charged. Sign in and press "Pay the ' . $what . '" to try again.'],
    ];
    [$icon, $title, $text] = $academy[$state] ?? [$icon, $title, $text];
}
$backUrl = in_array($purpose, ['academy_signup', 'academy_cert'], true) ? 'academy/login' : ($state === 'paid' ? '' : 'donate');
$backLabel = in_array($purpose, ['academy_signup', 'academy_cert'], true) ? 'Go to my learner portal' : ($state === 'paid' ? 'Back to the home page' : 'Back to the donate page');
?>
<?= partial('partials/page-hero', ['title' => $title, 'eyebrow' => 'Donate']) ?>
<section class="section">
    <div class="container container--narrow" data-no-justify style="text-align:center">
        <span aria-hidden="true"><?= icon($icon, 'icon--xl') ?></span>
        <p class="lead"><?= e($text) ?></p>
        <?php if (!empty($row['merchant_reference'])): ?><p class="text-muted">Reference: <strong><?= e($row['merchant_reference']) ?></strong></p><?php endif; ?>
        <p>
            <a class="btn btn--cta btn--caps" href="<?= e(url($backUrl)) ?>"><?= e($backLabel) ?></a>
        </p>
    </div>
</section>
