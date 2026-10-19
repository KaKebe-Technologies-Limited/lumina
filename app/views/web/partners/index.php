<?php
/**
 * PARTNERS (/partners) - B-INNER-B restyle of the B7 view.
 * Vars: $groups (partner|funder|donor|collaborator => list of partners rows, website already filtered to http(s)), $total (int).
 * Every visible string is read through content('partners', ...) so the owner can edit it in the admin (Pages > Partners).
 */
$heroImg = content_img('partners', 'hero.image');
$visitLabel = (string) content('partners', 'labels.visit');
$empty = content_btn('partners', 'empty.cta');
$empty2 = content_btn('partners', 'empty.cta2');
$join = content_btn('partners', 'join.cta');
$grey = (string) setting('partners_logo_hover_grey', '1') !== '0';      // logos grey until hovered (mouse only), see components/_partner-logo.scss
$emptyActions = [];
if ($empty['has']) {
    $emptyActions[] = ['label' => $empty['label'], 'url' => $empty['url'], 'class' => 'btn--cta btn--caps'];
}
if ($empty2['has']) {
    $emptyActions[] = ['label' => $empty2['label'], 'url' => $empty2['url'], 'class' => 'btn--outline btn--caps'];
}
?>
<?= partial('partials/page-hero', [
    'eyebrow' => content('partners', 'hero.eyebrow'),
    'title'   => content('partners', 'hero.title'),
    'lead'    => content('partners', 'hero.lead'),
    'image'   => $heroImg['has'] ? ['src' => $heroImg['src'], 'alt' => $heroImg['alt'], 'focus' => '50% 40%'] : null,
    'sheet'   => $total === 0 ? 'tint' : 'white',
]) ?>

<?php if ($total === 0): ?>
    <section class="section section--tint" id="partners-empty">
        <div class="container container--narrow">
            <?= partial('partials/empty-state', [
                'title'   => content('partners', 'empty.title'),
                'text'    => content('partners', 'empty.text'),
                'icon'    => 'handshake',
                'actions' => $emptyActions,
            ]) ?>
        </div>
    </section>
<?php else: ?>
    <?php $shown = 0; foreach (['partner', 'funder', 'donor', 'collaborator'] as $type):
        if (empty($groups[$type])) { continue; }
        $shown++;
        $headingId = 'partners-' . $type . '-title';
        section_begin('partners', $type, ['class' => $shown % 2 === 0 ? 'section--tint' : '', 'labelledby' => $headingId]);
    ?>
        <?= partial('partials/section-head', [
            'eyebrow' => content('partners', $type . '.eyebrow'),
            'title'   => content('partners', $type . '.title'),
            'lead'    => content('partners', $type . '.intro'),
            'id'      => $headingId,
            'align'   => 'center',
        ]) ?>
        <div class="partner-cards plogos<?= $grey ? ' plogos--grey' : '' ?>">
            <?php foreach ($groups[$type] as $partner): ?>
                <?= partial('web/partners/_card', ['partner' => $partner, 'visitLabel' => $visitLabel]) ?>
            <?php endforeach; ?>
        </div>
    <?php section_end(); endforeach; ?>

    <?php section_begin('partners', 'join', ['bleed' => true, 'class' => 'section--band-wrap']); ?>
        <?= partial('web/blog/_band', [
            'title'   => content('partners', 'join.title'),
            'text'    => content('partners', 'join.text'),
            'actions' => [['label' => $join['label'], 'url' => $join['url'], 'external' => $join['external']]],
        ]) ?>
    <?php section_end(); ?>
<?php endif; ?>
