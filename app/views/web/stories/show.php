<?php
/**
 * STORY DETAIL (/stories/{slug}) - B-INNER-B restyle of the B7 view.
 * Vars: $story (stories row - guaranteed active + consent_confirmed + published by StoriesController), $others (up to 3 other consented stories).
 * Every label comes from content('stories', ...) (admin: Pages > Impact Stories); the story itself from the Stories admin.
 * The image alt text is the one written by the editor in Stories ("image_alt"); when empty the image is treated as decorative.
 */
if (empty($story['consent_confirmed'])) {
    return;                                   // belt and braces: never render a story without confirmed consent
}
$img = img_url($story['image'] ?? '');
$summary = trim((string) ($story['summary'] ?? ''));
$body = trim((string) ($story['body'] ?? ''));
$published = format_date($story['published_at'] ?? null);
$personBits = array_filter([trim((string) ($story['person_role'] ?? '')), trim((string) ($story['location'] ?? ''))]);
$person = trim((string) ($story['person_name'] ?? ''));
$initial = $person !== '' ? mb_strtoupper(mb_substr($person, 0, 1)) : '';
$join = content_btn('stories', 'join.cta');
?>
<?= partial('partials/page-hero', [
    'eyebrow' => $published !== '' && trim((string) content('stories', 'detail.published')) !== '' ? content('stories', 'detail.published') . ' ' . $published : $published,
    'title'   => $story['title'],
    'lead'    => $summary,
]) ?>

<article class="section story-detail" id="story">
    <div class="container">
        <p class="story-back"><a class="btn btn--outline btn--caps btn--sm" href="<?= e(url('stories')) ?>"><?= icon('arrow-left', 'icon--sm') ?> <?= e(content('stories', 'detail.back')) ?></a></p>

        <div class="story-detail__layout<?= $img !== '' ? ' story-detail__layout--media' : '' ?>">
            <?php if ($img !== ''): ?>
                <figure class="story-detail__media"><img src="<?= e($img) ?>" alt="<?= e($story['image_alt'] ?? '') ?>" fetchpriority="high" decoding="async"></figure>
            <?php endif; ?>
            <div class="story-detail__body">
                <?php if ($person !== ''): ?>
                    <p class="story-person">
                        <span class="story-person__avatar" aria-hidden="true"><?= e($initial) ?></span>
                        <span>
                            <span class="story-person__name"><?= e($person) ?></span>
                            <?php if ($personBits): ?><span class="story-person__meta"><?= e(implode(' · ', $personBits)) ?></span><?php endif; ?>
                        </span>
                    </p>
                <?php endif; ?>
                <?php if ($body !== ''): ?><div class="prose"><?= rich($body) ?></div><?php endif; ?>
            </div>
        </div>
    </div>
</article>

<?php if ($others): ?>
    <section class="section section--tint" id="more-stories" aria-labelledby="more-stories-title">
        <div class="container">
            <?= partial('partials/section-head', ['title' => content('stories', 'detail.more_title'), 'id' => 'more-stories-title', 'align' => 'center']) ?>
            <div class="grid grid--3 grid--gap-lg story-grid post-grid post-grid--n<?= min(count($others), 3) ?>">
                <?php foreach ($others as $other): ?>
                    <?= partial('web/stories/_card', ['story' => $other, 'cta' => content('stories', 'list.cta')]) ?>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php section_begin('stories', 'join', ['bleed' => true, 'class' => 'section--band-wrap']); ?>
    <?= partial('web/blog/_band', [
        'title'   => content('stories', 'join.title'),
        'text'    => content('stories', 'join.text'),
        'actions' => [['label' => $join['label'], 'url' => $join['url'], 'external' => $join['external']]],
    ]) ?>
<?php section_end(); ?>
