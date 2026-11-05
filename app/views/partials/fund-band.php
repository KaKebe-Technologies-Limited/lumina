<?php
/** "Fund a project" band for the home, Donate and Projects pages: the project covers and a button to the hub. No variables. */
$fbProjects = \App\Services\Fund\FundProjects::all();
if ($fbProjects === []) {
    return;
}
?>
<section class="section section--navy fund-band" aria-labelledby="fund-band-title">
    <div class="container">
        <header class="section-head section-head--on-dark section-head--center">
            <span class="eyebrow">Fund a project</span>
            <h2 class="section-head__title" id="fund-band-title">Pick a project. Pick a contribution. Change a future.</h2>
            <p class="section-head__lead">Our costed proposals for Northern Uganda are open for funding. Give to the whole project, a launch package, or one priced contribution, from a sanitary kit to a solar-powered ICT hub.</p>
        </header>
        <ul class="fund-band__grid">
            <?php foreach ($fbProjects as $fp): ?>
                <li><a class="fund-band__item" href="<?= e(url('fund-a-project/' . $fp['slug'])) ?>"><img src="<?= e(asset('img/projects/' . $fp['cover'] . '-card.jpg')) ?>" alt="" width="800" height="450" loading="lazy"><span><?= e($fp['short']) ?></span></a></li>
            <?php endforeach; ?>
        </ul>
        <p class="fund-band__cta"><a class="btn btn--cta btn--caps" href="<?= e(url('fund-a-project')) ?>">Fund a project <?= icon('arrow-right', 'icon--sm') ?></a></p>
    </div>
</section>
