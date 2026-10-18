<?php
/**
 * Invitee page: the link is not valid (mistyped, unknown, or the person was removed from the meeting). Status 404, noindex, no-store.
 * One answer for every reason, so the page never hints what exists. Copy: registry site_meetings > page.not_found_*, page.home_label.
 */

use App\Services\Meetings\MeetingRenderer as R;

$this->start('head'); ?>
<script src="<?= e(asset('js/meetings.js')) ?>" defer></script>
<?php $this->end(); ?>
<?= partial('partials/page-hero', ['title' => R::tf('page.not_found_title'), 'eyebrow' => R::tf('page.eyebrow'), 'lead' => R::tf('page.not_found_text'), 'sheet' => 'tint']) ?>

<section class="section section--tint meeting-page meeting-page--missing">
    <div class="container">
        <p class="meeting-page__home meeting-page__home--center"><a class="btn btn--primary btn--caps" href="<?= e(url('')) ?>"><?= e(R::tf('page.home_label')) ?></a></p>
    </div>
</section>
