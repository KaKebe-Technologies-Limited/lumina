<?php /** The leave / activity log of one exam attempt. Vars: $events (rows of academy_exam_events). */
$labels = ['leave' => 'Left the page', 'return' => 'Came back', 'blur' => 'Window lost focus', 'pagehide' => 'Page closed or reloaded', 'fullscreen_exit' => 'Left full screen', 'copy_blocked' => 'Copy / paste blocked', 'resumed' => 'Page opened again', 'second_tab' => 'Opened in a second window (refused)', 'auto_submit' => 'Submitted automatically'];
if (empty($events)) { return; } ?>
<details class="aac-events"><summary><?= count($events) ?> exam event<?= count($events) === 1 ? '' : 's' ?></summary>
    <ul class="aac-events__list">
        <?php foreach ($events as $ev): ?>
            <li><span class="nowrap"><?= e(format_datetime($ev['created_at'], 'H:i:s')) ?></span> <?= e($labels[$ev['kind']] ?? $ev['kind']) ?><?= (int) $ev['counted'] === 1 ? ' <b>(counted)</b>' : '' ?><?= $ev['seconds_away'] !== null ? ' - away ' . (int) $ev['seconds_away'] . ' s' : '' ?><?= !empty($ev['detail']) ? ' - ' . e((string) $ev['detail']) : '' ?></li>
        <?php endforeach; ?>
    </ul>
</details>