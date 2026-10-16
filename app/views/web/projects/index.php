<?php
/**
 * PROJECTS list. Copy: content('projects', ...). Vars: $projects (rows of Admin > Projects), $pagination, $statuses (status => editable label).
 * Alternating photo / text rows with a numbered round chip on the photo edge (the number is the row position, not data).
 */

$heroImg = content_img('projects', 'hero.image');
$donateBtn = content_btn('projects', 'hero.cta');
$donateHref = trim((string) content('projects', 'hero.cta_url')) !== '' ? $donateBtn['url'] : link_url((string) setting('donate_url', ''));
$actions = [];
if (trim((string) content('projects', 'hero.cta_label')) !== '' && $donateHref !== '') {
    $actions[] = ['label' => (string) content('projects', 'hero.cta_label'), 'url' => $donateHref, 'class' => 'btn--cta btn--caps'];
}
$cta = trim((string) content('projects', 'list.card_cta'));
$emptyBtn = content_btn('projects', 'list.empty_cta');
$listTitle = trim((string) content('projects', 'list.lead'));
$rowTag = $listTitle !== '' ? 'h3' : 'h2';
?>
<?= partial('partials/page-hero', [
    'eyebrow' => (string) content('projects', 'hero.eyebrow'),
    'title'   => (string) content('projects', 'hero.title'),
    'lead'    => (string) content('projects', 'hero.lead'),
    'image'   => $heroImg['has'] ? ['src' => $heroImg['src'], 'alt' => $heroImg['alt'], 'focus' => '50% 30%'] : null,
    'actions' => $actions,
]) ?>

<section class="section projects-list" id="projects-list" aria-label="<?= e(strip_highlight($listTitle !== '' ? $listTitle : (string) content('projects', 'hero.title'))) ?>">
    <div class="container">
        <?php if ($projects): ?>
            <?= partial('partials/section-head', ['eyebrow' => (string) content('projects', 'list.eyebrow'), 'title' => $listTitle, 'align' => 'center']) ?>
            <div class="projects-list__rows">
                <?php foreach ($projects as $i => $project): $img = img_url((string) ($project['image'] ?? '')); $href = url('projects/' . $project['slug']); $num = str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT); ?>
                    <article class="project-row<?= $i % 2 === 1 ? ' project-row--reverse' : '' ?>" id="<?= e($project['slug']) ?>" data-reveal>
                        <div class="project-row__media">
                            <div class="project-row__frame">
                                <?php if ($img !== ''): ?>
                                    <a href="<?= e($href) ?>" tabindex="-1" aria-hidden="true"><img src="<?= e($img) ?>" alt="" width="800" height="600" loading="<?= $i === 0 ? 'eager' : 'lazy' ?>" decoding="async"></a>
                                <?php else: ?>
                                    <span class="project-row__bignum" aria-hidden="true"><?= e($num) ?></span>
                                <?php endif; ?>
                            </div>
                            <?php if ($img !== ''): ?><span class="project-row__num" aria-hidden="true"><?= e($num) ?></span><?php endif; ?>
                        </div>
                        <div class="project-row__body">
                            <div class="project-row__meta">
                                <?php if (trim((string) ($project['tagline'] ?? '')) !== ''): ?><span class="eyebrow"><?= e($project['tagline']) ?></span><?php endif; ?>
                                <?php if (!empty($statuses[$project['status'] ?? ''])): ?>
                                    <span class="badge badge--<?= ['planned' => 'info', 'ongoing' => 'success', 'completed' => 'neutral'][$project['status']] ?? 'neutral' ?>"><?= e($statuses[$project['status']]) ?></span>
                                <?php endif; ?>
                            </div>
                            <<?= $rowTag ?> class="project-row__title"><a href="<?= e($href) ?>"><?= e($project['title']) ?></a></<?= $rowTag ?>>
                            <?php if (trim((string) ($project['summary'] ?? '')) !== ''): ?><p class="project-row__text"><?= e($project['summary']) ?></p><?php endif; ?>
                            <?php if (trim((string) ($project['location'] ?? '')) !== ''): ?><p class="project-row__where"><?= icon('map-pin', 'icon--sm') ?> <?= e($project['location']) ?></p><?php endif; ?>
                            <?php if ($cta !== ''): ?><p class="project-row__cta"><a class="btn btn--cta btn--caps btn--sm" href="<?= e($href) ?>"><?= e($cta) ?><span class="visually-hidden">: <?= e($project['title']) ?></span></a></p><?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
            <?= partial('partials/pagination', ['pagination' => $pagination]) ?>
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

<?php /* ------------------------------------------------------------------ OUR APPROACH */ ?>
<?php
$appImgs = [content_img('projects', 'approach.image'), content_img('projects', 'approach.image2')];
$appHasMedia = (bool) array_filter($appImgs, static fn(array $i): bool => $i['has']);
$items = array_values(array_filter(content_list('projects', 'approach.items'), static fn(array $r): bool => trim((string) ($r['text'] ?? '')) !== ''));
$closing = trim((string) content('projects', 'approach.closing'));
$appIntro = trim((string) content('projects', 'approach.intro'));
section_begin('projects', 'approach', ['class' => 'section--tint projects-approach', 'labelledby' => 'projects-approach-title']); ?>
    <div class="split split--start<?= $appHasMedia ? ' split--wide-media' : ' ia-split--solo' ?>">
        <?= partial('partials/photo-stack', ['images' => $appImgs]) ?>
        <div data-reveal>
            <?= partial('partials/section-head', ['eyebrow' => (string) content('projects', 'approach.eyebrow'), 'title' => (string) content('projects', 'approach.title'), 'id' => 'projects-approach-title']) ?>
            <?php if ($appIntro !== ''): ?><p class="lead"><?= e($appIntro) ?></p><?php endif; ?>
            <?php if ($items): ?>
                <ul class="ia-checklist mt-5"><?php foreach ($items as $it): ?><li><span class="ia-checklist__tick" aria-hidden="true"><?= icon('check') ?></span><span><?= e($it['text']) ?></span></li><?php endforeach; ?></ul>
            <?php endif; ?>
            <?php if ($closing !== ''): ?><div class="ia-note mt-6"><?= nl2p($closing) ?></div><?php endif; ?>
        </div>
    </div>
<?php section_end(); ?>

<?= partial('partials/cta-donate') ?>
