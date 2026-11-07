<?php
declare(strict_types=1);
/**
 * The focus areas as an interactive WHEEL (assets/js/focus-wheel.js, scss/components/_focus-wheel.scss). Technology & Innovation sits at the TOP, larger and glowing: it is the lead focus
 * area; the others follow clockwise. Hover or focus a node (or tap it) and the panel beside the wheel shows that area and the projects you can support; clicking a node opens its project.
 * Without JavaScript every area shows its panel (stacked) and the nodes are plain links.
 * Vars: $areas (list of ['icon','title','text','url','projects' (text: one "Label | link" per line), optional 'label' (short name on the circle), 'image', 'badge', 'cta', 'tone'] in editable order),
 * $word (the small word before the number), $hub (logo url), $hubText, $tones (colour names, default red/green/gold/purple), $leadWord / $leadBadge (the words for the lead circle). Any number of areas from 2 to 6.
 */
$fwAreas = array_values(array_filter((array) ($areas ?? []), static fn($a): bool => is_array($a) && trim((string) ($a['title'] ?? '')) !== ''));
if (!$fwAreas) {
    return;
}
$fwTones = (array) ($tones ?? ['red', 'green', 'gold', 'purple']);
foreach ($fwAreas as $i => $a) {
    $fwAreas[$i]['_tone'] = (string) ($a['tone'] ?? '') !== '' ? (string) $a['tone'] : $fwTones[$i % count($fwTones)];     // colours follow the editable order unless an area names its own
}
// Technology & Innovation first (top of the wheel); the others keep their order, clockwise
$fwLead = 0;
foreach ($fwAreas as $i => $a) {
    if (preg_match('/techn|digital|innovat/i', (string) $a['title']) === 1) {
        $fwLead = $i;
        break;
    }
}
$fwOrder = array_merge([$fwLead], array_values(array_filter(array_keys($fwAreas), static fn(int $i): bool => $i !== $fwLead)));
$fwN = count($fwAreas);
$fwPos = [];                                                           // node centres on a circle, the first (the lead) at 12 o'clock, then clockwise
foreach ($fwOrder as $pos => $idx) {
    $ang = deg2rad(-90 + $pos * 360 / $fwN);
    $fwPos[$pos] = [round(50 + 38 * cos($ang), 2), round(50 + 38 * sin($ang), 2)];
}
$fwWord = trim((string) ($word ?? ''));
$fwHub = (string) ($hub ?? '');
$fwHubText = trim((string) ($hubText ?? ''));
$fwProjects = static function (string $raw): array {
    $out = [];
    foreach (preg_split('/\R/', trim($raw)) ?: [] as $line) {
        $parts = array_map('trim', explode('|', $line, 2));
        if ($parts[0] !== '') {
            $out[] = ['label' => $parts[0], 'url' => link_url($parts[1] ?? '')];
        }
    }
    return $out;
};
?>
<div class="fw fw--n<?= $fwN ?>" data-fw data-active="0">
    <div class="fw__wheel-wrap" data-reveal>
        <div class="fw__wheel">
            <svg class="fw__ring" viewBox="0 0 400 400" aria-hidden="true" focusable="false">
                <circle class="fw__orbit fw__orbit--outer" cx="200" cy="200" r="168"/>
                <circle class="fw__orbit fw__orbit--inner" cx="200" cy="200" r="108"/>
                <?php foreach ($fwPos as $pos => $xy): ?><line class="fw__spoke fw__spoke--<?= $pos ?>" x1="200" y1="200" x2="<?= round($xy[0] * 4, 1) ?>" y2="<?= round($xy[1] * 4, 1) ?>"/><?php endforeach; ?>
            </svg>
            <div class="fw__hub" aria-hidden="true">
                <?php if ($fwHub !== ''): ?><img class="fw__hub-logo" src="<?= e($fwHub) ?>" alt="" width="64" height="64" loading="lazy"><?php endif; ?>
                <?php if ($fwHubText !== ''): ?><span class="fw__hub-text"><?= e($fwHubText) ?></span><?php endif; ?>
            </div>
            <?php foreach ($fwOrder as $pos => $idx): $a = $fwAreas[$idx]; $href = link_url((string) ($a['url'] ?? '')); ?>
                <a class="fw__node fw__node--<?= e($a['_tone']) ?><?= $pos === 0 ? ' is-lead' : '' ?>" style="left:<?= $fwPos[$pos][0] ?>%;top:<?= $fwPos[$pos][1] ?>%" data-fw-node="<?= $pos ?>" href="<?= e($href !== '' ? $href : '#fw-panel-' . $pos) ?>" aria-describedby="fw-panel-<?= $pos ?>">
                    <?php if ($pos === 0): ?><span class="fw__lead-badge"><?= e((string) ($leadBadge ?? 'Top focus')) ?></span><?php endif; ?>
                    <span class="fw__node-icon"><?= icon((string) ($a['icon'] ?? 'check-circle')) ?></span>
                    <span class="fw__node-label"><?= e((string) (($a['label'] ?? '') !== '' ? $a['label'] : $a['title'])) ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="fw__panels" data-reveal>
        <?php foreach ($fwOrder as $pos => $idx): $a = $fwAreas[$idx]; $projects = $fwProjects((string) ($a['projects'] ?? '')); $href = link_url((string) ($a['url'] ?? '')); ?>
            <section class="fw-panel fw-panel--<?= e($a['_tone']) ?><?= $pos === 0 ? ' is-active' : '' ?>" id="fw-panel-<?= $pos ?>" data-fw-panel="<?= $pos ?>" aria-label="<?= e($a['title']) ?>">
                <?php if (!empty($a['image'])): ?><figure class="fw-panel__photo"><img src="<?= e(img_url((string) $a['image'])) ?>" alt="" loading="lazy"><?php if (!empty($a['badge'])): ?><span class="fw-panel__badge"><?= e((string) $a['badge']) ?></span><?php endif; ?></figure><?php endif; ?>
                <header class="fw-panel__head">
                    <span class="fw-panel__icon"><?= icon((string) ($a['icon'] ?? 'check-circle'), 'icon--lg') ?></span>
                    <div>
                        <span class="fw-panel__kicker"><?= $pos === 0 ? e((string) ($leadWord ?? 'Our top focus')) : ($fwWord !== '' ? e($fwWord) : 'Focus area') ?> &middot; <?= str_pad((string) ($pos + 1), 2, '0', STR_PAD_LEFT) ?></span>
                        <h3 class="fw-panel__title"><?= e($a['title']) ?></h3>
                    </div>
                </header>
                <p class="fw-panel__text"><?= e((string) ($a['text'] ?? '')) ?></p>
                <?php if ($projects): ?>
                    <p class="fw-panel__sub">Projects you can support</p>
                    <ul class="fw-panel__list">
                        <?php foreach ($projects as $k => $pr): ?>
                            <li><?php if ($pr['url'] !== ''): ?><a href="<?= e($pr['url']) ?>"><span class="fw-panel__n"><?= $k + 1 ?></span><span class="fw-panel__lbl"><?= e($pr['label']) ?></span><?= icon('arrow-right', 'icon--arrow') ?></a><?php else: ?><span class="fw-panel__static"><span class="fw-panel__n"><?= $k + 1 ?></span><span class="fw-panel__lbl"><?= e($pr['label']) ?></span></span><?php endif; ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                <?php if ($href !== ''): ?><a class="btn btn--cta btn--caps fw-panel__cta" href="<?= e($href) ?>"><?= e((string) (($a['cta'] ?? '') !== '' ? $a['cta'] : 'Open ' . $a['title'])) ?> <?= icon('arrow-right', 'icon--arrow') ?></a><?php endif; ?>
            </section>
        <?php endforeach; ?>
    </div>
</div>
