<?php
/**
 * QUICK ADD: the easy path. Three things are needed (what, when, which type); everything else sits under "More options".
 * With JavaScript this form lives in a dialog that opens when you press "Add a date" or click a day; without it, it is a normal card on the page.
 * Posts to admin/calendar/quick-add (CalendarWriter::quickAdd). On a mistake the controller sends you back here with your input and the
 * messages next to the fields; the dialog then opens by itself (old('form') === 'quick').
 * Vars: $quick (date, type), $addTypes (active types except Events), $returnMonth ('YYYY-MM' or '').
 */
$failed = old('form') === 'quick' && errors() !== [];
$moreKeys = ['date_end', 'start_time', 'end_time', 'location', 'repeat', 'until'];
$moreHasError = false;
foreach ($moreKeys as $k) {
    $moreHasError = $moreHasError || has_error($k);
}
$allDay = (string) old('all_day', '1') !== '0';
$typeOptions = [];
foreach ($addTypes as $t) {
    $typeOptions[$t['slug']] = $t['name'];
}
?>
<dialog class="cal-quick" id="quick-add" aria-labelledby="quick-title" data-cal-quick<?= $failed ? ' data-open="1"' : '' ?>>
    <form class="cal-quick__form" method="post" action="<?= e(url('admin/calendar/quick-add')) ?>" data-cal-quick-form>
        <?= csrf_field() ?>
        <input type="hidden" name="form" value="quick">
        <input type="hidden" name="return_month" value="<?= e($returnMonth) ?>">
        <div class="cal-quick__head">
            <div>
                <h2 class="cal-quick__title" id="quick-title">Add a date</h2>
                <p class="cal-quick__sub" data-cal-quick-sub>Tell us what is happening and when. It appears on the calendar straight away.</p>
            </div>
            <button class="btn btn--ghost btn--sm cal-quick__close" type="button" data-cal-close aria-label="Close"><?= icon('x', 'icon--sm') ?></button>
        </div>
        <?php if (error('_') !== null): ?><div class="alert alert--error" role="alert"><?= icon('alert-circle') ?><div class="alert__body"><?= e((string) error('_')) ?></div></div><?php endif; ?>

        <div class="cal-quick__body">
            <?= partial('partials/form-field', ['name' => 'title', 'label' => 'What is happening?', 'required' => true, 'maxlength' => 190, 'placeholder' => 'For example: Digital skills workshop', 'id' => 'q-title', 'autocomplete' => 'off']) ?>
            <div class="form-grid">
                <?= partial('partials/form-field', ['name' => 'date', 'label' => 'Date', 'type' => 'date', 'required' => true, 'id' => 'q-date', 'value' => old('date', $quick['date'])]) ?>
                <?= partial('partials/form-field', ['name' => 'type', 'label' => 'Type', 'type' => 'select', 'id' => 'q-type', 'options' => $typeOptions, 'value' => old('type', $quick['type'])]) ?>
            </div>
            <input type="hidden" name="public" value="0">
            <label class="check cal-quick__public"><input type="checkbox" name="public" value="1"<?= (string) old('public', '1') !== '0' ? ' checked' : '' ?>>
                <span>Show it on the website <small>(visitors can see it in the calendar)</small></span></label>

            <details class="cal-more"<?= ($moreHasError || (old('form') === 'quick' && (old('repeat', 'none') !== 'none' || old('location', '') !== '' || old('date_end', '') !== ''))) ? ' open' : '' ?>>
                <summary>More options: end date, time, place, repeat</summary>
                <div class="cal-more__body">
                    <div class="form-grid">
                        <?= partial('partials/form-field', ['name' => 'date_end', 'label' => 'Last day (only if it lasts several days)', 'type' => 'date', 'id' => 'q-date-end']) ?>
                        <?= partial('partials/form-field', ['name' => 'location', 'label' => 'Place', 'maxlength' => 255, 'id' => 'q-location', 'placeholder' => 'For example: Gulu City']) ?>
                    </div>
                    <input type="hidden" name="all_day" value="0">
                    <label class="check"><input type="checkbox" name="all_day" value="1" data-cal-allday<?= $allDay ? ' checked' : '' ?>><span>All day <small>(untick to set a start and end time, East Africa Time)</small></span></label>
                    <div class="form-grid cal-times" data-cal-times<?= $allDay ? ' hidden' : '' ?>>
                        <?= partial('partials/form-field', ['name' => 'start_time', 'label' => 'Starts at', 'type' => 'time', 'id' => 'q-start-time']) ?>
                        <?= partial('partials/form-field', ['name' => 'end_time', 'label' => 'Ends at (optional)', 'type' => 'time', 'id' => 'q-end-time']) ?>
                    </div>
                    <div class="form-grid">
                        <?= partial('partials/form-field', ['name' => 'repeat', 'label' => 'Repeat', 'type' => 'select', 'id' => 'q-repeat', 'value' => old('repeat', 'none'),
                            'options' => ['none' => 'Does not repeat', 'daily' => 'Every day', 'weekly' => 'Every week', 'monthly' => 'Every month', 'yearly' => 'Every year'],
                            'attrs' => ['data-cal-quick-repeat' => true]]) ?>
                        <div data-cal-quick-until<?= old('repeat', 'none') === 'none' ? ' hidden' : '' ?>>
                            <?= partial('partials/form-field', ['name' => 'until', 'label' => 'Repeat until (optional)', 'type' => 'date', 'id' => 'q-until', 'help' => 'Leave empty to repeat for ever.']) ?>
                        </div>
                    </div>
                </div>
            </details>
        </div>
        <div class="cal-quick__foot">
            <button class="btn btn--primary" type="submit"><?= icon('plus', 'icon--sm') ?> Add to calendar</button>
            <button class="btn btn--ghost" type="button" data-cal-close>Cancel</button>
            <a class="btn btn--link cal-quick__full" href="<?= e(url('admin/calendar/create', ['date' => $quick['date']])) ?>" data-cal-full-form>Need a description, links or a special repeat? Use the full form</a>
        </div>
    </form>
</dialog>
