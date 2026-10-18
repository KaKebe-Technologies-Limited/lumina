<?php
/**
 * Admin > Calendar > Types & colours. Vars: $available, $types (CalendarService::types(), each with 'pair'), $counts (type id => entries), $icons (icon => label),
 * $core (slugs that are part of the calendar), $nextOrder.
 * The same readability maths as the calendar itself (ColorContrast) runs live in admin-calendar.js; the numbers on first load are computed on the server.
 */
$this->start('scripts'); ?>
<script src="<?= e(asset('js/admin-calendar.js')) ?>" defer></script>
<?php $this->end(); ?>
<div class="admin-calendar cal-types" data-cal-page="types">
    <div class="page-header">
        <div>
            <h1 class="page-header__title">Types &amp; colours</h1>
            <p class="page-header__subtitle">Every date on the calendar has a type. A type is a name, a colour and a shape. The calendar makes the text on top of the colour readable by itself; the check on each card shows how readable it is.</p>
        </div>
        <div class="page-header__actions"><a class="btn btn--outline" href="<?= e(url('admin/calendar')) ?>"><?= icon('calendar', 'icon--sm') ?> Back to the calendar</a></div>
    </div>

    <?php if (!$available): ?>
        <div class="alert alert--warning" role="status"><?= icon('alert-triangle') ?><div class="alert__body">The calendar tables are missing from the database. Import <code>database/lumina-live-import.sql</code> first.</div></div>
    <?php else: ?>
        <div class="cal-hintbar"><?= icon('info', 'icon--sm') ?> <span>Colour is never the only clue: each type also has a shape and a name. Pick colours that are not too light, so the dots and borders show on a white page.</span></div>
        <div hidden data-cal-icon-bank aria-hidden="true"><?php foreach ($icons as $key => $label): ?><span data-icon="<?= e($key) ?>"><?= icon($key, 'icon--sm') ?></span><?php endforeach; ?></div>
        <?php $submitted = (string) old('type_form', ''); ?>
        <div class="cal-types__list">
            <?php foreach ($types as $t): ?>
                <?= partial('admin/calendar/_type-form', [
                    't' => $t, 'icons' => $icons, 'used' => (int) ($counts[$t['id']] ?? 0), 'core' => $core, 'nextOrder' => $nextOrder, 'failed' => $submitted === (string) $t['id'],
                ]) ?>
            <?php endforeach; ?>
        </div>
        <h2 class="cal-subhead">Add a type</h2>
        <?= partial('admin/calendar/_type-form', ['t' => null, 'icons' => $icons, 'used' => 0, 'core' => $core, 'nextOrder' => $nextOrder, 'failed' => $submitted === 'new']) ?>
    <?php endif; ?>
</div>
