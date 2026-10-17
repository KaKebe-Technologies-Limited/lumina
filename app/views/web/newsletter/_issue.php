<?php
/**
 * One newsletter issue: masthead (issue label, title, summary), cover, featured article, blocks. Used by the public pages, the admin preview and
 * the public JSON API, always through App\Services\Newsletter\NewsletterRenderer::renderIssue() (context 'preview' only adds data-nl-block hooks
 * and drops the scroll-reveal attributes). OWNER: WEB agent. Keep the vars and the data-nl-block hooks of the 'preview' context.
 *
 * Vars: $issue (row; the controller may add is_latest = true), $content (normalised), $context (public|preview), $featured (HTML, may be ''),
 * $blocksHtml, $label ("Issue No. 3 · 17 October 2026"). Shared (View::share, public page only): $breadcrumbs.
 *
 * Layout: a navy masthead in the same language as partials/page-hero (gradient, rounded white sheet cut-in), the cover photo overlapping the sheet,
 * then the featured article panel and the blocks. The page chrome (pager, share, subscribe box) is in show.php.
 */
use App\Services\Newsletter\NewsletterContent;
use App\Services\Newsletter\NewsletterRenderer as R;

$preview = $context === 'preview';
$hook = static fn(string $id): string => $preview ? ' data-nl-block="' . e($id) . '" tabindex="0"' : '';
$coverPath = (string) ($issue['cover_image'] ?? '');
$coverPath = NewsletterContent::validImagePath($coverPath) ? $coverPath : '';          // only an upload or a bundled photo, whatever the row says
$hasCover = $coverPath !== '';
$coverSize = R::imageSize($coverPath);
$summary = trim((string) ($issue['summary'] ?? ''));
$isLatest = !empty($issue['is_latest']);
$crumbs = array_values(array_filter((array) ($breadcrumbs ?? []), 'is_array'));
?>
<article class="nl-issue<?= $preview ? ' nl-issue--preview' : '' ?>" aria-label="<?= e(content('newsletter', 'issue.contents_label')) ?>" data-nl-issue>
    <header class="page-hero nl-hero<?= $hasCover ? ' nl-hero--cover' : '' ?>"<?= $hook('details') ?>>
        <div class="page-hero__inner nl-hero__inner">
            <?php if ($crumbs !== []): ?><?= partial('partials/breadcrumbs', ['breadcrumbs' => $crumbs]) ?><?php endif; ?>
            <?php if ($label !== '' || $isLatest): ?>
                <p class="nl-issue__meta">
                    <?php if ($label !== ''): ?><span class="eyebrow nl-issue__label"><?= e($label) ?></span><?php endif; ?>
                    <?php if ($isLatest): ?><span class="badge badge--gold nl-issue__badge"><?= e(content('newsletter', 'issue.latest_badge')) ?></span><?php endif; ?>
                </p>
            <?php endif; ?>
            <h1 class="nl-issue__title"><?= e($issue['title'] !== '' ? $issue['title'] : '…') ?></h1>
            <?php if ($summary !== ''): ?><p class="page-hero__lead nl-issue__summary"><?= e($summary) ?></p><?php endif; ?>
        </div>
    </header>

    <div class="nl-issue__main">
        <?php if ($hasCover): ?>
            <figure class="nl-issue__cover">
                <img src="<?= e(R::imageUrl(['path' => $coverPath], $context)) ?>" alt="<?= e((string) ($issue['cover_alt'] ?? '')) ?>"<?= $coverSize ? ' width="' . $coverSize[0] . '" height="' . $coverSize[1] . '"' : '' ?> fetchpriority="high" decoding="async">
            </figure>
        <?php endif; ?>

        <?= $featured ?>

        <?php if (trim($blocksHtml) !== ''): ?>
            <div class="nl-issue__blocks">
                <?= $blocksHtml ?>
            </div>
        <?php endif; ?>
    </div>
</article>
