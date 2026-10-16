<?php
/**
 * NEWS / PRESS ARTICLE (/news/{slug}, /press/{slug}) - OWNED BY B7 "org-public".
 * Vars: $type ('news'|'press'), $post (posts row, published), $others (up to 3 more of the same type), $external (validated http(s) link of a press
 * item or '').
 * Every label comes from content('news', ...) (admin: Pages > News & Press); the article itself is written under Posts in the admin.
 */
$isPress = $type === 'press';
$img = img_url($post['cover_image'] ?? '');
$excerptText = trim((string) ($post['excerpt'] ?? ''));
$body = trim((string) ($post['body'] ?? ''));
$published = format_date($post['published_at'] ?? null);
$author = trim((string) ($post['author_name'] ?? ''));
$source = trim((string) ($post['source_name'] ?? ''));
$category = trim((string) ($post['category'] ?? ''));
$backUrl = url($isPress ? 'press' : 'news');
$backLabel = (string) content('news', $isPress ? 'article.back_press' : 'article.back_news');
$moreTitle = (string) content('news', $isPress ? 'article.more_press' : 'article.more_news');
$cta = (string) content('news', $isPress ? 'press_hero.cta' : 'list.cta');

$ld = array_filter([
    '@context'         => 'https://schema.org',
    '@type'            => 'NewsArticle',
    'headline'         => mb_substr((string) $post['title'], 0, 110),
    'datePublished'    => $published !== '' ? (to_datetime($post['published_at'])?->format('c')) : null,
    'dateModified'     => to_datetime($post['updated_at'] ?? null)?->format('c'),
    'image'            => $img !== '' ? [abs_url_for_image((string) $post['cover_image'])] : null,
    'author'           => $author !== '' ? ['@type' => 'Person', 'name' => $author] : null,
    'publisher'        => ['@type' => 'Organization', 'name' => (string) setting('site_name', ''), 'url' => abs_url('')],
    'mainEntityOfPage' => abs_url(($isPress ? 'press/' : 'news/') . $post['slug']),
    'description'      => $excerptText !== '' ? $excerptText : excerpt($body, 200),
], static fn($v) => $v !== null && $v !== '');
?>
<?php $this->start('head'); ?>
<script type="application/ld+json"><?= json_encode($ld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
<?php $this->end(); ?>

<?= partial('partials/page-hero', [
    'eyebrow' => $category,
    'title'   => $post['title'],
    'lead'    => $excerptText,
]) ?>

<article class="section article" id="article">
    <div class="container container--narrow">
        <p class="article__back"><a class="link-arrow" href="<?= e($backUrl) ?>"><?= icon('arrow-left', 'icon--arrow') ?> <?= e($backLabel) ?></a></p>

        <ul class="article__meta">
            <?php if ($published !== ''): ?>
                <li><?= icon('calendar', 'icon--sm') ?> <?php if (trim((string) content('news', 'article.published')) !== ''): ?><?= e(content('news', 'article.published')) ?> <?php endif; ?><time datetime="<?= e(format_date($post['published_at'], 'Y-m-d')) ?>"><?= e($published) ?></time></li>
            <?php endif; ?>
            <?php if ($author !== ''): ?><li><?= icon('user', 'icon--sm') ?> <?php if (trim((string) content('news', 'article.by')) !== ''): ?><?= e(content('news', 'article.by')) ?> <?php endif; ?><?= e($author) ?></li><?php endif; ?>
            <?php if ($source !== ''): ?><li><?= icon('newspaper', 'icon--sm') ?> <?php if (trim((string) content('news', 'article.source')) !== ''): ?><?= e(content('news', 'article.source')) ?>: <?php endif; ?><?= e($source) ?></li><?php endif; ?>
        </ul>

        <?php if ($img !== ''): ?>
            <figure class="article__cover"><img src="<?= e($img) ?>" alt="<?= e($post['cover_alt'] ?? '') ?>" fetchpriority="high"></figure>
        <?php endif; ?>

        <?php if ($body !== ''): ?><div class="prose"><?= rich($body) ?></div><?php endif; ?>

        <?php if ($external !== '' && trim((string) content('news', 'article.external_cta')) !== ''): ?>
            <p class="article__external"><a class="btn btn--primary" href="<?= e($external) ?>" target="_blank" rel="noopener noreferrer"><?= e(content('news', 'article.external_cta')) ?> <?= icon('external-link', 'icon--sm') ?></a></p>
        <?php endif; ?>
    </div>
</article>

<?php if ($others): ?>
    <section class="section section--tint" id="more-articles" aria-labelledby="more-articles-title">
        <div class="container">
            <div class="section__header"><h2 id="more-articles-title"><?= e($moreTitle) ?></h2></div>
            <div class="grid grid--3 news-grid">
                <?php foreach ($others as $other): ?>
                    <?= partial('partials/post-card', ['post' => $other, 'cta' => $cta]) ?>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>
