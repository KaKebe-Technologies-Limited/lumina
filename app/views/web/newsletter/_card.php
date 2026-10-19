<?php
/**
 * One issue in the archive grid (same card language as the blog: web/blog/_card.php). OWNER: WEB / NEWSLETTER agent.
 * Vars: $issue (list row: slug, kind, issue_number, title, summary, issue_date, cover_image, cover_alt), $latest (bool: the newest issue),
 * $featured (bool: the large horizontal card used for the newest issue on page 1).
 * A PDF issue carries the PDF badge (content('newsletter', 'pdf.badge')). An issue without a cover gets the branded tile of _cover.php (its number or
 * a document icon, plus the editable caption for PDF issues) - decorative, it never pretends to be a photograph.
 */
use App\Services\Newsletter\NewsletterContent;
use App\Services\Newsletter\NewsletterRenderer as R;

$featured = !empty($featured);
$href = url('newsletter/' . $issue['slug']);
$cover = (string) ($issue['cover_image'] ?? '');
$cover = NewsletterContent::validImagePath($cover) ? $cover : '';
$label = R::issueLabel($issue);
$isPdf = (string) ($issue['kind'] ?? 'online') === 'pdf';
$pdfBadge = $isPdf ? trim((string) content('newsletter', 'pdf.badge')) : '';
$summary = trim((string) ($issue['summary'] ?? ''));
$tag = 'h2';          // the page title is the h1 in the hero; every card is a section of the archive
?>
<article class="card post-card nl-card<?= $featured ? ' post-card--feature nl-card--feature' : '' ?><?= $isPdf ? ' nl-card--pdf' : '' ?>" data-reveal>
    <a class="card__media nl-card__media<?= $cover === '' ? ' card__media--ph nl-card__media--ph' : '' ?>" href="<?= e($href) ?>" tabindex="-1" aria-hidden="true">
        <?= partial('web/newsletter/_cover', ['issue' => $issue, 'loading' => 'lazy', 'caption' => $isPdf, 'alt' => '']) ?>
        <?php if ($pdfBadge !== ''): ?><span class="nl-card__pdf-badge"><?= e($pdfBadge) ?></span><?php endif; ?>
    </a>
    <div class="card__body">
        <p class="card__meta nl-card__meta">
            <?php if ($label !== ''): ?><time datetime="<?= e(format_date($issue['issue_date'], 'Y-m-d')) ?>"><?= e($label) ?></time><?php endif; ?>
            <?php if (!empty($latest)): ?><span class="badge badge--gold"><?= e(content('newsletter', 'issue.latest_badge')) ?></span><?php endif; ?>
            <?php if ($pdfBadge !== ''): ?><span class="badge badge--outline nl-card__format"><?= e($pdfBadge) ?></span><?php endif; ?>
        </p>
        <<?= $tag ?> class="card__title nl-card__title"><a href="<?= e($href) ?>"><?= e($issue['title']) ?></a></<?= $tag ?>>
        <?php if ($summary !== ''): ?><p class="card__text nl-card__text"><?= e(excerpt($summary, $featured ? 320 : 180)) ?></p><?php endif; ?>
        <p class="card__footer">
            <?php if ($featured): ?>
                <a class="btn btn--cta btn--caps btn--sm" href="<?= e($href) ?>"><?= e(content('newsletter', 'archive.read_label')) ?><span class="visually-hidden">: <?= e($issue['title']) ?></span> <?= icon('arrow-right', 'icon--sm') ?></a>
            <?php else: ?>
                <a class="link-arrow" href="<?= e($href) ?>"><?= e(content('newsletter', 'archive.read_label')) ?><span class="visually-hidden">: <?= e($issue['title']) ?></span> <?= icon('arrow-right', 'icon--arrow') ?></a>
            <?php endif; ?>
        </p>
    </div>
</article>
