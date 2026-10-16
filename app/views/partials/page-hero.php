<?php
/**
 * Inner-page banner. Vars: $title (required), $eyebrow, $lead, $image (['src','alt'] - optional background photo),
 * $actions (list of ['label','url','class'?]), $breadcrumbs (default: the layout's $breadcrumbs).
 * All text must come from content()/DB, never hardcoded.
 */
$crumbs = $breadcrumbs ?? [];
// The photo is a CSS background: quote the url() and percent-encode every character that could end it (an editor-supplied
// address must never be able to add its own declarations). HTML escaping alone is not enough: the browser decodes entities first.
$bgUrl = '';
if (!empty($image['src'])) {
    $bgUrl = strtr((string) $image['src'], ['(' => '%28', ')' => '%29', "'" => '%27', '"' => '%22', chr(92) => '%5C', ';' => '%3B',
        ' ' => '%20', "\t" => '%09', "\n" => '', "\r" => '', '{' => '%7B', '}' => '%7D', '<' => '%3C', '>' => '%3E', '`' => '%60']);
}
$bg = $bgUrl !== '' ? ' style="background-image:url(\'' . e($bgUrl) . '\')"' : '';
// a photo with a description is exposed to assistive technology; a decorative one (no description) stays hidden from it
$bgAlt = ($bg !== '' && !empty($image['alt'])) ? '<span class="visually-hidden" role="img" aria-label="' . e($image['alt']) . '"></span>' : '';
?>
<header class="page-hero<?= $bg !== '' ? ' page-hero--image' : '' ?>"<?= $bg ?>>
    <?= $bgAlt ?>
    <div class="page-hero__inner">
        <?= partial('partials/breadcrumbs', ['breadcrumbs' => $crumbs]) ?>
        <?php if (!empty($eyebrow)): ?><span class="eyebrow"><?= e($eyebrow) ?></span><br><?php endif; ?>
        <h1 class="page-hero__title"><?= e($title) ?></h1>
        <?php if (!empty($lead)): ?><p class="page-hero__lead"><?= e($lead) ?></p><?php endif; ?>
        <?php if (!empty($actions)): ?>
            <div class="page-hero__actions">
                <?php foreach ($actions as $a): if (empty($a['label']) || empty($a['url'])) { continue; } ?>
                    <a class="btn <?= e($a['class'] ?? 'btn--accent') ?>" href="<?= e($a['url']) ?>"><?= e($a['label']) ?></a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</header>
