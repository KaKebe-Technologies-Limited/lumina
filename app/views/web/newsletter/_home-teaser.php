<?php
/**
 * HOME PAGE > "Latest newsletter" teaser. OWNER: NEWSLETTER agent (docs/PARTNERS_NEWSLETTER.md section 8). Included by web/home/index.php between
 * the Impact stories and the FAQ. No vars.
 *
 * The card is filled in by itself from the newest PUBLIC issue (NewsletterService::visibleSql: published, or scheduled and due): title, number and
 * date, summary, cover (or the branded fallback tile), a "PDF" badge for PDF issues and a button to read it. It renders NOTHING when no issue is
 * public or when the Newsletter page is switched off (every issue would answer 404), and the owner can hide the section (Admin > Pages > Home >
 * "8b. Latest newsletter"). Nothing is cached: the row is read on every request, so publish / unpublish / edit show at once.
 * All wording: content('home', 'newsletter.*') and content('newsletter', 'pdf.*').
 */
use App\Core\Log;
use App\Services\Newsletter\NewsletterRenderer as R;
use App\Services\Newsletter\NewsletterService;

$nlIssue = null;
try {
    if (NewsletterService::pageVisible()) {
        $nlIssue = NewsletterService::archive(1, 1)->items[0] ?? null;          // list columns only: the heavy `content` text is not needed here
    }
} catch (\Throwable $e) {
    Log::warning('Home newsletter teaser skipped - ' . $e->getMessage());       // never let the newsletter break the home page
    $nlIssue = null;
}
if ($nlIssue !== null):
    $nlHref = NewsletterService::url($nlIssue);
    $nlLabel = R::issueLabel($nlIssue);
    $nlPdf = (string) ($nlIssue['kind'] ?? 'online') === 'pdf';
    $nlPdfBadge = $nlPdf ? trim((string) content('newsletter', 'pdf.badge')) : '';
    $nlBadge = trim((string) content('home', 'newsletter.badge'));
    $nlSummary = trim((string) ($nlIssue['summary'] ?? ''));
    $nlCta = trim((string) content('home', 'newsletter.cta_label'));
    $nlArchive = content_btn('home', 'newsletter.archive_cta');
    section_begin('home', 'newsletter', ['class' => 'home-newsletter', 'labelledby' => 'home-newsletter-title']);
    echo partial('partials/section-head', [
        'eyebrow' => content('home', 'newsletter.eyebrow'),
        'title'   => content('home', 'newsletter.title'),
        'lead'    => content('home', 'newsletter.lead'),
        'id'      => 'home-newsletter-title',
        'align'   => 'center',
    ]); ?>
    <article class="nl-teaser<?= $nlPdf ? ' nl-teaser--pdf' : '' ?>" data-reveal data-nl-teaser="<?= (int) $nlIssue['id'] ?>">
        <a class="nl-teaser__media<?= trim((string) ($nlIssue['cover_image'] ?? '')) === '' ? ' nl-teaser__media--ph' : '' ?>" href="<?= e($nlHref) ?>" tabindex="-1" aria-hidden="true">
            <?= partial('web/newsletter/_cover', ['issue' => $nlIssue, 'loading' => 'lazy', 'caption' => true, 'alt' => '']) ?>
        </a>
        <div class="nl-teaser__body">
            <p class="nl-teaser__meta">
                <?php if ($nlBadge !== ''): ?><span class="badge badge--gold"><?= e($nlBadge) ?></span><?php endif; ?>
                <?php if ($nlPdfBadge !== ''): ?><span class="badge badge--outline"><?= icon('file-text', 'icon--sm') ?> <?= e($nlPdfBadge) ?></span><?php endif; ?>
                <?php if ($nlLabel !== ''): ?><time datetime="<?= e(format_date($nlIssue['issue_date'], 'Y-m-d')) ?>"><?= e($nlLabel) ?></time><?php endif; ?>
            </p>
            <h3 class="nl-teaser__title"><a href="<?= e($nlHref) ?>"><?= e($nlIssue['title']) ?></a></h3>
            <?php if ($nlSummary !== ''): ?><p class="nl-teaser__text"><?= e(excerpt($nlSummary, 260)) ?></p><?php endif; ?>
            <p class="nl-teaser__actions">
                <?php if ($nlCta !== ''): ?><a class="btn btn--cta btn--caps" href="<?= e($nlHref) ?>"><?= e($nlCta) ?><span class="visually-hidden">: <?= e($nlIssue['title']) ?></span> <?= icon('arrow-right', 'icon--sm') ?></a><?php endif; ?>
                <?php if ($nlArchive['has']): ?><a class="link-arrow" href="<?= e($nlArchive['url']) ?>"><?= e($nlArchive['label']) ?> <?= icon('arrow-right', 'icon--arrow') ?></a><?php endif; ?>
            </p>
        </div>
    </article>
    <?php section_end();
endif;
