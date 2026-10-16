<?php
/**
 * ABOUT (B1 "web-core"). All copy from content('about', ...) (registry app/config/pages/about.php). Vars: $board (team_members rows, group board),
 * $hasTeam (bool: at least one active team member exists).
 */

use App\Controllers\Web\HomeSupport as H;

$heroImg = content_img('about', 'hero.image');
$mapUrl = trim((string) setting('map_embed_url', ''));
$mapOk = (bool) preg_match('~^https://(www\.google\.com/maps|maps\.google\.com)/~i', $mapUrl);
$addressLine = implode(', ', array_filter([trim((string) setting('address', '')), trim((string) setting('city_country', ''))]));
?>
<?= partial('partials/page-hero', [
    'eyebrow' => (string) content('about', 'hero.eyebrow'),
    'title'   => (string) content('about', 'hero.title'),
    'lead'    => H::fill((string) content('about', 'hero.lead')),
    'image'   => $heroImg['has'] ? ['src' => $heroImg['src'], 'alt' => $heroImg['alt']] : null,
]) ?>

<?php /* ------------------------------------------------------------------ STORY */ ?>
<?php section_begin('about', 'story', ['class' => 'about-story']); $img = content_img('about', 'story.image'); $title = trim((string) content('about', 'story.title')); $emphasis = trim((string) content('about', 'story.emphasis')); ?>
    <div class="split">
        <div data-reveal>
            <?php if ($title !== ''): ?><h2 class="mb-4"><?= e($title) ?></h2><?php endif; ?>
            <div class="prose"><?= content_html('about', 'story.body') ?></div>
            <?php if ($emphasis !== ''): ?><p class="about-story__emphasis mt-5"><?= e($emphasis) ?></p><?php endif; ?>
        </div>
        <?php if ($img['has']): ?><div class="split__media home-photo" data-reveal><img src="<?= e($img['src']) ?>" alt="<?= e($img['alt']) ?>" loading="lazy"></div><?php endif; ?>
    </div>
<?php section_end(); ?>

<?php /* ------------------------------------------------------------------ FOUNDER QUOTE */ ?>
<?php section_begin('about', 'quote', ['class' => 'section--tint about-quote']); $img = content_img('about', 'quote.image'); $btn = content_btn('about', 'quote.cta'); $author = trim((string) content('about', 'quote.author')); ?>
    <figure class="about-quote__box" data-reveal>
        <?php if ($img['has']): ?><img class="about-quote__photo" src="<?= e($img['src']) ?>" alt="<?= e($img['alt']) ?>" loading="lazy"><?php endif; ?>
        <blockquote class="about-quote__text">
            <?= nl2p((string) content('about', 'quote.text')) ?>
            <?php if ($author !== ''): ?><footer class="about-quote__cite"><?= e($author) ?></footer><?php endif; ?>
            <?php if ($btn['has']): ?><p class="mt-5"><a class="btn btn--accent" href="<?= e($btn['url']) ?>"><?= e($btn['label']) ?> <?= icon('arrow-right', 'icon--arrow') ?></a></p><?php endif; ?>
        </blockquote>
    </figure>
<?php section_end(); ?>

<?php /* ------------------------------------------------------------------ VISION & MISSION */ ?>
<?php section_begin('about', 'vision_mission', ['class' => 'about-vm', 'labelledby' => 'about-vm-title']); ?>
    <?php if (trim((string) content('about', 'vision_mission.title')) !== ''): ?>
        <div class="section__header section__header--center" data-reveal><h2 id="about-vm-title"><?= e(content('about', 'vision_mission.title')) ?></h2></div>
    <?php endif; ?>
    <div class="about-vm__grid">
        <article class="about-vm__card" data-reveal>
            <span class="icon-tile" aria-hidden="true"><?= icon('eye', 'icon--lg') ?></span>
            <h3><?= e(content('about', 'vision_mission.vision_title')) ?></h3>
            <p><?= e(content('about', 'vision_mission.vision_text')) ?></p>
        </article>
        <article class="about-vm__card" data-reveal>
            <span class="icon-tile" aria-hidden="true"><?= icon('award', 'icon--lg') ?></span>
            <h3><?= e(content('about', 'vision_mission.mission_title')) ?></h3>
            <p><?= e(content('about', 'vision_mission.mission_text')) ?></p>
        </article>
    </div>
<?php section_end(); ?>

<?php /* ------------------------------------------------------------------ SECOND VISION / MISSION WORDING (hidden by default) */ ?>
<?php $bannerEyebrow = trim((string) content('about', 'banner.eyebrow')); ?>
<?php section_begin('about', 'banner', ['class' => 'section--navy about-banner'] + ($bannerEyebrow !== '' ? ['labelledby' => 'about-banner-title'] : [])); ?>
    <?php if ($bannerEyebrow !== ''): ?><h2 class="eyebrow about-banner__eyebrow" id="about-banner-title"><?= e($bannerEyebrow) ?></h2><?php endif; ?>
    <div class="about-banner__grid">
        <article class="about-banner__card" data-reveal>
            <h3><?= e(content('about', 'banner.vision_title')) ?></h3>
            <p><?= e(content('about', 'banner.vision_text')) ?></p>
        </article>
        <article class="about-banner__card" data-reveal>
            <h3><?= e(content('about', 'banner.mission_title')) ?></h3>
            <p><?= e(content('about', 'banner.mission_text')) ?></p>
        </article>
    </div>
<?php section_end(); ?>

<?php /* ------------------------------------------------------------------ CORE VALUES */ ?>
<?php section_begin('about', 'values', ['class' => 'section--tint about-values', 'labelledby' => 'about-values-title']); $values = content_list('about', 'values.items'); ?>
    <div class="section__header section__header--center" data-reveal>
        <h2 id="about-values-title"><?= e(content('about', 'values.title')) ?></h2>
        <?php if (trim((string) content('about', 'values.intro')) !== ''): ?><p class="lead"><?= e(content('about', 'values.intro')) ?></p><?php endif; ?>
    </div>
    <?php if ($values): ?>
        <ul class="about-values__grid">
            <?php foreach ($values as $v): if (trim((string) ($v['title'] ?? '')) === '') { continue; } ?>
                <li class="about-value" data-reveal>
                    <span class="icon-tile" aria-hidden="true"><?= icon('check-circle', 'icon--lg') ?></span>
                    <div>
                        <h3><?= e($v['title']) ?></h3>
                        <?php if (trim((string) ($v['text'] ?? '')) !== ''): ?><p><?= e($v['text']) ?></p><?php endif; ?>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
<?php section_end(); ?>

<?php /* ------------------------------------------------------------------ GOVERNANCE (+ board from Admin > Team) */ ?>
<?php section_begin('about', 'governance', ['class' => 'about-governance', 'labelledby' => 'about-gov-title']); $teamBtn = content_btn('about', 'governance.team_cta'); ?>
    <div class="section__header section__header--center" data-reveal>
        <h2 id="about-gov-title"><?= e(content('about', 'governance.title')) ?></h2>
        <?php if (trim((string) content('about', 'governance.text')) !== ''): ?><p class="lead"><?= e(content('about', 'governance.text')) ?></p><?php endif; ?>
    </div>
    <?php if ($board): ?>
        <h3 class="about-governance__board h2 text-center mb-5" data-reveal><?= e(content('about', 'governance.board_title')) ?></h3>
        <div class="about-governance__grid">
            <?php foreach ($board as $member): ?><?= partial('partials/team-card', ['member' => $member]) ?><?php endforeach; ?>
        </div>
    <?php endif; ?>
    <?php if ($hasTeam && $teamBtn['has']): ?>
        <div class="section__footer"><a class="btn btn--outline" href="<?= e($teamBtn['url']) ?>"><?= e($teamBtn['label']) ?> <?= icon('arrow-right', 'icon--arrow') ?></a></div>
    <?php endif; ?>
<?php section_end(); ?>

<?php /* ------------------------------------------------------------------ LOCATION */ ?>
<?php section_begin('about', 'location', ['class' => 'section--tint about-location', 'labelledby' => 'about-loc-title']); ?>
    <div class="split split--start">
        <div data-reveal>
            <h2 id="about-loc-title"><?= e(content('about', 'location.title')) ?></h2>
            <?php if (trim((string) content('about', 'location.text')) !== ''): ?><p class="lead mt-4"><?= e(content('about', 'location.text')) ?></p><?php endif; ?>
            <?php if ($addressLine !== ''): ?>
                <p class="about-location__address mt-5"><?= icon('map-pin', 'icon--lg') ?> <span><strong><?= e(content('about', 'location.address_label')) ?></strong><br><?= e($addressLine) ?></span></p>
            <?php endif; ?>
        </div>
        <?php if ($mapOk): ?>
            <div class="about-location__map" data-reveal>
                <iframe src="<?= e($mapUrl) ?>" title="<?= e(content('about', 'location.map_title')) ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
            </div>
        <?php endif; ?>
    </div>
<?php section_end(); ?>

<?= partial('partials/cta-donate') ?>
