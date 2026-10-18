<?php
/**
 * Calendar entry detail (/calendar/{slug}[?date=YYYY-MM-DD]). Vars: $entry (row), $occ (the occurrence shown, decorated), $next (next occurrences),
 * $type, $jsonLd (schema.org Event data: programme sessions only, never holidays or international days).
 * Layout as the Event page: the facts card is first in the DOM (phones read the date first) and sits in the right-hand column from lg.
 * All copy: content('calendar', ...). The "Add to calendar" link is a plain download; "Share" and "Copy link" appear when the script runs.
 */
use App\Models\Event;
use App\Models\Program;
use App\Models\Project;
use App\Services\Calendar\CalendarService;
use App\Services\Calendar\CivilDate;

$this->start('scripts'); ?>
<script src="<?= e(asset('js/calendar.js')) ?>" defer></script>
<?php $this->end();
if (!empty($jsonLd)) {
    $this->start('head');
    foreach ($jsonLd as $ld) {
        echo '<script type="application/ld+json">' . json_encode($ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) . "</script>\n";
    }
    $this->end();
}

$p = $occ['pair'];
$time = CalendarService::timeLabel($occ);
$multi = $occ['end_date'] !== $occ['start_date'];
$prog = $entry['program_id'] ? Program::find((int) $entry['program_id']) : null;
$proj = $entry['project_id'] ? Project::find((int) $entry['project_id']) : null;
$event = $entry['event_id'] ? Event::find((int) $entry['event_id']) : null;
$prog = ($prog && (int) $prog['is_active'] === 1) ? $prog : null;
$proj = ($proj && (int) $proj['is_active'] === 1) ? $proj : null;
$event = ($event && (int) $event['is_active'] === 1) ? $event : null;
$shareOn = section_on('calendar', 'share');
$pageUrl = origin() . $occ['url'];
$hasDesc = trim((string) $occ['description']) !== '';
$disclaimer = trim((string) content('calendar', 'notes.disclaimer'));
// the editor only accepts http(s) addresses; a stored value is re-checked here before it becomes a link
$linkUrl = preg_match('~^https?://~i', (string) $occ['link_url']) ? (string) $occ['link_url'] : '';
$sourceUrl = preg_match('~^https?://~i', (string) $occ['source_url']) ? (string) $occ['source_url'] : '';
$config = ['kind' => 'detail', 'title' => (string) $occ['title'], 'url' => $pageUrl];
?>
<?= partial('partials/page-hero', ['title' => $occ['title'], 'eyebrow' => $occ['type_name'], 'lead' => CalendarService::dateLabel($occ)]) ?>

<section class="section cal-detail" id="calendar-entry" data-cal-detail data-cal-config="<?= e(trim(partial('partials/calendar-config', ['extra' => $config]))) ?>">
    <div class="container">
        <p class="cal-detail__back"><a class="btn btn--outline btn--caps btn--sm" href="<?= e(url('calendar', ['month' => substr($occ['start_date'], 0, 7)])) ?>"><?= icon('arrow-left', 'icon--sm') ?> <?= e(content('calendar', 'day.back_label')) ?></a></p>

        <div class="cal-detail__layout">
            <aside class="cal-detail__aside">
                <div class="cal-facts">
                    <p class="cal-facts__badges">
                        <span class="cal-pill cal-pill--<?= $occ['is_holiday'] ? 'holiday' : (in_array($occ['type_slug'], ['programme', 'event'], true) ? 'own' : 'obs') ?>" style="--cal-solid:<?= e($p['solid']) ?>;--cal-tint:<?= e($p['tint']) ?>;--cal-text:<?= e($p['text']) ?>;--cal-on:<?= e($p['on_solid']) ?>"><?= icon($occ['type_icon'], 'icon--sm cal-pill__icon') ?><span class="cal-pill__text"><?= e($occ['type_name']) ?></span></span>
                        <?php if ($occ['is_holiday']): ?><span class="cal-facts__badge"><?= e(content('calendar', 'filters.holiday_badge')) ?></span><?php endif; ?>
                        <?php if ($occ['needs_confirmation']): ?><span class="cal-facts__badge cal-facts__badge--tbc"><?= e(content('calendar', 'filters.tbc_badge')) ?></span><?php endif; ?>
                        <?php if ($occ['moved']): ?><span class="cal-facts__badge"><?= e(content('calendar', 'filters.moved_badge')) ?></span><?php endif; ?>
                    </p>
                    <?php if ($occ['cancelled']): ?><p class="cal-facts__cancelled"><?= icon('alert-triangle', 'icon--sm') ?> <?= e(content('calendar', 'day.cancelled_note')) ?></p><?php endif; ?>
                    <dl class="cal-facts__list">
                        <div class="cal-facts__item">
                            <span class="cal-facts__icon"><?= icon('calendar') ?></span>
                            <div>
                                <dt><?= e(content('calendar', 'day.when_label')) ?></dt>
                                <dd>
                                    <time datetime="<?= e($occ['start_date']) ?>"><?= e(CivilDate::fmt($occ['start_date'], 'l j F Y')) ?></time>
                                    <?php if ($multi): ?>
                                        <span class="cal-facts__sub"><?= e(CalendarService::dateLabel($occ)) ?></span>
                                    <?php endif; ?>
                                    <span class="cal-facts__sub"><?= e($time !== '' ? $time : content('calendar', 'views.all_day')) ?><?= $time !== '' ? ' &middot; ' . e(content('calendar', 'views.time_note')) : '' ?></span>
                                </dd>
                            </div>
                        </div>
                        <?php if ($occ['location'] !== ''): ?>
                            <div class="cal-facts__item">
                                <span class="cal-facts__icon"><?= icon('map-pin') ?></span>
                                <div><dt><?= e(content('calendar', 'day.where_label')) ?></dt><dd><?= e($occ['location']) ?></dd></div>
                            </div>
                        <?php endif; ?>
                        <?php if ($occ['source_label']): ?>
                            <div class="cal-facts__item">
                                <span class="cal-facts__icon"><?= icon('info') ?></span>
                                <div>
                                    <dt><?= e(content('calendar', 'day.source_label')) ?></dt>
                                    <dd><?php if ($sourceUrl !== ''): ?><a href="<?= e($sourceUrl) ?>" target="_blank" rel="noopener noreferrer"><?= e($occ['source_label']) ?> <?= icon('external-link', 'icon--sm') ?></a><?php else: ?><?= e($occ['source_label']) ?><?php endif; ?></dd>
                                </div>
                            </div>
                        <?php endif; ?>
                    </dl>
                    <?php if ($shareOn || $linkUrl !== ''): ?>
                        <div class="cal-facts__actions">
                            <?php if ($linkUrl !== ''): ?><a class="btn btn--gold btn--caps btn--block" href="<?= e($linkUrl) ?>" target="_blank" rel="noopener noreferrer"><?= e($occ['link_label'] ?: content('calendar', 'day.link_default')) ?> <?= icon('external-link', 'icon--sm') ?></a><?php endif; ?>
                            <?php if ($shareOn && !$occ['cancelled']): ?><a class="btn btn--outline-light btn--caps btn--block" href="<?= e($occ['ics_url']) ?>"><?= icon('calendar', 'icon--sm') ?> <?= e(content('calendar', 'share.add_label')) ?></a><?php endif; ?>
                            <?php if ($shareOn): ?>
                                <button type="button" class="btn btn--outline-light btn--caps btn--block" hidden data-cal-share="<?= e($pageUrl) ?>" data-title="<?= e($occ['title']) ?>"><?= icon('send', 'icon--sm') ?> <?= e(content('calendar', 'share.share_label')) ?></button>
                                <button type="button" class="btn btn--outline-light btn--caps btn--block" hidden data-cal-copy="<?= e($pageUrl) ?>"><?= icon('copy', 'icon--sm') ?> <?= e(content('calendar', 'share.copy_label')) ?></button>
                                <p class="cal-facts__copied" role="status" aria-live="polite" data-cal-copied></p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </aside>

            <div class="cal-detail__main">
                <?php if ($occ['note']): ?><p class="cal-detail__note"><?= icon('info', 'icon--sm') ?> <span><?= e($occ['note']) ?></span></p><?php endif; ?>
                <?php if ($hasDesc): ?>
                    <h2 class="cal-detail__heading"><?= e(content('calendar', 'day.about_label')) ?></h2>
                    <div class="prose cal-detail__body"><?= rich($occ['description']) ?></div>
                <?php endif; ?>

                <?php if ($prog || $proj || $event): ?>
                    <p class="cal-detail__related">
                        <?php if ($prog): ?><a class="btn btn--outline" href="<?= e(url('programs/' . $prog['slug'])) ?>"><?= e(content('calendar', 'day.program_label')) ?>: <?= e($prog['title']) ?></a><?php endif; ?>
                        <?php if ($proj): ?><a class="btn btn--outline" href="<?= e(url('projects/' . $proj['slug'])) ?>"><?= e(content('calendar', 'day.project_label')) ?>: <?= e($proj['title']) ?></a><?php endif; ?>
                        <?php if ($event): ?><a class="btn btn--outline" href="<?= e(url('events/' . $event['slug'])) ?>"><?= e(content('calendar', 'day.event_label')) ?>: <?= e($event['title']) ?></a><?php endif; ?>
                    </p>
                <?php endif; ?>

                <?php if (count($next) > 1 || ($next && $next[0]['key'] !== $occ['key'])): ?>
                    <h2 class="cal-detail__heading"><?= e(content('calendar', 'day.next_dates')) ?></h2>
                    <ul class="cal-detail__next">
                        <?php foreach ($next as $n): ?>
                            <li<?= $n['key'] === $occ['key'] ? ' aria-current="date"' : '' ?>>
                                <a href="<?= e($n['url']) ?>"><?= e(CalendarService::dateLabel($n)) ?></a>
                                <?php if ($n['needs_confirmation']): ?><span class="cal-detail__tbc"><?= e(content('calendar', 'filters.tbc_badge')) ?></span><?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <?php if ($disclaimer !== '' && ($occ['is_holiday'] || $occ['needs_confirmation'])): ?>
                    <p class="cal-notes__disclaimer cal-detail__disclaimer"><?= icon('info', 'icon--sm') ?><span><?= e($disclaimer) ?></span></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
