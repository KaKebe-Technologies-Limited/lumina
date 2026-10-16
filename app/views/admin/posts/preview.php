<?php
/**
 * Post preview rendered inside the PUBLIC layout (admin only, never indexed). OWNER: B4.
 * Vars: $post (row) $state (draft|scheduled|published) $stateLabel $typeLabel $editUrl.
 * Only public components are used (page-hero, container, prose, alert). The public blog/news/press page may look slightly different.
 */
$img = !empty($post['cover_image']) ? ['src' => img_url($post['cover_image']), 'alt' => (string) ($post['cover_alt'] ?? '')] : null;
$byline = array_filter([
    $post['category'] ?? '',
    !empty($post['author_name']) ? 'By ' . $post['author_name'] : '',
    !empty($post['published_at']) ? format_date($post['published_at'], 'j F Y') : '',
    !empty($post['source_name']) ? $post['source_name'] : '',
]);
?>
<div class="container preview-banner-wrap">
    <div class="alert alert--warning" role="status">
        <div class="alert__body">
            <strong class="alert__title">Preview (<?= e($stateLabel) ?>)</strong>
            <?php if ($state === 'draft'): ?>
                This <?= e(strtolower($typeLabel)) ?> post is a draft. Visitors cannot see it yet.
            <?php elseif ($state === 'scheduled'): ?>
                This post is scheduled for <?= e(format_date($post['published_at'], 'j F Y, H:i')) ?>. Visitors cannot see it before then.
            <?php else: ?>
                This post is live on the website.
            <?php endif; ?>
            <a href="<?= e($editUrl) ?>">Back to editing</a>
        </div>
    </div>
</div>

<?= partial('partials/page-hero', ['title' => (string) $post['title'], 'eyebrow' => $typeLabel, 'lead' => (string) ($post['excerpt'] ?? '')]) ?>

<section class="section">
    <div class="container container--narrow">
        <?php if ($byline): ?><p class="small text-muted"><?= e(implode(' · ', $byline)) ?></p><?php endif; ?>
        <?php if ($img): ?>
            <figure class="mb-5"><img src="<?= e($img['src']) ?>" alt="<?= e($img['alt']) ?>" class="w-full"></figure>
        <?php endif; ?>
        <?php if (!empty($post['body'])): ?>
            <div class="prose"><?= rich($post['body']) ?></div>
        <?php else: ?>
            <p class="text-muted">This post has no text yet.</p>
        <?php endif; ?>
        <?php if (!empty($post['external_url'])): ?>
            <p class="mt-5"><a class="btn btn--outline" href="<?= e($post['external_url']) ?>" target="_blank" rel="noopener noreferrer"><?= e(!empty($post['source_name']) ? 'Read it on ' . $post['source_name'] : 'Read the original article') ?></a></p>
        <?php endif; ?>
    </div>
</section>
