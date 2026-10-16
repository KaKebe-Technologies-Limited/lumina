<?php
/**
 * Graceful empty state (works in both bundles). Vars: $title (required), $text, $icon (icon name, default 'inbox'),
 * $actions (list of ['label','url','class'?]). All text from content()/admin copy - never hardcode public copy.
 */
?>
<div class="empty-state">
    <span class="empty-state__icon"><?= icon($icon ?? 'inbox', 'icon--xl') ?></span>
    <h3 class="empty-state__title"><?= e($title) ?></h3>
    <?php if (!empty($text)): ?><p class="empty-state__text"><?= e($text) ?></p><?php endif; ?>
    <?php if (!empty($actions)): ?>
        <div class="empty-state__actions">
            <?php foreach ($actions as $a): if (empty($a['label']) || empty($a['url'])) { continue; } ?>
                <a class="btn <?= e($a['class'] ?? 'btn--primary') ?>" href="<?= e($a['url']) ?>"><?= e($a['label']) ?></a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
