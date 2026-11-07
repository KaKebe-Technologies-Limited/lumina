<?php
/**
 * HOME hero, animated and interactive (assets/js/hero.js, scss/components/_hero-photo.scss). Left: small-caps eyebrow, a headline whose highlighted word cycles through a few
 * words, a short intro with "Read more / Show less", the buttons and the video button. Right: a photo stage with up to three slides (arrows, dots, autoplay that pauses
 * on hover), a gentle tilt that follows the pointer, and floating stat chips whose numbers count up. Nothing the visitor needs depends on the animation: without JavaScript
 * the first photo, the full text and the final numbers are shown; with "reduce motion" nothing moves by itself.
 * Follow it with a <section class="section sheet"> (rounded white sheet that overlaps its bottom edge).
 * Vars: $title* ([[highlight]] marks ONE phrase), $eyebrow, $lead, $more, $moreLabel, $lessLabel, $rotate (comma list of words for the highlight),
 * $slides (list of ['src','alt','caption']), $stats (rows of the stats table), $focus (CSS object-position of the first photo), $buttons, $video.
 */
$hpTitle = trim((string) ($title ?? ''));
if ($hpTitle === '') {
    return;
}
$hpSlides = array_values(array_filter((array) ($slides ?? []), static fn($s): bool => is_array($s) && !empty($s['src'])));
$hpFocus = preg_match('/^[0-9%. a-z-]{1,30}$/i', (string) ($focus ?? '')) ? (string) $focus : '';
$hpVideo = (array) ($video ?? []);
$hpVideoHtml = !empty($hpVideo['url']) ? partial('partials/video-button', ['url' => $hpVideo['url'], 'label' => $hpVideo['label'] ?? '', 'title' => $hpVideo['title'] ?? '']) : '';
$hpButtons = array_values(array_filter((array) ($buttons ?? []), static fn($b): bool => !empty($b['label']) && !empty($b['url'])));
$hpStats = array_slice(array_values(array_filter((array) ($stats ?? []), static fn($s): bool => is_array($s) && trim((string) ($s['label'] ?? '')) !== '')), 0, 3);
$hpWords = array_values(array_filter(array_map('trim', explode(',', (string) ($rotate ?? '')))));
$hpMore = trim((string) ($more ?? ''));
$hpMoreLabel = ($moreLabel ?? '') !== '' ? (string) $moreLabel : 'Read more';
$hpLessLabel = ($lessLabel ?? '') !== '' ? (string) $lessLabel : 'Show less';
$hpHtml = highlight($hpTitle);
if ($hpWords && str_contains($hpHtml, '<span class="hl">')) {
    // the highlighted phrase carries the words it cycles through (the first is the phrase itself, so without JavaScript nothing changes)
    $hpHtml = preg_replace_callback('/<span class="hl">(.+?)<\/span>/su', static fn(array $m): string => '<span class="hl" data-hero-words="' . e(json_encode(array_merge([html_entity_decode(strip_tags($m[1]), ENT_QUOTES)], $hpWords), JSON_UNESCAPED_UNICODE)) . '">' . $m[1] . '</span>', $hpHtml, 1) ?? $hpHtml;
}
?>
<section class="hero-photo" aria-labelledby="hero-title"<?= $hpFocus !== '' ? ' style="--focus:' . e($hpFocus) . '"' : '' ?> data-hero>
    <span class="hero-photo__orb hero-photo__orb--a" aria-hidden="true"></span>
    <span class="hero-photo__orb hero-photo__orb--b" aria-hidden="true"></span>
    <div class="hero-photo__inner<?= $hpSlides ? ' hero-photo__inner--stage' : '' ?>">
        <div class="hero-photo__copy">
            <?php if (trim((string) ($eyebrow ?? '')) !== ''): ?><span class="eyebrow"><?= e($eyebrow) ?></span><?php endif; ?>
            <h1 class="hero-photo__title" id="hero-title"><?= $hpHtml ?></h1>
            <?php if (trim((string) ($lead ?? '')) !== ''): ?><p class="hero-photo__lead"><?= e($lead) ?></p><?php endif; ?>
            <?php if ($hpMore !== ''): ?>
                <p class="hero-photo__more" id="hero-more" data-hero-more><?= e($hpMore) ?></p>
                <button class="hero-photo__toggle" type="button" data-hero-toggle aria-expanded="false" aria-controls="hero-more" data-more="<?= e($hpMoreLabel) ?>" data-less="<?= e($hpLessLabel) ?>"><span data-hero-toggle-text><?= e($hpMoreLabel) ?></span> <?= icon('chevron-down', 'icon--sm') ?></button>
            <?php endif; ?>
            <?php if ($hpButtons || $hpVideoHtml !== ''): ?>
                <div class="hero-photo__actions">
                    <?php foreach ($hpButtons as $i => $b): ?>
                        <a class="btn <?= e($b['class'] ?? ($i === 0 ? 'btn--cta btn--caps' : 'btn--photo btn--caps')) ?>" href="<?= e($b['url']) ?>"<?= is_external($b['url']) ? ' target="_blank" rel="noopener noreferrer"' : '' ?>><?= e($b['label']) ?></a>
                    <?php endforeach; ?>
                    <?= $hpVideoHtml ?>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($hpSlides): ?>
            <div class="hero-photo__stage" data-hero-stage aria-roledescription="carousel" aria-label="Photos from our work">
                <div class="hero-photo__frame" data-hero-tilt>
                    <?php foreach ($hpSlides as $i => $sl): ?>
                        <figure class="hero-photo__slide<?= $i === 0 ? ' is-active' : '' ?>" data-hero-slide aria-roledescription="slide" aria-label="<?= $i + 1 ?> of <?= count($hpSlides) ?>"<?= $i > 0 ? ' aria-hidden="true"' : '' ?>>
                            <?= partial('partials/hero-img', ['src' => $sl['src'], 'alt' => $sl['alt'] ?? '', 'class' => 'hero-photo__img', 'priority' => $i === 0 ? 'high' : 'lazy', 'focus' => $i === 0 ? $hpFocus : '']) ?>
                            <?php if (trim((string) ($sl['caption'] ?? '')) !== ''): ?><figcaption class="hero-photo__caption"><?= e($sl['caption']) ?></figcaption><?php endif; ?>
                        </figure>
                    <?php endforeach; ?>
                    <?php if ($hpStats): ?>
                        <ul class="hero-photo__chips">
                            <?php foreach ($hpStats as $k => $s): $v = $s['value'] ?? $s['value_number'] ?? 0; $pre = (string) ($s['prefix'] ?? ''); $suf = (string) ($s['suffix'] ?? ''); ?>
                                <li class="hero-photo__chip hero-photo__chip--<?= $k + 1 ?>"><strong data-count-to="<?= (int) $v ?>" data-count-prefix="<?= e($pre) ?>" data-count-suffix="<?= e($suf) ?>"><?= e($pre . number_format((int) $v) . $suf) ?></strong><span><?= e($s['label']) ?></span></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
                <?php if (count($hpSlides) > 1): ?>
                    <div class="hero-photo__nav">
                        <button class="hero-photo__arrow" type="button" data-hero-prev aria-label="Previous photo"><?= icon('arrow-left', 'icon--sm') ?></button>
                        <div class="hero-photo__dots" role="tablist" aria-label="Choose a photo">
                            <?php foreach ($hpSlides as $i => $sl): ?><button class="hero-photo__dot<?= $i === 0 ? ' is-active' : '' ?>" type="button" role="tab" data-hero-dot="<?= $i ?>" aria-selected="<?= $i === 0 ? 'true' : 'false' ?>" aria-label="Photo <?= $i + 1 ?>"></button><?php endforeach; ?>
                        </div>
                        <button class="hero-photo__arrow" type="button" data-hero-next aria-label="Next photo"><?= icon('arrow-right', 'icon--sm') ?></button>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
