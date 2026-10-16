<?php
/**
 * REPORTS & DOWNLOADS (/reports) - B-INNER-B restyle of the B7 view.
 * Vars: $documents (rows of the current page), $pagination, $everything (count of ALL public documents), $categories (category keys that have
 * documents), $years (years that have documents), $category / $year (active filter, '' / 0 = none), $filtered (bool).
 * Every visible string is read through content('reports', ...) so the owner can edit it in the admin (Pages > Reports & Downloads).
 */
$heroImg = content_img('reports', 'hero.image');
$categoryLabels = [];
foreach (array_keys(\App\Models\Document::CATEGORIES) as $key) {
    $categoryLabels[$key] = (string) content('reports', 'categories.' . $key);
}
$showFilter = $everything > 0 && (count($categories) > 1 || count($years) > 1 || $filtered);
$catOptions = [];
foreach ($categories as $key) {
    $catOptions[$key] = $categoryLabels[$key] ?? label_from($key);
}
$yearOptions = [];
foreach ($years as $y) {
    $yearOptions[(string) $y] = (string) $y;
}
$count = (int) $pagination->total;
$countText = $count === 1
    ? (string) content('reports', 'list.count_one')
    : str_replace('{n}', (string) $count, (string) content('reports', 'list.count_many'));
$contact = content_btn('reports', 'contact.cta');
$empty = content_btn('reports', 'empty.cta');
$empty2 = content_btn('reports', 'empty.cta2');
$emptyActions = [];
if ($empty['has']) {
    $emptyActions[] = ['label' => $empty['label'], 'url' => $empty['url'], 'class' => 'btn--cta btn--caps'];
}
if ($empty2['has']) {
    $emptyActions[] = ['label' => $empty2['label'], 'url' => $empty2['url'], 'class' => 'btn--outline btn--caps'];
}
?>
<?= partial('partials/page-hero', [
    'eyebrow' => content('reports', 'hero.eyebrow'),
    'title'   => content('reports', 'hero.title'),
    'lead'    => content('reports', 'hero.lead'),
    'image'   => $heroImg['has'] ? ['src' => $heroImg['src'], 'alt' => $heroImg['alt'], 'focus' => '50% 45%'] : null,
    'sheet'   => $everything === 0 ? 'tint' : 'white',
]) ?>

<?php if ($everything === 0): ?>
    <section class="section section--tint" id="reports-empty">
        <div class="container container--narrow">
            <?= partial('partials/empty-state', [
                'title'   => content('reports', 'empty.title'),
                'text'    => content('reports', 'empty.text'),
                'icon'    => 'file-text',
                'actions' => $emptyActions,
            ]) ?>
        </div>
    </section>
<?php else: ?>
    <?php section_begin('reports', 'list', ['labelledby' => 'documents-title']); ?>
        <?= partial('partials/section-head', [
            'eyebrow' => content('reports', 'list.eyebrow'),
            'title'   => content('reports', 'list.title'),
            'id'      => 'documents-title',
            'align'   => 'center',
        ]) ?>
        <p class="reports-count" aria-live="polite"><?= icon('file-text', 'icon--sm') ?> <?= e($countText) ?></p>

        <?php if ($showFilter): ?>
            <form class="filter-bar" method="get" action="<?= e(url('reports')) ?>" aria-label="<?= e(content('reports', 'filters.legend')) ?>">
                <?php if (count($catOptions) > 0): ?>
                    <?= partial('partials/form-field', [
                        'name' => 'category', 'label' => content('reports', 'filters.category_label'), 'type' => 'select',
                        'options' => $catOptions, 'empty' => content('reports', 'filters.all_categories'), 'value' => $category,
                    ]) ?>
                <?php endif; ?>
                <?php if (count($yearOptions) > 0): ?>
                    <?= partial('partials/form-field', [
                        'name' => 'year', 'label' => content('reports', 'filters.year_label'), 'type' => 'select',
                        'options' => $yearOptions, 'empty' => content('reports', 'filters.all_years'), 'value' => $year > 0 ? (string) $year : '',
                    ]) ?>
                <?php endif; ?>
                <button class="btn btn--primary btn--caps" type="submit"><?= e(content('reports', 'filters.apply')) ?></button>
                <?php if ($filtered): ?>
                    <a class="btn btn--ghost btn--caps filter-bar__reset" href="<?= e(url('reports')) ?>"><?= e(content('reports', 'filters.reset')) ?></a>
                <?php endif; ?>
            </form>
        <?php endif; ?>

        <?php if ($documents === []): ?>
            <?= partial('partials/empty-state', [
                'title'   => content('reports', 'no_results.title'),
                'text'    => content('reports', 'no_results.text'),
                'icon'    => 'search',
                'actions' => trim((string) content('reports', 'no_results.cta_label')) !== ''
                    ? [['label' => content('reports', 'no_results.cta_label'), 'url' => url('reports'), 'class' => 'btn--cta btn--caps']] : [],
            ]) ?>
        <?php else: ?>
            <div class="document-list">
                <?php foreach ($documents as $doc): ?>
                    <?= partial('web/reports/_row', [
                        'doc'            => $doc,
                        'categoryLabels' => $categoryLabels,
                        'cta'            => content('reports', 'list.download'),
                        'publishedLabel' => content('reports', 'list.published'),
                    ]) ?>
                <?php endforeach; ?>
            </div>
            <?= partial('partials/pagination', ['pagination' => $pagination]) ?>
        <?php endif; ?>
    <?php section_end(); ?>

    <?php section_begin('reports', 'contact', ['bleed' => true, 'class' => 'section--band-wrap']); ?>
        <?= partial('web/blog/_band', [
            'title'   => content('reports', 'contact.title'),
            'text'    => content('reports', 'contact.text'),
            'actions' => [['label' => $contact['label'], 'url' => $contact['url'], 'external' => $contact['external']]],
        ]) ?>
    <?php section_end(); ?>
<?php endif; ?>
