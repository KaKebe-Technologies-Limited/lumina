<?php
/**
 * Testimonial / impact-story card on a pale tint (no star ratings, nothing invented).
 * EITHER pass $story = a stories row (the card renders NOTHING unless consent_confirmed = 1: use Story::published()),
 * OR pass the pieces yourself: $quote*, $name, $role, $image (['src','alt']), $href.  $variant: '' | 'dark' | 'large'.
 */
if (isset($story) && is_array($story)) {
    if (empty($story['consent_confirmed'])) {
        return;                                                   // never show a story without confirmed consent
    }
    $qcQuote = trim((string) ($story['summary'] ?? '')) !== '' ? (string) $story['summary'] : (string) ($story['title'] ?? '');
    $qcName = (string) ($story['person_name'] ?? '');
    $qcRole = implode(' · ', array_filter([(string) ($story['person_role'] ?? ''), (string) ($story['location'] ?? '')]));
    $qcImg = img_url((string) ($story['image'] ?? ''));
    $qcAlt = (string) ($story['image_alt'] ?? '');
    $qcHref = !empty($story['slug']) ? url('stories/' . $story['slug']) : '';
} else {
    $qcQuote = (string) ($quote ?? '');
    $qcName = (string) ($name ?? '');
    $qcRole = (string) ($role ?? '');
    $qcImg = (string) ($image['src'] ?? '');
    $qcAlt = (string) ($image['alt'] ?? '');
    $qcHref = (string) ($href ?? '');
}
if (trim($qcQuote) === '') {
    return;
}
$qcVariant = in_array($variant ?? '', ['dark', 'large'], true) ? ' quote-card--' . $variant : '';
$qcInitial = $qcName !== '' ? mb_strtoupper(mb_substr(trim($qcName), 0, 1)) : '';
?>
<figure class="quote-card<?= e($qcVariant) ?>" data-reveal>
    <span class="quote-card__mark"><?= icon('quote') ?></span>
    <blockquote class="quote-card__text"><?= e($qcQuote) ?></blockquote>
    <?php if ($qcName !== ''): ?>
        <figcaption class="quote-card__person">
            <span class="quote-card__avatar"><?php if ($qcImg !== ''): ?><img src="<?= e($qcImg) ?>" alt="<?= e($qcAlt) ?>" loading="lazy"><?php else: ?><span aria-hidden="true"><?= e($qcInitial) ?></span><?php endif; ?></span>
            <span>
                <span class="quote-card__name"><?php if ($qcHref !== ''): ?><a href="<?= e($qcHref) ?>"><?= e($qcName) ?></a><?php else: ?><?= e($qcName) ?><?php endif; ?></span>
                <?php if ($qcRole !== ''): ?><span class="quote-card__role"><?= e($qcRole) ?></span><?php endif; ?>
            </span>
        </figcaption>
    <?php endif; ?>
</figure>
