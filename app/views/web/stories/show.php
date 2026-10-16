<?php
/**
 * STORY DETAIL (/stories/{slug}) - OWNED BY B7 "org-public".
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
$join = content_btn('stories', 'join.cta');
?>
<?= partial('partials/page-hero', [
    'eyebrow' => $published !== '' && trim((string) content('stories', 'detail.published')) !== '' ? content('stories', 'detail.published') . ' ' . $published : $published,
    'title'   => $story['title'],
    'lead'    => $summary,
]) ?>

<article class="section story-detail" id="story">
    <div class="container">
        <p class="story-back"><a class="link-arrow" href="<?= e(url('stories')) ?>"><?= icon('arrow-left', 'icon--arrow') ?> <?= e(content('stories', 'detail.back')) ?></a></p>

        <div class="story-detail__layout<?= $img !== '' ? ' story-detail__layout--media' : '' ?>">
            <?php if ($img !== ''): ?>
                <figure class="story-detail__media"><img src="<?= e($img) ?>" alt="<?= e($story['image_alt'] ?? '') ?>" fetchpriority="high"></figure>
            <?php endif; ?>
            <div class="story-detail__body">
                <?php if ($person !== ''): ?>
                    <p class="story-person">
                        <span class="story-person__name"><?= e($person) ?></span>
                        <?php if ($personBits): ?><span class="story-person__meta"><?= e(implode(' · ', $personBits)) ?></span><?php endif; ?>
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
            <div class="section__header"><h2 id="more-stories-title"><?= e(content('stories', 'detail.more_title')) ?></h2></div>
            <div class="grid grid--3 story-grid">
                <?php foreach ($others as $other): ?>
                    <?= partial('partials/story-card', ['story' => $other, 'cta' => content('stories', 'list.cta')]) ?>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php section_begin('stories', 'join', ['class' => 'section--navy', 'container' => 'container--narrow']); ?>
    <div class="stories-join text-center">
        <h2><?= e(content('stories', 'join.title')) ?></h2>
        <?php if (trim((string) content('stories', 'join.text')) !== ''): ?><p class="lead mt-3"><?= e(content('stories', 'join.text')) ?></p><?php endif; ?>
        <?php if ($join['has']): ?>
            <p class="mt-5"><a class="btn btn--accent btn--lg" href="<?= e($join['url']) ?>"<?= $join['external'] ? ' target="_blank" rel="noopener noreferrer"' : '' ?>><?= e($join['label']) ?></a></p>
        <?php endif; ?>
    </div>
<?php section_end(); ?>
