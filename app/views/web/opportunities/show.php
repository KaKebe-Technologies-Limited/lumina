<?php
/** One opportunity. Vars: $o (row), $closed (bool), $more (rows), $absUrl, $share. Labels: content('opportunities', 'detail.*'). */
$this->start('scripts'); ?>
<script src="<?= e(asset('js/pages/blog.js')) ?>" defer></script>
<?php $this->end();
$img = img_url($o['image'] ?? '');
$fromFeed = ($o['source'] ?? '') === 'feed';
$by = trim((string) ($o['organisation'] ?? '')) ?: ($fromFeed ? trim((string) ($o['source_name'] ?? '')) : '');
$applyUrl = trim((string) ($o['apply_url'] ?? ''));
$hasDesc = trim((string) strip_tags((string) ($o['description'] ?? ''))) !== '';
$block = static fn(string $text): string => '<div class="prose">' . nl2p($text) . '</div>';
?>
<?= partial('partials/page-hero', [
    'title'   => $o['title'],
    'eyebrow' => \App\Models\Opportunity::categoryLabel($o['category']),
    'lead'    => (string) ($o['summary'] ?? ''),
]) ?>

<section class="section job-page opp-page" aria-label="<?= e($o['title']) ?>">
    <div class="container">
        <?php if ($closed): ?><p class="opp-notice opp-notice--closed" role="status"><?= icon('clock', 'icon--sm') ?> <?= e(content('opportunities', 'detail.closed_notice')) ?></p><?php endif; ?>
        <div class="job-page__layout">
            <div class="job-page__main" data-reveal>
                <?php if ($img !== ''): ?><figure class="post__cover"><img src="<?= e($img) ?>" alt="<?= e($o['image_alt'] ?? '') ?>" decoding="async"></figure><?php endif; ?>

                <?php if ($hasDesc): ?>
                    <h2 class="job-page__heading"><?= e(content('opportunities', 'detail.about_title')) ?></h2>
                    <div class="prose"><?= rich($o['description']) ?></div>
                <?php endif; ?>
                <?php if (trim((string) ($o['eligibility'] ?? '')) !== ''): ?>
                    <h2 class="job-page__heading"><?= e(content('opportunities', 'detail.eligibility_title')) ?></h2>
                    <?= $block((string) $o['eligibility']) ?>
                <?php endif; ?>
                <?php if (trim((string) ($o['benefits'] ?? '')) !== ''): ?>
                    <h2 class="job-page__heading"><?= e(content('opportunities', 'detail.benefits_title')) ?></h2>
                    <?= $block((string) $o['benefits']) ?>
                <?php endif; ?>
                <?php if (trim((string) ($o['how_to_apply'] ?? '')) !== ''): ?>
                    <h2 class="job-page__heading"><?= e(content('opportunities', 'detail.how_title')) ?></h2>
                    <?= $block((string) $o['how_to_apply']) ?>
                <?php endif; ?>

                <?php if (!$hasDesc && $applyUrl !== '' && trim((string) ($o['eligibility'] ?? '') . ($o['benefits'] ?? '') . ($o['how_to_apply'] ?? '')) === ''): ?>
                    <div class="opp-source-panel">
                        <p><?= e(content('opportunities', 'detail.source_panel')) ?></p>
                        <p><a class="btn btn--cta btn--caps" href="<?= e($applyUrl) ?>" target="_blank" rel="noopener noreferrer nofollow"><?= e(content('opportunities', 'detail.source_button')) ?> <?= icon('external-link', 'icon--sm') ?></a></p>
                    </div>
                <?php endif; ?>
                <?php if ($fromFeed): ?>
                    <p class="opp-note"><?= icon('info', 'icon--sm') ?> <?= e(str_replace('{source}', (string) ($o['source_name'] ?? ''), (string) content('opportunities', 'detail.source_credit'))) ?></p>
                <?php endif; ?>
                <p class="opp-note opp-note--warn"><?= icon('shield', 'icon--sm') ?> <?= e(content('opportunities', 'detail.warning')) ?></p>

                <?= partial('web/blog/_share', [
                    'share'       => $share,
                    'absUrl'      => $absUrl,
                    'heading'     => content('opportunities', 'detail.share_label'),
                    'shareOn'     => content('opportunities', 'detail.share_on'),
                    'copyLabel'   => content('opportunities', 'detail.copy_label'),
                    'copiedLabel' => content('opportunities', 'detail.copied_label'),
                ]) ?>
            </div>

            <aside class="ia-aside" aria-label="<?= e($o['title']) ?>" data-reveal>
                <dl class="ia-facts">
                    <div class="ia-facts__row"><span class="ia-facts__icon" aria-hidden="true"><?= icon(\App\Models\Opportunity::categoryIcon($o['category'])) ?></span><div><dt><?= e(content('opportunities', 'detail.type_label')) ?></dt><dd><?= e(\App\Models\Opportunity::categoryLabel($o['category'])) ?></dd></div></div>
                    <?php if ($by !== ''): ?><div class="ia-facts__row"><span class="ia-facts__icon" aria-hidden="true"><?= icon('globe') ?></span><div><dt><?= e(content('opportunities', 'detail.by_label')) ?></dt><dd><?= e($by) ?></dd></div></div><?php endif; ?>
                    <?php if (!empty($o['location'])): ?><div class="ia-facts__row"><span class="ia-facts__icon" aria-hidden="true"><?= icon('map-pin') ?></span><div><dt><?= e(content('opportunities', 'detail.where_label')) ?></dt><dd><?= e($o['location']) ?></dd></div></div><?php endif; ?>
                    <div class="ia-facts__row"><span class="ia-facts__icon" aria-hidden="true"><?= icon('calendar') ?></span><div><dt><?= e(content('opportunities', 'detail.deadline_label')) ?></dt>
                        <dd><?php if (!empty($o['deadline'])): ?><time datetime="<?= e(format_date($o['deadline'], 'Y-m-d')) ?>"><?= e(format_date($o['deadline'])) ?></time><?php else: ?><?= e(content('opportunities', 'list.rolling')) ?><?php endif; ?></dd></div></div>
                </dl>
                <?php if ($applyUrl !== ''): ?>
                    <p><a class="btn btn--cta btn--caps btn--block" href="<?= e($applyUrl) ?>" target="_blank" rel="noopener noreferrer<?= $fromFeed ? ' nofollow' : '' ?>">
                        <?= e($fromFeed && $applyUrl === (string) ($o['source_url'] ?? '') ? content('opportunities', 'detail.source_button') : content('opportunities', 'detail.apply_button')) ?> <?= icon('external-link', 'icon--sm') ?></a></p>
                <?php endif; ?>
                <?php if (!empty($o['file_path'])): ?>
                    <p><a class="btn btn--outline btn--caps btn--block" href="<?= e(url('opportunities/' . $o['slug'] . '/file')) ?>"><?= icon('download', 'icon--sm') ?> <?= e(content('opportunities', 'detail.file_button')) ?></a></p>
                <?php endif; ?>
                <p><a class="btn btn--ghost btn--caps btn--block" href="<?= e(url('opportunities')) ?>"><?= icon('arrow-left', 'icon--sm') ?> <?= e(content('opportunities', 'detail.back')) ?></a></p>
            </aside>
        </div>
    </div>
</section>

<?php if ($more): ?>
    <section class="section section--tint" aria-labelledby="more-title">
        <div class="container">
            <?= partial('partials/section-head', ['title' => content('opportunities', 'detail.more_title'), 'id' => 'more-title', 'align' => 'center']) ?>
            <div class="grid grid--3 grid--gap-lg post-grid post-grid--n<?= min(count($more), 3) ?>">
                <?php foreach ($more as $m): ?><?= partial('web/opportunities/_card', ['o' => $m]) ?><?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>
