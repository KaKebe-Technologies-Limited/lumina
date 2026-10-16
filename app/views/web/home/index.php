<?php
/**
 * HOME (B1 "web-core"). EVERY string comes from content('home', ...) (registry app/config/pages/home.php, editable in Admin > Pages > Home)
 * or from the database / Settings. Vars: $stats (list of ['label','value','prefix','suffix']), $projects (card arrays).
 */

use App\Controllers\Web\HomeSupport as H;

$heroImg = content_img('home', 'hero.image');
$cta1 = content_btn('home', 'hero.cta1');
$cta2 = content_btn('home', 'hero.cta2');
$city = trim((string) setting('city_country', ''));
$eyebrow = static function (string $key): string {
    $t = trim((string) content('home', $key));
    return $t === '' ? '' : '<span class="eyebrow">' . e($t) . '</span>';
};
$videoId = H::youtubeId((string) content('home', 'why_digital.video_url'));
?>
<section class="hero home-hero" aria-labelledby="hero-title">
    <div class="hero__inner">
        <div class="home-hero__copy">
            <?php if (trim((string) content('home', 'hero.eyebrow')) !== ''): ?><span class="eyebrow hero__eyebrow"><?= e(content('home', 'hero.eyebrow')) ?></span><?php endif; ?>
            <h1 class="hero__title" id="hero-title"><?= e(content('home', 'hero.title')) ?></h1>
            <p class="hero__lead"><?= e(H::fill((string) content('home', 'hero.lead'))) ?></p>
            <?php if ($cta1['has'] || $cta2['has']): ?>
                <div class="hero__actions">
                    <?php if ($cta1['has']): ?><a class="btn btn--accent btn--lg" href="<?= e($cta1['url']) ?>"<?= $cta1['external'] ? ' target="_blank" rel="noopener noreferrer"' : '' ?>><?= e($cta1['label']) ?> <?= icon('arrow-right', 'icon--arrow') ?></a><?php endif; ?>
                    <?php if ($cta2['has']): ?><a class="btn btn--outline-light btn--lg" href="<?= e($cta2['url']) ?>"<?= $cta2['external'] ? ' target="_blank" rel="noopener noreferrer"' : '' ?>><?= e($cta2['label']) ?></a><?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php if ($heroImg['has']): ?>
            <div class="hero__media home-hero__media">
                <img src="<?= e($heroImg['src']) ?>" alt="<?= e($heroImg['alt']) ?>" fetchpriority="high" decoding="async">
                <?php if ((bool) content('home', 'hero.show_location') && $city !== ''): ?>
                    <span class="home-hero__badge"><?= icon('map-pin') ?> <span><?= e($city) ?></span></span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php /* ------------------------------------------------------------------ WHO WE ARE */ ?>
<?php section_begin('home', 'who_we_are', ['class' => 'home-who', 'labelledby' => 'who-title']); $img = content_img('home', 'who_we_are.image'); $btn = content_btn('home', 'who_we_are.cta'); ?>
    <div class="split">
        <div data-reveal>
            <?= $eyebrow('who_we_are.eyebrow') ?>
            <h2 id="who-title"><?= e(content('home', 'who_we_are.title')) ?></h2>
            <div class="prose mt-4"><?= content_html('home', 'who_we_are.body') ?></div>
            <?php if ($btn['has']): ?><p class="mt-5"><a class="btn btn--primary" href="<?= e($btn['url']) ?>"><?= e($btn['label']) ?> <?= icon('arrow-right', 'icon--arrow') ?></a></p><?php endif; ?>
        </div>
        <?php if ($img['has']): ?><div class="split__media home-photo" data-reveal><img src="<?= e($img['src']) ?>" alt="<?= e($img['alt']) ?>" loading="lazy"></div><?php endif; ?>
    </div>
<?php section_end(); ?>

<?php /* ------------------------------------------------------------------ CHALLENGE */ ?>
<?php section_begin('home', 'challenge', ['class' => 'section--tint home-challenge', 'labelledby' => 'challenge-title']); $img = content_img('home', 'challenge.image'); ?>
    <div class="split split--reverse">
        <div data-reveal>
            <?= $eyebrow('challenge.eyebrow') ?>
            <h2 id="challenge-title"><?= e(content('home', 'challenge.title')) ?></h2>
            <div class="prose mt-4"><?= content_html('home', 'challenge.body') ?></div>
        </div>
        <?php if ($img['has']): ?><div class="split__media home-photo" data-reveal><img src="<?= e($img['src']) ?>" alt="<?= e($img['alt']) ?>" loading="lazy"></div><?php endif; ?>
    </div>
<?php section_end(); ?>

<?php /* ------------------------------------------------------------------ WHY DIGITAL INCLUSION MATTERS */ ?>
<?php section_begin('home', 'why_digital', ['class' => 'home-why', 'labelledby' => 'why-title']); $img = content_img('home', 'why_digital.image'); $closing = trim((string) content('home', 'why_digital.closing')); ?>
    <div class="section__header section__header--center" data-reveal>
        <?= $eyebrow('why_digital.eyebrow') ?>
        <h2 id="why-title"><?= e(content('home', 'why_digital.title')) ?></h2>
        <?php if (trim((string) content('home', 'why_digital.lead')) !== ''): ?><p class="lead"><?= e(content('home', 'why_digital.lead')) ?></p><?php endif; ?>
    </div>
    <div class="split split--start">
        <div class="prose" data-reveal><?= content_html('home', 'why_digital.body') ?></div>
        <?php if ($videoId !== ''): ?>
            <div class="home-video" data-reveal>
                <div class="video-facade" data-video-facade data-video-id="<?= e($videoId) ?>" data-video-title="<?= e(content('home', 'why_digital.video_title')) ?>">
                    <a class="video-facade__poster" href="https://www.youtube.com/watch?v=<?= e($videoId) ?>" target="_blank" rel="noopener noreferrer" data-video-play>
                        <span class="video-facade__icon" aria-hidden="true"><svg viewBox="0 0 24 24" width="28" height="28" fill="currentColor"><path d="M8 5v14l11-7z"/></svg></span>
                        <span class="video-facade__label"><?= e(content('home', 'why_digital.video_play')) ?></span>
                        <span class="video-facade__title"><?= e(content('home', 'why_digital.video_title')) ?></span>
                    </a>
                </div>
                <p class="home-video__link"><a href="https://www.youtube.com/watch?v=<?= e($videoId) ?>" target="_blank" rel="noopener noreferrer"><?= e(content('home', 'why_digital.video_watch')) ?> <?= icon('external-link', 'icon--sm') ?></a></p>
            </div>
        <?php elseif ($img['has']): ?>
            <div class="split__media home-photo" data-reveal><img src="<?= e($img['src']) ?>" alt="<?= e($img['alt']) ?>" loading="lazy"></div>
        <?php endif; ?>
    </div>
    <?php if ($closing !== ''): ?><div class="callout mt-6" data-reveal><?= nl2p($closing) ?></div><?php endif; ?>
<?php section_end(); ?>
<?php if ($videoId !== '' && section_on('home', 'why_digital')): ?>
    <?php $this->start('scripts'); ?><script src="<?= e(asset('js/pages/home.js')) ?>" defer></script><?php $this->end(); ?>
<?php endif; ?>

<?php /* ------------------------------------------------------------------ VISION & MISSION */ ?>
<?php $vmEyebrow = trim((string) content('home', 'vision_mission.eyebrow')); ?>
<?php section_begin('home', 'vision_mission', ['class' => 'section--navy home-vm'] + ($vmEyebrow !== '' ? ['labelledby' => 'vm-title'] : [])); ?>
    <?php if ($vmEyebrow !== ''): ?><h2 class="home-vm__eyebrow eyebrow" id="vm-title"><?= e($vmEyebrow) ?></h2><?php endif; ?>
    <div class="home-vm__grid">
        <article class="home-vm__card" data-reveal>
            <span class="home-vm__icon" aria-hidden="true"><?= icon('eye', 'icon--lg') ?></span>
            <h3><?= e(content('home', 'vision_mission.vision_title')) ?></h3>
            <p><?= e(content('home', 'vision_mission.vision_text')) ?></p>
        </article>
        <article class="home-vm__card" data-reveal>
            <span class="home-vm__icon" aria-hidden="true"><?= icon('award', 'icon--lg') ?></span>
            <h3><?= e(content('home', 'vision_mission.mission_title')) ?></h3>
            <p><?= e(content('home', 'vision_mission.mission_text')) ?></p>
        </article>
    </div>
<?php section_end(); ?>

<?php /* ------------------------------------------------------------------ STATS (table stats, registry fallback) */ ?>
<?php if ($stats): ?>
<?php section_begin('home', 'stats', ['class' => 'section--navy home-stats', 'attrs' => ['aria-label' => (string) (trim((string) content('home', 'stats.title')) !== '' ? content('home', 'stats.title') : setting('site_name', ''))]]); ?>
    <?php if (trim((string) content('home', 'stats.title')) !== ''): ?><h2 class="home-stats__title"><?= e(content('home', 'stats.title')) ?></h2><?php endif; ?>
    <div class="stats stats--<?= count($stats) >= 4 ? '4' : (count($stats) === 3 ? '3' : '2') ?>">
        <?php foreach ($stats as $s): ?>
            <div class="stat" data-reveal>
                <span class="stat__value" data-count-to="<?= (int) $s['value'] ?>" data-count-prefix="<?= e($s['prefix']) ?>" data-count-suffix="<?= e($s['suffix']) ?>"><?= e($s['prefix'] . number_format((int) $s['value']) . $s['suffix']) ?></span>
                <span class="stat__label"><?= e($s['label']) ?></span>
            </div>
        <?php endforeach; ?>
    </div>
<?php section_end(); ?>
<?php endif; ?>

<?php /* ------------------------------------------------------------------ EMPOWERMENT PROJECTS */ ?>
<?php section_begin('home', 'projects', ['class' => 'section--tint home-projects', 'labelledby' => 'projects-title']); $pbtn = content_btn('home', 'projects.cta'); $cardCta = trim((string) content('home', 'projects.card_cta')); ?>
    <div class="section__header section__header--center" data-reveal>
        <h2 id="projects-title"><?= e(content('home', 'projects.title')) ?></h2>
        <?php if (trim((string) content('home', 'projects.intro')) !== ''): ?><p class="lead"><?= e(content('home', 'projects.intro')) ?></p><?php endif; ?>
    </div>
    <?php if ($projects): ?>
        <div class="grid grid--2 grid--gap-lg">
            <?php foreach ($projects as $i => $card): ?><?= partial('web/home/_project-card', ['card' => $card, 'n' => $i + 1, 'cta' => $cardCta]) ?><?php endforeach; ?>
        </div>
        <?php if ($pbtn['has']): ?><div class="section__footer"><a class="btn btn--primary btn--lg" href="<?= e($pbtn['url']) ?>"><?= e($pbtn['label']) ?> <?= icon('arrow-right', 'icon--arrow') ?></a></div><?php endif; ?>
    <?php endif; ?>
<?php section_end(); ?>

<?= partial('partials/cta-donate') ?>

<?php /* ------------------------------------------------------------------ FOUNDER STORY */ ?>
<?php section_begin('home', 'founder', ['class' => 'home-founder', 'labelledby' => 'founder-title']); $img = content_img('home', 'founder.image'); $btn = content_btn('home', 'founder.cta'); $caption = trim((string) content('home', 'founder.caption')); ?>
    <div class="split split--start">
        <?php if ($img['has']): ?>
            <figure class="split__media split__media--arch home-founder__photo" data-reveal>
                <img src="<?= e($img['src']) ?>" alt="<?= e($img['alt']) ?>" loading="lazy">
                <?php if ($caption !== ''): ?><figcaption><?= e($caption) ?></figcaption><?php endif; ?>
            </figure>
        <?php endif; ?>
        <div data-reveal>
            <?= $eyebrow('founder.eyebrow') ?>
            <h2 id="founder-title"><?= e(content('home', 'founder.title')) ?></h2>
            <div class="prose mt-4"><?= content_html('home', 'founder.body') ?></div>
            <?php if ($btn['has']): ?>
                <p class="mt-5"><a class="btn btn--outline" href="<?= e($btn['url']) ?>"<?= $btn['external'] ? ' target="_blank" rel="noopener noreferrer"' : '' ?>><?= e($btn['label']) ?> <?= icon($btn['external'] ? 'external-link' : 'arrow-right', $btn['external'] ? '' : 'icon--arrow') ?></a></p>
            <?php endif; ?>
        </div>
    </div>
<?php section_end(); ?>

<?php /* ------------------------------------------------------------------ IMPACT */ ?>
<?php section_begin('home', 'impact', ['class' => 'section--tint home-impact', 'labelledby' => 'impact-title']); $img = content_img('home', 'impact.image'); ?>
    <div class="split">
        <div data-reveal>
            <?= $eyebrow('impact.eyebrow') ?>
            <h2 id="impact-title"><?= e(content('home', 'impact.title')) ?></h2>
            <div class="prose mt-4"><?= content_html('home', 'impact.body') ?></div>
        </div>
        <?php if ($img['has']): ?><div class="split__media home-photo" data-reveal><img src="<?= e($img['src']) ?>" alt="<?= e($img['alt']) ?>" loading="lazy"></div><?php endif; ?>
    </div>
<?php section_end(); ?>

<?php /* ------------------------------------------------------------------ BACKGROUND & RATIONALE */ ?>
<?php section_begin('home', 'background', ['class' => 'home-background', 'container' => 'container--narrow', 'labelledby' => 'background-title']); $closing = trim((string) content('home', 'background.closing')); ?>
    <h2 id="background-title" data-reveal><?= e(content('home', 'background.title')) ?></h2>
    <div class="prose prose--wide mt-5" data-reveal><?= content_html('home', 'background.body') ?></div>
    <?php if ($closing !== ''): ?><div class="callout mt-6" data-reveal><?= nl2p($closing) ?></div><?php endif; ?>
<?php section_end(); ?>

<?php /* ------------------------------------------------------------------ CONTACT TEASER */ ?>
<?php
section_begin('home', 'contact', ['class' => 'section--tint home-contact', 'labelledby' => 'contact-title']);
$cbtn = content_btn('home', 'contact.cta');
$emails = array_values(array_filter([(string) setting('email_primary', ''), (string) setting('email_info', '')]));
$phones = array_values(array_filter([(string) setting('phone_1', ''), (string) setting('phone_2', '')]));
$wa = whatsapp_url();
$waDigits = preg_replace('/[^0-9]/', '', (string) preg_replace('~^https?://wa\.me/~i', '', (string) setting('whatsapp', ''))) ?? '';
$waText = strlen($waDigits) >= 7 ? '+' . $waDigits : $wa;
$where = implode(', ', array_filter([trim((string) setting('address', '')), $city]));
?>
    <div class="home-contact__box" data-reveal>
        <div class="home-contact__text">
            <h2 id="contact-title"><?= e(content('home', 'contact.title')) ?></h2>
            <?php if (trim((string) content('home', 'contact.text')) !== ''): ?><p class="lead"><?= e(content('home', 'contact.text')) ?></p><?php endif; ?>
            <?php if ($cbtn['has']): ?><p class="mt-5"><a class="btn btn--accent btn--lg" href="<?= e($cbtn['url']) ?>"><?= e($cbtn['label']) ?> <?= icon('arrow-right', 'icon--arrow') ?></a></p><?php endif; ?>
        </div>
        <?php if ($emails || $phones || $wa !== '' || $where !== ''): ?>
            <ul class="home-contact__list">
                <?php foreach ($emails as $em): ?>
                    <li><?= icon('mail', 'icon--lg') ?><span><span class="home-contact__label"><?= e(content('home', 'contact.email_label')) ?></span><a href="mailto:<?= e($em) ?>"><?= email_html($em) ?></a></span></li>
                <?php endforeach; ?>
                <?php foreach ($phones as $ph): ?>
                    <li><?= icon('phone', 'icon--lg') ?><span><span class="home-contact__label"><?= e(content('home', 'contact.phone_label')) ?></span><a href="<?= e(phone_href($ph)) ?>"><?= e($ph) ?></a></span></li>
                <?php endforeach; ?>
                <?php if ($wa !== ''): ?>
                    <li><?= icon('whatsapp', 'icon--lg') ?><span><span class="home-contact__label"><?= e(content('home', 'contact.whatsapp_label')) ?></span><a href="<?= e($wa) ?>" target="_blank" rel="noopener noreferrer"><?= e($waText) ?></a></span></li>
                <?php endif; ?>
                <?php if ($where !== ''): ?>
                    <li><?= icon('map-pin', 'icon--lg') ?><span><span class="home-contact__label"><?= e(content('home', 'contact.location_label')) ?></span><span><?= e($where) ?></span></span></li>
                <?php endif; ?>
            </ul>
        <?php endif; ?>
    </div>
<?php section_end(); ?>
