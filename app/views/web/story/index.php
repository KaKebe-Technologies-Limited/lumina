<?php
declare(strict_types=1);
/**
 * The Northern Uganda story: a long, interactive read. A progress bar, a chapter menu, seven chapters (story, key facts, an expandable "go deeper"), a donor call and the sources.
 * Vars: $story (app/config/story/northern-uganda.php). The expanders are plain <details>; assets/js/nu-story.js adds "expand all" and the reading progress bar.
 */
$this->start('scripts'); ?><script src="<?= e(asset('js/nu-story.js')) ?>" defer></script><?php $this->end();
$chapters = (array) $story['chapters'];
?>
<div class="nu-progress" aria-hidden="true"><span class="nu-progress__bar" data-nu-progress></span></div>

<section class="nu-hero">
    <div class="container">
        <span class="nu-hero__eyebrow"><?= icon('map-pin', 'icon--sm') ?> The story behind our work</span>
        <h1 class="nu-hero__title">They were children when the war came. They should not be left behind when it is over.</h1>
        <p class="nu-hero__lead">For nearly two decades, conflict in Northern Uganda took children from their homes, families from their land and a generation from its classrooms. This is that story: what happened, what it cost, and where you come in.</p>
        <div class="nu-hero__actions">
            <a class="btn btn--cta btn--caps" href="#chapter-1"><?= icon('arrow-down', 'icon--sm') ?> Begin the story</a>
            <a class="btn btn--outline-light btn--caps" href="<?= e(url('fund-a-project')) ?>"><?= icon('heart', 'icon--sm') ?> Fund a project</a>
        </div>
    </div>
</section>

<nav class="nu-menu" aria-label="Chapters of the story">
    <div class="container nu-menu__inner">
        <?php foreach ($chapters as $n => $c): ?>
            <a class="nu-menu__link" href="#chapter-<?= $n + 1 ?>"><span><?= str_pad((string) ($n + 1), 2, '0', STR_PAD_LEFT) ?></span> <?= e($c['year']) ?></a>
        <?php endforeach; ?>
        <button class="nu-menu__toggle" type="button" data-nu-toggle aria-pressed="false"><?= icon('plus', 'icon--sm') ?> <span data-nu-toggle-label>Open all details</span></button>
    </div>
</nav>

<div class="nu-story">
    <div class="container nu-story__inner">
        <?php foreach ($chapters as $n => $c): $src = (string) $c['image'] !== '' ? img_url((string) $c['image']) : ''; $side = $n % 2 ? 'right' : 'left'; ?>
            <article class="nu-ch nu-ch--<?= e((string) $c['tone']) ?> nu-ch--<?= $side ?><?= $src === '' ? ' nu-ch--nophoto' : '' ?>" id="chapter-<?= $n + 1 ?>">
                <div class="nu-ch__media">
                    <?php if ($src !== ''): ?>
                        <figure class="nu-ch__photo"><img src="<?= e($src) ?>" alt="<?= e((string) $c['image_alt']) ?>" loading="lazy" decoding="async"></figure>
                    <?php else: ?>
                        <div class="nu-ch__art" aria-hidden="true"><?= icon((string) $c['icon']) ?><strong><?= e($c['year']) ?></strong></div>
                    <?php endif; ?>
                </div>
                <div class="nu-ch__text">
                    <span class="nu-ch__no" aria-hidden="true"><?= str_pad((string) ($n + 1), 2, '0', STR_PAD_LEFT) ?></span>
                    <span class="nu-ch__tag"><?= e($c['year']) ?> &middot; <?= e($c['tag']) ?></span>
                    <h2 class="nu-ch__title"><?= e($c['title']) ?></h2>
                    <?php foreach ((array) $c['story'] as $para): ?><p class="nu-ch__p"><?= e($para) ?></p><?php endforeach; ?>
                    <?php if (!empty($c['facts'])): ?>
                        <ul class="nu-ch__facts"><?php foreach ((array) $c['facts'] as $f): ?><li><?= e($f) ?></li><?php endforeach; ?></ul>
                    <?php endif; ?>
                    <?php if (trim((string) $c['deeper']) !== ''): ?>
                        <details class="nu-ch__more" data-nu-details>
                            <summary><?= icon('plus', 'icon--sm') ?> Go deeper</summary>
                            <p><?= e($c['deeper']) ?></p>
                        </details>
                    <?php endif; ?>
                </div>
            </article>
            <?php if ($n < count($chapters) - 1): ?>
                <div class="nu-ch__link" aria-hidden="true"><?= icon('arrow-down') ?></div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</div>

<section class="nu-call">
    <div class="container">
        <div class="nu-call__card">
            <h2 class="nu-call__title">You can write the next chapter</h2>
            <p class="nu-call__text">A young person in Northern Uganda who gets a computer, a trainer and a year of skills is no longer defined by what the war took. Fund a project, pick one contribution, and we will report back to you on exactly what it did.</p>
            <div class="nu-call__actions">
                <a class="btn btn--cta btn--caps" href="<?= e(url('fund-a-project')) ?>"><?= icon('heart', 'icon--sm') ?> Fund a project</a>
                <a class="btn btn--outline-light btn--caps" href="<?= e(url('donate')) ?>">Give without choosing</a>
            </div>
        </div>
    </div>
</section>

<section class="section nu-sources">
    <div class="container container--narrow">
        <h2 class="nu-sources__title">Where these facts come from</h2>
        <p class="nu-sources__lead">Figures are from the published sources below. Where sources differ, we say so ("at least", "an estimated").</p>
        <ul class="nu-sources__list">
            <?php foreach ((array) $story['sources'] as $s): ?><li><a href="<?= e($s[1]) ?>" target="_blank" rel="noopener noreferrer"><?= e($s[0]) ?> <?= icon('external-link', 'icon--sm') ?></a></li><?php endforeach; ?>
        </ul>
    </div>
</section>
