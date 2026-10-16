<?php
/**
 * PROJECT detail (B1). Data: the projects row (Admin > Projects); labels: content('projects', 'detail.*').
 * Vars: $project, $program (parent program row or null), $others (up to 3 other projects), $statuses (status => label).
 */

use App\Controllers\Web\HomeSupport;

$img = img_url((string) ($project['image'] ?? ''));
$join = content_btn('projects', 'detail.join');
$back = trim((string) content('projects', 'detail.back_label'));
$cta = trim((string) content('projects', 'list.card_cta'));
$summary = trim((string) ($project['summary'] ?? ''));
$status = $statuses[$project['status'] ?? ''] ?? '';
$dates = '';
if (!empty($project['start_date'])) {
    $dates = format_date($project['start_date']);
    if (!empty($project['end_date'])) {
        $dates .= ' – ' . format_date($project['end_date']);
    }
}
$facts = array_filter([
    ['label' => (string) content('projects', 'detail.program_label'), 'value' => $program ? (string) $program['title'] : '', 'href' => $program ? url('programs/' . $program['slug']) : ''],
    ['label' => (string) content('projects', 'detail.status_label'), 'value' => $status, 'href' => ''],
    ['label' => (string) content('projects', 'detail.location_label'), 'value' => trim((string) ($project['location'] ?? '')), 'href' => ''],
    ['label' => (string) content('projects', 'detail.dates_label'), 'value' => $dates, 'href' => ''],
], static fn(array $f): bool => $f['value'] !== '');
?>
<?= partial('partials/page-hero', [
    'eyebrow' => (string) ($project['tagline'] ?? ''),
    'title'   => (string) $project['title'],
    'lead'    => $summary,
]) ?>

<section class="section projects-detail" aria-label="<?= e($project['title']) ?>">
    <div class="container">
        <div class="projects-detail__grid">
            <div class="projects-detail__main" data-reveal>
                <?php if ($img !== ''): ?>
                    <figure class="projects-detail__figure"><img src="<?= e($img) ?>" alt="<?= e((string) ($project['image_alt'] ?? '')) ?>"></figure>
                <?php endif; ?>
                <?php
                $bodyHtml = trim((string) ($project['body'] ?? '')) !== '' ? HomeSupport::answerHtml((string) $project['body']) : '';
                // the summary is already the banner's lead: do not repeat it as the first paragraph of the text
                if ($bodyHtml !== '' && $summary !== '' && preg_match('~^\s*<p>(.*?)</p>\s*~su', $bodyHtml, $first) === 1
                    && trim(html_entity_decode(strip_tags($first[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8')) === $summary) {
                    $bodyHtml = substr($bodyHtml, strlen($first[0]));
                }
                ?>
                <?php if (trim($bodyHtml) !== ''): ?>
                    <div class="prose prose--wide"><?= $bodyHtml ?></div>
                <?php endif; ?>
            </div>
            <aside class="projects-detail__aside" data-reveal>
                <?php if ($facts): ?>
                    <dl class="projects-facts">
                        <?php foreach ($facts as $f): ?>
                            <div><dt><?= e($f['label']) ?></dt><dd><?php if ($f['href'] !== ''): ?><a href="<?= e($f['href']) ?>"><?= e($f['value']) ?></a><?php else: ?><?= e($f['value']) ?><?php endif; ?></dd></div>
                        <?php endforeach; ?>
                    </dl>
                <?php endif; ?>
                <div class="stack stack--sm mt-5">
                    <?php if ($join['has']): ?><a class="btn btn--accent btn--block" href="<?= e($join['url']) ?>"><?= e($join['label']) ?> <?= icon('arrow-right', 'icon--arrow') ?></a><?php endif; ?>
                    <?php if ($back !== ''): ?><a class="btn btn--outline btn--block" href="<?= e(url('projects')) ?>"><?= icon('arrow-left') ?> <?= e($back) ?></a><?php endif; ?>
                </div>
            </aside>
        </div>
    </div>
</section>

<?php if ($others): ?>
<section class="section section--tint projects-more" aria-labelledby="projects-more-title">
    <div class="container">
        <div class="section__header section__header--center" data-reveal><h2 id="projects-more-title"><?= e(content('projects', 'detail.more_title')) ?></h2></div>
        <div class="cards-center">
            <?php foreach ($others as $other): ?><?= partial('partials/project-card', ['project' => $other, 'cta' => $cta, 'statusLabels' => $statuses]) ?><?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?= partial('partials/cta-donate') ?>
