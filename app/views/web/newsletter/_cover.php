<?php
/**
 * The picture of an issue: its cover image, or a branded fallback tile when it has none (navy tile, gold ring, the issue number or a document
 * icon, and the editable caption content('newsletter', 'pdf.card_fallback')). The ONE place that decides this, so the archive card, the Home
 * teaser and the PDF panel always look the same. OWNER: NEWSLETTER agent.
 * Vars: $issue (row: cover_image, issue_number, kind), $loading ('lazy' | 'eager'), $caption (bool: print the caption on the fallback tile),
 * $alt (alt text of a real cover; default the cover's own description). A fallback tile is decorative (aria-hidden).
 */
use App\Services\Newsletter\NewsletterContent;
use App\Services\Newsletter\NewsletterRenderer as R;

$cover = (string) ($issue['cover_image'] ?? '');
$cover = NewsletterContent::validImagePath($cover) ? $cover : '';
$loading = ($loading ?? 'lazy') === 'eager' ? 'eager' : 'lazy';
$alt = isset($alt) ? (string) $alt : (string) ($issue['cover_alt'] ?? '');
if ($cover !== ''):
    $size = R::imageSize($cover); ?>
<img class="nl-cover-img" src="<?= e(img_url($cover)) ?>" alt="<?= e($alt) ?>"<?= $size ? ' width="' . $size[0] . '" height="' . $size[1] . '"' : '' ?> loading="<?= $loading ?>" decoding="async">
<?php else:
    $number = (int) ($issue['issue_number'] ?? 0);
    $isPdf = (string) ($issue['kind'] ?? 'online') === 'pdf';
    $text = trim((string) content('newsletter', 'pdf.card_fallback')); ?>
<span class="nl-cover-ph<?= $isPdf ? ' nl-cover-ph--pdf' : '' ?>" aria-hidden="true">
    <?php if ($number > 0): ?><span class="nl-cover-ph__num"><?= $number ?></span>
    <?php else: ?><span class="nl-cover-ph__icon"><?= icon($isPdf ? 'file-text' : 'newspaper', 'icon--xl') ?></span><?php endif; ?>
    <?php if (!empty($caption) && $text !== ''): ?><span class="nl-cover-ph__text"><?= e($text) ?></span><?php endif; ?>
</span>
<?php endif; ?>
