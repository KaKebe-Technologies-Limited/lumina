<?php
/** Partner courses page. Vars: $rows, $filters, $filtered, $categories, $levels, $providers, $counts, $costs, $total, $learner. Copy: content('academy', 'partners.*'). */
use App\Services\Academy\PartnerCourses;

$by = ['beginner' => [], 'intermediate' => [], 'advanced' => []];
foreach ($rows as $r) {
    $by[$r['level']][] = $r;
}
$levelNote = ['beginner' => 'No experience needed', 'intermediate' => 'You can already use a computer and know the basics', 'advanced' => 'For learners comfortable with the intermediate skills'];
?>
<?= partial('partials/page-hero', ['title' => content('academy', 'partners.page_title'), 'eyebrow' => 'LAF Academy', 'lead' => content('academy', 'partners.page_lead')]) ?>
<section class="section" id="partner-courses">
    <div class="container">
        <p class="opp-note opp-note--warn ac-note-box"><?= icon('info', 'icon--sm') ?> <?= e(content('academy', 'partners.disclaimer')) ?></p>

        <form class="filter-bar opp-filter" method="get" action="<?= e(url('academy/partner-courses')) ?>" role="search">
            <?= partial('partials/form-field', ['name' => 'q', 'id' => 'pc-q', 'label' => 'Search', 'value' => $filters['q'], 'maxlength' => 80, 'placeholder' => 'For example: Python, Excel, cybersecurity', 'class' => 'opp-filter__q']) ?>
            <?= partial('partials/form-field', ['name' => 'category', 'id' => 'pc-cat', 'label' => 'Topic', 'type' => 'select', 'options' => $categories, 'empty' => 'All topics', 'value' => $filters['category']]) ?>
            <?= partial('partials/form-field', ['name' => 'level', 'id' => 'pc-level', 'label' => 'Level', 'type' => 'select', 'options' => $levels, 'empty' => 'All levels', 'value' => $filters['level']]) ?>
            <?= partial('partials/form-field', ['name' => 'provider', 'id' => 'pc-prov', 'label' => 'Platform', 'type' => 'select', 'options' => array_combine($providers, $providers) ?: [], 'empty' => 'All platforms', 'value' => $filters['provider']]) ?>
            <button class="btn btn--primary btn--caps" type="submit">Search</button>
            <?php if ($filtered): ?><a class="btn btn--ghost btn--caps filter-bar__reset" href="<?= e(url('academy/partner-courses')) ?>">Clear</a><?php endif; ?>
        </form>

        <?php if ($counts): ?>
            <nav class="pills opp-pills" aria-label="Topic">
                <a class="pills__item<?= $filters['category'] === '' ? ' is-active' : '' ?>" href="<?= e(url('academy/partner-courses')) ?>">All topics (<?= (int) array_sum($counts) ?>)</a>
                <?php foreach ($categories as $key => $label): if (empty($counts[$key])) { continue; } ?>
                    <a class="pills__item<?= $filters['category'] === $key ? ' is-active' : '' ?>" href="<?= e(url('academy/partner-courses', ['category' => $key])) ?>"><?= e($label) ?> (<?= (int) $counts[$key] ?>)</a>
                <?php endforeach; ?>
            </nav>
        <?php endif; ?>

        <?php if ($rows === []): ?>
            <?= partial('partials/empty-state', ['title' => content('academy', 'partners.empty_title'), 'text' => content('academy', 'partners.empty_text'), 'icon' => 'book']) ?>
        <?php else: ?>
            <p class="ac-note"><?= (int) $total ?> course<?= (int) $total === 1 ? '' : 's' ?> from <?= count(array_unique(array_column($rows, 'provider'))) ?> platforms</p>
            <?php foreach ($by as $lvl => $list): if ($list === []) { continue; } ?>
                <h2 class="ac-block__title ac-level-title" id="lvl-<?= e($lvl) ?>"><?= e($levels[$lvl] ?? ucfirst($lvl)) ?> <small><?= e($levelNote[$lvl] ?? '') ?> &middot; <?= count($list) ?></small></h2>
                <div class="grid grid--3 grid--gap-lg post-grid ac-grid">
                    <?php foreach ($list as $p): ?><?= partial('web/academy/_partner_card', ['p' => $p]) ?><?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <p class="ac-center"><a class="btn btn--outline btn--caps" href="<?= e(url('academy')) ?>#courses">Back to the LAF Academy courses</a></p>
    </div>
</section>
