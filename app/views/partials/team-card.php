<?php
/**
 * Team member card. Vars: $member (team_members row). Shows initials when there is no photo.
 * The photo alt text is the person's name (never claims anything else).
 */
$photo = img_url($member['photo'] ?? '');
$initials = '';
foreach (array_slice(preg_split('/\s+/', trim((string) $member['name'])) ?: [], 0, 2) as $part) {
    $initials .= mb_strtoupper(mb_substr($part, 0, 1));
}
?>
<article class="team-card" data-reveal>
    <div class="team-card__photo">
        <?php if ($photo !== ''): ?>
            <img src="<?= e($photo) ?>" alt="<?= e($member['name']) ?>" loading="lazy">
        <?php else: ?>
            <span class="team-card__initials" aria-hidden="true"><?= e($initials) ?></span>
        <?php endif; ?>
    </div>
    <div class="team-card__body">
        <h3 class="team-card__name"><?= e($member['name']) ?></h3>
        <?php if (!empty($member['role_title'])): ?><p class="team-card__role"><?= e($member['role_title']) ?></p><?php endif; ?>
        <?php if (!empty($member['bio'])): ?><p class="team-card__bio"><?= e($member['bio']) ?></p><?php endif; ?>
        <?php if (!empty($member['linkedin']) || !empty($member['x']) || !empty($member['email'])): ?>
            <div class="team-card__links">
                <?php if (!empty($member['linkedin'])): ?><a href="<?= e($member['linkedin']) ?>" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn: <?= e($member['name']) ?>"><?= icon('linkedin') ?></a><?php endif; ?>
                <?php if (!empty($member['x'])): ?><a href="<?= e($member['x']) ?>" target="_blank" rel="noopener noreferrer" aria-label="X: <?= e($member['name']) ?>"><?= icon('twitter') ?></a><?php endif; ?>
                <?php if (!empty($member['email'])): ?><a href="mailto:<?= e($member['email']) ?>" aria-label="Email: <?= e($member['name']) ?>"><?= icon('mail') ?></a><?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</article>
