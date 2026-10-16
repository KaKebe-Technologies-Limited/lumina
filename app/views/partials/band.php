<?php
/**
 * Full-bleed photo band: dark overlay, centred serif heading, optional text and buttons (e.g. "Volunteer opportunities").
 * Vars: $image* (['src','alt'] / content_img() result), $title* ([[highlight]] allowed), $eyebrow, $text,
 * $actions (list of ['label','url','class'?]), $video (['url','label','title'] -> adds a video button), $overlap (bool: leave room for a
 * following stat-band), $after (ready-made SAFE html placed in a .container under the band, e.g. partial('partials/stat-band', ...)),
 * $focus (CSS object-position of the photo, e.g. '50% 20%').
 * Use inside section_begin(page, key, ['bleed' => true, 'class' => 'band-section']).
 */
$bdTitle = trim((string) ($title ?? ''));
if ($bdTitle === '') {
    return;
}
$bdImg = (array) ($image ?? []);
$bdHasImg = !empty($bdImg['src']) && (!array_key_exists('has', $bdImg) || $bdImg['has']);
$bdAfter = (string) ($after ?? '');
$bdVideo = (array) ($video ?? []);
$bdFocus = preg_match('/^[0-9%. a-z-]{1,30}$/i', (string) ($focus ?? '')) ? (string) $focus : '';
?>
<div class="band<?= !empty($overlap) || $bdAfter !== '' ? ' band--overlap' : '' ?>"<?= $bdFocus !== '' ? ' style="--focus:' . e($bdFocus) . '"' : '' ?>>
    <?php if ($bdHasImg): ?><img class="band__bg" src="<?= e($bdImg['src']) ?>" alt="<?= e($bdImg['alt'] ?? '') ?>" loading="lazy" decoding="async"><?php endif; ?>
    <div class="band__inner">
        <?php if (trim((string) ($eyebrow ?? '')) !== ''): ?><span class="eyebrow eyebrow--center"><?= e($eyebrow) ?></span><?php endif; ?>
        <h2 class="band__title"><?= highlight($bdTitle) ?></h2>
        <?php if (trim((string) ($text ?? '')) !== ''): ?><p class="band__text"><?= e($text) ?></p><?php endif; ?>
        <?php $bdVideoHtml = !empty($bdVideo['url']) ? partial('partials/video-button', ['url' => $bdVideo['url'], 'label' => $bdVideo['label'] ?? '', 'title' => $bdVideo['title'] ?? '', 'class' => 'btn btn--white btn--sm btn--caps']) : ''; ?>
        <?php if (!empty($actions) || $bdVideoHtml !== ''): ?>
            <div class="band__actions">
                <?php foreach ((array) ($actions ?? []) as $a): if (empty($a['label']) || empty($a['url'])) { continue; } ?>
                    <a class="btn <?= e($a['class'] ?? 'btn--cta btn--caps') ?>" href="<?= e($a['url']) ?>"<?= is_external($a['url']) ? ' target="_blank" rel="noopener noreferrer"' : '' ?>><?= e($a['label']) ?></a>
                <?php endforeach; ?>
                <?= $bdVideoHtml ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php if ($bdAfter !== ''): ?><div class="container"><?= $bdAfter ?></div><?php endif; ?>
