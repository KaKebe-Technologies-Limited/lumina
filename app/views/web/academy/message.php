<?php /** A short message screen. Vars: $heading, $text, $actions (list of ['label','url']). */ ?>
<?= partial('partials/page-hero', ['title' => $heading, 'eyebrow' => 'LAF Academy']) ?>
<section class="section ac-auth">
    <div class="container container--narrow">
        <div class="ac-notice card"><div class="card__body">
            <p><?= e($text) ?></p>
            <p class="ac-center"><?php foreach ((array) $actions as $a): ?><a class="btn btn--cta btn--caps" href="<?= e($a['url']) ?>"><?= e($a['label']) ?></a> <?php endforeach; ?></p>
        </div></div>
    </div>
</section>
