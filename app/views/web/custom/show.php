<?php
/**
 * Owner-created page. Vars: $row (pages row), $template ('default' | 'wide' | 'landing'), $children (published sub-pages).
 * Title, subtitle, hero photo (+ alt) and body are edited in the admin (Pages). Body is sanitised rich text.
 *   default  narrow reading column     wide  full-width reading area     landing  photo banner, wide body and the donate band
 */
$hero = img_url($row['hero_image'] ?? '');
$heroAlt = (string) ($row['hero_image_alt'] ?? '');
$landing = $template === 'landing';
$container = $template === 'default' ? 'container container--narrow' : 'container container--wide';
$showUpdated = (bool) content('site_custom', 'labels.show_updated');
?>
<?= partial('partials/page-hero', [
    'title' => $row['title'],
    'lead'  => (string) ($row['subtitle'] ?? ''),
    'image' => $landing && $hero !== '' ? ['src' => $hero, 'alt' => $heroAlt] : null,
]) ?>

<article class="section custom-page custom-page--<?= e($template) ?>">
    <div class="<?= e($container) ?>">
        <?php if (!$landing && $hero !== ''): ?>
            <figure class="custom-page__figure">
                <img src="<?= e($hero) ?>" alt="<?= e($heroAlt) ?>" fetchpriority="high">
            </figure>
        <?php endif; ?>

        <div class="prose<?= $template === 'default' ? '' : ' prose--wide' ?> custom-page__body"><?= rich((string) ($row['body'] ?? '')) ?></div>

        <?php if ($children): ?>
            <nav class="custom-page__children" aria-labelledby="children-title">
                <h2 id="children-title" class="h3"><?= e(content('site_custom', 'labels.children_title')) ?></h2>
                <ul class="grid grid--auto">
                    <?php foreach ($children as $c): ?>
                        <li class="card card--flat card--tint custom-page__child">
                            <div class="card__body">
                                <h3 class="card__title"><a class="card__stretch" href="<?= e(url($c['slug'])) ?>"><?= e($c['nav_label'] !== null && $c['nav_label'] !== '' ? $c['nav_label'] : $c['title']) ?></a></h3>
                                <?php if (trim((string) ($c['subtitle'] ?? '')) !== ''): ?><p class="card__text"><?= e($c['subtitle']) ?></p><?php endif; ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </nav>
        <?php endif; ?>

        <?php if ($showUpdated && format_date($row['updated_at'] ?? null) !== ''): ?>
            <p class="custom-page__updated"><?= e(content('site_custom', 'labels.updated_label')) ?>: <time datetime="<?= e(format_date($row['updated_at'], 'Y-m-d')) ?>"><?= e(format_date($row['updated_at'])) ?></time></p>
        <?php endif; ?>
    </div>
</article>

<?php if ($landing): ?><?= partial('partials/cta-donate') ?><?php endif; ?>
