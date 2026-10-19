<?php
/**
 * One newsletter issue: masthead (issue label, title, summary), cover, featured article, blocks - or, for a PDF issue, the PDF panel (download
 * button, "read in a new tab", embedded viewer). Used by the public pages, the admin preview and the public JSON API, always through
 * App\Services\Newsletter\NewsletterRenderer::renderIssue() (context 'preview' only adds data-nl-block hooks, drops the scroll-reveal attributes and
 * points the PDF buttons at the admin stream because a draft has no public address). OWNER: WEB / NEWSLETTER agent. Keep the vars and the
 * data-nl-block hooks of the 'preview' context.
 *
 * Vars: $issue (row; the controller may add is_latest = true; the admin live preview adds pdf_embed = false because its frame is sandboxed and
 * a sandboxed document cannot show a PDF), $content (normalised), $context (public|preview), $featured (HTML, may be ''), $blocksHtml,
 * $label ("Issue No. 3 · 17 October 2026"). Shared (View::share, public page only): $breadcrumbs.
 *
 * Layout: a navy masthead in the same language as partials/page-hero (gradient, rounded white sheet cut-in), the cover photo overlapping the sheet,
 * then the featured article panel and the blocks. A PDF issue has no overlapping photo: its cover sits in the PDF panel as a document thumbnail.
 * Mobile first: the buttons come BEFORE the viewer; the viewer itself only appears from 768 px (most phone browsers cannot embed a PDF) and when the
 * browser says it can show one (assets/js/newsletter.js). The wording is all editable: content('newsletter', 'pdf.*').
 */
use App\Services\Newsletter\NewsletterContent;
use App\Services\Newsletter\NewsletterPdf;
use App\Services\Newsletter\NewsletterRenderer as R;

$preview = $context === 'preview';
$hook = static fn(string $id): string => $preview ? ' data-nl-block="' . e($id) . '" tabindex="0"' : '';
$isPdfIssue = (string) ($issue['kind'] ?? 'online') === 'pdf';

// ---- the PDF (own document, or the optional attachment of a written issue) -------------------------------------------------------------
$pdf = NewsletterPdf::info($issue);
$pdfReady = $pdf['has'] && $pdf['exists'];                        // a row whose file is gone shows no PDF controls (the admin sees a warning)
$adminPdf = $preview && (int) ($issue['id'] ?? 0) > 0 ? url('admin/newsletters/' . (int) $issue['id'] . '/pdf') : '';
$viewUrl = $preview ? $adminPdf : $pdf['view_url'];
$downloadUrl = $preview ? $adminPdf : $pdf['download_url'];
$embed = $pdfReady && $viewUrl !== '' && ($issue['pdf_embed'] ?? true) !== false;
$sizeLabel = $pdfReady ? trim(strtr((string) content('newsletter', 'pdf.size_label'), ['{size}' => $pdf['size_human']])) : '';
// the owner's wording is used exactly as written: the "one download" line is its own editable field (empty = use the general line)
$countText = $pdf['downloads'] === 1 && trim((string) content('newsletter', 'pdf.downloads_label_one')) !== ''
    ? (string) content('newsletter', 'pdf.downloads_label_one') : (string) content('newsletter', 'pdf.downloads_label');
$countLabel = $pdfReady && content('newsletter', 'pdf.show_downloads') && $pdf['downloads'] > 0
    ? trim(strtr($countText, ['{count}' => number_format($pdf['downloads'])])) : '';
$downloadLabel = (string) content('newsletter', 'pdf.download_label');
$openLabel = (string) content('newsletter', 'pdf.open_label');
$absDownload = $pdfReady ? NewsletterPdf::urls($issue, true)['download'] : '';

// ---- masthead ---------------------------------------------------------------------------------------------------------------------------------
$coverPath = (string) ($issue['cover_image'] ?? '');
$coverPath = NewsletterContent::validImagePath($coverPath) ? $coverPath : '';          // only an upload or a bundled photo, whatever the row says
$hasCover = $coverPath !== '' && !$isPdfIssue;                                         // a PDF issue shows its cover inside the PDF panel
$coverSize = R::imageSize($coverPath);
$summary = trim((string) ($issue['summary'] ?? ''));
$summaryLabel = $isPdfIssue ? trim((string) content('newsletter', 'pdf.summary_label')) : '';
$isLatest = !empty($issue['is_latest']);
$crumbs = array_values(array_filter((array) ($breadcrumbs ?? []), 'is_array'));
?>
<article class="nl-issue<?= $preview ? ' nl-issue--preview' : '' ?><?= $isPdfIssue ? ' nl-issue--pdf' : '' ?>" aria-label="<?= e(content('newsletter', 'issue.contents_label')) ?>" data-nl-issue>
    <header class="page-hero nl-hero<?= $hasCover ? ' nl-hero--cover' : '' ?>"<?= $hook('details') ?>>
        <div class="page-hero__inner nl-hero__inner">
            <?php if ($crumbs !== []): ?><?= partial('partials/breadcrumbs', ['breadcrumbs' => $crumbs]) ?><?php endif; ?>
            <?php if ($label !== '' || $isLatest || $isPdfIssue): ?>
                <p class="nl-issue__meta">
                    <?php if ($label !== ''): ?><span class="eyebrow nl-issue__label"><?= e($label) ?></span><?php endif; ?>
                    <?php if ($isLatest): ?><span class="badge badge--gold nl-issue__badge"><?= e(content('newsletter', 'issue.latest_badge')) ?></span><?php endif; ?>
                    <?php if ($isPdfIssue && trim((string) content('newsletter', 'pdf.badge')) !== ''): ?><span class="badge badge--gold nl-issue__badge nl-issue__badge--pdf"><?= e(content('newsletter', 'pdf.badge')) ?></span><?php endif; ?>
                </p>
            <?php endif; ?>
            <h1 class="nl-issue__title"><?= e($issue['title'] !== '' ? $issue['title'] : '…') ?></h1>
            <?php if ($summaryLabel !== '' && $summary !== ''): ?><p class="nl-issue__summary-label"><?= e($summaryLabel) ?></p><?php endif; ?>
            <?php if ($summary !== ''): ?><p class="page-hero__lead nl-issue__summary"><?= e($summary) ?></p><?php endif; ?>
            <?php if (!$isPdfIssue && $pdfReady && $downloadUrl !== ''): ?>
                <?php /* a written issue with a PDF attached: one issue, two ways to read */ ?>
                <p class="nl-issue__pdf">
                    <a class="btn btn--outline-light btn--sm" href="<?= e($downloadUrl) ?>"<?= $preview ? ' target="_blank" rel="noopener"' : '' ?>><?= icon('download', 'icon--sm') ?> <?= e(content('newsletter', 'pdf.attached_label')) ?></a>
                    <?php if ($sizeLabel !== ''): ?><span class="nl-issue__pdf-size"><?= e($sizeLabel) ?></span><?php endif; ?>
                </p>
            <?php endif; ?>
        </div>
    </header>

    <div class="nl-issue__main">
        <?php if ($hasCover): ?>
            <figure class="nl-issue__cover">
                <img src="<?= e(R::imageUrl(['path' => $coverPath], $context)) ?>" alt="<?= e((string) ($issue['cover_alt'] ?? '')) ?>"<?= $coverSize ? ' width="' . $coverSize[0] . '" height="' . $coverSize[1] . '"' : '' ?> fetchpriority="high" decoding="async">
            </figure>
        <?php endif; ?>

        <?php if ($isPdfIssue && ($pdfReady || $preview)): ?>
            <section class="nl-pdf" aria-label="<?= e(strtr((string) content('newsletter', 'pdf.viewer_title'), ['{title}' => (string) $issue['title']])) ?>" data-nl-pdf<?= $hook('pdf') ?>>
                <?php if ($pdfReady): ?>
                    <div class="nl-pdf__bar">
                        <div class="nl-pdf__doc">
                            <span class="nl-pdf__cover"><?= partial('web/newsletter/_cover', ['issue' => $issue, 'loading' => 'eager', 'caption' => false]) ?></span>
                            <span class="nl-pdf__facts">
                                <?php if ($sizeLabel !== ''): ?><span class="nl-pdf__size"><?= icon('file-text', 'icon--sm') ?> <?= e($sizeLabel) ?></span><?php endif; ?>
                                <?php if ($countLabel !== ''): ?><span class="nl-pdf__count"><?= e($countLabel) ?></span><?php endif; ?>
                            </span>
                        </div>
                        <div class="nl-pdf__actions">
                            <a class="btn btn--cta btn--caps nl-pdf__download" href="<?= e($downloadUrl) ?>"<?= $preview ? ' target="_blank" rel="noopener"' : '' ?> data-nl-pdf-download><?= icon('download', 'icon--sm') ?> <?= e($downloadLabel) ?></a>
                            <a class="btn btn--outline btn--caps nl-pdf__open" href="<?= e($viewUrl) ?>" target="_blank" rel="noopener"><?= icon('external-link', 'icon--sm') ?> <?= e($openLabel) ?></a>
                        </div>
                    </div>
                    <p class="nl-pdf__fallback" data-nl-pdf-fallback><?= e(content('newsletter', 'pdf.fallback')) ?></p>
                    <?php if ($embed): ?>
                        <div class="nl-pdf__viewer" data-nl-pdf-viewer>
                            <iframe class="nl-pdf__frame" src="<?= e($viewUrl) ?>#view=FitH&amp;navpanes=0" title="<?= e(strtr((string) content('newsletter', 'pdf.viewer_title'), ['{title}' => (string) $issue['title']])) ?>" loading="lazy"><?= e(content('newsletter', 'pdf.fallback')) ?></iframe>
                        </div>
                    <?php elseif ($preview): ?>
                        <?php /* the editor's live preview frame is sandboxed: a PDF cannot be shown there, the full-page preview shows it */ ?>
                        <div class="nl-pdf__viewer nl-pdf__viewer--placeholder" data-nl-pdf-viewer><p>The PDF viewer appears here on the website. Use “Preview in new tab” to see it.</p></div>
                    <?php endif; ?>
                    <?php /* print: the viewer is hidden, so the page prints the address of the PDF instead */ ?>
                    <p class="nl-pdf__print-url"><?= e($absDownload) ?></p>
                <?php elseif ($preview): ?>
                    <p class="nl-pdf__missing">This issue has no PDF yet. Upload one in the editor; visitors will see the download button and the viewer here.</p>
                <?php endif; ?>
            </section>
        <?php endif; ?>

        <?= $featured ?>

        <?php if (trim($blocksHtml) !== ''): ?>
            <div class="nl-issue__blocks">
                <?= $blocksHtml ?>
            </div>
        <?php endif; ?>
    </div>
</article>
