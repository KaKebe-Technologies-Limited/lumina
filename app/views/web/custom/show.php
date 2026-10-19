<?php
/**
 * Owner-created page. Vars: $row (pages row), $template ('default' | 'wide' | 'landing'), $children (published sub-pages).
 * Title, subtitle, hero photo (+ alt) and body are edited in the admin (Pages). Body is sanitised rich text.
 *   default  narrow reading column     wide  full-width reading area     landing  photo banner, wide body and the donate band
 * B-INNER-B restyle: photo page-hero (landing), rounded figure, readable prose, sub-pages as feature cards.
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
    'image' => $landing && $hero !== '' ? ['src' => $hero, 'alt' => $heroAlt, 'focus' => '50% 35%'] : null,
]) ?>

<article class="section custom-page custom-page--<?= e($template) ?>">
    <div class="<?= e($container) ?>">
        <?php if (!$landing && $hero !== ''): ?>
            <figure class="custom-page__figure">
                <img src="<?= e($hero) ?>" alt="<?= e($heroAlt) ?>" fetchpriority="high" decoding="async">
            </figure>
        <?php endif; ?>

        <div class="prose<?= $template === 'default' ? '' : ' prose--wide' ?> custom-page__body"><?= \App\Services\Forms\FormShortcode::expand(rich((string) ($row['body'] ?? ''))) ?></div>

        <?php if ($children): ?>
            <nav class="custom-page__children" aria-labelledby="children-title">
                <h2 id="children-title" class="h3"><?= e(content('site_custom', 'labels.children_title')) ?></h2>
                <ul class="custom-page__list">
                    <?php foreach ($children as $c): ?>
                        <li>
                            <?= partial('partials/feature-card', [
                                'title' => $c['nav_label'] !== null && $c['nav_label'] !== '' ? $c['nav_label'] : $c['title'],
                                'text'  => trim((string) ($c['subtitle'] ?? '')),
                                'icon'  => 'arrow-up-right',
                                'href'  => url($c['slug']),
                            ]) ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </nav>
        <?php endif; ?>

        <?php if ($showUpdated && format_date($row['updated_at'] ?? null) !== ''): ?>
            <p class="custom-page__updated"><?= icon('clock', 'icon--sm') ?> <?= e(content('site_custom', 'labels.updated_label')) ?>: <time datetime="<?= e(format_date($row['updated_at'], 'Y-m-d')) ?>"><?= e(format_date($row['updated_at'])) ?></time></p>
        <?php endif; ?>
    </div>
</article>

<?php if ($landing): ?><?= partial('partials/cta-donate') ?><?php endif; ?>
