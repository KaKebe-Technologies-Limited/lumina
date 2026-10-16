<?php
/**
 * HOME. EVERY string comes from content('home', ...) (registry app/config/pages/home.php, editable in Admin > Pages > Home),
 * from the database (programs, projects, stats, stories, faqs, gallery) or from Settings. Nothing is hard-coded or invented:
 * data blocks print nothing when their table is empty.
 * Vars (HomeController): $stats, $featured, $stories, $gallery, $faqs.   Layout language: docs/DESIGN.md.
 *
 * Section order (each one has an on/off switch in the registry):
 *   hero > help > who_we_are + challenge > volunteer band + stats card > vision_mission (+ founder quote, mosaic)
 *   > projects carousel > donate_panel > stories > faq > why_digital > founder > impact > background > contact
 */

use App\Controllers\Web\HomeSupport as H;

/** Small helper: a pill button as safe html for the partials that take ready-made html (carousel foot). */
$pill = static function (array $b, string $class): string {
    return $b['has'] ? '<a class="btn ' . e($class) . '" href="' . e($b['url']) . '"' . ($b['external'] ? ' target="_blank" rel="noopener noreferrer"' : '') . '>' . e($b['label']) . '</a>' : '';
};
/** Rows of a repeater that have a title. */
$titled = static fn(array $rows): array => array_values(array_filter($rows, static fn(array $r): bool => trim((string) ($r['title'] ?? '')) !== ''));

$heroOn = section_on('home', 'hero');
$founderOn = section_on('home', 'founder') && trim(strip_tags((string) content('home', 'founder.body'))) !== '';
$city = trim((string) setting('city_country', ''));
?>
<?php /* ------------------------------------------------------------------ 1. HERO (photo, headline with one highlighted phrase, orange button + video lightbox) */ ?>
<?php
if ($heroOn) {
    $heroButtons = [];
    foreach (['hero.cta1', 'hero.cta2'] as $key) {
        $b = content_btn('home', $key);
        if ($b['has']) {
            $heroButtons[] = ['label' => $b['label'], 'url' => $b['url']];
        }
    }
    echo partial('partials/hero-photo', [
        'eyebrow' => content('home', 'hero.eyebrow'),
        'title'   => content('home', 'hero.title'),
        'lead'    => H::fill((string) content('home', 'hero.lead')),
        'image'   => content_img('home', 'hero.image'),
        'buttons' => $heroButtons,
        'video'   => ['url' => content('home', 'hero.video_url'), 'label' => content('home', 'hero.video_label'), 'title' => content('home', 'hero.video_title')],
        'focus'   => (string) content('home', 'hero.image_focus'),
    ]);
} else {
    // the page always keeps its one <h1>, even when the owner hides the hero
    echo '<h1 class="visually-hidden">' . e(strip_highlight((string) content('home', 'hero.title'))) . '</h1>';
}
?>

<?php /* ------------------------------------------------------------------ 2. HOW WE HELP (outlined feature cards + tall rounded photo) */ ?>
<?php
$helpItems = $titled(content_list('home', 'help.items'));
$helpImg = content_img('home', 'help.image');
section_begin('home', 'help', ['class' => 'home-help' . ($heroOn ? ' sheet' : ''), 'labelledby' => 'help-title']);
?>
    <div class="split split--wide-text split--start home-help__grid<?= $helpImg['has'] ? '' : ' home-help__grid--solo' ?>">
        <div>
            <?= partial('partials/section-head', ['eyebrow' => content('home', 'help.eyebrow'), 'title' => content('home', 'help.title'), 'lead' => content('home', 'help.lead'), 'id' => 'help-title']) ?>
            <?php if ($helpItems): ?>
                <div class="home-help__cards">
                    <?php foreach ($helpItems as $f): ?>
                        <?= partial('partials/feature-card', ['icon' => $f['icon'] ?? '', 'title' => $f['title'], 'text' => $f['text'] ?? '', 'href' => link_url((string) ($f['url'] ?? ''))]) ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php if ($helpImg['has']): ?><figure class="split__media split__media--portrait home-help__media" data-reveal><?= photo_tag($helpImg) ?></figure><?php endif; ?>
    </div>
<?php section_end(); ?>

<?php /* ------------------------------------------------------------------ 3. WHO WE ARE (tilted photo stack + copy + orange button) */ ?>
<?php
$whoBtn = content_btn('home', 'who_we_are.cta');
$whoStack = partial('partials/photo-stack', ['images' => [content_img('home', 'who_we_are.image'), content_img('home', 'who_we_are.image2'), content_img('home', 'who_we_are.image3')]]);
section_begin('home', 'who_we_are', ['class' => 'section--tint home-who', 'labelledby' => 'who-title']);
?>
    <div class="split split--wide-media">
        <?= $whoStack ?>
        <div data-reveal>
            <?= partial('partials/section-head', ['eyebrow' => content('home', 'who_we_are.eyebrow'), 'title' => content('home', 'who_we_are.title'), 'id' => 'who-title', 'class' => 'mb-0']) ?>
            <div class="prose mt-4"><?= content_html('home', 'who_we_are.body') ?></div>
            <?php if ($whoBtn['has']): ?><p class="mt-5"><a class="btn btn--cta btn--caps" href="<?= e($whoBtn['url']) ?>"<?= $whoBtn['external'] ? ' target="_blank" rel="noopener noreferrer"' : '' ?>><?= e($whoBtn['label']) ?></a></p><?php endif; ?>
        </div>
    </div>
<?php section_end(); ?>

<?php /* ------------------------------------------------------------------ 3b. THE CHALLENGE (white card on the same cream background) */ ?>
<?php
$chImg = content_img('home', 'challenge.image');
section_begin('home', 'challenge', ['class' => 'section--tint section--flush-top home-challenge', 'labelledby' => 'challenge-title']);
?>
    <div class="home-challenge__card<?= $chImg['has'] ? ' home-challenge__card--photo' : '' ?>" data-reveal>
        <div class="home-challenge__head">
            <?php if (trim((string) content('home', 'challenge.eyebrow')) !== ''): ?><span class="eyebrow"><?= e(content('home', 'challenge.eyebrow')) ?></span><?php endif; ?>
            <h2 class="home-challenge__title" id="challenge-title"><?= highlight((string) content('home', 'challenge.title')) ?></h2>
        </div>
        <div class="prose home-challenge__body"><?= content_html('home', 'challenge.body') ?></div>
        <?php if ($chImg['has']): ?><figure class="home-challenge__media"><?= photo_tag($chImg) ?></figure><?php endif; ?>
    </div>
<?php section_end(); ?>

<?php /* ------------------------------------------------------------------ 4. VOLUNTEER BAND + overlapping STATS CARD (stats table only) */ ?>
<?php
$volOn = section_on('home', 'volunteer');
$statCard = ($stats && section_on('home', 'stats')) ? partial('partials/stat-band', ['stats' => $stats, 'overlap' => $volOn]) : '';
if ($volOn) {
    $volActions = [];
    $b = content_btn('home', 'volunteer.cta');
    if ($b['has']) {
        $volActions[] = ['label' => $b['label'], 'url' => $b['url'], 'class' => 'btn--cta btn--caps'];
    }
    $b = content_btn('home', 'volunteer.cta2');
    if ($b['has']) {
        $volActions[] = ['label' => $b['label'], 'url' => $b['url'], 'class' => 'btn--white btn--caps'];
    }
    section_begin('home', 'volunteer', ['bleed' => true, 'class' => 'band-section home-volunteer']);
    echo partial('partials/band', [
        'image'   => content_img('home', 'volunteer.image'),
        'eyebrow' => content('home', 'volunteer.eyebrow'),
        'title'   => content('home', 'volunteer.title'),
        'text'    => content('home', 'volunteer.text'),
        'actions' => $volActions,
        'video'   => ['url' => content('home', 'volunteer.video_url'), 'label' => content('home', 'volunteer.video_label'), 'title' => content('home', 'volunteer.video_title')],
        'after'   => $statCard,
        'focus'   => '50% 40%',
    ]);
    section_end();
} elseif ($statCard !== '') {
    section_begin('home', 'stats', ['class' => 'section--sm home-stats']);
    echo $statCard;
    section_end();
}
?>

<?php /* ------------------------------------------------------------------ 5. VISION & MISSION (collage + tint cards + icon rows + founder quote) + gallery MOSAIC */ ?>
<?php
$vmRows = $titled(content_list('home', 'vision_mission.rows'));
$vmStack = partial('partials/photo-stack', ['images' => [content_img('home', 'vision_mission.image1'), content_img('home', 'vision_mission.image2')]]);
$quote = trim((string) content('home', 'founder.quote'));
$founderImg = content_img('home', 'founder.image');
// gallery photos the carousel cards below already show are left out of the mosaic; fewer than three left = no mosaic (no repeats)
$srcKey = static fn(string $src): string => (string) strtok($src, '?');
$cardPhotos = array_map(static fn(array $f): string => $srcKey(img_url((string) ($f['row']['image'] ?? ''))), $featured);
// (filter first, THEN keep five: photos added later in Admin > Gallery sit at the end of the list and must still reach the mosaic)
$mosaicImages = array_slice(array_values(array_filter($gallery, static fn(array $g): bool => !in_array($srcKey($g['src']), $cardPhotos, true))), 0, 5);
$mosaic = (bool) content('home', 'vision_mission.show_mosaic') && count($mosaicImages) >= 3 ? partial('partials/media-mosaic', ['images' => $mosaicImages]) : '';
section_begin('home', 'vision_mission', ['class' => 'home-mission', 'labelledby' => 'mission-title']);
?>
    <div class="split split--start home-mission__grid<?= $vmStack === '' ? ' home-mission__grid--solo' : '' ?>">
        <?= $vmStack ?>
        <div>
            <?= partial('partials/section-head', ['eyebrow' => content('home', 'vision_mission.eyebrow'), 'title' => content('home', 'vision_mission.title'), 'id' => 'mission-title', 'class' => 'mb-0']) ?>
            <div class="home-mission__cards mt-5">
                <?= partial('partials/feature-card', ['class' => 'feature-card--tint feature-card--row', 'icon' => 'eye', 'title' => content('home', 'vision_mission.vision_title'), 'text' => content('home', 'vision_mission.vision_text')]) ?>
                <?= partial('partials/feature-card', ['class' => 'feature-card--tint feature-card--row', 'icon' => 'target', 'title' => content('home', 'vision_mission.mission_title'), 'text' => content('home', 'vision_mission.mission_text')]) ?>
            </div>
        </div>
    </div>
    <?php if ($vmRows || $quote !== ''): ?>
        <div class="home-mission__lower">
            <?php if ($vmRows): ?>
                <div class="home-mission__rows">
                    <?php foreach ($vmRows as $r): ?>
                        <?= partial('partials/feature-card', ['class' => 'feature-card--row feature-card--plain', 'icon' => $r['icon'] ?? '', 'title' => $r['title'], 'text' => $r['text'] ?? '']) ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <?php if ($quote !== ''): ?>
                <?= partial('partials/quote-card', [
                    'variant' => 'dark',
                    'quote'   => $quote,
                    'name'    => (string) setting('founder_name', ''),
                    'role'    => (string) content('home', 'founder.quote_role'),
                    'image'   => ['src' => $founderImg['src'], 'alt' => $founderImg['alt']],
                    'href'    => $founderOn ? '#founder' : '',
                ]) ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    <?php if ($mosaic !== ''): ?><div class="home-mission__mosaic"><?= $mosaic ?></div><?php endif; ?>
<?php section_end(); ?>

<?php /* ------------------------------------------------------------------ 6. FEATURED PROGRAMS & PROJECTS (carousel; round focus-area chip, no figures) */ ?>
<?php
$cardCta = trim((string) content('home', 'projects.card_cta'));
$cards = [];
foreach ($featured as $f) {
    $row = $f['row'];
    if ($f['kind'] === 'program') {
        $cards[] = partial('partials/program-card', ['program' => $row, 'cta' => $cardCta !== '' ? $cardCta : null]);
    } elseif ($f['kind'] === 'project') {
        $cards[] = partial('partials/project-card', ['project' => $row, 'cta' => $cardCta !== '' ? $cardCta : null]);
    } else {
        ob_start(); ?>
        <article class="card" data-reveal>
            <?php if ($row['image'] !== ''): ?><div class="card__media"><img src="<?= e($row['image']) ?>" alt="<?= e($row['image_alt']) ?>" loading="lazy"></div><?php endif; ?>
            <div class="card__body">
                <?php if (trim($row['tagline']) !== ''): ?><div class="card__meta"><span><?= e($row['tagline']) ?></span></div><?php endif; ?>
                <h3 class="card__title"><?php if ($row['href'] !== ''): ?><a class="card__stretch" href="<?= e($row['href']) ?>"><?= e($row['title']) ?></a><?php else: ?><?= e($row['title']) ?><?php endif; ?></h3>
                <?php if (trim($row['summary']) !== ''): ?><p class="card__text"><?= e($row['summary']) ?></p><?php endif; ?>
                <?php if ($row['href'] !== '' && $cardCta !== ''): ?><div class="card__footer"><span class="btn btn--cta btn--caps btn--sm"><?= e($cardCta) ?></span></div><?php endif; ?>
            </div>
        </article>
        <?php $cards[] = (string) ob_get_clean();
    }
}
if ($cards):
    section_begin('home', 'projects', ['class' => 'section--tint home-featured', 'labelledby' => 'featured-title']);
    echo partial('partials/carousel', [
        'items'   => $cards,
        'label'   => strip_highlight((string) content('home', 'projects.title')),
        'eyebrow' => content('home', 'projects.eyebrow'),
        'title'   => content('home', 'projects.title'),
        'lead'    => content('home', 'projects.intro'),
        'id'      => 'featured',
        'foot'    => $pill(content_btn('home', 'projects.cta'), 'btn--primary btn--caps'),
    ]);
    section_end();
endif;
?>

<?php /* ------------------------------------------------------------------ 7. DONATION PANEL (pledge / redirect, never a payment form; wording lives in Site-wide content) */ ?>
<?php
$dpOverrides = ['images' => [content_img('home', 'donate_panel.image1'), content_img('home', 'donate_panel.image2')]];
foreach (['eyebrow', 'title', 'text'] as $k) {
    $v = trim((string) content('home', 'donate_panel.' . $k));
    if ($v !== '') {
        $dpOverrides[$k] = $v;
    }
}
$dpHtml = section_on('global', 'donate_panel') ? partial('partials/donate-panel', $dpOverrides) : '';
if (trim($dpHtml) !== ''):
    section_begin('home', 'donate_panel', ['class' => 'home-donate']);
    echo $dpHtml;
    section_end();
endif;
?>

<?php /* ------------------------------------------------------------------ 8. IMPACT STORIES (consented stories only; hidden when none) */ ?>
<?php
$storyCards = array_values(array_filter(array_map(static fn(array $s): string => partial('partials/quote-card', ['story' => $s]), $stories), static fn(string $h): bool => trim($h) !== ''));
if ($storyCards):
    section_begin('home', 'stories', ['class' => 'section--cool home-stories', 'labelledby' => 'stories-title']);
    $storiesFoot = $pill(content_btn('home', 'stories.cta'), 'btn--primary btn--caps');
    if (count($storyCards) > 3) {
        echo partial('partials/carousel', ['items' => $storyCards, 'label' => strip_highlight((string) content('home', 'stories.title')), 'eyebrow' => content('home', 'stories.eyebrow'), 'title' => content('home', 'stories.title'), 'lead' => content('home', 'stories.lead'), 'id' => 'stories', 'foot' => $storiesFoot]);
    } else {
        echo partial('partials/section-head', ['eyebrow' => content('home', 'stories.eyebrow'), 'title' => content('home', 'stories.title'), 'lead' => content('home', 'stories.lead'), 'align' => 'center', 'id' => 'stories-title']);
        ?>
        <div class="cards-center"><?= implode('', $storyCards) ?></div>
        <?php if ($storiesFoot !== ''): ?><p class="section__footer"><?= $storiesFoot ?></p><?php endif;
    }
    section_end();
endif;
?>

<?php /* ------------------------------------------------------------------ 9. FAQ (photo + accordion, first item open) */ ?>
<?php
if ($faqs):
    section_begin('home', 'faq', ['class' => 'section--tint home-faq']);
    echo partial('partials/faq-split', [
        'faqs'    => $faqs,
        'image'   => content_img('home', 'faq.image'),
        'eyebrow' => content('home', 'faq.eyebrow'),
        'title'   => content('home', 'faq.title'),
        'lead'    => content('home', 'faq.lead'),
        'prefix'  => 'home-faq',
    ]);
    section_end();
endif;
?>

<?php /* ------------------------------------------------------------------ 10a. WHY DIGITAL INCLUSION MATTERS (two-column text + highlighted closing statement) */ ?>
<?php
$whyImg = content_img('home', 'why_digital.image');
$whyClosing = trim((string) content('home', 'why_digital.closing'));
$whyHead = partial('partials/section-head', ['eyebrow' => content('home', 'why_digital.eyebrow'), 'title' => content('home', 'why_digital.title'), 'lead' => content('home', 'why_digital.lead'), 'id' => 'why-title', 'align' => $whyImg['has'] ? 'left' : 'center']);
section_begin('home', 'why_digital', ['class' => 'home-why', 'labelledby' => 'why-title'] + ($whyImg['has'] ? [] : ['container' => 'container--narrow']));
?>
    <?php if ($whyImg['has']): ?>
        <div class="split split--start">
            <div data-reveal>
                <?= $whyHead ?>
                <div class="prose"><?= content_html('home', 'why_digital.body') ?></div>
            </div>
            <figure class="split__media split__media--frame" data-reveal><?= photo_tag($whyImg) ?></figure>
        </div>
    <?php else: ?>
        <div data-reveal><?= $whyHead ?></div>
        <div class="prose prose--wide home-why__body" data-reveal><?= content_html('home', 'why_digital.body') ?></div>
    <?php endif; ?>
    <?php if ($whyClosing !== ''): ?><div class="callout mt-6" data-reveal><?= nl2p($whyClosing) ?></div><?php endif; ?>
<?php section_end(); ?>

<?php /* ------------------------------------------------------------------ 10b. FOUNDER STORY (arch portrait + story) */ ?>
<?php
$fBtn = content_btn('home', 'founder.cta');
$fCaption = trim((string) content('home', 'founder.caption'));
section_begin('home', 'founder', ['class' => 'section--cool home-founder', 'labelledby' => 'founder-title']);
?>
    <div class="split split--start<?= $founderImg['has'] ? '' : ' home-founder__solo' ?>">
        <?php if ($founderImg['has']): ?>
            <figure class="split__media split__media--arch home-founder__photo" data-reveal>
                <?= photo_tag($founderImg) ?>
                <?php if ($fCaption !== ''): ?><figcaption><?= e($fCaption) ?></figcaption><?php endif; ?>
            </figure>
        <?php endif; ?>
        <div data-reveal>
            <?= partial('partials/section-head', ['eyebrow' => content('home', 'founder.eyebrow'), 'title' => content('home', 'founder.title'), 'id' => 'founder-title', 'class' => 'mb-0']) ?>
            <div class="prose mt-4"><?= content_html('home', 'founder.body') ?></div>
            <?php if ($fBtn['has']): ?>
                <p class="mt-5"><a class="btn btn--outline btn--caps" href="<?= e($fBtn['url']) ?>"<?= $fBtn['external'] ? ' target="_blank" rel="noopener noreferrer"' : '' ?>><?= e($fBtn['label']) ?> <?= icon($fBtn['external'] ? 'external-link' : 'arrow-right', $fBtn['external'] ? 'icon--sm' : 'icon--arrow') ?></a></p>
            <?php endif; ?>
        </div>
    </div>
<?php section_end(); ?>

<?php /* ------------------------------------------------------------------ 10c. OUR IMPACT (deep navy band) */ ?>
<?php
$impImg = content_img('home', 'impact.image');
section_begin('home', 'impact', ['class' => 'section--navy home-impact', 'labelledby' => 'impact-title']);
?>
    <?php if ($impImg['has']): ?>
        <div class="split">
            <div data-reveal>
                <?= partial('partials/section-head', ['eyebrow' => content('home', 'impact.eyebrow'), 'title' => content('home', 'impact.title'), 'id' => 'impact-title', 'class' => 'mb-0']) ?>
                <div class="prose mt-4"><?= content_html('home', 'impact.body') ?></div>
            </div>
            <figure class="split__media" data-reveal><?= photo_tag($impImg) ?></figure>
        </div>
    <?php else: ?>
        <div class="home-impact__inner" data-reveal>
            <?= partial('partials/section-head', ['eyebrow' => content('home', 'impact.eyebrow'), 'title' => content('home', 'impact.title'), 'id' => 'impact-title', 'align' => 'center', 'class' => 'mb-0']) ?>
            <div class="prose prose--wide mt-4"><?= content_html('home', 'impact.body') ?></div>
        </div>
    <?php endif; ?>
<?php section_end(); ?>

<?php /* ------------------------------------------------------------------ 10d. BACKGROUND & RATIONALE */ ?>
<?php
$bgClosing = trim((string) content('home', 'background.closing'));
section_begin('home', 'background', ['class' => 'home-background', 'container' => 'container--narrow', 'labelledby' => 'background-title']);
?>
    <div data-reveal><?= partial('partials/section-head', ['eyebrow' => content('home', 'background.eyebrow'), 'title' => content('home', 'background.title'), 'id' => 'background-title', 'class' => 'mb-0']) ?></div>
    <div class="prose prose--wide mt-5" data-reveal><?= content_html('home', 'background.body') ?></div>
    <?php if ($bgClosing !== ''): ?><div class="callout mt-6" data-reveal><?= nl2p($bgClosing) ?></div><?php endif; ?>
<?php section_end(); ?>

<?php /* ------------------------------------------------------------------ 10e. CONTACT TEASER (the newsletter form lives in the footer) */ ?>
<?php
$cbtn = content_btn('home', 'contact.cta');
$emails = array_values(array_filter([(string) setting('email_primary', ''), (string) setting('email_info', '')]));
$phones = array_values(array_filter([(string) setting('phone_1', ''), (string) setting('phone_2', '')]));
$wa = whatsapp_url();
$waDigits = preg_replace('/[^0-9]/', '', (string) preg_replace('~^https?://wa\.me/~i', '', (string) setting('whatsapp', ''))) ?? '';
$waText = strlen($waDigits) >= 7 ? '+' . $waDigits : $wa;
$where = implode(', ', array_filter([trim((string) setting('address', '')), $city]));
section_begin('home', 'contact', ['class' => 'section--tint home-contact', 'labelledby' => 'contact-title']);
?>
    <div class="home-contact__box" data-reveal>
        <div class="home-contact__text">
            <h2 id="contact-title"><?= highlight((string) content('home', 'contact.title')) ?></h2>
            <?php if (trim((string) content('home', 'contact.text')) !== ''): ?><p class="lead"><?= e(content('home', 'contact.text')) ?></p><?php endif; ?>
            <?php if ($cbtn['has']): ?><p class="mt-5"><a class="btn btn--cta btn--caps btn--lg" href="<?= e($cbtn['url']) ?>"><?= e($cbtn['label']) ?></a></p><?php endif; ?>
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
