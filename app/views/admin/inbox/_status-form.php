<?php
/**
 * Status + private notes card (messages, involvement, applications).
 * Vars: $action (POST url), $statuses (value => label), $current, $notes, $title (card title), $help.
 */
?>
<section class="card">
    <div class="card__header"><h2 class="card__title"><?= e($title ?? 'Status and notes') ?></h2></div>
    <form method="post" action="<?= e($action) ?>" data-unsaved-guard>
        <?= csrf_field() ?>
        <div class="card__body">
            <?= partial('partials/form-field', ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => $statuses, 'value' => old('status', $current), 'required' => true]) ?>
            <?= partial('partials/form-field', ['name' => 'admin_notes', 'label' => 'Private notes', 'type' => 'textarea', 'rows' => 5, 'value' => old('admin_notes', (string) $notes), 'maxlength' => 5000, 'help' => $help ?? 'Only the team can see these notes.']) ?>
            <?php if (!empty($notify)): ?>
                <?= partial('partials/form-field', ['name' => 'applicant_note', 'label' => 'Message to the applicant (optional)', 'type' => 'textarea', 'rows' => 3, 'maxlength' => 1000, 'help' => 'The applicant is e-mailed automatically when the status changes. This line is added to that e-mail.']) ?>
            <?php endif; ?>
        </div>
        <div class="card__footer"><button class="btn btn--primary" type="submit"><?= icon('save', 'icon--sm') ?> Save</button></div>
    </form>
</section>
