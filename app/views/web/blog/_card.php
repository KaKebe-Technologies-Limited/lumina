<?php
/**
 * Post card for the Blog, News and Press listings (B-INNER-B; used instead of partials/post-card on these pages).
 * Vars: $post (posts row), $cta (link label), $featured (bool: large horizontal card for the newest post), $badge (small label on the
 * featured card), $tag (heading tag, default h3).
 * Press items that carry an external_url link out (new tab). A post without a cover photo gets a navy tile with an icon: it is purely
 * decorative and never pretends to be a photograph.
 */
$type = (string) ($post['post_type'] ?? 'blog');
$ext = trim((string) ($post['external_url'] ?? ''));
$external = $type === 'press' && $ext !== '' && preg_match('~^https?://~i', $ext) === 1;
$href = $external ? $ext : url($type . '/' . $post['slug']);
$cta = $cta ?? (string) content('global', 'labels.read_more');
$img = img_url($post['cover_image'] ?? '');
$tag = in_array($tag ?? 'h3', ['h2', 'h3', 'h4'], true) ? ($tag ?? 'h3') : 'h3';
$featured = !empty($featured);
$cat = trim((string) ($post['category'] ?? ''));
$source = trim((string) ($post['source_name'] ?? ''));
$badge = trim((string) ($badge ?? ''));
$linkAttrs = $external ? ' target="_blank" rel="noopener noreferrer"' : '';
?>
<article class="card post-card<?= $featured ? ' post-card--feature' : '' ?>" data-reveal>
    <a class="card__media<?= $img === '' ? ' card__media--ph' : '' ?>" href="<?= e($href) ?>" tabindex="-1" aria-hidden="true"<?= $linkAttrs ?>>
        <?php if ($img !== ''): ?>
            <img src="<?= e($img) ?>" alt="" loading="lazy" decoding="async">
        <?php else: ?>
            <span class="card__ph"><?= icon($type === 'press' ? 'mic' : 'newspaper', 'icon--xl') ?></span>
        <?php endif; ?>
        <?php if ($cat !== ''): ?><span class="card__tag"><?= e($cat) ?></span><?php endif; ?>
    </a>
    <div class="card__body">
        <?php if ($featured && $badge !== ''): ?><span class="eyebrow post-card__badge"><?= e($badge) ?></span><?php endif; ?>
        <div class="card__meta">
            <?php if (!empty($post['published_at'])): ?><time datetime="<?= e(format_date($post['published_at'], 'Y-m-d')) ?>"><?= e(format_date($post['published_at'])) ?></time><?php endif; ?>
            <?php if ($source !== ''): ?><span><?= e($source) ?></span><?php endif; ?>
        </div>
        <<?= $tag ?> class="card__title"><a href="<?= e($href) ?>"<?= $linkAttrs ?>><?= e($post['title']) ?></a></<?= $tag ?>>
        <?php if (trim((string) ($post['excerpt'] ?? '')) !== ''): ?><p class="card__text"><?= e($post['excerpt']) ?></p><?php endif; ?>
        <div class="card__footer">
            <?php if ($featured): ?>
                <a class="btn btn--cta btn--caps btn--sm" href="<?= e($href) ?>"<?= $linkAttrs ?>><?= e($cta) ?><span class="visually-hidden">: <?= e($post['title']) ?></span> <?= icon($external ? 'external-link' : 'arrow-right', 'icon--sm') ?></a>
            <?php else: ?>
                <a class="link-arrow" href="<?= e($href) ?>"<?= $linkAttrs ?>><?= e($cta) ?><span class="visually-hidden">: <?= e($post['title']) ?></span> <?= icon($external ? 'external-link' : 'arrow-right', 'icon--arrow') ?></a>
            <?php endif; ?>
        </div>
    </div>
</article>
