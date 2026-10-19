<?php
/**
 * ABOUT PAGE > "Our partners" section. OWNER: PARTNERS agent (docs/PARTNERS_NEWSLETTER.md section 7). Included by web/about/index.php just above
 * the donate call to action. The organisations are managed in Admin > Partners (add, delete, hide, reorder, logo); the heading, intro, "most logos"
 * limit and the link to /partners are registry fields of the About page (Admin > Pages > About > "Our partners").
 *
 * - NOTHING at all (not even a heading) while no partner is active; the owner can also switch the whole section off (registry toggle).
 * - Logos: a responsive wall of uniform boxes (partials/partner-grid). Each logo links to the partner's website (new tab) when one is set.
 * - Grouped by type (Partners / Funders / Donors / Collaborators, headings = Admin > Pages > Partners) ONLY when more than one type is shown.
 * - At most `limit` logos (default 24). The "See all partners" link is shown whenever its text is set and the Partners page is published (not only
 *   when the limit is exceeded: it is also the place with the descriptions); empty its text to hide it.
 */

use App\Core\SitePages;
use App\Models\Partner;

try {
    $apRows = Partner::active();                       // already ordered: sort_order, id
} catch (\Throwable) {
    $apRows = [];                                      // a missing table must never take the About page down
}
if ($apRows === []) {
    return;
}

$apLimit = max(4, min(60, (int) content('about', 'partners.limit')));
$apRows = array_slice($apRows, 0, $apLimit);

$apTypes = ['partner', 'funder', 'donor', 'collaborator'];
$apGroups = [];
foreach ($apRows as $apRow) {
    $apType = in_array((string) $apRow['partner_type'], $apTypes, true) ? (string) $apRow['partner_type'] : 'partner';
    $apGroups[$apType][] = $apRow;
}
$apGrouped = count($apGroups) > 1;

$apTitle = trim((string) content('about', 'partners.title'));
$apEyebrow = trim((string) content('about', 'partners.eyebrow'));
$apIntro = trim((string) content('about', 'partners.intro'));
$apAll = content_btn('about', 'partners.all_cta');
$apShowAll = $apAll['has'] && SitePages::isPublished('partners');

section_begin('about', 'partners', ['class' => 'section--tint about-partners', 'labelledby' => $apTitle !== '' ? 'about-partners-title' : null, 'attrs' => $apTitle === '' ? ['aria-label' => $apEyebrow !== '' ? $apEyebrow : 'Our partners'] : []]); ?>
    <?= partial('partials/section-head', ['eyebrow' => $apEyebrow, 'title' => $apTitle, 'lead' => $apIntro, 'align' => 'center', 'id' => 'about-partners-title']) ?>

    <?php if ($apGrouped): ?>
        <div class="partner-groups">
            <?php foreach ($apTypes as $apType): if (empty($apGroups[$apType])) { continue; }
                $apGroupTitle = trim((string) content('partners', $apType . '.title')); ?>
                <div class="partner-group">
                    <?php if ($apGroupTitle !== ''): ?><h3 class="partner-group__title" id="about-partners-<?= e($apType) ?>"><?= e($apGroupTitle) ?></h3><?php endif; ?>
                    <?= partial('partials/partner-grid', ['partners' => $apGroups[$apType], 'labelledby' => $apGroupTitle !== '' ? 'about-partners-' . $apType : '', 'label' => $apGroupTitle]) ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <?= partial('partials/partner-grid', ['partners' => $apRows, 'labelledby' => $apTitle !== '' ? 'about-partners-title' : '', 'label' => $apTitle]) ?>
    <?php endif; ?>

    <?php if ($apShowAll): ?>
        <p class="partner-wall-more"><a class="btn btn--outline btn--caps" href="<?= e($apAll['url']) ?>"><?= e($apAll['label']) ?> <?= icon('arrow-right', 'icon--arrow') ?></a></p>
    <?php endif; ?>
<?php section_end(); ?>
