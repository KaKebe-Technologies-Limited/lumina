<?php
/**
 * ABOUT. All copy from content('about', ...) (registry app/config/pages/about.php). Vars: $board (team_members rows, group board),
 * $hasTeam (bool: at least one active team member exists). The figures card reads the stats table and disappears while it is empty.
 * Layout language: docs/DESIGN.md (page-hero, photo-stack, stat-band, feature cards, deep navy panel).
 */

use App\Controllers\Web\HomeSupport as H;
use App\Models\Stat;

$heroImg = content_img('about', 'hero.image');
$mapUrl = trim((string) setting('map_embed_url', ''));
$mapOk = (bool) preg_match('~^https://(www\.google\.com/maps|maps\.google\.com)/~i', $mapUrl);
$addressLine = implode(', ', array_filter([trim((string) setting('address', '')), trim((string) setting('city_country', ''))]));
$stats = [];
try {
    $stats = Stat::forGroup('home');
} catch (\Throwable) {
    $stats = [];
}
?>
<?= partial('partials/page-hero', [
    'eyebrow' => (string) content('about', 'hero.eyebrow'),
    'title'   => (string) content('about', 'hero.title'),
    'lead'    => H::fill((string) content('about', 'hero.lead')),
    'image'   => $heroImg['has'] ? ['src' => $heroImg['src'], 'alt' => $heroImg['alt'], 'focus' => '50% 38%'] : null,
]) ?>

<?php /* ------------------------------------------------------------------ STORY: tilted photo stack + text */ ?>
<?php
$storyImgs = [content_img('about', 'story.image'), content_img('about', 'story.image2')];
$storyHasMedia = (bool) array_filter($storyImgs, static fn(array $i): bool => $i['has']);
$storyEyebrow = trim((string) content('about', 'story.eyebrow'));
$storyTitle = trim((string) content('about', 'story.title'));
$emphasis = trim((string) content('about', 'story.emphasis'));
section_begin('about', 'story', ['class' => 'about-story', 'labelledby' => $storyTitle !== '' ? 'about-story-title' : null, 'attrs' => $storyTitle === '' && $storyEyebrow !== '' ? ['aria-label' => $storyEyebrow] : []]); ?>
    <div class="split split--start<?= $storyHasMedia ? ' split--wide-media' : ' ia-split--solo' ?>">
        <?= partial('partials/photo-stack', ['images' => $storyImgs]) ?>
        <div data-reveal>
            <?= partial('partials/section-head', ['eyebrow' => $storyEyebrow, 'title' => $storyTitle, 'id' => 'about-story-title']) ?>
            <div class="prose"><?= content_html('about', 'story.body') ?></div>
            <?php if ($emphasis !== ''): ?><p class="ia-statement mt-5"><?= highlight($emphasis) ?></p><?php endif; ?>
        </div>
    </div>
<?php section_end(); ?>

<?php /* ------------------------------------------------------------------ FIGURES (stats table; hidden while empty) */ ?>
<?php if ($stats): ?>
<?php $impTitle = trim((string) content('about', 'impact.title')); section_begin('about', 'impact', ['class' => 'section--tint ia-impact', 'labelledby' => $impTitle !== '' ? 'about-impact-title' : null]); ?>
    <?= partial('partials/section-head', ['eyebrow' => (string) content('about', 'impact.eyebrow'), 'title' => $impTitle, 'align' => 'center', 'id' => 'about-impact-title']) ?>
    <?= partial('partials/stat-band', ['stats' => $stats]) ?>
<?php section_end(); ?>
<?php endif; ?>

<?php /* ------------------------------------------------------------------ FOUNDER QUOTE: deep navy panel with portrait */ ?>
<?php section_begin('about', 'quote', ['class' => 'about-quote']);
$img = content_img('about', 'quote.image');
$btn = content_btn('about', 'quote.cta');
$author = trim((string) content('about', 'quote.author'));
$quoteEyebrow = trim((string) content('about', 'quote.eyebrow')); ?>
    <figure class="ia-panel ia-quote<?= $img['has'] ? '' : ' ia-quote--solo' ?>" data-reveal>
        <?php if ($img['has']): ?><div class="ia-quote__photo"><img src="<?= e($img['src']) ?>" alt="<?= e($img['alt']) ?>" loading="lazy" decoding="async"></div><?php endif; ?>
        <div class="ia-quote__body">
            <?php if ($quoteEyebrow !== ''): ?><span class="eyebrow"><?= e($quoteEyebrow) ?></span><?php endif; ?>
            <span class="ia-quote__mark" aria-hidden="true"><?= icon('quote', 'icon--xl') ?></span>
            <blockquote class="ia-quote__text"><?= nl2p((string) content('about', 'quote.text')) ?></blockquote>
            <?php if ($author !== ''): ?><figcaption class="ia-quote__cite"><?= e($author) ?></figcaption><?php endif; ?>
            <?php if ($btn['has']): ?><p class="ia-quote__cta"><a class="btn btn--cta btn--caps" href="<?= e($btn['url']) ?>"><?= e($btn['label']) ?></a></p><?php endif; ?>
        </div>
    </figure>
<?php section_end(); ?>

<?php /* ------------------------------------------------------------------ VISION & MISSION */ ?>
<?php section_begin('about', 'vision_mission', ['class' => 'section--cool about-vm', 'labelledby' => 'about-vm-title']); ?>
    <?= partial('partials/section-head', ['eyebrow' => (string) content('about', 'vision_mission.eyebrow'), 'title' => (string) content('about', 'vision_mission.title'), 'align' => 'center', 'id' => 'about-vm-title']) ?>
    <div class="feature-row feature-row--2 ia-vm">
        <?= partial('partials/feature-card', ['class' => 'ia-vm__card', 'icon' => (string) content('about', 'vision_mission.vision_icon'), 'title' => (string) content('about', 'vision_mission.vision_title'), 'text' => (string) content('about', 'vision_mission.vision_text')]) ?>
        <?= partial('partials/feature-card', ['class' => 'ia-vm__card', 'icon' => (string) content('about', 'vision_mission.mission_icon'), 'title' => (string) content('about', 'vision_mission.mission_title'), 'text' => (string) content('about', 'vision_mission.mission_text')]) ?>
    </div>
<?php section_end(); ?>

<?php /* ------------------------------------------------------------------ SECOND VISION / MISSION WORDING (hidden by default) */ ?>
<?php $bannerEyebrow = trim((string) content('about', 'banner.eyebrow')); ?>
<?php section_begin('about', 'banner', ['class' => 'about-banner'] + ($bannerEyebrow !== '' ? ['labelledby' => 'about-banner-title'] : [])); ?>
    <div class="ia-panel" data-reveal>
        <?php if ($bannerEyebrow !== ''): ?><h2 class="eyebrow eyebrow--center about-banner__eyebrow" id="about-banner-title"><?= e($bannerEyebrow) ?></h2><?php endif; ?>
        <div class="about-banner__grid">
            <article class="about-banner__card">
                <h3><?= e(content('about', 'banner.vision_title')) ?></h3>
                <p><?= e(content('about', 'banner.vision_text')) ?></p>
            </article>
            <article class="about-banner__card">
                <h3><?= e(content('about', 'banner.mission_title')) ?></h3>
                <p><?= e(content('about', 'banner.mission_text')) ?></p>
            </article>
        </div>
    </div>
<?php section_end(); ?>

<?php /* ------------------------------------------------------------------ CORE VALUES */ ?>
<?php section_begin('about', 'values', ['class' => 'about-values', 'labelledby' => 'about-values-title']); $values = content_list('about', 'values.items'); ?>
    <?= partial('partials/section-head', ['eyebrow' => (string) content('about', 'values.eyebrow'), 'title' => (string) content('about', 'values.title'), 'lead' => (string) content('about', 'values.intro'), 'align' => 'center', 'id' => 'about-values-title']) ?>
    <?php if ($values): ?>
        <div class="ia-cards">
            <?php foreach ($values as $v): ?>
                <?= partial('partials/feature-card', ['icon' => (string) ($v['icon'] ?? ''), 'title' => (string) ($v['title'] ?? ''), 'text' => (string) ($v['text'] ?? '')]) ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
<?php section_end(); ?>

<?php /* ------------------------------------------------------------------ GOVERNANCE (+ board from Admin > Team) */ ?>
<?php section_begin('about', 'governance', ['class' => 'section--tint about-governance', 'labelledby' => 'about-gov-title']); $teamBtn = content_btn('about', 'governance.team_cta'); ?>
    <?= partial('partials/section-head', ['eyebrow' => (string) content('about', 'governance.eyebrow'), 'title' => (string) content('about', 'governance.title'), 'lead' => (string) content('about', 'governance.text'), 'align' => 'center', 'id' => 'about-gov-title']) ?>
    <?php if ($board): ?>
        <h3 class="about-governance__board text-center" data-reveal><?= e(content('about', 'governance.board_title')) ?></h3>
        <div class="about-governance__grid">
            <?php foreach ($board as $member): ?><?= partial('partials/team-card', ['member' => $member]) ?><?php endforeach; ?>
        </div>
    <?php endif; ?>
    <?php if ($hasTeam && $teamBtn['has']): ?>
        <div class="section__footer"><a class="btn btn--outline btn--caps" href="<?= e($teamBtn['url']) ?>"><?= e($teamBtn['label']) ?> <?= icon('arrow-right', 'icon--arrow') ?></a></div>
    <?php endif; ?>
<?php section_end(); ?>

<?php /* ------------------------------------------------------------------ LOCATION */ ?>
<?php section_begin('about', 'location', ['class' => 'about-location', 'labelledby' => 'about-loc-title']); ?>
    <div class="split split--start<?= $mapOk ? '' : ' ia-split--solo' ?>">
        <div data-reveal>
            <?= partial('partials/section-head', ['eyebrow' => (string) content('about', 'location.eyebrow'), 'title' => (string) content('about', 'location.title'), 'lead' => (string) content('about', 'location.text'), 'id' => 'about-loc-title']) ?>
            <?php if ($addressLine !== ''): ?>
                <div class="feature-card feature-card--row ia-address">
                    <span class="feature-card__icon"><?= icon('map-pin') ?></span>
                    <div class="feature-card__body">
                        <h3 class="feature-card__title"><?= e(content('about', 'location.address_label')) ?></h3>
                        <p class="feature-card__text"><?= e($addressLine) ?></p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php if ($mapOk): ?>
            <div class="ia-map" data-reveal>
                <iframe src="<?= e($mapUrl) ?>" title="<?= e(content('about', 'location.map_title')) ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
            </div>
        <?php endif; ?>
    </div>
<?php section_end(); ?>

<?= partial('partials/cta-donate') ?>
