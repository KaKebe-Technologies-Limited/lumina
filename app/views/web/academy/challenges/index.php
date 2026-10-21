<?php
/** Challenges list. Vars: $running, $done, $categories, $kinds, $filters, $filtered, $learner. Copy: content('academy', 'challenges.*'). */
?>
<?= partial('partials/page-hero', ['title' => content('academy', 'challenges.page_title'), 'eyebrow' => 'LAF Academy', 'lead' => content('academy', 'challenges.page_lead'),
    'actions' => [['label' => 'Winners of the month', 'url' => url('academy/winners'), 'class' => 'btn--photo btn--caps']]]) ?>
<section class="section">
    <div class="container">
        <form class="filter-bar opp-filter" method="get" action="<?= e(url('academy/challenges')) ?>" role="search">
            <?= partial('partials/form-field', ['name' => 'kind', 'id' => 'ch-kind', 'label' => 'Type', 'type' => 'select', 'options' => $kinds, 'empty' => 'All types', 'value' => $filters['kind']]) ?>
            <?= partial('partials/form-field', ['name' => 'category', 'id' => 'ch-cat', 'label' => 'Topic', 'type' => 'select', 'options' => $categories, 'empty' => 'All topics', 'value' => $filters['category']]) ?>
            <button class="btn btn--primary btn--caps" type="submit">Filter</button>
            <?php if ($filtered): ?><a class="btn btn--ghost btn--caps filter-bar__reset" href="<?= e(url('academy/challenges')) ?>">Clear</a><?php endif; ?>
        </form>

        <h2 class="ac-block__title">Open and coming up</h2>
        <?php if ($running === []): ?>
            <?= partial('partials/empty-state', ['title' => content('academy', 'challenges.empty_title'), 'text' => content('academy', 'challenges.empty_text'), 'icon' => 'target']) ?>
        <?php else: ?>
            <div class="grid grid--3 grid--gap-lg post-grid ac-grid"><?php foreach ($running as $c): ?><?= partial('web/academy/_challenge_card', ['c' => $c]) ?><?php endforeach; ?></div>
        <?php endif; ?>

        <?php if ($done): ?>
            <h2 class="ac-block__title">Finished challenges</h2>
            <div class="grid grid--3 grid--gap-lg post-grid ac-grid"><?php foreach ($done as $c): ?><?= partial('web/academy/_challenge_card', ['c' => $c]) ?><?php endforeach; ?></div>
        <?php endif; ?>
    </div>
</section>
