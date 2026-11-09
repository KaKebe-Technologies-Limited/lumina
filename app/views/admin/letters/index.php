<?php
/** Admin > Official letters. Vars: $ready, $letters. */
?>
<?= partial('partials/admin/page-header', ['title' => 'Official letters', 'subtitle' => 'Write a letter, preview it as a PDF, then e-mail it to one person or to a whole group.', 'actions' => [['label' => 'Contacts', 'url' => url('admin/letters/contacts'), 'icon' => 'users', 'class' => 'btn--outline'], ['label' => 'New letter', 'url' => url('admin/letters/new'), 'icon' => 'plus']]]) ?>
<?php if (!$ready): ?>
    <div class="alert alert--warning" role="status"><strong>The letter tables could not be created automatically.</strong> Import <code>database/updates/2026-11-official-letters.sql</code> in phpMyAdmin once, then reload this page.</div>
<?php elseif (!$letters): ?>
    <section class="card"><div class="card__body"><p class="text-muted">No letters yet. Press <strong>New letter</strong>: add the date, address, the RE: line, the body, who sends it and the signature. Most fields are optional.</p></div></section>
<?php else: ?>
    <section class="card"><div class="card__body">
        <div class="table-wrap"><table class="table table--stack">
            <caption class="visually-hidden">Official letters</caption>
            <thead><tr><th scope="col">Letter</th><th scope="col">To</th><th scope="col">Date</th><th scope="col">Status</th><th scope="col" class="col-actions"><span class="visually-hidden">Actions</span></th></tr></thead>
            <tbody>
            <?php foreach ($letters as $l): ?>
                <tr>
                    <th scope="row" data-label="Letter"><a href="<?= e(url('admin/letters/' . (int) $l['id'] . '/preview')) ?>"><?= e($l['subject']) ?></a><?php if ($l['ref_no']): ?><span class="cell-sub"><?= e($l['ref_no']) ?></span><?php endif; ?></th>
                    <td data-label="To"><?= e(trim($l['to_name'] . ' ' . $l['to_org']) ?: 'Not named') ?></td>
                    <td data-label="Date"><?= e(format_date($l['letter_date'], 'j M Y')) ?></td>
                    <td data-label="Status"><?= $l['status'] === 'sent' ? '<span class="badge badge--success">Sent to ' . (int) $l['sent_to'] . '</span>' : '<span class="badge">Draft</span>' ?></td>
                    <td class="col-actions">
                        <a class="btn btn--outline btn--sm" href="<?= e(url('admin/letters/' . (int) $l['id'] . '/preview')) ?>"><?= icon('eye', 'icon--sm') ?> Preview</a>
                        <a class="btn btn--ghost btn--sm" href="<?= e(url('admin/letters/' . (int) $l['id'] . '/edit')) ?>">Edit</a>
                        <a class="btn btn--primary btn--sm" href="<?= e(url('admin/letters/' . (int) $l['id'] . '/send')) ?>"><?= icon('send', 'icon--sm') ?> Send</a>
                        <form method="post" action="<?= e(url('admin/letters/' . (int) $l['id'] . '/duplicate')) ?>" class="inline-form"><?= csrf_field() ?><button class="btn btn--ghost btn--sm" type="submit">Copy</button></form>
                        <form method="post" action="<?= e(url('admin/letters/' . (int) $l['id'] . '/delete')) ?>" class="inline-form" data-confirm="Delete this letter and its sending history?"><?= csrf_field() ?><button class="btn btn--ghost btn--sm" type="submit">Delete</button></form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table></div>
    </div></section>
<?php endif; ?>
