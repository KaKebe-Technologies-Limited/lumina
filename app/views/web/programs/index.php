<?php
/**
 * PROGRAMS list: "Our planned programs". Copy: content('programs', ...) (owner wording, docs/content-source/planned-programs.md);
 * the pillars and their areas of intervention are database rows. Vars: $programs (active pillars), $interventions (program_id => list of ['slug','title']).
 * Order: hero -> #pillars -> cross-cutting priorities -> implementation approach -> who we intend to serve -> partnerships -> commitment.
 * These are PLANNED programs: nothing here may be worded as delivered results. Map of keys: docs/PROGRAMS_CONTENT_MAP.md.
 */

// Decorative icons by position (the registry rows carry no icon field). Purely visual: aria-hidden.
$priorityIcons = ['rocket', 'briefcase', 'lightbulb', 'users', 'hand-heart', 'compass', 'sprout', 'handshake'];
$wayIcons = ['banknote', 'laptop', 'award', 'heart', 'building', 'briefcase', 'bar-chart', 'lightbulb', 'handshake'];

/** Rows of a registry repeater that have the given (non-empty) field. */
$rows = static fn(string $key, string $field): array => array_values(array_filter(
    content_list('programs', $key),
    static fn(array $r): bool => trim((string) ($r[$field] ?? '')) !== ''
));

// ------------------------------------------------------------------------------------------------ HERO
$heroImg = content_img('programs', 'hero.image');
$heroActions = [];
foreach (['cta1' => 'btn--cta btn--caps btn--lg', 'cta2' => 'btn--outline-light btn--caps btn--lg'] as $key => $class) {
    $b = content_btn('programs', 'hero.' . $key);
    if ($b['has']) {
        $heroActions[] = ['label' => $b['label'], 'url' => $b['url'], 'class' => $class];
    }
}
?>
<?= partial('partials/programs-hero', [
    'eyebrow' => (string) content('programs', 'hero.eyebrow'),
    'titles'  => [(string) content('programs', 'hero.title'), (string) content('programs', 'hero.title2')],
    'leads'   => [(string) content('programs', 'hero.lead'), (string) content('programs', 'hero.lead2'), (string) content('programs', 'hero.lead3')],
    'quote'   => (string) content('programs', 'hero.vision'),
    'actions' => $heroActions,
    'image'   => $heroImg['has'] ? ['src' => $heroImg['src'], 'alt' => $heroImg['alt'], 'focus' => '50% 12%'] : null,
]) ?>

<?php /* ------------------------------------------------------------------ #pillars: the three planned pillars */ ?>
<?php section_begin('programs', 'pillars', ['class' => 'programs-pillars', 'labelledby' => 'programs-pillars-title']); ?>
    <?= partial('partials/section-head', [
        'eyebrow' => (string) content('programs', 'pillars.eyebrow'),
        'title'   => (string) content('programs', 'pillars.title'),
        'lead'    => (string) content('programs', 'pillars.intro'),
        'align'   => 'center',
        'id'      => 'programs-pillars-title',
    ]) ?>
    <?php if ($programs): ?>
        <ol class="programs-pillars__list" role="list">
            <?php foreach ($programs as $i => $program): ?>
                <li><?= partial('partials/programs-pillar', [
                    'program'       => $program,
                    'number'        => str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT),
                    'interventions' => $interventions[(int) $program['id']] ?? [],
                    'reverse'       => $i % 2 === 1,
                ]) ?></li>
            <?php endforeach; ?>
        </ol>
    <?php else:
        $emptyBtn = content_btn('programs', 'pillars.empty_cta'); ?>
        <?= partial('partials/empty-state', [
            'icon'    => 'layers',
            'title'   => (string) content('programs', 'pillars.empty_title'),
            'text'    => (string) content('programs', 'pillars.empty_text'),
            'actions' => $emptyBtn['has'] ? [['label' => $emptyBtn['label'], 'url' => $emptyBtn['url'], 'class' => 'btn--primary']] : [],
        ]) ?>
    <?php endif; ?>
<?php section_end(); ?>

<?php /* ------------------------------------------------------------------ CROSS-CUTTING PRIORITIES (8 cards) */ ?>
<?php section_begin('programs', 'cross_cutting', ['class' => 'section--tint programs-priorities', 'labelledby' => 'programs-priorities-title']); $priorities = $rows('cross_cutting.items', 'title'); ?>
    <?= partial('partials/section-head', [
        'eyebrow' => (string) content('programs', 'cross_cutting.eyebrow'),
        'title'   => (string) content('programs', 'cross_cutting.title'),
        'lead'    => (string) content('programs', 'cross_cutting.intro'),
        'align'   => 'center',
        'id'      => 'programs-priorities-title',
    ]) ?>
    <?php if ($priorities): ?>
        <ul class="programs-grid programs-grid--priorities" role="list">
            <?php foreach ($priorities as $i => $p): ?>
                <li><?= partial('partials/feature-card', [
                    'icon'  => (string) (($p['icon'] ?? '') !== '' ? $p['icon'] : $priorityIcons[$i % count($priorityIcons)]),
                    'title' => (string) $p['title'],
                    'text'  => (string) ($p['text'] ?? ''),
                ]) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
<?php section_end(); ?>

<?php /* ------------------------------------------------------------------ IMPLEMENTATION APPROACH (6-step timeline) */ ?>
<?php section_begin('programs', 'approach', ['class' => 'programs-approach', 'labelledby' => 'programs-approach-title']); $steps = $rows('approach.items', 'title'); ?>
    <?= partial('partials/section-head', [
        'eyebrow' => (string) content('programs', 'approach.eyebrow'),
        'title'   => (string) content('programs', 'approach.title'),
        'lead'    => (string) content('programs', 'approach.intro'),
        'align'   => 'center',
        'id'      => 'programs-approach-title',
    ]) ?>
    <?php if ($steps): ?>
        <ol class="programs-steps" role="list">
            <?php foreach ($steps as $i => $s): $num = trim((string) ($s['number'] ?? '')) !== '' ? (string) $s['number'] : str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT); ?>
                <li class="programs-step" data-reveal>
                    <span class="programs-step__num" aria-hidden="true"><?= e($num) ?></span>
                    <div class="programs-step__body">
                        <h3 class="programs-step__title"><?= e($s['title']) ?></h3>
                        <?php if (trim((string) ($s['text'] ?? '')) !== ''): ?><p class="programs-step__text"><?= e($s['text']) ?></p><?php endif; ?>
                    </div>
                </li>
            <?php endforeach; ?>
        </ol>
    <?php endif; ?>
<?php section_end(); ?>

<?php /* ------------------------------------------------------------------ WHO WE INTEND TO SERVE (+ geographic focus in a navy band) */ ?>
<?php section_begin('programs', 'audience', ['class' => 'section--cool programs-audience-section', 'labelledby' => 'programs-audience-title']); $groups = $rows('audience.items', 'text'); $geo = trim((string) content('programs', 'audience.geographic')); ?>
    <?= partial('partials/section-head', [
        'eyebrow' => (string) content('programs', 'audience.eyebrow'),
        'title'   => (string) content('programs', 'audience.title'),
        'lead'    => (string) content('programs', 'audience.intro'),
        'align'   => 'center',
        'id'      => 'programs-audience-title',
    ]) ?>
    <?php if ($groups): ?>
        <ul class="programs-grid programs-grid--audience" role="list">
            <?php foreach ($groups as $g): ?>
                <li class="programs-audience" data-reveal>
                    <span class="ia-checklist__tick" aria-hidden="true"><?= icon('check') ?></span>
                    <span class="programs-audience__text"><?= e($g['text']) ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
    <?php if ($geo !== ''): ?>
        <div class="programs-geo" data-reveal>
            <span class="programs-geo__icon" aria-hidden="true"><?= icon('map-pin', 'icon--lg') ?></span>
            <p class="programs-geo__text"><?= e($geo) ?></p>
        </div>
    <?php endif; ?>
<?php section_end(); ?>

<?php /* ------------------------------------------------------------------ PARTNERSHIPS (9 ways + navy call-to-action band) */ ?>
<?php section_begin('programs', 'partnerships', ['class' => 'programs-partners', 'labelledby' => 'programs-partners-title']);
    $ways = $rows('partnerships.items', 'text');
    $listIntro = trim((string) content('programs', 'partnerships.list_intro'));
    $closing = array_values(array_filter([trim((string) content('programs', 'partnerships.closing')), trim((string) content('programs', 'partnerships.closing2'))], static fn(string $t): bool => $t !== ''));
    $pBtn1 = content_btn('programs', 'partnerships.cta1');
    $pBtn2 = content_btn('programs', 'partnerships.cta2'); ?>
    <?= partial('partials/section-head', [
        'eyebrow' => (string) content('programs', 'partnerships.eyebrow'),
        'title'   => (string) content('programs', 'partnerships.title'),
        'lead'    => (string) content('programs', 'partnerships.intro'),
        'align'   => 'center',
        'id'      => 'programs-partners-title',
    ]) ?>
    <?php if ($listIntro !== ''): ?><p class="programs-partners__list-intro" id="programs-ways-intro"><?= e($listIntro) ?></p><?php endif; ?>
    <?php if ($ways): ?>
        <ul class="programs-grid programs-grid--ways" role="list"<?= $listIntro !== '' ? ' aria-labelledby="programs-ways-intro"' : '' ?>>
            <?php foreach ($ways as $i => $w): ?>
                <li class="programs-way" data-reveal>
                    <span class="programs-way__icon" aria-hidden="true"><?= icon(safe_icon($wayIcons[$i % count($wayIcons)])) ?></span>
                    <span class="programs-way__text"><?= e($w['text']) ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
    <?php if ($closing || $pBtn1['has'] || $pBtn2['has']): ?>
        <div class="cta programs-cta" data-reveal>
            <?php foreach ($closing as $i => $c): ?><p class="<?= $i === 0 ? 'cta__text' : 'programs-cta__lead' ?>"><?= e($c) ?></p><?php endforeach; ?>
            <?php if ($pBtn1['has'] || $pBtn2['has']): ?>
                <div class="cta__actions">
                    <?php if ($pBtn1['has']): ?><a class="btn btn--cta btn--caps btn--lg" href="<?= e($pBtn1['url']) ?>"><?= e($pBtn1['label']) ?></a><?php endif; ?>
                    <?php if ($pBtn2['has']): ?><a class="btn btn--outline-light btn--caps btn--lg" href="<?= e($pBtn2['url']) ?>"><?= e($pBtn2['label']) ?></a><?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
<?php section_end(); ?>

<?php /* ------------------------------------------------------------------ COMMITMENT TO RESPONSIBLE DEVELOPMENT (always shown) */ ?>
<?php section_begin('programs', 'commitment', ['class' => 'section--tint programs-commitment-section', 'labelledby' => 'programs-commitment-title']);
    $cBody = content_html('programs', 'commitment.body');
    $signName = trim((string) content('programs', 'commitment.signoff_name'));
    $signTag = trim((string) content('programs', 'commitment.signoff_tagline')); ?>
    <div class="programs-commitment" data-reveal>
        <span class="programs-commitment__icon" aria-hidden="true"><?= icon('shield', 'icon--lg') ?></span>
        <?= partial('partials/section-head', [
            'eyebrow' => (string) content('programs', 'commitment.eyebrow'),
            'title'   => (string) content('programs', 'commitment.title'),
            'id'      => 'programs-commitment-title',
        ]) ?>
        <?php if (trim($cBody) !== ''): ?><div class="prose programs-commitment__body"><?= $cBody ?></div><?php endif; ?>
        <?php if ($signName !== '' || $signTag !== ''): ?>
            <p class="programs-commitment__sign">
                <?php if ($signName !== ''): ?><strong class="programs-commitment__name"><?= e($signName) ?></strong><?php endif; ?>
                <?php if ($signTag !== ''): ?><span class="programs-commitment__tagline"><?= e($signTag) ?></span><?php endif; ?>
            </p>
        <?php endif; ?>
    </div>
<?php section_end(); ?>
