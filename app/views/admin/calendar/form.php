<?php
/**
 * Admin > Calendar > Add a date / Edit a date: the full editor. OWNER: ADMIN agent.
 * Vars: $entry (row or defaults), $isNew, $types, $type, $canAdvanced, $advancedUsed, $locked (an editor looking at an advanced rule), $rule (display
 * values of the repeat settings), $ruleText, $preview (next 5 dates, same engine as the public calendar), $occurrences (next dates of a repeating entry, with
 * cancelled ones), $overrides (every one-off change), $islamic, $programs, $projects, $events, $today, $canDelete.
 * The repeat settings are friendly fields (rule_*); CalendarController::ruleFromForm turns them into the stored JSON (same code as the live preview).
 */
use App\Services\Calendar\CalendarService;
use App\Services\Calendar\CivilDate;

$this->start('scripts'); ?>
<script src="<?= e(asset('js/admin-editor.js')) ?>" defer></script>
<script src="<?= e(asset('js/admin-calendar.js')) ?>" defer></script>
<?php $this->end(); ?>
<?php
$id = $isNew ? null : (int) $entry['id'];
$isSystem = (int) $entry['is_system'] === 1;
$fieldLabels = [
    'title' => 'Title', 'type_id' => 'Type', 'description' => 'Description', 'location' => 'Place', 'start_date' => 'Start date', 'end_date' => 'Last day', 'start_time' => 'Start time',
    'end_time' => 'End time', 'recurrence' => 'Repeat', 'recurrence_data' => 'Repeat settings', 'repeat_until' => 'Repeat until', 'program_id' => 'Program', 'project_id' => 'Project',
    'event_id' => 'Event', 'link_url' => 'Link', 'source_url' => 'Source link', 'source_label' => 'Source', 'color' => 'Colour', '_' => '',
];
$rec = (string) old('recurrence', (string) $entry['recurrence']);
$rec = in_array($rec, ['none', 'daily', 'weekly', 'monthly', 'yearly', 'nth_weekday', 'easter', 'islamic'], true) ? $rec : 'none';
$allDay = (string) old('all_day', (string) (int) $entry['all_day']) !== '0';
$hm = static fn($t): string => $t ? substr((string) $t, 0, 5) : '';
$weekdays = (array) old('rule_weekdays', $rule['weekdays']);
$weekdays = array_map('strval', $weekdays);
$monthlyMode = (string) old('rule_monthly_mode', $rule['monthly_mode']);
$easterPreset = (string) old('rule_easter', in_array($rule['easter_offset'], [-2, 0, 1], true) ? (string) $rule['easter_offset'] : 'custom');
$nthOptions = [1 => '1st', 2 => '2nd', 3 => '3rd', 4 => '4th', 5 => '5th', -1 => 'last'];
$weekdayNames = CivilDate::WEEKDAYS;
$monthNames = CivilDate::MONTHS;
$typeOptions = [];
foreach ($types as $t) {
    $typeOptions[$t['id']] = $t['name'];
}
$typeId = (int) old('type_id', (int) $entry['type_id']);
$color = (string) old('color', (string) ($entry['color'] ?? ''));
$linksHaveData = false;
foreach (['link_url', 'link_label', 'program_id', 'project_id', 'event_id', 'source_label', 'source_url'] as $k) {
    $linksHaveData = $linksHaveData || (string) old($k, (string) ($entry[$k] ?? '')) !== '' || has_error($k);
}
$panel = static fn(string $forTypes): string => ' data-cal-rule="' . e($forTypes) . '"' . (in_array($rec, explode(' ', $forTypes), true) ? '' : ' hidden');
$startDate = (string) old('start_date', (string) $entry['start_date']);
$publicUrl = (!$isNew && (int) $entry['is_active'] === 1 && (int) $entry['is_public'] === 1) ? url('calendar/' . $entry['slug']) : '';
$action = $isNew ? url('admin/calendar') : url('admin/calendar/' . $id);
$errs = errors();
unset($errs['_']);
?>
<div class="admin-calendar cal-editor-page" data-cal-page="editor">
    <div class="page-header">
        <div>
            <h1 class="page-header__title"><?= $isNew ? 'Add a date' : e((string) $entry['title']) ?></h1>
            <p class="page-header__subtitle">
                <?php if ($isNew): ?>Fill in what is happening and when. Only the title, the type and the start date are required.
                <?php else: ?>
                    <?php if ($type): ?><span class="cal-typechip" style="--cal-solid:<?= e($type['pair']['solid']) ?>;--cal-tint:<?= e($type['pair']['tint']) ?>;--cal-text:<?= e($type['pair']['text']) ?>"><?= icon((string) $type['icon'], 'icon--sm') ?><?= e($type['name']) ?></span><?php endif; ?>
                    <?= (int) $entry['is_active'] !== 1 ? '<span class="badge badge--neutral">Hidden</span>' : ((int) $entry['is_public'] !== 1 ? '<span class="badge badge--neutral">Team only</span>' : '<span class="badge badge--success">On the website</span>') ?>
                    <?= $isSystem ? '<span class="badge badge--outline" title="Came with the site. It can be edited or hidden, but not deleted.">Seeded</span>' : '' ?>
                    <?= e($ruleText) ?>
                <?php endif; ?>
            </p>
        </div>
        <div class="page-header__actions">
            <a class="btn btn--outline" href="<?= e(url('admin/calendar', $isNew ? ['month' => substr($startDate, 0, 7)] : ['month' => substr((string) $entry['start_date'], 0, 7)])) ?>"><?= icon('arrow-left', 'icon--sm') ?> Back to the calendar</a>
            <?php if ($publicUrl !== ''): ?><a class="btn btn--ghost" href="<?= e($publicUrl) ?>" target="_blank" rel="noopener"><?= icon('external-link', 'icon--sm') ?> View on the website</a><?php endif; ?>
            <?php if (!$isNew && $entry['recurrence'] !== 'none'): ?><a class="btn btn--ghost" href="#dates"><?= icon('calendar', 'icon--sm') ?> Change a single date</a><?php endif; ?>
        </div>
    </div>

    <?php if ($isSystem): ?>
        <div class="alert alert--info" role="note"><?= icon('info') ?><div class="alert__body">This <?= $type && $type['slug'] === CalendarService::SLUG_HOLIDAY ? 'public holiday' : 'day' ?> came with the site. You can reword it, change its colour or hide it, but it cannot be deleted. To change a single year, use "Change a single date" below.</div></div>
    <?php endif; ?>

    <form id="cal-entry-form" class="cal-editor" method="post" action="<?= e($action) ?>" data-unsaved-guard data-cal-form
          data-preview-url="<?= e(url('admin/api/calendar/preview')) ?>" data-entry-id="<?= $id !== null ? (int) $id : '' ?>" data-locked="<?= $locked ? '1' : '0' ?>">
        <?= csrf_field() ?>
        <?php if ($errs !== [] || error('_') !== null): ?>
            <div class="alert alert--error cal-editor__errors" role="alert" id="form-errors">
                <?= icon('alert-circle') ?>
                <div class="alert__body"><strong class="alert__title">Some details need your attention.</strong>
                    <ul>
                        <?php if (error('_') !== null): ?><li><?= e((string) error('_')) ?></li><?php endif; ?>
                        <?php foreach ($errs as $k => $m): ?><li><?= ($fieldLabels[$k] ?? '') !== '' ? '<strong>' . e($fieldLabels[$k]) . ':</strong> ' : '' ?><?= e((string) $m) ?></li><?php endforeach; ?>
                    </ul>
                </div>
            </div>
        <?php endif; ?>

        <div class="cal-editor__main">
            <section class="card" aria-labelledby="sec-what">
                <div class="card__header"><h2 class="card__title" id="sec-what">1. What is happening?</h2></div>
                <div class="card__body">
                    <?= partial('partials/form-field', ['name' => 'title', 'label' => 'Title', 'required' => true, 'maxlength' => 190, 'value' => old('title', (string) $entry['title']),
                        'placeholder' => 'For example: Digital skills workshop', 'help' => 'Short and clear. Visitors see this on the calendar.']) ?>
                    <div class="field<?= has_error('type_id') ? ' field--error' : '' ?>">
                        <label class="field__label" for="f-type_id">Type <span class="field__req" aria-hidden="true">*</span></label>
                        <?php if ($isSystem): ?>
                            <input type="hidden" name="type_id" value="<?= (int) $entry['type_id'] ?>">
                            <select class="select" id="f-type_id" disabled aria-describedby="f-type_id-help"><?php foreach ($typeOptions as $tid => $tname): ?><option<?= (int) $tid === (int) $entry['type_id'] ? ' selected' : '' ?>><?= e($tname) ?></option><?php endforeach; ?></select>
                        <?php else: ?>
                            <select class="select" id="f-type_id" name="type_id" required aria-describedby="f-type_id-help">
                                <?php foreach ($typeOptions as $tid => $tname): ?><option value="<?= (int) $tid ?>"<?= (int) $tid === $typeId ? ' selected' : '' ?>><?= e($tname) ?></option><?php endforeach; ?>
                            </select>
                        <?php endif; ?>
                        <div class="field__help" id="f-type_id-help"><?= $isSystem ? 'The type of a holiday or day that came with the site cannot be changed.' : 'The type decides the colour and shape on the calendar. Manage types under Types &amp; colours. Events are added under Events and show up here by themselves.' ?></div>
                        <?php if (error('type_id')): ?><div class="field__error" role="alert"><?= e((string) error('type_id')) ?></div><?php endif; ?>
                    </div>
                    <?= partial('partials/admin/richtext-field', ['name' => 'description', 'label' => 'Description (optional)', 'rows' => 8, 'value' => old('description', (string) ($entry['description'] ?? '')),
                        'help' => 'A few lines for visitors: what it is, who it is for. Keep it short.']) ?>
                    <?= partial('partials/form-field', ['name' => 'location', 'label' => 'Place (optional)', 'maxlength' => 255, 'value' => old('location', (string) ($entry['location'] ?? '')),
                        'placeholder' => 'For example: Gulu City, or Online']) ?>
                </div>
            </section>

            <section class="card" aria-labelledby="sec-when">
                <div class="card__header"><h2 class="card__title" id="sec-when">2. When?</h2></div>
                <div class="card__body">
                    <?php if ($locked): ?>
                        <dl class="cal-facts">
                            <div><dt>Starts from</dt><dd><?= e(CivilDate::fmt((string) $entry['start_date'], 'l j F Y')) ?></dd></div>
                            <div><dt>Repeats</dt><dd><?= e($ruleText) ?></dd></div>
                        </dl>
                        <p class="cal-locked"><?= icon('lock', 'icon--sm') ?> This repeat was set up by an administrator, so only an administrator can change it. You can still change the time and everything else, and move or cancel a single date below.</p>
                    <?php else: ?>
                        <div class="form-grid">
                            <?= partial('partials/form-field', ['name' => 'start_date', 'label' => $isSystem ? 'First day this rule applies' : 'Date (or first day)', 'type' => 'date', 'required' => true,
                                'value' => $startDate, 'attrs' => ['data-cal-pv' => true],
                                'help' => $isSystem ? 'The rule never gives a date before this day. You rarely need to change it.' : null]) ?>
                            <?= partial('partials/form-field', ['name' => 'end_date', 'label' => 'Last day (only if it lasts several days)', 'type' => 'date', 'value' => old('end_date', (string) ($entry['end_date'] ?? '')),
                                'attrs' => ['data-cal-pv' => true, 'data-cal-end' => true], 'help' => 'Leave empty for a single day.']) ?>
                        </div>
                    <?php endif; ?>
                    <input type="hidden" name="all_day" value="0">
                    <label class="check"><input type="checkbox" name="all_day" value="1" data-cal-allday<?= $allDay ? ' checked' : '' ?>><span>All day <small>(untick to give a start and end time, East Africa Time)</small></span></label>
                    <div class="form-grid cal-times" data-cal-times<?= $allDay ? ' hidden' : '' ?>>
                        <?= partial('partials/form-field', ['name' => 'start_time', 'label' => 'Starts at', 'type' => 'time', 'value' => old('start_time', $hm($entry['start_time'] ?? null))]) ?>
                        <?= partial('partials/form-field', ['name' => 'end_time', 'label' => 'Ends at (optional)', 'type' => 'time', 'value' => old('end_time', $hm($entry['end_time'] ?? null))]) ?>
                    </div>
                </div>
            </section>

            <?php if (!$locked): ?>
            <section class="card" aria-labelledby="sec-repeat" data-cal-repeat-card>
                <div class="card__header"><h2 class="card__title" id="sec-repeat">3. Does it repeat?</h2></div>
                <div class="card__body">
                    <div class="field<?= has_error('recurrence') ? ' field--error' : '' ?>">
                        <label class="field__label" for="f-recurrence">Repeat</label>
                        <select class="select" id="f-recurrence" name="recurrence" data-cal-repeat data-cal-pv>
                            <option value="none"<?= $rec === 'none' ? ' selected' : '' ?>>Does not repeat</option>
                            <option value="daily"<?= $rec === 'daily' ? ' selected' : '' ?>>Every day</option>
                            <option value="weekly"<?= $rec === 'weekly' ? ' selected' : '' ?>>Every week</option>
                            <option value="monthly"<?= $rec === 'monthly' ? ' selected' : '' ?>>Every month</option>
                            <option value="yearly"<?= $rec === 'yearly' ? ' selected' : '' ?>>Every year</option>
                            <?php if ($canAdvanced || $advancedUsed): ?>
                                <optgroup label="Advanced (administrators)">
                                    <option value="nth_weekday"<?= $rec === 'nth_weekday' ? ' selected' : '' ?>>Every year on the 2nd Tuesday of a month (and similar)</option>
                                    <option value="easter"<?= $rec === 'easter' ? ' selected' : '' ?>>Every year, counted from Easter (Good Friday and similar)</option>
                                    <option value="islamic"<?= $rec === 'islamic' ? ' selected' : '' ?>>Islamic holiday (follows the moon)</option>
                                </optgroup>
                            <?php endif; ?>
                        </select>
                        <?php if (error('recurrence')): ?><div class="field__error" role="alert"><?= e((string) error('recurrence')) ?></div><?php endif; ?>
                        <?php if (!$canAdvanced): ?><div class="field__help">Special repeats (the 2nd Tuesday of February, Easter, Islamic holidays) can only be set up by an administrator.</div><?php endif; ?>
                    </div>

                    <div class="cal-rules">
                        <div class="cal-rule"<?= $panel('daily weekly monthly yearly nth_weekday') ?>>
                            <div class="field<?= has_error('recurrence_data') ? ' field--error' : '' ?>">
                                <label class="field__label" for="f-rule_interval">Repeat every</label>
                                <div class="cal-inline"><input class="input cal-narrow" id="f-rule_interval" type="number" name="rule_interval" min="1" max="99" step="1" inputmode="numeric" value="<?= (int) old('rule_interval', $rule['interval']) ?>" data-cal-pv>
                                    <span data-cal-unit>week(s)</span></div>
                                <div class="field__help">1 means every one; 2 means every other one.</div>
                            </div>
                        </div>

                        <fieldset class="cal-rule cal-days"<?= $panel('weekly') ?>>
                            <legend class="field__label">On these days</legend>
                            <div class="cal-days__list">
                                <?php for ($d = 1; $d <= 7; $d++): ?>
                                    <label class="cal-day-check"><input type="checkbox" name="rule_weekdays[]" value="<?= $d ?>"<?= in_array((string) $d, $weekdays, true) ? ' checked' : '' ?> data-cal-pv data-cal-weekday><span><?= e(substr($weekdayNames[$d], 0, 3)) ?></span></label>
                                <?php endfor; ?>
                            </div>
                            <div class="field__help">Tick every weekday it happens on. If you tick none, the weekday of the date above is used.</div>
                        </fieldset>

                        <?php if ($canAdvanced || $advancedUsed): ?>
                        <fieldset class="cal-rule"<?= $panel('monthly') ?>>
                            <legend class="field__label">Which day of the month?</legend>
                            <label class="check"><input type="radio" name="rule_monthly_mode" value="day" data-cal-monthly-mode data-cal-pv<?= $monthlyMode !== 'nth_weekday' ? ' checked' : '' ?>><span>The same date each month (for example the 15th)</span></label>
                            <label class="check"><input type="radio" name="rule_monthly_mode" value="nth_weekday" data-cal-monthly-mode data-cal-pv<?= $monthlyMode === 'nth_weekday' ? ' checked' : '' ?>><span>A weekday of the month (for example the 2nd Tuesday)</span></label>
                        </fieldset>
                        <?php endif; ?>

                        <div class="cal-rule cal-rule--grid"<?= $panel('monthly nth_weekday') ?> data-cal-nth-box<?= ($rec === 'monthly' && $monthlyMode !== 'nth_weekday') ? ' hidden' : '' ?>>
                            <div class="field"><label class="field__label" for="f-rule_nth">Which one</label>
                                <select class="select" id="f-rule_nth" name="rule_nth" data-cal-pv><?php foreach ($nthOptions as $v => $l): ?><option value="<?= $v ?>"<?= (int) old('rule_nth', $rule['nth']) === $v ? ' selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select></div>
                            <div class="field"><label class="field__label" for="f-rule_weekday">Weekday</label>
                                <select class="select" id="f-rule_weekday" name="rule_weekday" data-cal-pv><?php for ($d = 1; $d <= 7; $d++): ?><option value="<?= $d ?>"<?= (int) old('rule_weekday', $rule['weekday']) === $d ? ' selected' : '' ?>><?= e($weekdayNames[$d]) ?></option><?php endfor; ?></select></div>
                        </div>
                        <div class="cal-rule"<?= $panel('nth_weekday') ?>>
                            <div class="field"><label class="field__label" for="f-rule_month">In the month of</label>
                                <select class="select" id="f-rule_month" name="rule_month" data-cal-pv><?php foreach ($monthNames as $i => $mn): ?><option value="<?= (int) $i ?>"<?= (int) old('rule_month', $rule['month']) === (int) $i ? ' selected' : '' ?>><?= e($mn) ?></option><?php endforeach; ?></select></div>
                            <div class="field__help">Example: 2nd Tuesday of February gives Safer Internet Day every year.</div>
                        </div>

                        <div class="cal-rule cal-rule--grid"<?= $panel('easter') ?>>
                            <div class="field"><label class="field__label" for="f-rule_easter">Which day</label>
                                <select class="select" id="f-rule_easter" name="rule_easter" data-cal-easter data-cal-pv>
                                    <option value="-2"<?= $easterPreset === '-2' ? ' selected' : '' ?>>Good Friday (2 days before Easter Sunday)</option>
                                    <option value="0"<?= $easterPreset === '0' ? ' selected' : '' ?>>Easter Sunday</option>
                                    <option value="1"<?= $easterPreset === '1' ? ' selected' : '' ?>>Easter Monday (the day after)</option>
                                    <option value="custom"<?= $easterPreset === 'custom' ? ' selected' : '' ?>>Another day counted from Easter Sunday</option>
                                </select></div>
                            <div class="field" data-cal-easter-custom<?= $easterPreset === 'custom' ? '' : ' hidden' ?>><label class="field__label" for="f-rule_easter_custom">Days from Easter Sunday</label>
                                <input class="input cal-narrow" id="f-rule_easter_custom" type="number" name="rule_easter_custom" min="-366" max="366" step="1" value="<?= (int) old('rule_easter_custom', $rule['easter_offset']) ?>" data-cal-pv>
                                <div class="field__help">Negative = before, positive = after.</div></div>
                        </div>

                        <div class="cal-rule"<?= $panel('islamic') ?>>
                            <div class="field"><label class="field__label" for="f-rule_islamic">Which holiday</label>
                                <select class="select" id="f-rule_islamic" name="rule_islamic" data-cal-pv><?php foreach ($islamic as $key => $h): ?><option value="<?= e($key) ?>"<?= (string) old('rule_islamic', $rule['islamic']) === $key ? ' selected' : '' ?>><?= e($h[2]) ?></option><?php endforeach; ?></select></div>
                            <div class="alert alert--warning"><?= icon('alert-triangle') ?><div class="alert__body">These dates follow the moon, so the calendar can only <strong>predict</strong> them (it can be 1 or 2 days off). Each date shows "Date to be confirmed" until you confirm it under Holidays &amp; days.</div></div>
                        </div>

                        <div class="cal-rule"<?= $panel('daily weekly monthly yearly nth_weekday easter islamic') ?>>
                            <?= partial('partials/form-field', ['name' => 'repeat_until', 'label' => 'Repeat until (optional)', 'type' => 'date', 'value' => old('repeat_until', (string) ($entry['repeat_until'] ?? '')),
                                'attrs' => ['data-cal-pv' => true], 'help' => 'The last day it can start. Leave empty to repeat for ever.']) ?>
                        </div>
                    </div>
                </div>
            </section>
            <?php endif; ?>

            <details class="card cal-links" <?= $linksHaveData ? 'open' : '' ?>>
                <summary class="card__header"><span class="card__title">4. Links and source <small>(optional)</small></span></summary>
                <div class="card__body">
                    <div class="form-grid">
                        <?= partial('partials/form-field', ['name' => 'link_url', 'label' => 'Link for more information', 'type' => 'url', 'maxlength' => 500, 'value' => old('link_url', (string) ($entry['link_url'] ?? '')),
                            'placeholder' => 'https://', 'help' => 'Starts with http:// or https://. For example a registration page.']) ?>
                        <?= partial('partials/form-field', ['name' => 'link_label', 'label' => 'Link text', 'maxlength' => 120, 'value' => old('link_label', (string) ($entry['link_label'] ?? '')), 'placeholder' => 'For example: Register here']) ?>
                        <?= partial('partials/form-field', ['name' => 'program_id', 'label' => 'Belongs to a program', 'type' => 'select', 'empty' => 'None', 'value' => old('program_id', (string) ($entry['program_id'] ?? '')),
                            'options' => array_column($programs, 'title', 'id')]) ?>
                        <?= partial('partials/form-field', ['name' => 'project_id', 'label' => 'Belongs to a project', 'type' => 'select', 'empty' => 'None', 'value' => old('project_id', (string) ($entry['project_id'] ?? '')),
                            'options' => array_column($projects, 'title', 'id')]) ?>
                        <?php
                        $eventOptions = [];
                        foreach ($events as $ev) {
                            $eventOptions[$ev['id']] = $ev['title'] . ' (' . substr((string) $ev['start_at'], 0, 10) . ')';
                        }
                        ?>
                        <?= partial('partials/form-field', ['name' => 'event_id', 'label' => 'Linked event', 'type' => 'select', 'empty' => 'None', 'value' => old('event_id', (string) ($entry['event_id'] ?? '')),
                            'options' => $eventOptions, 'help' => 'Shows a link to the event page next to this date.']) ?>
                        <?= partial('partials/form-field', ['name' => 'source_label', 'label' => 'Where the date comes from', 'maxlength' => 190, 'value' => old('source_label', (string) ($entry['source_label'] ?? '')),
                            'placeholder' => 'For example: United Nations', 'help' => 'Shown to visitors as the source.']) ?>
                        <?= partial('partials/form-field', ['name' => 'source_url', 'label' => 'Source link', 'type' => 'url', 'maxlength' => 500, 'value' => old('source_url', (string) ($entry['source_url'] ?? '')), 'placeholder' => 'https://']) ?>
                    </div>
                </div>
            </details>
        </div>

        <aside class="cal-editor__aside" aria-label="Visibility and preview">
            <section class="card" aria-labelledby="sec-vis">
                <div class="card__header"><h2 class="card__title" id="sec-vis">Who sees it</h2></div>
                <div class="card__body">
                    <input type="hidden" name="is_active" value="0">
                    <label class="check"><input type="checkbox" name="is_active" value="1"<?= (string) old('is_active', (string) (int) $entry['is_active']) !== '0' ? ' checked' : '' ?>><span>Show on the calendar <small>(untick to hide it without deleting)</small></span></label>
                    <input type="hidden" name="is_public" value="0">
                    <label class="check"><input type="checkbox" name="is_public" value="1"<?= (string) old('is_public', (string) (int) $entry['is_public']) !== '0' ? ' checked' : '' ?>><span>Visible to website visitors <small>(untick to keep it for your team only)</small></span></label>
                    <input type="hidden" name="needs_confirmation" value="0">
                    <label class="check" data-cal-tbc<?= $rec === 'islamic' ? ' hidden' : '' ?>><input type="checkbox" name="needs_confirmation" value="1"<?= (string) old('needs_confirmation', (string) (int) $entry['needs_confirmation']) === '1' ? ' checked' : '' ?>><span>Show "Date to be confirmed" <small>(until you confirm each date)</small></span></label>
                    <p class="cal-note-inline" data-cal-tbc-note<?= $rec === 'islamic' ? '' : ' hidden' ?>><?= icon('info', 'icon--sm') ?> Islamic holiday dates always show "Date to be confirmed" until you confirm them.</p>
                    <div class="field<?= has_error('color') ? ' field--error' : '' ?>">
                        <label class="field__label" for="f-color">Own colour (optional)</label>
                        <div class="cal-colour">
                            <input class="cal-colour__pick" type="color" value="<?= e($color !== '' ? $color : (string) ($type['pair']['solid'] ?? '#5a6470')) ?>" data-cal-color-pick aria-label="Pick a colour">
                            <input class="input" id="f-color" type="text" name="color" value="<?= e($color) ?>" maxlength="7" placeholder="Same as the type" data-cal-color autocomplete="off">
                            <button class="btn btn--ghost btn--sm" type="button" data-cal-color-clear>Use the type's colour</button>
                        </div>
                        <div class="field__help">Leave empty to use the colour of the type. The calendar picks readable text colours by itself.</div>
                        <?php if (error('color')): ?><div class="field__error" role="alert"><?= e((string) error('color')) ?></div><?php endif; ?>
                    </div>
                </div>
            </section>

            <section class="card cal-preview" aria-labelledby="cal-preview-title">
                <div class="card__header"><h2 class="card__title" id="cal-preview-title">Next dates</h2></div>
                <div class="card__body" data-cal-preview aria-live="polite">
                    <p class="cal-preview__rule" data-cal-preview-rule><?= e($preview['ok'] ? $preview['description'] : '') ?></p>
                    <ol class="cal-preview__list" data-cal-preview-list>
                        <?php foreach ($preview['dates'] as $d): ?><li><?= e($d['label']) ?></li><?php endforeach; ?>
                    </ol>
                    <p class="cal-preview__msg" data-cal-preview-msg<?= $preview['ok'] && $preview['dates'] !== [] ? ' hidden' : '' ?>>
                        <?= !$preview['ok'] ? e((string) (array_values($preview['errors'])[0] ?? 'Choose a date to see the next dates.')) : 'No dates left: the repeat has ended.' ?></p>
                    <p class="cal-preview__help">Worked out by the same calendar engine visitors see.</p>
                </div>
            </section>
        </aside>

        <div class="form-actions cal-editor__actions">
            <button class="btn btn--primary" type="submit"><?= icon('check', 'icon--sm') ?> <?= $isNew ? 'Add to calendar' : 'Save changes' ?></button>
            <a class="btn btn--ghost" href="<?= e(url('admin/calendar')) ?>">Cancel</a>
        </div>
    </form>

    <?php if (!$isNew && $entry['recurrence'] !== 'none'): ?>
        <section class="card cal-dates" id="dates" aria-labelledby="dates-title" tabindex="-1">
            <div class="card__header"><h2 class="card__title" id="dates-title">Change a single date</h2></div>
            <div class="card__body">
                <p>Move, cancel or confirm <strong>one</strong> of the coming dates. The rest of the repeat is not touched. Visitors see the change straight away.</p>
                <?php if ($occurrences === []): ?>
                    <p class="text-muted">There are no coming dates: the repeat has ended.</p>
                <?php else: ?>
                    <ul class="cal-occs">
                        <?php foreach ($occurrences as $i => $o):
                            $key = 'occ' . $i;
                            $canConfirm = (int) $entry['needs_confirmation'] === 1 && empty($o['confirmed']);
                            $default = $canConfirm ? 'confirm' : (!empty($o['cancelled']) ? 'restore' : 'move');
                            ?>
                            <li class="cal-occ<?= !empty($o['cancelled']) ? ' is-cancelled' : '' ?>">
                                <div class="cal-occ__when">
                                    <strong><?= e(CivilDate::fmt($o['start_date'], 'l j F Y')) ?><?= $o['end_date'] !== $o['start_date'] ? ' to ' . e(CivilDate::fmt($o['end_date'], 'j F Y')) : '' ?></strong>
                                    <span class="cal-occ__time"><?= CalendarService::timeLabel($o) !== '' ? e(CalendarService::timeLabel($o)) : 'All day' ?></span>
                                    <span class="cal-occ__badges"><?= partial('admin/calendar/_badges', ['o' => $o]) ?></span>
                                    <?php if (!empty($o['moved'])): ?><span class="cal-occ__sub">Usual date: <?= e(CivilDate::fmt($o['occurrence_date'], 'D j M Y')) ?></span><?php endif; ?>
                                    <?php if (!empty($o['note'])): ?><span class="cal-occ__sub">Note: <?= e($o['note']) ?></span><?php endif; ?>
                                </div>
                                <details class="cal-occ__more">
                                    <summary>Change this date</summary>
                                    <form method="post" action="<?= e(url('admin/calendar/' . $id . '/occurrence')) ?>" class="cal-occ__form" data-cal-occ-form>
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="occurrence_date" value="<?= e($o['occurrence_date']) ?>">
                                        <fieldset class="cal-choices">
                                            <legend class="visually-hidden">What do you want to do with this date?</legend>
                                            <label class="check"><input type="radio" name="action" value="move" data-cal-action<?= $default === 'move' ? ' checked' : '' ?>><span>Move it to another day or time</span></label>
                                            <?php if (!empty($o['cancelled'])): ?>
                                                <label class="check"><input type="radio" name="action" value="restore" data-cal-action<?= $default === 'restore' ? ' checked' : '' ?>><span>Put it back on the calendar</span></label>
                                            <?php else: ?>
                                                <label class="check"><input type="radio" name="action" value="cancel" data-cal-action><span>Cancel this date (it will not happen)</span></label>
                                            <?php endif; ?>
                                            <?php if ($canConfirm): ?><label class="check"><input type="radio" name="action" value="confirm" data-cal-action<?= $default === 'confirm' ? ' checked' : '' ?>><span>Confirm the date (remove "Date to be confirmed")</span></label><?php endif; ?>
                                            <?php if (!empty($o['override_id'])): ?><label class="check"><input type="radio" name="action" value="reset" data-cal-action><span>Go back to the usual date</span></label><?php endif; ?>
                                        </fieldset>
                                        <div class="cal-occ__fields">
                                            <div class="field" data-cal-for="move confirm"><label class="field__label" for="<?= $key ?>-date">New date <small>(confirm: leave as it is if the prediction is right)</small></label>
                                                <input class="input" id="<?= $key ?>-date" type="date" name="new_date" value="<?= e($o['start_date']) ?>"></div>
                                            <div class="field" data-cal-for="move"><label class="field__label" for="<?= $key ?>-st">New start time (optional)</label>
                                                <input class="input" id="<?= $key ?>-st" type="time" name="new_start_time" value="<?= e((string) ($o['start_time'] ?? '')) ?>"></div>
                                            <div class="field" data-cal-for="move"><label class="field__label" for="<?= $key ?>-et">New end time (optional)</label>
                                                <input class="input" id="<?= $key ?>-et" type="time" name="new_end_time" value="<?= e((string) ($o['end_time'] ?? '')) ?>"></div>
                                            <div class="field" data-cal-for="move cancel confirm restore"><label class="field__label" for="<?= $key ?>-note">Note shown with the date (optional)</label>
                                                <input class="input" id="<?= $key ?>-note" type="text" name="note" maxlength="300" value="<?= e((string) ($o['note'] ?? '')) ?>" placeholder="For example: venue changed"></div>
                                        </div>
                                        <button class="btn btn--primary btn--sm" type="submit">Save this change</button>
                                    </form>
                                </details>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <?php if ($overrides !== []): ?>
                    <h3 class="cal-subhead">Changes made so far</h3>
                    <div class="table-wrap">
                        <table class="table table--stack">
                            <thead><tr><th scope="col">Usual date</th><th scope="col">What changed</th><th scope="col">Note</th><th scope="col" class="col-actions"><span class="visually-hidden">Remove</span></th></tr></thead>
                            <tbody>
                            <?php foreach ($overrides as $ov):
                                $what = [];
                                if ((int) $ov['cancelled'] === 1) {
                                    $what[] = 'Cancelled';
                                }
                                if (!empty($ov['new_date'])) {
                                    $what[] = 'Moved to ' . CivilDate::fmt((string) $ov['new_date'], 'D j M Y');
                                }
                                if (!empty($ov['new_start_time'])) {
                                    $what[] = 'Time ' . substr((string) $ov['new_start_time'], 0, 5) . (!empty($ov['new_end_time']) ? ' to ' . substr((string) $ov['new_end_time'], 0, 5) : '');
                                }
                                if ((int) $ov['confirmed'] === 1) {
                                    $what[] = 'Confirmed';
                                }
                                ?>
                                <tr>
                                    <td data-label="Usual date" class="nowrap"><?= e(CivilDate::fmt((string) $ov['occurrence_date'], 'D j M Y')) ?></td>
                                    <td data-label="What changed"><?= e($what === [] ? 'Note only' : implode(', ', $what)) ?></td>
                                    <td data-label="Note"><?= e((string) ($ov['note'] ?? '')) ?></td>
                                    <td class="col-actions" data-label="">
                                        <form method="post" action="<?= e(url('admin/calendar/' . $id . '/occurrence/' . (int) $ov['id'] . '/delete')) ?>" class="inline-form" data-confirm="Remove this change? That date will follow the usual repeat again.">
                                            <?= csrf_field() ?><button class="btn btn--ghost btn--sm" type="submit"><?= icon('history', 'icon--sm') ?> Undo</button></form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    <?php endif; ?>

    <?php if (!$isNew): ?>
        <section class="card cal-more-actions" aria-labelledby="more-title">
            <div class="card__header"><h2 class="card__title" id="more-title">More</h2></div>
            <div class="card__body cal-more-actions__row">
                <form method="post" action="<?= e(url('admin/calendar/' . $id . '/duplicate')) ?>"><?= csrf_field() ?>
                    <button class="btn btn--outline" type="submit"><?= icon('copy', 'icon--sm') ?> Make a copy</button></form>
                <form method="post" action="<?= e(url('admin/calendar/' . $id . '/toggle')) ?>"><?= csrf_field() ?>
                    <button class="btn btn--outline" type="submit"><?= (int) $entry['is_active'] === 1 ? icon('eye-off', 'icon--sm') . ' Hide from the calendar' : icon('eye', 'icon--sm') . ' Show on the calendar' ?></button></form>
                <?php if ($canDelete): ?>
                    <form method="post" action="<?= e(url('admin/calendar/' . $id . '/delete')) ?>" data-confirm="Delete &quot;<?= e((string) $entry['title']) ?>&quot; for good? This cannot be undone. (To keep it but stop showing it, use Hide instead.)"><?= csrf_field() ?>
                        <button class="btn btn--danger" type="submit"><?= icon('trash', 'icon--sm') ?> Delete</button></form>
                <?php else: ?>
                    <p class="text-muted cal-more-actions__note"><?= icon('lock', 'icon--sm') ?> Holidays and days that came with the site cannot be deleted. Hide it instead.</p>
                <?php endif; ?>
            </div>
        </section>
    <?php endif; ?>
</div>
