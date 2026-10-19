<?php
/**
 * The "Our partners" logo WALL: a responsive grid of uniform white boxes (2 / 3 / 4 / 5 columns), one logo per box.
 * OWNER: PARTNERS agent. Vars: $partners (list of partners rows)*, $labelledby (id of the heading above, optional), $label (aria-label, optional).
 * Prints NOTHING when the list is empty. Each box comes from partials/partner-tile (link rules, name fallback); the grey-until-hover look is
 * switched by the setting "partners_logo_hover_grey" and only applies to a mouse (scss/components/_partner-logo.scss).
 */
if (empty($partners)) {
    return;
}
$pgGrey = (string) setting('partners_logo_hover_grey', '1') !== '0';
$pgLabelledby = preg_replace('/[^a-z0-9_-]/i', '', (string) ($labelledby ?? ''));
$pgLabel = trim((string) ($label ?? ''));
?>
<ul class="partner-wall plogos<?= $pgGrey ? ' plogos--grey' : '' ?>" role="list"<?= $pgLabelledby !== '' ? ' aria-labelledby="' . e($pgLabelledby) . '"' : ($pgLabel !== '' ? ' aria-label="' . e($pgLabel) . '"' : '') ?>>
    <?php foreach ($partners as $pgPartner): ?>
        <li class="partner-wall__item" data-reveal><?= partial('partials/partner-tile', ['partner' => $pgPartner, 'variant' => 'wall']) ?></li>
    <?php endforeach; ?>
</ul>
