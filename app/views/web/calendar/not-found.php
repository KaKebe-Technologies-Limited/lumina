<?php
/**
 * A calendar date that does not exist (or is hidden / private): answered with status 404 and a way back (registry calendar > day.not_found_*, day.back_label).
 * The page never says whether the entry exists but is private.
 */
?>
<?= partial('partials/page-hero', ['title' => content('calendar', 'hero.title'), 'eyebrow' => content('calendar', 'hero.eyebrow')]) ?>

<section class="section cal-detail" id="calendar-entry">
    <div class="container container--narrow">
        <?= partial('partials/empty-state', [
            'title' => (string) content('calendar', 'day.not_found_title'), 'text' => (string) content('calendar', 'day.not_found_text'), 'icon' => 'calendar',
            'actions' => [['label' => (string) content('calendar', 'day.back_label'), 'url' => url('calendar'), 'class' => 'btn--primary']],
        ]) ?>
    </div>
</section>
