<?php /** Add a learner by hand (administrators). */ ?>
<?= partial('partials/admin/page-header', ['title' => 'Add a learner', 'subtitle' => 'For someone who cannot sign up alone, for example after a training day. The e-mail address counts as confirmed; a link lets them choose their own password.']) ?>
<form class="card" method="post" action="<?= e(url('admin/academy/learners')) ?>" novalidate>
    <?= csrf_field() ?>
    <div class="card__body">
        <div class="form-grid">
            <?= partial('partials/form-field', ['name' => 'name', 'label' => 'Full name', 'required' => true, 'maxlength' => 120, 'help' => 'As it should appear on certificates.']) ?>
            <?= partial('partials/form-field', ['name' => 'email', 'label' => 'E-mail address', 'type' => 'email', 'required' => true, 'maxlength' => 190]) ?>
            <?= partial('partials/form-field', ['name' => 'phone', 'label' => 'Phone (optional)', 'type' => 'tel', 'maxlength' => 40]) ?>
            <?= partial('partials/form-field', ['name' => 'country', 'label' => 'Country (optional)', 'maxlength' => 80]) ?>
        </div>
        <label class="check"><input type="checkbox" name="send_link" value="1" checked><span>E-mail them a link to choose a password (works for 7 days)</span></label>
    </div>
    <div class="card__footer">
        <button class="btn btn--primary" type="submit"><?= icon('user-plus', 'icon--sm') ?> Create the learner</button>
        <a class="btn btn--ghost" href="<?= e(url('admin/academy/learners')) ?>">Cancel</a>
    </div>
</form>
