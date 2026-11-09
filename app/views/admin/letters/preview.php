<?php
/** Admin > Official letters > preview. Vars: $l, $sends. The PDF is shown in a frame (same origin) exactly as it will be attached. */
$id = (int) $l['id'];
?>
<?= partial('partials/admin/page-header', ['title' => 'Preview: ' . $l['subject'], 'subtitle' => 'This is the PDF exactly as the recipients will receive it. Nothing is sent from here.', 'actions' => [
    ['label' => 'Edit', 'url' => url('admin/letters/' . $id . '/edit'), 'icon' => 'edit', 'class' => 'btn--outline'],
    ['label' => 'Download PDF', 'url' => url('admin/letters/' . $id . '/preview.pdf', ['download' => 1]), 'icon' => 'download', 'class' => 'btn--outline'],
    ['label' => 'Send by e-mail', 'url' => url('admin/letters/' . $id . '/send'), 'icon' => 'send'],
]]) ?>
<section class="card"><div class="card__body">
    <iframe class="ad-letter-frame" title="Letter preview" src="<?= e(url('admin/letters/' . $id . '/preview.pdf')) ?>"></iframe>
</div></section>
<?php if ($sends): ?>
<section class="card"><div class="card__body">
    <h2 class="card__title">Sending history</h2>
    <div class="table-wrap"><table class="table table--compact">
        <thead><tr><th scope="col">When</th><th scope="col">How</th><th scope="col" class="num">Sent</th><th scope="col" class="num">Failed</th><th scope="col">By</th></tr></thead>
        <tbody>
        <?php foreach ($sends as $s): ?>
            <tr><td><?= e(format_date($s['created_at'], 'j M Y H:i')) ?></td><td><?= $s['mode'] === 'one' ? 'One e-mail to the group' : 'One e-mail each' ?></td><td class="num"><?= (int) $s['sent_count'] ?></td><td class="num"><?= (int) $s['failed_count'] ?></td><td><?= e((string) ($s['by_name'] ?? '')) ?></td></tr>
            <?php foreach (\App\Services\Letters\LetterService::sendRecipients((int) $s['id']) as $r): ?>
                <tr class="ad-letter-rcpt"><td></td><td colspan="4"><?= $r['status'] === 'sent' ? '&#10003;' : '&#10007;' ?> <?= e(trim($r['name'] . ' <' . $r['email'] . '>')) ?><?= $r['error'] ? ' - ' . e($r['error']) : '' ?></td></tr>
            <?php endforeach; ?>
        <?php endforeach; ?>
        </tbody>
    </table></div>
</div></section>
<?php endif; ?>
