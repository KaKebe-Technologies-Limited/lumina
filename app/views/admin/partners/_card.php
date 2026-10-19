<?php
/**
 * ONE partner card of the Partners screen (a grid item). OWNER: PARTNERS agent (docs/PARTNERS_NEWSLETTER.md section 6).
 * Rendered by admin/partners/index.php AND by Admin\PartnerApiController (every write answers with the fresh card as `html`), so the screen never
 * rebuilds this markup in JavaScript. The logo sits in the SAME box class (.plogo) as the website, so what you see is what visitors see.
 * Vars: $p (partners row)*, $types (partner_type => label), $canDelete (bool).
 * JS hooks (assets/js/admin-partners.js): data-pa-card, data-pa-select, data-pa-handle, data-pa-toggle, data-pa-move="earlier|later", data-pa-edit,
 * data-pa-replace, data-pa-remove-logo, data-pa-delete, the inline form [data-pa-edit-form].
 */
use App\Services\PartnerLogos;

$pcId = (int) $p['id'];
$pcName = (string) $p['name'];
$pcLogo = PartnerLogos::view($p);
$pcOn = (int) $p['is_active'] === 1;
$pcSite = (string) ($p['website'] ?? '');
$pcSiteOk = preg_match('~^https?://[^\s"\'<>]+$~i', $pcSite) === 1;
$pcHost = rtrim((string) preg_replace('~^https?://(www\.)?~i', '', $pcSite), '/');
$pcType = (string) $p['partner_type'];
$pcTypeLabel = ($types ?? [])[$pcType] ?? label_from($pcType);
$pcLegacy = $pcLogo['has'] && empty($pcLogo['missing']) && !$pcLogo['optimised'] && !preg_match('~^(assets/|https?://)~i', (string) ($p['logo'] ?? ''));
?>
<li class="pa-card<?= $pcOn ? '' : ' is-off' ?>" data-pa-card data-id="<?= $pcId ?>" data-name="<?= e($pcName) ?>" data-active="<?= $pcOn ? '1' : '0' ?>" data-type="<?= e($pcType) ?>">
    <div class="pa-card__bar">
        <label class="pa-check">
            <input type="checkbox" data-pa-select aria-label="Select <?= e($pcName) ?>">
            <span class="pa-check__box" aria-hidden="true"><?= icon('check', 'icon--sm') ?></span>
        </label>
        <span class="pa-card__pos" data-pa-pos title="Position on the website" aria-hidden="true"></span>
        <button class="pa-handle" type="button" data-pa-handle aria-label="Reorder <?= e($pcName) ?>: drag it, or press the arrow keys" title="Drag to reorder (or use the arrow keys)"><?= icon('grip') ?></button>
    </div>

    <div class="pa-card__logo plogo" data-pa-logo>
        <?php if ($pcLogo['has'] && empty($pcLogo['missing'])): ?>
            <?= partial('partials/partner-logo-img', ['partner' => $p, 'loading' => 'lazy']) ?>
        <?php else: ?>
            <span class="plogo__name"><?= e($pcName) ?></span>
        <?php endif; ?>
        <span class="pa-card__busy" data-pa-busy hidden><span class="pa-spinner" aria-hidden="true"></span><span class="visually-hidden">Working&hellip;</span></span>
        <span class="pa-card__drop" aria-hidden="true"><?= icon('upload') ?> Drop to replace the logo</span>
    </div>
    <?php if (!$pcLogo['has']): ?>
        <p class="pa-card__nologo">No logo yet: the website shows the name. Use <strong>Replace logo</strong> to add one.</p>
    <?php elseif (!empty($pcLogo['missing'])): ?>
        <p class="pa-card__legacy" data-pa-missing title="The picture file of this logo is no longer on the server (it was deleted, or the site was moved without its uploads folder). The website shows the name until a new logo is uploaded.">
            <?= icon('alert-triangle', 'icon--sm') ?> Logo file missing: upload it again
        </p>
    <?php elseif ($pcLegacy): ?>
        <p class="pa-card__legacy" title="This logo was added before logos were optimised automatically. Use &quot;Optimise existing logos&quot; at the top of the page, or replace the logo.">
            <?= icon('alert-triangle', 'icon--sm') ?> Not optimised yet
        </p>
    <?php endif; ?>

    <div class="pa-card__main">
        <h3 class="pa-card__name"><a href="<?= e(url('admin/partners/' . $pcId . '/edit')) ?>" data-pa-name title="Open the full form (description, position)"><?= e($pcName) ?></a></h3>
        <p class="pa-card__meta">
            <span class="badge badge--info" data-pa-type><?= e($pcTypeLabel) ?></span>
            <?php if ($pcSiteOk): ?>
                <a class="pa-card__site" href="<?= e($pcSite) ?>" target="_blank" rel="noopener noreferrer" data-pa-site><?= e($pcHost) ?><span class="visually-hidden"> (opens the website in a new tab)</span></a>
            <?php else: ?>
                <span class="pa-card__site pa-card__site--none" data-pa-site>No website</span>
            <?php endif; ?>
        </p>
    </div>

    <form class="pa-card__edit" data-pa-edit-form hidden novalidate>
        <div class="field">
            <label class="field__label" for="pa-name-<?= $pcId ?>">Name</label>
            <input class="input" id="pa-name-<?= $pcId ?>" name="name" value="<?= e($pcName) ?>" maxlength="190" required autocomplete="off">
            <p class="field__error" data-pa-error="name" hidden></p>
        </div>
        <div class="field">
            <label class="field__label" for="pa-site-<?= $pcId ?>">Website</label>
            <input class="input" id="pa-site-<?= $pcId ?>" name="website" value="<?= e($pcSite) ?>" maxlength="255" inputmode="url" placeholder="https://www.example.org" autocomplete="off">
            <p class="field__error" data-pa-error="website" hidden></p>
        </div>
        <div class="field">
            <label class="field__label" for="pa-type-<?= $pcId ?>">Type</label>
            <select class="select" id="pa-type-<?= $pcId ?>" name="partner_type">
                <?php foreach (($types ?? []) as $tv => $tl): ?><option value="<?= e($tv) ?>"<?= $pcType === $tv ? ' selected' : '' ?>><?= e($tl) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="pa-card__edit-actions">
            <button class="btn btn--primary btn--sm" type="submit"><?= icon('check', 'icon--sm') ?> Save</button>
            <button class="btn btn--ghost btn--sm" type="button" data-pa-edit-cancel>Cancel</button>
            <a class="pa-card__more" href="<?= e(url('admin/partners/' . $pcId . '/edit')) ?>">More options</a>
        </div>
    </form>

    <div class="pa-card__foot">
        <label class="switch pa-switch" title="<?= $pcOn ? 'Click to hide this partner from the website' : 'Click to show this partner on the website' ?>">
            <input type="checkbox" role="switch" data-pa-toggle<?= $pcOn ? ' checked' : '' ?> aria-label="Show <?= e($pcName) ?> on the website">
            <span class="switch__track" aria-hidden="true"></span>
            <span class="switch__text" data-pa-switch-text><?= $pcOn ? 'On website' : 'Hidden' ?></span>
        </label>
        <span class="pa-tools">
            <button class="pa-tool" type="button" data-pa-move="earlier" aria-label="Move <?= e($pcName) ?> earlier" title="Move earlier"><?= icon('arrow-left', 'icon--sm') ?></button>
            <button class="pa-tool" type="button" data-pa-move="later" aria-label="Move <?= e($pcName) ?> later" title="Move later"><?= icon('arrow-right', 'icon--sm') ?></button>
            <button class="pa-tool" type="button" data-pa-edit aria-expanded="false" aria-label="Edit name, website and type of <?= e($pcName) ?>" title="Edit name, website, type"><?= icon('edit', 'icon--sm') ?></button>
            <button class="pa-tool" type="button" data-pa-replace aria-label="<?= $pcLogo['has'] ? 'Replace' : 'Add' ?> the logo of <?= e($pcName) ?>" title="<?= $pcLogo['has'] ? 'Replace logo' : 'Add logo' ?>"><?= icon('image', 'icon--sm') ?></button>
            <?php if ($pcLogo['has']): ?>
                <button class="pa-tool" type="button" data-pa-remove-logo aria-label="Remove the logo of <?= e($pcName) ?>" title="Remove logo (the name is shown instead)"><?= icon('x', 'icon--sm') ?></button>
            <?php endif; ?>
            <?php if (!empty($canDelete)): ?>
                <button class="pa-tool pa-tool--danger" type="button" data-pa-delete aria-label="Delete <?= e($pcName) ?>" title="Delete"><?= icon('trash', 'icon--sm') ?></button>
            <?php endif; ?>
        </span>
    </div>
</li>
