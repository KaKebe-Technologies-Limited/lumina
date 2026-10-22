<?php
/**
 * Admin > Documents > Signatories. Vars: $rows (signatories with 'attached' and 'waiting' counts), $docs (PDF documents: id, title, category, is_public), $roles.
 * The people responsible for the policies: name, title and a signature picture (kept privately). Attach a person to documents, then apply their signature.
 */
$this->start('scripts'); ?>
<script src="<?= e(asset('js/admin-documents.js')) ?>" defer></script>
<?php $this->end(); ?>
<div class="admin-org">
    <?= partial('partials/admin/page-header', [
        'title' => 'Signatories',
        'subtitle' => 'The people responsible for the policies. Add each person once with a picture of their signature, attach them to the documents they must sign, then apply their signature: every document gets a signed PDF with an "Approval and signatures" page.',
        'actions' => [['label' => 'All documents', 'url' => url('admin/documents'), 'class' => 'btn--ghost', 'icon' => 'arrow-left']],
    ]) ?>

    <section class="card" aria-labelledby="sg-new">
        <div class="card__header"><h2 class="card__title" id="sg-new">Add a person</h2></div>
        <form class="card__body" method="post" action="<?= e(url('admin/documents/signatories')) ?>" enctype="multipart/form-data" novalidate>
            <?= csrf_field() ?>
            <div class="form-grid">
                <?= partial('partials/form-field', ['name' => 'name', 'id' => 'sg-name', 'label' => 'Full name', 'required' => true, 'maxlength' => 150]) ?>
                <?= partial('partials/form-field', ['name' => 'title', 'id' => 'sg-title', 'label' => 'Title or position', 'maxlength' => 190, 'help' => 'For example: Chairperson of the Board, Executive Director.']) ?>
                <div class="field<?= error('signature') ? ' field--error' : '' ?>">
                    <label class="field__label" for="sg-file">Signature picture</label>
                    <input class="input" type="file" id="sg-file" name="signature" accept="image/png,image/jpeg">
                    <div class="field__help">A PNG with a transparent background looks best (sign on white paper, photograph or scan it, remove the background). JPEG also works. Up to 1.5 MB. You can add it later.</div>
                    <?php if (error('signature')): ?><div class="field__error" role="alert"><?= e((string) error('signature')) ?></div><?php endif; ?>
                </div>
            </div>
            <div class="form-actions"><button class="btn btn--primary" type="submit"><?= icon('plus', 'icon--sm') ?> Add the person</button></div>
        </form>
    </section>

    <?php if ($rows === []): ?>
        <?= partial('partials/empty-state', ['title' => 'No signatories yet', 'icon' => 'users', 'text' => 'Add the Chairperson of the Board, the Executive Director and anyone else who signs the policies.']) ?>
    <?php endif; ?>

    <?php foreach ($rows as $g): $gid = (int) $g['id']; $active = (int) $g['is_active'] === 1; ?>
        <section class="card doc-sig-person<?= $active ? '' : ' is-off' ?>" aria-labelledby="sg-<?= $gid ?>">
            <div class="card__header">
                <h2 class="card__title" id="sg-<?= $gid ?>"><?= e($g['name']) ?><?php if (!$active): ?> <span class="badge">Inactive</span><?php endif; ?></h2>
                <span class="text-muted"><?= e((string) ($g['title'] ?? '')) ?></span>
            </div>
            <div class="card__body doc-sig-person__body">
                <div class="doc-sig-person__pic">
                    <?php if (!empty($g['signature_path'])): ?>
                        <img src="<?= e(url('admin/documents/signatories/' . $gid . '/signature')) ?>?v=<?= e((string) strtotime((string) $g['signature_updated_at'])) ?>" alt="Signature of <?= e($g['name']) ?>" loading="lazy">
                    <?php else: ?>
                        <span class="text-muted">No signature picture yet.</span>
                    <?php endif; ?>
                </div>
                <form class="doc-sig-person__edit" method="post" action="<?= e(url('admin/documents/signatories/' . $gid)) ?>" enctype="multipart/form-data" novalidate>
                    <?= csrf_field() ?>
                    <div class="form-grid">
                        <?= partial('partials/form-field', ['name' => 'name', 'id' => 'sgn-' . $gid, 'label' => 'Name', 'required' => true, 'value' => $g['name'], 'maxlength' => 150]) ?>
                        <?= partial('partials/form-field', ['name' => 'title', 'id' => 'sgt-' . $gid, 'label' => 'Title', 'value' => (string) ($g['title'] ?? ''), 'maxlength' => 190]) ?>
                        <div class="field"><label class="field__label" for="sgf-<?= $gid ?>"><?= !empty($g['signature_path']) ? 'Replace the signature picture' : 'Upload the signature picture' ?></label>
                            <input class="input" type="file" id="sgf-<?= $gid ?>" name="signature" accept="image/png,image/jpeg"></div>
                    </div>
                    <button class="btn btn--outline btn--sm" type="submit"><?= icon('save', 'icon--sm') ?> Save</button>
                </form>
                <div class="doc-sig-person__actions">
                    <p class="text-muted">Attached to <strong><?= (int) $g['attached'] ?></strong> document<?= (int) $g['attached'] === 1 ? '' : 's' ?>; <strong><?= (int) $g['waiting'] ?></strong> awaiting this signature.</p>
                    <?php if ((int) $g['waiting'] > 0 && !empty($g['signature_path'])): ?>
                        <form method="post" action="<?= e(url('admin/documents/signatories/' . $gid . '/sign-all')) ?>" data-confirm="<?= e('Apply the signature of ' . $g['name'] . ' to the ' . (int) $g['waiting'] . ' documents that await it? Each signed PDF is rebuilt.') ?>"><?= csrf_field() ?>
                            <button class="btn btn--primary btn--sm" type="submit"><?= icon('check', 'icon--sm') ?> Apply to all <?= (int) $g['waiting'] ?> waiting documents</button></form>
                    <?php endif; ?>
                    <details class="doc-sig-attach">
                        <summary class="btn btn--outline btn--sm">Attach to documents</summary>
                        <form method="post" action="<?= e(url('admin/documents/signatories/' . $gid . '/attach')) ?>" novalidate>
                            <?= csrf_field() ?>
                            <div class="field"><label class="field__label" for="role-<?= $gid ?>">Role on the signature page</label>
                                <input class="input" id="role-<?= $gid ?>" name="role_label" list="roles-list" value="Approved by" maxlength="80"></div>
                            <label class="check"><input type="checkbox" name="all_policies" value="1"> <span>All policies</span></label>
                            <fieldset class="doc-sig-docs"><legend class="visually-hidden">Or choose documents</legend>
                                <?php foreach ($docs as $d): ?><label class="check"><input type="checkbox" name="documents[]" value="<?= (int) $d['id'] ?>"> <span><?= e($d['title']) ?><?= (int) $d['is_public'] === 1 ? ' <em class="text-muted">(public)</em>' : '' ?></span></label><?php endforeach; ?>
                            </fieldset>
                            <button class="btn btn--primary btn--sm" type="submit">Attach</button>
                        </form>
                    </details>
                    <div class="table__actions">
                        <form method="post" action="<?= e(url('admin/documents/signatories/' . $gid . '/toggle')) ?>" class="inline-form"><?= csrf_field() ?><button class="btn btn--ghost btn--sm" type="submit"><?= $active ? 'Make inactive' : 'Make active' ?></button></form>
                        <form method="post" action="<?= e(url('admin/documents/signatories/' . $gid . '/delete')) ?>" class="inline-form" data-confirm="<?= e('Delete ' . $g['name'] . ' and the signature picture? They are removed from every document and the signed copies are rebuilt.') ?>"><?= csrf_field() ?><button class="btn btn--ghost btn--sm org-danger" type="submit"><?= icon('trash', 'icon--sm') ?> Delete</button></form>
                    </div>
                </div>
            </div>
        </section>
    <?php endforeach; ?>
    <datalist id="roles-list"><?php foreach ($roles as $r): ?><option value="<?= e($r) ?>"><?php endforeach; ?></datalist>
</div>
