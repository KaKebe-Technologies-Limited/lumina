<?php
/**
 * /newsletter/archive: paginated grid of published issues, newest first; on page 1 the newest issue is a large feature card.
 * Vars: $pagination (items = rows from NewsletterService::archive), $issues. All wording: content('newsletter', 'archive.*'). OWNER: WEB agent.
 */
$rest = $issues;
$feature = $rest !== [] && $pagination->page === 1 ? array_shift($rest) : null;
$pageText = $pagination->hasPages()
    ? strtr((string) content('newsletter', 'archive.page_label'), ['{page}' => (string) $pagination->page, '{pages}' => (string) $pagination->pages])
    : '';

$this->start('head');
if ($pagination->hasPrev()): ?>
<link rel="prev" href="<?= e(abs_url('newsletter/archive', $pagination->page > 2 ? ['page' => $pagination->page - 1] : [])) ?>">
<?php endif;
if ($pagination->hasNext()): ?>
<link rel="next" href="<?= e(abs_url('newsletter/archive', ['page' => $pagination->page + 1])) ?>">
<?php endif;
$this->end(); ?>
<?= partial('partials/page-hero', [
    'title'   => content('newsletter', 'archive.title'),
    'eyebrow' => content('newsletter', 'archive.eyebrow'),
    'lead'    => content('newsletter', 'archive.lead'),
]) ?>

<section class="section nl-archive">
    <div class="container">
        <?php if ($issues): ?>
            <?php if ($feature): ?>
                <?= partial('web/newsletter/_card', ['issue' => $feature, 'latest' => true, 'featured' => true]) ?>
            <?php endif; ?>
            <?php if ($rest): ?>
                <div class="grid grid--3 grid--gap-lg nl-grid post-grid post-grid--n<?= min(count($rest), 3) ?>">
                    <?php foreach ($rest as $it): ?>
                        <?= partial('web/newsletter/_card', ['issue' => $it, 'latest' => false, 'featured' => false]) ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <?= partial('partials/pagination', ['pagination' => $pagination, 'base' => 'newsletter/archive', 'summaryText' => $pageText]) ?>
        <?php else: ?>
            <?= partial('partials/empty-state', [
                'title' => content('newsletter', 'empty.archive_title'),
                'text'  => content('newsletter', 'empty.archive_text'),
                'icon'  => 'newspaper',
            ]) ?>
        <?php endif; ?>
    </div>
</section>

<?php if (section_on('newsletter', 'subscribe')): ?>
    <?= partial('web/newsletter/_subscribe') ?>
<?php endif; ?>
