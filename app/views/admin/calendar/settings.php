<?php
/**
 * Admin > Calendar > Settings (administrators: ability settings.manage). Vars: $weekStart, $showOnContact, $selected (slugs ticked first), $types (visitor-visible types),
 * $available, $weekOptions. The same three settings are also in Admin > Settings > Calendar; this screen only makes the type list friendlier.
 */
?>
<div class="admin-calendar cal-settings" data-cal-page="settings">
    <div class="page-header">
        <div>
            <h1 class="page-header__title">Calendar settings</h1>
            <p class="page-header__subtitle">How the public calendar behaves. The words on the pages (headings, the note about public holidays, labels) are edited in Admin &gt; Pages.</p>
        </div>
        <div class="page-header__actions"><a class="btn btn--outline" href="<?= e(url('admin/calendar')) ?>"><?= icon('calendar', 'icon--sm') ?> Back to the calendar</a></div>
    </div>

    <?php if (!$available): ?>
        <div class="alert alert--warning" role="status"><?= icon('alert-triangle') ?><div class="alert__body">The calendar tables are missing from the database. Import <code>database/lumina-live-import.sql</code> first.</div></div>
    <?php else: ?>
        <div class="cal-settings__grid">
            <form class="card" method="post" action="<?= e(url('admin/calendar/settings')) ?>" data-unsaved-guard>
                <?= csrf_field() ?>
                <div class="card__body">
                    <div class="field<?= has_error('calendar_week_start') ? ' field--error' : '' ?>">
                        <label class="field__label" for="s-week">The week starts on</label>
                        <select class="select" id="s-week" name="calendar_week_start">
                            <?php foreach ($weekOptions as $k => $label): ?><option value="<?= e($k) ?>"<?= (string) old('calendar_week_start', $weekStart) === (string) $k ? ' selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?>
                        </select>
                        <div class="field__help">The first column of the month grid on the website.</div>
                        <?php if (error('calendar_week_start')): ?><div class="field__error" role="alert"><?= e((string) error('calendar_week_start')) ?></div><?php endif; ?>
                    </div>

                    <input type="hidden" name="calendar_show_on_contact" value="0">
                    <label class="check"><input type="checkbox" name="calendar_show_on_contact" value="1"<?= (string) old('calendar_show_on_contact', $showOnContact ? '1' : '0') !== '0' ? ' checked' : '' ?>>
                        <span>Show the small calendar on the Contact page <small>(under the contact form, with the next dates and a link to the full calendar)</small></span></label>

                    <fieldset class="cal-fieldset<?= has_error('calendar_types') ? ' field--error' : '' ?>">
                        <legend class="field__label">Types ticked when someone opens the calendar</legend>
                        <?php $picked = old('calendar_types', $selected === [] ? array_column($types, 'slug') : $selected); $picked = is_array($picked) ? $picked : []; ?>
                        <div class="cal-checks">
                            <?php foreach ($types as $t): $p = $t['pair']; ?>
                                <label class="check cal-typecheck" style="--cal-solid:<?= e($p['solid']) ?>;--cal-tint:<?= e($p['tint']) ?>;--cal-text:<?= e($p['text']) ?>">
                                    <input type="checkbox" name="calendar_types[]" value="<?= e($t['slug']) ?>"<?= in_array($t['slug'], $picked, true) ? ' checked' : '' ?>>
                                    <span class="cal-typechip"><?= icon((string) $t['icon'], 'icon--sm') ?><?= e($t['name']) ?></span></label>
                            <?php endforeach; ?>
                        </div>
                        <div class="field__help">Visitors can still tick the other types themselves. Keep them all ticked to show everything first.</div>
                        <?php if (error('calendar_types')): ?><div class="field__error" role="alert"><?= e((string) error('calendar_types')) ?></div><?php endif; ?>
                    </fieldset>
                </div>
                <div class="card__footer"><button class="btn btn--primary" type="submit"><?= icon('check', 'icon--sm') ?> Save settings</button></div>
            </form>

            <aside class="card" aria-labelledby="where-title">
                <div class="card__header"><h2 class="card__title" id="where-title">Where to change the words</h2></div>
                <div class="card__body">
                    <ul class="cal-links-list">
                        <li><a href="<?= e(url('admin/pages/calendar/edit')) ?>">Calendar page</a> <span class="text-muted">headings, filters and labels</span></li>
                        <li><a href="<?= e(url('admin/pages/calendar/edit')) ?>#sec-notes">Note about public holiday dates</a> <span class="text-muted">the disclaimer shown under the calendar ("moveable dates are confirmed by official announcement")</span></li>
                        <li><a href="<?= e(url('admin/pages/contact/edit')) ?>#sec-calendar">Contact page: calendar section</a> <span class="text-muted">its words, and the switch to hide it</span></li>
                        <li><a href="<?= e(url('admin/calendar/types')) ?>">Types &amp; colours</a> <span class="text-muted">names, colours and shapes</span></li>
                        <li><a href="<?= e(url('calendar')) ?>" target="_blank" rel="noopener">View the public calendar</a></li>
                    </ul>
                </div>
            </aside>
        </div>
    <?php endif; ?>
</div>
