<?php
/**
 * "Search engines & social sharing" card (shared by registry pages and custom pages). OWNER: B3.
 * Vars: $seoTitle, $seoDesc, $ogImage (current/old values), $defTitle, $defDesc, $defOg (what is used when the fields are empty),
 *       $pageUrl (display URL), $siteName, $sectionId (anchor), $open (bool).
 * Field names: seo_title, seo_description, og_image.
 */
$sectionId = $sectionId ?? 'sec-seo';
$shownTitle = $seoTitle !== '' ? $seoTitle : $defTitle;
$shownDesc = $seoDesc !== '' ? $seoDesc : $defDesc;
?>
<section class="card pe-section" id="<?= e($sectionId) ?>" data-pe-section data-section="seo">
    <div class="card__header pe-section__head">
        <h2 class="card__title"><button class="pe-section__toggle" type="button" data-pe-toggle aria-expanded="true" aria-controls="<?= e($sectionId) ?>-body"><?= icon('chevron-down', 'icon--sm') ?><span class="pe-section__label">Search engines and social sharing</span></button></h2>
    </div>
    <div class="card__body pe-section__body" id="<?= e($sectionId) ?>-body">
        <p class="pe-section__help">What appears in Google results and when this page is shared on WhatsApp, Facebook or LinkedIn. Leave a box empty to use the automatic text.</p>
        <div class="pe-seo">
            <div class="pe-seo__fields">
                <div class="field<?= has_error('seo_title') ? ' field--error' : '' ?>">
                    <div class="field__label-row"><label class="field__label" for="f-seo-title">Title shown in search results</label><span class="char-count" data-pe-count="#f-seo-title" data-pe-good="60" aria-live="off"></span></div>
                    <input class="input" type="text" id="f-seo-title" name="seo_title" maxlength="190" value="<?= e($seoTitle) ?>" placeholder="<?= e($defTitle) ?>" data-pe-serp-title aria-describedby="f-seo-title-help">
                    <div class="field__help" id="f-seo-title-help">Around 50 to 60 characters works best. The site name is added automatically.</div>
                    <?php if (has_error('seo_title')): ?><div class="field__error" role="alert"><?= e(error('seo_title')) ?></div><?php endif; ?>
                </div>
                <div class="field<?= has_error('seo_description') ? ' field--error' : '' ?>">
                    <div class="field__label-row"><label class="field__label" for="f-seo-description">Description shown in search results</label><span class="char-count" data-pe-count="#f-seo-description" data-pe-good="160" aria-live="off"></span></div>
                    <textarea class="textarea" id="f-seo-description" name="seo_description" rows="3" maxlength="175" placeholder="<?= e($defDesc) ?>" data-pe-serp-desc aria-describedby="f-seo-description-help"><?= e($seoDesc) ?></textarea>
                    <div class="field__help" id="f-seo-description-help">One or two sentences, about 160 characters (anything over 175 is cut), that make people want to click.</div>
                    <?php if (has_error('seo_description')): ?><div class="field__error" role="alert"><?= e(error('seo_description')) ?></div><?php endif; ?>
                </div>
                <div class="field<?= has_error('og_image') ? ' field--error' : '' ?>">
                    <span class="field__label" id="f-og-image-label">Sharing picture</span>
                    <?= partial('admin/pages/_image', ['name' => 'og_image', 'altName' => '', 'value' => $ogImage, 'alt' => '', 'id' => 'f-og-image', 'label' => 'Sharing picture']) ?>
                    <div class="field__help">Shown as the preview image on social media. A landscape picture around 1200 x 630 pixels works best.<?= $defOg !== '' && $ogImage === '' ? ' Currently the default picture is used.' : '' ?></div>
                    <?php if (has_error('og_image')): ?><div class="field__error" role="alert"><?= e(error('og_image')) ?></div><?php endif; ?>
                </div>
            </div>
            <figure class="pe-serp" aria-label="Preview of the search result">
                <figcaption class="pe-serp__caption">Search result preview</figcaption>
                <div class="pe-serp__card">
                    <span class="pe-serp__url"><?= e($pageUrl) ?></span>
                    <span class="pe-serp__title" data-pe-serp-out-title data-pe-site="<?= e($siteName) ?>"><?= e($shownTitle) ?></span>
                    <span class="pe-serp__desc" data-pe-serp-out-desc><?= e(excerpt($shownDesc, 175)) ?></span>
                </div>
            </figure>
        </div>
    </div>
</section>
