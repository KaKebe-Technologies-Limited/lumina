<?php /** One last step after signing up with Google: follow and subscribe to our social media pages. */ ?>
<?= partial('partials/page-hero', ['title' => 'One last step', 'eyebrow' => 'LAF Academy', 'lead' => 'Follow and subscribe to our pages to stay close to the foundation and the Academy.']) ?>
<section class="section ac-auth">
    <div class="container container--narrow">
        <form class="ac-form card" method="post" action="<?= e(url('academy/follow')) ?>" novalidate>
            <?= csrf_field() ?>
            <div class="card__body">
                <?= partial('partials/academy-follow', ['required' => \App\Services\Academy\LearnerAuth::followRequired()]) ?>
                <button class="btn btn--cta btn--caps btn--block" type="submit">Continue</button>
            </div>
        </form>
    </div>
</section>
