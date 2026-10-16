<?php
/**
 * Deep navy donation panel: amount field + quick-amount chips + two buttons + photo collage.
 * IT IS NOT A PAYMENT FORM. "Pledge" is a GET to /donate with ?amount=&currency= (the Donate page prefills its pledge form from them via
 * donate_prefill()); "Donate online" goes to the donation link in Settings (GoFundMe). Hidden entirely when the registry switch
 * "Donation panel" (Site-wide content) is off. All copy comes from the registry page "global" > donate_panel; every var below is an
 * optional override: $eyebrow, $title, $text, $class, and $images (list of content_img() results that replace the site-wide collage
 * photos on one page; when none of them has a photo the site-wide ones are used).
 *   <section class="section" id="donate-panel"><div class="container"><?= partial('partials/donate-panel') ?></div></section>
 */
if (!section_on('global', 'donate_panel')) {
    return;
}
$dpTitle = $title ?? (string) content('global', 'donate_panel.title');
$dpEyebrow = $eyebrow ?? (string) content('global', 'donate_panel.eyebrow');
$dpText = $text ?? (string) content('global', 'donate_panel.text');
$dpCurrency = in_array((string) content('global', 'donate_panel.currency'), ['UGX', 'USD', 'EUR', 'GBP'], true) ? (string) content('global', 'donate_panel.currency') : 'USD';
$dpAmounts = [];
foreach (content_list('global', 'donate_panel.amounts') as $row) {
    $n = (float) ($row['amount'] ?? 0);
    if ($n >= 1 && $n <= 1000000000) {
        $dpAmounts[] = rtrim(rtrim(number_format($n, 2, '.', ''), '0'), '.');
    }
}
$dpImages = array_values(array_filter([content_img('global', 'donate_panel.image1'), content_img('global', 'donate_panel.image2'), content_img('global', 'donate_panel.image3')], static fn(array $i): bool => $i['has']));
if (!empty($images) && is_array($images)) {
    $dpOwn = array_values(array_filter($images, static fn($i): bool => is_array($i) && !empty($i['has']) && !empty($i['src'])));
    if ($dpOwn) {
        $dpImages = $dpOwn;
    }
}
$dpOnline = trim((string) setting('donate_url', ''));
$dpOnlineLabel = trim((string) content('global', 'donate_panel.online_label'));
$dpNote = trim((string) content('global', 'donate_panel.note'));
$dpId = 'donate-panel-' . substr(md5((string) $dpTitle), 0, 5);
?>
<div class="donate-panel<?= !empty($class) ? ' ' . e($class) : '' ?>" data-reveal>
    <div class="donate-panel__body">
        <?php if (trim((string) $dpEyebrow) !== ''): ?><span class="eyebrow"><?= e($dpEyebrow) ?></span><?php endif; ?>
        <h2 class="donate-panel__title"><?= highlight((string) $dpTitle) ?></h2>
        <?php if (trim((string) $dpText) !== ''): ?><p class="donate-panel__text"><?= e($dpText) ?></p><?php endif; ?>

        <form class="donate-panel__form" method="get" action="<?= e(url('donate')) ?>#pledge" data-donate-panel data-no-guard>
            <input type="hidden" name="currency" value="<?= e($dpCurrency) ?>">
            <label class="donate-panel__label" for="<?= e($dpId) ?>-amount"><?= e(content('global', 'donate_panel.amount_label')) ?></label>
            <div class="donate-panel__field">
                <span class="donate-panel__currency" aria-hidden="true"><?= e($dpCurrency) ?></span>
                <input class="donate-panel__input" id="<?= e($dpId) ?>-amount" name="amount" type="text" inputmode="decimal" autocomplete="off" required
                       pattern="[0-9][0-9 ,]*(\.[0-9]{1,2})?" maxlength="14" placeholder="<?= e(content('global', 'donate_panel.amount_placeholder')) ?>" data-donate-input
                       aria-describedby="<?= e($dpId) ?>-cur">
                <span class="visually-hidden" id="<?= e($dpId) ?>-cur"><?= e($dpCurrency) ?></span>
            </div>
            <?php if ($dpAmounts): ?>
                <div class="donate-panel__chips" role="group" aria-label="<?= e(content('global', 'donate_panel.amount_label')) ?>">
                    <?php foreach ($dpAmounts as $amt): ?>
                        <button class="donate-panel__chip" type="button" data-donate-chip data-amount="<?= e($amt) ?>" aria-pressed="false"><?= e(number_format((float) $amt, str_contains($amt, '.') ? 2 : 0)) ?></button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <div class="donate-panel__actions">
                <button class="btn btn--cta btn--caps" type="submit"><?= e(content('global', 'donate_panel.pledge_label')) ?></button>
                <?php if ($dpOnline !== '' && $dpOnlineLabel !== ''): ?>
                    <a class="btn btn--outline-light btn--caps" href="<?= e($dpOnline) ?>" target="_blank" rel="noopener noreferrer"><?= e($dpOnlineLabel) ?> <?= icon('external-link', 'icon--sm') ?></a>
                <?php endif; ?>
            </div>
            <?php if ($dpNote !== ''): ?><p class="donate-panel__note"><?= e($dpNote) ?></p><?php endif; ?>
        </form>
    </div>
    <?php if ($dpImages): ?>
        <div class="donate-panel__media">
            <?php foreach (array_slice($dpImages, 0, 3) as $im): ?>
                <figure class="donate-panel__photo"><img src="<?= e($im['src']) ?>" alt="<?= e($im['alt']) ?>" loading="lazy" decoding="async"></figure>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
