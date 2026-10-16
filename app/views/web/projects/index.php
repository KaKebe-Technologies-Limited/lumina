<?php
/**
 * PROJECTS list = the planned "proposed areas of intervention", grouped under the program pillar they belong to.
 * Copy: content('projects', ...) (registry app/config/pages/projects.php) + content('programs', 'labels.*'); data: Admin > Projects / Programs.
 *
 * Vars (ProjectsController::index):
 *   $pillars     slug => programs row + number, chip_label, projects[]   (every active program, in order)
 *   $loose       projects whose pillar is missing/inactive               $total  number of active projects
 *   $statuses    status => editable label        $statusChips  statuses that get a filter button ([] when only one status exists)
 *   $filtersOn   filter section switched on      $pillar / $status  active filter ('' = all)
 *
 * Filters work WITHOUT JavaScript: the buttons are plain links (?pillar=slug&status=planned) and the server marks the cards that do not match
 * with `hidden`. assets/js/projects-filter.js (progressive enhancement) toggles the same attributes in place. All 27 cards are always in the DOM.
 */

$heroImg = content_img('projects', 'hero.image');
$ctaBtn = content_btn('projects', 'hero.cta');
$ctaHref = trim((string) content('projects', 'hero.cta_url')) !== '' ? $ctaBtn['url'] : link_url((string) setting('donate_url', ''));
$actions = [];
if (trim((string) content('projects', 'hero.cta_label')) !== '' && $ctaHref !== '') {
    $actions[] = ['label' => (string) content('projects', 'hero.cta_label'), 'url' => $ctaHref, 'class' => 'btn--cta btn--caps'];
}

$listTitle = trim((string) content('projects', 'list.lead'));
$listName = $listTitle !== '' ? strip_highlight($listTitle) : strip_highlight((string) content('projects', 'hero.title'));
$cardCta = trim((string) content('projects', 'list.card_cta'));
$focusLabel = trim((string) content('programs', 'labels.planned_focus'));
$viewPillar = trim((string) content('programs', 'labels.view_pillar'));
$filterEmpty = trim((string) content('projects', 'list.filter_empty'));
$emptyBtn = content_btn('projects', 'list.empty_cta');

// a card is visible when it matches BOTH active filters
$matches = static fn(string $projectPillar, array $project): bool =>
    ($pillar === '' || $projectPillar === $pillar) && ($status === '' || (string) ($project['status'] ?? '') === $status);
// number of cards for a pillar ('' = any) and a status ('' = any); the pillar buttons keep the active status, the status buttons the active pillar
$countFor = static function (string $pillarValue, string $statusValue) use ($pillars, $loose): int {
    $n = 0;
    foreach ($pillars as $slug => $p) {
        foreach ($p['projects'] as $project) {
            if (($pillarValue === '' || $pillarValue === $slug) && ($statusValue === '' || (string) ($project['status'] ?? '') === $statusValue)) {
                $n++;
            }
        }
    }
    if ($pillarValue === '') {
        foreach ($loose as $project) {
            if ($statusValue === '' || (string) ($project['status'] ?? '') === $statusValue) {
                $n++;
            }
        }
    }
    return $n;
};
$href = static fn(string $p, string $s): string => url('projects', ['pillar' => $p, 'status' => $s]) . '#projects-list';

$visible = 0;
foreach ($pillars as $slug => $p) {
    foreach ($p['projects'] as $project) {
        $visible += $matches($slug, $project) ? 1 : 0;
    }
}
foreach ($loose as $project) {
    $visible += $matches('', $project) ? 1 : 0;
}
$showFilters = $filtersOn && $total > 0 && count($pillars) > 1;
$aria = trim((string) content('projects', 'filters.aria_label'));
$allLabel = trim((string) content('projects', 'filters.all'));
$statusGroupLabel = trim((string) content('projects', 'detail.status_label'));
$scriptOn = $showFilters;
?>
<?= partial('partials/page-hero', [
    'eyebrow' => (string) content('projects', 'hero.eyebrow'),
    'title'   => (string) content('projects', 'hero.title'),
    'lead'    => (string) content('projects', 'hero.lead'),
    'image'   => $heroImg['has'] ? ['src' => $heroImg['src'], 'alt' => $heroImg['alt'], 'focus' => '50% 30%'] : null,
    'actions' => $actions,
]) ?>

<?php /* ------------------------------------------------------------------ RESPONSIBLE-DEVELOPMENT NOTICE (always on, directly above the list) */ ?>
<section class="section pi-intro">
    <div class="container container--narrow">
        <?= partial('partials/projects-notice') ?>
    </div>
</section>

<?php /* ------------------------------------------------------------------ FILTERS + GROUPED LIST */ ?>
<section class="section pi-list" id="projects-list" aria-labelledby="projects-list-title">
    <div class="container">
        <?= partial('partials/section-head', ['eyebrow' => (string) content('projects', 'list.eyebrow'), 'title' => $listTitle !== '' ? $listTitle : (string) content('projects', 'hero.title'), 'align' => 'center', 'id' => 'projects-list-title']) ?>

        <?php if ($total > 0): ?>
        <div class="pi-filter" data-pi-filter data-base="<?= e(url('projects')) ?>" data-label="<?= e($listName) ?>" data-empty-text="<?= e($filterEmpty) ?>">
            <?php if ($showFilters): ?>
                <div class="pi-filters">
                    <nav class="pi-filters__row"<?= $aria !== '' ? ' aria-label="' . e($aria) . '"' : '' ?>>
                        <ul class="pi-chips" role="list">
                            <li><a class="pi-chip<?= $pillar === '' ? ' is-active' : '' ?>" href="<?= e($href('', $status)) ?>" data-pi-chip data-kind="pillar" data-value=""<?= $pillar === '' ? ' aria-current="true"' : '' ?>><span class="pi-chip__label"><?= e($allLabel) ?></span> <span class="pi-chip__count"><span class="visually-hidden">(</span><span data-pi-chip-count><?= (int) $countFor('', $status) ?></span><span class="visually-hidden">)</span></span></a></li>
                            <?php foreach ($pillars as $slug => $p): ?>
                                <li><a class="pi-chip<?= $pillar === $slug ? ' is-active' : '' ?>" href="<?= e($href($slug, $status)) ?>" data-pi-chip data-kind="pillar" data-value="<?= e($slug) ?>"<?= $pillar === $slug ? ' aria-current="true"' : '' ?>><span class="pi-chip__label"><?= e($p['chip_label']) ?></span> <span class="pi-chip__count"><span class="visually-hidden">(</span><span data-pi-chip-count><?= (int) $countFor($slug, $status) ?></span><span class="visually-hidden">)</span></span></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </nav>
                    <?php if ($statusChips): ?>
                        <div class="pi-filters__status">
                            <?php if ($statusGroupLabel !== ''): ?><span class="pi-filters__label" id="projects-status-label"><?= e($statusGroupLabel) ?></span><?php endif; ?>
                            <nav class="pi-filters__row"<?= $statusGroupLabel !== '' ? ' aria-labelledby="projects-status-label"' : '' ?>>
                                <ul class="pi-chips" role="list">
                                    <li><a class="pi-chip<?= $status === '' ? ' is-active' : '' ?>" href="<?= e($href($pillar, '')) ?>" data-pi-chip data-kind="status" data-value=""<?= $status === '' ? ' aria-current="true"' : '' ?>><span class="pi-chip__label"><?= e($allLabel) ?></span> <span class="pi-chip__count"><span class="visually-hidden">(</span><span data-pi-chip-count><?= (int) $countFor($pillar, '') ?></span><span class="visually-hidden">)</span></span></a></li>
                                    <?php foreach ($statusChips as $st): ?>
                                        <li><a class="pi-chip<?= $status === $st ? ' is-active' : '' ?>" href="<?= e($href($pillar, $st)) ?>" data-pi-chip data-kind="status" data-value="<?= e($st) ?>"<?= $status === $st ? ' aria-current="true"' : '' ?>><span class="pi-chip__label"><?= e($statuses[$st] ?? $st) ?></span> <span class="pi-chip__count"><span class="visually-hidden">(</span><span data-pi-chip-count><?= (int) $countFor($pillar, $st) ?></span><span class="visually-hidden">)</span></span></a></li>
                                    <?php endforeach; ?>
                                </ul>
                            </nav>
                        </div>
                    <?php endif; ?>
                </div>
                <p class="visually-hidden" role="status" aria-live="polite" aria-atomic="true" data-pi-live></p>
            <?php endif; ?>

            <p class="pi-empty" data-pi-empty<?= $visible > 0 ? ' hidden' : '' ?>><?= icon('folder', 'icon--lg') ?> <span><?= e($filterEmpty) ?></span></p>

            <div class="pi-groups">
                <?php foreach ($pillars as $slug => $p): if ($p['projects'] === []) { continue; }
                    $tone = (($p['number'] - 1) % 3) + 1;
                    $groupVisible = false;
                    foreach ($p['projects'] as $project) { $groupVisible = $groupVisible || $matches($slug, $project); }
                    $gid = 'pillar-' . $slug;
                    $planned = trim((string) ($p['tagline'] ?? '')); ?>
                    <section class="pi-group pi-tone-<?= (int) $tone ?>" id="<?= e($gid) ?>" aria-labelledby="<?= e($gid) ?>-title" data-pi-group data-pillar="<?= e($slug) ?>"<?= $groupVisible ? '' : ' hidden' ?>>
                        <header class="pi-group__head" data-reveal>
                            <span class="pi-group__num" aria-hidden="true"><?= e(str_pad((string) $p['number'], 2, '0', STR_PAD_LEFT)) ?></span>
                            <div class="pi-group__text">
                                <h3 class="pi-group__title" id="<?= e($gid) ?>-title"><?= e($p['title']) ?></h3>
                                <?php if ($planned !== ''): ?><p class="pi-group__focus"><?php if ($focusLabel !== ''): ?><strong><?= e($focusLabel) ?>:</strong> <?php endif; ?><?= e($planned) ?></p><?php endif; ?>
                            </div>
                            <?php if ($viewPillar !== ''): ?><a class="pi-group__link" href="<?= e(url('programs/' . $slug)) ?>"><?= e($viewPillar) ?><span class="visually-hidden">: <?= e($p['title']) ?></span> <?= icon('arrow-right', 'icon--sm') ?></a><?php endif; ?>
                        </header>
                        <ul class="pi-grid" role="list">
                            <?php foreach ($p['projects'] as $project): $show = $matches($slug, $project); ?>
                                <li class="pi-grid__item" data-pi-card data-pillar="<?= e($slug) ?>" data-status="<?= e((string) $project['status']) ?>"<?= $show ? '' : ' hidden' ?>>
                                    <?= partial('partials/projects-card', ['project' => $project, 'pillar' => $p, 'showPillar' => false, 'statuses' => $statuses, 'cta' => $cardCta]) ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </section>
                <?php endforeach; ?>

                <?php if ($loose): // interventions whose pillar is missing: still listed, under "All" ?>
                    <?php $looseVisible = false; foreach ($loose as $project) { $looseVisible = $looseVisible || $matches('', $project); } ?>
                    <section class="pi-group pi-tone-0" aria-labelledby="projects-other-title" data-pi-group data-pillar=""<?= $looseVisible ? '' : ' hidden' ?>>
                        <h3 class="visually-hidden" id="projects-other-title"><?= e($listName) ?></h3>
                        <ul class="pi-grid" role="list">
                            <?php foreach ($loose as $project): $show = $matches('', $project); ?>
                                <li class="pi-grid__item" data-pi-card data-pillar="" data-status="<?= e((string) $project['status']) ?>"<?= $show ? '' : ' hidden' ?>>
                                    <?= partial('partials/projects-card', ['project' => $project, 'pillar' => null, 'statuses' => $statuses, 'cta' => $cardCta]) ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </section>
                <?php endif; ?>
            </div>
        </div>
        <?php else: ?>
            <?= partial('partials/empty-state', [
                'icon'    => 'folder',
                'title'   => (string) content('projects', 'list.empty_title'),
                'text'    => (string) content('projects', 'list.empty_text'),
                'actions' => $emptyBtn['has'] ? [['label' => $emptyBtn['label'], 'url' => $emptyBtn['url'], 'class' => 'btn--primary']] : [],
            ]) ?>
        <?php endif; ?>
    </div>
</section>

<?= partial('partials/projects-cta') ?>

<?php if ($scriptOn): ?>
<?php $this->start('scripts'); ?><script src="<?= e(asset('js/projects-filter.js')) ?>" defer></script><?php $this->end(); ?>
<?php endif; ?>
