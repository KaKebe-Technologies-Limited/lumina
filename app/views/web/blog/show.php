<?php
/**
 * Single blog post. Vars: $post (row), $older, $newer (slug,title|null), $related (rows), $absUrl, $share.
 * Body is sanitised rich text (rich()). Labels come from content('blog', 'post.*').
 */
$cover = img_url($post['cover_image'] ?? '');
$byline = trim((string) ($post['author_name'] ?? ''));
$posted = format_date($post['published_at']);
$bodyHtml = rich($post['body'] ?? '');
$readMin = max(1, (int) ceil(str_word_count(strip_tags($bodyHtml)) / 200));
$readLabel = trim((string) content('blog', 'post.read_time'));
$ld = array_filter([
    '@context'      => 'https://schema.org',
    '@type'         => 'BlogPosting',
    'headline'      => (string) $post['title'],
    'datePublished' => to_datetime($post['published_at'])?->format(DATE_ATOM),
    'dateModified'  => to_datetime($post['updated_at'] ?? $post['published_at'])?->format(DATE_ATOM),
    'description'   => (string) $meta['description'],
    'image'         => $cover !== '' ? abs_url_for_image($post['cover_image']) : null,
    'mainEntityOfPage' => $absUrl,
    'author'        => $byline !== '' ? ['@type' => 'Person', 'name' => $byline] : ['@type' => 'Organization', 'name' => (string) setting('site_name', '')],
    'publisher'     => ['@type' => 'Organization', 'name' => (string) setting('site_name', ''), 'logo' => ['@type' => 'ImageObject', 'url' => abs_url_for_image((string) setting('logo_path', ''))]],
]);
$this->start('head'); ?>
<script type="application/ld+json"><?= json_encode($ld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
<?php $this->end(); $this->start('scripts'); ?>
<script src="<?= e(asset('js/pages/blog.js')) ?>" defer></script>
<?php $this->end(); ?>

<?= partial('partials/page-hero', [
    'title'   => $post['title'],
    'eyebrow' => $post['category'] ?? '',
    'lead'    => '',
]) ?>

<article class="section post">
    <div class="container container--narrow">
        <ul class="post-meta">
            <?php if ($posted !== ''): ?><li><?= icon('calendar', 'icon--sm') ?> <time datetime="<?= e(format_date($post['published_at'], 'Y-m-d')) ?>"><?= e($posted) ?></time></li><?php endif; ?>
            <?php if ($byline !== ''): ?><li><?= icon('user', 'icon--sm') ?> <?= e(trim((string) content('blog', 'post.by_label') . ' ' . $byline)) ?></li><?php endif; ?>
            <?php if ($readLabel !== '' && $bodyHtml !== ''): ?><li><?= icon('clock', 'icon--sm') ?> <?= e(str_replace('{n}', (string) $readMin, $readLabel)) ?></li><?php endif; ?>
        </ul>

        <?php if ($cover !== ''): ?>
            <figure class="post__cover">
                <img src="<?= e($cover) ?>" alt="<?= e($post['cover_alt'] ?? '') ?>" fetchpriority="high" decoding="async">
            </figure>
        <?php endif; ?>

        <?php if (trim((string) ($post['excerpt'] ?? '')) !== ''): ?>
            <p class="post__excerpt"><?= e($post['excerpt']) ?></p>
        <?php endif; ?>

        <div class="prose post__body"><?= $bodyHtml ?></div>

        <?= partial('web/blog/_share', [
            'share'       => $share,
            'absUrl'      => $absUrl,
            'heading'     => content('blog', 'post.share_label'),
            'shareOn'     => content('blog', 'post.share_on'),
            'copyLabel'   => content('blog', 'post.copy_label'),
            'copiedLabel' => content('blog', 'post.copied_label'),
        ]) ?>

        <?php if ($older || $newer): ?>
            <nav class="post-nav" aria-label="<?= e(content('blog', 'post.related_title')) ?>">
                <?php if ($older): ?>
                    <a class="post-nav__link post-nav__link--older" href="<?= e(url('blog/' . $older['slug'])) ?>" rel="prev">
                        <span class="post-nav__label"><?= icon('arrow-left', 'icon--sm') ?> <?= e(content('blog', 'post.older_label')) ?></span>
                        <span class="post-nav__title"><?= e($older['title']) ?></span>
                    </a>
                <?php endif; ?>
                <?php if ($newer): ?>
                    <a class="post-nav__link post-nav__link--newer" href="<?= e(url('blog/' . $newer['slug'])) ?>" rel="next">
                        <span class="post-nav__label"><?= e(content('blog', 'post.newer_label')) ?> <?= icon('arrow-right', 'icon--sm') ?></span>
                        <span class="post-nav__title"><?= e($newer['title']) ?></span>
                    </a>
                <?php endif; ?>
            </nav>
        <?php endif; ?>

        <p class="post__back"><a class="btn btn--outline btn--caps btn--sm" href="<?= e(url('blog')) ?>"><?= icon('arrow-left', 'icon--sm') ?> <?= e(content('blog', 'post.back_label')) ?></a></p>
    </div>
</article>

<?php if ($related): ?>
    <section class="section section--tint" aria-labelledby="related-title">
        <div class="container">
            <?= partial('partials/section-head', ['title' => content('blog', 'post.related_title'), 'id' => 'related-title', 'align' => 'center']) ?>
            <div class="grid grid--3 grid--gap-lg post-grid post-grid--n<?= min(count($related), 3) ?>">
                <?php foreach ($related as $r): ?><?= partial('web/blog/_card', ['post' => $r]) ?><?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php section_begin('blog', 'subscribe', ['class' => 'section--panel', 'id' => 'subscribe']); ?>
    <?= partial('web/blog/_subscribe', [
        'id'          => 'post-subscribe',
        'eyebrow'     => content('blog', 'subscribe.eyebrow'),
        'title'       => content('blog', 'subscribe.title'),
        'text'        => content('blog', 'subscribe.text'),
        'placeholder' => content('blog', 'subscribe.placeholder'),
        'button'      => content('blog', 'subscribe.button'),
        'source'      => 'blog-post',
    ]) ?>
<?php section_end(); ?>
