<?php
/**
 * Admin > Events > one registration (edit contact details + internal notes, change status). OWNER: B8.
 * Vars: $event, $reg, $flow (status => allowed next statuses), $labels (status => label).
 */
$this->start('scripts'); ?>
<script src="<?= e(asset('js/pages/admin-org.js')) ?>" defer></script>
<?php $this->end();
$eid = (int) $event['id'];
$rid = (int) $reg['id'];
$base = 'admin/events/' . $eid . '/registrations';
$val = static fn(string $key, mixed $default = '') => old($key, $reg[$key] ?? $default);
$stepIcons = ['attended' => 'check', 'cancelled' => 'x', 'registered' => 'refresh'];
?>
<div class="admin-org">
    <?= partial('partials/admin/page-header', [
        'title'    => 'Registration',
        'subtitle' => $reg['name'] . ' - ' . $event['title'],
        'actions'  => [['label' => 'Back to registrations', 'url' => url($base), 'class' => 'btn--outline', 'icon' => 'arrow-left']],
    ]) ?>

    <form method="post" action="<?= e(url($base . '/' . $rid)) ?>" novalidate data-unsaved-guard>
        <?= csrf_field() ?>
        <div class="card-grid card-grid--main-aside">
            <section class="card" aria-labelledby="reg-details">
                <div class="card__header">
                    <h2 class="card__title" id="reg-details">Attendee</h2>
                    <span class="required-note"><span aria-hidden="true">*</span> required</span>
                </div>
                <div class="card__body">
                    <div class="form-grid">
                        <?= partial('partials/form-field', ['name' => 'name', 'label' => 'Name', 'required' => true, 'value' => $val('name'), 'maxlength' => 150, 'autocomplete' => 'off']) ?>
                        <?= partial('partials/form-field', ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true, 'value' => $val('email'), 'maxlength' => 190, 'autocomplete' => 'off']) ?>
                        <?= partial('partials/form-field', ['name' => 'phone', 'label' => 'Phone', 'type' => 'tel', 'value' => $val('phone'), 'maxlength' => 40, 'autocomplete' => 'off']) ?>
                        <?= partial('partials/form-field', ['name' => 'organisation', 'label' => 'Organisation', 'value' => $val('organisation'), 'maxlength' => 190, 'autocomplete' => 'off']) ?>
                        <?= partial('partials/form-field', ['name' => 'notes', 'label' => 'Internal notes', 'type' => 'textarea', 'rows' => 5, 'value' => $val('notes'), 'maxlength' => 2000, 'class' => 'field--full', 'attrs' => ['data-maxlength-counter' => true], 'help' => 'Only visible to signed-in admins. Never shown to the public.']) ?>
                    </div>
                </div>
            </section>

            <div class="admin-org__aside">
                <section class="card" aria-labelledby="reg-status">
                    <div class="card__header"><h2 class="card__title" id="reg-status">Status</h2></div>
                    <div class="card__body">
                        <p><?= partial('partials/admin/status-badge', ['status' => $reg['status'], 'label' => $labels[$reg['status']] ?? label_from($reg['status'])]) ?></p>
                        <dl class="definition-list">
                            <dt>Registered</dt><dd><?= e(date('j M Y, H:i', (int) strtotime((string) $reg['created_at']))) ?></dd>
                            <dt>Last change</dt><dd><?= e(date('j M Y, H:i', (int) strtotime((string) $reg['updated_at']))) ?></dd>
                            <?php if (!empty($reg['ip'])): ?><dt>IP address</dt><dd><?= e($reg['ip']) ?></dd><?php endif; ?>
                        </dl>
                    </div>
                </section>
            </div>
        </div>

        <div class="form-actions">
            <button class="btn btn--primary" type="submit"><?= icon('save', 'icon--sm') ?> Save changes</button>
            <a class="btn btn--ghost" href="<?= e(url($base)) ?>">Cancel</a>
            <?php if (can('inbox.delete')): ?>
                <button class="btn btn--ghost org-danger org-push-end" type="submit" form="delete-form"><?= icon('trash', 'icon--sm') ?> Delete</button>
            <?php endif; ?>
        </div>
    </form>

    <?php if (($flow[$reg['status']] ?? []) !== []): ?>
        <section class="card" aria-labelledby="reg-workflow">
            <div class="card__header"><h2 class="card__title" id="reg-workflow">Change status</h2></div>
            <div class="card__body">
                <div class="btn-row">
                    <?php foreach ($flow[$reg['status']] as $next):
                        $stepText = $next === 'attended' ? 'Mark attended' : ($next === 'cancelled' ? 'Cancel registration' : ($reg['status'] === 'attended' ? 'Undo attended' : 'Reinstate registration'));
                    ?>
                        <?= partial('admin/org/_row-action', [
                            'action' => url($base . '/' . $rid . '/status'),
                            'label'  => $stepText,
                            'text'   => $stepText,
                            'icon'   => $stepIcons[$next],
                            'class'  => 'btn--outline btn--sm',
                            'fields' => ['status' => $next],
                        ]) ?>
                    <?php endforeach; ?>
                </div>
                <p class="form-note">Registered &rarr; attended or cancelled. Attended and cancelled registrations can be moved back to registered.</p>
            </div>
        </section>
    <?php endif; ?>

    <?php if (can('inbox.delete')): ?>
        <form id="delete-form" method="post" action="<?= e(url($base . '/' . $rid . '/delete')) ?>" data-confirm="<?= e('Delete the registration of ' . $reg['name'] . '? Their personal details are removed and this cannot be undone.') ?>">
            <?= csrf_field() ?>
        </form>
    <?php endif; ?>
</div>
