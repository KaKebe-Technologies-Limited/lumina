<?php
/**
 * THE CONNECT HUB: the contact details as a set of tiles that are linked to each other by small glowing connectors, on a quietly animated network of nodes. Used by the home page
 * contact band and by the Contact page. Everything is plain HTML first (links work without JavaScript); assets/js/contact-hub.js adds the "copy" buttons, the live message and a light
 * that follows the pointer. Motion is switched off for visitors who ask for reduced motion.
 *
 * Vars: $tiles = list of ['key' => mail|phone|whatsapp|map|clock|office, 'icon' => icon name, 'label' => text,
 *                         'wide' => bool (the tile fills a whole row: long e-mail addresses), 'lines' => list of ['text' => text, 'href' => '' (no link), 'copy' => text to copy ('' = no copy button), 'external' => bool, 'html' => ready SAFE html (optional, replaces text)]],
 *       $copyLabel (default "Copy"), $copiedLabel (default "Copied"), $variant ('home' | 'page'), $class (extra class).
 */
$tiles = $tiles ?? [];
if ($tiles === []) {
    return;
}
$copyLabel = (string) ($copyLabel ?? 'Copy');
$copiedLabel = (string) ($copiedLabel ?? 'Copied');
$variant = ($variant ?? 'home') === 'page' ? 'page' : 'home';
// the connectors (2 columns from the small breakpoint up): a stub to the right neighbour in the same row, and one up to the row above
$links = [];
$col = 0;
$row = 0;
$prev = null;
foreach ($tiles as $i => $t) {
    $wide = !empty($t['wide']);
    if ($wide || $col === 0) {
        $row += ($i === 0 ? 0 : 1);
        $links[$i] = ['up' => $row > 0, 'right' => false, 'wide' => $wide];
        $col = $wide ? 0 : 1;
    } else {
        $links[$prev]['right'] = true;
        $links[$i] = ['up' => $row > 0, 'right' => false, 'wide' => false];
        $col = 0;
    }
    $prev = $i;
}
?>
<div class="cx cx--<?= e($variant) ?><?= !empty($class) ? ' ' . e($class) : '' ?>" data-contact-hub data-copied="<?= e($copiedLabel) ?>">
    <svg class="cx__net" viewBox="0 0 400 300" preserveAspectRatio="xMidYMid slice" aria-hidden="true" focusable="false">
        <g class="cx__lines">
            <path d="M40 60 L130 120 L210 70 L320 130 L360 60" /><path d="M130 120 L90 220 L200 250 L210 70" /><path d="M200 250 L310 230 L320 130" /><path d="M90 220 L40 170 L40 60" /><path d="M310 230 L370 250" />
        </g>
        <g class="cx__nodes">
            <circle cx="40" cy="60" r="3" /><circle cx="130" cy="120" r="4" /><circle cx="210" cy="70" r="3" /><circle cx="320" cy="130" r="4.5" /><circle cx="360" cy="60" r="2.5" />
            <circle cx="90" cy="220" r="3.5" /><circle cx="200" cy="250" r="4" /><circle cx="310" cy="230" r="3" /><circle cx="40" cy="170" r="2.5" /><circle cx="370" cy="250" r="2.5" />
        </g>
    </svg>
    <ul class="cx__grid">
        <?php foreach ($tiles as $i => $t): ?>
            <li class="cx-tile cx-tile--<?= e((string) ($t['key'] ?? 'item')) ?><?= !empty($links[$i]['wide']) ? ' cx-tile--wide' : '' ?><?= !empty($links[$i]['up']) ? ' has-up' : '' ?><?= !empty($links[$i]['right']) ? ' has-right' : '' ?>" style="--i: <?= (int) $i ?>" data-reveal>
                <span class="cx-tile__glow" aria-hidden="true"></span>
                <span class="cx-tile__icon"><?= icon((string) ($t['icon'] ?? 'mail')) ?></span>
                <div class="cx-tile__body">
                    <span class="cx-tile__label"><?= e((string) ($t['label'] ?? '')) ?></span>
                    <?php foreach ((array) ($t['lines'] ?? []) as $ln): ?>
                        <span class="cx-tile__line">
                            <?php if (isset($ln['html'])): ?>
                                <?= $ln['html'] ?>
                            <?php elseif (($ln['href'] ?? '') !== ''): ?>
                                <a class="cx-tile__value" href="<?= e((string) $ln['href']) ?>"<?= !empty($ln['external']) ? ' target="_blank" rel="noopener noreferrer"' : '' ?>><?= e((string) $ln['text']) ?></a>
                            <?php else: ?>
                                <span class="cx-tile__value cx-tile__value--plain"><?= e((string) $ln['text']) ?></span>
                            <?php endif; ?>
                            <?php if (($ln['copy'] ?? '') !== ''): ?>
                                <button class="cx-copy" type="button" hidden data-copy="<?= e((string) $ln['copy']) ?>" aria-label="<?= e($copyLabel . ': ' . (string) $ln['copy']) ?>"><?= icon('copy', 'icon--sm') ?><span class="cx-copy__text"><?= e($copyLabel) ?></span></button>
                            <?php endif; ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
    <p class="visually-hidden" role="status" aria-live="polite" data-cx-live></p>
</div>
