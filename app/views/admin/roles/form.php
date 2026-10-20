<?php
/** New / edit role. Vars: $mode, $row (name, description, abilities, is_system, slug), $catalog, $users (people holding it). */
$creating = $mode === 'create';
$action = $creating ? url('admin/roles') : url('admin/roles/' . (int) $row['id']);
$v = static fn(string $k, mixed $d = ''): string => (string) old($k, $row[$k] ?? $d);
// old() answers '' (not null) when nothing was flashed, so ask for null explicitly: otherwise every ability shows unticked on a normal edit and a plain Save wipes the role
$oldAbilities = old('abilities', null);
$have = is_array($oldAbilities) ? $oldAbilities : (array) ($row['abilities'] ?? []);
?>
<div class="admin-pages-editor">
<?= partial('partials/admin/page-header', ['title' => $creating ? 'New role' : 'Edit role: ' . (string) $row['name'], 'subtitle' => 'Tick what people with this role are allowed to do. They only see the menu items they are allowed to use.']) ?>
<form class="card" method="post" action="<?= e($action) ?>" data-unsaved-guard novalidate>
    <?= csrf_field() ?>
    <div class="card__body">
        <div class="form-grid">
            <?= partial('partials/form-field', ['name' => 'name', 'label' => 'Role name', 'value' => $v('name'), 'required' => true, 'maxlength' => 80, 'help' => 'For example: Communications Officer']) ?>
            <?= partial('partials/form-field', ['name' => 'description', 'label' => 'What is this role for?', 'value' => $v('description'), 'maxlength' => 255, 'help' => 'Shown when you choose a role for a user.']) ?>
        </div>
        <?php if (!$creating && $users > 0): ?><p class="form-hint"><?= (int) $users ?> <?= $users === 1 ? 'person has' : 'people have' ?> this role. Changes apply to them straight away.</p><?php endif; ?>

        <?php foreach ($catalog as $gkey => $group): ?>
            <?php if ($gkey === 'admin'): continue; endif; ?>
            <fieldset class="role-group">
                <legend class="role-group__title"><?= e($group['label']) ?></legend>
                <?php foreach ($group['items'] as $ab => $def):
                    $locked = !empty($def['locked']);
                    $on = $locked || in_array($ab, $have, true); ?>
                    <label class="check role-ability">
                        <input type="checkbox" name="abilities[]" value="<?= e($ab) ?>"<?= $on ? ' checked' : '' ?><?= $locked ? ' disabled' : '' ?>>
                        <span><strong><?= e($def[0]) ?></strong><?php if (!empty($def['secrets'])): ?> <span class="badge badge--warning">Holds payment secrets</span><?php endif; ?><?php if (!empty($def[1])): ?><small><?= e($def[1]) ?></small><?php endif; ?></span>
                    </label>
                    <?php if ($locked): ?><input type="hidden" name="abilities[]" value="<?= e($ab) ?>"><?php endif; ?>
                <?php endforeach; ?>
            </fieldset>
        <?php endforeach; ?>
        <p class="form-hint">Creating users and roles is for administrators only and cannot be given to another role.</p>
    </div>
    <div class="card__footer">
        <button class="btn btn--primary" type="submit"><?= icon('save', 'icon--sm') ?> <?= $creating ? 'Create role' : 'Save role' ?></button>
        <a class="btn btn--ghost" href="<?= e(url('admin/roles')) ?>">Cancel</a>
    </div>
</form>
</div>
