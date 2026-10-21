<?php
/**
 * The longer story of the foundation, shown on the About page: the challenge we address, why digital inclusion matters, the founder story, our commitment
 * and the background. The wording is the registry page "home" (Admin > Pages > Home, sections 10a-10e), which is where it has always been edited; the home page itself
 * now carries only highlights. The wrapper carries the class "page-home" so the narrative keeps the layout styles it has always had (they are scoped to it).
 * No variables.
 */
$nvAny = false;
foreach (['challenge', 'why_digital', 'founder', 'impact', 'background'] as $nvKey) {
    $nvAny = $nvAny || section_on('home', $nvKey);
}
if (!$nvAny) {
    return;
}
?>
<div class="page-home about-narrative">
<?php /* ---- THE CHALLENGE (white card on a cream background) */ ?>
<?php
$nvChImg = content_img('home', 'challenge.image');
section_begin('home', 'challenge', ['id' => 'about-challenge', 'class' => 'section--tint home-challenge', 'labelledby' => 'challenge-title']);
?>
    <div class="home-challenge__card<?= $nvChImg['has'] ? ' home-challenge__card--photo' : '' ?>" data-reveal>
        <div class="home-challenge__head">
            <?php if (trim((string) content('home', 'challenge.eyebrow')) !== ''): ?><span class="eyebrow"><?= e(content('home', 'challenge.eyebrow')) ?></span><?php endif; ?>
            <h2 class="home-challenge__title" id="challenge-title"><?= highlight((string) content('home', 'challenge.title')) ?></h2>
        </div>
        <div class="prose home-challenge__body"><?= content_html('home', 'challenge.body') ?></div>
        <?php if ($nvChImg['has']): ?><figure class="home-challenge__media"><?= photo_tag($nvChImg) ?></figure><?php endif; ?>
    </div>
<?php section_end(); ?>

<?php /* ---- WHY DIGITAL INCLUSION MATTERS */ ?>
<?php
$nvWhyImg = content_img('home', 'why_digital.image');
$nvWhyClosing = trim((string) content('home', 'why_digital.closing'));
$nvWhyHead = partial('partials/section-head', ['eyebrow' => content('home', 'why_digital.eyebrow'), 'title' => content('home', 'why_digital.title'), 'lead' => content('home', 'why_digital.lead'), 'id' => 'why-title', 'align' => $nvWhyImg['has'] ? 'left' : 'center']);
section_begin('home', 'why_digital', ['id' => 'about-why', 'class' => 'home-why', 'labelledby' => 'why-title'] + ($nvWhyImg['has'] ? [] : ['container' => 'container--narrow']));
?>
    <?php if ($nvWhyImg['has']): ?>
        <div class="split split--start">
            <div data-reveal>
                <?= $nvWhyHead ?>
                <div class="prose"><?= content_html('home', 'why_digital.body') ?></div>
            </div>
            <figure class="split__media split__media--frame" data-reveal><?= photo_tag($nvWhyImg) ?></figure>
        </div>
    <?php else: ?>
        <div data-reveal><?= $nvWhyHead ?></div>
        <div class="prose prose--wide home-why__body" data-reveal><?= content_html('home', 'why_digital.body') ?></div>
    <?php endif; ?>
    <?php if ($nvWhyClosing !== ''): ?><div class="callout mt-6" data-reveal><?= nl2p($nvWhyClosing) ?></div><?php endif; ?>
<?php section_end(); ?>

<?php /* ---- FOUNDER STORY (arch portrait + story) */ ?>
<?php
$nvFounderImg = content_img('home', 'founder.image');
$nvFBtn = content_btn('home', 'founder.cta');
$nvFCaption = trim((string) content('home', 'founder.caption'));
section_begin('home', 'founder', ['id' => 'about-founder', 'class' => 'section--cool home-founder', 'labelledby' => 'founder-title']);
?>
    <div class="split split--start<?= $nvFounderImg['has'] ? '' : ' home-founder__solo' ?>">
        <?php if ($nvFounderImg['has']): ?>
            <figure class="split__media split__media--arch home-founder__photo" data-reveal>
                <?= photo_tag($nvFounderImg) ?>
                <?php if ($nvFCaption !== ''): ?><figcaption><?= e($nvFCaption) ?></figcaption><?php endif; ?>
            </figure>
        <?php endif; ?>
        <div data-reveal>
            <?= partial('partials/section-head', ['eyebrow' => content('home', 'founder.eyebrow'), 'title' => content('home', 'founder.title'), 'id' => 'founder-title', 'class' => 'mb-0']) ?>
            <div class="prose mt-4"><?= content_html('home', 'founder.body') ?></div>
            <?php if ($nvFBtn['has']): ?>
                <p class="mt-5"><a class="btn btn--outline btn--caps" href="<?= e($nvFBtn['url']) ?>"<?= $nvFBtn['external'] ? ' target="_blank" rel="noopener noreferrer"' : '' ?>><?= e($nvFBtn['label']) ?> <?= icon($nvFBtn['external'] ? 'external-link' : 'arrow-right', $nvFBtn['external'] ? 'icon--sm' : 'icon--arrow') ?></a></p>
            <?php endif; ?>
        </div>
    </div>
<?php section_end(); ?>

<?php /* ---- OUR COMMITMENT (deep navy band) */ ?>
<?php
$nvImpImg = content_img('home', 'impact.image');
section_begin('home', 'impact', ['id' => 'about-commitment', 'class' => 'section--navy home-impact', 'labelledby' => 'commitment-title']);
?>
    <?php if ($nvImpImg['has']): ?>
        <div class="split">
            <div data-reveal>
                <?= partial('partials/section-head', ['eyebrow' => content('home', 'impact.eyebrow'), 'title' => content('home', 'impact.title'), 'id' => 'commitment-title', 'class' => 'mb-0']) ?>
                <div class="prose mt-4"><?= content_html('home', 'impact.body') ?></div>
            </div>
            <figure class="split__media" data-reveal><?= photo_tag($nvImpImg) ?></figure>
        </div>
    <?php else: ?>
        <div class="home-impact__inner" data-reveal>
            <?= partial('partials/section-head', ['eyebrow' => content('home', 'impact.eyebrow'), 'title' => content('home', 'impact.title'), 'id' => 'commitment-title', 'align' => 'center', 'class' => 'mb-0']) ?>
            <div class="prose prose--wide mt-4"><?= content_html('home', 'impact.body') ?></div>
        </div>
    <?php endif; ?>
<?php section_end(); ?>

<?php /* ---- BACKGROUND AND RATIONALE */ ?>
<?php
$nvBgClosing = trim((string) content('home', 'background.closing'));
section_begin('home', 'background', ['id' => 'about-background', 'class' => 'home-background', 'container' => 'container--narrow', 'labelledby' => 'background-title']);
?>
    <div data-reveal><?= partial('partials/section-head', ['eyebrow' => content('home', 'background.eyebrow'), 'title' => content('home', 'background.title'), 'id' => 'background-title', 'class' => 'mb-0']) ?></div>
    <div class="prose prose--wide mt-5" data-reveal><?= content_html('home', 'background.body') ?></div>
    <?php if ($nvBgClosing !== ''): ?><div class="callout mt-6" data-reveal><?= nl2p($nvBgClosing) ?></div><?php endif; ?>
<?php section_end(); ?>
</div>
