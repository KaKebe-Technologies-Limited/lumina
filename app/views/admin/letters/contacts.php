<?php
/** Admin > Official letters > contacts. Vars: $ready, $contacts. */
?>
<?= partial('partials/admin/page-header', ['title' => 'Letter contacts', 'subtitle' => 'People and groups you e-mail letters to. You can also just type addresses when sending.', 'actions' => [['label' => 'All letters', 'url' => url('admin/letters'), 'icon' => 'arrow-left', 'class' => 'btn--ghost']]]) ?>
<?php if ($ready): ?>
<div class="ad-letter-grid">
    <section class="card"><div class="card__body">
        <h2 class="card__title">Add one contact</h2>
        <form method="post" action="<?= e(url('admin/letters/contacts')) ?>"><?= csrf_field() ?>
            <div class="field"><label class="field__label" for="c-name">Name</label><input class="input" id="c-name" name="name" maxlength="190"></div>
            <div class="field"><label class="field__label" for="c-email">E-mail <span class="field__req" aria-hidden="true">*</span></label><input class="input" type="email" id="c-email" name="email" required></div>
            <div class="field"><label class="field__label" for="c-org">Organisation</label><input class="input" id="c-org" name="organisation" maxlength="190"></div>
            <div class="field"><label class="field__label" for="c-phone">Phone</label><input class="input" id="c-phone" name="phone" maxlength="60"></div>
            <div class="field"><label class="field__label" for="c-grp">Group (for example Partners)</label><input class="input" id="c-grp" name="grp" maxlength="80"></div>
            <button class="btn btn--primary" type="submit">Save contact</button>
        </form>
    </div></section>
    <section class="card"><div class="card__body">
        <h2 class="card__title">Paste many at once</h2>
        <form method="post" action="<?= e(url('admin/letters/contacts/import')) ?>"><?= csrf_field() ?>
            <div class="field"><label class="field__label" for="i-list">Contacts</label><textarea class="textarea" id="i-list" name="list" rows="8" placeholder="Name, email, Organisation, Phone&#10;Grace Auma &lt;grace@example.org&gt;&#10;peter@example.org"></textarea></div>
            <div class="field"><label class="field__label" for="i-grp">Put them in the group (optional)</label><input class="input" id="i-grp" name="grp" maxlength="80"></div>
            <button class="btn btn--primary" type="submit">Import</button>
        </form>
    </div></section>
</div>
<section class="card"><div class="card__body">
    <h2 class="card__title">Contact book (<?= count($contacts) ?>)</h2>
    <?php if (!$contacts): ?><p class="text-muted">No contacts yet.</p><?php else: ?>
    <div class="table-wrap"><table class="table table--compact">
        <thead><tr><th scope="col">Name</th><th scope="col">E-mail</th><th scope="col">Organisation</th><th scope="col">Phone</th><th scope="col">Group</th><th scope="col"><span class="visually-hidden">Remove</span></th></tr></thead>
        <tbody><?php foreach ($contacts as $c): ?>
            <tr><td><?= e($c['name']) ?></td><td><?= e($c['email']) ?></td><td><?= e((string) $c['organisation']) ?></td><td><?= e((string) $c['phone']) ?></td><td><?= e((string) $c['grp']) ?></td>
                <td><form method="post" action="<?= e(url('admin/letters/contacts/' . (int) $c['id'] . '/delete')) ?>" class="inline-form"><?= csrf_field() ?><button class="btn btn--ghost btn--sm" type="submit">Remove</button></form></td></tr>
        <?php endforeach; ?></tbody>
    </table></div><?php endif; ?>
</div></section>
<?php else: ?><div class="alert alert--warning" role="status">The letter tables are missing. Import <code>database/updates/2026-11-official-letters.sql</code>.</div><?php endif; ?>
