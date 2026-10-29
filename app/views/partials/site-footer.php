<?php
/**
 * Site footer (rendered by layouts/main.php). No variables. Parts, top to bottom:
 *  1. partners strip (partners table, hidden when empty)   2. newsletter row (POST /subscribe)
 *  3. deep navy footer: logo + blurb + socials | quick links (Nav::footer()) | programs (programs table) | contact | copyright row.
 * Copy: registry "Site-wide content" > Footer. Contact + socials + registration number: Settings.
 */

use App\Core\Nav;
use App\Models\Partner;
use App\Models\Program;

$ftName = (string) setting('site_name', 'Lumina Africa Foundation');
$ftTag = (string) setting('tagline', '');
$ftRegNo = trim((string) setting('reg_number', ''));
$ftRegLabel = trim((string) content('global', 'footer.reg_label'));
$ftPhones = array_filter([(string) setting('phone_1', ''), (string) setting('phone_2', '')]);
$ftEmails = array_filter([(string) setting('email_primary', ''), (string) setting('email_info', '')]);
$ftWa = whatsapp_url();
$ftAddress = implode(', ', array_filter([trim((string) setting('address', '')), trim((string) setting('city_country', ''))]));
$ftSocial = social_links('footer');
$ftCopyright = str_replace('{year}', date('Y'), (string) content('global', 'footer.copyright'));
$ftCredit = trim((string) setting('footer_credit', ''));
$ftBlurb = trim((string) content('global', 'footer.blurb'));
$ftLinks = Nav::footer();
// the footer links in labelled groups (anything new a page owner switches on lands in the first group); Privacy sits in the bottom bar
$ftGroupMap = [
    'About us'      => ['about', 'team', 'partners', 'reports', 'contact'],
    'What we do'    => ['programs', 'projects', 'activities', 'academy', 'shop'],
    'Get involved'  => ['donate', 'involvement', 'membership', 'opportunities', 'careers', 'involvement/ambassadors', 'ambassadors'],
    'News & media'  => ['news', 'blog', 'stories', 'newsletter', 'events', 'calendar', 'gallery'],
];
$ftLegal = [];
$ftGroups = array_fill_keys(array_keys($ftGroupMap), []);
foreach ($ftLinks as $l) {
    if (in_array($l['slug'], ['privacy', 'terms', 'cookies'], true)) {
        $ftLegal[] = $l;
        continue;
    }
    $placed = false;
    foreach ($ftGroupMap as $label => $slugs) {
        if (in_array($l['slug'], $slugs, true)) {
            $ftGroups[$label][] = $l;
            $placed = true;
            break;
        }
    }
    if (!$placed) {
        $ftGroups['About us'][] = $l;
    }
}
foreach ($ftGroupMap as $label => $slugs) {           // inside a group: the order above, not the page order
    usort($ftGroups[$label], static fn(array $x, array $y): int => (int) array_search($x['slug'], $slugs, true) <=> (int) array_search($y['slug'], $slugs, true));
}
$ftGroups = array_filter($ftGroups);
try {
    $ftPartners = Partner::active();
} catch (Throwable) {
    $ftPartners = [];             // a missing table must never take the whole site down
}
?>
<div class="footer-pre">
    <div class="container">
        <?= partial('partials/newsletter-row', ['idp' => 'footer-newsletter', 'source' => 'footer']) ?>
    </div>
</div>

<footer class="site-footer">
    <div class="site-footer__top">
        <div class="site-footer__brand">
            <img class="site-footer__logo" src="<?= e(logo_url(60)) ?>" alt="" width="64" height="64" loading="lazy">
            <span class="site-footer__name"><?= e($ftName) ?></span>
            <?php if ($ftTag !== ''): ?><span class="site-footer__tagline"><?= e($ftTag) ?></span><?php endif; ?>
            <?php if ($ftBlurb !== ''): ?><p class="site-footer__blurb"><?= e(hero_brief($ftBlurb, 140)) ?></p><?php endif; ?>
            <?php if ($ftSocial): ?>
                <div>
                    <h2 class="visually-hidden"><?= e(content('global', 'footer.social_title')) ?></h2>
                    <ul class="social-list">
                        <?php foreach ($ftSocial as $s): ?>
                            <li><a href="<?= e($s['url']) ?>" target="_blank" rel="noopener noreferrer" aria-label="<?= e($s['label']) ?>"><?= icon($s['icon']) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
        <div class="site-footer__contactbox">
            <h2 class="site-footer__title"><?= e(content('global', 'footer.contact_title')) ?></h2>
            <ul class="site-footer__contact">
                <?php if ($ftAddress !== ''): ?><li><?= icon('map-pin') ?><span><?= e($ftAddress) ?></span></li><?php endif; ?>
                <?php foreach ($ftEmails as $em): ?><li><?= icon('mail') ?><a href="mailto:<?= e($em) ?>"><?= e($em) ?></a></li><?php endforeach; ?>
                <?php foreach ($ftPhones as $ph): ?><li><?= icon('phone') ?><a href="<?= e(phone_href($ph)) ?>"><?= e($ph) ?></a></li><?php endforeach; ?>
                <?php if ($ftWa !== ''): ?><li><?= icon('whatsapp') ?><a href="<?= e($ftWa) ?>" target="_blank" rel="noopener noreferrer"><?= e(content('global', 'footer.whatsapp_label')) ?></a></li><?php endif; ?>
            </ul>
        </div>
    </div>

    <nav class="site-footer__groups" aria-label="<?= e(content('global', 'footer.links_title')) ?>">
        <?php foreach ($ftGroups as $label => $items): ?>
            <div class="site-footer__group">
                <h2 class="site-footer__title"><?= e($label) ?></h2>
                <ul class="site-footer__links">
                    <?php foreach ($items as $f): ?>
                        <li><a href="<?= e($f['url']) ?>"><?= e($f['label']) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endforeach; ?>
    </nav>
    <div class="site-footer__bottom">
        <div>
            <span><?= e($ftCopyright) ?></span>
            <?php $ftRight = array_filter([$ftRegNo !== '' && $ftRegLabel !== '' ? $ftRegLabel . ' ' . $ftRegNo : '', $ftCredit]); ?>
            <?php if ($ftRight): ?><span><?= e(implode(' · ', $ftRight)) ?></span><?php endif; ?>
            <?php foreach ($ftLegal as $lg): ?><a href="<?= e($lg['url']) ?>"><?= e($lg['label']) ?></a><?php endforeach; ?>
            <?= partial('partials/pwa-install', ['part' => 'button']) ?>
        </div>
    </div>
</footer>
