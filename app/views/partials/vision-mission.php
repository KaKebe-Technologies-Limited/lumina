<?php
/**
 * THE VISION & MISSION EXPLORER: two big switches (Our Vision / Our Mission) and one stage that changes with them: an animated emblem, the statement and its key points.
 * Without JavaScript both statements are simply shown one under the other; assets/js/vision-mission.js turns them into tabs (arrow keys, swipe, a quiet auto-advance that stops when the
 * visitor takes over). Motion is switched off for visitors who ask for reduced motion.
 * Vars: $items = list of ['key' => vision|mission, 'icon' => icon name, 'title', 'text', 'points' => list<string>], $label (accessible name of the switch).
 */
$items = array_values(array_filter((array) ($items ?? []), static fn(array $i): bool => trim((string) ($i['text'] ?? '')) !== ''));
if ($items === []) {
    return;
}
$label = trim((string) ($label ?? '')) !== '' ? (string) $label : 'Vision and mission';
?>
<div class="vm" data-vm>
    <div class="vm__tabs" role="tablist" aria-label="<?= e($label) ?>" hidden>
        <?php foreach ($items as $n => $it): ?>
            <button class="vm__tab<?= $n === 0 ? ' is-active' : '' ?>" type="button" role="tab" id="vm-tab-<?= e($it['key']) ?>" aria-controls="vm-panel-<?= e($it['key']) ?>" aria-selected="<?= $n === 0 ? 'true' : 'false' ?>" tabindex="<?= $n === 0 ? '0' : '-1' ?>">
                <span class="vm__tab-icon"><?= icon((string) $it['icon']) ?></span>
                <span class="vm__tab-text"><?= e((string) $it['title']) ?></span>
                <span class="vm__bar" aria-hidden="true"><i></i></span>
            </button>
        <?php endforeach; ?>
    </div>
    <div class="vm__stage">
        <span class="vm__glow" aria-hidden="true"></span>
        <?php foreach ($items as $n => $it): ?>
            <article class="vm__panel vm__panel--<?= e($it['key']) ?><?= $n === 0 ? ' is-active' : '' ?>" id="vm-panel-<?= e($it['key']) ?>" role="tabpanel" aria-labelledby="vm-tab-<?= e($it['key']) ?>" tabindex="0">
                <div class="vm__emblem" aria-hidden="true">
                    <span class="vm__ring vm__ring--1"></span><span class="vm__ring vm__ring--2"></span><span class="vm__ring vm__ring--3"></span>
                    <span class="vm__core"><?= icon((string) $it['icon']) ?></span>
                    <span class="vm__dot vm__dot--1"></span><span class="vm__dot vm__dot--2"></span><span class="vm__dot vm__dot--3"></span>
                </div>
                <div class="vm__body">
                    <h3 class="vm__title"><?= e((string) $it['title']) ?></h3>
                    <p class="vm__text"><?= e((string) $it['text']) ?></p>
                    <?php if (!empty($it['points'])): ?>
                        <ul class="vm__points">
                            <?php foreach ((array) $it['points'] as $k => $pt): ?><li style="--i: <?= (int) $k ?>"><?= icon('check', 'icon--sm') ?><span><?= e((string) $pt) ?></span></li><?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</div>
