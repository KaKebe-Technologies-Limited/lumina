<?php
/** Winners of the month. Vars: $months (list of {month,label,winners}), $learner. Copy: content('academy', 'winners.*'). */
use App\Services\Academy\ChallengeService;
use App\Services\Academy\CourseService;

$initials = static function (string $name): string {
    $parts = preg_split('/\s+/u', trim($name)) ?: [];
    return mb_strtoupper(mb_substr((string) ($parts[0] ?? '?'), 0, 1) . (count($parts) > 1 ? mb_substr((string) end($parts), 0, 1) : ''));
};
?>
<?= partial('partials/page-hero', ['title' => content('academy', 'winners.title'), 'eyebrow' => 'LAF Academy', 'lead' => content('academy', 'winners.lead'),
    'actions' => [['label' => 'See the challenges', 'url' => url('academy/challenges'), 'class' => 'btn--photo btn--caps']]]) ?>
<section class="section">
    <div class="container">
        <?php if ($months === []): ?>
            <?= partial('partials/empty-state', ['title' => 'No winners yet', 'text' => content('academy', 'winners.empty_text'), 'icon' => 'award']) ?>
        <?php endif; ?>
        <?php foreach ($months as $m): ?>
            <h2 class="ac-block__title" id="m-<?= e($m['month']) ?>"><?= e($m['label']) ?></h2>
            <div class="ac-winners">
                <?php foreach ($m['winners'] as $w): ?>
                    <article class="ac-winner<?= (int) $w['rank_no'] === 1 ? ' ac-winner--first' : '' ?>" data-reveal>
                        <div class="ac-winner__pic">
                            <?php if ($w['photo_ok']): ?>
                                <img src="<?= e(url('academy/winners/' . (int) $w['id'] . '/photo')) ?>" alt="Photo of <?= e($w['display_name']) ?>" width="220" height="220" loading="lazy">
                            <?php else: ?>
                                <span class="ac-winner__initials" aria-hidden="true"><?= e($initials((string) $w['display_name'])) ?></span>
                            <?php endif; ?>
                            <span class="ac-winner__badge"><?= icon('award', 'icon--sm') ?> <?= e((string) $w['award']) ?></span>
                        </div>
                        <h3 class="ac-winner__name"><?= e($w['display_name']) ?></h3>
                        <p class="ac-winner__meta"><a href="<?= e(url('academy/challenges/' . $w['challenge_slug'])) ?>"><?= e($w['challenge_title']) ?></a><br>
                            <?= e(CourseService::categoryLabel((string) $w['challenge_category'])) ?> &middot; score <?= e(rtrim(rtrim(number_format((float) $w['score'], 1), '0'), '.')) ?>%</p>
                        <?php if (!empty($w['cert_code'])): ?><p class="ac-winner__cert"><a href="<?= e(url('academy/certificate/' . $w['cert_code'])) ?>">Check the certificate</a></p><?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </div>
</section>
