<?php
/**
 * /newsletter while nothing is published yet: friendly empty state + the subscribe box. noindex (set by the controller).
 * All wording: content('newsletter', ...). OWNER: WEB agent.
 */
?>
<?= partial('partials/page-hero', [
    'title'   => content('newsletter', 'hero.title'),
    'eyebrow' => content('newsletter', 'hero.eyebrow'),
    'lead'    => content('newsletter', 'hero.lead'),
]) ?>

<section class="section nl-empty">
    <div class="container container--narrow">
        <?= partial('partials/empty-state', [
            'title' => content('newsletter', 'empty.title'),
            'text'  => content('newsletter', 'empty.text'),
            'icon'  => 'newspaper',
        ]) ?>
    </div>
</section>

<?php if (section_on('newsletter', 'subscribe')): ?>
    <?= partial('web/newsletter/_subscribe') ?>
<?php endif; ?>
