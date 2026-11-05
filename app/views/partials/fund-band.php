<?php
/**
 * "FUND A PROJECT" spotlight: our most important call to action. A navy section with the headline, four highlights (how many costed projects, what they ask, the smallest gift,
 * what a partner receives), one card per proposal with its cover, its launch package and its smallest gift, and a button to the hub. Used at the top of the home page and on the
 * Donate and Projects pages. Vars: $fundHome (bool, default false: the home page uses a bigger headline).
 */
$fh = \App\Services\Fund\FundProjects::highlights();
if ($fh['cards'] === []) {
    return;
}
$fundHome = !empty($fundHome);
$fmt = static fn(float $v): string => \App\Services\Fund\FundProjects::usdShort($v);
?>
<section class="section section--navy fund-band<?= $fundHome ? ' fund-band--home' : '' ?>" id="fund" aria-labelledby="fund-band-title">
    <div class="container">
        <header class="section-head section-head--on-dark section-head--center">
            <span class="eyebrow fund-band__eyebrow"><?= icon('heart', 'icon--sm') ?> Fund a project</span>
            <h2 class="section-head__title" id="fund-band-title">Fund a project. Change a young person's future.</h2>
            <p class="section-head__lead">Our costed proposals for Gulu and Pader are open for funding. Fund the whole project, a launch package, or one priced contribution: a scholar, 100 trees, a clinic day, a laptop.</p>
        </header>

        <ul class="fund-band__stats" aria-label="Fund a project in numbers">
            <li><b><?= (int) $fh['projects'] ?></b><span>costed projects ready to fund</span></li>
            <li><b><?= e($fmt($fh['total_usd'])) ?></b><span>needed to deliver them all</span></li>
            <li><b>From <?= e($fmt($fh['min_gift_usd'])) ?></b><span>one priced contribution</span></li>
            <li><b>100%</b><span>reported back to every funder</span></li>
        </ul>

        <ul class="fund-band__grid">
            <?php foreach ($fh['cards'] as $c): ?>
                <li>
                    <a class="fund-spot" href="<?= e(url('fund-a-project/' . $c['slug'])) ?>">
                        <span class="fund-spot__cover"><img src="<?= e(asset('img/projects/' . $c['cover'] . '-card.jpg')) ?>" alt="" width="800" height="450" loading="lazy"></span>
                        <span class="fund-spot__body">
                            <strong class="fund-spot__title"><?= e($c['short']) ?></strong>
                            <span class="fund-spot__tag"><?= e($c['tagline']) ?></span>
                            <span class="fund-spot__chips">
                                <em>Total <?= e($fmt($c['total_usd'])) ?></em>
                                <em>Launch <?= e($fmt($c['launch_usd'])) ?></em>
                                <em class="is-gold">Give from <?= e($fmt($c['gift_usd'])) ?></em>
                            </span>
                            <span class="fund-spot__go">See how to give <?= icon('arrow-right', 'icon--sm') ?></span>
                        </span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>

        <?php foreach ($fh['soon'] as $s): ?>
            <p class="fund-band__soon"><b>Coming next:</b> <a href="<?= e(url('fund-a-project/' . $s['slug'])) ?>"><?= e($s['short']) ?></a>, <?= e(lcfirst((string) $s['tagline'])) ?>. Register your interest.</p>
        <?php endforeach; ?>

        <p class="fund-band__cta">
            <a class="btn btn--cta btn--caps" href="<?= e(url('fund-a-project')) ?>">Fund a project <?= icon('arrow-right', 'icon--sm') ?></a>
            <a class="btn btn--photo btn--caps" href="<?= e(url('donate')) ?>">Or give without choosing</a>
        </p>
    </div>
</section>
