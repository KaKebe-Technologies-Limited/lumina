<?php
/**
 * Compact calendar block of the Contact page (rendered by web/contact/index.php, below the form and map).
 * Var: $calendar* = CalendarService::embedData() (the caller prints nothing when it is null).
 * Content: registry page "contact", section "calendar" (admin: Pages > Contact; the section switch there and the setting calendar_show_on_contact
 * hide the block), plus the shared labels of the "calendar" page.
 * Left: a month of icon markers (no JavaScript: ?cal=YYYY-MM links; with it: assets/js/calendar.js changes the month in place from /calendar/feed and
 * opens the day panel). Right: the next dates, the key, the disclaimer and the link to the full calendar.
 */
use App\Services\Calendar\CalendarService;

if (empty($calendar)) {
    return;
}
$grid = $calendar['grid'];
$cta = content_btn('contact', 'calendar.cta');
$ctaUrl = !empty($cta['has']) ? $cta['url'] : '';
$disclaimer = trim((string) content('calendar', 'notes.disclaimer'));
$config = [
    'kind' => 'embed', 'feed' => url('calendar/feed'), 'base' => url('calendar'), 'today' => $grid['today'], 'weekStart' => $grid['week_start'],
    'view' => 'month', 'month' => $grid['month'], 'checked' => CalendarService::defaultTypeSlugs(),
];
$more = static fn(string $d): string => url('calendar', ['month' => substr($d, 0, 7), 'view' => 'list']) . '#d-' . $d;
?>
<section class="section contact-calendar" id="contact-calendar" aria-labelledby="contact-calendar-title">
    <div class="container">
        <?= partial('partials/section-head', ['eyebrow' => (string) content('contact', 'calendar.eyebrow'), 'title' => (string) content('contact', 'calendar.title'), 'lead' => (string) content('contact', 'calendar.intro'), 'id' => 'contact-calendar-title']) ?>
        <div class="cal-embed" data-cal-embed data-cal-config="<?= e(trim(partial('partials/calendar-config', ['extra' => $config]))) ?>">
            <div class="cal-embed__main">
                <div class="cal-bar cal-bar--embed">
                    <h3 class="cal-bar__title" data-cal-title id="cal-embed-title" tabindex="-1"><?= e($grid['label']) ?></h3>
                    <div class="cal-bar__nav">
                        <a class="cal-bar__btn" href="<?= e(url('contact', ['cal' => $grid['prev']]) . '#contact-calendar') ?>" data-cal-prev rel="nofollow"><span class="visually-hidden"><?= e(content('calendar', 'views.prev_label')) ?></span><?= icon('chevron-left') ?></a>
                        <a class="cal-bar__btn" href="<?= e(url('contact', ['cal' => $grid['next']]) . '#contact-calendar') ?>" data-cal-next rel="nofollow"><span class="visually-hidden"><?= e(content('calendar', 'views.next_label')) ?></span><?= icon('chevron-right') ?></a>
                    </div>
                </div>
                <p class="visually-hidden" role="status" aria-live="polite" data-cal-status></p>
                <?= partial('partials/calendar-grid', ['grid' => $grid, 'max' => 3, 'compact' => true, 'moreUrl' => $more]) ?>
                <p class="cal-hint"><?= e(content('calendar', 'views.time_note')) ?></p>
            </div>
            <div class="cal-embed__side">
                <h3 class="cal-embed__heading"><?= e(content('contact', 'calendar.upcoming_title')) ?></h3>
                <?= partial('partials/calendar-upcoming', ['items' => $calendar['upcoming'], 'emptyText' => (string) content('contact', 'calendar.empty_text')]) ?>
                <?= partial('partials/calendar-legend', ['types' => $calendar['types'], 'title' => (string) content('contact', 'calendar.legend_title')]) ?>
                <?php if ($disclaimer !== ''): ?><p class="cal-embed__note"><?= e($disclaimer) ?></p><?php endif; ?>
                <?php if ($ctaUrl !== ''): ?>
                    <p class="cal-embed__cta"><a class="btn btn--primary btn--caps" href="<?= e($ctaUrl) ?>"><?= e($cta['label']) ?> <?= icon('arrow-right', 'icon--sm') ?></a></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
