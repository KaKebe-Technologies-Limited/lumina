<?php
/**
 * Public "Share this issue" row of a newsletter issue (docs/SOCIAL.md section 8). OWNER: IMAGES agent.
 * Plain links to the platforms' own share pages: no third-party script, no tracker, no embedded widget; the only JavaScript is the "Copy link" button of assets/js/pages/blog.js
 * (hidden until the Clipboard API exists). All wording is editable in Admin > Pages > Newsletter > Share buttons (registry `newsletter` > `share`).
 * Vars: $issue (newsletters row), $absUrl (absolute address of the issue).
 */
use App\Services\Web\ShareLinks;

$labels = [];
foreach (['facebook', 'x', 'linkedin', 'whatsapp', 'telegram', 'email'] as $k) {
    $labels[$k] = trim((string) content('newsletter', 'share.label_' . $k, ''));
}
$site = (string) setting('site_name', '');
$fill = static fn(string $tpl): string => strtr($tpl, ['{title}' => (string) $issue['title'], '{url}' => (string) $absUrl, '{site}' => $site]);
$links = ShareLinks::extended(
    (string) $absUrl,
    (string) $issue['title'],
    $labels,
    $fill((string) content('newsletter', 'share.email_subject', 'Newsletter: {title}')),
    $fill((string) content('newsletter', 'share.email_body', "I thought you would like this newsletter from {site}:\n\n{title}\n{url}"))
);
$heading = trim((string) content('newsletter', 'share.title', 'Share this issue'));
$on = (string) content('newsletter', 'share.on', 'Share on {network}');
$copyLabel = (string) content('newsletter', 'share.copy', 'Copy link');
$copied = (string) content('newsletter', 'share.copied', 'Link copied');
?>
<div class="nl-share" data-share>
    <?php if ($heading !== ''): ?><h2 class="nl-share__title"><?= e($heading) ?></h2><?php endif; ?>
    <ul class="nl-share__list">
        <?php foreach ($links as $s): $isMail = $s['key'] === 'email'; ?>
            <li>
                <a class="nl-share__link nl-share__link--<?= e($s['key']) ?>" href="<?= e($s['url']) ?>"<?= $isMail ? '' : ' target="_blank" rel="noopener noreferrer"' ?> aria-label="<?= e($isMail ? $s['label'] : str_replace('{network}', $s['label'], $on)) ?>">
                    <?= icon($s['icon'], 'icon--sm') ?><span class="nl-share__text"><?= e($s['label']) ?></span>
                </a>
            </li>
        <?php endforeach; ?>
        <li>
            <button class="nl-share__link nl-share__copy" type="button" data-copy-link="<?= e($absUrl) ?>" data-copied-text="<?= e($copied) ?>" hidden>
                <?= icon('link', 'icon--sm') ?><span class="nl-share__text" data-copy-text><?= e($copyLabel) ?></span>
            </button>
        </li>
    </ul>
    <p class="visually-hidden" role="status" aria-live="polite" data-copy-status></p>
</div>
