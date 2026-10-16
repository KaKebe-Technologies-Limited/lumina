<?php
/**
 * Partnership call to action band at the end of /projects and /projects/{slug}. Copy: content('projects', 'cta.*'); the whole band can be
 * switched off in Admin > Pages > Projects. Reuses the shared .cta look (navy panel, pill buttons).
 */
if (!section_on('projects', 'cta')) {
    return;
}
$pcEyebrow = trim((string) content('projects', 'cta.eyebrow'));
$pcTitle = trim((string) content('projects', 'cta.title'));
$pcText = trim((string) content('projects', 'cta.text'));
$pcBtn1 = content_btn('projects', 'cta.cta1');
$pcBtn2 = content_btn('projects', 'cta.cta2');
if ($pcTitle === '' && $pcText === '' && !$pcBtn1['has'] && !$pcBtn2['has']) {
    return;
}
?>
<div class="cta-wrap pi-cta">
    <div class="container">
        <section class="cta" aria-labelledby="projects-cta-title" data-reveal>
            <?php if ($pcEyebrow !== ''): ?><span class="eyebrow eyebrow--center eyebrow--light"><?= e($pcEyebrow) ?></span><?php endif; ?>
            <?php if ($pcTitle !== ''): ?><h2 class="cta__title" id="projects-cta-title"><?= highlight($pcTitle) ?></h2><?php endif; ?>
            <?php if ($pcText !== ''): ?><p class="cta__text"><?= e($pcText) ?></p><?php endif; ?>
            <?php if ($pcBtn1['has'] || $pcBtn2['has']): ?>
                <div class="cta__actions">
                    <?php if ($pcBtn1['has']): ?><a class="btn btn--cta btn--caps btn--lg" href="<?= e($pcBtn1['url']) ?>"<?= $pcBtn1['external'] ? ' target="_blank" rel="noopener noreferrer"' : '' ?>><?= e($pcBtn1['label']) ?></a><?php endif; ?>
                    <?php if ($pcBtn2['has']): ?><a class="btn btn--outline-light btn--caps btn--lg" href="<?= e($pcBtn2['url']) ?>"<?= $pcBtn2['external'] ? ' target="_blank" rel="noopener noreferrer"' : '' ?>><?= e($pcBtn2['label']) ?></a><?php endif; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>
</div>
