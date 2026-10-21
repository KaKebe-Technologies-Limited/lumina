<?php
/**
 * HOME: a page of HIGHLIGHTS that lead to the detail pages. EVERY string comes from content('home', ...) (registry app/config/pages/home.php,
 * editable in Admin > Pages > Home), from the database (programs, projects, stats, opportunities, stories, Academy numbers) or from Settings.
 * Nothing is hard-coded or invented: data blocks print nothing when their table is empty.
 * Vars (HomeController): $stats, $programs, $projects, $fallbackCards, $stories, $academy (numbers|null), $opportunities.   Layout language: docs/DESIGN.md.
 *
 * Section order (each one has an on/off switch in the registry):
 *   hero > quick links > statistics > programs > projects > LAF Academy > opportunities > get involved > about us (short) > stories > newsletter > contact
 * The long narrative (challenge, why digital inclusion, founder story, commitment, background) is shown on the About page.
 */

use App\Controllers\Web\HomeSupport as H;

/** A pill link as safe html. */
$pill = static function (array $b, string $class): string {
    return $b['has'] ? '<a class="btn ' . e($class) . '" href="' . e($b['url']) . '"' . ($b['external'] ? ' target="_blank" rel="noopener noreferrer"' : '') . '>' . e($b['label']) . '</a>' : '';
};
/** Rows of a repeater that have a title. */
$titled = static fn(array $rows): array => array_values(array_filter($rows, static fn(array $r): bool => trim((string) ($r['title'] ?? '')) !== ''));

$heroOn = section_on('home', 'hero');
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

<?php /* ------------------------------------------------------------------ 2. QUICK LINKS (four tiles that overlap the hero) */ ?>
<?php
$quick = $titled(content_list('home', 'quick.items'));
if ($quick):
    section_begin('home', 'quick', ['class' => 'home-quick', 'attrs' => ['aria-label' => 'Quick links']]);
?>
    <ul class="home-quick__grid">
        <?php foreach ($quick as $q): $qHref = link_url((string) ($q['url'] ?? '')); ?>
            <li>
                <a class="home-tile" href="<?= e($qHref !== '' ? $qHref : url('')) ?>" data-reveal>
                    <span class="home-tile__icon"><?= icon(safe_icon($q['icon'] ?? '', 'check-circle'), 'icon--lg') ?></span>
                    <span class="home-tile__body"><strong class="home-tile__title"><?= e($q['title']) ?></strong><span class="home-tile__text"><?= e($q['text'] ?? '') ?></span></span>
                    <span class="home-tile__go" aria-hidden="true">Explore <?= icon('arrow-right', 'icon--arrow') ?></span>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
<?php section_end(); endif; ?>

<?php /* ------------------------------------------------------------------ 2b. STATISTICS (numbers from Admin > Stats) */ ?>
<?php
if ($stats):
    section_begin('home', 'stats', ['class' => 'section--sm home-stats']);
    echo partial('partials/stat-band', ['stats' => $stats]);
    section_end();
endif;
?>

<?php /* ------------------------------------------------------------------ 3. WHAT WE DO (program highlights) */ ?>
<?php
$cardCta = trim((string) content('home', 'programs.card_cta'));
$progBtn = $pill(content_btn('home', 'programs.cta'), 'btn--primary btn--caps');
if ($programs):
    section_begin('home', 'programs', ['class' => 'home-programs', 'labelledby' => 'programs-title']);
    echo partial('partials/section-head', ['eyebrow' => content('home', 'programs.eyebrow'), 'title' => content('home', 'programs.title'), 'lead' => content('home', 'programs.intro'), 'align' => 'center', 'id' => 'programs-title']);
?>
    <div class="home-cards">
        <?php foreach ($programs as $p): ?><?= partial('partials/program-card', ['program' => $p, 'cta' => $cardCta !== '' ? $cardCta : null]) ?><?php endforeach; ?>
    </div>
    <?php if ($progBtn !== ''): ?><p class="section__footer"><?= $progBtn ?></p><?php endif; ?>
<?php section_end(); endif; ?>

<?php /* ------------------------------------------------------------------ 3b. PROJECTS IN FOCUS */ ?>
<?php
$projCta = trim((string) content('home', 'projects.card_cta'));
$projBtn = $pill(content_btn('home', 'projects.cta'), 'btn--primary btn--caps');
$projectStatusLabels = [
    'planned'   => (string) content('projects', 'list.status_planned'),
    'ongoing'   => (string) content('projects', 'list.status_ongoing'),
    'completed' => (string) content('projects', 'list.status_completed'),
];
if ($projects || $fallbackCards):
    section_begin('home', 'projects', ['class' => 'section--tint home-projects', 'labelledby' => 'projects-title']);
    echo partial('partials/section-head', ['eyebrow' => content('home', 'projects.eyebrow'), 'title' => content('home', 'projects.title'), 'lead' => content('home', 'projects.intro'), 'align' => 'center', 'id' => 'projects-title']);
?>
    <div class="home-cards">
        <?php foreach ($projects as $p): $pHref = url('projects/' . $p['slug']); $pStatus = (string) ($p['status'] ?? ''); $pStatusText = (string) ($projectStatusLabels[$pStatus] ?? ''); ?>
            <article class="home-focus" data-reveal>
                <div class="home-focus__top">
                    <?php if (trim((string) ($p['program_title'] ?? '')) !== ''): ?><span class="home-focus__program"><?= e($p['program_title']) ?></span><?php endif; ?>
                    <?php if ($pStatusText !== ''): ?><span class="badge badge--<?= e(['planned' => 'info', 'ongoing' => 'success', 'completed' => 'neutral'][$pStatus] ?? 'neutral') ?>"><?= e($pStatusText) ?></span><?php endif; ?>
                </div>
                <h3 class="home-focus__title"><a href="<?= e($pHref) ?>"><?= e($p['title']) ?></a></h3>
                <?php if (trim((string) ($p['summary'] ?? '')) !== ''): ?><p class="home-focus__text"><?= e(excerpt((string) $p['summary'], 130)) ?></p><?php endif; ?>
                <?php if ($projCta !== ''): ?><span class="home-focus__go" aria-hidden="true"><?= e($projCta) ?> <?= icon('arrow-right', 'icon--arrow') ?></span><?php endif; ?>
            </article>
        <?php endforeach; ?>
        <?php foreach ($fallbackCards as $row): ?>
            <article class="card" data-reveal>
                <?php if ($row['image'] !== ''): ?><div class="card__media"><img src="<?= e($row['image']) ?>" alt="<?= e($row['image_alt']) ?>" loading="lazy"></div><?php endif; ?>
                <div class="card__body">
                    <?php if (trim($row['tagline']) !== ''): ?><div class="card__meta"><span><?= e($row['tagline']) ?></span></div><?php endif; ?>
                    <h3 class="card__title"><?php if ($row['href'] !== ''): ?><a class="card__stretch" href="<?= e($row['href']) ?>"><?= e($row['title']) ?></a><?php else: ?><?= e($row['title']) ?><?php endif; ?></h3>
                    <?php if (trim($row['summary']) !== ''): ?><p class="card__text"><?= e($row['summary']) ?></p><?php endif; ?>
                    <?php if ($row['href'] !== '' && $projCta !== ''): ?><div class="card__footer"><span class="btn btn--cta btn--caps btn--sm"><?= e($projCta) ?></span></div><?php endif; ?>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
    <?php if ($projBtn !== ''): ?><p class="section__footer"><?= $projBtn ?></p><?php endif; ?>
<?php section_end(); endif; ?>

<?php /* ------------------------------------------------------------------ 4. LAF ACADEMY (live numbers + shortcuts) */ ?>
<?php
if ($academy !== null):
    $acImg = content_img('home', 'academy.image');
    $acLinks = $titled(content_list('home', 'academy.links'));
    $acB1 = content_btn('home', 'academy.cta');
    $acB2 = content_btn('home', 'academy.cta2');
    $acFacts = array_values(array_filter([
        ['n' => (int) $academy['courses'], 'l' => (int) $academy['courses'] === 1 ? 'free course' : 'free courses'],
        ['n' => (int) $academy['lessons'], 'l' => (int) $academy['lessons'] === 1 ? 'lesson' : 'lessons'],
        ['n' => (int) $academy['challenges'], 'l' => (int) $academy['challenges'] === 1 ? 'open challenge' : 'open challenges'],
    ], static fn(array $f): bool => $f['n'] > 0));
    section_begin('home', 'academy', ['bleed' => true, 'class' => 'home-academy-wrap']);
?>
    <div class="home-academy"<?= $acImg['has'] ? ' style="--ac-photo: url(\'' . e($acImg['src']) . '\')"' : '' ?>>
        <div class="container home-academy__inner">
            <div class="home-academy__copy" data-reveal>
                <?php if (trim((string) content('home', 'academy.eyebrow')) !== ''): ?><span class="eyebrow"><?= e(content('home', 'academy.eyebrow')) ?></span><?php endif; ?>
                <h2 class="home-academy__title"><?= highlight((string) content('home', 'academy.title')) ?></h2>
                <p class="home-academy__text"><?= e(content('home', 'academy.text')) ?></p>
                <p class="home-academy__actions">
                    <?php if ($acB1['has']): ?><a class="btn btn--cta btn--caps btn--lg" href="<?= e($acB1['url']) ?>"><?= e($acB1['label']) ?></a><?php endif; ?>
                    <?php if ($acB2['has']): ?><a class="btn btn--white btn--caps btn--lg" href="<?= e($acB2['url']) ?>"><?= e($acB2['label']) ?></a><?php endif; ?>
                </p>
            </div>
            <div class="home-academy__side" data-reveal>
                <?php if ($acFacts): ?>
                    <ul class="home-academy__facts">
                        <?php foreach ($acFacts as $f): ?><li><b data-count-to="<?= (int) $f['n'] ?>"><?= (int) $f['n'] ?></b><span><?= e($f['l']) ?></span></li><?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                <?php if ($acLinks): ?>
                    <ul class="home-academy__links">
                        <?php foreach ($acLinks as $l): ?>
                            <li><a href="<?= e(link_url((string) ($l['url'] ?? ''))) ?>"><?= icon(safe_icon($l['icon'] ?? '', 'check-circle'), 'icon--sm') ?> <span><?= e($l['title']) ?></span> <?= icon('arrow-right', 'icon--arrow') ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php section_end(); endif; ?>

<?php /* ------------------------------------------------------------------ 5. OPPORTUNITIES (the newest open ones) */ ?>
<?php
$oppBtn = $pill(content_btn('home', 'opportunities.cta'), 'btn--primary btn--caps');
if ($opportunities):
    section_begin('home', 'opportunities', ['class' => 'home-opps', 'labelledby' => 'opps-title']);
    echo partial('partials/section-head', ['eyebrow' => content('home', 'opportunities.eyebrow'), 'title' => content('home', 'opportunities.title'), 'lead' => content('home', 'opportunities.intro'), 'align' => 'center', 'id' => 'opps-title']);
?>
    <div class="home-cards">
        <?php foreach ($opportunities as $o): ?><?= partial('web/opportunities/_card', ['o' => $o]) ?><?php endforeach; ?>
    </div>
    <?php if ($oppBtn !== ''): ?><p class="section__footer"><?= $oppBtn ?></p><?php endif; ?>
<?php section_end(); endif; ?>

<?php /* ------------------------------------------------------------------ 6. GET INVOLVED (volunteer, partner, donate) */ ?>
<?php
$involved = $titled(content_list('home', 'involved.items'));
if ($involved):
    section_begin('home', 'involved', ['class' => 'section--tint home-involve', 'labelledby' => 'involve-title']);
    echo partial('partials/section-head', ['eyebrow' => content('home', 'involved.eyebrow'), 'title' => content('home', 'involved.title'), 'align' => 'center', 'id' => 'involve-title']);
?>
    <div class="home-involve__grid">
        <?php foreach ($involved as $i): $iHref = link_url((string) ($i['url'] ?? '')); ?>
            <article class="home-involve__card" data-reveal>
                <span class="home-involve__icon"><?= icon(safe_icon($i['icon'] ?? '', 'check-circle'), 'icon--lg') ?></span>
                <h3 class="home-involve__title"><?= e($i['title']) ?></h3>
                <p class="home-involve__text"><?= e($i['text'] ?? '') ?></p>
                <?php if ($iHref !== '' && trim((string) ($i['button'] ?? '')) !== ''): ?><p class="home-involve__cta"><a class="btn btn--outline btn--caps btn--sm" href="<?= e($iHref) ?>"><?= e($i['button']) ?> <?= icon('arrow-right', 'icon--arrow') ?></a></p><?php endif; ?>
            </article>
        <?php endforeach; ?>
    </div>
<?php section_end(); endif; ?>

<?php /* ------------------------------------------------------------------ 7. ABOUT US (short: vision, mission, founder quote; the full story is on the About page) */ ?>
<?php
$vmStack = partial('partials/photo-stack', ['images' => [content_img('home', 'vision_mission.image1'), content_img('home', 'vision_mission.image2')]]);
$quote = trim((string) content('home', 'founder.quote'));
$founderImg = content_img('home', 'founder.image');
$vmB1 = content_btn('home', 'vision_mission.cta');
$vmB2 = content_btn('home', 'vision_mission.cta2');
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
            <?php if ($vmB1['has'] || $vmB2['has']): ?>
                <p class="mt-5 home-mission__actions">
                    <?php if ($vmB1['has']): ?><a class="btn btn--cta btn--caps" href="<?= e($vmB1['url']) ?>"><?= e($vmB1['label']) ?></a><?php endif; ?>
                    <?php if ($vmB2['has']): ?><a class="btn btn--outline btn--caps" href="<?= e($vmB2['url']) ?>"><?= e($vmB2['label']) ?> <?= icon('arrow-right', 'icon--arrow') ?></a><?php endif; ?>
                </p>
            <?php endif; ?>
        </div>
    </div>
    <?php if ($quote !== ''): ?>
        <div class="home-mission__lower">
            <?= partial('partials/quote-card', [
                'variant' => 'dark',
                'quote'   => $quote,
                'name'    => (string) setting('founder_name', ''),
                'role'    => (string) content('home', 'founder.quote_role'),
                'image'   => ['src' => $founderImg['src'], 'alt' => $founderImg['alt']],
                'href'    => $vmB1['has'] ? $vmB1['url'] : '',
            ]) ?>
        </div>
    <?php endif; ?>
<?php section_end(); ?>

<?php /* ------------------------------------------------------------------ 8. IMPACT STORIES (consented stories only; hidden when none) */ ?>
<?php
$storyCards = array_values(array_filter(array_map(static fn(array $s): string => partial('partials/quote-card', ['story' => $s]), $stories), static fn(string $h): bool => trim($h) !== ''));
if ($storyCards):
    section_begin('home', 'stories', ['class' => 'section--cool home-stories', 'labelledby' => 'stories-title']);
    echo partial('partials/section-head', ['eyebrow' => content('home', 'stories.eyebrow'), 'title' => content('home', 'stories.title'), 'lead' => content('home', 'stories.lead'), 'align' => 'center', 'id' => 'stories-title']);
    ?>
    <div class="cards-center"><?= implode('', $storyCards) ?></div>
    <?php $storiesFoot = $pill(content_btn('home', 'stories.cta'), 'btn--primary btn--caps'); ?>
    <?php if ($storiesFoot !== ''): ?><p class="section__footer"><?= $storiesFoot ?></p><?php endif;
    section_end();
endif;
?>

<?php /* ------------------------------------------------------------------ 9. LATEST NEWSLETTER (newest published issue; hidden when there is none) */ ?>
<?= partial('web/newsletter/_home-teaser') ?>

<?php /* ------------------------------------------------------------------ 10. CONTACT TEASER (the newsletter form lives in the footer) */ ?>
<?php
$city = trim((string) setting('city_country', ''));
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
