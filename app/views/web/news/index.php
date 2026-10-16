<?php
/**
 * NEWS (/news) and PRESS (/press) listings - B-INNER-B restyle of the B7 views.
 * Vars: $type ('news'|'press'), $posts (rows of the current page), $pagination, $categories (category names in use), $category (active filter or ''),
 * $otherCount (number of published posts of the other type, to decide whether to link to it).
 * Every visible string is read through content('news', ...) so the owner can edit it in the admin (Pages > News & Press).
 */
$isPress = $type === 'press';
$hero = $isPress ? 'press_hero' : 'hero';
$empty = $isPress ? 'empty_press' : 'empty_news';
$heroImg = content_img('news', $hero . '.image');
$cta = (string) content('news', $isPress ? 'press_hero.cta' : 'list.cta');
$emptyBtn = content_btn('news', $empty . '.cta');
$emptyBtn2 = content_btn('news', $empty . '.cta2');
$linkSection = $isPress ? 'news_link' : 'press_link';
$link = content_btn('news', $linkSection . '.cta');
$baseUrl = url($isPress ? 'press' : 'news');
$listTitle = !$isPress ? trim((string) content('news', 'list.title')) : '';

$rest = $posts;
$feature = null;
if ($rest && (int) $pagination->page === 1) {
    $feature = array_shift($rest);
}
$emptyActions = [];
if ($emptyBtn['has']) {
    $emptyActions[] = ['label' => $emptyBtn['label'], 'url' => $emptyBtn['url'], 'class' => 'btn--cta btn--caps'];
}
if ($emptyBtn2['has']) {
    $emptyActions[] = ['label' => $emptyBtn2['label'], 'url' => $emptyBtn2['url'], 'class' => 'btn--outline btn--caps'];
}
?>
<?= partial('partials/page-hero', [
    'eyebrow' => content('news', $hero . '.eyebrow'),
    'title'   => content('news', $hero . '.title'),
    'lead'    => content('news', $hero . '.lead'),
    'image'   => $heroImg['has'] ? ['src' => $heroImg['src'], 'alt' => $heroImg['alt'], 'focus' => '50% 40%'] : null,
    'sheet'   => $posts === [] ? 'tint' : 'white',
]) ?>

<?php if ($posts === []): ?>
    <section class="section section--tint" id="news-empty">
        <div class="container container--narrow">
            <?= partial('partials/empty-state', [
                'title'   => content('news', $empty . '.title'),
                'text'    => content('news', $empty . '.text'),
                'icon'    => $isPress ? 'mic' : 'newspaper',
                'actions' => $emptyActions,
            ]) ?>
        </div>
    </section>
<?php else: ?>
    <?php section_begin('news', 'list', ['class' => 'section--list', 'labelledby' => $listTitle !== '' ? 'news-title' : null]); ?>
        <?php if ($listTitle !== ''): ?>
            <?= partial('partials/section-head', ['title' => $listTitle, 'id' => 'news-title', 'align' => 'center']) ?>
        <?php endif; ?>

        <?php if (count($categories) > 1): ?>
            <nav class="news-filter" aria-label="<?= e(content('news', 'list.filter_label')) ?>">
                <ul class="pills pills--center">
                    <li><a class="pills__item<?= $category === '' ? ' is-active' : '' ?>" href="<?= e($baseUrl) ?>"<?= $category === '' ? ' aria-current="true"' : '' ?>><?= e(content('news', 'list.all_label')) ?></a></li>
                    <?php foreach ($categories as $c): ?>
                        <li><a class="pills__item<?= $category === $c ? ' is-active' : '' ?>" href="<?= e(url($isPress ? 'press' : 'news', ['category' => $c])) ?>"<?= $category === $c ? ' aria-current="true"' : '' ?>><?= e($c) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </nav>
        <?php endif; ?>

        <?php if ($isPress || $listTitle === ''): ?><h2 class="visually-hidden"><?= e(content('news', 'list.sr_title')) ?></h2><?php endif; ?>
        <?php if ($feature): ?>
            <?= partial('web/blog/_card', ['post' => $feature, 'featured' => true, 'badge' => content('news', 'list.featured_label'), 'cta' => $cta]) ?>
        <?php endif; ?>
        <?php if ($rest): ?>
            <div class="grid grid--3 grid--gap-lg post-grid post-grid--n<?= min(count($rest), 3) ?> news-grid">
                <?php foreach ($rest as $post): ?>
                    <?= partial('web/blog/_card', ['post' => $post, 'cta' => $cta]) ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <?= partial('partials/pagination', ['pagination' => $pagination]) ?>
    <?php section_end(); ?>
<?php endif; ?>

<?php if ($otherCount > 0 && $link['has']): ?>
    <?php section_begin('news', $linkSection, ['bleed' => true, 'class' => 'section--band-wrap']); ?>
        <?= partial('web/blog/_band', [
            'title'   => content('news', $linkSection . '.title'),
            'text'    => content('news', $linkSection . '.text'),
            'actions' => [['label' => $link['label'], 'url' => $link['url'], 'external' => $link['external']]],
        ]) ?>
    <?php section_end(); ?>
<?php endif; ?>
