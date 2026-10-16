<?php
/** FAQ list with re-ordering. OWNER: B3. Vars: $groups, $group, $counts, $rows, $hint, $canDelete. */
?>
<div class="admin-pages-editor">
<?= partial('partials/admin/page-header', [
    'title'    => 'Frequently asked questions',
    'subtitle' => 'Questions and answers shown on the website, grouped by where they appear.',
    'actions'  => [['label' => 'Add a question', 'url' => url('admin/faqs/create', ['group' => $group]), 'icon' => 'plus']],
]) ?>

<nav class="tabs" aria-label="Question groups">
    <?php foreach ($groups as $g): ?>
        <a class="tabs__item<?= $g === $group ? ' is-active' : '' ?>" href="<?= e(url('admin/faqs', ['group' => $g])) ?>"<?= $g === $group ? ' aria-current="page"' : '' ?>><?= e(label_from($g)) ?> <span class="pe-group__count"><?= (int) ($counts[$g] ?? 0) ?></span></a>
    <?php endforeach; ?>
</nav>
<p class="pe-section__help"><?= e($hint) ?></p>

<?php if ($rows === []): ?>
    <?= partial('partials/empty-state', [
        'title' => 'No questions in this group yet', 'text' => 'Add the questions people often ask, with short, clear answers.',
        'icon' => 'help-circle', 'actions' => [['label' => 'Add a question', 'url' => url('admin/faqs/create', ['group' => $group])]],
    ]) ?>
<?php else: ?>
    <form method="post" action="<?= e(url('admin/faqs/reorder')) ?>" data-unsaved-guard>
        <?= csrf_field() ?>
        <input type="hidden" name="group" value="<?= e($group) ?>">
        <div class="reorder-list" data-reorder>
            <?php foreach ($rows as $r): ?>
                <div class="reorder-item<?= (int) $r['is_active'] === 1 ? '' : ' is-unpublished' ?>" data-reorder-item>
                    <span class="reorder-item__handle" data-drag-handle aria-hidden="true" title="Drag to change the order"><?= icon('grip') ?></span>
                    <input type="hidden" name="ids[]" value="<?= (int) $r['id'] ?>">
                    <div class="reorder-item__body">
                        <strong><?= e($r['question']) ?></strong>
                        <span class="cell-sub"><?= e(excerpt((string) $r['answer'], 140)) ?></span>
                    </div>
                    <?= partial('partials/admin/status-badge', ['status' => (int) $r['is_active'] === 1 ? 'active' : 'hidden', 'label' => (int) $r['is_active'] === 1 ? 'Shown' : 'Hidden']) ?>
                    <span class="reorder-item__tools">
                        <button class="repeater__tool" type="button" data-move-up aria-label="Move up"><?= icon('arrow-up', 'icon--sm') ?></button>
                        <button class="repeater__tool" type="button" data-move-down aria-label="Move down"><?= icon('arrow-down', 'icon--sm') ?></button>
                        <a class="btn btn--outline btn--sm" href="<?= e(url('admin/faqs/' . (int) $r['id'] . '/edit')) ?>"><?= icon('edit', 'icon--sm') ?> Edit</a>
                        <button class="btn btn--ghost btn--sm" type="submit" formaction="<?= e(url('admin/faqs/' . (int) $r['id'] . '/toggle')) ?>"><?= (int) $r['is_active'] === 1 ? 'Hide' : 'Show' ?></button>
                        <?php if ($canDelete): ?><button class="btn btn--ghost btn--sm pe-danger" type="submit" formaction="<?= e(url('admin/faqs/' . (int) $r['id'] . '/delete')) ?>" data-confirm="Delete this question permanently?" aria-label="Delete the question <?= e(excerpt((string) $r['question'], 40)) ?>"><?= icon('trash', 'icon--sm') ?></button><?php endif; ?>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="form-actions pe-actions">
            <span class="pe-actions__state" data-pe-state role="status" aria-live="polite">Drag or use the arrows to re-order, then save the order.</span>
            <span class="pe-actions__buttons"><button class="btn btn--primary" type="submit"><?= icon('save', 'icon--sm') ?> Save order</button></span>
        </div>
    </form>
<?php endif; ?>
</div>
<?php $this->start('scripts'); ?><script src="<?= e(asset('js/pages/admin-pages.js')) ?>" defer></script><?php $this->end(); ?>
