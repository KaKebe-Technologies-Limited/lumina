<?php /** Facilitator application sent. Copy: content('academy', 'facilitate.thanks_*'). */ ?>
<?= partial('partials/page-hero', ['title' => content('academy', 'facilitate.thanks_title'), 'eyebrow' => 'LAF Academy', 'lead' => content('academy', 'facilitate.thanks_text')]) ?>
<section class="section ac-auth">
    <div class="container container--narrow ac-center">
        <p><a class="btn btn--cta btn--caps" href="<?= e(url('academy')) ?>">Back to the LAF Academy</a></p>
    </div>
</section>
