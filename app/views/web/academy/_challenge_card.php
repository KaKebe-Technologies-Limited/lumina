<?php
/** Challenge card. Vars: $c (challenge row, with 'phase' and 'entries'). */
use App\Services\Academy\ChallengeService;
use App\Services\Academy\CourseService;

$href = url('academy/challenges/' . $c['slug']);
$cat = CourseService::CATEGORIES[$c['category']] ?? ['Topic', 'star'];
$phase = (string) ($c['phase'] ?? ChallengeService::phase($c));
$icon = $c['kind'] === 'quiz' ? 'target' : ($c['kind'] === 'hackathon' ? 'code' : 'star');
?>
<article class="card ac-card ac-card--challenge" data-reveal>
    <a class="ac-card__icon ac-tone-<?= e((string) $c['category']) ?>" href="<?= e($href) ?>" tabindex="-1" aria-hidden="true"><?= icon(safe_icon($icon, 'star'), 'icon--xl') ?></a>
    <div class="card__body">
        <div class="ac-card__tags">
            <span class="opp-chip"><?= e(ChallengeService::KINDS[$c['kind']] ?? 'Challenge') ?></span>
            <span class="opp-badge ac-phase ac-phase--<?= e($phase) ?>"><?= e(ChallengeService::PHASES[$phase] ?? '') ?></span>
        </div>
        <h3 class="card__title"><a href="<?= e($href) ?>"><?= e($c['title']) ?></a></h3>
        <?php if (trim((string) $c['summary']) !== ''): ?><p class="card__text"><?= e($c['summary']) ?></p><?php endif; ?>
        <ul class="ac-card__facts">
            <li><?= icon('layers', 'icon--sm') ?> <?= e($cat[0]) ?></li>
            <?php if (!empty($c['starts_at'])): ?><li><?= icon('calendar', 'icon--sm') ?> <?= e(format_datetime($c['starts_at'], 'j M Y, H:i')) ?> EAT</li><?php endif; ?>
            <?php if (isset($c['entries'])): ?><li><?= icon('users', 'icon--sm') ?> <?= (int) $c['entries'] ?> entr<?= (int) $c['entries'] === 1 ? 'y' : 'ies' ?></li><?php endif; ?>
        </ul>
        <div class="card__footer"><a class="btn btn--outline btn--caps btn--sm" href="<?= e($href) ?>"><?= $phase === 'finished' ? 'See the results' : (ChallengeService::canApply($c) ? 'Apply now' : 'View details') ?></a></div>
    </div>
</article>
