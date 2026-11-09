<?php
/** Admin > Official letters > write / edit. Vars: $l (letter or defaults), $signatories, $sigModes, $errors. Fields marked optional can stay empty. */
$id = (int) ($l['id'] ?? 0);
$v = static fn(string $k): string => (string) old($k, $l[$k] ?? '');
$err = static fn(string $k): string => isset($errors[$k]) ? '<span class="field__error">' . e($errors[$k]) . '</span>' : '';
$mode = $v('sig_mode') ?: 'blank';
$this->start('scripts'); ?><script src="<?= e(asset('js/admin-letters.js')) ?>" defer></script><?php $this->end(); ?>
<?= partial('partials/admin/page-header', ['title' => $id ? 'Edit letter' : 'New letter', 'subtitle' => 'Fill in what you need. Fields marked optional can be left empty and are simply left out of the PDF.', 'actions' => [['label' => 'All letters', 'url' => url('admin/letters'), 'icon' => 'arrow-left', 'class' => 'btn--ghost']]]) ?>
<form method="post" action="<?= e($id ? url('admin/letters/' . $id) : url('admin/letters')) ?>" novalidate>
    <?= csrf_field() ?>
    <div class="ad-letter-grid">
        <section class="card"><div class="card__body">
            <h2 class="card__title">The letter</h2>
            <div class="ad-letter-row">
                <div class="field"><label class="field__label" for="ref_no">Reference number (optional)</label>
                    <input class="input" id="ref_no" name="ref_no" maxlength="60" value="<?= e($v('ref_no')) ?>" placeholder="LAF/2026/001">
                    <label class="check"><input type="checkbox" name="auto_ref" value="1" <?= $v('ref_no') === '' ? 'checked' : '' ?>> Number it for me when the box is empty</label></div>
                <div class="field"><label class="field__label" for="letter_date">Date</label>
                    <input class="input" type="date" id="letter_date" name="letter_date" value="<?= e($v('letter_date')) ?>" required><?= $err('letter_date') ?></div>
            </div>
            <label class="check"><input type="checkbox" name="use_letterhead" value="1" <?= (int) old('use_letterhead', $l['use_letterhead'] ?? 1) === 1 ? 'checked' : '' ?>> Print the organisation's letterhead (logo, name, address) at the top</label>

            <h3 class="ad-letter-sub">To (all optional)</h3>
            <div class="ad-letter-row">
                <div class="field"><label class="field__label" for="to_name">Name</label><input class="input" id="to_name" name="to_name" maxlength="190" value="<?= e($v('to_name')) ?>"></div>
                <div class="field"><label class="field__label" for="to_title">Title / position</label><input class="input" id="to_title" name="to_title" maxlength="190" value="<?= e($v('to_title')) ?>"></div>
            </div>
            <div class="field"><label class="field__label" for="to_org">Organisation</label><input class="input" id="to_org" name="to_org" maxlength="190" value="<?= e($v('to_org')) ?>"></div>
            <div class="field"><label class="field__label" for="to_address">Address</label><textarea class="textarea" id="to_address" name="to_address" rows="3" maxlength="600" placeholder="P.O. Box 1, Kampala&#10;Uganda"><?= e($v('to_address')) ?></textarea></div>

            <div class="field"><label class="field__label" for="salutation">Greeting (optional)</label><input class="input" id="salutation" name="salutation" maxlength="190" value="<?= e($v('salutation')) ?>" placeholder="Dear Sir/Madam,"></div>
            <div class="field<?= isset($errors['subject']) ? ' field--error' : '' ?>"><label class="field__label" for="subject">RE: (subject) <span class="field__req" aria-hidden="true">*</span></label>
                <input class="input" id="subject" name="subject" maxlength="300" value="<?= e($v('subject')) ?>" required><?= $err('subject') ?></div>
            <div class="field<?= isset($errors['body']) ? ' field--error' : '' ?>"><label class="field__label" for="body">Body <span class="field__req" aria-hidden="true">*</span></label>
                <textarea class="textarea" id="body" name="body" rows="14" required><?= e($v('body')) ?></textarea>
                <span class="field__hint">Leave an empty line between paragraphs. Start a line with "- " for a bullet, "1." for a numbered point, or "## " for a small heading.</span><?= $err('body') ?></div>
            <div class="field"><label class="field__label" for="closing">Closing (optional)</label><input class="input" id="closing" name="closing" maxlength="120" value="<?= e($v('closing')) ?>" placeholder="Yours faithfully,"></div>
        </div></section>

        <section class="card"><div class="card__body">
            <h2 class="card__title">Sent by</h2>
            <div class="field<?= isset($errors['from_name']) ? ' field--error' : '' ?>"><label class="field__label" for="from_name">Name of the person sending <span class="field__req" aria-hidden="true">*</span></label>
                <input class="input" id="from_name" name="from_name" maxlength="190" value="<?= e($v('from_name')) ?>"><?= $err('from_name') ?></div>
            <div class="field"><label class="field__label" for="from_title">Position (optional)</label><input class="input" id="from_title" name="from_title" maxlength="190" value="<?= e($v('from_title')) ?>" placeholder="Executive Director"></div>
            <div class="field"><label class="field__label" for="from_office">Office / department (optional)</label><input class="input" id="from_office" name="from_office" maxlength="190" value="<?= e($v('from_office')) ?>" placeholder="Office of the Executive Director"></div>

            <h3 class="ad-letter-sub">Signature</h3>
            <?php foreach ($sigModes as $k => $label): ?>
                <label class="check"><input type="radio" name="sig_mode" value="<?= e($k) ?>" <?= $mode === $k ? 'checked' : '' ?> data-sig-mode> <?= e($label) ?></label>
            <?php endforeach; ?>
            <div class="field<?= isset($errors['signatory_id']) ? ' field--error' : '' ?>" data-sig-pick>
                <label class="field__label" for="signatory_id">Signatory</label>
                <select class="select" id="signatory_id" name="signatory_id">
                    <option value="0">Choose...</option>
                    <?php foreach ($signatories as $s): ?><option value="<?= (int) $s['id'] ?>" <?= (int) $v('signatory_id') === (int) $s['id'] ? 'selected' : '' ?>><?= e($s['name'] . ($s['title'] ? ' - ' . $s['title'] : '') . ($s['has_picture'] ? '' : ' (no picture yet)')) ?></option><?php endforeach; ?>
                </select><?= $err('signatory_id') ?>
                <span class="field__hint">Signature pictures are kept in <a href="<?= e(url('admin/documents/signatories')) ?>">Documents &gt; Signatories</a>.</span>
            </div>

            <h3 class="ad-letter-sub">At the end (optional)</h3>
            <div class="field"><label class="field__label" for="enclosures">Enclosures</label><textarea class="textarea" id="enclosures" name="enclosures" rows="2" maxlength="600" placeholder="One per line"><?= e($v('enclosures')) ?></textarea></div>
            <div class="field"><label class="field__label" for="cc">Copy to (cc)</label><textarea class="textarea" id="cc" name="cc" rows="2" maxlength="600" placeholder="One per line"><?= e($v('cc')) ?></textarea></div>
        </div></section>
    </div>
    <div class="form-actions">
        <button class="btn btn--primary" type="submit" name="preview" value="1"><?= icon('eye', 'icon--sm') ?> Save and preview the PDF</button>
        <button class="btn btn--outline" type="submit">Save</button>
        <a class="btn btn--ghost" href="<?= e(url('admin/letters')) ?>">Cancel</a>
    </div>
</form>
