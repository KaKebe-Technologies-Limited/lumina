<?php
/**
 * "Thanks to our partners" strip (site footer). Vars: $partners (list of partners rows)*, $title (heading, from the registry), $limit (most logos
 * to show, default 12), $all_label (text of the link to the Partners page, shown only when some partners are not in the strip; empty hides it).
 * Renders NOTHING when the list is empty. Same logo boxes as the About page (partials/partner-tile, variant "strip"): uniform size, optimised
 * WebP/PNG with 1x/2x, width + height, lazy. Logos are grey until hovered when the setting partners_logo_hover_grey is on (mouse only).
 * It is a labelled <section> (a landmark), so screen-reader users can jump to it.
 */

use App\Core\SitePages;

if (empty($partners)) {
    return;
}
$psTitle = trim((string) ($title ?? ''));
$psGrey = (string) setting('partners_logo_hover_grey', '0') !== '0';
$psLimit = max(1, (int) ($limit ?? 12));
$psTotal = count($partners);
$psShown = array_slice($partners, 0, $psLimit);
$psAllLabel = trim((string) ($all_label ?? ''));
$psMore = $psTotal > count($psShown) && $psAllLabel !== '' && SitePages::isPublished('partners');
?>
<section class="partner-strip"<?= $psTitle !== '' ? ' aria-labelledby="partner-strip-title"' : ' aria-label="Partners"' ?>>
    <?php if ($psTitle !== ''): ?><h2 class="partner-strip__title" id="partner-strip-title"><?= e($psTitle) ?></h2><?php endif; ?>
    <div class="partner-strip__body">
        <ul class="partner-strip__list plogos<?= $psGrey ? ' plogos--grey' : '' ?>" role="list">
            <?php foreach ($psShown as $psPartner): ?>
                <li class="partner-strip__cell"><?= partial('partials/partner-tile', ['partner' => $psPartner, 'variant' => 'strip']) ?></li>
            <?php endforeach; ?>
        </ul>
        <?php if ($psMore): ?>
            <p class="partner-strip__more"><a href="<?= e(url('partners')) ?>"><?= e($psAllLabel) ?> <?= icon('arrow-right', 'icon--arrow') ?></a></p>
        <?php endif; ?>
    </div>
</section>
