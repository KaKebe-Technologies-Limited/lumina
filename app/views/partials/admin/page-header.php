<?php
/**
 * Admin page heading. Vars: $title (required), $subtitle, $actions = list of ['label','url','class'? (btn--primary default),
 * 'icon'? (icon name), 'confirm'? (message), 'method'? ('post' renders a mini-form with CSRF)]. Place at the top of every admin view.
 *   <?= partial('partials/admin/page-header', ['title' => 'Posts', 'actions' => [['label' => 'New post', 'url' => url('admin/posts/create'), 'icon' => 'plus']]]) ?>
 */
?>
<div class="page-header">
    <div>
        <h1 class="page-header__title"><?= e($title) ?></h1>
        <?php if (!empty($subtitle)): ?><p class="page-header__subtitle"><?= e($subtitle) ?></p><?php endif; ?>
    </div>
    <?php if (!empty($actions)): ?>
        <div class="page-header__actions">
            <?php foreach ($actions as $a): ?>
                <?php if (($a['method'] ?? '') === 'post'): ?>
                    <form method="post" action="<?= e($a['url']) ?>"<?= !empty($a['confirm']) ? ' data-confirm="' . e($a['confirm']) . '"' : '' ?>>
                        <?= csrf_field() ?>
                        <button class="btn <?= e($a['class'] ?? 'btn--outline') ?>" type="submit"><?= !empty($a['icon']) ? icon($a['icon'], 'icon--sm') . ' ' : '' ?><?= e($a['label']) ?></button>
                    </form>
                <?php else: ?>
                    <a class="btn <?= e($a['class'] ?? 'btn--primary') ?>" href="<?= e($a['url']) ?>"<?= !empty($a['target']) ? ' target="' . e($a['target']) . '" rel="noopener"' : '' ?><?= !empty($a['confirm']) ? ' data-confirm="' . e($a['confirm']) . '"' : '' ?>><?= !empty($a['icon']) ? icon($a['icon'], 'icon--sm') . ' ' : '' ?><?= e($a['label']) ?></a>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
