<?php
/**
 * BLOG PAGE > "Newsletter call to action". OWNER: NEWSLETTER agent (docs/PARTNERS_NEWSLETTER.md section 8). Included by web/blog/index.php just above
 * the subscribe box. No vars. Renders NOTHING while no issue is public (NewsletterService::countPublished() = 0) or when the Newsletter page is
 * switched off (every issue would answer 404); the owner can hide the section (Admin > Pages > Blog > "Newsletter call to action").
 * All wording and both links: content('blog', 'newsletter.*'). Nothing is cached: the count is read on every request.
 */
use App\Core\Log;
use App\Services\Newsletter\NewsletterService;

$nlShow = false;
try {
    $nlShow = NewsletterService::pageVisible() && NewsletterService::countPublished() > 0;
} catch (\Throwable $e) {
    Log::warning('Blog newsletter call to action skipped - ' . $e->getMessage());
}
if ($nlShow):
    $nlMain = content_btn('blog', 'newsletter.button');
    $nlMore = content_btn('blog', 'newsletter.archive');
    $nlEyebrow = trim((string) content('blog', 'newsletter.eyebrow'));
    $nlText = trim((string) content('blog', 'newsletter.text'));
    section_begin('blog', 'newsletter', ['class' => 'blog-newsletter', 'id' => 'newsletter', 'labelledby' => 'blog-newsletter-title']); ?>
    <div class="nl-cta" data-reveal>
        <span class="nl-cta__icon" aria-hidden="true"><?= icon('newspaper', 'icon--lg') ?></span>
        <div class="nl-cta__copy">
            <?php if ($nlEyebrow !== ''): ?><span class="eyebrow"><?= e($nlEyebrow) ?></span><?php endif; ?>
            <h2 class="nl-cta__title" id="blog-newsletter-title"><?= highlight((string) content('blog', 'newsletter.title')) ?></h2>
            <?php if ($nlText !== ''): ?><p class="nl-cta__text"><?= e($nlText) ?></p><?php endif; ?>
        </div>
        <p class="nl-cta__actions">
            <?php if ($nlMain['has']): ?><a class="btn btn--cta btn--caps" href="<?= e($nlMain['url']) ?>"><?= e($nlMain['label']) ?> <?= icon('arrow-right', 'icon--sm') ?></a><?php endif; ?>
            <?php if ($nlMore['has']): ?><a class="link-arrow" href="<?= e($nlMore['url']) ?>"><?= e($nlMore['label']) ?> <?= icon('arrow-right', 'icon--arrow') ?></a><?php endif; ?>
        </p>
    </div>
    <?php section_end();
endif;
