<?php
/**
 * NEWS / PRESS ARTICLE (/news/{slug}, /press/{slug}) - B-INNER-B restyle of the B7 view.
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
$labelPublished = trim((string) content('news', 'article.published'));
$labelBy = trim((string) content('news', 'article.by'));
$labelSource = trim((string) content('news', 'article.source'));

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
    'lead'    => '',
]) ?>

<article class="section article post" id="article">
    <div class="container container--narrow">
        <ul class="post-meta">
            <?php if ($published !== ''): ?>
                <li><?= icon('calendar', 'icon--sm') ?> <?= $labelPublished !== '' ? e($labelPublished) . ' ' : '' ?><time datetime="<?= e(format_date($post['published_at'], 'Y-m-d')) ?>"><?= e($published) ?></time></li>
            <?php endif; ?>
            <?php if ($author !== ''): ?><li><?= icon('user', 'icon--sm') ?> <?= $labelBy !== '' ? e($labelBy) . ' ' : '' ?><?= e($author) ?></li><?php endif; ?>
            <?php if ($source !== ''): ?><li><?= icon('newspaper', 'icon--sm') ?> <?= $labelSource !== '' ? e($labelSource) . ': ' : '' ?><?= e($source) ?></li><?php endif; ?>
        </ul>

        <?php if ($img !== ''): ?>
            <figure class="post__cover"><img src="<?= e($img) ?>" alt="<?= e($post['cover_alt'] ?? '') ?>" fetchpriority="high" decoding="async"></figure>
        <?php endif; ?>

        <?php if ($excerptText !== ''): ?><p class="post__excerpt"><?= e($excerptText) ?></p><?php endif; ?>

        <?php if ($body !== ''): ?><div class="prose post__body"><?= rich($body) ?></div><?php endif; ?>

        <?php if ($external !== '' && trim((string) content('news', 'article.external_cta')) !== ''): ?>
            <p class="article__external"><a class="btn btn--cta btn--caps btn--lg" href="<?= e($external) ?>" target="_blank" rel="noopener noreferrer"><?= e(content('news', 'article.external_cta')) ?> <?= icon('external-link', 'icon--sm') ?></a></p>
        <?php endif; ?>

        <p class="post__back"><a class="btn btn--outline btn--caps btn--sm" href="<?= e($backUrl) ?>"><?= icon('arrow-left', 'icon--sm') ?> <?= e($backLabel) ?></a></p>
    </div>
</article>

<?php if ($others): ?>
    <section class="section section--tint" id="more-articles" aria-labelledby="more-articles-title">
        <div class="container">
            <?= partial('partials/section-head', ['title' => $moreTitle, 'id' => 'more-articles-title', 'align' => 'center']) ?>
            <div class="grid grid--3 grid--gap-lg post-grid post-grid--n<?= min(count($others), 3) ?> news-grid">
                <?php foreach ($others as $other): ?>
                    <?= partial('web/blog/_card', ['post' => $other, 'cta' => $cta]) ?>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>
