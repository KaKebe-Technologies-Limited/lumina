<?php
/**
 * PUBLIC routes of the PDF side of the newsletter (docs/PARTNERS_NEWSLETTER.md section 5). Loaded after web_newsletter.php; the static suffixes
 * (/pdf, /download) never collide with /newsletter/{slug} or /newsletter/archive.
 * Both stream the whole file through PHP and /download bumps a counter, so they are throttled per visitor address and issue (like /reports/download):
 * 60 requests in 5 minutes is far above any human use and stops a loop from inflating the counter or tying up PHP workers.
 */

use App\Controllers\Web\NewsletterPdfController;

$router->get('/newsletter/{slug}/pdf', [NewsletterPdfController::class, 'view'])->name('newsletter.pdf')->middleware(['page.published:newsletter', 'throttle:60,300']);
$router->get('/newsletter/{slug}/download', [NewsletterPdfController::class, 'download'])->name('newsletter.download')->middleware(['page.published:newsletter', 'throttle:60,300']);
