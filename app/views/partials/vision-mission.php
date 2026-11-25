<?php
/**
 * THE VISION & MISSION EXPLORER: a photo that changes with a two-way switch (Our Vision / Our Mission) beside one navy card with the statement and its key points.
 * Without JavaScript both statements are simply shown one under the other, each with its photo; assets/js/vision-mission.js turns them into tabs (click, arrow keys, Home / End,
 * swipe, a quiet auto-advance that pauses when the visitor is busy), makes the key points tappable chips that reveal a one-line explanation, and gives the photo a soft tilt.
 * Motion is switched off for visitors who ask for reduced motion.
 * Vars: $items = list of ['key' => vision|mission, 'icon' => icon name, 'title', 'text', 'points' => list<string>, 'explain' => list<string> (same order as points, may be empty strings),
 *       'image' => ['src','alt','has'] (optional)], $label (accessible name of the switch), $actions (ready-made SAFE html: the buttons under the card).
 */
$items = array_values(array_filter((array) ($items ?? []), static fn(array $i): bool => trim((string) ($i['text'] ?? '')) !== ''));
if ($items === []) {
    return;
}
$label = trim((string) ($label ?? '')) !== '' ? (string) $label : 'Vision and mission';
$actions = (string) ($actions ?? '');

// one photo per statement; a missing one borrows the other's so the stage never has a hole
$fallback = null;
foreach ($items as $it) {
    if (!empty($it['image']['src']) && (!array_key_exists('has', $it['image']) || $it['image']['has'])) {
        $fallback = $it['image'];
        break;
    }
}
foreach ($items as $n => $it) {
    $im = (array) ($it['image'] ?? []);
    $ok = !empty($im['src']) && (!array_key_exists('has', $im) || $im['has']);
    $items[$n]['image'] = $ok ? $im : $fallback;
}
$hasPhotos = $fallback !== null;
?>
<div class="vm<?= $hasPhotos ? '' : ' vm--solo' ?>" data-vm>
    <?php if ($hasPhotos): ?>
        <div class="vm__media" data-vm-media>
            <div class="vm__frame" data-vm-frame>
                <?php foreach ($items as $n => $it): $im = $it['image']; $set = responsive_sources((string) $im['src']); ?>
                    <figure class="vm__photo<?= $n === 0 ? ' is-active' : '' ?>" data-vm-photo>
                        <picture>
                            <?php if ($set !== null): ?><source type="image/webp" srcset="<?= e($set['srcset']) ?>" sizes="(min-width: 1024px) 560px, (min-width: 768px) 50vw, 100vw"><?php endif; ?>
                            <img src="<?= e((string) $im['src']) ?>" alt="<?= e((string) ($im['alt'] ?? '')) ?>" loading="lazy" decoding="async">
                        </picture>
                        <figcaption class="vm__chipcap"><span class="vm__chipcap-icon" aria-hidden="true"><?= icon((string) $it['icon']) ?></span><?= e((string) $it['title']) ?></figcaption>
                    </figure>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
    <div class="vm__main">
        <div class="vm__tabs" role="tablist" aria-label="<?= e($label) ?>" hidden>
            <span class="vm__thumb" aria-hidden="true"></span>
            <?php foreach ($items as $n => $it): ?>
                <button class="vm__tab<?= $n === 0 ? ' is-active' : '' ?>" type="button" role="tab" id="vm-tab-<?= e($it['key']) ?>" aria-controls="vm-panel-<?= e($it['key']) ?>" aria-selected="<?= $n === 0 ? 'true' : 'false' ?>" tabindex="<?= $n === 0 ? '0' : '-1' ?>">
                    <span class="vm__tab-icon"><?= icon((string) $it['icon']) ?></span>
                    <span class="vm__tab-text"><?= e((string) $it['title']) ?></span>
                    <span class="vm__bar" aria-hidden="true"><i></i></span>
                </button>
            <?php endforeach; ?>
        </div>
        <div class="vm__stage" data-vm-stage>
            <span class="vm__glow" aria-hidden="true"></span>
            <?php foreach ($items as $n => $it): ?>
                <article class="vm__panel vm__panel--<?= e($it['key']) ?><?= $n === 0 ? ' is-active' : '' ?>" id="vm-panel-<?= e($it['key']) ?>" role="tabpanel" aria-labelledby="vm-tab-<?= e($it['key']) ?>" tabindex="0">
                    <div class="vm__head">
                        <span class="vm__emblem" aria-hidden="true"><span class="vm__ring"></span><span class="vm__core"><?= icon((string) $it['icon']) ?></span></span>
                        <h3 class="vm__title"><?= e((string) $it['title']) ?></h3>
                    </div>
                    <p class="vm__text"><?= e((string) $it['text']) ?></p>
                    <?php if (!empty($it['points'])): ?>
                        <ul class="vm__points">
                            <?php foreach ((array) $it['points'] as $k => $pt): $note = trim((string) ($it['explain'][$k] ?? '')); ?>
                                <li style="--i: <?= (int) $k ?>"><span class="vm__chip-label"><?= icon('check', 'icon--sm') ?><span><?= e((string) $pt) ?></span></span><?php if ($note !== ''): ?><span class="vm__note"><?= e($note) ?></span><?php endif; ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <p class="vm__explain" aria-live="polite" hidden></p>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
        <?php if ($actions !== ''): ?><p class="vm__actions"><?= $actions ?></p><?php endif; ?>
    </div>
</div>
