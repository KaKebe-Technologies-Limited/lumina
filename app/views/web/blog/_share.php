<?php
/**
 * Share row: network links + copy-link button (behaviour in assets/js/pages/blog.js; works as plain links without JS).
 * Vars: $share (ShareLinks::for()), $absUrl, $heading, $shareOn ("Share on {network}"), $copyLabel, $copiedLabel.
 */
?>
<div class="share" data-share>
    <?php if (trim((string) $heading) !== ''): ?><h2 class="share__title"><?= e($heading) ?></h2><?php endif; ?>
    <ul class="share__list">
        <?php foreach ($share as $s): ?>
            <li>
                <a class="share__link share__link--<?= e($s['key']) ?>" href="<?= e($s['url']) ?>" target="_blank" rel="noopener noreferrer" aria-label="<?= e(str_replace('{network}', $s['label'], (string) $shareOn)) ?>">
                    <?= icon($s['icon']) ?>
                </a>
            </li>
        <?php endforeach; ?>
        <li>
            <button class="share__link share__copy" type="button" data-copy-link="<?= e($absUrl) ?>" data-copied-text="<?= e($copiedLabel) ?>" hidden>
                <?= icon('link') ?> <span data-copy-text><?= e($copyLabel) ?></span>
            </button>
        </li>
    </ul>
    <p class="visually-hidden" role="status" aria-live="polite" data-copy-status></p>
</div>
