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
$cover = NewsletterContent::imageExists($cover) ? $cover : '';          // a path whose file is not on the server (a lost upload) must not print a broken picture
$loading = ($loading ?? 'lazy') === 'eager' ? 'eager' : 'lazy';
$alt = isset($alt) ? (string) $alt : (string) ($issue['cover_alt'] ?? '');
if ($cover !== ''):
    $size = R::imageSize($cover); ?>
<img class="nl-cover-img" src="<?= e(img_url($cover)) ?>" alt="<?= e($alt) ?>"<?= $size ? ' width="' . $size[0] . '" height="' . $size[1] . '"' : '' ?> loading="<?= $loading ?>" decoding="async">
<?php else:
    // no cover (or its file is missing): a magazine-style cover, a Gulu photo behind the title and the issue, so the card is never an empty box
    $isPdf = (string) ($issue['kind'] ?? 'online') === 'pdf';
    $photos = ['assets/img/site/mentorship/lacekocot-0270.jpg', 'assets/img/site/mentorship/lacekocot-0409.jpg', 'assets/img/site/mentorship/lacekocot-0601.jpg', 'assets/img/site/mentorship/lacekocot-0343.jpg'];
    $photo = $photos[abs((int) ($issue['id'] ?? 0)) % count($photos)];
    $photo = is_file(BASE_PATH . '/' . $photo) ? $photo : '';
    $label = R::issueLabel($issue); ?>
<span class="nl-cover-mag<?= $isPdf ? ' nl-cover-mag--pdf' : '' ?>" aria-hidden="true">
    <?php if ($photo !== ''): ?><img class="nl-cover-mag__bg" src="<?= e(asset(substr($photo, strlen('assets/')))) ?>" alt="" loading="<?= $loading ?>" decoding="async"><?php endif; ?>
    <span class="nl-cover-mag__veil"></span>
    <span class="nl-cover-mag__mast"><?= e((string) setting('site_name', 'Lumina Africa Foundation')) ?></span>
    <span class="nl-cover-mag__title"><?= e((string) ($issue['title'] ?? '')) ?></span>
    <?php if ($label !== ''): ?><span class="nl-cover-mag__issue"><?= e($label) ?></span><?php endif; ?>
</span>
<?php endif; ?>
