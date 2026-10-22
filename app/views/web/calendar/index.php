<?php
/**
 * Calendar page. Vars: $grid (CalendarService::monthGrid), $view ('month'|'list'|'upcoming'), $upcoming, $agenda, $jsonLd (schema.org Events),
 * $types (public types), $sel (type selection, see CalendarController::selection), $month, $todayMonth. All copy: content('calendar', ...)
 * (admin: Pages > Calendar), nothing is hard-coded here.
 * Works without JavaScript (month = ?month=YYYY-MM, filters = a GET form, "+N more" = the list view anchored on the day);
 * assets/js/calendar.js adds in-place month changes from /calendar/feed, the keyboard grid, the day panel and the entry dialog.
 */
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

$tplc = static fn(string $s, array $v): string => strtr($s, array_combine(array_map(static fn($k) => '{' . $k . '}', array_keys($v)), array_map('strval', array_values($v))));
$heroImg = content_img('calendar', 'hero.image');
$heroTitle = strip_highlight((string) content('calendar', 'hero.title'));
$checked = $sel['checked'];
$allChecked = count($checked) === count($sel['all']);
// one place that builds every link of the page; "current month" and "month view" are left out of the URL, so the default page stays /calendar
$link = static function (array $o = []) use ($grid, $view, $sel, $todayMonth): string {
    $m = array_key_exists('month', $o) ? $o['month'] : $grid['month'];
    $v = array_key_exists('view', $o) ? $o['view'] : $view;
    $t = array_key_exists('types', $o) ? $o['types'] : $sel['param'];
    return url('calendar', ['month' => ($m !== null && $m !== $todayMonth) ? $m : null, 'view' => ($v !== null && $v !== 'month') ? $v : null, 'types' => $t]);
};
// previous / next months far from today are not worth following for a crawler (endless chain of near-empty pages)
$monthsFromToday = static fn(string $m): int => abs(((int) substr($m, 0, 4) * 12 + (int) substr($m, 5, 2)) - ((int) substr($todayMonth, 0, 4) * 12 + (int) substr($todayMonth, 5, 2)));
$relFor = static fn(string $m): string => $monthsFromToday($m) > 24 ? ' rel="nofollow"' : '';
$moreUrl = static fn(string $d): string => $link(['month' => substr($d, 0, 7), 'view' => 'list']) . '#d-' . $d;
$monthDays = $view === 'month' ? CalendarService::groupByDay($grid['items'], CivilDate::monthStart($grid['month']), CivilDate::monthEnd($grid['month'])) : [];
$emptyTitle = $tplc((string) content('calendar', 'empty.month_title'), ['month' => $grid['label']]);
$config = [
    'kind' => 'page', 'feed' => url('calendar/feed'), 'base' => url('calendar'), 'today' => $grid['today'], 'weekStart' => $grid['week_start'],
    'view' => $view, 'month' => $grid['month'], 'checked' => $checked, 'allTypes' => $sel['all'], 'defaults' => $sel['defaults'],
];
?>
<?= partial('partials/page-hero', [
    'title'   => content('calendar', 'hero.title'),
    'eyebrow' => content('calendar', 'hero.eyebrow'),
    'lead'    => content('calendar', 'hero.lead'),
    'image'   => $heroImg['has'] ? ['src' => $heroImg['src'], 'alt' => $heroImg['alt'], 'focus' => '50% 40%'] : null,
]) ?>

<section class="section cal-page" id="calendar" data-cal-page data-cal-config="<?= e(trim(partial('partials/calendar-config', ['extra' => $config]))) ?>" aria-label="<?= e($heroTitle !== '' ? $heroTitle : 'Calendar') ?>">
    <div class="container">

        <?php /* ---- view tabs + print */ ?>
        <div class="cal-toolbar">
            <nav class="cal-tabs" aria-label="<?= e(content('calendar', 'aria.nav_label')) ?>">
                <?php foreach (['month' => ['views.month_tab', 'calendar'], 'list' => ['views.agenda_tab', 'list'], 'upcoming' => ['views.upcoming_tab', 'clock'], 'podcast' => ['views.podcast_tab', 'mic']] as $v => [$key, $ico]): ?>
                    <a class="cal-tabs__item<?= $view === $v ? ' is-active' : '' ?>" href="<?= e($link(['view' => $v])) ?>"<?= $view === $v ? ' aria-current="page"' : '' ?> data-cal-tab="<?= e($v) ?>"><?= icon($ico, 'icon--sm') ?><span><?= e(content('calendar', $key)) ?></span></a>
                <?php endforeach; ?>
            </nav>
            <?php if (!in_array($view, ['upcoming', 'podcast'], true)): ?><button type="button" class="btn btn--ghost btn--sm" hidden data-cal-print><?= icon('file-text', 'icon--sm') ?> <?= e(content('calendar', 'views.print_label')) ?></button><?php endif; ?>
        </div>

        <div class="cal-shell">
            <?php if (!in_array($view, ['upcoming', 'podcast'], true)): ?>
                <?php /* ---- month controls (previous / next / today + jump to a month) */ ?>
                <div class="cal-bar">
                    <div class="cal-bar__nav">
                        <a class="cal-bar__btn" href="<?= e($link(['month' => $grid['prev']])) ?>" data-cal-prev<?= $relFor($grid['prev']) ?>><span class="visually-hidden"><?= e(content('calendar', 'views.prev_label')) ?></span><?= icon('chevron-left') ?></a>
                        <a class="cal-bar__btn" href="<?= e($link(['month' => $grid['next']])) ?>" data-cal-next<?= $relFor($grid['next']) ?>><span class="visually-hidden"><?= e(content('calendar', 'views.next_label')) ?></span><?= icon('chevron-right') ?></a>
                    </div>
                    <h2 class="cal-bar__title" data-cal-title id="cal-title" tabindex="-1"><?= e($grid['label']) ?></h2>
                    <a class="btn btn--outline btn--sm cal-bar__today" href="<?= e($link(['month' => $todayMonth])) ?>" data-cal-today><?= e(content('calendar', 'views.today_label')) ?></a>
                    <form class="cal-jump" method="get" action="<?= e(url('calendar')) ?>" data-cal-jump aria-label="<?= e(content('calendar', 'views.jump_label')) ?>">
                        <?php if ($view !== 'month'): ?><input type="hidden" name="view" value="<?= e($view) ?>"><?php endif; ?>
                        <?php if ($sel['param'] !== null): ?><input type="hidden" name="types" value="<?= e($sel['param']) ?>"><?php endif; ?>
                        <label class="visually-hidden" for="cal-jump-month"><?= e(content('calendar', 'views.month_label')) ?></label>
                        <select class="select" id="cal-jump-month" name="m" data-cal-jump-month>
                            <?php foreach (CivilDate::MONTHS as $n => $name): ?><option value="<?= $n ?>"<?= $n === $grid['month_number'] ? ' selected' : '' ?>><?= e($name) ?></option><?php endforeach; ?>
                        </select>
                        <label class="visually-hidden" for="cal-jump-year"><?= e(content('calendar', 'views.year_label')) ?></label>
                        <input class="input" id="cal-jump-year" type="number" min="1950" max="2100" step="1" inputmode="numeric" name="y" value="<?= (int) $grid['year'] ?>" data-cal-jump-year>
                        <button class="btn btn--primary btn--sm" type="submit"><?= e(content('calendar', 'views.go_label')) ?></button>
                    </form>
                </div>
            <?php endif; ?>

            <?php /* ---- filters = the colour key (a GET form, so it works without JavaScript) */ ?>
            <?php if ($view !== 'podcast'): ?>
            <form class="cal-filters" method="get" action="<?= e(url('calendar')) ?>" data-cal-filters>
                <?php if ($view !== 'upcoming' && $grid['month'] !== $todayMonth): ?><input type="hidden" name="month" value="<?= e($grid['month']) ?>"><?php endif; ?>
                <?php if ($view !== 'month'): ?><input type="hidden" name="view" value="<?= e($view) ?>"><?php endif; ?>
                <?= partial('partials/calendar-legend', ['types' => $types, 'selected' => $checked, 'form' => true, 'title' => (string) content('calendar', 'filters.title'), 'label' => (string) content('calendar', 'filters.group_label')]) ?>
                <p class="cal-filters__foot">
                    <span class="cal-filters__hint"><?= e(content('calendar', 'aria.filter_hint')) ?></span>
                    <a class="cal-filters__all" href="<?= e($link(['types' => 'all'])) ?>" data-cal-all<?= $allChecked ? ' hidden' : '' ?>><?= e(content('calendar', 'filters.all_label')) ?></a>
                    <button class="btn btn--primary btn--sm cal-filters__apply" type="submit"><?= e(content('calendar', 'views.go_label')) ?></button>
                </p>
            </form>
            <?php endif; ?>

            <p class="visually-hidden" role="status" aria-live="polite" data-cal-status></p>

            <?php if ($view === 'month'): ?>
                <div class="cal-month" data-cal-month>
                    <p class="cal-empty cal-empty--grid" data-cal-empty<?= $grid['total'] > 0 ? ' hidden' : '' ?>>
                        <strong><?= e($emptyTitle) ?></strong>
                        <span><?= e($checked === [] || !$allChecked ? content('calendar', 'empty.filtered_text') : content('calendar', 'empty.month_text')) ?></span>
                    </p>
                    <div class="cal-month__grid"><?= partial('partials/calendar-grid', ['grid' => $grid, 'moreUrl' => $moreUrl]) ?></div>
                    <div class="cal-month__agenda" data-cal-agenda><?= partial('partials/calendar-agenda', ['days' => $monthDays, 'emptyText' => $emptyTitle]) ?></div>
                </div>
                <p class="cal-hint"><?= e(content('calendar', 'views.time_note')) ?> <span class="cal-hint__keys"><?= e(content('calendar', 'aria.keyboard_hint')) ?></span></p>
            <?php elseif ($view === 'podcast'): ?>
                <div class="cal-upcoming-view cal-podcast">
                    <h2 class="cal-upcoming-view__title"><?= highlight((string) content('calendar', 'podcast.title')) ?></h2>
                    <?php if (trim((string) content('calendar', 'podcast.intro')) !== ''): ?><p class="cal-podcast__intro"><?= e(content('calendar', 'podcast.intro')) ?></p><?php endif; ?>
                    <?php if ($upcoming): ?>
                        <ul class="cal-upcoming cal-upcoming--page cal-podcast__list">
                            <?php foreach ($upcoming as $o): $watch = preg_match('~^https?://~i', (string) ($o['link_url'] ?? '')) ? (string) $o['link_url'] : ''; ?>
                                <li class="cal-upcoming__item cal-podcast__item">
                                    <?= partial('partials/calendar-item', ['o' => $o, 'lead' => 'date', 'summary' => true]) ?>
                                    <?php if ($watch !== ''): ?><a class="btn btn--gold btn--caps btn--sm cal-podcast__watch" href="<?= e($watch) ?>" target="_blank" rel="noopener noreferrer"><?= icon('mic', 'icon--sm') ?> <?= e($o['link_label'] ?: 'Watch live') ?> <?= icon('external-link', 'icon--sm') ?><span class="visually-hidden">: <?= e($o['title']) ?></span></a><?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <?= partial('partials/empty-state', ['title' => (string) content('calendar', 'podcast.empty_title'), 'text' => (string) content('calendar', 'podcast.empty_text'), 'icon' => 'mic', 'tag' => 'h3']) ?>
                    <?php endif; ?>
                </div>
                <p class="cal-hint"><?= e(content('calendar', 'views.time_note')) ?></p>
            <?php elseif ($view === 'list'): ?>
                <div class="cal-listview" data-cal-agenda data-cal-list>
                    <?= partial('partials/calendar-agenda', ['days' => $agenda, 'emptyText' => $emptyTitle . ' ' . ($allChecked ? content('calendar', 'empty.month_text') : content('calendar', 'empty.filtered_text')), 'summary' => true]) ?>
                </div>
                <p class="cal-hint"><?= e(content('calendar', 'views.time_note')) ?></p>
            <?php else: ?>
                <div class="cal-upcoming-view">
                    <h2 class="cal-upcoming-view__title"><?= highlight((string) content('calendar', 'upcoming.title')) ?></h2>
                    <?php if ($upcoming): ?>
                        <?= partial('partials/calendar-upcoming', ['items' => $upcoming, 'summary' => true, 'class' => 'cal-upcoming--page']) ?>
                    <?php else: ?>
                        <?= partial('partials/empty-state', ['title' => (string) content('calendar', 'upcoming.empty_title'), 'text' => (string) content('calendar', 'upcoming.empty_text'), 'icon' => 'calendar', 'tag' => 'h3']) ?>
                    <?php endif; ?>
                </div>
                <p class="cal-hint"><?= e(content('calendar', 'views.time_note')) ?></p>
            <?php endif; ?>
        </div>

        <?php /* ---- notes: disclaimer + sources, subscribe */ ?>
        <div class="cal-notes">
            <?php if (trim((string) content('calendar', 'notes.disclaimer')) !== '' || trim((string) content('calendar', 'notes.sources_text')) !== ''): ?>
                <div class="cal-notes__card cal-notes__card--info">
                    <?php if (trim((string) content('calendar', 'notes.disclaimer')) !== ''): ?><p class="cal-notes__disclaimer"><?= icon('info', 'icon--sm') ?><span><?= e(content('calendar', 'notes.disclaimer')) ?></span></p><?php endif; ?>
                    <?php if (trim((string) content('calendar', 'notes.sources_text')) !== ''): ?>
                        <h2 class="cal-notes__title"><?= e(content('calendar', 'notes.sources_label')) ?></h2>
                        <p class="cal-notes__text"><?= e(content('calendar', 'notes.sources_text')) ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <?php if (section_on('calendar', 'share')): ?>
                <div class="cal-notes__card cal-subscribe">
                    <h2 class="cal-notes__title"><?= e(content('calendar', 'share.subscribe_title')) ?></h2>
                    <p class="cal-notes__text"><?= e(content('calendar', 'share.subscribe_text')) ?></p>
                    <p class="cal-subscribe__actions">
                        <a class="btn btn--outline btn--sm" href="<?= e(url('calendar.ics')) ?>"><?= icon('download', 'icon--sm') ?> <?= e(content('calendar', 'share.subscribe_label')) ?></a>
                        <button type="button" class="btn btn--ghost btn--sm" hidden data-cal-copy="<?= e(abs_url('calendar.ics')) ?>"><?= icon('copy', 'icon--sm') ?> <?= e(content('calendar', 'share.copy_label')) ?></button>
                    </p>
                    <p class="cal-subscribe__url"><code><?= e(abs_url('calendar.ics')) ?></code></p>
                    <p class="visually-hidden" role="status" aria-live="polite" data-cal-copied></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
