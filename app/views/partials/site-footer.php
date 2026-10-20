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
try {
    $ftPartners = Partner::active();
    $ftPrograms = (bool) content('global', 'footer.show_programs') ? Program::active(6) : [];
} catch (Throwable) {
    $ftPartners = $ftPrograms = [];             // a missing table must never take the whole site down
}
?>
<div class="footer-pre">
    <div class="container">
        <?= partial('partials/newsletter-row', ['idp' => 'footer-newsletter', 'source' => 'footer']) ?>
    </div>
</div>

<footer class="site-footer">
    <div class="site-footer__grid">
        <div class="site-footer__brand">
            <img class="site-footer__logo" src="<?= e(logo_url(60)) ?>" alt="" width="64" height="64" loading="lazy">
            <span class="site-footer__name"><?= e($ftName) ?></span>
            <?php if ($ftTag !== ''): ?><span class="site-footer__tagline"><?= e($ftTag) ?></span><?php endif; ?>
            <?php if ($ftBlurb !== ''): ?><p class="site-footer__blurb"><?= e($ftBlurb) ?></p><?php endif; ?>
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

        <nav aria-label="<?= e(content('global', 'footer.links_title')) ?>">
            <h2 class="site-footer__title"><?= e(content('global', 'footer.links_title')) ?></h2>
            <ul class="site-footer__links<?= count($ftLinks) > 7 ? ' site-footer__links--cols' : '' ?>">
                <?php foreach ($ftLinks as $f): ?>
                    <li><a href="<?= e($f['url']) ?>"><?= e($f['label']) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </nav>

        <?php if ($ftPrograms): ?>
            <nav aria-label="<?= e(content('global', 'footer.programs_title')) ?>">
                <h2 class="site-footer__title"><?= e(content('global', 'footer.programs_title')) ?></h2>
                <ul class="site-footer__links">
                    <?php foreach ($ftPrograms as $pr): ?>
                        <li><a href="<?= e(url('programs/' . $pr['slug'])) ?>"><?= e($pr['title']) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </nav>
        <?php endif; ?>

        <div>
            <h2 class="site-footer__title"><?= e(content('global', 'footer.contact_title')) ?></h2>
            <ul class="site-footer__contact">
                <?php if ($ftAddress !== ''): ?><li><?= icon('map-pin') ?><span><?= e($ftAddress) ?></span></li><?php endif; ?>
                <?php foreach ($ftEmails as $em): ?><li><?= icon('mail') ?><a href="mailto:<?= e($em) ?>"><?= e($em) ?></a></li><?php endforeach; ?>
                <?php foreach ($ftPhones as $ph): ?><li><?= icon('phone') ?><a href="<?= e(phone_href($ph)) ?>"><?= e($ph) ?></a></li><?php endforeach; ?>
                <?php if ($ftWa !== ''): ?><li><?= icon('whatsapp') ?><a href="<?= e($ftWa) ?>" target="_blank" rel="noopener noreferrer"><?= e(content('global', 'footer.whatsapp_label')) ?></a></li><?php endif; ?>
            </ul>
        </div>
    </div>
    <div class="site-footer__bottom">
        <div>
            <span><?= e($ftCopyright) ?></span>
            <?php $ftRight = array_filter([$ftRegNo !== '' && $ftRegLabel !== '' ? $ftRegLabel . ' ' . $ftRegNo : '', $ftCredit]); ?>
            <?php if ($ftRight): ?><span><?= e(implode(' · ', $ftRight)) ?></span><?php endif; ?>
            <?= partial('partials/pwa-install', ['part' => 'button']) ?>
        </div>
    </div>
</footer>
