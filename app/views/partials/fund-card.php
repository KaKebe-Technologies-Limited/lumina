<?php
/** A project card: the cover, the pillar, the title, what it does, the key numbers and the investment. Vars: $p (a project of app/config/fundraising/projects.php). */
$coming = $p['status'] !== 'proposal';
$top = array_slice($p['figures'], 0, 3);
?>
<article class="fund-pcard<?= $coming ? ' fund-pcard--soon' : '' ?>">
    <a class="fund-pcard__cover" href="<?= e(url('fund-a-project/' . $p['slug'])) ?>" tabindex="-1" aria-hidden="true">
        <img src="<?= e(asset('img/projects/' . $p['cover'] . '-card.jpg')) ?>" alt="" width="800" height="450" loading="lazy">
        <?php if ($coming): ?><span class="fund-pcard__flag">Proposal in preparation</span><?php endif; ?>
    </a>
    <div class="fund-pcard__body">
        <span class="fund-pcard__pillar"><?= e($p['pillar']) ?></span>
        <h3 class="fund-pcard__title"><a href="<?= e(url('fund-a-project/' . $p['slug'])) ?>"><?= e($p['short']) ?></a></h3>
        <p class="fund-pcard__text"><?= e($p['summary']) ?></p>
        <?php if ($top !== []): ?>
            <ul class="fund-pcard__stats"><?php foreach ($top as [$v, $l]): ?><li><b><?= e($v) ?></b><span><?= e($l) ?></span></li><?php endforeach; ?></ul>
        <?php endif; ?>
        <?php if (!$coming): ?>
            <p class="fund-pcard__ask">Total <strong><?= e(\App\Services\Fund\FundProjects::short($p['budget']['ugx'])) ?></strong> <span>(about USD <?= e(number_format((float) $p['budget']['usd'])) ?>)</span><br>Start with a launch package of <strong>USD <?= e(number_format((float) $p['packages'][1]['usd'])) ?></strong>, or give <strong>from USD <?= e(number_format((float) min(array_column($p['items'], 'usd')))) ?></strong>.</p>
        <?php endif; ?>
        <a class="btn btn--cta btn--caps btn--block" href="<?= e(url('fund-a-project/' . $p['slug'])) ?>"><?= $coming ? 'Register your interest' : 'See how to give' ?> <?= icon('arrow-right', 'icon--sm') ?></a>
    </div>
</article>
