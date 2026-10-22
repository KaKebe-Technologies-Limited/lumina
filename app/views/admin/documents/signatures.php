<?php
/** Admin > Documents > one document's signatures. Vars: $doc, $rows, $status, $pick (signatories not yet attached), $roles, $isPdf. */
$id = (int) $doc['id'];
$tone = ['signed' => 'success', 'partial' => 'warning', 'awaiting' => 'info', 'none' => 'neutral'][$status['state']] ?? 'neutral';
?>
<div class="admin-org">
    <?= partial('partials/admin/page-header', [
        'title' => 'Signatures',
        'subtitle' => (string) $doc['title'],
        'actions' => [
            ['label' => 'Signatories', 'url' => url('admin/documents/signatories'), 'class' => 'btn--outline', 'icon' => 'users'],
            ['label' => 'Back to the document', 'url' => url('admin/documents/' . $id . '/edit'), 'class' => 'btn--ghost', 'icon' => 'arrow-left'],
        ],
    ]) ?>

    <?php if (!$isPdf): ?><p class="opp-notice"><?= icon('alert-triangle', 'icon--sm') ?> Only PDF documents can be signed. This file is not a PDF.</p><?php endif; ?>

    <section class="card" aria-labelledby="ds-state">
        <div class="card__header"><h2 class="card__title" id="ds-state">Status</h2><?= partial('partials/admin/status-badge', ['status' => $status['state'], 'tone' => $tone, 'label' => $status['label']]) ?></div>
        <div class="card__body">
            <?php if (!empty($doc['signed_path'])): ?>
                <p><?= icon('check-circle', 'icon--sm') ?> A signed copy exists (built <?= e(format_datetime($doc['signed_at'], 'j M Y, H:i')) ?>, <?= e(human_filesize((int) $doc['signed_size'])) ?>).
                    <?= match ((string) ($doc['signed_mode'] ?? '')) { 'inplace' => 'The signatures, names and dates are on the document\'s own signature lines.', 'merged' => 'It is the original pages plus an "Approval and signatures" page.', default => 'It is the approval page on its own.' } ?></p>
                <p><a class="btn btn--primary btn--sm" href="<?= e(url('admin/documents/' . $id . '/signed')) ?>"><?= icon('download', 'icon--sm') ?> Download the signed copy</a>
                    <a class="btn btn--outline btn--sm" href="<?= e(url('admin/documents/' . $id . '/download')) ?>">Download the original</a></p>
                <p class="text-muted">Visitors get the signed copy while this document is public. <?= (int) $doc['is_public'] === 1 ? 'It is public now.' : 'It is private now: make it public from the document list when you are ready.' ?></p>
            <?php else: ?>
                <p class="text-muted">There is no signed copy yet. Add the people who sign below and apply their signatures: the signed PDF is built automatically.</p>
            <?php endif; ?>
        </div>
    </section>

    <section class="card" aria-labelledby="ds-rows">
        <div class="card__header"><h2 class="card__title" id="ds-rows">Who signs</h2>
            <?php if ($isPdf): ?><form method="post" action="<?= e(url('admin/documents/' . $id . '/signatures/read')) ?>" class="inline-form"><?= csrf_field() ?>
                <button class="btn btn--outline btn--sm" type="submit"><?= icon('refresh', 'icon--sm') ?> Read the signature page</button></form><?php endif; ?></div>
        <div class="card__body">
            <?php if ($rows === []): ?>
                <p class="text-muted">Nobody is attached yet. Press "Read the signature page" to find who must sign this document, or add a person below.</p>
            <?php else: ?>
                <div class="table-wrap"><table class="table table--stack">
                    <caption class="visually-hidden">Signatures of this document</caption>
                    <thead><tr><th scope="col">Role</th><th scope="col">Person</th><th scope="col">Signature</th><th scope="col">Status</th><th scope="col" class="col-actions"><span class="visually-hidden">Actions</span></th></tr></thead>
                    <tbody>
                    <?php foreach ($rows as $r): $rid = (int) $r['id']; $done = !empty($r['signed_at']); ?>
                        <tr>
                            <td data-label="Role"><?= e($r['role_label']) ?><?php if ($r['slot_index'] !== null): ?><span class="cell-sub">printed in the document</span><?php endif; ?></td>
                            <?php if (empty($r['signatory_id'])): ?>
                            <td data-label="Person" colspan="2">
                                <form class="doc-sig-pick" method="post" action="<?= e(url('admin/documents/' . $id . '/signatures/' . $rid . '/assign')) ?>"><?= csrf_field() ?>
                                    <select class="select" name="signatory_id" aria-label="Person for <?= e($r['role_label']) ?>"><option value="">Choose who signs as <?= e($r['role_label']) ?></option>
                                        <?php foreach ($people as $p): ?><option value="<?= (int) $p['id'] ?>"><?= e($p['name']) ?><?= $p['title'] ? ' (' . e($p['title']) . ')' : '' ?></option><?php endforeach; ?></select>
                                    <button class="btn btn--outline btn--sm" type="submit">Assign</button></form>
                            </td>
                            <td data-label="Status"><span class="badge">Waiting for a person</span></td>
                            <td class="col-actions"></td>
                            <?php else: ?>
                            <td data-label="Person"><strong><?= e($r['name']) ?></strong><span class="cell-sub"><?= e((string) ($r['title'] ?? '')) ?></span></td>
                            <td data-label="Signature"><?php if (!empty($r['signature_path'])): ?><img class="doc-sig-thumb" src="<?= e(url('admin/documents/signatories/' . (int) $r['signatory_id'] . '/signature')) ?>" alt="Signature of <?= e($r['name']) ?>" loading="lazy"><?php else: ?><span class="text-muted">No picture uploaded</span><?php endif; ?></td>
                            <td data-label="Status"><?= $done ? '<span class="badge badge--success">Signed ' . e(format_date($r['signed_at'], 'j M Y')) . '</span>' : '<span class="badge badge--info">Awaiting signature</span>' ?></td>
                            <td class="col-actions"><div class="table__actions">
                                <?php if (!$done): ?>
                                    <form method="post" action="<?= e(url('admin/documents/' . $id . '/signatures/' . $rid . '/apply')) ?>" class="inline-form"><?= csrf_field() ?>
                                        <button class="btn btn--primary btn--sm" type="submit"<?= empty($r['signature_path']) || !$isPdf ? ' disabled' : '' ?>><?= icon('check', 'icon--sm') ?> Apply signature</button></form>
                                <?php else: ?>
                                    <form method="post" action="<?= e(url('admin/documents/' . $id . '/signatures/' . $rid . '/unsign')) ?>" class="inline-form" data-confirm="Withdraw this signature? The signed copy is rebuilt without it."><?= csrf_field() ?>
                                        <button class="btn btn--outline btn--sm" type="submit">Withdraw</button></form>
                                <?php endif; ?>
                                <form method="post" action="<?= e(url('admin/documents/' . $id . '/signatures/' . $rid . '/delete')) ?>" class="inline-form" data-confirm="Remove this person from the document?"><?= csrf_field() ?>
                                    <button class="btn btn--ghost btn--sm org-danger" type="submit" aria-label="Remove <?= e($r['name']) ?>"><?= icon('trash', 'icon--sm') ?></button></form>
                            </div></td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                    </tbody></table></div>
            <?php endif; ?>

            <form class="doc-sig-add" method="post" action="<?= e(url('admin/documents/' . $id . '/signatures/add')) ?>" novalidate>
                <?= csrf_field() ?>
                <div class="form-grid">
                    <div class="field"><label class="field__label" for="ds-person">Add a person</label>
                        <select class="select" id="ds-person" name="signatory_id" required><option value="">Choose a person</option>
                            <?php foreach ($pick as $g): ?><option value="<?= (int) $g['id'] ?>"><?= e($g['name']) ?><?= $g['title'] ? ' (' . e($g['title']) . ')' : '' ?><?= empty($g['signature_path']) ? ' - no picture yet' : '' ?></option><?php endforeach; ?>
                        </select></div>
                    <div class="field"><label class="field__label" for="ds-role">Role on the signature page</label>
                        <input class="input" id="ds-role" name="role_label" list="ds-roles" value="Approved by" maxlength="80"><datalist id="ds-roles"><?php foreach ($roles as $r): ?><option value="<?= e($r) ?>"><?php endforeach; ?></datalist></div>
                </div>
                <button class="btn btn--outline" type="submit"><?= icon('plus', 'icon--sm') ?> Add to this document</button>
                <?php if ($pick === []): ?><span class="text-muted">Every active person is already attached. <a href="<?= e(url('admin/documents/signatories')) ?>">Add a new person</a>.</span><?php endif; ?>
            </form>
        </div>
    </section>

    <section class="card" aria-labelledby="ds-details">
        <div class="card__header"><h2 class="card__title" id="ds-details">Approval details (printed on the signature page)</h2></div>
        <form class="card__body" method="post" action="<?= e(url('admin/documents/' . $id . '/signatures/details')) ?>" novalidate>
            <?= csrf_field() ?>
            <div class="form-grid">
                <?= partial('partials/form-field', ['name' => 'version', 'id' => 'ds-version', 'label' => 'Version', 'value' => (string) ($doc['version'] ?? ''), 'maxlength' => 20, 'help' => 'For example 1.0.']) ?>
                <?= partial('partials/form-field', ['name' => 'approval_authority', 'id' => 'ds-auth', 'label' => 'Approval authority', 'value' => (string) ($doc['approval_authority'] ?? ''), 'maxlength' => 120, 'help' => 'For example Board of Directors.']) ?>
                <?= partial('partials/form-field', ['name' => 'adoption_date', 'id' => 'ds-date', 'label' => 'Date of adoption', 'type' => 'date', 'value' => (string) ($doc['adoption_date'] ?? ''), 'help' => 'The date the policy was adopted. Leave empty if it is not decided yet.']) ?>
            </div>
            <div class="form-actions"><button class="btn btn--primary" type="submit"><?= icon('save', 'icon--sm') ?> Save details</button></div>
        </form>
        <?php if ($rows !== [] && $isPdf): ?>
            <form class="card__footer" method="post" action="<?= e(url('admin/documents/' . $id . '/signatures/generate')) ?>"><?= csrf_field() ?>
                <button class="btn btn--outline btn--sm" type="submit"><?= icon('refresh', 'icon--sm') ?> Rebuild the signed copy</button>
                <span class="text-muted">Use this after replacing a signature picture or editing the details.</span>
            </form>
        <?php endif; ?>
    </section>
</div>
