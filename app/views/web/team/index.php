<?php
/**
 * TEAM (/team) - OWNED BY B7 "org-public".
 * Vars: $groups (board|staff|advisor|volunteer => list of team_members rows), $total (int).
 * Every visible string is read through content('team', ...) so the owner can edit it in the admin (Pages > Our Team).
 */
$heroImg = content_img('team', 'hero.image');
$sectionOf = ['board' => 'board', 'staff' => 'staff', 'advisor' => 'advisors', 'volunteer' => 'volunteers'];
$readBio = (string) content('team', 'labels.read_bio');
$closeLabel = (string) content('team', 'labels.close');
$empty = content_btn('team', 'empty.cta');
$join = content_btn('team', 'join.cta');
?>
<?= partial('partials/page-hero', [
    'eyebrow' => content('team', 'hero.eyebrow'),
    'title'   => content('team', 'hero.title'),
    'lead'    => content('team', 'hero.lead'),
    'image'   => $heroImg['has'] ? ['src' => $heroImg['src'], 'alt' => $heroImg['alt']] : null,
]) ?>

<?php if ($total === 0): ?>
    <section class="section" id="team-empty">
        <div class="container container--narrow">
            <?= partial('partials/empty-state', [
                'title'   => content('team', 'empty.title'),
                'text'    => content('team', 'empty.text'),
                'icon'    => 'users',
                'actions' => $empty['has'] ? [['label' => $empty['label'], 'url' => $empty['url'], 'class' => 'btn--primary']] : [],
            ]) ?>
        </div>
    </section>
<?php else: ?>
    <div data-team-groups data-close-label="<?= e($closeLabel) ?>">
    <?php $shown = 0; foreach ($sectionOf as $group => $sectionKey):
        if (empty($groups[$group])) { continue; }
        $shown++;
        $headingId = 'team-' . $sectionKey . '-title';
        section_begin('team', $sectionKey, ['class' => $shown % 2 === 0 ? 'section--tint' : '', 'labelledby' => $headingId]);
    ?>
        <div class="section__header">
            <h2 id="<?= e($headingId) ?>"><?= e(content('team', $sectionKey . '.title')) ?></h2>
            <?php if (trim((string) content('team', $sectionKey . '.intro')) !== ''): ?>
                <p class="lead"><?= e(content('team', $sectionKey . '.intro')) ?></p>
            <?php endif; ?>
        </div>
        <div class="grid grid--4 team-grid">
            <?php foreach ($groups[$group] as $member): ?>
                <?= partial('web/team/_card', ['member' => $member, 'readBio' => $readBio]) ?>
            <?php endforeach; ?>
        </div>
    <?php section_end(); endforeach; ?>
    </div>

    <?php section_begin('team', 'join', ['class' => 'section--navy', 'container' => 'container--narrow']); ?>
        <div class="team-join text-center">
            <h2><?= e(content('team', 'join.title')) ?></h2>
            <?php if (trim((string) content('team', 'join.text')) !== ''): ?><p class="lead mt-3"><?= e(content('team', 'join.text')) ?></p><?php endif; ?>
            <?php if ($join['has']): ?>
                <p class="mt-5"><a class="btn btn--accent btn--lg" href="<?= e($join['url']) ?>"<?= $join['external'] ? ' target="_blank" rel="noopener noreferrer"' : '' ?>><?= e($join['label']) ?></a></p>
            <?php endif; ?>
        </div>
    <?php section_end(); ?>
<?php endif; ?>

<?php $this->start('scripts'); ?>
<script src="<?= e(asset('js/pages/team.js')) ?>" defer></script>
<?php $this->end(); ?>
