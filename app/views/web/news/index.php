<?php
/**
 * NEWS (/news) and PRESS (/press) listings - OWNED BY B7 "org-public".
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
$linkSection = $isPress ? 'news_link' : 'press_link';
$link = content_btn('news', $linkSection . '.cta');
$baseUrl = url($isPress ? 'press' : 'news');
?>
<?= partial('partials/page-hero', [
    'eyebrow' => content('news', $hero . '.eyebrow'),
    'title'   => content('news', $hero . '.title'),
    'lead'    => content('news', $hero . '.lead'),
    'image'   => $heroImg['has'] ? ['src' => $heroImg['src'], 'alt' => $heroImg['alt']] : null,
]) ?>

<?php if ($posts === []): ?>
    <section class="section" id="news-empty">
        <div class="container container--narrow">
            <?= partial('partials/empty-state', [
                'title'   => content('news', $empty . '.title'),
                'text'    => content('news', $empty . '.text'),
                'icon'    => 'newspaper',
                'actions' => $emptyBtn['has'] ? [['label' => $emptyBtn['label'], 'url' => $emptyBtn['url'], 'class' => 'btn--primary']] : [],
            ]) ?>
        </div>
    </section>
<?php else: ?>
    <?php section_begin('news', 'list', ['labelledby' => !$isPress && trim((string) content('news', 'list.title')) !== '' ? 'news-title' : null]); ?>
        <?php if (!$isPress && trim((string) content('news', 'list.title')) !== ''): ?>
            <div class="section__header"><h2 id="news-title"><?= e(content('news', 'list.title')) ?></h2></div>
        <?php endif; ?>

        <?php if (count($categories) > 1): ?>
            <nav class="news-filter" aria-label="<?= e(content('news', 'list.filter_label')) ?>">
                <ul class="pills">
                    <li><a class="pills__item<?= $category === '' ? ' is-active' : '' ?>" href="<?= e($baseUrl) ?>"<?= $category === '' ? ' aria-current="true"' : '' ?>><?= e(content('news', 'list.all_label')) ?></a></li>
                    <?php foreach ($categories as $c): ?>
                        <li><a class="pills__item<?= $category === $c ? ' is-active' : '' ?>" href="<?= e(url($isPress ? 'press' : 'news', ['category' => $c])) ?>"<?= $category === $c ? ' aria-current="true"' : '' ?>><?= e($c) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </nav>
        <?php endif; ?>

        <?php if ($isPress || trim((string) content('news', 'list.title')) === ''): ?><h2 class="visually-hidden"><?= e(content('news', 'list.sr_title')) ?></h2><?php endif; ?>
        <div class="grid grid--3 news-grid">
            <?php foreach ($posts as $post): ?>
                <?= partial('partials/post-card', ['post' => $post, 'cta' => $cta]) ?>
            <?php endforeach; ?>
        </div>
        <?= partial('partials/pagination', ['pagination' => $pagination]) ?>
    <?php section_end(); ?>
<?php endif; ?>

<?php if ($otherCount > 0 && $link['has']): ?>
    <?php section_begin('news', $linkSection, ['class' => 'section--navy', 'container' => 'container--narrow']); ?>
        <div class="news-link text-center">
            <h2><?= e(content('news', $linkSection . '.title')) ?></h2>
            <?php if (trim((string) content('news', $linkSection . '.text')) !== ''): ?><p class="lead mt-3"><?= e(content('news', $linkSection . '.text')) ?></p><?php endif; ?>
            <p class="mt-5"><a class="btn btn--accent btn--lg" href="<?= e($link['url']) ?>"><?= e($link['label']) ?></a></p>
        </div>
    <?php section_end(); ?>
<?php endif; ?>
