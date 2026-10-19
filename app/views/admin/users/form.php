<?php
/** Add / edit a user. OWNER: B3. Vars: $mode, $row, $self, $lastAdmin, $roles. */
$creating = $mode === 'create';
$action = $creating ? url('admin/users') : url('admin/users/' . (int) $row['id']);
$v = static fn(string $k, mixed $d = ''): string => (string) old($k, $row[$k] ?? $d);
$role = $v('role', 'editor');
$active = (string) old('is_active', (int) ($row['is_active'] ?? 1) === 1 ? '1' : '0') === '1';
$locked = $self || $lastAdmin;
?>
<div class="admin-pages-editor">
<?= partial('partials/admin/page-header', ['title' => $creating ? 'Add a user' : 'Edit user', 'subtitle' => $creating ? 'A temporary password is created for the new account and shown to you once.' : '']) ?>
<form class="card pe-narrow-wide" method="post" action="<?= e($action) ?>" data-unsaved-guard novalidate>
    <?= csrf_field() ?>
    <div class="card__body">
        <div class="form-grid">
            <?= partial('partials/form-field', ['name' => 'name', 'label' => 'Full name', 'value' => $v('name'), 'required' => true, 'maxlength' => 120, 'autocomplete' => 'off']) ?>
            <?= partial('partials/form-field', ['name' => 'email', 'label' => 'Email address', 'type' => 'email', 'value' => $v('email'), 'required' => true, 'maxlength' => 190, 'autocomplete' => 'off', 'help' => 'They sign in with this address.']) ?>
            <fieldset class="field field--full pe-roles"<?= $locked ? ' disabled' : '' ?>>
                <legend class="field__label">Role</legend>
                <?php foreach ($roles as $rv => $rl): ?>
                    <label class="pe-role"><input type="radio" name="role" value="<?= e($rv) ?>"<?= $role === $rv ? ' checked' : '' ?>><span><strong><?= e(trim(explode(' - ', $rl, 2)[0])) ?></strong><small><?= e(substr($rl, strpos($rl, ' - ') + 3)) ?></small></span></label>
                <?php endforeach; ?>
                <?php if (has_error('role')): ?><div class="field__error" role="alert"><?= e(error('role')) ?></div><?php endif; ?>
                <?php if ($self): ?><div class="field__help">You cannot change your own role.</div><?php elseif ($lastAdmin): ?><div class="field__help">This is the only active administrator, so the role cannot be changed.</div><?php endif; ?>
            </fieldset>
            <?php if ($locked): ?><input type="hidden" name="role" value="<?= e((string) ($row['role'] ?? 'editor')) ?>"><?php endif; ?>
            <?php if (!$creating): ?>
                <div class="field pe-switches">
                    <label class="switch"><input type="checkbox" name="is_active" value="1"<?= $active ? ' checked' : '' ?><?= $locked ? ' disabled' : '' ?>><span class="switch__track" aria-hidden="true"></span><span class="switch__text">Account is active (can sign in)</span></label>
                    <?php if ($locked): ?><input type="hidden" name="is_active" value="1"><div class="field__help"><?= $self ? 'You cannot disable your own account.' : 'The only active administrator cannot be disabled.' ?></div><?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="card__footer">
        <button class="btn btn--primary" type="submit"><?= icon($creating ? 'user-plus' : 'save', 'icon--sm') ?> <?= $creating ? 'Create account' : 'Save changes' ?></button>
        <a class="btn btn--ghost" href="<?= e(url('admin/users')) ?>">Cancel</a>
    </div>
</form>
</div>
