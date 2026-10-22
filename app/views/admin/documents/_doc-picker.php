<?php
/**
 * Which documents a person signs and in which role on each. Vars: $docs (id, title, category, is_public), $roles, $skip (optional list of document ids to leave out), $uid (unique id prefix).
 * Fields: documents[] = document ids; doc_role[<id>] = the role on that document.
 */
$skip = array_flip(array_map('intval', $skip ?? []));
?>
<fieldset class="doc-sig-docs" data-sig-picker>
    <legend class="field__label">Documents this person signs, and the role on each</legend>
    <div class="doc-sig-docs__tools">
        <label class="check"><input type="checkbox" data-sig-all="policy"> <span>Select all policies</span></label>
        <label class="check"><input type="checkbox" data-sig-all="all"> <span>Select every document</span></label>
    </div>
    <?php foreach ($docs as $d): if (isset($skip[(int) $d['id']])) { continue; } $did = (int) $d['id']; ?>
        <div class="doc-sig-docs__row">
            <label class="check"><input type="checkbox" name="documents[]" value="<?= $did ?>" data-category="<?= e($d['category']) ?>"> <span><?= e($d['title']) ?><?= (int) $d['is_public'] === 1 ? ' <em class="text-muted">(public)</em>' : '' ?></span></label>
            <select class="select" name="doc_role[<?= $did ?>]" aria-label="Role on <?= e($d['title']) ?>">
                <?php foreach ($roles as $r): ?><option value="<?= e($r) ?>"><?= e($r) ?></option><?php endforeach; ?>
            </select>
        </div>
    <?php endforeach; ?>
    <?php if ($docs === []): ?><p class="text-muted">There are no PDF documents yet. Import the policy pack under Documents first.</p><?php endif; ?>
</fieldset>
