<?php
declare(strict_types=1);
/**
 * The Northern Uganda story as a FLOW: seven steps joined by curved arrows (scss/pages/_nu-story.scss), with a button to the full interactive story.
 * Content: app/config/story/northern-uganda.php ("short" text of each chapter). No variables.
 */
$nuStory = (array) require APP_PATH . '/config/story/northern-uganda.php';
$nuChapters = (array) $nuStory['chapters'];
?>
<section class="section nu-flow" aria-labelledby="nu-flow-title">
    <div class="container">
        <header class="nu-flow__head" data-reveal>
            <span class="nu-flow__eyebrow"><?= icon('map-pin', 'icon--sm') ?> The story behind our work</span>
            <h2 class="nu-flow__title" id="nu-flow-title">How a generation in Northern Uganda was left behind</h2>
            <p class="nu-flow__lead">It did not happen overnight. It happened one stolen childhood, one closed school and one empty clinic at a time. Follow the story, from the first rebellion to the digital gap that young people face today.</p>
        </header>

        <ol class="nu-flow__steps">
            <?php foreach ($nuChapters as $n => $c): $src = (string) $c['image'] !== '' ? img_url((string) $c['image']) : ''; $side = $n % 2 ? 'right' : 'left'; ?>
                <li class="nu-step nu-step--<?= $side ?> nu-step--<?= e((string) $c['tone']) ?>" data-reveal>
                    <div class="nu-step__card">
                        <span class="nu-step__year"><?= e($c['year']) ?></span>
                        <span class="nu-step__icon"><?= icon((string) $c['icon']) ?></span>
                        <h3 class="nu-step__title"><?= e($c['tag']) ?></h3>
                        <p class="nu-step__text"><?= e($c['short']) ?></p>
                        <?php if ($src !== ''): ?><img class="nu-step__photo" src="<?= e($src) ?>" alt="<?= e((string) $c['image_alt']) ?>" loading="lazy" decoding="async"><?php endif; ?>
                    </div>
                    <?php if ($n < count($nuChapters) - 1): ?>
                        <svg class="nu-step__arrow nu-step__arrow--<?= $side ?>" viewBox="0 0 120 70" aria-hidden="true" focusable="false">
                            <path d="<?= $side === 'left' ? 'M10 6 C 70 6, 100 20, 100 50' : 'M110 6 C 50 6, 20 20, 20 50' ?>" fill="none" stroke="currentColor" stroke-width="3" stroke-dasharray="7 7" stroke-linecap="round"/>
                            <path d="<?= $side === 'left' ? 'M88 42 L100 58 L112 42' : 'M8 42 L20 58 L32 42' ?>" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ol>

        <div class="nu-flow__more" data-reveal>
            <p>There is more to this story than a timeline: the names, the numbers, the sources and what it means for one child.</p>
            <a class="btn btn--cta btn--caps" href="<?= e(url('northern-uganda-story')) ?>"><?= icon('book-open', 'icon--sm') ?> Read the full story <?= icon('arrow-right', 'icon--arrow') ?></a>
            <a class="btn btn--outline btn--caps" href="<?= e(url('fund-a-project')) ?>"><?= icon('heart', 'icon--sm') ?> Fund a project</a>
        </div>
    </div>
</section>
