<?php
/**
 * Single blog post. Vars: $post (row), $older, $newer (slug,title|null), $related (rows), $absUrl, $share.
 * Body is sanitised rich text (rich()). Labels come from content('blog', 'post.*').
 */
$cover = img_url($post['cover_image'] ?? '');
$byline = trim((string) ($post['author_name'] ?? ''));
$posted = format_date($post['published_at']);
$metaLine = implode(' · ', array_filter([
    $posted,
    $byline !== '' ? trim((string) content('blog', 'post.by_label') . ' ' . $byline) : '',
]));
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
    'lead'    => $metaLine,
]) ?>

<article class="section post">
    <div class="container container--narrow">
        <?php if ($cover !== ''): ?>
            <figure class="post__cover">
                <img src="<?= e($cover) ?>" alt="<?= e($post['cover_alt'] ?? '') ?>" width="1200" height="750" fetchpriority="high">
            </figure>
        <?php endif; ?>

        <?php if (trim((string) ($post['excerpt'] ?? '')) !== ''): ?>
            <p class="lead post__excerpt"><?= e($post['excerpt']) ?></p>
        <?php endif; ?>

        <div class="prose post__body"><?= rich($post['body'] ?? '') ?></div>

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

        <p class="post__back"><a class="link-arrow" href="<?= e(url('blog')) ?>"><?= icon('arrow-left', 'icon--arrow') ?> <?= e(content('blog', 'post.back_label')) ?></a></p>
    </div>
</article>

<?php if ($related): ?>
    <section class="section section--tint" aria-labelledby="related-title">
        <div class="container">
            <div class="section__header"><h2 id="related-title"><?= e(content('blog', 'post.related_title')) ?></h2></div>
            <div class="grid grid--3 grid--gap-lg">
                <?php foreach ($related as $r): ?><?= partial('partials/post-card', ['post' => $r]) ?><?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php section_begin('blog', 'subscribe', ['id' => 'subscribe']); ?>
    <?= partial('web/blog/_subscribe', [
        'id'          => 'post-subscribe',
        'title'       => content('blog', 'subscribe.title'),
        'text'        => content('blog', 'subscribe.text'),
        'placeholder' => content('blog', 'subscribe.placeholder'),
        'button'      => content('blog', 'subscribe.button'),
        'source'      => 'blog-post',
    ]) ?>
<?php section_end(); ?>
