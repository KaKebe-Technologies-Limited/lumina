<?php
/**
 * TEAM (/team) - B-INNER-B restyle of the B7 view.
 * Vars: $groups (board|staff|advisor|volunteer => list of team_members rows), $total (int).
 * Every visible string is read through content('team', ...) so the owner can edit it in the admin (Pages > Our Team).
 */
$heroImg = content_img('team', 'hero.image');
$sectionOf = ['board' => 'board', 'staff' => 'staff', 'advisor' => 'advisors', 'volunteer' => 'volunteers'];
$readBio = (string) content('team', 'labels.read_bio');
$closeLabel = (string) content('team', 'labels.close');
$empty = content_btn('team', 'empty.cta');
$empty2 = content_btn('team', 'empty.cta2');
$join = content_btn('team', 'join.cta');
$emptyActions = [];
if ($empty['has']) {
    $emptyActions[] = ['label' => $empty['label'], 'url' => $empty['url'], 'class' => 'btn--cta btn--caps'];
}
if ($empty2['has']) {
    $emptyActions[] = ['label' => $empty2['label'], 'url' => $empty2['url'], 'class' => 'btn--outline btn--caps'];
}
?>
<?= partial('partials/page-hero', [
    'eyebrow' => content('team', 'hero.eyebrow'),
    'title'   => content('team', 'hero.title'),
    'lead'    => content('team', 'hero.lead'),
    'image'   => $heroImg['has'] ? ['src' => $heroImg['src'], 'alt' => $heroImg['alt'], 'focus' => '40% 12%'] : null,
    'sheet'   => $total === 0 ? 'tint' : 'white',
]) ?>

<?php if ($total === 0): ?>
    <section class="section section--tint" id="team-empty">
        <div class="container container--narrow">
            <?= partial('partials/empty-state', [
                'title'   => content('team', 'empty.title'),
                'text'    => content('team', 'empty.text'),
                'icon'    => 'users',
                'actions' => $emptyActions,
            ]) ?>
        </div>
    </section>
<?php else: ?>
    <div data-team-groups data-close-label="<?= e($closeLabel) ?>">
    <?php $shown = 0; foreach ($sectionOf as $group => $sectionKey):
        if (empty($groups[$group])) { continue; }
        $shown++;
        $count = count($groups[$group]);
        $headingId = 'team-' . $sectionKey . '-title';
        section_begin('team', $sectionKey, ['class' => $shown % 2 === 0 ? 'section--tint' : '', 'labelledby' => $headingId]);
    ?>
        <?= partial('partials/section-head', [
            'eyebrow' => content('team', $sectionKey . '.eyebrow'),
            'title'   => content('team', $sectionKey . '.title'),
            'lead'    => content('team', $sectionKey . '.intro'),
            'id'      => $headingId,
            'align'   => 'center',
        ]) ?>
        <div class="team-grid team-grid--n<?= $count > 4 ? '4' : (int) $count ?>">
            <?php foreach ($groups[$group] as $member): ?>
                <?= partial('web/team/_card', ['member' => $member, 'readBio' => $readBio]) ?>
            <?php endforeach; ?>
        </div>
    <?php section_end(); endforeach; ?>
    </div>

    <?php section_begin('team', 'join', ['bleed' => true, 'class' => 'section--band-wrap']); ?>
        <?= partial('web/blog/_band', [
            'title'   => content('team', 'join.title'),
            'text'    => content('team', 'join.text'),
            'actions' => [['label' => $join['label'], 'url' => $join['url'], 'external' => $join['external']]],
        ]) ?>
    <?php section_end(); ?>
<?php endif; ?>

<?php $this->start('scripts'); ?>
<script src="<?= e(asset('js/pages/team.js')) ?>" defer></script>
<?php $this->end(); ?>
