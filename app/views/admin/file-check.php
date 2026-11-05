<?php /** Admin > File check. Vars: $hasManifest, $generated, $missing, $different, $ok. */
$bad = count($missing) + count($different);
$group = static function (array $list): array {
    $g = [];
    foreach ($list as $p) {
        $g[dirname($p)][] = basename($p);
    }
    ksort($g);
    return $g;
};
?>
<?= partial('partials/admin/page-header', ['title' => 'File check', 'subtitle' => 'Compares the files on this server with the latest version of the site. A page that shows "500" often means a file was not uploaded, or went into the wrong folder: this page lists exactly which.']) ?>
<?php if (!$hasManifest): ?>
    <div class="alert alert--warning" role="status"><strong>The file list is missing.</strong> Upload <code>app/config/manifest.php</code> (from the latest upload folder), then open this page again.</div>
<?php else: ?>
    <div class="alert alert--<?= $bad === 0 ? 'success' : 'warning' ?>" role="status">
        <strong><?= $bad === 0 ? 'Every file matches.' : $bad . ' file' . ($bad === 1 ? '' : 's') . ' need' . ($bad === 1 ? 's' : '') . ' attention.' ?></strong>
        <?= (int) $ok ?> file<?= $ok === 1 ? '' : 's' ?> are exactly as in the latest version (list made <?= e($generated) ?>).
    </div>
    <?php if ($missing !== []): ?>
        <div class="card" style="margin-bottom:1rem"><div class="card__header"><h2 class="card__title">Missing: upload these (<?= count($missing) ?>)</h2></div><div class="card__body">
            <p class="text-muted">These files do not exist on the server. Upload each one into the folder shown, under <code>public_html</code>.</p>
            <?php foreach ($group($missing) as $dir => $names): ?>
                <p style="margin:.5rem 0 .15rem"><strong><?= e($dir) ?>/</strong></p>
                <ul style="margin:0 0 .5rem;padding-left:1.2rem"><?php foreach ($names as $n): ?><li><code><?= e($n) ?></code></li><?php endforeach; ?></ul>
            <?php endforeach; ?>
        </div></div>
    <?php endif; ?>
    <?php if ($different !== []): ?>
        <div class="card" style="margin-bottom:1rem"><div class="card__header"><h2 class="card__title">Different: an older version is on the server (<?= count($different) ?>)</h2></div><div class="card__body">
            <p class="text-muted">These files exist but their content is not the latest. Delete the old file and upload the new one.</p>
            <?php foreach ($group($different) as $dir => $names): ?>
                <p style="margin:.5rem 0 .15rem"><strong><?= e($dir) ?>/</strong></p>
                <ul style="margin:0 0 .5rem;padding-left:1.2rem"><?php foreach ($names as $n): ?><li><code><?= e($n) ?></code></li><?php endforeach; ?></ul>
            <?php endforeach; ?>
        </div></div>
    <?php endif; ?>
    <?php if ($bad > 0): ?>
        <div class="card"><div class="card__header"><h2 class="card__title">Send this list to the developer</h2></div><div class="card__body">
            <p class="text-muted">Copy everything in the box and send it: the missing upload folder can then be made in one go.</p>
            <textarea class="textarea" rows="8" readonly><?= e("MISSING:\n" . implode("\n", $missing) . "\n\nDIFFERENT:\n" . implode("\n", $different)) ?></textarea>
        </div></div>
    <?php endif; ?>
<?php endif; ?>
