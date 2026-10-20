<?php
/**
 * FAQ with a photo on one side and an accordion on the other (first item open). Vars: $faqs* (rows of the faqs table or
 * ['question','answer'] arrays), $image (content_img() result: shown only when it has a photo), $eyebrow, $title, $lead,
 * $prefix (unique id prefix when there are two FAQ blocks on a page), $first_open (default true), $reverse (photo on the right), $id (id of the heading, for aria-labelledby).
 * Renders nothing when there are no questions. Answers may be plain text (newlines kept) or rich text; both are output safely.
 */
if (empty($faqs)) {
    return;
}
$fqPrefix = preg_replace('/[^a-z0-9_-]/i', '', (string) ($prefix ?? 'faq')) ?: 'faq';
$fqOpen = ($first_open ?? true) !== false;
$fqImg = (array) ($image ?? []);
$fqHasImg = !empty($fqImg['src']) && (!array_key_exists('has', $fqImg) || $fqImg['has']);
$fqHasHead = trim((string) ($title ?? '')) !== '' || trim((string) ($eyebrow ?? '')) !== '' || trim((string) ($lead ?? '')) !== '';
?>
<div class="faq-split<?= !empty($reverse) ? ' faq-split--reverse' : '' ?><?= $fqHasImg ? '' : ' faq-split--solo' ?>">
    <?php if ($fqHasImg): ?>
        <figure class="faq-split__media" data-reveal><img src="<?= e($fqImg['src']) ?>" alt="<?= e($fqImg['alt'] ?? '') ?>" loading="lazy" decoding="async"></figure>
    <?php endif; ?>
    <div class="faq-split__body">
        <?php if ($fqHasHead): ?><div class="faq-split__head"><?= partial('partials/section-head', ['eyebrow' => $eyebrow ?? '', 'title' => $title ?? '', 'lead' => $lead ?? '', 'class' => 'mb-0', 'id' => $id ?? '']) ?></div><?php endif; ?>
        <div class="accordion" data-accordion="single">
            <?php foreach (array_values((array) $faqs) as $i => $f):
                $pid = $fqPrefix . '-' . ($i + 1);
                $open = $fqOpen && $i === 0;
                $answer = (string) ($f['answer'] ?? '');
                $answerHtml = $answer !== strip_tags($answer) ? rich($answer) : nl2p($answer);
            ?>
                <div class="accordion__item<?= $open ? ' is-open' : '' ?>">
                    <h3 class="accordion__heading">
                        <button class="accordion__trigger" type="button" id="<?= e($pid) ?>-q" aria-expanded="<?= $open ? 'true' : 'false' ?>" aria-controls="<?= e($pid) ?>-a" data-accordion-trigger>
                            <span><?= e($f['question'] ?? '') ?></span><?= icon('chevron-down') ?>
                        </button>
                    </h3>
                    <div class="accordion__panel" id="<?= e($pid) ?>-a" role="region" aria-labelledby="<?= e($pid) ?>-q" data-accordion-panel<?= $open ? '' : ' hidden' ?>>
                        <?= $answerHtml ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
