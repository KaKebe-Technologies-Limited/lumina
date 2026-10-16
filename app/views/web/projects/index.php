<?php
/**
 * PROJECTS list (B1). Copy: content('projects', ...). Vars: $projects (rows of Admin > Projects), $pagination, $statuses (status => editable label).
 */

$heroImg = content_img('projects', 'hero.image');
$donateBtn = content_btn('projects', 'hero.cta');
$donateHref = trim((string) content('projects', 'hero.cta_url')) !== '' ? $donateBtn['url'] : link_url((string) setting('donate_url', ''));
$actions = [];
if (trim((string) content('projects', 'hero.cta_label')) !== '' && $donateHref !== '') {
    $actions[] = ['label' => (string) content('projects', 'hero.cta_label'), 'url' => $donateHref, 'class' => 'btn--accent'];
}
$cta = trim((string) content('projects', 'list.card_cta'));
$emptyBtn = content_btn('projects', 'list.empty_cta');
?>
<?= partial('partials/page-hero', [
    'eyebrow' => (string) content('projects', 'hero.eyebrow'),
    'title'   => (string) content('projects', 'hero.title'),
    'lead'    => (string) content('projects', 'hero.lead'),
    'image'   => $heroImg['has'] ? ['src' => $heroImg['src'], 'alt' => $heroImg['alt']] : null,
    'actions' => $actions,
]) ?>

<section class="section projects-list" id="projects-list" aria-label="<?= e(content('projects', 'hero.title')) ?>">
    <div class="container">
        <?php if ($projects): ?>
            <?php if (trim((string) content('projects', 'list.lead')) !== ''): ?>
                <p class="lead projects-list__lead text-center mb-6" data-reveal><?= e(content('projects', 'list.lead')) ?></p>
            <?php endif; ?>
            <div class="projects-list__rows">
                <?php foreach ($projects as $i => $project): $img = img_url((string) ($project['image'] ?? '')); $href = url('projects/' . $project['slug']); ?>
                    <article class="project-row<?= $i % 2 === 1 ? ' project-row--reverse' : '' ?>" id="<?= e($project['slug']) ?>" data-reveal>
                        <div class="project-row__media">
                            <?php if ($img !== ''): ?>
                                <a href="<?= e($href) ?>" tabindex="-1" aria-hidden="true"><img src="<?= e($img) ?>" alt="" loading="lazy"></a>
                            <?php else: ?>
                                <span class="project-row__num" aria-hidden="true"><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="project-row__body">
                            <div class="project-row__meta">
                                <?php if (trim((string) ($project['tagline'] ?? '')) !== ''): ?><span class="eyebrow"><?= e($project['tagline']) ?></span><?php endif; ?>
                                <?php if (!empty($statuses[$project['status'] ?? ''])): ?>
                                    <span class="badge badge--<?= ['planned' => 'info', 'ongoing' => 'success', 'completed' => 'neutral'][$project['status']] ?? 'neutral' ?>"><?= e($statuses[$project['status']]) ?></span>
                                <?php endif; ?>
                            </div>
                            <h2 class="h3 project-row__title"><a href="<?= e($href) ?>"><?= e($project['title']) ?></a></h2>
                            <?php if (trim((string) ($project['summary'] ?? '')) !== ''): ?><p class="project-row__text"><?= e($project['summary']) ?></p><?php endif; ?>
                            <?php if (trim((string) ($project['location'] ?? '')) !== ''): ?><p class="project-row__where"><?= icon('map-pin', 'icon--sm') ?> <?= e($project['location']) ?></p><?php endif; ?>
                            <?php if ($cta !== ''): ?><p class="mt-4"><a class="link-arrow" href="<?= e($href) ?>"><?= e($cta) ?> <?= icon('arrow-right', 'icon--arrow') ?></a></p><?php endif; ?>
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
<?php section_begin('projects', 'approach', ['class' => 'section--tint projects-approach', 'labelledby' => 'projects-approach-title']); $img = content_img('projects', 'approach.image'); $items = array_values(array_filter(content_list('projects', 'approach.items'), static fn(array $r): bool => trim((string) ($r['text'] ?? '')) !== '')); $closing = trim((string) content('projects', 'approach.closing')); ?>
    <div class="split">
        <div data-reveal>
            <h2 id="projects-approach-title"><?= e(content('projects', 'approach.title')) ?></h2>
            <?php if (trim((string) content('projects', 'approach.intro')) !== ''): ?><p class="lead mt-4"><?= e(content('projects', 'approach.intro')) ?></p><?php endif; ?>
            <?php if ($items): ?>
                <ul class="checklist mt-5"><?php foreach ($items as $it): ?><li><?= e($it['text']) ?></li><?php endforeach; ?></ul>
            <?php endif; ?>
            <?php if ($closing !== ''): ?><div class="callout mt-6"><?= nl2p($closing) ?></div><?php endif; ?>
        </div>
        <?php if ($img['has']): ?><div class="split__media home-photo" data-reveal><img src="<?= e($img['src']) ?>" alt="<?= e($img['alt']) ?>" loading="lazy"></div><?php endif; ?>
    </div>
<?php section_end(); ?>

<?= partial('partials/cta-donate') ?>
