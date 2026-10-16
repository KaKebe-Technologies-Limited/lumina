<?php
/**
 * Full-bleed photographic HOME hero: dark gradient on the left, small-caps eyebrow, big serif headline with one highlighted phrase,
 * two pill buttons (solid orange + translucent video button). Follow it with a <section class="section sheet"> (rounded white sheet that
 * overlaps its bottom edge). Vars: $title* ([[highlight]] marks ONE phrase), $eyebrow, $lead, $image* (content_img() result: src, alt),
 * $buttons (list of ['label','url','class'?]; the first defaults to the orange CTA), $video (['url','label','title']; button hidden when the
 * url is empty or not YouTube), $focus (CSS object-position of the photo, default '60% 30%').
 */
$hpTitle = trim((string) ($title ?? ''));
if ($hpTitle === '') {
    return;
}
$hpImg = (array) ($image ?? []);
$hpHasImg = !empty($hpImg['src']) && (!array_key_exists('has', $hpImg) || $hpImg['has']);
$hpFocus = preg_match('/^[0-9%. a-z-]{1,30}$/i', (string) ($focus ?? '')) ? (string) $focus : '';
$hpVideo = (array) ($video ?? []);
$hpVideoHtml = !empty($hpVideo['url']) ? partial('partials/video-button', ['url' => $hpVideo['url'], 'label' => $hpVideo['label'] ?? '', 'title' => $hpVideo['title'] ?? '']) : '';
$hpButtons = array_values(array_filter((array) ($buttons ?? []), static fn($b): bool => !empty($b['label']) && !empty($b['url'])));
?>
<section class="hero-photo" aria-labelledby="hero-title"<?= $hpFocus !== '' ? ' style="--focus:' . e($hpFocus) . '"' : '' ?>>
    <?php if ($hpHasImg): ?><img class="hero-photo__bg" src="<?= e($hpImg['src']) ?>" alt="<?= e($hpImg['alt'] ?? '') ?>" fetchpriority="high" decoding="async"><?php endif; ?>
    <div class="hero-photo__inner">
        <div class="hero-photo__copy">
            <?php if (trim((string) ($eyebrow ?? '')) !== ''): ?><span class="eyebrow"><?= e($eyebrow) ?></span><?php endif; ?>
            <h1 class="hero-photo__title" id="hero-title"><?= highlight($hpTitle) ?></h1>
            <?php if (trim((string) ($lead ?? '')) !== ''): ?><p class="hero-photo__lead"><?= e($lead) ?></p><?php endif; ?>
            <?php if ($hpButtons || $hpVideoHtml !== ''): ?>
                <div class="hero-photo__actions">
                    <?php foreach ($hpButtons as $i => $b): ?>
                        <a class="btn <?= e($b['class'] ?? ($i === 0 ? 'btn--cta btn--caps' : 'btn--photo btn--caps')) ?>" href="<?= e($b['url']) ?>"<?= is_external($b['url']) ? ' target="_blank" rel="noopener noreferrer"' : '' ?>><?= e($b['label']) ?></a>
                    <?php endforeach; ?>
                    <?= $hpVideoHtml ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
