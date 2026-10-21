<?php
/** Virtual class form. Vars: $row, $class, $isNew, $action, $platforms, $manager, $facilitators, $courses, $learnersCount. */
$v = static fn(string $k, mixed $d = '') => old($k, $row[$k] ?? $d);
$this->start('scripts'); ?>
<script src="<?= e(asset('js/admin-academy.js')) ?>" defer></script>
<?php $this->end(); ?>
<?= partial('partials/admin/page-header', ['title' => $isNew ? 'Schedule a virtual class' : 'Edit virtual class', 'subtitle' => $isNew ? 'Set the time and the meeting link. All times are East Africa Time.' : (string) $class['title']]) ?>
<form method="post" action="<?= e($action) ?>" novalidate data-unsaved-guard>
    <?= csrf_field() ?>
    <div class="card-grid card-grid--main-aside">
        <div class="content-main">
            <section class="card" aria-labelledby="k-main">
                <div class="card__header"><h2 class="card__title" id="k-main">The class</h2></div>
                <div class="card__body">
                    <div class="form-grid">
                        <?= partial('partials/form-field', ['name' => 'title', 'label' => 'Title', 'required' => true, 'maxlength' => 190, 'value' => $v('title'), 'class' => 'field--full', 'help' => 'For example: Word practice session: letters and CVs']) ?>
                        <?= partial('partials/form-field', ['name' => 'description', 'label' => 'What the class is about', 'type' => 'textarea', 'rows' => 3, 'maxlength' => 1500, 'value' => $v('description'), 'class' => 'field--full', 'help' => 'Shown to learners. Plain text.']) ?>
                        <?= partial('partials/form-field', ['name' => 'date', 'label' => 'Date', 'type' => 'date', 'required' => true, 'value' => $v('date')]) ?>
                        <?= partial('partials/form-field', ['name' => 'time', 'label' => 'Start time (24 hours)', 'type' => 'time', 'required' => true, 'value' => $v('time'), 'help' => 'East Africa Time (Uganda).']) ?>
                        <?= partial('partials/form-field', ['name' => 'duration', 'label' => 'Length (minutes)', 'type' => 'number', 'min' => 15, 'max' => 600, 'step' => 15, 'required' => true, 'value' => $v('duration', 60)]) ?>
                    </div>
                </div>
            </section>
            <section class="card" aria-labelledby="k-link">
                <div class="card__header"><h2 class="card__title" id="k-link">Meeting link</h2></div>
                <div class="card__body">
                    <div class="form-grid">
                        <?= partial('partials/form-field', ['name' => 'platform', 'label' => 'Platform', 'type' => 'select', 'options' => $platforms, 'value' => $v('platform', 'meet'), 'required' => true]) ?>
                        <?= partial('partials/form-field', ['name' => 'passcode', 'label' => 'Passcode (optional)', 'maxlength' => 60, 'value' => $v('passcode')]) ?>
                        <?= partial('partials/form-field', ['name' => 'join_url', 'label' => 'Join link', 'type' => 'url', 'maxlength' => 500, 'value' => $v('join_url'), 'class' => 'field--full', 'help' => 'Create the meeting in Google Meet, Zoom or Teams and paste its address here. Learners see it only after they sign in.']) ?>
                    </div>
                    <p><button class="btn btn--outline btn--sm" type="button" data-aac-jitsi="#f-join_url"><?= icon('video', 'icon--sm') ?> Make a free Jitsi link</button>
                        <span class="text-muted">A free room that needs no account. Anyone with the link can join, so it is shown only to the learners who may attend.</span></p>
                    <?= partial('partials/form-field', ['name' => 'recording_url', 'label' => 'Recording link (after the class)', 'type' => 'url', 'maxlength' => 500, 'value' => $v('recording_url'), 'help' => 'Optional. Shown to signed-in learners once the class is over.']) ?>
                </div>
            </section>
        </div>
        <aside class="content-aside" aria-label="Class settings">
            <section class="card" aria-labelledby="k-who">
                <div class="card__header"><h2 class="card__title" id="k-who">Who may attend</h2></div>
                <div class="card__body">
                    <?= partial('partials/form-field', ['name' => 'audience', 'label' => 'Open to', 'type' => 'select', 'options' => ['course' => 'Learners enrolled in the course', 'all' => 'Every signed-in learner'], 'value' => $v('audience', 'course'), 'required' => true]) ?>
                    <div class="field"><label class="field__label" for="f-course_id">Course</label>
                        <select class="select" id="f-course_id" name="course_id"><option value="">No course</option>
                            <?php foreach ($courses as $c): ?><option value="<?= (int) $c['id'] ?>"<?= (int) $v('course_id', 0) === (int) $c['id'] ? ' selected' : '' ?>><?= e($c['title']) ?></option><?php endforeach; ?></select>
                        <?php if (error('course_id')): ?><div class="field__error"><?= e((string) error('course_id')) ?></div><?php endif; ?></div>
                    <?php if ($manager): ?>
                        <div class="field"><label class="field__label" for="f-facilitator_id">Facilitator</label>
                            <select class="select" id="f-facilitator_id" name="facilitator_id"><option value="">Not set</option>
                                <?php foreach ($facilitators as $f): ?><option value="<?= (int) $f['id'] ?>"<?= (int) $v('facilitator_id', 0) === (int) $f['id'] ? ' selected' : '' ?>><?= e($f['name']) ?></option><?php endforeach; ?></select></div>
                    <?php endif; ?>
                    <?= partial('partials/form-field', ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['scheduled' => 'Scheduled', 'cancelled' => 'Cancelled', 'completed' => 'Completed'], 'value' => $v('status', 'scheduled'), 'required' => true]) ?>
                </div>
            </section>
        </aside>
    </div>
    <div class="form-actions">
        <button class="btn btn--primary" type="submit"><?= icon('save', 'icon--sm') ?> <?= $isNew ? 'Schedule the class' : 'Save changes' ?></button>
        <a class="btn btn--ghost" href="<?= e(url('admin/academy/classes')) ?>">Back to the classes</a>
    </div>
</form>
<?php if (!$isNew && $class['status'] === 'scheduled'): ?>
    <section class="card" aria-labelledby="k-mail">
        <div class="card__header"><h2 class="card__title" id="k-mail">E-mail the learners</h2></div>
        <form class="card__body" method="post" action="<?= e(url('admin/academy/classes/' . (int) $class['id'] . '/notify')) ?>" data-confirm="E-mail <?= (int) $learnersCount ?> learner(s) about this class?">
            <?= csrf_field() ?>
            <p><?= (int) $learnersCount ?> learner<?= (int) $learnersCount === 1 ? '' : 's' ?> may attend<?= (string) setting('academy_class_reminder_hours', '24') !== '0' ? '. A reminder is also e-mailed automatically ' . e((string) setting('academy_class_reminder_hours', '24')) . ' hour(s) before the class by the reminder cron job.' : '.' ?>
                Each learner is e-mailed at most once an hour about the same class. The e-mail holds the time and a link to the class page, not the join link itself.</p>
            <label class="check"><input type="checkbox" name="reminder" value="1"><span>Word it as a reminder</span></label>
            <button class="btn btn--outline" type="submit"><?= icon('mail', 'icon--sm') ?> Send the e-mail now</button>
        </form>
    </section>
<?php endif; ?>
