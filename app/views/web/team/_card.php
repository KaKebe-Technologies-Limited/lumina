<?php
/**
 * One team member card - OWNED BY B7 "org-public". Reuses the shared .team-card look (see scss/components/_team-card.scss).
 * Vars: $member (team_members row, links already filtered by TeamController), $readBio (button label).
 *
 * Long biographies: the full text is always in the page (so it reads fine without JavaScript); when JavaScript is available
 * assets/js/pages/team.js shows a short excerpt in the card and opens the full text in a modal dialog from the button.
 * The photo's alt text is only the person's name - we never describe or claim anything about the picture.
 */
$m = $member;
$name = (string) $m['name'];
$photo = img_url($m['photo'] ?? '');
$initials = '';
foreach (array_slice(preg_split('/\s+/', trim($name)) ?: [], 0, 2) as $part) {
    $initials .= mb_strtoupper(mb_substr($part, 0, 1));
}
$bio = trim((string) ($m['bio'] ?? ''));
$long = $bio !== '' && (mb_strlen($bio) > 220 || str_contains(str_replace("\r", '', $bio), "\n\n"));
$linkedin = (string) ($m['linkedin'] ?? '');
$x = (string) ($m['x'] ?? '');
$email = (string) ($m['email'] ?? '');
?>
<article class="team-card<?= $long ? ' team-card--long' : '' ?>" id="member-<?= (int) $m['id'] ?>" data-reveal>
    <div class="team-card__photo">
        <?php if ($photo !== ''): ?>
            <img src="<?= e($photo) ?>" alt="<?= e($name) ?>" loading="lazy" data-bio-photo>
        <?php else: ?>
            <span class="team-card__initials" aria-hidden="true"><?= e($initials) ?></span>
        <?php endif; ?>
    </div>
    <div class="team-card__body">
        <h3 class="team-card__name" data-bio-name><?= e($name) ?></h3>
        <?php if (trim((string) ($m['role_title'] ?? '')) !== ''): ?><p class="team-card__role" data-bio-role><?= e($m['role_title']) ?></p><?php endif; ?>
        <?php if ($bio !== ''): ?>
            <div class="team-card__bio team-card__bio--full" data-bio-full><?= nl2p($bio) ?></div>
            <?php if ($long): ?>
                <p class="team-card__bio team-card__bio--short"><?= e(excerpt($bio, 150)) ?></p>
                <button class="btn btn--outline btn--sm team-card__more" type="button" data-bio-open aria-haspopup="dialog" hidden><?= e($readBio) ?><span class="visually-hidden">: <?= e($name) ?></span></button>
            <?php endif; ?>
        <?php endif; ?>
        <?php if ($linkedin !== '' || $x !== '' || $email !== ''): ?>
            <div class="team-card__links" data-bio-links>
                <?php if ($linkedin !== ''): ?><a href="<?= e($linkedin) ?>" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn: <?= e($name) ?>"><?= icon('linkedin') ?></a><?php endif; ?>
                <?php if ($x !== ''): ?><a href="<?= e($x) ?>" target="_blank" rel="noopener noreferrer" aria-label="X: <?= e($name) ?>"><?= icon('twitter') ?></a><?php endif; ?>
                <?php if ($email !== ''): ?><a href="mailto:<?= e($email) ?>" aria-label="Email: <?= e($name) ?>"><?= icon('mail') ?></a><?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</article>
