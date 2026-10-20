<?php
/**
 * Opportunities listing. Vars: $rows, $pagination, $total, $q, $type, $closed, $counts (category => open count), $categories (key => label).
 * Copy: content('opportunities', ...) - admin: Pages > Opportunities; items: Content > Opportunities.
 */
$heroImg = content_img('opportunities', 'hero.image');
$filtered = $q !== '' || $type !== '' || $closed;
$pillUrl = static fn(string $t): string => url('opportunities', array_filter(['q' => $q !== '' ? $q : null, 'type' => $t !== '' ? $t : null, 'closed' => $closed ? '1' : null]));
$allCount = array_sum($counts);
$cta = content_btn('opportunities', 'cta.button');
?>
<?= partial('partials/page-hero', [
    'title'   => content('opportunities', 'hero.title'),
    'eyebrow' => content('opportunities', 'hero.eyebrow'),
    'lead'    => content('opportunities', 'hero.lead'),
    'image'   => $heroImg['has'] ? ['src' => $heroImg['src'], 'alt' => $heroImg['alt'], 'focus' => '50% 35%'] : null,
]) ?>

<?php section_begin('opportunities', 'list', ['id' => 'opportunities-list', 'labelledby' => 'opportunities-list-title']); ?>
    <?= partial('partials/section-head', [
        'eyebrow' => content('opportunities', 'list.eyebrow'),
        'title'   => content('opportunities', 'list.title'),
        'lead'    => content('opportunities', 'list.intro'),
        'id'      => 'opportunities-list-title',
        'align'   => 'center',
    ]) ?>

    <form class="filter-bar opp-filter" method="get" action="<?= e(url('opportunities')) ?>" role="search" aria-label="<?= e(strip_highlight((string) content('opportunities', 'list.title'))) ?>">
        <?= partial('partials/form-field', ['name' => 'q', 'id' => 'opp-q', 'label' => content('opportunities', 'list.search_label'), 'value' => $q, 'maxlength' => 100,
            'placeholder' => content('opportunities', 'list.search_placeholder'), 'class' => 'opp-filter__q']) ?>
        <?= partial('partials/form-field', ['name' => 'type', 'id' => 'opp-type', 'label' => content('opportunities', 'list.type_label'), 'type' => 'select', 'options' => $categories,
            'empty' => content('opportunities', 'list.all_types'), 'value' => $type]) ?>
        <label class="check opp-filter__closed"><input type="checkbox" name="closed" value="1"<?= $closed ? ' checked' : '' ?>><span><?= e(content('opportunities', 'list.show_closed')) ?></span></label>
        <button class="btn btn--primary btn--caps" type="submit"><?= e(content('opportunities', 'list.apply_filter')) ?></button>
        <?php if ($filtered): ?><a class="btn btn--ghost btn--caps filter-bar__reset" href="<?= e(url('opportunities')) ?>"><?= e(content('opportunities', 'list.reset')) ?></a><?php endif; ?>
    </form>

    <?php if ($counts): ?>
        <nav class="pills opp-pills" aria-label="<?= e(content('opportunities', 'list.type_label')) ?>">
            <a class="pills__item<?= $type === '' ? ' is-active' : '' ?>" href="<?= e($pillUrl('')) ?>"<?= $type === '' ? ' aria-current="true"' : '' ?>><?= e(content('opportunities', 'list.all_types')) ?> (<?= (int) $allCount ?>)</a>
            <?php foreach ($categories as $key => $label): if (empty($counts[$key])) { continue; } ?>
                <a class="pills__item<?= $type === $key ? ' is-active' : '' ?>" href="<?= e($pillUrl($key)) ?>"<?= $type === $key ? ' aria-current="true"' : '' ?>><?= e($label) ?> (<?= (int) $counts[$key] ?>)</a>
            <?php endforeach; ?>
        </nav>
    <?php endif; ?>

    <?php if ($rows): ?>
        <div class="grid grid--3 grid--gap-lg post-grid opp-grid">
            <?php foreach ($rows as $o): ?><?= partial('web/opportunities/_card', ['o' => $o]) ?><?php endforeach; ?>
        </div>
        <?= partial('partials/pagination', ['pagination' => $pagination, 'base' => 'opportunities']) ?>
    <?php else: ?>
        <?= partial('partials/empty-state', [
            'title' => content('opportunities', 'list.empty_title'),
            'text'  => content('opportunities', 'list.empty_text'),
            'icon'  => 'award',
        ]) ?>
    <?php endif; ?>

    <?php if (trim((string) content('opportunities', 'list.credit_note')) !== ''): ?><p class="opp-note"><?= icon('info', 'icon--sm') ?> <?= e(content('opportunities', 'list.credit_note')) ?></p><?php endif; ?>
<?php section_end(); ?>

<?php section_begin('opportunities', 'cta', ['class' => 'section--tint', 'labelledby' => 'opp-cta-title']); ?>
    <div class="cta" data-reveal>
        <h2 class="cta__title" id="opp-cta-title"><?= highlight((string) content('opportunities', 'cta.title')) ?></h2>
        <p class="cta__text"><?= e(content('opportunities', 'cta.text')) ?></p>
        <?php if ($cta['has']): ?><p><a class="btn btn--cta btn--caps btn--lg" href="<?= e($cta['url']) ?>"><?= e($cta['label']) ?></a></p><?php endif; ?>
    </div>
<?php section_end(); ?>
