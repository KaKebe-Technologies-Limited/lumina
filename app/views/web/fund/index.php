<?php
/** FUND A PROJECT (the hub, /fund-a-project). Vars: $projects, $gets. */
$ready = array_values(array_filter($projects, static fn(array $p): bool => $p['status'] === 'proposal'));
$total = array_sum(array_map(static fn(array $p): float => (float) $p['budget']['usd'], $ready));
?>
<header class="fund-banner">
    <div class="container">
        <?= partial('partials/breadcrumbs', ['breadcrumbs' => $breadcrumbs ?? []]) ?>
        <span class="eyebrow">Fund a project</span>
        <h1 class="fund-banner__title">Fund a project that changes a young person's future</h1>
        <p class="fund-banner__lead">Lumina Africa Foundation has <?= count($ready) ?> costed project proposals for Gulu and Pader in Northern Uganda: digital skills, schooling, a greener land and healthier families. Pick the one you care about, then fund all of it, a launch package, or a single contribution: a laptop, a scholar, 100 trees, a clinic day.</p>
        <div class="fund-banner__actions">
            <a class="btn btn--cta btn--caps" href="#projects">See the projects</a>
            <a class="btn btn--photo btn--caps" href="<?= e(url('donate')) ?>">Give without choosing a project</a>
        </div>
        <ul class="fund-banner__stats">
            <li><b><?= count($ready) ?></b><span>costed proposals</span></li>
            <li><b>From USD 5</b><span>one hygiene kit or sanitary kit</span></li>
            <li><b>36 months</b><span>each project, 2027 to 2029</span></li>
            <li><b>Every shilling</b><span>traceable, audited, reported</span></li>
        </ul>
    </div>
</header>

<section class="section" id="projects" aria-labelledby="fund-projects">
    <div class="container">
        <header class="section-head section-head--center"><span class="eyebrow">Our proposed projects</span><h2 class="section-head__title" id="fund-projects">Choose the change you want to fund</h2>
            <p class="section-head__lead">Each project has its own case, budget and price list. Together they ask for about USD <?= e(number_format($total)) ?>, and any piece of it is a way to say yes.</p></header>
        <ul class="fund-grid">
            <?php foreach ($projects as $p): ?><li><?= partial('partials/fund-card', ['p' => $p]) ?></li><?php endforeach; ?>
        </ul>
    </div>
</section>

<section class="section section--tint" aria-labelledby="fund-how">
    <div class="container">
        <header class="section-head section-head--center"><span class="eyebrow">How it works</span><h2 class="section-head__title" id="fund-how">Three steps, no guesswork</h2></header>
        <ol class="fund-steps">
            <li><span>1</span><h3>Pick a project</h3><p>Read the case and the budget of the project that matches what you care about.</p></li>
            <li><span>2</span><h3>Choose your contribution</h3><p>Fund the whole programme, a Year 1 launch package, a co-funding share, one priced contribution, or any amount.</p></li>
            <li><span>3</span><h3>Send your pledge</h3><p>We contact you to complete the gift by bank transfer, mobile money or PayPal, and we report back to you.</p></li>
        </ol>
    </div>
</section>

<section class="section" aria-labelledby="fund-trust">
    <div class="container">
        <header class="section-head section-head--center"><span class="eyebrow">What our funding partners receive</span><h2 class="section-head__title" id="fund-trust">You will always know where your gift went</h2></header>
        <div class="fund-gets">
            <?php foreach ($gets as $g): ?>
                <article class="fund-gets__card"><span class="fund-gets__icon"><?= icon($g['icon'], 'icon--lg') ?></span><h3><?= e($g['title']) ?></h3><p><?= e($g['text']) ?></p></article>
            <?php endforeach; ?>
        </div>
        <p class="fund-note">These are proposed projects seeking funding. Lumina Africa Foundation is registered (GSD/R 1545) and commits to report the status, progress and verified results of every project it delivers.</p>
    </div>
</section>

<section class="section section--navy fund-cta" aria-labelledby="fund-cta">
    <div class="container container--narrow">
        <h2 id="fund-cta">Prefer to talk first?</h2>
        <p>We are happy to walk you through any proposal, share the full budget and arrange a visit to Gulu or Pader.</p>
        <p><a class="btn btn--cta btn--caps" href="<?= e(url('contact')) ?>">Contact us</a> <a class="btn btn--photo btn--caps" href="<?= e(url('involvement/ambassadors')) ?>">Become an ambassador</a></p>
    </div>
</section>
