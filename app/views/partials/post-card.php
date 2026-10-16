<?php
/**
 * Card for a posts row (blog | news | press). Vars: $post (DB row), $cta (link label; default content('global','labels.read_more')).
 * Links to /blog/{slug}, /news/{slug} or /press/{slug} by post_type, or to external_url for press items that have one.
 */
$type = $post['post_type'] ?? 'blog';
$external = $type === 'press' && !empty($post['external_url']);
$href = $external ? (string) $post['external_url'] : url($type . '/' . $post['slug']);
$cta = $cta ?? (string) content('global', 'labels.read_more');
$img = img_url($post['cover_image'] ?? '');
?>
<article class="card" data-reveal>
    <?php if ($img !== ''): ?>
        <a class="card__media" href="<?= e($href) ?>" tabindex="-1" aria-hidden="true"<?= $external ? ' target="_blank" rel="noopener noreferrer"' : '' ?>><img src="<?= e($img) ?>" alt="" loading="lazy"></a>
    <?php endif; ?>
    <div class="card__body">
        <div class="card__meta">
            <?php if (!empty($post['published_at'])): ?><time datetime="<?= e(format_date($post['published_at'], 'Y-m-d')) ?>"><?= e(format_date($post['published_at'])) ?></time><?php endif; ?>
            <?php if (!empty($post['category'])): ?><span><?= e($post['category']) ?></span><?php endif; ?>
            <?php if (!empty($post['source_name'])): ?><span><?= e($post['source_name']) ?></span><?php endif; ?>
        </div>
        <h3 class="card__title"><a href="<?= e($href) ?>"<?= $external ? ' target="_blank" rel="noopener noreferrer"' : '' ?>><?= e($post['title']) ?></a></h3>
        <?php if (!empty($post['excerpt'])): ?><p class="card__text"><?= e($post['excerpt']) ?></p><?php endif; ?>
        <div class="card__footer"><a class="link-arrow" href="<?= e($href) ?>"<?= $external ? ' target="_blank" rel="noopener noreferrer"' : '' ?>><?= e($cta) ?><span class="visually-hidden">: <?= e($post['title']) ?></span> <?= icon($external ? 'external-link' : 'arrow-right', 'icon--arrow') ?></a></div>
    </div>
</article>
