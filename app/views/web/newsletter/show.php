<?php
/**
 * A newsletter issue page (used by /newsletter and /newsletter/{slug}, and by the admin "Preview in new tab").
 * Vars: $issue (row), $issueHtml (NewsletterRenderer::renderIssue - the only place the issue itself is rendered), $seo, $adjacent (prev = older,
 * next = newer, either may be null), $isCurrent (this is the newest public issue), $preview, $banner, $absUrl, $share (ShareLinks),
 * $ld (NewsArticle JSON-LD). All wording: content('newsletter', ...). OWNER: WEB agent.
 */
use App\Services\Newsletter\NewsletterContent;

$prev = $adjacent['prev'];
$next = $adjacent['next'];
$pagerLabel = trim(content('newsletter', 'pager.prev') . ' / ' . content('newsletter', 'pager.next'));
$thumb = static function (?array $it): string {
    $cover = (string) ($it['cover_image'] ?? '');
    return $cover === '' || !NewsletterContent::validImagePath($cover) ? '' : '<img class="nl-pager__thumb" src="' . e(img_url($cover)) . '" alt="" width="72" height="72" loading="lazy" decoding="async">';
};
$published = to_datetime($seo['published'])?->format(DATE_ATOM);
$modified = to_datetime($seo['modified'])?->format(DATE_ATOM);

$this->start('head'); ?>
<?php if ($ld): ?><script type="application/ld+json"><?= json_encode($ld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?></script><?php endif; ?>
<?php if (!$preview && $published): ?><meta property="article:published_time" content="<?= e($published) ?>"><?php endif; ?>
<?php if (!$preview && $modified): ?><meta property="article:modified_time" content="<?= e($modified) ?>"><?php endif; ?>
<?php $this->end(); $this->start('scripts'); ?>
<script src="<?= e(asset('js/newsletter.js')) ?>" defer></script>
<script src="<?= e(asset('js/pages/blog.js')) ?>" defer></script>
<?php $this->end(); ?>

<?php if ($preview && $banner !== ''): ?>
    <p class="nl-preview-banner" role="note"><?= e($banner) ?></p>
<?php endif; ?>

<?= $issueHtml ?>

<?php if (!$preview): ?>
    <div class="nl-after section">
        <div class="container">
            <div class="nl-after__inner">
                <?php if ($prev || $next): ?>
                    <nav class="nl-pager" aria-label="<?= e($pagerLabel) ?>">
                        <?php if ($prev): ?>
                            <a class="nl-pager__link nl-pager__link--prev" href="<?= e(url('newsletter/' . $prev['slug'])) ?>" rel="prev">
                                <?= $thumb($prev) ?>
                                <span class="nl-pager__text">
                                    <span class="nl-pager__label"><?= icon('arrow-left', 'icon--sm') ?> <?= e(content('newsletter', 'pager.prev')) ?></span>
                                    <span class="nl-pager__title"><?= e($prev['title']) ?></span>
                                </span>
                            </a>
                        <?php endif; ?>
                        <?php if ($next): ?>
                            <a class="nl-pager__link nl-pager__link--next" href="<?= e(url('newsletter/' . $next['slug'])) ?>" rel="next">
                                <span class="nl-pager__text">
                                    <span class="nl-pager__label"><?= e(content('newsletter', 'pager.next')) ?> <?= icon('arrow-right', 'icon--sm') ?></span>
                                    <span class="nl-pager__title"><?= e($next['title']) ?></span>
                                </span>
                                <?= $thumb($next) ?>
                            </a>
                        <?php endif; ?>
                    </nav>
                <?php endif; ?>

                <p class="nl-links">
                    <a class="link-arrow" href="<?= e(url('newsletter/archive')) ?>"><?= e(content('newsletter', 'pager.archive_link')) ?> <?= icon('arrow-right', 'icon--sm') ?></a>
                    <?php if (!$isCurrent): ?>
                        <a class="link-arrow" href="<?= e(url('newsletter')) ?>"><?= e(content('newsletter', 'pager.latest_link')) ?> <?= icon('arrow-right', 'icon--sm') ?></a>
                    <?php endif; ?>
                </p>

                <?php if (section_on('newsletter', 'share')): ?>
                    <?= partial('web/blog/_share', [
                        'share'       => $share,
                        'absUrl'      => $absUrl,
                        'heading'     => content('newsletter', 'share.title'),
                        'shareOn'     => content('newsletter', 'share.on'),
                        'copyLabel'   => content('newsletter', 'share.copy'),
                        'copiedLabel' => content('newsletter', 'share.copied'),
                    ]) ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php /* printed copies carry the address of the issue (screen: hidden) */ ?>
    <p class="nl-print-source"><?= e($absUrl) ?></p>

    <?php if (section_on('newsletter', 'subscribe')): ?>
        <?= partial('web/newsletter/_subscribe') ?>
    <?php endif; ?>
<?php endif; ?>
