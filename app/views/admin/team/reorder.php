<?php
/**
 * Admin > Team members > Reorder (drag handle or arrow buttons; behaviour = assets/js/admin.js [data-reorder]). OWNER: B8.
 * Vars: $byGroup (group => list of rows), $groups (group => label). The submitted ids[] order (DOM order) becomes sort_order 10, 20, ...
 */
$initials = static function (string $name): string {
    $out = '';
    foreach (array_slice(preg_split('/\s+/', trim($name)) ?: [], 0, 2) as $part) {
        $out .= mb_strtoupper(mb_substr($part, 0, 1));
    }
    return $out;
};
$any = false;
foreach ($byGroup as $list) {
    $any = $any || $list !== [];
}
?>
<div class="admin-org">
    <?= partial('partials/admin/page-header', [
        'title'    => 'Reorder team',
        'subtitle' => 'Drag a person by the handle, or use the arrows, then save. People keep to their own group.',
        'actions'  => [['label' => 'Back to the list', 'url' => url('admin/team'), 'class' => 'btn--outline', 'icon' => 'arrow-left']],
    ]) ?>

    <?php if (!$any): ?>
        <?= partial('partials/empty-state', [
            'title'   => 'Nothing to reorder yet',
            'text'    => 'Add team members first, then come back to arrange them.',
            'icon'    => 'users',
            'actions' => [['label' => 'Add a team member', 'url' => url('admin/team/create')]],
        ]) ?>
    <?php else: ?>
        <form method="post" action="<?= e(url('admin/team/reorder')) ?>" data-unsaved-guard>
            <?= csrf_field() ?>
            <?php foreach ($byGroup as $g => $list): if ($list === []) { continue; } ?>
                <section class="card" aria-labelledby="grp-<?= e($g) ?>">
                    <div class="card__header"><h2 class="card__title" id="grp-<?= e($g) ?>"><?= e($groups[$g] ?? label_from($g)) ?></h2><span class="text-muted small"><?= count($list) ?></span></div>
                    <div class="card__body">
                        <div class="reorder-list" data-reorder>
                            <?php foreach ($list as $m):
                                $photo = img_url((string) ($m['photo'] ?? ''));
                            ?>
                                <div class="reorder-item" data-reorder-item>
                                    <input type="hidden" name="ids[]" value="<?= (int) $m['id'] ?>">
                                    <span class="reorder-item__handle" data-drag-handle title="Drag to reorder"><?= icon('grip') ?><span class="visually-hidden">Drag handle</span></span>
                                    <?php if ($photo !== ''): ?>
                                        <img class="org-avatar" src="<?= e($photo) ?>" alt="" width="40" height="40" loading="lazy">
                                    <?php else: ?>
                                        <span class="org-avatar org-avatar--initials" aria-hidden="true"><?= e($initials((string) $m['name'])) ?></span>
                                    <?php endif; ?>
                                    <span class="reorder-item__body">
                                        <strong><?= e($m['name']) ?></strong>
                                        <?php if ($m['role_title'] !== ''): ?><span class="cell-sub"><?= e($m['role_title']) ?></span><?php endif; ?>
                                    </span>
                                    <?php if ((int) $m['is_active'] !== 1): ?><span class="badge badge--neutral">Hidden</span><?php endif; ?>
                                    <span class="reorder-item__tools">
                                        <button class="btn btn--ghost btn--sm btn--icon" type="button" data-move-up aria-label="Move <?= e($m['name']) ?> up"><?= icon('arrow-up', 'icon--sm') ?></button>
                                        <button class="btn btn--ghost btn--sm btn--icon" type="button" data-move-down aria-label="Move <?= e($m['name']) ?> down"><?= icon('arrow-down', 'icon--sm') ?></button>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </section>
            <?php endforeach; ?>
            <div class="form-actions">
                <button class="btn btn--primary" type="submit"><?= icon('save', 'icon--sm') ?> Save order</button>
                <a class="btn btn--ghost" href="<?= e(url('admin/team')) ?>">Cancel</a>
            </div>
        </form>
    <?php endif; ?>
</div>
