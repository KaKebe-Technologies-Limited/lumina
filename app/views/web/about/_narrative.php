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
<?php /* ---- THE CHALLENGE: a strong opening, a photo collage, the donor's main questions answered, and a call to act (scss/pages/_challenge-story.scss) */ ?>
<?php
$nvChLead = trim((string) content('home', 'challenge.lead'));
$nvChPhotos = array_values(array_filter([content_img('home', 'challenge.photo1'), content_img('home', 'challenge.photo2'), content_img('home', 'challenge.photo3')], static fn(array $i): bool => $i['has']));
$nvAnswers = array_values(array_filter(content_list('home', 'challenge.answers'), static fn(array $a): bool => trim((string) $a['question']) !== '' || trim((string) $a['answer']) !== ''));
$nvChBtn = content_btn('home', 'challenge.cta');
$nvChBtn2 = content_btn('home', 'challenge.cta2');
$nvChCtaTitle = trim((string) content('home', 'challenge.cta_title'));
$nvChCtaText = trim((string) content('home', 'challenge.cta_text'));
section_begin('home', 'challenge', ['id' => 'about-challenge', 'class' => 'section--tint home-challenge cq', 'labelledby' => 'challenge-title']);
?>
    <div class="cq__top">
        <div class="cq__intro" data-reveal>
            <span class="cq__eyebrow"><?= icon('alert-circle', 'icon--sm') ?> <?= e(trim((string) content('home', 'challenge.eyebrow')) ?: 'The challenge') ?></span>
            <h2 class="cq__title" id="challenge-title"><?= highlight((string) content('home', 'challenge.title')) ?></h2>
            <?php if ($nvChLead !== ''): ?><p class="cq__lead"><?= e($nvChLead) ?></p><?php endif; ?>
        </div>
        <?php if ($nvChPhotos): ?>
            <div class="cq__collage cq__collage--<?= count($nvChPhotos) ?>" data-reveal>
                <?php foreach ($nvChPhotos as $k => $ph): ?><figure class="cq__photo cq__photo--<?= $k + 1 ?>"><?= photo_tag($ph) ?></figure><?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($nvAnswers): ?>
        <ol class="cq__grid">
            <?php foreach ($nvAnswers as $n => $a): $src = img_url((string) $a['image']); ?>
                <li class="cq-card" data-reveal>
                    <?php if ($src !== ''): ?><figure class="cq-card__photo"><img src="<?= e($src) ?>" alt="<?= e((string) $a['image_alt']) ?>" loading="lazy" decoding="async"></figure><?php endif; ?>
                    <div class="cq-card__body">
                        <span class="cq-card__icon"><?= icon((string) ($a['icon'] ?: 'help-circle')) ?></span>
                        <h3 class="cq-card__q"><?= e($a['question']) ?></h3>
                        <p class="cq-card__a"><?= e($a['answer']) ?></p>
                    </div>
                </li>
            <?php endforeach; ?>
        </ol>
    <?php else: ?>
        <div class="prose prose--wide" data-reveal><?= content_html('home', 'challenge.body') ?></div>
    <?php endif; ?>

    <?php if ($nvChBtn['has'] || $nvChBtn2['has'] || $nvChCtaTitle !== ''): ?>
        <div class="cq__cta" data-reveal>
            <div>
                <?php if ($nvChCtaTitle !== ''): ?><h3 class="cq__cta-title"><?= e($nvChCtaTitle) ?></h3><?php endif; ?>
                <?php if ($nvChCtaText !== ''): ?><p class="cq__cta-text"><?= e($nvChCtaText) ?></p><?php endif; ?>
            </div>
            <div class="cq__cta-actions">
                <?php if ($nvChBtn['has']): ?><a class="btn btn--cta btn--caps" href="<?= e($nvChBtn['url']) ?>"<?= $nvChBtn['external'] ? ' target="_blank" rel="noopener noreferrer"' : '' ?>><?= icon('heart', 'icon--sm') ?> <?= e($nvChBtn['label']) ?></a><?php endif; ?>
                <?php if ($nvChBtn2['has']): ?><a class="btn btn--outline-light btn--caps" href="<?= e($nvChBtn2['url']) ?>"<?= $nvChBtn2['external'] ? ' target="_blank" rel="noopener noreferrer"' : '' ?>><?= e($nvChBtn2['label']) ?></a><?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
<?php section_end(); ?>

<?php /* ---- WHY DIGITAL INCLUSION MATTERS: a navy band of photo cards, a big quote and the vision (scss/pages/_why-story.scss) */ ?>
<?php
$nvWhyClosing = trim((string) content('home', 'why_digital.closing'));
$nvWhyLead = trim((string) content('home', 'why_digital.lead'));
$nvWhyQuote = trim((string) content('home', 'why_digital.quote'));
$nvWhyBtn = content_btn('home', 'why_digital.cta');
$nvMoments = array_values(array_filter(content_list('home', 'why_digital.moments'), static fn(array $m): bool => trim((string) $m['title']) !== '' || trim((string) $m['text']) !== ''));
section_begin('home', 'why_digital', ['id' => 'about-why', 'class' => 'home-why wy', 'labelledby' => 'why-title']);
?>
    <header class="wy__head" data-reveal>
        <?php if (trim((string) content('home', 'why_digital.eyebrow')) !== ''): ?><span class="wy__eyebrow"><?= icon('lightbulb', 'icon--sm') ?> <?= e(content('home', 'why_digital.eyebrow')) ?></span><?php endif; ?>
        <h2 class="wy__title" id="why-title"><?= highlight((string) content('home', 'why_digital.title')) ?></h2>
        <?php if ($nvWhyLead !== ''): ?><p class="wy__lead"><?= e($nvWhyLead) ?></p><?php endif; ?>
    </header>

    <?php if ($nvMoments): ?>
        <div class="wy__grid">
            <?php foreach ($nvMoments as $n => $m): $src = img_url((string) $m['image']); ?>
                <article class="wy-card wy-card--<?= $n % 2 ? 'b' : 'a' ?>" data-reveal>
                    <?php if ($src !== ''): ?>
                        <figure class="wy-card__photo"><img src="<?= e($src) ?>" alt="<?= e((string) $m['image_alt']) ?>" loading="lazy" decoding="async"><span class="wy-card__no" aria-hidden="true"><?= str_pad((string) ($n + 1), 2, '0', STR_PAD_LEFT) ?></span></figure>
                    <?php endif; ?>
                    <div class="wy-card__body">
                        <h3 class="wy-card__title"><?= e($m['title']) ?></h3>
                        <p class="wy-card__text"><?= e($m['text']) ?></p>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="prose prose--wide wy__prose" data-reveal><?= content_html('home', 'why_digital.body') ?></div>
    <?php endif; ?>

    <?php if ($nvWhyQuote !== ''): ?>
        <blockquote class="wy__quote" data-reveal><span aria-hidden="true">&ldquo;</span><?= e($nvWhyQuote) ?></blockquote>
    <?php endif; ?>

    <?php if ($nvWhyClosing !== '' || $nvWhyBtn['has']): ?>
        <div class="wy__vision" data-reveal>
            <?php if ($nvWhyClosing !== ''): ?><p class="wy__vision-text"><?= e($nvWhyClosing) ?></p><?php endif; ?>
            <?php if ($nvWhyBtn['has']): ?><a class="btn btn--cta btn--caps" href="<?= e($nvWhyBtn['url']) ?>"<?= $nvWhyBtn['external'] ? ' target="_blank" rel="noopener noreferrer"' : '' ?>><?= e($nvWhyBtn['label']) ?> <?= icon('arrow-right', 'icon--arrow') ?></a><?php endif; ?>
        </div>
    <?php endif; ?>
<?php section_end(); ?>

<?php /* ---- FOUNDER STORY: told in chapters, photo beside words, joined by a thread of light (scss/pages/_founder-story.scss) */ ?>
<?php
$nvFounderImg = content_img('home', 'founder.image');
$nvFBtn = content_btn('home', 'founder.cta');
$nvFBtn2 = content_btn('home', 'founder.cta2');
$nvFCaption = trim((string) content('home', 'founder.caption'));
$nvLead = trim((string) content('home', 'founder.lead'));
$nvClosing = trim((string) content('home', 'founder.closing'));
$nvChapters = array_values(array_filter(content_list('home', 'founder.chapters'), static fn(array $c): bool => trim((string) $c['title']) !== '' || trim((string) $c['text']) !== ''));
$nvShapes = ['arch', 'circle', 'polaroid', 'blob', 'arch'];
section_begin('home', 'founder', ['id' => 'about-founder', 'class' => 'fs', 'labelledby' => 'founder-title']);
?>
    <header class="fs__open" data-reveal>
        <?= partial('partials/section-head', ['eyebrow' => content('home', 'founder.eyebrow'), 'title' => content('home', 'founder.title'), 'id' => 'founder-title', 'align' => 'center', 'class' => 'mb-0']) ?>
        <?php if ($nvLead !== ''): ?>
            <blockquote class="fs__lead"><span class="fs__mark" aria-hidden="true">&ldquo;</span><?= e($nvLead) ?></blockquote>
        <?php endif; ?>
        <?php if ($nvFounderImg['has']): ?>
            <figure class="fs__portrait">
                <span class="fs__rays" aria-hidden="true"></span>
                <?= photo_tag($nvFounderImg) ?>
                <?php if ($nvFCaption !== ''): ?><figcaption><?= e($nvFCaption) ?></figcaption><?php endif; ?>
            </figure>
        <?php endif; ?>
    </header>

    <?php if ($nvChapters): ?>
        <ol class="fs__chapters">
            <?php foreach ($nvChapters as $n => $c): $src = img_url((string) $c['image']); ?>
                <li class="fs-ch fs-ch--<?= $n % 2 ? 'right' : 'left' ?> fs-ch--tone-<?= min($n, 2) ?>" data-reveal>
                    <span class="fs-ch__dot" aria-hidden="true"></span>
                    <?php if ($src !== ''): ?>
                        <figure class="fs-ch__media fs-ch__media--<?= e($nvShapes[$n % count($nvShapes)]) ?>">
                            <img src="<?= e($src) ?>" alt="<?= e((string) $c['image_alt']) ?>" loading="lazy" decoding="async">
                        </figure>
                    <?php endif; ?>
                    <div class="fs-ch__text">
                        <span class="fs-ch__no" aria-hidden="true"><?= str_pad((string) ($n + 1), 2, '0', STR_PAD_LEFT) ?></span>
                        <?php if (trim((string) $c['label']) !== ''): ?><span class="fs-ch__label"><?= e($c['label']) ?></span><?php endif; ?>
                        <?php if (trim((string) $c['title']) !== ''): ?><h3 class="fs-ch__title"><?= e($c['title']) ?></h3><?php endif; ?>
                        <?php if (trim((string) $c['text']) !== ''): ?><p class="fs-ch__body"><?= e($c['text']) ?></p><?php endif; ?>
                    </div>
                </li>
            <?php endforeach; ?>
        </ol>
    <?php else: ?>
        <div class="container container--narrow"><div class="prose mt-4" data-reveal><?= content_html('home', 'founder.body') ?></div></div>
    <?php endif; ?>

    <?php if ($nvClosing !== '' || $nvFBtn['has'] || $nvFBtn2['has']): ?>
        <div class="fs__close" data-reveal>
            <?php if ($nvClosing !== ''): ?><p class="fs__closing"><?= e($nvClosing) ?></p><?php endif; ?>
            <div class="fs__actions">
                <?php if ($nvFBtn2['has']): ?><a class="btn btn--cta btn--caps" href="<?= e($nvFBtn2['url']) ?>"<?= $nvFBtn2['external'] ? ' target="_blank" rel="noopener noreferrer"' : '' ?>><?= icon('heart', 'icon--sm') ?> <?= e($nvFBtn2['label']) ?></a><?php endif; ?>
                <?php if ($nvFBtn['has']): ?><a class="btn btn--outline-light btn--caps" href="<?= e($nvFBtn['url']) ?>"<?= $nvFBtn['external'] ? ' target="_blank" rel="noopener noreferrer"' : '' ?>><?= e($nvFBtn['label']) ?></a><?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
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
