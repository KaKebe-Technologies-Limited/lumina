<?php
declare(strict_types=1);
/**
 * The website launch pop-up. Config: app/config/launch.php (dates, photo, words). Printed on every public page until "ends_at"; assets/js/launch.js opens it, runs the big countdown
 * (with a ten-second final countdown and a balloon burst at zero) and closes it. Without JavaScript nothing is shown (it stays hidden until the script opens it).
 * Wide layout: the photo with the countdown on the left, the message on the right (stacked on phones). Styles: scss/components/_launch.scss. No variables.
 */
$lp = (array) require APP_PATH . '/config/launch.php';
$lpEnds = strtotime((string) ($lp['ends_at'] ?? ''));
$lpLaunch = strtotime((string) ($lp['launch_at'] ?? ''));
if (empty($lp['enabled']) || $lpLaunch === false || $lpEnds === false || time() >= $lpEnds) {
    return;
}
$lpImg = (string) ($lp['image'] ?? '');
$lpImg = $lpImg !== '' && is_file(BASE_PATH . '/' . $lpImg) ? img_url($lpImg) : '';
?>
<?php $lpGate = !empty($gate); ?>
<div class="launch<?= $lpGate ? ' launch--gate' : '' ?>" id="launch-popup" data-launch<?= $lpGate ? ' data-launch-gate' : '' ?> data-launch-at="<?= (int) $lpLaunch ?>" data-launch-ends="<?= (int) $lpEnds ?>" role="dialog" aria-modal="true" aria-labelledby="launch-title"<?= $lpGate ? '' : ' hidden' ?>>
    <div class="launch__backdrop"<?= $lpGate ? '' : ' data-launch-close' ?>></div>
    <div class="launch__card" tabindex="-1">
        <?php if (!$lpGate): ?><button class="launch__close" type="button" data-launch-close aria-label="Close"><?= icon('x') ?></button><?php endif; ?>

        <div class="launch__media">
            <?php if ($lpImg !== ''): ?><?= partial('partials/hero-img', ['src' => (string) $lp['image'], 'alt' => (string) ($lp['image_alt'] ?? ''), 'class' => 'launch__img', 'priority' => 'lazy']) ?><?php endif; ?>
            <span class="launch__veil" aria-hidden="true"></span>
            <span class="launch__ring" aria-hidden="true"></span>
            <div class="launch__stage">
                <span class="launch__badge"><?= icon('rocket', 'icon--sm') ?> <span data-launch-badge>Launching 6 November 2026 &middot; 10:00 AM</span></span>
                <p class="launch__kicker" data-launch-kicker>The countdown has begun</p>
                <div class="launch__count" data-launch-count role="timer" aria-live="off" aria-label="Time until the launch">
                    <div class="launch__unit"><strong data-launch-d>00</strong><span>Days</span></div>
                    <div class="launch__unit"><strong data-launch-h>00</strong><span>Hours</span></div>
                    <div class="launch__unit"><strong data-launch-m>00</strong><span>Minutes</span></div>
                    <div class="launch__unit launch__unit--sec"><strong data-launch-s>00</strong><span>Seconds</span></div>
                </div>
                <p class="launch__final" data-launch-final aria-hidden="true" hidden>10</p>
                <p class="launch__live" data-launch-live hidden>We are live!</p>
            </div>
        </div>

        <div class="launch__body">
            <h2 class="launch__title" id="launch-title">&#127757;&#127757; <?= e((string) $lp['title']) ?></h2>
            <?php foreach ((array) $lp['paragraphs'] as $i => $para): ?><p class="<?= $i === 0 ? 'launch__lead' : '' ?>"><?= e($para) ?></p><?php endforeach; ?>
            <p class="launch__explore">&#128640; <strong><?= e((string) $lp['explore']) ?></strong><a href="<?= e((string) $lp['site_url']) ?>"><?= e((string) $lp['site_url']) ?></a></p>
            <p>&#129309; <?= e((string) $lp['invite']) ?></p>
            <p class="launch__closing"><?= e((string) $lp['closing']) ?></p>
            <p class="launch__sign"><strong><?= e((string) $lp['signature'][0]) ?></strong><br><?= e((string) $lp['signature'][1]) ?></p>
            <p class="launch__tags"><?= e(implode(' ', (array) $lp['hashtags'])) ?></p>
            <?php if (!$lpGate): ?>
            <div class="launch__actions">
                <button class="btn btn--cta btn--caps" type="button" data-launch-close><?= icon('arrow-right', 'icon--sm') ?> Explore the website</button>
                <a class="btn btn--outline btn--caps" href="<?= e(url('involvement')) ?>" data-launch-close-link>Partner with us</a>
            </div>
            <?php else: ?>
            <p class="launch__gate-note"><?= icon('clock', 'icon--sm') ?> The website opens automatically at launch time. Keep this page open.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
<script src="<?= e(asset('js/launch.js')) ?>" defer></script>
