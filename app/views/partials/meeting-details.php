<?php
/**
 * SHARED partial: the detail rows of a meeting (when, organiser, how to join, passcode, dial-in, address) as a definition list.
 * Used by the invitee page AND the admin meeting page so both read the same rows that MeetingRenderer::details() puts in the e-mails.
 * Vars: $rows (from MeetingRenderer::details()), $class (extra class, optional), $iso_start / $iso_end (optional UTC ISO strings: the first row
 * ("when") gets <time datetime> + data attributes so assets/js/meetings.js can add "in your time zone").
 */
$class = trim('meeting-details ' . ($class ?? ''));
?>
<dl class="<?= e($class) ?>">
    <?php foreach ($rows as $r): ?>
        <div class="meeting-details__row meeting-details__row--<?= e($r['key']) ?>">
            <dt><?= e($r['label']) ?></dt>
            <dd>
                <?php if ($r['key'] === 'when' && !empty($iso_start)): ?>
                    <time datetime="<?= e($iso_start) ?>" data-meeting-start="<?= e($iso_start) ?>"<?= !empty($iso_end) ? ' data-meeting-end="' . e($iso_end) . '"' : '' ?>><?= e($r['text']) ?></time>
                <?php elseif (!empty($r['url'])): ?>
                    <a href="<?= e($r['url']) ?>"<?= str_starts_with($r['url'], 'https://') ? ' target="_blank" rel="noopener noreferrer"' : '' ?>><?= e($r['text']) ?></a>
                <?php else: ?>
                    <?= nl2br(e($r['text'])) ?>
                <?php endif; ?>
            </dd>
        </div>
    <?php endforeach; ?>
</dl>
