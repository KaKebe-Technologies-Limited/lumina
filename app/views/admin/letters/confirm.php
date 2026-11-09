<?php
/** Admin > Official letters > check before sending. Vars: $l, $set (rows, bad, over, mode, hide, personalise, again, subject), $newCount, $already, $token. */
$id = (int) $l['id'];
?>
<?= partial('partials/admin/page-header', ['title' => 'Check before sending', 'subtitle' => 'Step 2 of 2: nothing has been sent yet.', 'actions' => [['label' => 'Change the list', 'url' => url('admin/letters/' . $id . '/send'), 'icon' => 'arrow-left', 'class' => 'btn--ghost']]]) ?>
<section class="card"><div class="card__body">
    <p><strong><?= e($l['subject']) ?></strong> will go as a PDF attachment to <strong><?= (int) $newCount ?></strong> <?= $newCount === 1 ? 'address' : 'addresses' ?>,
        <?= $set['mode'] === 'one' ? 'in <strong>one e-mail</strong>' . ($set['hide'] ? ' (addresses hidden from each other)' : ' (everybody sees the other addresses)') : 'as <strong>one e-mail each</strong>' . ($set['personalise'] ? ', each letter addressed to the person by name' : '') ?>.</p>
    <?php if ($already > 0 && !$set['again']): ?><div class="alert alert--info" role="status"><?= (int) $already ?> address(es) already received this letter and will be skipped.</div><?php endif; ?>
    <?php if ($set['bad']): ?><div class="alert alert--warning" role="status">Not valid, so left out: <?= e(implode('; ', array_slice($set['bad'], 0, 15))) ?></div><?php endif; ?>
    <?php if ($set['over']): ?><div class="alert alert--warning" role="status">Only the first <?= (int) \App\Services\Letters\LetterService::MAX_RECIPIENTS ?> addresses are used.</div><?php endif; ?>
    <div class="table-wrap"><table class="table table--compact">
        <caption class="visually-hidden">Recipients</caption>
        <thead><tr><th scope="col">#</th><th scope="col">Name</th><th scope="col">E-mail</th></tr></thead>
        <tbody><?php foreach ($set['rows'] as $i => $r): ?><tr><td><?= $i + 1 ?></td><td><?= e($r['name'] ?: '-') ?></td><td><?= e($r['email']) ?></td></tr><?php endforeach; ?></tbody>
    </table></div>
    <p class="text-muted">Subject: <?= e($set['subject']) ?></p>
    <form method="post" action="<?= e(url('admin/letters/' . $id . '/go')) ?>" class="form-actions"><?= csrf_field() ?>
        <input type="hidden" name="token" value="<?= e($token) ?>">
        <button class="btn btn--primary" type="submit" <?= $newCount < 1 ? 'disabled' : '' ?>><?= icon('send', 'icon--sm') ?> Send now</button>
        <a class="btn btn--outline" href="<?= e(url('admin/letters/' . $id . '/preview')) ?>">Look at the letter again</a>
    </form>
</div></section>
