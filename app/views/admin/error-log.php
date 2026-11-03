<?php /** Admin > Error log. Vars: $lines (at, level, text), $files, $level, $q, $days. */ ?>
<?= partial('partials/admin/page-header', ['title' => 'Error log', 'subtitle' => 'The newest problems the website recorded. When a page shows "500", find the time of the problem here and send the line to the developer.']) ?>
<form class="filters" method="get" action="<?= e(url('admin/error-log')) ?>" role="search">
    <div class="field"><label class="field__label" for="el-level">Show</label><select class="select" id="el-level" name="level">
        <?php foreach (['error' => 'Errors only', 'warning' => 'Errors and warnings', 'all' => 'Everything'] as $k => $l): ?><option value="<?= e($k) ?>"<?= $level === $k ? ' selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select></div>
    <div class="field"><label class="field__label" for="el-days">Days</label><select class="select" id="el-days" name="days">
        <?php foreach ([1 => 'Today', 2 => 'Today and yesterday', 3 => 'Last 3 days', 7 => 'Last 7 days'] as $k => $l): ?><option value="<?= (int) $k ?>"<?= $days === $k ? ' selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select></div>
    <div class="field field--grow"><label class="field__label" for="el-q">Contains</label><input class="input" id="el-q" type="search" name="q" value="<?= e($q) ?>" placeholder="For example: ambassadors" maxlength="80"></div>
    <button class="btn btn--outline" type="submit">Show</button>
</form>
<?php if ($files === []): ?>
    <?= partial('partials/empty-state', ['title' => 'No log file for these days', 'icon' => 'check-circle', 'text' => 'Nothing has been recorded: the website had no problem to report.']) ?>
<?php elseif ($lines === []): ?>
    <?= partial('partials/empty-state', ['title' => 'Nothing to show', 'icon' => 'check-circle', 'text' => 'No line matches. Files read: ' . implode(', ', $files) . '.']) ?>
<?php else: ?>
    <p class="result-count"><?= count($lines) ?> line<?= count($lines) === 1 ? '' : 's' ?> (newest first). Files read: <?= e(implode(', ', $files)) ?></p>
    <div class="table-wrap"><table class="table table--stack">
        <caption class="visually-hidden">Error log</caption>
        <thead><tr><th scope="col">When</th><th scope="col">Level</th><th scope="col">What happened</th></tr></thead>
        <tbody>
        <?php foreach ($lines as $l): ?>
            <tr><td class="nowrap" data-label="When"><?= e($l['at']) ?></td><td data-label="Level"><span class="badge badge--<?= $l['level'] === 'error' ? 'danger' : ($l['level'] === 'warning' ? 'warning' : 'neutral') ?>"><?= e($l['level']) ?></span></td><td data-label="What happened" style="word-break:break-word;font-family:ui-monospace,Menlo,Consolas,monospace;font-size:.85rem"><?= e($l['text']) ?></td></tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
<?php endif; ?>
