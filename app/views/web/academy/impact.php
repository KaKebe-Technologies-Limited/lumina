<?php
declare(strict_types=1);
/**
 * LAF Academy DASHBOARD (public): /academy/impact. The Academy in numbers: learners by topic and by country, growth by month and the road from sign-up to certificate.
 * Only counts and percentages are shown, never a name or an e-mail. Vars: $d (AcademyStats::publicData()), $ready (bool: the Academy tables exist).
 */
$o = (array) ($d['overview'] ?? []);
?>
<?= partial('partials/page-hero', [
    'eyebrow' => 'LAF Academy',
    'title'   => 'The Academy in numbers',
    'lead'    => 'Who is learning with us, what they are studying and where they come from. The figures update by themselves as learners join and finish courses.',
    'actions' => [['label' => 'Join free', 'url' => url('academy/register'), 'class' => 'btn--cta btn--caps'], ['label' => 'Browse the courses', 'url' => url('academy'), 'class' => 'btn--photo btn--caps']],
]) ?>

<section class="section ad-page">
    <div class="container">
        <?php if (!$ready): ?>
            <p class="ad-empty">The dashboard will appear here once the Academy has learners.</p>
        <?php else: ?>
            <div data-reveal><?= partial('partials/academy-charts', ['part' => 'kpis', 'overview' => $o]) ?></div>

            <div class="ad-grid">
                <article class="ad-panel" data-reveal>
                    <h2 class="ad-panel__title"><?= icon('layers', 'icon--sm') ?> Learners by topic</h2>
                    <p class="ad-panel__sub">How many learners joined a course in each topic.</p>
                    <?= partial('partials/academy-charts', ['part' => 'topics', 'topics' => $d['topics']]) ?>
                </article>
                <article class="ad-panel" data-reveal>
                    <h2 class="ad-panel__title"><?= icon('globe', 'icon--sm') ?> Learners by country</h2>
                    <p class="ad-panel__sub">Where our learners say they come from.</p>
                    <?= partial('partials/academy-charts', ['part' => 'countries', 'countries' => $d['countries']]) ?>
                </article>
                <article class="ad-panel" data-reveal>
                    <h2 class="ad-panel__title"><?= icon('trending-up', 'icon--sm') ?> Growth, month by month</h2>
                    <p class="ad-panel__sub">New learners and certificates over the last 12 months.</p>
                    <?= partial('partials/academy-charts', ['part' => 'months', 'months' => $d['months']]) ?>
                </article>
                <article class="ad-panel" data-reveal>
                    <h2 class="ad-panel__title"><?= icon('award', 'icon--sm') ?> From sign-up to certificate</h2>
                    <p class="ad-panel__sub">How far learners go. <?= (float) ($o['completionPct'] ?? 0) > 0 ? 'Across all course enrolments, ' . rtrim(rtrim(number_format((float) $o['completionPct'], 1), '0'), '.') . '% are completed.' : '' ?></p>
                    <?= partial('partials/academy-charts', ['part' => 'funnel', 'funnel' => $d['funnel']]) ?>
                </article>
            </div>
            <p class="ad-note"><?= icon('shield', 'icon--sm') ?> These are totals only. No learner's name, e-mail or any personal detail is shown on this page.</p>
        <?php endif; ?>
    </div>
</section>
