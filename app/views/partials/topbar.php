<?php
/**
 * Thin gold strip above the header: address (left), e-mail + phone + quick link (right). Data comes from Settings (contact details),
 * the strip itself and its quick link from the registry page "Site-wide content" > Top bar (switch it off to hide it everywhere).
 * The live clock (registry page "global" > Clock; App\Core\SiteClock renders the first paint, the [data-clock] ticker in main.js keeps it
 * running) sits in the left group. No variables.
 */
if (!section_on('global', 'topbar')) {
    return;
}
$tbAddress = implode(', ', array_filter([trim((string) setting('address', '')), trim((string) setting('city_country', ''))]));
$tbEmail = trim((string) setting('email_primary', ''));
$tbPhone = trim((string) setting('phone_1', ''));
$tbShowAddress = (bool) content('global', 'topbar.show_address') && $tbAddress !== '';
$tbShowEmail = (bool) content('global', 'topbar.show_email') && $tbEmail !== '';
$tbShowPhone = (bool) content('global', 'topbar.show_phone') && $tbPhone !== '';
$tbType = (string) content('global', 'topbar.link_type');
$tbLabel = trim((string) content('global', 'topbar.link_label'));
$tbHref = '';
$tbIcon = 'heart';
if ($tbType === 'whatsapp') {
    $tbHref = whatsapp_url();
    $tbIcon = 'whatsapp';
} elseif ($tbType === 'donate') {
    $tbHref = url('donate');
}
$tbShowLink = $tbHref !== '' && $tbLabel !== '';
$tbClock = \App\Core\SiteClock::forView();
// the learner portal: "My learner portal" for a browser that has (had) a learner account (a small marker cookie set at sign-in), otherwise "Learner login"
$tbPortal = (string) setting('academy_enabled', '1') === '1';
$tbKnows = $tbPortal && \App\Services\Academy\LearnerAuth::knowsPortal();
if (!$tbShowAddress && !$tbShowEmail && !$tbShowPhone && !$tbShowLink && $tbClock === null) {
    return;
}
?>
<div class="topbar<?= $tbClock !== null ? ' topbar--clock' : '' ?>">
    <div class="topbar__inner">
        <div class="topbar__group">
            <?php if ($tbShowAddress): ?><span class="topbar__item topbar__item--address"><?= icon('map-pin') ?><span><?= e($tbAddress) ?></span></span><?php endif; ?>
            <?php if ($tbClock !== null): ?>
            <div class="clock<?= $tbClock['show_date'] ? ' clock--date' : '' ?><?= $tbClock['seconds'] ? ' clock--sec' : '' ?><?= $tbClock['hour12'] ? ' clock--12h' : '' ?>" role="timer" aria-live="off"<?= $tbClock['aria_label'] !== '' ? ' aria-label="' . e($tbClock['aria_label']) . '"' : '' ?> data-clock data-tz="<?= e($tbClock['tz']) ?>" data-mode="<?= e($tbClock['mode']) ?>" data-hour12="<?= $tbClock['hour12'] ? '1' : '0' ?>" data-seconds="<?= $tbClock['seconds'] ? '1' : '0' ?>" data-date="<?= $tbClock['show_date'] ? '1' : '0' ?>" data-abbr="<?= e($tbClock['abbr']) ?>" data-offset="<?= (int) $tbClock['offset'] ?>" data-epoch="<?= (int) $tbClock['epoch'] ?>">
                <?= icon('clock') ?>
                <?php if ($tbClock['label'] !== ''): ?><span class="clock__label" data-clock-label><?= e($tbClock['label']) ?></span><?php endif; ?>
                <time class="clock__time" datetime="<?= e($tbClock['iso']) ?>" data-clock-time>
                    <?php if ($tbClock['show_date']): ?><span class="clock__date" data-clock-date><?= e($tbClock['date']) ?></span><span class="clock__year" data-clock-year> <?= e($tbClock['year']) ?></span><span class="clock__sep" aria-hidden="true">&middot;</span><?php endif; ?>
                    <span class="clock__hms" data-clock-hms><?= e($tbClock['time']) ?></span> <span class="clock__tz" data-clock-tz><?= e($tbClock['abbr']) ?></span>
                </time>
            </div>
            <?php endif; ?>
        </div>
        <div class="topbar__group">
            <?php if ($tbShowEmail): ?><a class="topbar__item topbar__item--email" href="mailto:<?= e($tbEmail) ?>"><?= icon('mail') ?><span><?= e($tbEmail) ?></span></a><?php endif; ?>
            <?php if ($tbShowPhone): ?><a class="topbar__item topbar__item--phone" href="<?= e(phone_href($tbPhone)) ?>"><?= icon('phone') ?><span><?= e($tbPhone) ?></span></a><?php endif; ?>
            <?php if ($tbPortal): ?><a class="topbar__item topbar__item--portal" href="<?= e(url($tbKnows ? 'academy/dashboard' : 'academy/login')) ?>"><?= icon('user') ?><span><?= $tbKnows ? 'My learner portal' : 'Learner login' ?></span></a><?php endif; ?>
            <?php if ($tbShowLink): ?><a class="topbar__item topbar__item--link" href="<?= e($tbHref) ?>"<?= is_external($tbHref) ? ' target="_blank" rel="noopener noreferrer"' : '' ?>><?= icon($tbIcon) ?><span><?= e($tbLabel) ?></span></a><?php endif; ?>
        </div>
    </div>
</div>
